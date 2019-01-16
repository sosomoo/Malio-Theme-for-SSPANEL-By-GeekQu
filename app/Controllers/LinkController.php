<?php

//Thanks to http://blog.csdn.net/jollyjumper/article/details/9823047

namespace App\Controllers;

use App\Models\Link;
use App\Models\User;
use App\Models\Node;
use App\Models\Relay;
use App\Models\Smartline;
use App\Utils\ConfRender;
use App\Utils\Tools;
use App\Utils\URL;
use App\Services\Config;

/**
 *  HomeController
 */
class LinkController extends BaseController
{
    public function __construct()
    {
    }

    public static function GenerateRandomLink()
    {
        $i = 0;
        for ($i = 0; $i < 10; $i++) {
            $token = Tools::genRandomChar(16);
            $Elink = Link::where("token", "=", $token)->first();
            if ($Elink == null) {
                return $token;
            }
        }

        return "couldn't alloc token";
    }

    public static function GenerateSSRSubCode($userid, $without_mu)
    {
        $Elink = Link::where("type", "=", 11)->where("userid", "=", $userid)->where("geo", $without_mu)->first();
        if ($Elink != null) {
            return $Elink->token;
        }
        $NLink = new Link();
        $NLink->type = 11;
        $NLink->address = "";
        $NLink->port = 0;
        $NLink->ios = 0;
        $NLink->geo = $without_mu;
        $NLink->method = "";
        $NLink->userid = $userid;
        $NLink->token = LinkController::GenerateRandomLink();
        $NLink->save();

        return $NLink->token;
    }

