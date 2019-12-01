<?php

//Thanks to http://blog.csdn.net/jollyjumper/article/details/9823047

namespace App\Controllers;

use App\Models\Link;
use App\Models\User;
use App\Models\UserSubscribeLog;
use App\Models\Smartline;
use App\Utils\ConfRender;
use App\Utils\Tools;
use App\Utils\URL;
use App\Services\Config;
use App\Services\AppsProfiles;

/**
 *  HomeController
 */
class LinkController extends BaseController
{
    public static function GenerateRandomLink()
    {
        for ($i = 0; $i < 10; $i++) {
            $token = Tools::genRandomChar(16);
            $Elink = Link::where('token', '=', $token)->first();
            if ($Elink == null) {
                return $token;
            }
        }

        return "couldn't alloc token";
    }

    public static function GenerateSSRSubCode($userid, $without_mu)
    {
        $Elink = Link::where('type', '=', 11)->where('userid', '=', $userid)->where('geo', $without_mu)->first();
        if ($Elink != null) {
            return $Elink->token;
        }
        $NLink = new Link();
        $NLink->type = 11;
        $NLink->address = '';
        $NLink->port = 0;
        $NLink->ios = 0;
        $NLink->geo = $without_mu;
        $NLink->method = '';
        $NLink->userid = $userid;
        $NLink->token = self::GenerateRandomLink();
        $NLink->save();

        return $NLink->token;
    }

    public static function GetContent($request, $response, $args)
    {
        $token = $args['token'];

        //$builder->getPhrase();
        $Elink = Link::where('token', '=', $token)->first();
        if ($Elink == null) {
            return null;
        }

        if ($Elink->type != 11) {
            return null;
        }

        $user = User::where('id', $Elink->userid)->first();
        if ($user == null) {
            return null;
        }

        $opts = $request->getQueryParams();

        // 筛选节点部分
        $find = false;
        $Rule = [];
        if (isset($opts['class'])) {
            $Rule['content']['class'] = (int) urldecode(trim($opts['class']));
            $find = true;
        }
        if (isset($opts['noclass'])) {
            $Rule['content']['noclass'] = (int) urldecode(trim($opts['noclass']));
            $find = true;
        }
        if (isset($opts['regex'])) {
            $Rule['content']['regex'] = urldecode(trim($opts['regex']));
            $find = true;
        }

        $emoji = ((isset($opts['emoji']) && $opts['emoji'] == '1') || Config::get('add_emoji_to_node_name') == 'true'
            ? 1
            : 0);

        // 兼容原版
        if (isset($opts['mu'])) {
            $mu = (int) $opts['mu'];
            switch ($mu) {
                case 0:
                    $opts['sub'] = 1;
                    break;
                case 1:
                    $opts['sub'] = 1;
                    break;
                case 2:
                    $opts['sub'] = 3;
                    break;
                case 3:
                    $opts['ssd'] = 1;
                    break;
                case 4:
                    $opts['clash'] = 1;
                    break;
            }
        }

        $sub_type_array = [
            'clash' => ['filename' => 'config.yaml', 'class' => 'Clash'],
            'kitsunebi' => ['filename' => 'Kitsunebi.txt', 'class' => 'Kitsunebi'],
            'ssd' => ['filename' => 'SSD.txt', 'class' => 'SSD'],
            'surge' => ['filename' => 'Surge.conf', 'class' => 'Surge'],
            'surfboard' => ['filename' => 'Surfboard.conf', 'class' => 'Surfboard'],
            'shadowrocket' => ['filename' => 'Shadowrocket.txt', 'class' => 'Shadowrocket'],
            'quantumult' => ['filename' => 'Quantumult.conf', 'class' => 'Quantumult'],
            'sub' => ['filename' => 'node.txt', 'class' => 'Sub']
        ];

        // 订阅类型
        $subscribe_type = '';
        $sub_int_type = [
            1 => 'SSR',
            2 => 'SS',
            3 => 'V2Ray',
            4 => 'V2Ray + SS',
            5 => 'V2Ray + SS + SSR'
        ];

        $getBody = '';
        foreach ($sub_type_array as $key => $value) {
            if ($key != 'sub' && isset($opts[$key])) {
                $int = (int) $opts[$key];
                $class = ('get' . $value['class']);
                if ($int >= 1) {
                    $getBody = self::getBody(
                        $user,
                        $response,
                        self::$class($user, $int, $opts, $Rule, $find, $emoji),
                        $value['filename']
                    );
                    $subscribe_type = $value['class'];
                    break;
                }
                continue;
            }
            if ($key != 'sub') {
                continue;
            }
            $int = (!isset($opts[$key])
                ? 1
                : (int) $opts[$key]);
            if ($int == 0 || $int >= 6) {
                $int = 1;
            }
            $subscribe_type = $sub_int_type[$int];
            $getBody = self::getBody(
                $user,
                $response,
                self::getSub($user, $int, $opts, $Rule, $find, $emoji),
                $value['filename']
            );
        }

        // 记录订阅日志
        if (Config::get('subscribeLog') == 'true') {
            self::Subscribe_log($user, $subscribe_type, $request->getHeaderLine('User-Agent'));
        }

        return $getBody;
    }

    /**
     * 记录订阅日志
     *
     * @param object $user 用户
     * @param string $type 订阅类型
     * @param string $ua   UA
     *
     */
    private static function Subscribe_log($user, $type, $ua)
    {
        $log = new UserSubscribeLog();

        $log->user_name = $user->user_name;
        $log->user_id = $user->id;
        $log->email = $user->email;
        $log->subscribe_type = $type;
        $log->request_ip = $_SERVER['REMOTE_ADDR'];
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_user_agent = $ua;
        $log->save();
    }

    /**
     * 响应内容
     *
     * @param object $user     用户
     * @param array  $response 响应体
     * @param string $content  订阅内容
     * @param string $filename 文件名
     *
     * @return string
     */
    public static function getBody($user, $response, $content, $filename)
    {
        $newResponse = $response
            ->withHeader(
                'Content-type',
                ' application/octet-stream; charset=utf-8'
            )
            ->withHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate'
            )
            ->withHeader(
                'Content-Disposition',
                ' attachment; filename=' . $filename
            )
            ->withHeader(
                'Subscription-Userinfo',
                (' upload=' . $user->u
                    . '; download=' . $user->d
                    . '; total=' . $user->transfer_enable
                    . '; expire=' . strtotime($user->class_expire))
            );
        $newResponse->getBody()->write($content);

