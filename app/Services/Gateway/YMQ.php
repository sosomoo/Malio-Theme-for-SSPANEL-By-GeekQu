<?php

namespace App\Services\Gateway;

use App\Services\View;
use App\Services\Auth;
use App\Services\Config;
use App\Models\Paylist;

class YMQ extends AbstractPayment
{
    private $appSecret;
    private $gatewayUri;
    private $appId;
    private $itemNames;
    /**
     * 签名初始化
     * @param merKey    签名密钥
     */
    public function __construct($appSetting)
    {
        $this->appSecret = $appSetting['appSecret'];
        $this->gatewayUri = 'https://open.yunmianqian.com/api/pay';
        $this->appId =$appSetting['appId'];
        $this->itemNames = $appSetting['itemNames'];
    }
    /**
     * @name    准备签名/验签字符串
     */
    public function prepareSign($data)
    {
        return $data['app_id'].$data["out_order_sn"].$data['name'].
            $data['pay_way'].(string)$data['price'].$data["attach"].$data['notify_url'].$this->appSecret;
    }
    public function perparePostSign($data){
        return $data['app_id'].$data['order_sn'].$data["out_order_sn"].$data['notify_count'].
            $data['pay_way'].(string)$data['price'].$data['qr_type'].$data['qr_price'].
            $data['pay_price'].$data['created_at'].$data['paid_at'].$data["attach"].$data['server_time'].$this->appSecret;
    }
    /**
     * @name    生成签名
     * @param sourceData
     * @return    签名数据
     */
    public function sign($data)
    {
        return strtolower(md5($data));
    }
    /*
     * @name    验证签名
     * @param   signData 签名数据
     * @param   sourceData 原数据
     * @return
     */
    public function verify($data, $signature)
    {
        $mySign = $this->sign($data);
        return $mySign === $signature;
    }
    public function post($data)
    {
        $postdata = http_build_query($data);
        $opts = array('http' =>
            array( 'method'  => 'POST','header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata ) );
        $context = stream_context_create($opts);
        $result = file_get_contents($this->gatewayUri, false, $context);

        return $result;
    }
    public function purchase($request, $response, $args)
    {
        $price = $request->getParam('price');
        $type = $request->getParam('type');
        if ($price <= 0) {
            return json_encode(['code' => -1, 'errmsg' => '非法的金额.']);
        }
        $user = Auth::getUser();
        $pl = new Paylist();
        $pl->userid = $user->id;
        $pl->total = $price;
        $pl->tradeno = self::generateGuid();
        $pl->save();

        $data = ["app_id"=>$this->appId,
            "out_order_sn"=>$pl->tradeno,
            "name"=>$this->itemNames[array_rand($this->itemNames)],
            "pay_way"=>$type,
            "price"=>(int) $price * 100,
            "attach"=>"",
            "notify_url"=>Config::get('baseUrl') . '/payment/notify?way=YMQ',
        ];
        $params = $this->prepareSign($data);
        $data['sign'] = $this->sign($params);
        $contents = utf8_encode($this->post($data));
        $result = json_decode($contents,true);
        return json_encode(['code' => $result['code'], "msg"=>$result['msg'],'url' => $result['data']['qr'], 'pid' => $data['out_order_sn'],
            "pay_way"=>$type,"expire_in"=>$result['data']['expire_in']]);
        //$result = json_decode($this->post($data), true);
        //$result['pid'] = $pl->tradeno;
        //return json_encode($result);
    }

    public function notify($request, $response, $args)
    {
        $data = $_POST;
            // 验证签名
            $in_sign = $data['sign'];
            unset($data['sign']);
            $sign = $this->sign($this->perparePostSign($data));
            $resultVerify = $sign==$in_sign ? true : false;
            if ($resultVerify) {
                // 验重
                $p = Paylist::where('tradeno', '=', $data['out_order_sn'])->first();
                $money = $p->total;
                if ($p->status != 1) {
                    $this->postPayment($data['out_order_sn'], '云免签-'.strtoupper($data['pay_way']));
                    echo 'success';
                } else {
                    echo 'ERROR';
                }
            } else {
                echo 'FAIL2';
            }
        }

    public function getPurchaseHTML()
    {
        return View::getSmarty()->fetch('user/ymq.tpl');
    }
    public function getReturnHTML($request, $response, $args)
    {
        $pid = $_GET['merchantTradeNo'];
        $p = Paylist::where('tradeno', '=', $pid)->first();
        $money = $p->total;
        if ($p->status == 1) {
            $success = 1;
        } else {
            $data = $_POST;
            
            $in_sign = $data['sign'];
            unset($data['sign']);
            $data = array_filter($data);
            $sign = $this->sign($this->perparePostSign($data));
            $resultVerify = $sign==$in_sign ? true : false;

            if ($resultVerify) {
                $this->postPayment($data['out_trade_no'], '云免签'.data['pay_way']);
                $success = 1;
            } else {
                $success = 0;
            }
        }
        return View::getSmarty()->assign('money', $money)->assign('success', $success)->fetch('user/pay_success.tpl');
    }
    public function getStatus($request, $response, $args)
    {
        $return = [];
        $p = Paylist::where('tradeno', $_POST['pid'])->first();
        $return['ret'] = 1;
        $return['result'] = $p->status;
        return json_encode($return);
    }
}
