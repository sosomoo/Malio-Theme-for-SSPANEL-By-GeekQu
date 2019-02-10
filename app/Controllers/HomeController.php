<?php

namespace App\Controllers;

use App\Models\InviteCode;
use App\Models\User;
use App\Models\Code;
use App\Models\Payback;
use App\Models\Paylist;
use App\Services\Auth;
use App\Services\Config;
use App\Services\Payment;
use App\Utils\AliPay;
use App\Utils\Tools;
use App\Utils\Telegram;
use App\Utils\Tuling;
use App\Utils\TelegramSessionManager;
use App\Utils\QRcode;
use App\Utils\Pay;
use App\Utils\TelegramProcess;
use App\Utils\Spay_tool;
use App\Utils\Geetest;

/**
 *  HomeController
 */
class HomeController extends BaseController
{
    public function index()
    {
        $GtSdk = null;
        $recaptcha_sitekey = null;
        if (Config::get('captcha_provider') != ''){
            switch(Config::get('captcha_provider'))
            {
                case 'recaptcha':
                    $recaptcha_sitekey = Config::get('recaptcha_sitekey');
                    break;
                case 'geetest':
                    $uid = time().rand(1, 10000) ;
                    $GtSdk = Geetest::get($uid);
                    break;
            }
        }

        if (Config::get('enable_telegram') == 'true') {
            $login_text = TelegramSessionManager::add_login_session();
            $login = explode("|", $login_text);
            $login_token = $login[0];
            $login_number = $login[1];
        } else {
            $login_token = '';
            $login_number = '';
        }

        return $this->view()
            ->assign('geetest_html', $GtSdk)
            ->assign('login_token', $login_token)
            ->assign('login_number', $login_number)
            ->assign('telegram_bot', Config::get('telegram_bot'))
            ->assign('enable_logincaptcha', Config::get('enable_login_captcha'))
            ->assign('enable_regcaptcha', Config::get('enable_reg_captcha'))
            ->assign('base_url', Config::get('baseUrl'))
            ->assign('recaptcha_sitekey', $recaptcha_sitekey)
            ->display('index.tpl');
    }

    public function indexold()
    {
        return $this->view()->display('indexold.tpl');
    }

    public function code()
    {
        $codes = InviteCode::where('user_id', '=', '0')->take(10)->get();
        return $this->view()->assign('codes', $codes)->display('code.tpl');
    }

    public function down()
    {
    }

    public function tos()
    {
        return $this->view()->display('tos.tpl');
    }
    
    public function staff()
    {
        return $this->view()->display('staff.tpl');
    }
    
    public function telegram($request, $response, $args)
    {
        $token = "";
        if (isset($request->getQueryParams()["token"])) {
            $token = $request->getQueryParams()["token"];
        }
        
        if ($token == Config::get('telegram_request_token')) {
            TelegramProcess::process();
        } else {
            echo("不正确请求！");
        }
    }
    
    public function page404($request, $response, $args)
    {
        return $this->view()->display('404.tpl');
    }
    
    public function page405($request, $response, $args)
    {
        return $this->view()->display('405.tpl');
    }
    
    public function page500($request, $response, $args)
    {
		return $this->view()->display('500.tpl');
    }

    public function getOrderList($request, $response, $args)
    {
        $key = $request->getParam('key');
        if (!$key || $key != Config::get('key')) {
            $res['ret'] = 0;
            $res['msg'] = "错误";
            return $response->getBody()->write(json_encode($res));
        }
        return $response->getBody()->write(json_encode(['data' => AliPay::getList()]));
    }

    public function setOrder($request, $response, $args)
    {
        $key = $request->getParam('key');
        $sn = $request->getParam('sn');
        $url = $request->getParam('url');
        if (!$key || $key != Config::get('key')) {
            $res['ret'] = 0;
            $res['msg'] = "错误";
            return $response->getBody()->write(json_encode($res));
        }
        return $response->getBody()->write(json_encode(['res' => AliPay::setOrder($sn, $url)]));
    }

