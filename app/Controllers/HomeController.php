<?php

namespace App\Controllers;

use App\Models\InviteCode;
use App\Services\Auth;
use App\Services\Config;
use App\Utils\AliPay;
use App\Utils\TelegramSessionManager;
use App\Utils\TelegramProcess;
use App\Utils\Spay_tool;
use App\Utils\Geetest;
use App\Utils\Tools;

/**
 *  HomeController
 */
class HomeController extends BaseController
{
    public function index()
    {
        $GtSdk = null;
        $recaptcha_sitekey = null;
        if (Config::get('captcha_provider') != '') {
            switch (Config::get('captcha_provider')) {
                case 'recaptcha':
                    $recaptcha_sitekey = Config::get('recaptcha_sitekey');
                    break;
                case 'geetest':
                    $uid = time() . random_int(1, 10000);
                    $GtSdk = Geetest::get($uid);
                    break;
            }
        }

        if (Config::get('enable_telegram') == 'true') {
            $login_text = TelegramSessionManager::add_login_session();
            $login = explode('|', $login_text);
            $login_token = $login[0];
            $login_number = $login[1];
        } else {
            $login_token = '';
            $login_number = '';
        }

        if (Config::get('newIndex') != 'true' && Config::get('theme') == 'material') {
            return $this->view()->display('indexold.tpl');
        } else {
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
    { }

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
        $token = $request->getQueryParams()['token'] ?? '';

        if ($token == Config::get('telegram_request_token')) {
            TelegramProcess::process();
        } else {
            echo ('不正确请求！');
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
            $res['msg'] = '错误';
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
            $res['msg'] = '错误';
            return $response->getBody()->write(json_encode($res));
        }
        return $response->getBody()->write(json_encode(['res' => AliPay::setOrder($sn, $url)]));
    }

    public function getDocCenter($request, $response, $args)
    {
        $user = Auth::getUser();
        if (!$user->isLogin && Config::get('enable_documents') != 'true') {
            $newResponse = $response->withStatus(302)->withHeader('Location', '/');
            return $newResponse;
        }
        $basePath = Config::get('remote_documents') == 'true' ? Config::get('documents_source') : '/docs/GeekQu';
        return $this->view()
            ->assign('appName', Config::get('documents_name'))
            ->assign('basePath', $basePath)
            ->display('doc/index.tpl');
    }

    public function getSubLink($request, $response, $args)
    {
        $user = Auth::getUser();
        if (!$user->isLogin) {
            return $msg = '!> ₍₍ ◝(・ω・)◟ ⁾⁾ 您没有登录噢，[点击此处登录](/auth/login \':ignore target=_blank\') 之后再刷新就阔以了啦';
        } else {
            $subInfo = LinkController::getSubinfo($user, 0);
            switch ($request->getParam('type')) {
                case 'ssr':
                    $msg = [
                        '**订阅链接：**',
                        '```',
                        $subInfo['ssr'] . '&extend=1',
                        '```'
                    ];
                    break;
                case 'v2ray':
                    $msg = [
                        '**订阅链接：**',
                        '```',
                        $subInfo['v2ray'] . '&extend=1',
                        '```'
                    ];
                    break;
                case 'ssd':
                    $msg = [
                        '**订阅链接：**',
                        '```',
                        $subInfo['ssd'],
                        '```'
                    ];
                    break;
                case 'clash':
                    $msg = [
                        '**订阅链接：**[[点击下载配置]](' . $subInfo['clash'] . ')',
                        '```',
                        $subInfo['clash'],
                        '```'
                    ];
                    break;
                case 'surge':
                    $msg = [
                        '**Surge Version 2.x 托管配置链接：**[[iOS 点击此处一键添加]](surge:///install-config?url=' . urlencode($subInfo['surge2']) . ')',
                        '```',
                        $subInfo['surge2'],
                        '```',
                        '**Surge Version 3.x 托管配置链接：**[[iOS 点击此处一键添加]](surge3:///install-config?url=' . urlencode($subInfo['surge3']) . ')',
                        '```',
                        $subInfo['surge3'],
                        '```'
                    ];
                    break;
                case 'kitsunebi':
                    $msg = [
                        '**包含 ss、v2ray 的合并订阅链接：**',
                        '```',
                        $subInfo['kitsunebi'] . '&extend=1',
                        '```'
                    ];
                    break;
                case 'surfboard':
                    $msg = [
                        '**托管配置链接：**',
                        '```',
                        $subInfo['surfboard'],
                        '```'
                    ];
                    break;
                case 'quantumult_sub':
                    $msg = [
                        '**ssr 订阅链接：**[[iOS 点击此处一键添加]](quantumult://configuration?server=' . Tools::base64_url_encode($subInfo['ssr']) . ')',
                        '```',
                        $subInfo['ssr'],
                        '```',
                        '**V2ray 订阅链接：**[[iOS 点击此处一键添加]](quantumult://configuration?server=' . Tools::base64_url_encode($subInfo['quantumult_v2']) . ')',
                        '```',
                        $subInfo['quantumult_v2'],
                        '```'
                    ];
                    break;
                case 'quantumult_conf':
                    $msg = [
                        '**导入 ss、ssr、v2ray 以及分流规则的配置链接：**',
                        '```',
                        $subInfo['quantumult_sub'],
                        '```',
                        '**导入类似 Surge、Clash 使用自定义策略组的配置链接：**',
                        '```',
                        $subInfo['quantumult_conf'],
                        '```'
                    ];
                    break;
                case 'shadowrocket':
                    $msg = [
                        '**包含 ss、ssr、v2ray 的合并订阅链接：**[[iOS 点击此处一键添加]](sub://' . base64_encode($subInfo['shadowrocket']) . ')',
                        '```',
                        $subInfo['shadowrocket'],
                        '```'
                    ];
                    break;
                default:
                    $msg = [
                        '获取失败了呢...，请联系管理员。'
                    ];
                    break;
            }
        }
        return implode(PHP_EOL, $msg);
    }
}
