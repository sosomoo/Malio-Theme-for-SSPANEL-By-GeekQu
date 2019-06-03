<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Config;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 *  ConfController
 */
class ConfController extends BaseController
{

    // #------------------------- Surge --------------------------#

    public static function SurgeConfs($User, $AllProxys, $Nodes, $SourceContent)
    {
        try {
            $Configs = Yaml::parse($SourceContent);
        } catch (ParseException $exception) {
            return printf('无法解析 YAML 字符串: %s', $exception->getMessage());
        }
        $General = ConfController::SurgeConfGeneral($Configs['General']);
        $Proxys = isset($Configs['Proxy']) ? ConfController::SurgeConfProxy($Configs['Proxy']) : '';
        if (isset($Configs['ProxyGroup'])) {
            $ProxyGroup = ConfController::SurgeConfProxyGroup(
                $Nodes,
                $Configs['ProxyGroup']
            );
        } else {
            $ProxyGroup = ConfController::SurgeConfProxyGroup(
                $Nodes,
                $Configs['Proxy Group']
            );
        }
        $Rule = ConfController::SurgeConfRule($Configs['Rule']);
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

    public static function SurgeConfGeneral($General)
    {
        $return = '';
        if (count($General) != 0) {
            foreach ($General as $key => $value) {
                $return .= "\n$key = $value";
            }
        }
        return $return;
    }

    public static function SurgeConfProxy($Proxys)
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

    public static function SurgeConfProxyGroup($Nodes, $ProxyGroups)
    {
        $return = '';
        foreach ($ProxyGroups as $ProxyGroup) {
            $str = '';
            if (in_array($ProxyGroup['type'], ['select', 'url-test', 'fallback'])) {
                $AllRemark = [];
                $Remarks = '';
                if (isset($ProxyGroup['content']['class'])) {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == 'v2ray') {
                            continue;
                        }
                        if ($item['class'] == $ProxyGroup['content']['class']) {
                            $AllRemark[] = $item['remark'];
                            $Remarks .= ', ' . $item['remark'];
                        }
                    }
                } elseif (isset($ProxyGroup['content']['noclass'])) {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == 'v2ray') {
                            continue;
                        }
                        if ($item['class'] != $ProxyGroup['content']['noclass']) {
                            $AllRemark[] = $item['remark'];
                            $Remarks .= ', ' . $item['remark'];
                        }
                    }
                } else {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == 'v2ray') {
                            continue;
                        }
                        $AllRemark[] = $item['remark'];
                    }
                }
                if (isset($ProxyGroup['content']['regex'])) {
                    $Remarks = '';
                    foreach ($AllRemark as $item) {
                        if (preg_match($ProxyGroup['content']['regex'], $item)) {
                            $Remarks .= ', ' . $item;
                        }
                    }
                }
                $text1 = isset($ProxyGroup['content']['text1']) && $ProxyGroup['content']['text1'] != '' ? ', ' . $ProxyGroup['content']['text1'] : '';
                $text2 = isset($ProxyGroup['content']['text2']) && $ProxyGroup['content']['text2'] != '' ? ', ' . $ProxyGroup['content']['text2'] : '';
                $url = isset($ProxyGroup['url']) && $ProxyGroup['url'] != '' ? ', url = ' . $ProxyGroup['url'] : '';
                $interval = isset($ProxyGroup['interval']) && $ProxyGroup['interval'] != '' ? ', interval = ' . $ProxyGroup['interval'] : '';
                $str .= $ProxyGroup['name'] . ' = ' . $ProxyGroup['type'] . $text1 . $Remarks . $text2 . $url . $interval;
            } elseif ($ProxyGroup['type'] == 'ssid') {
                $wifi = '';
                foreach ($ProxyGroup['content'] as $key => $value) {
                    $wifi .= ', "' . $key . '" = ' . $value;
                }
                $cellular = isset($ProxyGroup['cellular']) ? ', cellular = ' . $ProxyGroup['cellular'] : '';
                $str .= $ProxyGroup['name'] . ' = ' . $ProxyGroup['type'] . ', default = ' . $ProxyGroup['default'] . $cellular . $wifi;
            } else {
                $str .= '';
            }
            $return .= "\n$str";
        }
        return $return;
    }

    public static function SurgeConfRule($Rules)
    {
        $return = '';
        if (isset($Rules['source']) && $Rules['source'] != '') {
            $sourceURL = trim($Rules['source']);
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $sourceURL)) {
                $return = @file_get_contents($sourceURL);
                if (!$return) {
                    $return = '// 远程规则加载失败'
                    . PHP_EOL .
                    'GEOIP,CN,DIRECT'
                    . PHP_EOL .
                    'FINAL,DIRECT,dns-failed';
                }
            } else {
                $return = '// 远程规则仅支持 github 以及 gitlab'
                . PHP_EOL .
                'GEOIP,CN,DIRECT'
                . PHP_EOL .
                'FINAL,DIRECT,dns-failed';
            }
        }
        return $return;
    }

    // #------------------------- Clash --------------------------#

    public static function ClashConfs($User, $AllProxys, $SourceContent)
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
        $tmp = ConfController::ClashConfGeneral($Configs['General']);
        $tmp['Proxy'] = $Proxys;
        if (isset($Configs['ProxyGroup'])) {
            $tmp['Proxy Group'] = ConfController::ClashConfProxyGroup(
                $AllProxys,
                $Configs['ProxyGroup']
            );
        } else {
            $tmp['Proxy Group'] = ConfController::ClashConfProxyGroup(
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
            . ConfController::ClashConfRule($Configs['Rule']);

        return $Conf;
    }

    public static function ClashConfGeneral($General)
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
                )
                ) {
                    unset($key);
                }
            }
        }
        return $General;
    }

    public static function ClashConfProxyGroup($Nodes, $ProxyGroups)
    {
        $return = [];
        foreach ($ProxyGroups as $ProxyGroup) {
            $tmp = [];
            if (in_array($ProxyGroup['type'], ['select', 'url-test', 'fallback', 'load-balance'])) {
                $proxies = [];
                if (isset($ProxyGroup['content']['left-proxies'])
                    && count($ProxyGroup['content']['left-proxies']) != 0
                ) {
                    $proxies = $ProxyGroup['content']['left-proxies'];
                }
                $AllRemark = [];
                if (isset($ProxyGroup['content']['class'])) {
                    foreach ($Nodes as $item) {
                        if ($item['class'] == $ProxyGroup['content']['class']) {
                            $AllRemark[] = $item['name'];
                        }
                    }
                } elseif (isset($ProxyGroup['content']['noclass'])) {
                    foreach ($Nodes as $item) {
                        if ($item['class'] != $ProxyGroup['content']['noclass']) {
                            $AllRemark[] = $item['name'];
                        }
                    }
                } else {
                    foreach ($Nodes as $item) {
                        $AllRemark[] = $item['name'];
                    }
                }
                if (isset($ProxyGroup['content']['regex'])) {
                    foreach ($AllRemark as $item) {
                        if (!preg_match($ProxyGroup['content']['regex'], $item)) {
                            unset($item);
                        }
                    }
                }
                if (isset($ProxyGroup['content']['class'])
                    || isset($ProxyGroup['content']['noclass'])
                    || isset($ProxyGroup['content']['regex'])
                ) {
                    $proxies = array_merge($proxies, $AllRemark);
                    if (isset($ProxyGroup['content']['right-proxies'])
                        && count($ProxyGroup['content']['right-proxies']) != 0
                    ) {
                        $proxies = array_merge(
                            $proxies,
                            $ProxyGroup['content']['right-proxies']
                        );
                    }
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

    public static function ClashConfRule($Rules)
    {
        $return = '';
        if (isset($Rules['source']) && $Rules['source'] != '') {
            $sourceURL = trim($Rules['source']);
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match('/^https:\/\/((gist\.)?github\.com|raw\.githubusercontent\.com|gitlab\.com)/i', $sourceURL)) {
                $return = @file_get_contents($sourceURL);
                if (!$return) {
                    $return = '// 远程规则加载失败'
                    . PHP_EOL .
                    'GEOIP,CN,DIRECT'
                    . PHP_EOL .
                    'MATCH,DIRECT';
                }
            } else {
                $return = '// 远程规则仅支持 github 以及 gitlab'
                . PHP_EOL .
                'GEOIP,CN,DIRECT'
                . PHP_EOL .
                'MATCH,DIRECT';
            }
        }
        return $return;
    }

    // 待续 Quantumult...
}
