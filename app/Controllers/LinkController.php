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
		if($Elink->type!=11){
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

        $app = 0;
        if (isset($request->getQueryParams()['app'])) {
            $app = (int)$request->getQueryParams()['app'];
        }
        $quantumult = 0;
        if (isset($request->getQueryParams()["quantumult"])) {
            $quantumult = $request->getQueryParams()["quantumult"];
        }

        if (($quantumult == 1 || $quantumult == 2) && ($mu == 0 || $mu == 1)) {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=Quantumult.conf');
            $newResponse->getBody()->write(LinkController::GetQuantumult($user, $mu, $quantumult));
            return $newResponse;
        }
        else {
            $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=' . $token . '.txt');
            $newResponse->getBody()->write(LinkController::GetSSRSub(User::where("id", "=", $Elink->userid)->first(), $mu,$app));
        return $newResponse;
        }
    }
    public static function GetQuantumult($user, $mu = 0, $quantumult = 0)
    {
        $ss_group = "";
        $ss_name = "";
        $ssr_group = "";
        $ssr_name = "";
        $v2ray_group = "";
        $v2ray_name = "";

        $v2rays = URL::getAllV2ray($user);

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
                    $v2ray_group .= "vmess://" . base64_encode($v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\", group=" . Config::get('appName') ."_v2". "" . $v2ray_tls . $v2ray_obfs) . "\n";
                } else {
                    $v2ray_group .= $v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\"" .", group=" . Config::get('appName') ."_v2". $v2ray_tls . $v2ray_obfs . "\n";
                }
            }
        }

        if ($quantumult == 1) {

            return base64_encode($v2ray_group);

        } else {

            $items = URL::getAllItems($user, $mu, 1);

            foreach($items as $item) {

                $ss_group .= $item['remark'] . " = shadowsocks, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", upstream-proxy=false, upstream-proxy-auth=false" . URL::getSurgeObfs($item) . ",group=".Config::get('appName')."\n";

                $ss_name .= "\n" . $item['remark'];
            }

            $ssrs = URL::getAllItems($user, $mu, 0);

            foreach($ssrs as $item) {

                $ssr_group .= $item['remark'] . " = shadowsocksr, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", protocol=" . $item['protocol'] . ", protocol_param=" . $item['protocol_param'] . ", obfs=" . $item['obfs'] . ", obfs_param=\"" . $item['obfs_param'] . "\"".",group=".Config::get('appName')."\n";

                $ssr_name .= "\n" . $item['remark'];
            }

            $quan_proxy_group = base64_encode("🍃 Proxy  :  static, 🏃 Auto\n🏃 Auto\n🚀 Direct\n" . $ss_name . $ssr_name . $v2ray_name);
            $quan_auto_group = base64_encode("🏃 Auto  :  auto\n" . $ss_name . $ssr_name . $v2ray_name);
            $quan_domestic_group = base64_encode("🍂 Domestic  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
            $quan_others_group = base64_encode("☁️ Others  :   static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
            $quan_direct_group = base64_encode("🚀 Direct : static, DIRECT\nDIRECT");
            $quan_apple_group = base64_encode("🍎 Only  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");

            $render = ConfRender::getTemplateRender();
            $render->assign('user', $user)
                ->assign('ss_group', $ss_group)
                ->assign('ssr_group', $ssr_group)
                ->assign('v2ray_group', $v2ray_group)
                ->assign('quan_proxy_group', $quan_proxy_group)
                ->assign('quan_auto_group', $quan_auto_group)
                ->assign('quan_domestic_group', $quan_domestic_group)
                ->assign('quan_others_group', $quan_others_group)
                ->assign('quan_direct_group', $quan_direct_group)
                ->assign('quan_apple_group', $quan_apple_group);

            return $render->fetch('quantumult.tpl');
        }
    }

    public static function GetPcConf($user, $is_mu = 0, $is_ss = 0)
    {
        if ($is_ss == 0) {
            $string = '
            {
                "index" : 0,
                "random" : false,
                "sysProxyMode" : 0,
                "shareOverLan" : false,
                "bypassWhiteList" : false,
                "localPort" : 1080,
                "localAuthPassword" : "' . Tools::genRandomChar(26) . '",
                "dns_server" : "",
                "reconnectTimes" : 4,
                "randomAlgorithm" : 0,
                "TTL" : 60,
                "connect_timeout" : 5,
                "proxyRuleMode" : 1,
                "proxyEnable" : false,
                "pacDirectGoProxy" : false,
                "proxyType" : 0,
                "proxyHost" : "",
                "proxyPort" : 0,
                "proxyAuthUser" : "",
                "proxyAuthPass" : "",
                "proxyUserAgent" : "",
                "authUser" : "",
                "authPass" : "",
                "autoBan" : false,
                "sameHostForSameTarget" : true,
                "keepVisitTime" : 180,
                "isHideTips" : true,
                "token" : {

                },
                "portMap" : {

                }
            }
        ';
        } else {
            $string = '
            {
                "strategy": null,
                "index": 6,
                "global": false,
                "enabled": false,
                "shareOverLan": false,
                "isDefault": false,
                "localPort": 1080,
                "pacUrl": null,
                "useOnlinePac": false,
                "secureLocalPac": true,
                "availabilityStatistics": false,
                "autoCheckUpdate": false,
                "checkPreRelease": false,
                "isVerboseLogging": true,
                "logViewer": {
                "topMost": false,
                "wrapText": false,
                "toolbarShown": false,
                "Font": "Consolas, 8pt",
                "BackgroundColor": "Black",
                "TextColor": "White"
                },
                "proxy": {
                "useProxy": false,
                "proxyType": 0,
                "proxyServer": "",
                "proxyPort": 0,
                "proxyTimeout": 3
                },
                "hotkey": {
                "SwitchSystemProxy": "",
                "SwitchSystemProxyMode": "",
                "SwitchAllowLan": "",
                "ShowLogs": "",
                "ServerMoveUp": "",
                "ServerMoveDown": ""
                }
            }
        ';
        }

        $json = json_decode($string, true);
        $temparray = array();

        $items = URL::getAllItems($user, $is_mu, $is_ss);
        foreach ($items as $item) {
            if ($is_ss == 0) {
                array_push($temparray, array("remarks" => $item['remark'],
                    "server" => $item['address'],
                    "server_port" => $item['port'],
                    "method" => $item['method'],
                    "obfs" => $item['obfs'],
                    "obfsparam" => $item['obfs_param'],
                    "remarks_base64" => base64_encode($item['remark']),
                    "password" => $item['passwd'],
                    "tcp_over_udp" => false,
                    "udp_over_tcp" => false,
                    "group" => $item['group'],
                    "protocol" => $item['protocol'],
                    "protoparam" => $item['protocol_param'],
                    "protocolparam" => $item['protocol_param'],
                    "obfs_udp" => false,
                    "enable" => true));
            } else {
                array_push($temparray, array("server" => $item['address'],
                    "server_port" => $item['port'],
                    "password" => $item['passwd'],
                    "method" => $item['method'],
                    "plugin" => "obfs-local",
                    "plugin_opts" => str_replace(',', ';', URL::getSurgeObfs($item)),
                    "remarks" => $item['remark'],
                    "timeout" => 5));
            }
        }

        $json["configs"] = $temparray;
        return json_encode($json, JSON_PRETTY_PRINT);
    }

    const V2RYA_MU = 2;
    const SSD_MU = 3;
    const CLASH_MU = 4;
    const OTHERS = 5;
    const APP_SHADOWROCKET = 1;
    const APP_KITSUNEBI = 2;

    public static function GetSSRSub($user, $mu = 0, $app = 0)
    {
        if ($app == LinkController::APP_SHADOWROCKET or $app == LinkController::APP_KITSUNEBI) {
            $vmessall = URL::getAllVMessUrl($user);
            if ($app==LinkController::APP_SHADOWROCKET) {
                $ssrall = URL::getAllUrl($user, $mu, 0, 1);
            }else{
                $ssrall = URL::getAllUrl($user, $mu, 1, 1);
            }
            $all = $ssrall.$vmessall;
            return Tools::base64_url_encode($all);
        } elseif ($mu == 0 || $mu == 1) {
            return Tools::base64_url_encode(URL::getAllUrl($user, $mu, 0, 1));
        } elseif ($mu == LinkController::V2RYA_MU) {
            return Tools::base64_url_encode(URL::getAllVMessUrl($user));
        } elseif ($mu == LinkController::SSD_MU) {
            return URL::getAllSSDUrl($user);
        } elseif ($mu == LinkController::CLASH_MU) {
            // Clash
            $render = ConfRender::getTemplateRender();
            $confs = URL::getClashInfo($user);

            $render->assign('user', $user)->assign('confs', $confs)->assign('proxies', array_map(function ($conf) {
                return $conf['name'];
            }, $confs));

            return $render->fetch('clash.tpl');
        }
    }
}
