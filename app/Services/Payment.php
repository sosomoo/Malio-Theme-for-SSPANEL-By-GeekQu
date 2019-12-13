<?php
/**
 * Created by PhpStorm.
 * User: tonyzou
 * Date: 2018/9/24
 * Time: 下午7:07
 */

namespace App\Services;

use App\Services\Gateway\{
    AopF2F, Codepay, DoiAMPay, PaymentWall, ChenPay, SPay, TrimePay, PAYJS, BitPayX,TomatoPay,IDtPay
};

class Payment
{   public static function getPaymentSystem(){
    $raw_methods = Config::get('payment_system');
    return str_split($raw_methods,",");
}
    public static function getClient($method="")
    {   if ($method==""|$method==null){
            $method=self::getPaymentSystem()[0];
        }
        switch ($method) {
            case ('codepay'):
                return new Codepay();
            case ('doiampay'):
                return new DoiAMPay();
            case ('paymentwall'):
                return new PaymentWall();
            case ('spay'):
                return new SPay();
            case ('f2fpay'):
                return new AopF2F();
            case ('chenAlipay'):
                return new ChenPay();
            case ('trimepay'):
                return new TrimePay(Config::get('trimepay_secret'));
            case ('bitpayx'):
                return new BitPayX(Config::get('bitpay_secret'));
            case ('payjs'):
                return new PAYJS(Config::get('payjs_key'));
            case ("tomatopay"):
                return new TomatoPay();
            case ("idtpay"):
                return new IDtPay();
            default:
                return null;
        }
    }

    public static function notify($request, $response, $args)
    {   $type = $args['type'];
        return self::getClient($type)->notify($request, $response, $args);
    }

    public static function returnHTML($request, $response, $args)
    {   $type = $args['type'];
        return self::getClient($type)->getReturnHTML($request, $response, $args);
    }

    public static function purchaseHTML()
    {   $methods =self::getPaymentSystem();
        $return_string ="";
        foreach ($methods as $method){
            if (self::getClient($method) != null) {
                $return_string = $return_string . self::getClient($method)->getPurchaseHTML();
            }
        }
        return $return_string;
    }

    public static function getStatus($request, $response, $args)
    {   $type = $args['type'];
        return self::getClient($type)->getStatus($request, $response, $args);
    }

    public static function purchase($request, $response, $args)
    {   $type = $args['type'];
        return self::getClient($type)->purchase($request, $response, $args);
    }
}