    public function docCenter()
    {
        return $this->view()->display('doc/index.tpl');
    }

    public function sublinkOut($request, $response, $args)
    {
        $user = Auth::getUser();
        if (!$user->isLogin) {
            $msg = "₍₍ ◝(・ω・)◟ ⁾⁾ 您没有登录噢，登录之后再刷新就阔以了啦";
        } else {
            $subUrl = Config::get('subUrl') . LinkController::GenerateSSRSubCode($user->id, 0);
            switch ($request->getParam('type')) {
                case 'ss':
                    $msg =  "```
个人端口：" . $subUrl . "?sub=2&mu=0
公共端口：" . $subUrl . "?sub=2&mu=1
```";
                    break;
                case 'ssr':
                    $msg =  "```
个人端口：" . $subUrl . "?sub=1&mu=0
公共端口：" . $subUrl . "?sub=1&mu=1
```";
                    break;
                case 'v2ray':
                    // v2rayN 格式
                    $msg =  "```
公共端口：" . $subUrl . "?sub=3
```";
                    break;
                    // APPs~
                case 'ssd':
                    $msg =  "```
个人端口：" . $subUrl . "?ssd=1&mu=0
公共端口：" . $subUrl . "?ssd=1&mu=1
```";
                    break;
                case 'clash':
                    $msg =  "```
个人端口：" . $subUrl . "?clash=1&mu=0
公共端口：" . $subUrl . "?clash=1&mu=1
```";
                    break;
                case 'surge':
                    $msg =  "```
// Surge Version 2.x 
个人端口：" . $subUrl . "?surge=2&mu=0
公共端口：" . $subUrl . "?surge=2&mu=1
// Surge Version 3.x 
个人端口：" . $subUrl . "?surge=3&mu=0
公共端口：" . $subUrl . "?surge=3&mu=1
```";
                    break;
                case 'kitsunebi':
                    $msg =  "```
// 合并订阅，包含 ss、v2ray
个人端口：" . $subUrl . "?sub=4&mu=0
公共端口：" . $subUrl . "?sub=4&mu=1
// v2ray 订阅
公共端口：" . $subUrl . "?sub=3
```";
                    break;
                    case 'surfboard':
                    $msg =  "```
个人端口：" . $subUrl . "?surfboard=1&mu=0
公共端口：" . $subUrl . "?surfboard=1&mu=1
```";
                    break;
                case 'quantumult_sub':
                    // Quantumult V2ray 专属格式
                    $msg =  "```
// ssr 订阅
个人端口：" . $subUrl . "?sub=1&mu=0
公共端口：" . $subUrl . "?sub=1&mu=1
// V2ray 订阅
公共端口：" . $subUrl . "?quantumult=1
```";
                    break;
                case 'quantumult_conf':
                    $msg =  "```
// 导入 ss、ssr、v2ray 以及分流规则的配置
个人端口[全部订阅]：" . $subUrl . "?quantumult=2&mu=0
公共端口[全部订阅]：" . $subUrl . "?quantumult=2&mu=1
// 使用自定义策略组的配置，类似 Surge
个人端口[全部订阅]：" . $subUrl . "?quantumult=3&mu=0
公共端口[全部订阅]：" . $subUrl . "?quantumult=3&mu=1
```";
                    break;
                case 'shadowrocket':
                    $msg =  "```
// ssr 订阅
个人端口：" . $subUrl . "?sub=1&mu=0
公共端口：" . $subUrl . "?sub=1&mu=1
// 合并订阅，包含 ss、ssr、v2ray
个人端口：" . $subUrl . "?sub=5&mu=0
公共端口：" . $subUrl . "?sub=5&mu=1
```";
                    break;
                default:
                    $msg = '获取失败了呢...，请联系管理员。';
                    break;
            }
        }
        return $msg;
    }

}
