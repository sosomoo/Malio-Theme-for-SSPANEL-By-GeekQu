<?php

//Thanks to http://blog.csdn.net/jollyjumper/article/details/9823047

namespace App\Controllers;

use App\Models\{Link, User, UserSubscribeLog, Smartline};
use App\Utils\{URL, Tools, AppURI, ConfRender};
use App\Services\{Config, AppsProfiles};
use Ramsey\Uuid\Uuid;

/**
 *  LinkController
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
        $Elink = Link::where('type', 11)->where('token', '=', $token)->first();
        if ($Elink == null) {
            return null;
        }

        $user = User::where('id', $Elink->userid)->first();
        if ($user == null) {
            return null;
        }

        $opts = $request->getQueryParams();

        // 筛选节点部分
        $find = false;
        $Rule['type'] = (isset($opts['type']) ? trim($opts['type']) : 'all');
        $Rule['is_mu'] = (Config::get('mergeSub') === true ? 1 : 0);
        if (isset($opts['mu'])) $Rule['is_mu'] = (int) $opts['mu'];

        if (isset($opts['class'])) {
            $class = trim(urldecode($opts['class']));
            $Rule['content']['class'] = array_map(
                function($item) {
                    return (int) $item;
                },
                explode('+', $class)
            );
            $find = true;
        }
        if (isset($opts['noclass'])) {
            $noclass = trim(urldecode($opts['noclass']));
            $Rule['content']['noclass'] = array_map(
                function($item) {
                    return (int) $item;
                },
                explode('+', $noclass)
            );
            $find = true;
        }
        if (isset($opts['regex'])) {
            $Rule['content']['regex'] = trim(urldecode($opts['regex']));
            $find = true;
        }

        // Emoji
        $Rule['emoji'] = Config::get('add_emoji_to_node_name');
        if (isset($opts['emoji'])) $Rule['emoji'] = (bool) $opts['emoji'];
        // 显示流量以及到期时间等
        $Rule['extend'] = Config::get('enable_sub_extend');
        if (isset($opts['extend'])) $Rule['extend'] = (bool) $opts['extend'];

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
            'list'          => ['filename' => 'node.txt', 'class' => 'Lists'],
            'clash'         => ['filename' => 'config.yaml', 'class' => 'Clash'],
            'kitsunebi'     => ['filename' => 'Kitsunebi.txt', 'class' => 'Kitsunebi'],
            'ssd'           => ['filename' => 'SSD.txt', 'class' => 'SSD'],
            'surge'         => ['filename' => 'Surge.conf', 'class' => 'Surge'],
            'surfboard'     => ['filename' => 'Surfboard.conf', 'class' => 'Surfboard'],
            'shadowrocket'  => ['filename' => 'Shadowrocket.txt', 'class' => 'Shadowrocket'],
            'quantumult'    => ['filename' => 'Quantumult.conf', 'class' => 'Quantumult'],
            'quantumultx'   => ['filename' => 'QuantumultX.conf', 'class' => 'QuantumultX'],
            'sub'           => ['filename' => 'node.txt', 'class' => 'Sub']
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

        // 请求路径以及查询参数
        $path = ($request->getUri()->getPath() . $request->getUri()->getQuery());

        $getBody = '';
        
        foreach ($sub_type_array as $key => $value) {
            if (isset($opts[$key])) {
                $query_value = $opts[$key];
                if ($query_value != '0' && $query_value != '') {
                    // 兼容代码开始
                    if ($key == 'sub' && $query_value > 6) {
                        $query_value = 1;
                    }
                    if ($key == 'surge' && $query_value == '1') {
                        $value['class'] = 'Lists';
                        $query_value = 'surge';
                    }
                    if ($key == 'quantumult' && $query_value == '1') {
                        $value['class'] = 'Lists';
                        $query_value = 'quantumult';
                    }
                    // 兼容代码结束
                    $Cache = false;
                    $class = ('get' . $value['class']);
                    if (Config::get('enable_sub_cache') === true) {
                        $Cache = true;
                        $content = self::getSubscribeCache($user, $path);
                        if ($content === false) {
                            $Cache = false;
                            $content = self::$class($user, $query_value, $opts, $Rule, $find);
                        }
                        self::SubscribeCache($user, $path, $content);
                    } else {
                        $content = self::$class($user, $query_value, $opts, $Rule, $find);
                    }
                    if ($sub_type_array[$key]['class'] != $value['class']) {
                        $filename = $sub_type_array[$query_value]['filename'];
                    } else {
                        $filename = $value['filename'];
                    }
                    if (in_array($query_value, ['ssa'])) {
                        $filename = 'node_' . time() . '.json';
                    }
                    if (in_array($query_value, ['clash', 'clashr'])) {
                        $filename = $sub_type_array['clash']['filename'];
                    }
                    $getBody = self::getBody(
                        $user,
                        $response,
                        $content,
                        $filename,
                        $Cache
                    );
                    if ($key == 'sub') {
                        $subscribe_type = $sub_int_type[$query_value];
                    } else {
                        $subscribe_type = ($value['class'] == 'Lists' ? ucfirst($query_value) : $value['class']);
                    }
                    break;
                }
                continue;
            }
        }

        // 记录订阅日志
        if (Config::get('subscribeLog') === true && $getBody != '') {
            self::Subscribe_log($user, $subscribe_type, $request->getHeaderLine('User-Agent'));
        }

        return $getBody;
    }

    /**
     * 获取订阅文件缓存
     *
     * @param object $user 用户
     * @param string $path 路径以及查询参数
     *
     */
    private static function getSubscribeCache($user, $path)
    {
        $user_path = (BASE_PATH . '/storage/SubscribeCache/' . $user->id . '/');
        if (!is_dir($user_path)) mkdir($user_path);
        $user_path_hash = ($user_path . Uuid::uuid3(Uuid::NAMESPACE_DNS, $path)->toString());
        if (!is_file($user_path_hash)) return false;
        $filemtime = filemtime($user_path_hash);
        if ($filemtime === false) {
            unlink($user_path_hash);
            return false;
        }
        if ((time() - $filemtime) >= (Config::get('sub_cache_time') * 60)) {
            unlink($user_path_hash);
            return false;
        }

        return file_get_contents($user_path_hash);
    }

    /**
     * 订阅文件写入缓存
     *
     * @param object $user 用户
     * @param string $path 路径以及查询参数
     *
     */
    private static function SubscribeCache($user, $path, $content)
    {
        $user_path = (BASE_PATH . '/storage/SubscribeCache/' . $user->id . '/');
        if (!is_dir($user_path)) mkdir($user_path);
        $number = 0;
        $files = glob($user_path . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $number++;
            }
        }
        if ($number >= Config::get('sub_cache_max_quantity') + 1) {
            Tools::delDirAndFile($user_path);
        }
        $user_path_hash = ($user_path . Uuid::uuid3(Uuid::NAMESPACE_DNS, $path)->toString());
        $file = fopen($user_path_hash, 'wb');
        fwrite($file, $content);
        fclose($file);
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
    public static function getBody($user, $response, $content, $filename, $Cache)
    {
        $CacheInfo = ($Cache === true ? 'HIT from Disktank' : 'MISS');
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
                'X-Cache',
                ' ' . $CacheInfo
            )
            ->withHeader(
                'Subscription-Userinfo',
                (' upload=' . $user->u
                    . '; download=' . $user->d
                    . '; total=' . $user->transfer_enable
                    . '; expire=' . strtotime($user->class_expire))
            );
        $newResponse->write($content);

        return $newResponse;
    }

    /**
     * 订阅链接汇总
     *
     * @param object $user 用户
     * @param int    $int  当前用户访问的订阅类型
     *
     * @return array
     */
    public static function getSubinfo($user, $int = 0)
    {
        if ($int == 0) {
            $int = '';
        }
        $userapiUrl = Config::get('subUrl') . self::GenerateSSRSubCode($user->id, 0);
        $return_info = [
            'link'            => '',
            // sub
            'ss'              => '?sub=2',
            'ssr'             => '?sub=1',
            'v2ray'           => '?sub=3',
            'v2ray_ss'        => '?sub=4',
            'v2ray_ss_ssr'    => '?sub=5',
            // apps
            'ssd'             => '?ssd=1',
            'clash'           => '?clash=1',
            'clash_provider'  => '?list=clash',
            'clashr'          => '?clash=2',
            'clashr_provider' => '?list=clashr',
            'surge'           => '?surge=' . $int,
            'surge_node'      => '?list=surge',
            'surge2'          => '?surge=2',
            'surge3'          => '?surge=3',
            'surge4'          => '?surge=4',
            'surfboard'       => '?surfboard=1',
            'quantumult'      => '?quantumult=' . $int,
            'quantumult_v2'   => '?list=quantumult',
            'quantumult_sub'  => '?quantumult=2',
            'quantumult_conf' => '?quantumult=3',
            'quantumultx'     => '?list=quantumultx',
            'shadowrocket'    => '?list=shadowrocket',
            'kitsunebi'       => '?list=kitsunebi'
        ];

        return array_map(
            function($item) use ($userapiUrl) {
                return ($userapiUrl . $item);
            },
            $return_info
        );
    }

    public static function getListItem($item, $list)
    {
        $return = null;
        switch ($list) {
            case 'ssa':
                $return = AppURI::getSSJSON($item);
                break;
            case 'surge':
                $return = AppURI::getSurgeURI($item, 3);
                break;
            case 'clash':
                $return = AppURI::getClashURI($item);
                break;
            case 'clashr':
                $return = AppURI::getClashURI($item, true);
                break;
            case 'kitsunebi':
                $return = AppURI::getKitsunebiURI($item);
                break;
            case 'quantumult':
                $return = AppURI::getQuantumultURI($item, true);
                break;
            case 'quantumultx':
                $return = AppURI::getQuantumultXURI($item);
                break;
            case 'shadowrocket':
                $return = AppURI::getShadowrocketURI($item);
                break;
        }
        return $return;
    }

    public static function getLists($user, $list, $opts, $Rule, $find)
    {
        $list = strtolower($list);
        if ($list == 'ssd') {
            return self::getSSD($user, 1, $opts, $Rule, false);
        }
        if ($list == 'ssa') {
            $Rule['type'] = 'ss';
        }
        if ($list == 'quantumult') {
            $Rule['type'] = 'vmess';
        }
        $items = URL::getNew_AllItems($user, $Rule);
        $return = [];
        if ($Rule['extend'] === true) {
            switch ($list) {
                case 'ssa':
                case 'clash':
                case 'clashr':
                    $return = array_merge($return, self::getListExtend($user, $list));
                    break;
                default:
                    $return[] = implode(PHP_EOL, self::getListExtend($user, $list));
                    break;
            }
        }
        foreach ($items as $item) {
            $out = self::getListItem($item, $list);
            if ($out != null) {
                $return[] = $out;
            }
        }
        switch ($list) {
            case 'ssa':
                return json_encode($return, 320);
                break;
            case 'clash':
            case 'clashr':
                return \Symfony\Component\Yaml\Yaml::dump(['proxies' => $return], 4, 2);
            case 'kitsunebi':
            case 'quantumult':
            case 'shadowrocket':
                return base64_encode(implode(PHP_EOL, $return));
            default:
                return implode(PHP_EOL, $return);
        }        
    }

    public static function getListExtend($user, $list)
    {
        $return = [];
        $info_array = (count(Config::get('sub_message')) != 0 ? (array) Config::get('sub_message') : []);
        if (strtotime($user->expire_in) > time()) {
            if ($user->transfer_enable == 0) {
                $unusedTraffic = '剩余流量：0';
            } else {
                $unusedTraffic = '剩余流量：' . $user->unusedTraffic();
            }
            $expire_in = '过期时间：';
            if ($user->class_expire != '1989-06-04 00:05:00') {
                $userClassExpire = explode(' ', $user->class_expire);
                $expire_in .= $userClassExpire[0];
            } else {
                $expire_in .= '无限期';
            }
        } else {
            $unusedTraffic  = '账户已过期，请续费后使用';
            $expire_in      = '账户已过期，请续费后使用';
        }
        if (!in_array($list, ['quantumult', 'quantumultx', 'shadowrocket'])) {
            $info_array[] = $unusedTraffic;
            $info_array[] = $expire_in;
        }
        $baseUrl = explode('//', Config::get('baseUrl'))[1];
        $Extend_ss = [
            'remark'    => '',
            'type'      => 'ss',
            'address'   => $baseUrl,
            'port'      => 10086,
            'method'    => 'chacha20-ietf-poly1305',
            'passwd'    => 'WWW.GOV.CN',
            'obfs'      => 'plain'
        ];
        $Extend_VMess = [
            'remark'    => '',
            'type'      => 'vmess',
            'add'       => $baseUrl,
            'port'      => 10086,
            'id'        => '2661b5f8-8062-34a5-9371-a44313a75b6b',
            'alterId'   => 0,
            'net'       => 'tcp'
        ];
        if ($list == 'shadowrocket') {
            $return[] = ('STATUS=' . $unusedTraffic . '.♥.' . $expire_in . PHP_EOL . 'REMARKS=' . Config::get('appName'));
        }
        foreach ($info_array as $remark) {
            $Extend_ss['remark']    = $remark;
            $Extend_VMess['remark'] = $remark;
            if (in_array($list, ['kitsunebi', 'quantumult'])) {
                $out = self::getListItem($Extend_VMess, $list);
            } else {
                $out = self::getListItem($Extend_ss, $list);
            }
            if ($out !== null) $return[] = $out;
        }
        return $return;
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
    public static function getSurge($user, int $surge, $opts, $Rule, $find)
    {
        if ($surge == 1) {
            return self::getLists($user, 'surge', $opts, $Rule, $find);
        }
        $subInfo = self::getSubinfo($user, $surge);
        $userapiUrl = $subInfo['surge'];
        $source = (isset($opts['source']) && $opts['source'] != '' ? true : false);
        if ($surge != 4) $Rule['type'] = 'ss';
        $items = URL::getNew_AllItems($user, $Rule);
        $All_Proxy = '';
        foreach ($items as $item) {
            $URI = AppURI::getSurgeURI($item, $surge) . PHP_EOL;
            if ($item !== null) $All_Proxy .= $URI;
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
    public static function getQuantumult($user, $quantumult, $opts, $Rule, $find)
    {
        $emoji = $Rule['emoji'];
        switch ($quantumult) {
            case 2:
                $subUrl = self::getSubinfo($user, 0);
                $str = [
                    '[SERVER]',
                    '',
                    '[SOURCE]',
                    Config::get('appName') . ', server ,' . $subUrl['ssr'] . ', false, true, false',
                    Config::get('appName') . '_ss, server ,' . $subUrl['ss'] . ', false, true, false',
                    Config::get('appName') . '_VMess, server ,' . $subUrl['quantumult_v2'] . ', false, true, false',
                    'Hackl0us Rules, filter, https://raw.githubusercontent.com/Hackl0us/Surge-Rule-Snippets/master/LAZY_RULES/Quantumult.conf, true',
                    '',
                    '[DNS]',
                    'system, 119.29.29.29, 223.6.6.6, 114.114.114.114',
                    '',
                    '[STATE]',
                    'STATE,AUTO'
                ];
                return implode(PHP_EOL, $str);
                break;
            case 3:
                $items = URL::getNew_AllItems($user, $Rule);
                break;
            default:
                return self::getLists($user, 'quantumult', $opts, $Rule, $find);
                break;
        }

        $All_Proxy          = '';
        $All_Proxy_name     = '';
        $BackChina_name     = '';
        foreach ($items as $item) {
            $out = AppURI::getQuantumultURI($item);
            if ($out !== null) {
                $All_Proxy .= $out . PHP_EOL;
                if (strpos($item['remark'], '回国') || strpos($item['remark'], 'China')) {
                    $BackChina_name .= "\n" . $item['remark'];
                } else {
                    $All_Proxy_name .= "\n" . $item['remark'];
                }
            }
        }
        $ProxyGroups = [
            'proxy_group'       => base64_encode("🍃 Proxy  :  static, 🏃 Auto\n🏃 Auto\n🚀 Direct\n" . $All_Proxy_name),
            'domestic_group'    => base64_encode("🍂 Domestic  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy\n" . $BackChina_name),
            'others_group'      => base64_encode("☁️ Others  :   static, 🍃 Proxy\n🚀 Direct\n🍃 Proxy"),
            'direct_group'      => base64_encode("🚀 Direct : static, DIRECT\nDIRECT"),
            'apple_group'       => base64_encode("🍎 Only  :  static, 🚀 Direct\n🚀 Direct\n🍃 Proxy"),
            'auto_group'        => base64_encode("🏃 Auto  :  auto\n" . $All_Proxy_name),
        ];
        $render = ConfRender::getTemplateRender();
        $render->assign('All_Proxy', $All_Proxy)->assign('ProxyGroups', $ProxyGroups);

        return $render->fetch('quantumult.tpl');
    }

    /**
     * QuantumultX 配置
     *
     * @param object $user        用户
     * @param int    $quantumultx 订阅类型
     * @param array  $Rule        节点筛选规则
     * @param bool   $find        是否筛选节点
     *
     * @return string
     */
    public static function getQuantumultX($user, $quantumultx, $opts, $Rule, $find)
    {
        switch ($quantumultx) {
            default:
                return self::getLists($user, 'quantumultx', $opts, $Rule, $find);
                break;
        }
    }

    /**
     * Surfboard 配置
     *
     * @param object $user 用户
     * @param array  $opts request
     *
     * @return string
     */
    public static function getSurfboard($user, $surfboard, $opts, $Rule, $find)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['surfboard'];
        $All_Proxy = '';
        $Rule['type'] = 'ss';
        $items = URL::getNew_AllItems($user, $Rule);
        foreach ($items as $item) {
            $out = AppURI::getSurfboardURI($item);
            if ($out !== null) {
                $All_Proxy .= $out . PHP_EOL;
            }
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
    public static function getClash($user, $clash, $opts, $Rule, $find)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['clash'];
        $ssr_support = ($clash == 2 ? true : false);
        $items = URL::getNew_AllItems($user, $Rule);
        $Proxys = [];
        foreach ($items as $item) {
            $Proxy = AppURI::getClashURI($item, $ssr_support);
            if ($item !== null) {
                if (isset($opts['source']) && $opts['source'] != '') {
                    $Proxy['class'] = $item['class'];
                }
                $Proxys[] = $Proxy;
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
    public static function getSSD($user, $ssd, $opts, $Rule, $find)
    {
        if (!URL::SSCanConnect($user)) {
            return null;
        }
        $array_all                  = [];
        $array_all['airport']       = Config::get('appName');
        $array_all['port']          = $user->port;
        $array_all['encryption']    = $user->method;
        $array_all['password']      = $user->passwd;
        $array_all['traffic_used']  = Tools::flowToGB($user->u + $user->d);
        $array_all['traffic_total'] = Tools::flowToGB($user->transfer_enable);
        $array_all['expiry']        = $user->class_expire;
        $array_all['url']           = self::getSubinfo($user, 0)['ssd'];
        $plugin_options             = '';
        if (strpos($user->obfs, 'http') != false) {
            $plugin_options = 'obfs=http';
        }
        if (strpos($user->obfs, 'tls') != false) {
            $plugin_options = 'obfs=tls';
        }
        if ($plugin_options != '') {
            $array_all['plugin'] = 'simple-obfs';
            $array_all['plugin_options'] = $plugin_options;
            if ($user->obfs_param != '') {
                $array_all['plugin_options'] .= ';obfs-host=' . $user->obfs_param;
            }
        }
        $array_server = [];
        $server_index = 1;
        $Rule['type'] = 'ss';
        $nodes = URL::getNew_AllItems($user, $Rule);
        foreach ($nodes as $item) {
            if ($item['type'] != 'ss') continue;
            $server = AppURI::getSSDURI($item);
            if ($server !== null) {
                $server['id'] = $server_index;
                $array_server[] = $server;
                $server_index++;
            }
        }
        $array_all['servers'] = $array_server;
        $json_all = json_encode($array_all, 320);

        return 'ssd://' . base64_encode($json_all);
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
    public static function getShadowrocket($user, $shadowrocket, $opts, $Rule, $find)
    {
        $Rule['emoji'] = false; // Shadowrocket 自带 emoji
        return self::getLists($user, 'shadowrocket', $opts, $Rule, $find);

        $emoji = false; // Shadowrocket 自带 emoji

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

        if (in_array($user->method, Config::getSupportParam('ss_aead_method')) || in_array($user->obfs, Config::getSupportParam('ss_obfs'))) {
            // 减少因为加密协议混淆同时支持 ss & ssr 而导致订阅出现大量重复节点
            $items = array_merge(
                URL::getAllItems($user, 0, 1),
                URL::getAllItems($user, 1, 1),
                URL::getAllV2RayPluginItems($user),
                URL::getAllVMessUrl($user, 1)
            );
        } else {
            $items = array_merge(
                URL::getAllItems($user, 1, 1),
                URL::getAllV2RayPluginItems($user),
                URL::getAllVMessUrl($user, 1)
            );
        }
        foreach ($items as $item) {
            if ($find) {
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) continue;
            }
            $return .= AppURI::getShadowrocketURI($item) . PHP_EOL;
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
    public static function getKitsunebi($user, $kitsunebi, $opts, $Rule, $find)
    {
        return self::getLists($user, 'kitsunebi', $opts, $Rule, $find);
    }

    public static function getSSPcConf($user)
    {
        $proxy = [];
        $items = array_merge(
            URL::getAllItems($user, 0, 1, 0),
            URL::getAllItems($user, 1, 1, 0),
            URL::getAllV2RayPluginItems($user)
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
            URL::getAllItems($user, 1, 1, 0),
            URL::getAllV2RayPluginItems($user)
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
    public static function getSub($user, $sub, $opts, $Rule, $find)
    {
        $emoji = $Rule['emoji'];
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
