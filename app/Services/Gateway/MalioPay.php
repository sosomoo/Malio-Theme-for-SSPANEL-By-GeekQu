<?php

namespace App\Services\Gateway;

use App\Services\View;
use App\Services\Auth;
use App\Services\Config;
use App\Models\Paylist;
use App\Services\MalioConfig;

class MalioPay extends AbstractPayment
{
    public function purchase($request, $response, $args)
    {
        $price = $request->getParam('price');
        $type = $request->getParam('type');
        $user = Auth::getUser();
        if ($type != 'alipay' and $type != 'wechat') {
            return json_encode(['ret' => 0, 'msg' => 'wrong type']);
        }

        if ($price < MalioConfig::get('mups_minimum_amount')) {
            return json_encode(['ret' => 0, 'msg' => '充值最低金额为' . MalioConfig::get('mups_minimum_amount') . '元']);
        }

        if ($price <= 0) {
            return json_encode(['ret' => 0, 'msg' => "金额必须大于0元"]);
        }

        if ($type == 'alipay') {
            $payment_system = MalioConfig::get('mups_alipay');
            switch ($payment_system) {
                case ('bitpayx'):
                    $bitpayx = new BitPayX(Config::get('bitpay_secret'));
                    $result = $bitpayx->purchase_maliopay($type, $price);
                    if ($result['errcode'] == 0) {
                        $return = array(
                            'ret' => 1,
                            'type' => 'url',
                            'tradeno' => $result['tradeno'],
                            'url' => $result['url']
                        );
                    } else {
                        $return = array(
                            'ret' => 0,
                            'msg' => $result['errmsg']
                        );
                    }
                    return json_encode($return);
                case ('tomatopay'):
                    $tomatopay = new TomatoPay();
                    $result = $tomatopay->purchase_maliopay($type, $price);
                    if ($result['errcode'] == 0) {
                        $return = array(
                            'ret' => 1,
                            'type' => 'url',
                            'tradeno' => $result['tradeno'],
                            'url' => $result['code']
                        );
                    } else {
                        $return = array(
                            'ret' => 0,
                            'msg' => $result['errmsg']
                        );
                    }
                    return json_encode($return);
            }
        } else if ($type == 'wechat') {
            $payment_system = MalioConfig::get('mups_wechat');
            switch ($payment_system) {
                case ('bitpayx'):
                    $bitpayx = new BitPayX(Config::get('bitpay_secret'));
                    $result = $bitpayx->purchase_maliopay($type, $price);
                    if ($result['errcode'] == 0) {
                        $return = array(
                            'ret' => 1,
                            'type' => 'qrcode',
                            'tradeno' => $result['tradeno'],
                            'url' => $result['qrcode_url']
                        );
                    } else {
                        $return = array(
                            'ret' => 0,
                            'msg' => $result['errmsg']
                        );
                    }
                    return json_encode($return);
                case ('tomatopay'):
                    $tomatopay = new TomatoPay();
                    $result = $tomatopay->purchase_maliopay($type, $price);
                    if ($result['errcode'] == 0) {
                        $return = array(
                            'ret' => 1,
                            'type' => 'url',
                            'tradeno' => $result['tradeno'],
                            'url' => $result['code']
                        );
                    } else {
                        $return = array(
                            'ret' => 0,
                            'msg' => $result['errmsg']
                        );
                    }
                    return json_encode($return);
            }
        }
    }

    public function notify($request, $response, $args)
    {
        $payment_system = $request->getParam('paysys');

        switch ($payment_system) {
            case ('bitpayx'):
                $bitpayx = new BitPayX(Config::get('bitpay_secret'));
                if (!$bitpayx->bitpayAppSecret || $bitpayx->bitpayAppSecret === '') {
                    $return = [];
                    $return['status'] = 400;
                    echo json_encode($return);
                    return;
                }
                $inputString = file_get_contents('php://input', 'r');
                $inputStripped = str_replace(array("\r", "\n", "\t", "\v"), '', $inputString);
                $inputJSON = json_decode($inputStripped, true); //convert JSON into array
                $data = array();
                if ($inputJSON !== null) {
                    $data['status'] = $inputJSON['status'];
                    $data['order_id'] = $inputJSON['order_id'];
                    $data['merchant_order_id'] = $inputJSON['merchant_order_id'];
                    $data['price_amount'] = $inputJSON['price_amount'];
                    $data['price_currency'] = $inputJSON['price_currency'];
                    $data['created_at_t'] = $inputJSON['created_at_t'];
                }
                $str_to_sign = $bitpayx->prepareSignId($inputJSON['merchant_order_id']);
                $resultVerify = $bitpayx->verify($str_to_sign, $inputJSON['token']);
                $isPaid = $data !== null && $data['status'] !== null && $data['status'] === 'PAID';
                if ($resultVerify && $isPaid) {
                    $bitpayx->postPayment($data['merchant_order_id'], '在线支付 ' . $data['merchant_order_id']);
                    // echo 'SUCCESS';
                    $return = [];
                    $return['status'] = 200;
                    echo json_encode($return);
                } else {
                    // echo 'FAIL';
                    $return = [];
                    $return['status'] = 400;
                    echo json_encode($return);
                }
                return 'bitpayx';
            case ('tomatopay'):
                $type = $args['type'];
                $settings = Config::get("tomatopay")[$type];
                $order_data = $_REQUEST;
                $transid   = $order_data['trade_no'];       //转账交易号
                $invoiceid = $order_data['out_trade_no'];     //订单号
                $amount    = $order_data['total_fee'];          //获取递过来的总价格
                $status    = $order_data['trade_status'];         //获取传递过来的交易状态
                $signs    = $order_data['sign'];

                $security  = array();
                $security['out_trade_no']      = $invoiceid;
                $security['total_fee']    = $amount;
                $security['trade_no']        = $transid;
                $security['trade_status']       = $status;
                foreach ($security as $k => $v) {
                    $o .= "$k=" . urlencode($v) . "&";
                }
                $sign = md5(substr($o, 0, -1) . $settings['token']);


                if ($sign == $signs) {
                    //验重
                    $p = Paylist::where('tradeno', '=', $order_data['out_trade_no'])->first();
                    $money = $p->total;
                    if ($p->status != 1) {
                        $this->postPayment($order_data['out_trade_no'], "在线支付");
                        echo 'SUCCESS';
                    } else {
                        echo 'ERROR';
                    }
                    echo 'success';
                } else {
                    echo '验证失败';
                }

                return 'tomatopay';
            default:
                return 'failed';
        }
    }

    public function getPurchaseHTML()
    {
        return 1;
    }

    public function getReturnHTML($request, $response, $args)
    {
        $tradeno = $_GET['tradeno'];
        $p = Paylist::where('tradeno', '=', $tradeno)->first();
        $money = $p->total;
        if ($p->status === 1) {
            $success = 1;
        } else {
            $success = 0;
        }
        return View::getSmarty()->assign('money', $money)->assign('success', $success)->fetch('user/pay_success.tpl');
    }

    public function getStatus($request, $response, $args)
    {
        $p = Paylist::where('tradeno', $_POST['tradeno'])->first();
        $return['ret'] = 1;
        $return['result'] = $p->status;
        return json_encode($return);
    }
}