        return $newResponse;
    }

    /**
     * 订阅链接汇总
     *
     * @param object $user 用户
     * @param int    $int  当前用户访问的订阅类型
     *
     * @return string
     */
    public static function getSubinfo($user, $int = 0)
    {
        if ($int == 0) {
            $int = '';
        }
        $userapiUrl = Config::get('subUrl') . self::GenerateSSRSubCode($user->id, 0);
        $return_info = [
            'link' => $userapiUrl,
            // sub
            'ss' => $userapiUrl . '?sub=2',
            'ssr' => $userapiUrl . '?sub=1',
            'v2ray' => $userapiUrl . '?sub=3',
            'v2ray_ss' => $userapiUrl . '?sub=4',
            'v2ray_ss_ssr' => $userapiUrl . '?sub=5',
            // apps
            'ssd' => $userapiUrl . '?ssd=1',
            'clash' => $userapiUrl . '?clash=1',
            'clashr' => $userapiUrl . '?clash=2',
            'surge' => $userapiUrl . '?surge=' . $int,
            'surge_node' => $userapiUrl . '?surge=1',
            'surge2' => $userapiUrl . '?surge=2',
            'surge3' => $userapiUrl . '?surge=3',
            'surge4' => $userapiUrl . '?surge=4',
            'surfboard' => $userapiUrl . '?surfboard=1',
            'quantumult' => $userapiUrl . '?quantumult=' . $int,
            'quantumult_v2' => $userapiUrl . '?quantumult=1',
            'quantumult_sub' => $userapiUrl . '?quantumult=2',
            'quantumult_conf' => $userapiUrl . '?quantumult=3',
            'shadowrocket' => $userapiUrl . '?shadowrocket=1',
            'kitsunebi' => $userapiUrl . '?kitsunebi=1'
        ];
        return $return_info;
    }

    /**
     * Surge 配置
     *
     * @param object $user  用户
     * @param int    $surge 订阅类型
     * @param array  $opts  request
     * @param array  $Rule  节点筛选规则
     * @param bool   $find  是否筛选节点
     *
     * @return string
     */
    public static function getSurge($user, $surge, $opts, $Rule, $find, $emoji)
    {
        $subInfo = self::getSubinfo($user, $surge);
        $userapiUrl = $subInfo['surge'];
        $source = (isset($opts['source']) && $opts['source'] != ''
            ? true
            : false);
        $All_Proxy = '';
        $items = array_merge(
            URL::getAllItems($user, 0, 1, $emoji),
            URL::getAllItems($user, 1, 1, $emoji)
        );
        if (!$source && $surge == 1) {
            foreach ($items as $item) {
                if ($find) {
                    $item = ConfController::getMatchProxy($item, $Rule);
                    if ($item !== null) {
                        $All_Proxy .= ($item['remark'] . ' = ss, ' . $item['address'] . ', ' . $item['port'] . ', encrypt-method=' . $item['method'] . ', password=' . $item['passwd'] . URL::getSurgeObfs($item) . ', udp-relay=true' . PHP_EOL);
                    }
                } else {
                    $All_Proxy .= ($item['remark'] . ' = ss, ' . $item['address'] . ', ' . $item['port'] . ', encrypt-method=' . $item['method'] . ', password=' . $item['passwd'] . URL::getSurgeObfs($item) . ', udp-relay=true' . PHP_EOL);
                }
            }

            // v2
            $v2_items = URL::getAllVMessUrl($user, 1, $emoji);
            foreach ($v2_items as $item) {
                if (!in_array($item['net'], ['ws', 'tcp','kcp','quic','h2'])) {
                    continue;
                }
                $item['remark'] = $item['ps'];
                $tls = ($item['tls'] == 'tls'
                    ? ', tls=true'
                    : '');
                $ws = ($item['net'] == 'ws'
                    ? ', ws=true, ws-path=' . $item['path'] . ', ws-headers=host:' . $item['host']
                    : '');
                $Proxy = $item['ps'] . ' = vmess, ' . $item['add'] . ', ' . $item['port'] . ', username = ' . $item['id'] . $ws . $tls . PHP_EOL;
                if ($find) {
                    $item = ConfController::getMatchProxy($item, $Rule);
                    if ($item !== null) {
                        $All_Proxy .= $Proxy;
                    }
                } else {
                    $All_Proxy .= $Proxy;
                }
            }

            return $All_Proxy;
        }
        foreach ($items as $item) {
            if (in_array($surge, array(3, 4))) {
                $All_Proxy .= ($item['remark'] . ' = ss, ' . $item['address'] . ', ' . $item['port'] . ', encrypt-method=' . $item['method'] . ', password=' . $item['passwd'] . URL::getSurgeObfs($item) . ', udp-relay=true' . PHP_EOL);
            } else {
                $All_Proxy .= ($item['remark'] . ' = custom, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', ' . $item['passwd'] . ', https://raw.githubusercontent.com/lhie1/Rules/master/SSEncrypt.module' . URL::getSurgeObfs($item) . PHP_EOL);
            }
        }

        if ($surge == 4) {
            // v2
            $v2_items = URL::getAllVMessUrl($user, 1, $emoji);
            foreach ($v2_items as $item) {
                if (!in_array($item['net'], ['ws', 'tcp','kcp','quic','h2'])) {
                    continue;
                }
                $tls = ($item['tls'] == 'tls'
                    ? ', tls=true'
                    : '');
                $ws = ($item['net'] == 'ws'
                    ? ', ws=true, ws-path=' . $item['path'] . ', ws-headers=host:' . $item['host']
                    : '');
                $All_Proxy .= $item['ps'] . ' = vmess, ' . $item['add'] . ', ' . $item['port'] . ', username = ' . $item['id'] . $ws . $tls . PHP_EOL;
                $item['remark'] = $item['ps'];
                $items[] = $item;
            }
        }

        if ($source) {
            $SourceURL = trim(urldecode($opts['source']));
            // 远程规则仅支持 github 以及 gitlab
            if (!preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $SourceURL)) {
                return '远程配置仅支持 (gist)github 以及 gitlab 的链接。';
            }
            $SourceContent = @file_get_contents($SourceURL);
            if ($SourceContent) {
                $Content = ConfController::YAML2Array($SourceContent);
                if (!is_array($Content)) {
                    return $Content;
                }
                return ConfController::getSurgeConfs(
                    $user,
                    $All_Proxy,
                    $items,
                    $Content
                );
            } else {
                return '远程配置下载失败。';
            }
        }
        if (isset($opts['profiles']) && in_array((string) $opts['profiles'], array_keys(AppsProfiles::Surge()))) {
            $Profiles = (string) trim($opts['profiles']);
            $userapiUrl .= ('&profiles=' . $Profiles);
        } else {
            $Profiles = '123456'; // 默认策略组
        }
        $ProxyGroups = ConfController::getSurgeConfProxyGroup($items, AppsProfiles::Surge()[$Profiles]['ProxyGroup']);
        $ProxyGroups = ConfController::fixSurgeProxyGroup($ProxyGroups, AppsProfiles::Surge()[$Profiles]['Checks']);
        $ProxyGroups = ConfController::getSurgeProxyGroup2String($ProxyGroups);

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('surge', $surge)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('All_Proxy', $All_Proxy)
            ->assign('ProxyGroups', $ProxyGroups);

        return $render->fetch('surge.tpl');
    }

    /**
     * Quantumult 配置
     *
     * @param object $user       用户
     * @param int    $quantumult 订阅类型
     * @param array  $Rule       节点筛选规则
     * @param bool   $find       是否筛选节点
     *
     * @return string
     */
    public static function getQuantumult($user, $quantumult, $opts, $Rule, $find, $emoji)
    {
        $subInfo = self::getSubinfo($user, 0);
        $proxys = [];
        $groups = [];
        $subUrl = '';
        if ($quantumult == 2) {
            $subUrl = $subInfo['link'];
        } else {
            $back_china_name = '';
            $v2ray_group = '';
            $v2ray_name = '';
            $v2rays = URL::getAllVMessUrl($user, 1, $emoji);
            foreach ($v2rays as $v2ray) {
                if (in_array($v2ray['net'], array( 'kcp', 'quic','h2'))){
                    continue;
                }
                if (strpos($v2ray['ps'], '回国') or strpos($v2ray['ps'], 'China')) {
                    $back_china_name .= "\n" . $v2ray['ps'];
                } else {
                    $v2ray_name .= "\n" . $v2ray['ps'];
                }
                $v2ray_tls = ', over-tls=false, certificate=1';
                if (($v2ray['net'] == 'tcp' && $v2ray['tls'] == 'tls') || $v2ray['tls'] == 'tls') {
                    $v2ray_tls = ', over-tls=true, tls-host=' . $v2ray['add'];
                    if ($v2ray['verify_cert']) {
                                $v2ray_tls.=', certificate=1';
                        }else{
                        $v2ray_tls.=', certificate=0';
                    }

                }
                $v2ray_obfs = '';
                if ($v2ray['net'] == 'ws' || $v2ray['net'] == 'http') {
                    $v2ray_obfs = ', obfs=' . $v2ray['net'] . ', obfs-path="' . $v2ray['path'] . '", obfs-header="Host: ' . $v2ray['host'] . '[Rr][Nn]User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 18_0_0 like Mac OS X) AppleWebKit/888.8.88 (KHTML, like Gecko) Mobile/6666666"';
                }
                if ($quantumult == 1) {
                    $v2ray_group .= 'vmess://' . base64_encode($v2ray['ps'] . ' = vmess, ' . $v2ray['add'] . ', ' . $v2ray['port'] . ', chacha20-ietf-poly1305, "' . $v2ray['id'] . '", group=' . Config::get('appName') . '_v2' . $v2ray_tls . $v2ray_obfs) . PHP_EOL;
                } else {
                    $v2ray_group .= $v2ray['ps'] . ' = vmess, ' . $v2ray['add'] . ', ' . $v2ray['port'] . ', chacha20-ietf-poly1305, "' . $v2ray['id'] . '", group=' . Config::get('appName') . '_v2' . $v2ray_tls . $v2ray_obfs . PHP_EOL;
                }
            }
            if ($quantumult == 1) {
                $extend = isset($opts['extend']) ? $opts['extend'] : 0;
                $v2ray_group .= ($extend == 0
                    ? ''
                    : URL::getUserInfo($user, 'quantumult_v2', 0) . PHP_EOL);
                return base64_encode($v2ray_group);
            } elseif ($quantumult == 3) {
                $ss_group = '';
                $ss_name = '';
                $items = array_merge(URL::getAllItems($user, 0, 1, $emoji), URL::getAllItems($user, 1, 1, $emoji));
                foreach ($items as $item) {
                    $ss_group .= $item['remark'] . ' = shadowsocks, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', "' . $item['passwd'] . '", upstream-proxy=false, upstream-proxy-auth=false' . URL::getSurgeObfs($item) . ', group=' . Config::get('appName') . PHP_EOL;
                    if (strpos($item['remark'], '回国') or strpos($item['remark'], 'China')) {
                        $back_china_name .= "\n" . $item['remark'];
                    } else {
                        $ss_name .= "\n" . $item['remark'];
                    }
                }
                $ssr_group = '';
                $ssr_name = '';
                $ssrs = array_merge(URL::getAllItems($user, 0, 0, $emoji), URL::getAllItems($user, 1, 0, $emoji));
                foreach ($ssrs as $item) {
                    $ssr_group .= $item['remark'] . ' = shadowsocksr, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', "' . $item['passwd'] . '", protocol=' . $item['protocol'] . ', protocol_param=' . $item['protocol_param'] . ', obfs=' . $item['obfs'] . ', obfs_param="' . $item['obfs_param'] . '", group=' . Config::get('appName') . PHP_EOL;
                    if (strpos($item['remark'], '回国') or strpos($item['remark'], 'China')) {
                        $back_china_name .= "\n" . $item['remark'];
                    } else {
                        $ssr_name .= "\n" . $item['remark'];
                    }
                }
                $quan_proxy_group = base64_encode("🍃 Proxy  :  static, 🏃 Auto\n🏃 Auto\n🚀 Direct\n" . $ss_name . $ssr_name . $v2ray_name);
                $quan_auto_group = base64_encode("🏃 Auto  :  auto\n" . $ss_name . $ssr_name . $v2ray_name);
                $quan_domestic_group = base64_encode("🍂 Domestic  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy\n" . $back_china_name);
                $quan_others_group = base64_encode("☁️ Others  :   static, 🍃 Proxy\n🚀 Direct\n🍃 Proxy");
                $quan_apple_group = base64_encode("🍎 Only  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy");
                $quan_direct_group = base64_encode("🚀 Direct : static, DIRECT\nDIRECT");
                $proxys = [
                    'ss' => $ss_group,
                    'ssr' => $ssr_group,
                    'v2ray' => $v2ray_group,
                ];
                $groups = [
                    'proxy_group' => $quan_proxy_group,
                    'auto_group' => $quan_auto_group,
                    'domestic_group' => $quan_domestic_group,
                    'others_group' => $quan_others_group,
                    'direct_group' => $quan_direct_group,
                    'apple_group' => $quan_apple_group,
                ];
            } else {
                return '悟空别闹...';
            }
        }
        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('subUrl', $subUrl)
            ->assign('proxys', $proxys)
            ->assign('groups', $groups)
            ->assign('quantumult', $quantumult)
            ->assign('appName', Config::get('appName'));
        return $render->fetch('quantumult.tpl');
    }

    /**
     * Surfboard 配置
     *
     * @param object $user 用户
     * @param array  $opts request
     *
     * @return string
     */
    public static function getSurfboard($user, $surfboard, $opts, $Rule, $find, $emoji)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['surfboard'];
        $All_Proxy = '';
        $items = array_merge(URL::getAllItems($user, 0, 1, $emoji), URL::getAllItems($user, 1, 1, $emoji));
        foreach ($items as $item) {
            $All_Proxy .= ($item['remark'] . ' = custom, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', ' . $item['passwd'] . ', https://raw.githubusercontent.com/lhie1/Rules/master/SSEncrypt.module' . URL::getSurgeObfs($item) . PHP_EOL);
        }

        if (isset($opts['profiles']) && in_array((string) $opts['profiles'], array_keys(AppsProfiles::Surfboard()))) {
            $Profiles = (string) trim($opts['profiles']);
            $userapiUrl .= ('&profiles=' . $Profiles);
        } else {
            $Profiles = '123456'; // 默认策略组
        }
        $ProxyGroups = ConfController::getSurgeConfProxyGroup($items, AppsProfiles::Surfboard()[$Profiles]['ProxyGroup']);
        $ProxyGroups = ConfController::fixSurgeProxyGroup($ProxyGroups, AppsProfiles::Surfboard()[$Profiles]['Checks']);
        $ProxyGroups = ConfController::getSurgeProxyGroup2String($ProxyGroups);

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('All_Proxy', $All_Proxy)
            ->assign('ProxyGroups', $ProxyGroups);

        return $render->fetch('surfboard.tpl');
    }

    /**
     * Clash 配置
     *
     * @param object $user  用户
     * @param int    $clash 订阅类型
     * @param array  $opts  request
     *
     * @return string
     */
    public static function getClash($user, $clash, $opts, $Rule, $find, $emoji)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['clash'];
        $Proxys = [];
        // ss
        $items = array_merge(
            URL::getAllItems($user, 0, 1, $emoji),
            URL::getAllItems($user, 1, 1, $emoji),
            URL::getAllV2RayPluginItems($user, $emoji)
        );
        foreach ($items as $item) {
            $sss = [
                'name' => $item['remark'],
                'type' => 'ss',
                'server' => $item['address'],
                'port' => $item['port'],
                'cipher' => $item['method'],
                'password' => $item['passwd'],
                'udp' => true
            ];
            if ($item['obfs'] != 'plain') {
                switch ($item['obfs']) {
                    case 'simple_obfs_http':
                        $sss['plugin'] = 'obfs';
                        $sss['plugin-opts']['mode'] = 'http';
                        break;
                    case 'simple_obfs_tls':
                        $sss['plugin'] = 'obfs';
                        $sss['plugin-opts']['mode'] = 'tls';
                        break;
                    case 'v2ray':
                        $sss['plugin'] = 'v2ray-plugin';
                        $sss['plugin-opts']['mode'] = 'websocket';
                        if (strpos($item['obfs_param'], 'security=tls')) {
                            $sss['plugin-opts']['tls'] = true;
                            if ($item['verify_cert']==false) {

                                    $sss['plugin-opts']['skip-cert-verify']=true;

                            }
                        }
                        $sss['plugin-opts']['host'] = $item['host'];
                        $sss['plugin-opts']['path'] = $item['path'];
                        $sss['plugin-opts']['mux'] = true;
                        break;
                }
                if ($item['obfs'] != 'v2ray') {
                    if ($item['obfs_param'] != '') {
                        $sss['plugin-opts']['host'] = $item['obfs_param'];
                    } else {
                        $sss['plugin-opts']['host'] = 'windowsupdate.windows.com';
                    }
                }
            }
            if (isset($opts['source']) && $opts['source'] != '') {
                $sss['class'] = $item['class'];
            }
            $Proxys[] = $sss;
        }
        // v2
        $items = URL::getAllVMessUrl($user, 1, $emoji);
        foreach ($items as $item) {
            if (in_array($item['net'], array('kcp', 'http', 'quic','h2'))) {
                continue;
            }
            $v2rays = [
                'name' => $item['ps'],
                'type' => 'vmess',
                'server' => $item['add'],
                'port' => $item['port'],
                'uuid' => $item['id'],
                'alterId' => $item['aid'],
                'cipher' => 'auto',
                'udp' => true
            ];
            if ($item['net'] == 'ws') {
                $v2rays['network'] = 'ws';
                $v2rays['ws-path'] = $item['path'];
                if ($item['tls'] == 'tls') {
                    $v2rays['tls'] = true;
                }
                if ($item['host'] != '') {
                    $v2rays['ws-headers']['Host'] = $item['host'];
                }
            } elseif (($item['net'] == 'tcp' && $item['tls'] == 'tls') || $item['net'] == 'tls') {
                $v2rays['tls'] = true;
            }

            if ($item['verify_cert']==false) {

                    $v2rays['skip-cert-verify']=true;

            }
            if (isset($opts['source']) && $opts['source'] != '') {
                $v2rays['class'] = $item['class'];
            }
            $Proxys[] = $v2rays;
        }

        if ($clash == 2) {
            // ssr
            $items = array_merge(
                URL::getAllItems($user, 0, 0, $emoji),
                URL::getAllItems($user, 1, 0, $emoji)
            );
            foreach ($items as $item) {
                // 不支持的
                if (
                    in_array($item['method'], ['rc4-md5-6', 'des-ede3-cfb', 'xsalsa20', 'none'])
                    ||
                    in_array($item['protocol'], array_merge(Config::getSupportParam('allow_none_protocol'), ['verify_deflate']))
                    ||
                    in_array($item['obfs'], ['tls1.2_ticket_fastauth'])
                ) {
                    continue;
                }
                $ssr = [
                    'name' => $item['remark'],
                    'type' => 'ssr',
                    'server' => $item['address'],
                    'port' => $item['port'],
                    'cipher' => $item['method'],
                    'password' => $item['passwd'],
                    'protocol' => $item['protocol'],
                    'protocolparam' => $item['protocol_param'],
                    'obfs' => $item['obfs'],
                    'obfsparam' => $item['obfs_param']
                ];
                if (isset($opts['source']) && $opts['source'] != '') {
                    $ssr['class'] = $item['class'];
                }
                $Proxys[] = $ssr;
            }
        }

        if (isset($opts['source']) && $opts['source'] != '') {
            $SourceURL = trim(urldecode($opts['source']));
            // 远程规则仅支持 github 以及 gitlab
            if (!preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $SourceURL)) {
                return '远程配置仅支持 (gist)github 以及 gitlab 的链接。';
            }
            $SourceContent = @file_get_contents($SourceURL);
            if ($SourceContent) {
                $Content = ConfController::YAML2Array($SourceContent);
                if (!is_array($Content)) {
                    return $Content;
                }
                return ConfController::getClashConfs(
                    $user,
                    $Proxys,
                    $Content
                );
            } else {
                return '远程配置下载失败。';
            }
        } else {
            if (isset($opts['profiles']) && in_array((string) $opts['profiles'], array_keys(AppsProfiles::Clash()))) {
                $Profiles = (string) trim($opts['profiles']);
                $userapiUrl .= ('&profiles=' . $Profiles);
            } else {
                $Profiles = '123456'; // 默认策略组
            }
            $ProxyGroups = ConfController::getClashConfProxyGroup($Proxys, AppsProfiles::Clash()[$Profiles]['ProxyGroup']);
            $ProxyGroups = ConfController::fixClashProxyGroup($ProxyGroups, AppsProfiles::Clash()[$Profiles]['Checks']);
            $ProxyGroups = ConfController::getClashProxyGroup2String($ProxyGroups);
        }

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('opts', $opts)
            ->assign('Proxys', $Proxys)
            ->assign('ProxyGroups', $ProxyGroups)
            ->assign('Profiles', $Profiles);

        return $render->fetch('clash.tpl');
    }

    /**
     * SSD 订阅
     *
     * @param object $user 用户
     *
     * @return string
     */
    public static function getSSD($user, $ssd, $opts, $Rule, $find, $emoji)
    {
        return URL::getAllSSDUrl($user);
    }

    /**
     * Shadowrocket 订阅
     *
     * @param object $user 用户
     * @param array  $opts request
     * @param array  $Rule 节点筛选规则
     * @param bool   $find 是否筛选节点
     *
     * @return string
     */
    public static function getShadowrocket($user, $shadowrocket, $opts, $Rule, $find, $emoji)
    {
        $emoji = 0; // Shadowrocket 自带 emoji

        $return = '';
        if (strtotime($user->expire_in) > time()) {
            if ($user->transfer_enable == 0) {
                $tmp = '剩余流量：0';
            } else {
                $tmp = '剩余流量：' . $user->unusedTraffic();
            }
            $tmp .= '.♥.过期时间：';
            if ($user->class_expire != '1989-06-04 00:05:00') {
                $userClassExpire = explode(' ', $user->class_expire);
                $tmp .= $userClassExpire[0];
            } else {
                $tmp .= '无限期';
            }
        } else {
            $tmp = '账户已过期，请续费后使用';
        }
        $return .= ('STATUS=' . $tmp
            . PHP_EOL
            . 'REMARKS=' . Config::get('appName')
            . PHP_EOL);

        $extend = isset($opts['extend']) ? $opts['extend'] : 0;
        $return .= ($extend == 0 ? '' : URL::getUserInfo($user, 'ssr', 0) . PHP_EOL);

        // v2ray
        $items = URL::getAllVMessUrl($user, 1);
        foreach ($items as $item) {
            if (in_array($item['net'], array( 'http', 'quic'))) {
                continue;
            }
            if ($find) {
                $item['remark'] = $item['ps'];
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) {
                    continue;
                }
            }
            $obfs = '';
            if ($item['net'] == 'ws') {
                $obfs .= ($item['host'] != ''
                    ? ('&obfsParam=' . $item['host'] .
                        '&path=' . $item['path'] . '&obfs=websocket')
                    : ('&obfsParam=' . $item['add'] .
                        '&path=' . $item['path'] . '&obfs=websocket'));
                $obfs .= ($item['tls'] == 'tls'
                    ? '&tls=1'
                    : '&tls=0');
            } elseif (($item['net'] == 'tcp' && $item['tls'] == 'tls') || $item['net'] == 'tls') {
                $obfs .= '&obfs=none';
                $obfs .= ($item['tls'] == 'tls'
                    ? '&tls=1'
                    : '&tls=0');

            } elseif($item['net'] == 'kcp' || $item['net'] == 'mkcp'){
                $obfs .='obfsParam={"header":'.'"'.($item['type'] == ''||$item['type'] == 'noop'
                        ? 'none'
                        : $item['type']).'"'.'}&obfs=mkcp';
            }
              elseif ($item['net'] == 'h2'){
                  $obfs .= ($item['host'] != ''
                      ? ('&obfsParam=' . $item['host'] .
                          '&path=' . $item['path'] . '&obfs=h2')
                      : ('&obfsParam=' . $item['add'] .
                          '&path=' . $item['path'] . '&obfs=h2'));
                  $obfs .= ($item['tls'] == 'tls'
                      ? '&tls=1'
                      : '&tls=0');
                }

            else {
                $obfs .= '&obfs=none';
            }

            if ($obfs!='&obfs=none' && $item['net'] != 'h2'){

                    if ($item['verify_cert']==false){
                        $obfs.="&allowInsecure=1";
                    }

            }
            $return .= ('vmess://' . Tools::base64_url_encode(
                'chacha20-poly1305:' . $item['id'] .
                    '@' . $item['add'] . ':' . $item['port']
            ) . '?remarks=' . rawurlencode($item['ps'])
                . $obfs . PHP_EOL);
        }

        // 减少因为加密协议混淆同时支持 ss & ssr 而导致订阅出现大量重复节点
        if (in_array($user->method, Config::getSupportParam('ss_aead_method')) || in_array($user->obfs, Config::getSupportParam('ss_obfs'))) {
            // ss
            $items = URL::getAllItems($user, 0, 1);
            foreach ($items as $item) {
                if ($find) {
                    $item = ConfController::getMatchProxy($item, $Rule);
                    if ($item === null) {
                        continue;
                    }
                }
                if (in_array($item['obfs'], Config::getSupportParam('ss_obfs'))) {
                    $return .= (URL::getItemUrl($item, 1) . PHP_EOL);
                } elseif ($item['obfs'] == 'plain') {
                    $return .= (URL::getItemUrl($item, 2) . PHP_EOL);
                }
            }
        }

        // ss_mu
        $items = array_merge(
            URL::getAllItems($user, 1, 1),
            URL::getAllV2RayPluginItems($user)
        );
        foreach ($items as $item) {
            if ($find) {
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) {
                    continue;
                }
            }
            //  V2Ray-Plugin
            if ($item['obfs'] == 'v2ray') {
                $v2rayplugin = [
                    'address' => $item['address'],
                    'port' => (string) $item['port'],
                    'path' => $item['path'],
                    'host' => $item['host'],
                    'mode' => 'websocket',
                ];
                $v2rayplugin['tls'] = $item['tls'] == 'tls' ? true : false;
                $return .= ('ss://' . Tools::base64_url_encode(
                    $item['method'] . ':' . $item['passwd'] .
                        '@' . $item['address'] . ':' . $item['port']
                ) . '?v2ray-plugin=' . base64_encode(
                    json_encode($v2rayplugin)
                ) . '#' . rawurlencode($item['remark']) . PHP_EOL);
            }
            // obfs
            if (in_array($item['obfs'], Config::getSupportParam('ss_obfs'))) {
                $return .= (URL::getItemUrl($item, 1) . PHP_EOL);
            }
            // ss 单端口不存在混淆为 plain
        }

        // ssr
        $return .= URL::get_NewAllUrl($user, 0, 0, $Rule, $find) . PHP_EOL;

        return Tools::base64_url_encode($return);
    }

    /**
     * Kitsunebi 订阅
     *
     * @param object $user 用户
     * @param array  $opts request
     * @param array  $Rule 节点筛选规则
     * @param bool   $find 是否筛选节点
     *
     * @return string
     */
    public static function getKitsunebi($user, $kitsunebi, $opts, $Rule, $find, $emoji)
    {
        $return = '';

        // 账户到期时间以及流量信息
        $extend = isset($opts['extend']) ? (int) $opts['extend'] : 0;
        $return .= $extend == 0 ? '' : URL::getUserInfo($user, 'ss', 1) . PHP_EOL;

        // v2ray
        $items = URL::getAllVMessUrl($user, 1, $emoji);
        foreach ($items as $item) {
            if ($find) {
                $item['remark'] = $item['ps'];
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) {
                    continue;
                }
            }
            $network = ($item['net'] == 'tls'
                ? '&network=tcp'
                : ('&network=' . $item['net']));
            $protocol = '';
            switch ($item['net']) {
                case 'kcp':
                    $protocol .= ('&kcpheader=' . $item['type'] .
                        '&uplinkcapacity=1' .
                        '&downlinkcapacity=6');
                    break;
                case 'ws':
                    $protocol .= ('&wspath=' . $item['path'] .
                        '&wsHost=' . $item['host']);
                    break;
                case "h2":
                    $protocol .= ('&h2Path=' . $item['path'] .
                        '&h2Host=' . $item['host']);
                    break;
            }
            $tls = ($item['tls'] == 'tls' || $item['net'] == 'tls'
                ? '&tls=1'
                : '&tls=0');
            if ($item['verify_cert']==false && ($item['tls'] == 'tls' || $item['net'] == 'tls')) {
                $tls .='&allowInsecure=1';
            }
            $mux = '&mux=&muxConcurrency=8';
            $return .= ('vmess://' . base64_encode(
                'auto:' . $item['id'] .
                    '@' . $item['add'] . ':' . $item['port']
            ) . '?remark=' . rawurlencode($item['ps']) .
                $network . $protocol .
                '&aid=' . $item['aid']
                . $tls . $mux . PHP_EOL);
        }

        // ss
        if (URL::SSCanConnect($user) && !in_array($user->obfs, ['simple_obfs_http', 'simple_obfs_tls'])) {
            $user = URL::getSSConnectInfo($user);
            $user->obfs = 'plain';
            $items = URL::getAllItems($user, 0, 1, $emoji);
            if ($find) {
                foreach ($items as $item) {
                    $item = ConfController::getMatchProxy($item, $Rule);
                    if ($item !== null) {
                        $return .= (URL::getItemUrl($item, 2) . PHP_EOL);
                    }
                }
            } else {
                foreach ($items as $item) {
                    $return .= (URL::getItemUrl($item, 2) . PHP_EOL);
                }
            }
        }

        return base64_encode($return);
    }

    public static function getSSPcConf($user)
    {
        $proxy = [];
        $items = array_merge(
            URL::getAllItems($user, 0, 1, 0),
            URL::getAllItems($user, 1, 1, 0)
        );
        foreach ($items as $item) {
            $proxy_plugin = '';
            $proxy_plugin_opts = '';
            if ($item['obfs'] == 'v2ray' || in_array($item['obfs'], Config::getSupportParam('ss_obfs'))) {
                if ($item['obfs'] == 'v2ray') {
                    $proxy_plugin .= 'v2ray';
                } else {
                    $proxy_plugin .= 'obfs-local';
                }
                if (strpos($item['obfs'], 'http') !== false) {
                    $proxy_plugin_opts .= 'obfs=http';
                } elseif (strpos($item['obfs'], 'tls') !== false) {
                    $proxy_plugin_opts .= 'obfs=tls';
                } else {
                    $proxy_plugin_opts .= 'v2ray;' . $item['obfs_param'];
                }
                if ($item['obfs_param'] != '' && $item['obfs'] != 'v2ray') {
                    $proxy_plugin_opts .= ';obfs-host=' . $item['obfs_param'];
                }
            }
            $proxy[] = [
                'remarks' => $item['remark'],
                'server' => $item['address'],
                'server_port' => $item['port'],
                'method' => $item['method'],
                'password' => $item['passwd'],
                'timeout' => 5,
                'plugin' => $proxy_plugin,
                'plugin_opts' => $proxy_plugin_opts
            ];
        }
        $config = [
            'configs' => $proxy,
            'strategy' => null,
            'index' => 0,
            'global' => false,
            'enabled' => true,
            'shareOverLan' => false,
            'isDefault' => false,
            'localPort' => 1080,
            'portableMode' => true,
            'pacUrl' => null,
            'useOnlinePac' => false,
            'secureLocalPac' => true,
            'availabilityStatistics' => false,
            'autoCheckUpdate' => true,
            'checkPreRelease' => false,
            'isVerboseLogging' => false,
            'logViewer' => [
              'topMost' => false,
              'wrapText' => false,
              'toolbarShown' => false,
              'Font' => 'Consolas, 8pt',
              'BackgroundColor' => 'Black',
              'TextColor' => 'White'
            ],
            'proxy' => [
              'useProxy' => false,
              'proxyType' => 0,
              'proxyServer' => '',
              'proxyPort' => 0,
              'proxyTimeout' => 3
            ],
            'hotkey' => [
              'SwitchSystemProxy' => '',
              'SwitchSystemProxyMode' => '',
              'SwitchAllowLan' => '',
              'ShowLogs' => '',
              'ServerMoveUp' => '',
              'ServerMoveDown' => '',
              'RegHotkeysAtStartup' => false
            ]
        ];

        return json_encode($config, JSON_PRETTY_PRINT);
    }

    public static function getSSRPcConf($user)
    {
        $proxy = [];
        $items = array_merge(
            URL::getAllItems($user, 0, 0, 0),
            URL::getAllItems($user, 1, 0, 0)
        );
        foreach ($items as $item) {
            $proxy[] = [
                'remarks' => $item['remark'],
                'server' => $item['address'],
                'server_port' => $item['port'],
                'method' => $item['method'],
                'obfs' => $item['obfs'],
                'obfsparam' => $item['obfs_param'],
                'remarks_base64' => base64_encode($item['remark']),
                'password' => $item['passwd'],
                'tcp_over_udp' => false,
                'udp_over_tcp' => false,
                'group' => Config::get('appName'),
                'protocol' => $item['protocol'],
                'protocolparam' => $item['protocol_param'],
                'obfs_udp' => false,
                'enable' => true
            ];
        }
        $config = [
            'configs' => $proxy,
            'index' => 0,
            'random' => true,
            'sysProxyMode' => 1,
            'shareOverLan' => false,
            'localPort' => 1080,
            'localAuthPassword' => Tools::genRandomChar(26),
            'dnsServer' => '',
            'reconnectTimes' => 2,
            'balanceAlgorithm' => 'LowException',
            'randomInGroup' => false,
            'TTL' => 0,
            'connectTimeout' => 5,
            'proxyRuleMode' => 2,
            'proxyEnable' => false,
            'pacDirectGoProxy' => false,
            'proxyType' => 0,
            'proxyHost' => '',
            'proxyPort' => 0,
            'proxyAuthUser' => '',
            'proxyAuthPass' => '',
            'proxyUserAgent' => '',
            'authUser' => '',
            'authPass' => '',
            'autoBan' => false,
            'sameHostForSameTarget' => false,
            'keepVisitTime' => 180,
            'isHideTips' => false,
            'nodeFeedAutoUpdate' => true,
            'serverSubscribes' => [
                [
                    'URL' => self::getSubinfo($user, 0)['ssr'],
                    'Group' => Config::get('appName'),
                    'LastUpdateTime' => 0
                ]
            ],
            'token' => [],
            'portMap' => []
        ];

        return json_encode($config, JSON_PRETTY_PRINT);
    }

    public static function getSSDPcConf($user)
    {
        $id = 1;
        $proxy = [];
        $items = array_merge(
            URL::getAllItems($user, 0, 1, 0),
            URL::getAllItems($user, 1, 1, 0)
        );
        foreach ($items as $item) {
            $proxy_plugin = '';
            $proxy_plugin_opts = '';
            if ($item['obfs'] == 'v2ray' || in_array($item['obfs'], Config::getSupportParam('ss_obfs'))) {
                if ($item['obfs'] == 'v2ray') {
                    $proxy_plugin .= 'v2ray';
                } else {
                    $proxy_plugin .= 'simple-obfs';
                }
                if (strpos($item['obfs'], 'http') !== false) {
                    $proxy_plugin_opts .= 'obfs=http';
                } elseif (strpos($item['obfs'], 'tls') !== false) {
                    $proxy_plugin_opts .= 'obfs=tls';
                } else {
                    $proxy_plugin_opts .= 'v2ray;' . $item['obfs_param'];
                }
                if ($item['obfs_param'] != '' && $item['obfs'] != 'v2ray') {
                    $proxy_plugin_opts .= ';obfs-host=' . $item['obfs_param'];
                }
            }
            $proxy[] = [
                'remarks' => $item['remark'],
                'server' => $item['address'],
                'server_port' => $item['port'],
                'password' => $item['passwd'],
                'method' => $item['method'],
                'plugin' => $proxy_plugin,
                'plugin_opts' => $proxy_plugin_opts,
                'plugin_args' => '',
                'timeout' => 5,
                'id' => $id,
                'ratio' => $item['ratio'],
                'subscription_url' => self::getSubinfo($user, 0)['ssd']
            ];
            $id++;
        }
        $plugin = '';
        $plugin_opts = '';
        if ($user->obfs == 'v2ray' || in_array($user->obfs, Config::getSupportParam('ss_obfs'))) {
            if ($user->obfs == 'v2ray') {
                $plugin .= 'v2ray';
            } else {
                $plugin .= 'simple-obfs';
            }
            if (strpos($user->obfs, 'http') !== false) {
                $plugin_opts .= 'obfs=http';
            } elseif (strpos($user->obfs, 'tls') !== false) {
                $plugin_opts .= 'obfs=tls';
            } else {
                $plugin_opts .= 'v2ray;' . $user->obfs_param;
            }
            if ($user->obfs_param != '' && $user->obfs != 'v2ray') {
                $plugin_opts .= ';obfs-host=' . $user->obfs_param;
            }
        }
        $config = [
            'configs' => $proxy,
            'strategy' => null,
            'index' => 0,
            'global' => false,
            'enabled' => true,
            'shareOverLan' => false,
            'isDefault' => false,
            'localPort' => 1080,
            'portableMode' => true,
            'pacUrl' => null,
            'useOnlinePac' => false,
            'secureLocalPac' => true,
            'availabilityStatistics' => false,
            'autoCheckUpdate' => true,
            'checkPreRelease' => false,
            'isVerboseLogging' => false,
            'logViewer' => [
              'topMost' => false,
              'wrapText' => false,
              'toolbarShown' => false,
              'Font' => 'Consolas, 8pt',
              'BackgroundColor' => 'Black',
              'TextColor' => 'White'
            ],
            'proxy' => [
              'useProxy' => false,
              'proxyType' => 0,
              'proxyServer' => '',
              'proxyPort' => 0,
              'proxyTimeout' => 3
            ],
            'hotkey' => [
              'SwitchSystemProxy' => '',
              'SwitchSystemProxyMode' => '',
              'SwitchAllowLan' => '',
              'ShowLogs' => '',
              'ServerMoveUp' => '',
              'ServerMoveDown' => '',
              'RegHotkeysAtStartup' => false
            ],
            'subscriptions' => [
              [
                'airport' => Config::get('appName'),
                'encryption' => $user->method,
                'password' => $user->passwd,
                'port' => $user->port,
                'expiry' => $user->class_expire,
                'traffic_used' => Tools::flowToGB($user->u + $user->d),
                'traffic_total' => Tools::flowToGB($user->transfer_enable),
                'url' => self::getSubinfo($user, 0)['ssd'],
                'plugin' => $plugin,
                'plugin_options' => $plugin_opts,
                'plugin_arguments' => '',
                'use_proxy' => false
              ]
            ]
        ];

        return json_encode($config, JSON_PRETTY_PRINT);
    }

    /**
     * 通用订阅，ssr & v2rayn
     *
     * @param object $user 用户
     * @param int    $sub  订阅类型
     * @param array  $opts request
     * @param array  $Rule 节点筛选规则
     * @param bool   $find 是否筛选节点
     *
     * @return string
     */
    public static function getSub($user, $sub, $opts, $Rule, $find, $emoji)
    {
        $extend = isset($opts['extend']) ? $opts['extend'] : 0;
        $traffic_class_expire = 1;
        $getV2rayPlugin = 1;
        $return_url = '';

        // Quantumult 则不显示账户到期以及流量信息
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult') !== false) {
            $traffic_class_expire = 0;
        }

        // 如果是 Kitsunebi 不输出 SS V2rayPlugin 节点
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Kitsunebi') !== false) {
            $getV2rayPlugin = 0;
        }
        switch ($sub) {
            case 1: // SSR
                $return_url .= $extend == 0 ? '' : URL::getUserInfo($user, 'ssr', $traffic_class_expire) . PHP_EOL;
                $return_url .= URL::get_NewAllUrl($user, 0, $getV2rayPlugin, $Rule, $find, $emoji) . PHP_EOL;
                break;
            case 2: // SS
                $return_url .= $extend == 0 ? '' : URL::getUserInfo($user, 'ss', $traffic_class_expire) . PHP_EOL;
                $return_url .= URL::get_NewAllUrl($user, 1, $getV2rayPlugin, $Rule, $find, $emoji) . PHP_EOL;
                break;
            case 3: // V2
                $return_url .= $extend == 0 ? '' : URL::getUserInfo($user, 'v2ray', $traffic_class_expire) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user, 0, $emoji) . PHP_EOL;
                break;
            case 4: // V2 + SS
                $return_url .= $extend == 0 ? '' : URL::getUserInfo($user, 'v2ray', $traffic_class_expire) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user, 0, $emoji) . PHP_EOL;
                $return_url .= URL::get_NewAllUrl($user, 1, $getV2rayPlugin, $Rule, $find, $emoji) . PHP_EOL;
                break;
            case 5: // V2 + SS + SSR
                $return_url .= $extend == 0 ? '' : URL::getUserInfo($user, 'ssr', $traffic_class_expire) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user, 0, $emoji) . PHP_EOL;
                $return_url .= URL::get_NewAllUrl($user, 1, $getV2rayPlugin, $Rule, $find, $emoji) . PHP_EOL;
                $return_url .= URL::get_NewAllUrl($user, 0, $getV2rayPlugin, $Rule, $find, $emoji) . PHP_EOL;
                break;
        }
        return base64_encode($return_url);
    }
}
