<?php

//Thanks to http://blog.csdn.net/jollyjumper/article/details/9823047

namespace App\Controllers;

use App\Models\Link;
use App\Models\User;
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

        $sub = (isset($request->getQueryParams()['sub'])
            ? (int) $request->getQueryParams()['sub']
            : 0);
        $ssd = (isset($request->getQueryParams()['ssd'])
            ? (int) $request->getQueryParams()['ssd']
            : 0);
        $clash = (isset($request->getQueryParams()['clash'])
            ? (int) $request->getQueryParams()['clash']
            : 0);
        $surge = (isset($request->getQueryParams()['surge'])
            ? (int) $request->getQueryParams()['surge']
            : 0);
        $quantumult = (isset($request->getQueryParams()['quantumult'])
            ? (int) $request->getQueryParams()['quantumult']
            : 0);
        $surfboard = (isset($request->getQueryParams()['surfboard'])
            ? (int) $request->getQueryParams()['surfboard']
            : 0);
        $kitsunebi = (isset($request->getQueryParams()['kitsunebi'])
            ? (int) $request->getQueryParams()['kitsunebi']
            : 0);
        $shadowrocket = (isset($request->getQueryParams()['shadowrocket'])
            ? (int) $request->getQueryParams()['shadowrocket']
            : 0);

        if (isset($request->getQueryParams()['mu'])) {
            $mu = (int) $request->getQueryParams()['mu'];
            switch ($mu) {
                case 0:
                    $sub = 1;
                    break;
                case 1:
                    $sub = 1;
                    break;
                case 2:
                    $sub = 3;
                    break;
                case 3:
                    $ssd = 1;
                    break;
                case 4:
                    $clash = 1;
                    break;
            }
        }

        // 将访问 V2RayNG 订阅的 Quantumult 转到 Quantumult 的 V2Ray 专属订阅
        if (
            strpos($_SERVER['HTTP_USER_AGENT'], 'Quantumult') !== false
            && $sub == 3
        ) {
            $quantumult = 1;
        }

        if (in_array($quantumult, array(1, 2, 3))) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getQuantumult($user, $quantumult),
                'Quantumult.conf'
            );
        } elseif (in_array($surge, array(1, 2, 3))) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getSurge($user, $surge, $opts),
                'Surge.conf'
            );
        } elseif ($surfboard == 1) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getSurfboard($user),
                'Surfboard.conf'
            );
        } elseif ($clash == 1) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getClash($user, $opts),
                'config.yaml'
            );
        } elseif ($ssd == 1) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getSSD($user),
                'SSD.txt'
            );
        } elseif ($kitsunebi == 1) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getKitsunebi($user),
                'Kitsunebi.txt'
            );
        } elseif ($shadowrocket == 1) {
            $getBody = self::getBody(
                $user,
                $response,
                self::getShadowrocket($user),
                'Shadowrocket.txt'
            );
        } else {
            $getBody = self::getBody(
                $user,
                $response,
                self::getSub($user, $sub, $opts),
                'node.txt'
            );
        }

        return $getBody;
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
            'surge' => $userapiUrl . '?surge=' . $int,
            'surge_node' => $userapiUrl . '?surge=1',
            'surge2' => $userapiUrl . '?surge=2',
            'surge3' => $userapiUrl . '?surge=3',
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
     *
     * @return string
     */
    public static function getSurge($user, $surge, $opts)
    {
        $subInfo = self::getSubinfo($user, $surge);
        $userapiUrl = $subInfo['surge'];
        $proxy_name = '';
        $proxy_group = '';
        $items = array_merge(
            URL::getAllItems($user, 0, 1, 0),
            URL::getAllItems($user, 1, 1, 0)
        );
        foreach ($items as $item) {
            if (in_array($surge, array(1, 3))) {
                $proxy_group .= ($item['remark']
                    . ' = ss, '
                    . $item['address'] .
                    ', '
                    . $item['port']
                    . ', encrypt-method='
                    . $item['method']
                    . ', password='
                    . $item['passwd']
                    . URL::getSurgeObfs($item)
                    . ', udp-relay=true'
                    . PHP_EOL);
            } else {
                $proxy_group .= ($item['remark']
                    . ' = custom, '
                    . $item['address']
                    . ', '
                    . $item['port']
                    . ', '
                    . $item['method']
                    . ', '
                    . $item['passwd']
                    . ', https://raw.githubusercontent.com/lhie1/Rules/master/SSEncrypt.module'
                    . URL::getSurgeObfs($item)
                    . PHP_EOL);
            }
            $proxy_name .= (', ' . $item['remark']);
        }

        if (isset($opts['source']) && $opts['source'] != '') {
            $SourceURL = trim(urldecode($opts['source']));
            // 远程规则仅支持 github 以及 gitlab
            if (!preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $SourceURL)) {
                return '远程配置仅支持 (gist)github 以及 gitlab 的链接。';
            }
            $SourceContent = @file_get_contents($SourceURL);
            if ($SourceContent) {
                return ConfController::getSurgeConfs(
                    $user,
                    $proxy_group,
                    $items,
                    $SourceContent
                );
            } else {
                return '远程配置下载失败。';
            }
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

    /**
     * Quantumult 配置
     *
     * @param object $user       用户
     * @param int    $quantumult 订阅类型
     *
     * @return string
     */
    public static function getQuantumult($user, $quantumult = 0)
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
            $v2rays = URL::getAllVMessUrl($user, 1);
            foreach ($v2rays as $v2ray) {
                if ($v2ray['net'] == 'kcp' || $v2ray['net'] == 'quic') {
                    continue;
                }
                if (strpos($v2ray['ps'], '回国') or strpos($v2ray['ps'], 'China')) {
                    $back_china_name .= "\n" . $v2ray['ps'];
                } else {
                    $v2ray_name .= "\n" . $v2ray['ps'];
                }
                $v2ray_tls = ', over-tls=false, certificate=1';
                if (($v2ray['net'] == 'tcp' && $v2ray['tls'] == 'tls') || $v2ray['tls'] == 'tls') {
                    $v2ray_tls = ', over-tls=true, tls-host=' . $v2ray['add'] . ', certificate=1';
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
                return base64_encode($v2ray_group);
            } elseif ($quantumult == 3) {
                $ss_group = '';
                $ss_name = '';
                $items = array_merge(URL::getAllItems($user, 0, 1, 0), URL::getAllItems($user, 1, 1, 0));
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
                $ssrs = array_merge(URL::getAllItems($user, 0, 0), URL::getAllItems($user, 1, 0));
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
     *
     * @return string
     */
    public static function getSurfboard($user)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['surfboard'];
        $ss_name = '';
        $ss_group = '';
        $items = array_merge(URL::getAllItems($user, 0, 1, 0), URL::getAllItems($user, 1, 1, 0));
        foreach ($items as $item) {
            $ss_group .= $item['remark'] . ' = ss, ' . $item['address'] . ', ' . $item['port'] . ', ' . $item['method'] . ', ' . $item['passwd'] . URL::getSurgeObfs($item) . PHP_EOL;
            $ss_name .= ', ' . $item['remark'];
        }
        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('ss_name', $ss_name)
            ->assign('ss_group', $ss_group);
        return $render->fetch('surfboard.tpl');
    }

    /**
     * Clash 配置
     *
     * @param object $user 用户
     * @param array  $opts request
     *
     * @return string
     */
    public static function getClash($user, $opts)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['clash'];
        $confs = [];
        $proxy_confs = [];
        $back_china_confs = [];
        // ss
        $items = array_merge(URL::getAllItems($user, 0, 1, 1), URL::getAllItems($user, 1, 1, 1));
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
                        }
                        $sss['plugin-opts']['host'] = $item['host'];
                        $sss['plugin-opts']['path'] = $item['path'];
                        $sss['plugin-opts']['skip-cert-verify'] = true;
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
            if (strpos($sss['name'], '回国') or strpos($sss['name'], 'China')) {
                $back_china_confs[] = $sss;
            } else {
                $proxy_confs[] = $sss;
            }
            $confs[] = $sss;
        }
        // v2
        $items = URL::getAllVMessUrl($user, 1);
        foreach ($items as $item) {
            if (in_array($item['net'], array('kcp', 'http', 'quic'))) {
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
            if (isset($opts['source']) && $opts['source'] != '') {
                $v2rays['class'] = $item['class'];
            }
            if (strpos($v2rays['name'], '回国') or strpos($v2rays['name'], 'China')) {
                $back_china_confs[] = $v2rays;
            } else {
                $proxy_confs[] = $v2rays;
            }
            $confs[] = $v2rays;
        }

        if (isset($opts['source']) && $opts['source'] != '') {
            $SourceURL = trim(urldecode($opts['source']));
            // 远程规则仅支持 github 以及 gitlab
            if (!preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $SourceURL)) {
                return '远程配置仅支持 (gist)github 以及 gitlab 的链接。';
            }
            $SourceContent = @file_get_contents($SourceURL);
            if ($SourceContent) {
                return ConfController::getClashConfs($user, $confs, $SourceContent);
            } else {
                return '远程配置下载失败。';
            }
        }

        $render = ConfRender::getTemplateRender();
        $render->assign('user', $user)
            ->assign('userapiUrl', $userapiUrl)
            ->assign('opts', $opts)
            ->assign('confs', $confs)
            ->assign(
                'proxies',
                array_map(
                    static function ($conf) {
                        return $conf['name'];
                    },
                    $proxy_confs
                )
            )
            ->assign(
                'back_china_proxies',
                array_map(
                    static function ($conf) {
                        return $conf['name'];
                    },
                    $back_china_confs
                )
            );

        return $render->fetch('clash.tpl');
    }

    /**
     * SSD 订阅
     *
     * @param object $user 用户
     *
     * @return string
     */
    public static function getSSD($user)
    {
        return URL::getAllSSDUrl($user);
    }

    /**
     * Shadowrocket 订阅
     *
     * @param object $user 用户
     *
     * @return string
     */
    public static function getShadowrocket($user)
    {
        $return = '';
        if (strtotime($user->expire_in) > time()) {
            if ($user->transfer_enable == 0) {
                $tmp = '剩余流量：0';
            } else {
                $tmp = '剩余流量：' . $user->unusedTraffic();
            }
            $tmp .= '...过期时间：';
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
        // v2ray
        $items = URL::getAllVMessUrl($user, 1);
        foreach ($items as $item) {
            if ($item['net'] == 'kcp') {
                continue;
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
            } else {
                $obfs .= '&obfs=none';
            }
            $return .= ('vmess://' . Tools::base64_url_encode(
                'chacha20-poly1305:' . $item['id'] .
                    '@' . $item['add'] . ':' . $item['port']
            ) . '?remarks=' . rawurlencode($item['ps'])
                . $obfs . PHP_EOL);
        }
        // ss
        $items = array_merge(
            URL::getAllItems($user, 0, 1),
            URL::getAllItems($user, 1, 1)
        );
        foreach ($items as $item) {
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
                ) . '?v2ray-plugin=' . Tools::base64_url_encode(
                    json_encode($v2rayplugin)
                ) . '#' . rawurlencode($item['remark']) . PHP_EOL);
            } elseif (in_array($item['obfs'], ['simple_obfs_http', 'simple_obfs_tls'])) {
                $obfs = ($item['method'] == 'simple_obfs_http'
                    ? 'obfs=http;'
                    : 'obfs=tls;');
                $obfs .= ($item['obfs_param'] != ''
                    ? ('obfs-host=' . $item['obfs_param'] . ';')
                    : 'obfs-host=windowsupdate.windows.com;');
                $return .= ('ss://' . Tools::base64_url_encode(
                    $item['method'] . ':' . $item['passwd']
                ) . '@' . $item['address'] . ':' . $item['port'] .
                    '?plugin=simple-obfs;' . $obfs .
                    'obfs-uri=/#' . rawurlencode(
                        Config::get('appName') . ' - ' . $item['remark']
                    ) . PHP_EOL);
            } elseif ($item['obfs'] == 'plain') {
                $return .= (URL::getItemUrl($item, 2) . PHP_EOL);
            }
        }
        // ssr
        $return .= URL::getAllUrl($user, 0, 0, 0) . PHP_EOL;

        return Tools::base64_url_encode($return);
    }

    /**
     * Kitsunebi 订阅
     *
     * @param object $user 用户
     *
     * @return string
     */
    public static function getKitsunebi($user)
    {
        $return = '';
        // v2ray
        $items = URL::getAllVMessUrl($user, 1);
        foreach ($items as $item) {
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
            }
            $tls = ($item['tls'] == 'tls' || $item['net'] == 'tls'
                ? '&tls=1'
                : '&tls=0');
            $mux = '&mux=1&muxConcurrency=8';
            $return .= ('vmess://' . base64_encode(
                'auto:' . $item['id'] .
                    '@' . $item['add'] . ':' . $item['port']
            ) . '?remark=' . rawurlencode($item['ps']) .
                $network . $protocol .
                '&aid=' . $item['aid']
                . $tls . $mux . PHP_EOL);
        }
        // ss
        $items = array_merge(
            URL::getAllItems($user, 0, 1, 0),
            URL::getAllItems($user, 1, 1, 0)
        );
        foreach ($items as $item) {
            if ($item['obfs'] == 'plain') {
                $return .= (URL::getItemUrl($item, 2) . PHP_EOL);
            }
        }

        return base64_encode($return);
    }

    /**
     * 通用订阅，ssr & v2rayn
     *
     * @param object $user 用户
     * @param int    $sub  订阅类型
     * @param array  $opts request
     *
     * @return string
     */
    public static function getSub($user, $sub, $opts)
    {
        $extend = isset($opts['extend']) ? $opts['extend'] : 0;
        $getV2rayPlugin = 1;
        $return_url = '';
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Kitsunebi') !== false) {
            $getV2rayPlugin = 0;
        }
        switch ($sub) {
            case 1: // SSR
                $return_url .= $extend == 0 ? '' : URL::getUserTraffic($user, 1) . PHP_EOL;
                $return_url .= URL::getAllUrl($user, 0, 0, $getV2rayPlugin) . PHP_EOL;
                break;
            case 2: // SS
                $return_url .= $extend == 0 ? '' : URL::getUserTraffic($user, 2) . PHP_EOL;
                $return_url .= URL::getAllUrl($user, 0, 1, $getV2rayPlugin) . PHP_EOL;
                break;
            case 3: // V2
                $return_url .= $extend == 0 ? '' : URL::getUserTraffic($user, 3) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user) . PHP_EOL;
                break;
            case 4: // V2 + SS
                $return_url .= $extend == 0 ? '' : URL::getUserTraffic($user, 3) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user) . PHP_EOL;
                $return_url .= URL::getAllUrl($user, 0, 1, $getV2rayPlugin) . PHP_EOL;
                break;
            case 5: // V2 + SS + SSR
                $return_url .= $extend == 0 ? '' : URL::getUserTraffic($user, 1) . PHP_EOL;
                $return_url .= URL::getAllVMessUrl($user) . PHP_EOL;
                $return_url .= URL::getAllUrl($user, 0, 0, $getV2rayPlugin) . PHP_EOL;
                $return_url .= URL::getAllUrl($user, 0, 1, $getV2rayPlugin) . PHP_EOL;
                break;
        }
        return Tools::base64_url_encode($return_url);
    }
}