    public static function GetContent($request, $response, $args)
    {
        $token = $args['token'];

        //$builder->getPhrase();
        $Elink = Link::where("token", "=", $token)->first();
        if ($Elink == null) {
            return null;
        }

        if ($Elink->type != 11) {
            return null;
        }

        $user = User::where("id", $Elink->userid)->first();
        if ($user == null) {
            return null;
        }

        $mu = 0;
        if (isset($request->getQueryParams()["mu"])) {
            $mu = (int)$request->getQueryParams()["mu"];
        }

        $sub = 0;
        if (isset($request->getQueryParams()["sub"])) {
            $sub = (int)$request->getQueryParams()["sub"];
        }

        $ssd = 0;
        if (isset($request->getQueryParams()["ssd"])) {
            $ssd = (int)$request->getQueryParams()["ssd"];
        }

        $clash = 0;
        if (isset($request->getQueryParams()["clash"])) {
            $clash = (int)$request->getQueryParams()["clash"];
        }

        $surge = 0;
        if (isset($request->getQueryParams()["surge"])) {
            $surge = (int)$request->getQueryParams()["surge"];
        }

        $quantumult = 0;
        if (isset($request->getQueryParams()["quantumult"])) {
            $quantumult = (int)$request->getQueryParams()["quantumult"];
        }

        $surfboard = 0;
        if (isset($request->getQueryParams()["surfboard"])) {
            $surfboard = (int)$request->getQueryParams()["surfboard"];
        }

        if (in_array($quantumult, array(1, 2, 3))) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=Quantumult.conf');
            $newResponse->getBody()->write(LinkController::GetQuantumult($user, $mu, $quantumult));
            return $newResponse;
        }
        elseif (in_array($surge, array(1, 2, 3))) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=Surge.conf');
            $newResponse->getBody()->write(LinkController::GetSurge($user, $mu, $surge));
            return $newResponse;
        }
        elseif ($surfboard == 1) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=Surfboard.conf');
            $newResponse->getBody()->write(LinkController::GetSurfboard($user, $mu));
            return $newResponse;
        }
        elseif ($clash == 1) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=config.yml');
            $newResponse->getBody()->write(LinkController::GetClash($user, $mu));
            return $newResponse;
        }
        elseif ($ssd == 1) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=SSD.txt');
            $newResponse->getBody()->write(LinkController::GetSSD($user, $mu));
            return $newResponse;
        }
        else {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=' . $token . '.txt');
            $newResponse->getBody()->write(LinkController::GetSub($user, $mu, $sub));
            return $newResponse;
        }

    }
    
    public static function GetSurge($user, $mu = 0, $surge = 0)
    {
        $userapiUrl = Config::get('subUrl') . LinkController::GenerateSSRSubCode($user->id, 0) . "?surge=" . $surge . "&mu=" . $mu;

        $proxy_name = "";
        $proxy_group = "";
        $items = URL::getAllItems($user, $mu, 1);
        foreach($items as $item) {
            if (in_array($surge, array(1, 3))) {
                $proxy_group .= $item['remark'] . " = ss, " . $item['address'] . ", " . $item['port'] . ", encrypt-method=" . $item['method'] . ", password=" . $item['passwd'] . URL::getSurgeObfs($item) . ", tfo=true, udp-relay=true\n";
            } else {
                $proxy_group .= $item['remark'] . " = custom, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", " . $item['passwd'] . ", https://raw.githubusercontent.com/lhie1/Rules/master/SSEncrypt.module" . URL::getSurgeObfs($item) . ", tfo=true\n";
            }
            $proxy_name .= ", ".$item['remark'];
        }

        if (in_array($surge, array(2, 3))) {
            $render = ConfRender::getTemplateRender();
            $render->assign('user', $user)
            ->assign('surge', $surge)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('proxy_name', $proxy_name)
            ->assign('proxy_group', $proxy_group);

            return $render->fetch('surge.tpl');
        } else {
            return $proxy_group;
        }
    }
    
    public static function GetQuantumult($user, $mu = 0, $quantumult = 0)
    {
        $proxys = [];
        $groups = [];
        $subUrl = "";

        if ($quantumult == 2) {
            $subUrl = Config::get('subUrl') . LinkController::GenerateSSRSubCode($user->id, 0);
        }
        else {
            $v2ray_group = "";
            $v2ray_name = "";
            $v2rays = URL::getAllVMessUrl($user, 1);
            foreach($v2rays as $v2ray) {
                $v2ray_name .= "\n" . $v2ray['ps'];
                $v2ray_tls = ", over-tls=false, certificate=1";
                if ($v2ray['tls'] == "tls"){
                    $v2ray_tls = ", over-tls=true, tls-host=" . $v2ray['add'] . ", certificate=1";
                }
                $v2ray_obfs = "";
                if ($v2ray['net'] == "ws" || $v2ray['net'] == "http"){
                    $v2ray_obfs = ", obfs=" . $v2ray['net'] . ", obfs-path=\"" . $v2ray['path'] . "\", obfs-header=\"Host: " . $v2ray['add'] . "[Rr][Nn]User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 18_0_0 like Mac OS X) AppleWebKit/888.8.88 (KHTML, like Gecko) Mobile/6666666\"";
                }
                if ($v2ray['net'] == "kcp"){
                    $v2ray_group .= "";
                } else {
                    if ($quantumult == 1) {
                        $v2ray_group .= "vmess://" . base64_encode($v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\", group=" . Config::get('appName') . "_v2" . $v2ray_tls . $v2ray_obfs) . "\n";
                    } else {
                        $v2ray_group .= $v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\", group=" . Config::get('appName') . "_v2" . $v2ray_tls . $v2ray_obfs . "\n";
                    }
                }
            }

            if ($quantumult == 1) {
                return base64_encode($v2ray_group);
            }
            elseif ($quantumult == 3) {
                $ss_group = "";
                $ss_name = "";
                $items = URL::getAllItems($user, $mu, 1);
                foreach($items as $item) {
                    $ss_group .= $item['remark'] . " = shadowsocks, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", upstream-proxy=false, upstream-proxy-auth=false" . URL::getSurgeObfs($item) . ", group=" . Config::get('appName') . "\n";
                    $ss_name .= "\n" . $item['remark'];
                }

                $ssr_group = "";
                $ssr_name = "";
                $ssrs = URL::getAllItems($user, $mu, 0);
                foreach($ssrs as $item) {
                    $ssr_group .= $item['remark'] . " = shadowsocksr, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", protocol=" . $item['protocol'] . ", protocol_param=" . $item['protocol_param'] . ", obfs=" . $item['obfs'] . ", obfs_param=\"" . $item['obfs_param'] . "\", group=" . Config::get('appName') . "\n";
                    $ssr_name .= "\n" . $item['remark'];
                }

                $quan_proxy_group = base64_encode("🍃 Proxy  :  static, 🏃 Auto\n🏃 Auto\n🚀 Direct\n" . $ss_name . $ssr_name . $v2ray_name);
                $quan_auto_group = base64_encode("🏃 Auto  :  auto\n" . $ss_name . $ssr_name . $v2ray_name);
                $quan_domestic_group = base64_encode("🍂 Domestic  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
                $quan_others_group = base64_encode("☁️ Others  :   static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
                $quan_apple_group = base64_encode("🍎 Only  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
                $quan_direct_group = base64_encode("🚀 Direct : static, DIRECT\nDIRECT");

                $proxys = [
                    "ss" => $ss_group,
                    "ssr" => $ssr_group,
                    "v2ray" => $v2ray_group,
                ];
                $groups = [
                    "proxy_group" => $quan_proxy_group,
                    "auto_group" => $quan_auto_group,
                    "domestic_group" => $quan_domestic_group,
                    "others_group" => $quan_others_group,
                    "direct_group" => $quan_direct_group,
                    "apple_group" => $quan_apple_group,
                ];
            }
            else {
                return "悟空别闹...";
            }
        }

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
        ->assign('mu', $mu)
        ->assign('subUrl', $subUrl)
        ->assign('proxys', $proxys)
        ->assign('groups', $groups)
        ->assign('quantumult', $quantumult)
        ->assign('appName', Config::get('appName'));

        return $render->fetch('quantumult.tpl');
    }

    public static function GetSurfboard($user, $mu = 0)
    {
        $userapiUrl = Config::get('subUrl') . LinkController::GenerateSSRSubCode($user->id, 0) . "?surfboard=1&mu=" . $mu;

        $ss_name="";
        $ss_group="";
        $items = URL::getAllItems($user, $mu, 1);
        foreach($items as $item) {
            $ss_group .= $item['remark'] . " = ss, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", " . $item['passwd'] . URL::getSurgeObfs($item) . "\n";
            $ss_name .= ", ".$item['remark'];
        }

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
        ->assign('userapiUrl', $userapiUrl)
        ->assign('ss_name', $ss_name)
        ->assign('ss_group', $ss_group);

        return $render->fetch('surfboard.tpl');
    }

    public static function GetClash($user, $mu = 0)
    {
        $userapiUrl = Config::get('subUrl') . LinkController::GenerateSSRSubCode($user->id, 0) . "?clash=1&mu=" . $mu;

        $render = ConfRender::getTemplateRender();
        $confs = URL::getClashInfo($user);

        $render->assign('user', $user)
        ->assign('userapiUrl', $userapiUrl)
        ->assign('confs', $confs)
        ->assign('proxies', array_map(function ($conf) {
                return $conf['name'];
            }, $confs));

        return $render->fetch('clash.tpl');
    }

    public static function GetSSD($user, $mu = 0)
    {
        return URL::getAllSSDUrl($user);
    }

    public static function GetSub($user, $mu = 0, $sub = 0)
    {
        // SSR
        if ($sub == 1) {
            return Tools::base64_url_encode(URL::getAllUrl($user, $mu, 0));
        }
        // SS
        elseif ($sub == 2) {
            return Tools::base64_url_encode(URL::getAllUrl($user, $mu, 1));
        }
        // V2
        elseif ($sub == 3) {
            return Tools::base64_url_encode(URL::getAllVMessUrl($user));
        }
        // V2 + SS
        elseif ($sub == 4) {
            $vmessall = URL::getAllVMessUrl($user);
            $ssall = URL::getAllUrl($user, $mu, 1);
            $SubAll = $ssall . $vmessall;

            return Tools::base64_url_encode($SubAll);
        }
        // V2 + SS + SSR
        elseif ($sub == 5) {
            $vmessall = URL::getAllVMessUrl($user);
            $ssrall = URL::getAllUrl($user, $mu, 0);
            $ssall = URL::getAllUrl($user, $mu, 1);
            $SubAll = $ssrall . $ssall . $vmessall;

            return Tools::base64_url_encode($SubAll);
        }
    }
}
