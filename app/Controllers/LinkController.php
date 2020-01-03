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

        $emoji = ((isset($opts['emoji']) && $opts['emoji'] == '1') || Config::get('add_emoji_to_node_name') === true
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
            'quantumultx' => ['filename' => 'QuantumultX.conf', 'class' => 'QuantumultX'],
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

        // 请求路径以及查询参数
        $path = ($request->getUri()->getPath() . $request->getUri()->getQuery());

        $getBody = '';
        foreach ($sub_type_array as $key => $value) {
            if ($key != 'sub' && isset($opts[$key])) {
                $int = (int) $opts[$key];
                $class = ('get' . $value['class']);
                if ($int >= 1) {
                    $Cache = false;
                    if (Config::get('enable_sub_cache') === true) {
                        $Cache = true;
                        $content = self::getSubscribeCache($user, $path);
                        if ($content === false) {
                            $Cache = false;
                            $content = self::$class($user, $int, $opts, $Rule, $find, $emoji);
                        }
                        self::SubscribeCache($user, $path, $content);
                    } else {
                        $content = self::$class($user, $int, $opts, $Rule, $find, $emoji);
                    }
                    $getBody = self::getBody(
                        $user,
                        $response,
                        $content,
                        $value['filename'],
                        $Cache
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
            $Cache = false;
            if (Config::get('enable_sub_cache') === true) {
                $Cache = true;
                $content = self::getSubscribeCache($user, $path);
                if ($content === false) {
                    $Cache = false;
                    $content = self::getSub($user, $int, $opts, $Rule, $find, $emoji);
                }
                self::SubscribeCache($user, $path, $content);
            } else {
                $content = self::getSub($user, $int, $opts, $Rule, $find, $emoji);
            }
            $getBody = self::getBody(
                $user,
                $response,
                $content,
                $value['filename'],
                $Cache
            );
        }

        // 记录订阅日志
        if (Config::get('subscribeLog') === true) {
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
     * @return string
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
            'clashr'          => '?clash=2',
            'surge'           => '?surge=' . $int,
            'surge_node'      => '?surge=1',
            'surge2'          => '?surge=2',
            'surge3'          => '?surge=3',
            'surge4'          => '?surge=4',
            'surfboard'       => '?surfboard=1',
            'quantumult'      => '?quantumult=' . $int,
            'quantumult_v2'   => '?quantumult=1',
            'quantumult_sub'  => '?quantumult=2',
            'quantumult_conf' => '?quantumult=3',
            'shadowrocket'    => '?shadowrocket=1',
            'kitsunebi'       => '?kitsunebi=1'
        ];

        return array_map(
            function($item) use ($userapiUrl) {
                return ($userapiUrl . $item);
            },
            $return_info
        );
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
        if (in_array($surge, [1, 4])) {
            $items = array_merge(
                URL::getAllItems($user, 0, 1, $emoji),
                URL::getAllItems($user, 1, 1, $emoji),
                URL::getAllVMessUrl($user, 1, $emoji)
            );
        } else {
            $items = array_merge(
                URL::getAllItems($user, 0, 1, $emoji),
                URL::getAllItems($user, 1, 1, $emoji)
            );
        }
        $All_Proxy = '';
        foreach ($items as $item) {
            if ($find) {
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) continue;
            }
            $All_Proxy .= AppURI::getSurgeURI($item, $surge) . PHP_EOL;
        }
        if (!$source && $surge == 1) {
            return $All_Proxy;
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
                $items = array_merge(
                    URL::getAllItems($user, 0, 1, $emoji),
                    URL::getAllItems($user, 1, 1, $emoji),
                    URL::getAllItems($user, 0, 0, $emoji),
                    URL::getAllItems($user, 1, 0, $emoji),
                    URL::getAllVMessUrl($user, 1, $emoji)
                );
                break;
            default:
                $items = URL::getAllVMessUrl($user, 1, $emoji);
                $extend = isset($opts['extend']) ? $opts['extend'] : 0;
                $All_Proxy = ($extend == 0 ? '' : URL::getUserInfo($user, 'quantumult_v2', 0) . PHP_EOL);
                foreach ($items as $item) {
                    if ($find) {
                        $item = ConfController::getMatchProxy($item, $Rule);
                        if ($item === null) continue;
                    }
                    $All_Proxy .= 'vmess://' . base64_encode(AppURI::getQuantumultURI($item)) . PHP_EOL;
                }
                return base64_encode($All_Proxy);
                break;
        }

        $All_Proxy          = '';
        $All_Proxy_name     = '';
        $BackChina_name     = '';
        foreach ($items as $item) {
            if ($find) {
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) continue;
            }
            $All_Proxy .= AppURI::getQuantumultURI($item) . PHP_EOL;
            if (strpos($item['remark'], '回国') || strpos($item['remark'], 'China')) {
                $BackChina_name .= "\n" . $item['remark'];
            } else {
                $All_Proxy_name .= "\n" . $item['remark'];
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
    public static function getQuantumultX($user, $quantumultx, $opts, $Rule, $find, $emoji)
    {
        switch ($quantumultx) {
            default:
                $items = array_merge(
                    URL::getAllItems($user, 0, 1, $emoji),
                    URL::getAllItems($user, 1, 1, $emoji),
                    URL::getAllItems($user, 0, 0, $emoji),
                    URL::getAllItems($user, 1, 0, $emoji),
                    URL::getAllVMessUrl($user, 1, $emoji)
                );
                $All_Proxy = '';
                foreach ($items as $item) {
                    if ($find) {
                        $item = ConfController::getMatchProxy($item, $Rule);
                        if ($item === null) continue;
                    }
                    $All_Proxy .= AppURI::getQuantumultXURI($item) . PHP_EOL;
                }
                return $All_Proxy;
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
    public static function getSurfboard($user, $surfboard, $opts, $Rule, $find, $emoji)
    {
        $subInfo = self::getSubinfo($user, 0);
        $userapiUrl = $subInfo['surfboard'];
        $All_Proxy = '';
        $items = array_merge(
            URL::getAllItems($user, 0, 1, $emoji),
            URL::getAllItems($user, 1, 1, $emoji)
        );
        foreach ($items as $item) {
            $All_Proxy .= AppURI::getSurfboardURI($item) . PHP_EOL;
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
        if ($clash == 2) {
            $items = array_merge(
                URL::getAllItems($user, 0, 1, $emoji),
                URL::getAllItems($user, 1, 1, $emoji),
                URL::getAllItems($user, 0, 0, $emoji),
                URL::getAllItems($user, 1, 0, $emoji),
                URL::getAllV2RayPluginItems($user, $emoji),
                URL::getAllVMessUrl($user, 1, $emoji)
            );
        } else {
            $items = array_merge(
                URL::getAllItems($user, 0, 1, $emoji),
                URL::getAllItems($user, 1, 1, $emoji),
                URL::getAllV2RayPluginItems($user, $emoji),
                URL::getAllVMessUrl($user, 1, $emoji)
            );
        }
        $Proxys = [];
        foreach ($items as $item) {
            $Proxy = AppURI::getClashURI($item);
            if (isset($opts['source']) && $opts['source'] != '') {
                $Proxy['class'] = $item['class'];
            }
            $Proxys[] = $Proxy;
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
    public static function getKitsunebi($user, $kitsunebi, $opts, $Rule, $find, $emoji)
    {
        $return = '';

        // 账户到期时间以及流量信息
        $extend = isset($opts['extend']) ? (int) $opts['extend'] : 0;
        $return .= $extend == 0 ? '' : URL::getUserInfo($user, 'ss', 1) . PHP_EOL;

        if (URL::SSCanConnect($user) && !in_array($user->obfs, ['simple_obfs_http', 'simple_obfs_tls'])) {
            $user = URL::getSSConnectInfo($user);
            $user->obfs = 'plain';
            $items = array_merge(
                URL::getAllItems($user, 0, 1, $emoji),
                URL::getAllVMessUrl($user, 1, $emoji)
            );
        } else {
            $items = URL::getAllVMessUrl($user, 1, $emoji);
        }
        foreach ($items as $item) {
            if ($find) {
                $item = ConfController::getMatchProxy($item, $Rule);
                if ($item === null) continue;
            }
            $return .= AppURI::getKitsunebiURI($item) . PHP_EOL;
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
