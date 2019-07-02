<?php

/**
 * 部分应用自定义配置
 *
 * PHP version 7.2+
 *
 * @category GeekQu
 * @package  App/Controllers/ConfController
 * @author   GeekQu <iloves@live.com>
 * @license  MIT https://github.com/GeekQu/ss-panel-v3-mod_Uim/blob/dev/LICENSE
 * @link     https://github.com/GeekQu
 */

namespace App\Controllers;

use App\Models\User;
use App\Services\Config;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * ConfController
 *
 * @category GeekQu
 * @package  App/Controllers/ConfController
 * @author   GeekQu <iloves@live.com>
 * @license  MIT https://github.com/GeekQu/ss-panel-v3-mod_Uim/blob/dev/LICENSE
 * @link     https://github.com/GeekQu
 */
class ConfController extends BaseController
{

    /**
     *  从远端自定义配置文件生成 Surge 托管配置
     *
     * @param object $User          用户
     * @param string $AllProxys     Surge 格式的全部节点
     * @param array  $Nodes         全部节点数组
     * @param string $SourceContent 远程配置内容
     *
     * @return string
     */
    public static function getSurgeConfs($User, $AllProxys, $Nodes, $SourceContent)
    {
        try {
            $Configs = Yaml::parse($SourceContent);
        } catch (ParseException $exception) {
            return printf('无法解析 YAML 字符串: %s', $exception->getMessage());
        }
        $General = self::getSurgeConfGeneral($Configs['General']);
        $Proxys = (isset($Configs['Proxy'])
            ? self::getSurgeConfProxy($Configs['Proxy'])
            : '');
        if (isset($Configs['ProxyGroup'])) {
            $ProxyGroup = self::getSurgeConfProxyGroup(
                $Nodes,
                $Configs['ProxyGroup']
            );
        } else {
            $ProxyGroup = self::getSurgeConfProxyGroup(
                $Nodes,
                $Configs['Proxy Group']
            );
        }
        $Rule = self::getSurgeConfRule($Configs['Rule']);
        $Conf = '#!MANAGED-CONFIG '
            . Config::get('baseUrl') . $_SERVER['REQUEST_URI'] .
            "\n\n#---------------------------------------------------#" .
            "\n## 上次更新于：" . date("Y-m-d h:i:s") .
            "\n#---------------------------------------------------#" .
            "\n\n[General]"
            . $General .
            "\n\n[Proxy]\n"
            . $AllProxys . $Proxys .
            "\n\n[Proxy Group]"
            . $ProxyGroup .
            "\n\n[Rule]\n"
            . $Rule;

        return $Conf;
    }

    /**
     * Surge 配置中的 General
     *
     * @param array $General Surge General 定义
     *
     * @return string
     */
    public static function getSurgeConfGeneral($General)
    {
        $return = '';
        if (count($General) != 0) {
            foreach ($General as $key => $value) {
                $return .= "\n$key = $value";
            }
        }
        return $return;
    }

    /**
     * Surge 配置中的 Proxy
     *
     * @param array $Proxys 自定义配置中的额外 Proxy
     *
     * @return string
     */
    public static function getSurgeConfProxy($Proxys)
    {
        $return = '';
        if (count($Proxys) != 0) {
            foreach ($Proxys as $value) {
                if (!preg_match('/(\[General|Replica|Proxy|Proxy\sGroup|Rule|Host|URL\sRewrite|Header\sRewrite|MITM|Script\])/', $value)) {
                    $return .= "\n$value";
                }
            }
        }
        return $return;
    }

