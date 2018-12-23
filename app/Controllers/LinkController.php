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

    public static function GenerateAclCode($address, $port, $userid, $geo, $method)
    {
        $Elink = Link::where("type", "=", 9)->where("address", "=", $address)->where("port", "=", $port)->where("userid", "=", $userid)->where("geo", "=", $geo)->where("method", "=", $method)->first();
        if ($Elink != null) {
            return $Elink->token;
        }
        $NLink = new Link();
        $NLink->type = 9;
        $NLink->address = $address;
        $NLink->port = $port;
        $NLink->ios = 0;
        $NLink->geo = $geo;
        $NLink->method = $method;
        $NLink->userid = $userid;
        $NLink->token = LinkController::GenerateRandomLink();
        $NLink->save();

        return $NLink->token;
    }

    public static function GenerateRouterCode($userid, $without_mu)
    {
        $Elink = Link::where("type", "=", 10)->where("userid", "=", $userid)->where("geo", $without_mu)->first();
        if ($Elink != null) {
            return $Elink->token;
        }
        $NLink = new Link();
        $NLink->type = 10;
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

        switch ($Elink->type) {
            case 9:
                $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=' . $token . '.acl');//->getBody()->write($builder->output());
                $newResponse->getBody()->write(LinkController::GetAcl(User::where("id", "=", $Elink->userid)->first()));
                return $newResponse;
            case 10:
                $user = User::where("id", $Elink->userid)->first();
                if ($user == null) {
                    return null;
                }

                $is_ss = 0;
                if (isset($request->getQueryParams()["is_ss"])) {
                    $is_ss = $request->getQueryParams()["is_ss"];
                }

                $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=' . $token . '.sh');//->getBody()->write($builder->output());
                $newResponse->getBody()->write(LinkController::GetRouter(User::where("id", "=", $Elink->userid)->first(), $Elink->geo, $is_ss));
                return $newResponse;
            case 11:
                $user = User::where("id", $Elink->userid)->first();
                if ($user == null) {
                    return null;
                }

                $max = 0;
                if (isset($request->getQueryParams()["max"])) {
                    $max = (int)$request->getQueryParams()["max"];
                }

                $mu = 0;
                if (isset($request->getQueryParams()["mu"])) {
                    $mu = (int)$request->getQueryParams()["mu"];
                }

                $sub = 0;
                if (isset($request->getQueryParams()["sub"])) {
                    $sub = $request->getQueryParams()["sub"];
                }

                $ssd = 0;
                if (isset($request->getQueryParams()["ssd"])) {
                    $ssd = $request->getQueryParams()["ssd"];
                }

                $clash = 0;
                if (isset($request->getQueryParams()["clash"])) {
                    $clash = $request->getQueryParams()["clash"];
                }

                $surge = 0;
                if (isset($request->getQueryParams()["surge"])) {
                    $surge = $request->getQueryParams()["surge"];
                }

                $quantumult = 0;
                if (isset($request->getQueryParams()["quantumult"])) {
                    $quantumult = $request->getQueryParams()["quantumult"];
                }

                $surfboard = 0;
                if (isset($request->getQueryParams()["surfboard"])) {
                    $surfboard = $request->getQueryParams()["surfboard"];
                }

                if (in_array($quantumult, array(1, 2))) {
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
                    $newResponse = $response->withHeader('Content-type', ' application/octet-stream; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')->withHeader('Content-Disposition', ' attachment; filename=Clash.conf');
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
                    $newResponse->getBody()->write(LinkController::GetSub($user, $mu, $max, $sub));
                return $newResponse;
                }
            default:
                break;
        }
        $newResponse = $response->withHeader('Content-type', ' application/x-ns-proxy-autoconfig; charset=utf-8')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate');//->getBody()->write($builder->output());
        $newResponse->getBody()->write(LinkController::GetPac($type, $Elink->address, $Elink->port, User::where("id", "=", $Elink->userid)->first()->pac));
        return $newResponse;
    }
    
    public static function GetSurge($user, $mu = 0, $surge = 0)
    {

        $proxy_name="";
        $proxy_group="";

        $userapiUrl = Config::get('baseUrl') . "/link/" . LinkController::GenerateSSRSubCode($user->id, 0) . "?surge=" . $surge . "&mu=" . $mu;

        $items = URL::getAllItems($user, $mu, 1);
        foreach($items as $item) {

            if (in_array($surge, array(1, 3))) {
                $proxy_group .= $item['remark'] . ' = ss, ' . $item['address'] . ', ' . $item['port'] . ', encrypt-method=' . $item['method'] . ', password=' . $item['passwd'] . '' . URL::getSurgeObfs($item) . ", tfo=true, udp-relay=true\n";

            } else {
                $proxy_group .= $item['remark'] . ' = custom, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', ' . $item['passwd'] . ', ' . Config::get('baseUrl') . '/downloads/SSEncrypt.module' . URL::getSurgeObfs($item) . ", tfo=true, udp-relay=true\n";
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
                    $v2ray_group .= "vmess://" . base64_encode($v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\", group=" . Config::get('appName') . "_v2" . $v2ray_tls . $v2ray_obfs) . "\n";
                } else {
                    $v2ray_group .= $v2ray['ps'] . " = vmess, " . $v2ray['add'] . ", " . $v2ray['port'] . ", chacha20-ietf-poly1305, \"" . $v2ray['id'] . "\", group=" . Config::get('appName') . "_v2" . $v2ray_tls . $v2ray_obfs . "\n";
                }
            }
        }

        if ($quantumult == 1) {
        
            return base64_encode($v2ray_group);
        
        } else {

            $items = URL::getAllItems($user, $mu, 1);

            foreach($items as $item) {

                $ss_group .= $item['remark'] . " = shadowsocks, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", upstream-proxy=false, upstream-proxy-auth=false" . URL::getSurgeObfs($item) . ", group=" . Config::get('appName') . "\n";

                $ss_name .= "\n" . $item['remark'];
            }

            $ssrs = URL::getAllItems($user, $mu, 0);

            foreach($ssrs as $item) {

                $ssr_group .= $item['remark'] . " = shadowsocksr, " . $item['address'] . ", " . $item['port'] . ", " . $item['method'] . ", \"" . $item['passwd'] . "\", protocol=" . $item['protocol'] . ", protocol_param=" . $item['protocol_param'] . ", obfs=" . $item['obfs'] . ", obfs_param=\"" . $item['obfs_param'] . "\", group=" . Config::get('appName') . "\n";

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

    public static function GetSurfboard($user, $mu = 0)
    {
        $userapiUrl = Config::get('baseUrl') . "/link/" . LinkController::GenerateSSRSubCode($user->id, 0) . "?surfboard=1&mu=" . $mu;

        $ss_name="";
        $ss_group="";

        $items = URL::getAllItems($user, $mu, 1);
        foreach($items as $item) {
            $ss_group .= $item['remark'].' = custom, '.$item['address'].', '.$item['port'].', '.$item['method'].', '.$item['passwd'].', '.Config::get('baseUrl').'/downloads/SSEncrypt.module'.URL::getSurgeObfs($item).", tfo=true, udp-relay=true\n";
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
        $render = ConfRender::getTemplateRender();
        $confs = URL::getClashInfo($user);

        $render->assign('user', $user)
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

    private static function GetPac($type, $address, $port, $defined)
    {
        header('Content-type: application/x-ns-proxy-autoconfig; charset=utf-8');
        return LinkController::get_pac($type, $address, $port, true, $defined);
    }

    private static function GetAcl($user)
    {
        $rulelist = base64_decode(file_get_contents("https://raw.githubusercontent.com/gfwlist/gfwlist/master/gfwlist.txt")) . "\n" . $user->pac;
        $gfwlist = explode("\n", $rulelist);

        $count = 0;
        $acl_content = '';
        $find_function_content = '
#Generated by sspanel-glzjin-mod v3
#Time:' . date('Y-m-d H:i:s') . '

[bypass_all]

';

        $proxy_list = '[proxy_list]

';
        $bypass_list = '[bypass_list]

';
        $outbound_block_list = '[outbound_block_list]

';

        $isget = array();
        foreach ($gfwlist as $index => $rule) {
            if (empty($rule)) {
                continue;
            } elseif (substr($rule, 0, 1) == '!' || substr($rule, 0, 1) == '[') {
                continue;
            }

            if (substr($rule, 0, 2) == '@@') {
                // ||开头表示前面还有路径
                if (substr($rule, 2, 2) == '||') {
                    //$rule_reg = preg_match("/^((http|https):\/\/)?([^\/]+)/i",substr($rule, 2), $matches);
                    $host = substr($rule, 4);
                    //preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
                    if (isset($isget[$host])) {
                        continue;
                    }
                    $isget[$host] = 1;
                    //$find_function_content.="DOMAIN,".$host.",DIRECT,force-remote-dns\n";
                    $bypass_list .= $host . "\n";
                    continue;
                    // !开头相当于正则表达式^
                } elseif (substr($rule, 2, 1) == '|') {
                    preg_match("/(\d{1,3}\.){3}\d{1,3}/", substr($rule, 3), $matches);
                    if (!isset($matches[0])) {
                        continue;
                    }

                    $host = $matches[0];
                    if ($host != "") {
                        if (isset($isget[$host])) {
                            continue;
                        }
                        $isget[$host] = 1;
                        //$find_function_content.="IP-CIDR,".$host."/32,DIRECT,no-resolve \n";
                        $bypass_list .= $host . "/32\n";
                        continue;
                    } else {
                        preg_match_all("~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?~i", substr($rule, 3), $matches);

                        if (!isset($matches[4][0])) {
                            continue;
                        }

                        $host = $matches[4][0];
                        if ($host != "") {
                            if (isset($isget[$host])) {
                                continue;
                            }
                            $isget[$host] = 1;
                            //$find_function_content.="DOMAIN-SUFFIX,".$host.",DIRECT,force-remote-dns\n";
                            $bypass_list .= $host . "\n";
                            continue;
                        }
                    }
                } elseif (substr($rule, 2, 1) == '.') {
                    $host = substr($rule, 3);
                    if ($host != "") {
                        if (isset($isget[$host])) {
                            continue;
                        }
                        $isget[$host] = 1;
                        //$find_function_content.="DOMAIN-SUFFIX,".$host.",DIRECT,force-remote-dns \n";
                        $bypass_list .= $host . "\n";
                        continue;
                    }
                }
            }

            // ||开头表示前面还有路径
            if (substr($rule, 0, 2) == '||') {
                //$rule_reg = preg_match("/^((http|https):\/\/)?([^\/]+)/i",substr($rule, 2), $matches);
                $host = substr($rule, 2);
                //preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);

                if (strpos($host, "*") !== false) {
                    $host = substr($host, strpos($host, "*") + 1);
                    if (strpos($host, ".") !== false) {
                        $host = substr($host, strpos($host, ".") + 1);
                    }
                    if (isset($isget[$host])) {
                        continue;
                    }
                    $isget[$host] = 1;
                    //$find_function_content.="DOMAIN-KEYWORD,".$host.",Proxy,force-remote-dns\n";
                    $proxy_list .= $host . "\n";
                    continue;
                }

                if (isset($isget[$host])) {
                    continue;
                }
                $isget[$host] = 1;
                //$find_function_content.="DOMAIN,".$host.",Proxy,force-remote-dns\n";
                $proxy_list .= $host . "\n";
                // !开头相当于正则表达式^
            } elseif (substr($rule, 0, 1) == '|') {
                preg_match("/(\d{1,3}\.){3}\d{1,3}/", substr($rule, 1), $matches);

                if (!isset($matches[0])) {
                    continue;
                }

                $host = $matches[0];
                if ($host != "") {
                    if (isset($isget[$host])) {
                        continue;
                    }
                    $isget[$host] = 1;

                    preg_match("/(\d{1,3}\.){3}\d{1,3}\/\d{1,2}/", substr($rule, 1), $matches_ips);

                    if (!isset($matches_ips[0])) {
                        $proxy_list .= $host . "/32\n";
                    } else {
                        $host = $matches_ips[0];
                        $proxy_list .= $host . "\n";
                    }

                    //$find_function_content.="IP-CIDR,".$host."/32,Proxy,no-resolve \n";

                    continue;
                } else {
                    preg_match_all("~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?~i", substr($rule, 1), $matches);

                    if (!isset($matches[4][0])) {
                        continue;
                    }

                    $host = $matches[4][0];
                    if (strpos($host, "*") !== false) {
                        $host = substr($host, strpos($host, "*") + 1);
                        if (strpos($host, ".") !== false) {
                            $host = substr($host, strpos($host, ".") + 1);
                        }
                        if (isset($isget[$host])) {
                            continue;
                        }
                        $isget[$host] = 1;
                        //$find_function_content.="DOMAIN-KEYWORD,".$host.",Proxy,force-remote-dns\n";
                        $proxy_list .= $host . "\n";
                        continue;
                    }

                    if ($host != "") {
                        if (isset($isget[$host])) {
                            continue;
                        }
                        $isget[$host] = 1;
                        //$find_function_content.="DOMAIN-SUFFIX,".$host.",Proxy,force-remote-dns\n";
                        $proxy_list .= $host . "\n";
                        continue;
                    }
                }
            } else {
                $host = substr($rule, 0);
                if (strpos($host, "/") !== false) {
                    $host = substr($host, 0, strpos($host, "/"));
                }

                if ($host != "") {
                    if (isset($isget[$host])) {
                        continue;
                    }
                    $isget[$host] = 1;
                    //$find_function_content.="DOMAIN-KEYWORD,".$host.",PROXY,force-remote-dns \n";
                    $proxy_list .= $host . "\n";
                    continue;
                }
            }


            $count = $count + 1;
        }

        $acl_content .= $find_function_content . "\n" . $proxy_list . "\n" . $bypass_list . "\n" . $outbound_block_list;
        return $acl_content;
    }

    /**
     * This is a php implementation of autoproxy2pac
     */
    private static function reg_encode($str)
    {
        $tmp_str = $str;
        $tmp_str = str_replace('/', "\\/", $tmp_str);
        $tmp_str = str_replace('.', "\\.", $tmp_str);
        $tmp_str = str_replace(':', "\\:", $tmp_str);
        $tmp_str = str_replace('%', "\\%", $tmp_str);
        $tmp_str = str_replace('*', ".*", $tmp_str);
        $tmp_str = str_replace('-', "\\-", $tmp_str);
        $tmp_str = str_replace('&', "\\&", $tmp_str);
        $tmp_str = str_replace('?', "\\?", $tmp_str);
        $tmp_str = str_replace('+', "\\+", $tmp_str);

        return $tmp_str;
    }

    private static function get_pac($proxy_type, $proxy_host, $proxy_port, $proxy_google, $defined)
    {
        $rulelist = base64_decode(file_get_contents("https://raw.githubusercontent.com/gfwlist/gfwlist/master/gfwlist.txt")) . "\n" . $defined;
        $gfwlist = explode("\n", $rulelist);
        if ($proxy_google == "true") {
            $gfwlist[] = ".google.com";
        }

        $count = 0;
        $pac_content = '';
        $find_function_content = 'function FindProxyForURL(url, host) { var PROXY = "' . $proxy_type . ' ' . $proxy_host . ':' . $proxy_port . '; DIRECT"; var DEFAULT = "DIRECT";' . "\n";
        foreach ($gfwlist as $index => $rule) {
            if (empty($rule)) {
                continue;
            } elseif (substr($rule, 0, 1) == '!' || substr($rule, 0, 1) == '[') {
                continue;
            }
            $return_proxy = 'PROXY';
            // @@开头表示默认是直接访问
            if (substr($rule, 0, 2) == '@@') {
                $rule = substr($rule, 2);
                $return_proxy = "DEFAULT";
            }

            // ||开头表示前面还有路径
            if (substr($rule, 0, 2) == '||') {
                $rule_reg = "^[\\w\\-]+:\\/+(?!\\/)(?:[^\\/]+\\.)?" . LinkController::reg_encode(substr($rule, 2));
                // !开头相当于正则表达式^
            } elseif (substr($rule, 0, 1) == '|') {
                $rule_reg = "^" . LinkController::reg_encode(substr($rule, 1));
                // 前后匹配的/表示精确匹配
            } elseif (substr($rule, 0, 1) == '/' && substr($rule, -1) == '/') {
                $rule_reg = substr($rule, 1, strlen($rule) - 2);
            } else {
                $rule_reg = LinkController::reg_encode($rule);
            }
            // 以|结尾，替换为$结尾
            if (preg_match("/\|$/i", $rule_reg)) {
                $rule_reg = substr($rule_reg, 0, strlen($rule_reg) - 1) . "$";
            }
            $find_function_content .= 'if (/' . $rule_reg . '/i.test(url)) return ' . $return_proxy . ';' . "\n";
            $count = $count + 1;
        }
        $find_function_content .= 'return DEFAULT;' . "}";
        $pac_content .= $find_function_content;
        return $pac_content;
    }

    public static function GetRouter($user, $is_mu = 0, $is_ss = 0)
    {
        $bash = '#!/bin/sh' . "\n";
        $bash .= 'export PATH=\'/opt/usr/sbin:/opt/usr/bin:/opt/sbin:/opt/bin:/usr/local/sbin:/usr/sbin:/usr/bin:/sbin:/bin\'' . "\n";
        $bash .= 'export LD_LIBRARY_PATH=/lib:/opt/lib' . "\n";
        $bash .= 'nvram set ss_type=' . ($is_ss == 1 ? '0' : '1') . "\n";

        $count = 0;

        $items = URL::getAllItems($user, $is_mu, $is_ss);
        foreach ($items as $item) {
            if ($is_ss == 0) {
                $bash .= 'nvram set rt_ss_name_x' . $count . '="' . $item['remark'] . "\"\n";
                $bash .= 'nvram set rt_ss_port_x' . $count . '=' . $item['port'] . "\n";
                $bash .= 'nvram set rt_ss_password_x' . $count . '="' . $item['passwd'] . "\"\n";
                $bash .= 'nvram set rt_ss_server_x' . $count . '=' . $item['address'] . "\n";
                $bash .= 'nvram set rt_ss_usage_x' . $count . '="' . "-o " . $item['obfs'] . " -g " . $item['obfs_param'] . " -O " . $item['protocol'] . " -G " . $item['protocol_param'] . "\"\n";
                $bash .= 'nvram set rt_ss_method_x' . $count . '=' . $item['method'] . "\n";
                $count += 1;
            } else {
                $bash .= 'nvram set rt_ss_name_x' . $count . '="' . $item['remark'] . "\"\n";
                $bash .= 'nvram set rt_ss_port_x' . $count . '=' . $item['port'] . "\n";
                $bash .= 'nvram set rt_ss_password_x' . $count . '="' . $item['passwd'] . "\"\n";
                $bash .= 'nvram set rt_ss_server_x' . $count . '=' . $item['address'] . "\n";
                $bash .= 'nvram set rt_ss_usage_x' . $count . '=""' . "\n";
                $bash .= 'nvram set rt_ss_method_x' . $count . '=' . $item['method'] . "\n";
                $count += 1;
            }
        }

        $bash .= "nvram set rt_ssnum_x=" . $count . "\n";

        return $bash;
    }

    public static function GetSub($user, $mu = 0, $max = 0, $sub = 0)
    {
        // SSR
        if ($sub == 1) {

            return Tools::base64_url_encode(URL::getAllUrl($user, $mu, 0, 1));

        }
        // SS
        elseif ($sub == 2) {

            return Tools::base64_url_encode(URL::getAllUrl($user, $mu, 1, 1));

        }
        // V2
        elseif ($sub == 3) {

            return Tools::base64_url_encode(URL::getAllVMessUrl($user));

        }
        // V2 + SS
        elseif ($sub == 4) {

            $vmessall = URL::getAllVMessUrl($user);
            $ssall = URL::getAllUrl($user, $mu, 1, 1);
            $SubAll = $ssall . $vmessall;

            return Tools::base64_url_encode($SubAll);
        }
        // V2 + SS + SSR
        elseif ($sub == 5) {

            $vmessall = URL::getAllVMessUrl($user);
            $ssrall = URL::getAllUrl($user, $mu, 0, 1);
            $ssall = URL::getAllUrl($user, $mu, 1, 1);
            $SubAll = $ssrall . $ssall . $vmessall;

            return Tools::base64_url_encode($SubAll);
        }

    }
}