    /**
     * Surge 配置中的 ProxyGroup
     *
     * @param array $Nodes       全部节点数组
     * @param array $ProxyGroups Surge 策略组定义
     *
     * @return string
     */
    public static function getSurgeConfProxyGroup($Nodes, $ProxyGroups)
    {
        $return = '';
        foreach ($ProxyGroups as $ProxyGroup) {
            $str = '';
            if (in_array($ProxyGroup['type'], ['select', 'url-test', 'fallback'])) {
                $proxies = [];
                if (
                    isset($ProxyGroup['content']['left-proxies'])
                    && count($ProxyGroup['content']['left-proxies']) != 0
                ) {
                    $proxies = $ProxyGroup['content']['left-proxies'];
                }
                foreach ($Nodes as $item) {
                    switch (true) {
                        case (isset($ProxyGroup['content']['class'])):
                            if ($item['class'] == $ProxyGroup['content']['class'] && !in_array($item['remark'], $proxies)) {
                                if (isset($ProxyGroup['content']['regex'])) {
                                    if (preg_match($ProxyGroup['content']['regex'], $item['remark'])) {
                                        $proxies[] = $item['remark'];
                                    }
                                } else {
                                    $proxies[] = $item['remark'];
                                }
                            }
                            break;
                        case (isset($ProxyGroup['content']['noclass'])):
                            if ($item['class'] != $ProxyGroup['content']['noclass'] && !in_array($item['remark'], $proxies)) {
                                if (isset($ProxyGroup['content']['regex'])) {
                                    if (preg_match($ProxyGroup['content']['regex'], $item['remark'])) {
                                        $proxies[] = $item['remark'];
                                    }
                                } else {
                                    $proxies[] = $item['remark'];
                                }
                            }
                            break;
                        case (!isset($ProxyGroup['content']['class'])
                            && !isset($ProxyGroup['content']['noclass'])
                            && isset($ProxyGroup['content']['regex'])
                            && preg_match($ProxyGroup['content']['regex'], $item['remark'])
                            && !in_array($item['remark'], $proxies)):
                            $proxies[] = $item['remark'];
                            break;
                        default:
                            continue;
                            break;
                    }
                }
                if (isset($ProxyGroup['content']['right-proxies'])) {
                    $proxies = array_merge($proxies, $ProxyGroup['content']['right-proxies']);
                }
                $Remarks = implode(', ', $proxies);
                if (in_array($ProxyGroup['type'], ['url-test', 'fallback'])) {
                    $str .= ($ProxyGroup['name']
                        . ' = '
                        . $ProxyGroup['type']
                        . ', '
                        . $Remarks
                        . ', url = ' . $ProxyGroup['url']
                        . ', interval = ' . $ProxyGroup['interval']);
                } else {
                    $str .= ($ProxyGroup['name']
                        . ' = '
                        . $ProxyGroup['type']
                        . ', '
                        . $Remarks);
                }
            } elseif ($ProxyGroup['type'] == 'ssid') {
                $wifi = '';
                foreach ($ProxyGroup['content'] as $key => $value) {
                    $wifi .= ', "' . $key . '" = ' . $value;
                }
                $cellular = (isset($ProxyGroup['cellular'])
                    ? ', cellular = ' . $ProxyGroup['cellular']
                    : '');
                $str .= ($ProxyGroup['name']
                    . ' = '
                    . $ProxyGroup['type']
                    . ', default = '
                    . $ProxyGroup['default']
                    . $cellular
                    . $wifi);
            } else {
                $str .= '';
            }
            $return .= "\n$str";
        }
        return $return;
    }

    /**
     * Surge 配置中的 Rule
     *
     * @param array $Rules Surge 规则加载地址
     *
     * @return string
     */
    public static function getSurgeConfRule($Rules)
    {
        $return = '';
        if (isset($Rules['source']) && $Rules['source'] != '') {
            $sourceURL = trim($Rules['source']);
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $sourceURL)) {
                $return = @file_get_contents($sourceURL);
                if (!$return) {
                    $return = ('// 远程规则加载失败'
                        . PHP_EOL
                        . 'GEOIP,CN,DIRECT'
                        . PHP_EOL
                        . 'FINAL,DIRECT,dns-failed');
                }
            } else {
                $return = ('// 远程规则仅支持 github 以及 gitlab'
                    . PHP_EOL
                    . 'GEOIP,CN,DIRECT'
                    . PHP_EOL
                    . 'FINAL,DIRECT,dns-failed');
            }
        }
        return $return;
    }

    /**
     * 从远端自定义配置文件生成 Clash 配置
     *
     * @param object $User          用户
     * @param array  $AllProxys     全部节点数组
     * @param string $SourceContent 远程配置内容
     *
     * @return string
     */
    public static function getClashConfs($User, $AllProxys, $SourceContent)
    {
        try {
            $Configs = Yaml::parse($SourceContent);
        } catch (ParseException $exception) {
            return printf('无法解析 YAML 字符串: %s', $exception->getMessage());
        }
        if (isset($Configs['Proxy']) || count($Configs['Proxy']) != 0) {
            $tmpProxys = array_merge($AllProxys, $Configs['Proxy']);
        } else {
            $tmpProxys = $AllProxys;
        }
        $Proxys = [];
        foreach ($tmpProxys as $Proxy) {
            unset($Proxy['class']);
            $Proxys[] = $Proxy;
        }
        $tmp = self::getClashConfGeneral($Configs['General']);
        $tmp['Proxy'] = $Proxys;
        if (isset($Configs['ProxyGroup'])) {
            $tmp['Proxy Group'] = self::getClashConfProxyGroup(
                $AllProxys,
                $Configs['ProxyGroup']
            );
        } else {
            $tmp['Proxy Group'] = self::getClashConfProxyGroup(
                $AllProxys,
                $Configs['Proxy Group']
            );
        }
        $Conf = '#!MANAGED-CONFIG '
            . Config::get('baseUrl') . $_SERVER['REQUEST_URI'] .
            "\n\n#---------------------------------------------------#" .
            "\n## 上次更新于：" . date("Y-m-d h:i:s") .
            "\n#---------------------------------------------------#" .
            "\n\n"
            . Yaml::dump($tmp, 4, 2) .
            "\n\nRule:\n"
            . self::getClashConfRule($Configs['Rule']);

        return $Conf;
    }

    /**
     * Clash 配置中的 General
     *
     * @param array $General Clash General 定义
     *
     * @return array
     */
    public static function getClashConfGeneral($General)
    {
        if (count($General) != 0) {
            foreach ($General as $key => $value) {
                if (!in_array(
                    $key,
                    [
                        'port',
                        'socks-port',
                        'redir-port',
                        'allow-lan',
                        'mode',
                        'log-level',
                        'external-controller',
                        'external-ui',
                        'secret',
                        'experimental',
                        'dns'
                    ]
                )) {
                    unset($key);
                }
            }
        }
        return $General;
    }

    /**
     * Clash 配置中的 ProxyGroup
     *
     * @param array $Nodes       全部节点数组
     * @param array $ProxyGroups Clash 策略组定义
     *
     * @return array
     */
    public static function getClashConfProxyGroup($Nodes, $ProxyGroups)
    {
        $return = [];
        foreach ($ProxyGroups as $ProxyGroup) {
            $tmp = [];
            if (in_array($ProxyGroup['type'], ['select', 'url-test', 'fallback', 'load-balance'])) {
                $proxies = [];
                if (
                    isset($ProxyGroup['content']['left-proxies'])
                    && count($ProxyGroup['content']['left-proxies']) != 0
                ) {
                    $proxies = $ProxyGroup['content']['left-proxies'];
                }
                foreach ($Nodes as $item) {
                    switch (true) {
                        case (isset($ProxyGroup['content']['class'])):
                            if ($item['class'] == $ProxyGroup['content']['class'] && !in_array($item['name'], $proxies)) {
                                if (isset($ProxyGroup['content']['regex'])) {
                                    if (preg_match($ProxyGroup['content']['regex'], $item['name'])) {
                                        $proxies[] = $item['name'];
                                    }
                                } else {
                                    $proxies[] = $item['name'];
                                }
                            }
                            break;
                        case (isset($ProxyGroup['content']['noclass'])):
                            if ($item['class'] != $ProxyGroup['content']['noclass'] && !in_array($item['name'], $proxies)) {
                                if (isset($ProxyGroup['content']['regex'])) {
                                    if (preg_match($ProxyGroup['content']['regex'], $item['name'])) {
                                        $proxies[] = $item['name'];
                                    }
                                } else {
                                    $proxies[] = $item['name'];
                                }
                            }
                            break;
                        case (!isset($ProxyGroup['content']['class'])
                            && !isset($ProxyGroup['content']['noclass'])
                            && isset($ProxyGroup['content']['regex'])
                            && preg_match($ProxyGroup['content']['regex'], $item['name'])
                            && !in_array($item['name'], $proxies)):
                            $proxies[] = $item['name'];
                            break;
                        default:
                            continue;
                            break;
                    }
                }
                if (isset($ProxyGroup['content']['right-proxies'])) {
                    $proxies = array_merge($proxies, $ProxyGroup['content']['right-proxies']);
                }
                $tmp = [
                    'name' => $ProxyGroup['name'],
                    'type' => $ProxyGroup['type'],
                    'proxies' => $proxies
                ];
                if ($ProxyGroup['type'] != 'select') {
                    $tmp['url'] = $ProxyGroup['url'];
                    $tmp['interval'] = $ProxyGroup['interval'];
                }
                $return[] = $tmp;
            }
        }
        return $return;
    }

    /**
     * Clash 配置中的 Rule
     *
     * @param array $Rules Clash 规则加载地址
     *
     * @return string
     */
    public static function getClashConfRule($Rules)
    {
        $return = '';
        if (isset($Rules['source']) && $Rules['source'] != '') {
            $sourceURL = trim($Rules['source']);
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $sourceURL)) {
                $return = @file_get_contents($sourceURL);
                if (!$return) {
                    $return = ('// 远程规则加载失败'
                        . PHP_EOL
                        . 'GEOIP,CN,DIRECT'
                        . PHP_EOL
                        . 'MATCH,DIRECT');
                }
            } else {
                $return = ('// 远程规则仅支持 github 以及 gitlab'
                    . PHP_EOL
                    . 'GEOIP,CN,DIRECT'
                    . PHP_EOL
                    . 'MATCH,DIRECT');
            }
        }
        return $return;
    }

    // 待续 Quantumult...
}
