<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Config;

/**
 *  ConfController
 */
class ConfController extends BaseController
{
    public static function SurgeConfs($User, $AllProxys, $Nodes, $Configs)
    {
        $General = ConfController::SurgeConfGeneral($Configs['General']);
        $Proxys = isset($Configs['Proxy']) ? ConfController::SurgeConfProxy($Configs['Proxy']) : "";
        $ProxyGroup = ConfController::SurgeConfProxyGroup($Nodes, $Configs['ProxyGroup']);
        $Rule = ConfController::SurgeConfRule($Configs['Rule']);

        $Conf = "#!MANAGED-CONFIG "
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
        $return = "";
        if (count($General) != 0) {
            foreach ($General as $key => $value) {
                $return .= "\n$key = $value";
            }
        }
        return $return;
    }

    public static function SurgeConfProxy($Proxys)
    {
        $return = "";
        if (count($Proxys) != 0) {
            foreach ($Proxys as $value) {
                if (!preg_match("/(\[General|Replica|Proxy|Proxy\sGroup|Rule|Host|URL\sRewrite|Header\sRewrite|MITM|Script\])/", $value)) {
                    $return .= "\n$value";
                }
            }
        }
        return $return;
    }

    public static function SurgeConfProxyGroup($Nodes, $ProxyGroups)
    {
        $return = "";
        foreach ($ProxyGroups as $ProxyGroup) {
            $str = "";
            if (in_array($ProxyGroup['type'], ["select", "url-test", "fallback"])) {
                $AllRemark = [];
                $Remarks = "";
                if (isset($ProxyGroup['content']['class'])) {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == "v2ray") {
                            continue;
                        }
                        if ($item['class'] == $ProxyGroup['content']['class']) {
                            $AllRemark[] = $item['remark'];
                            $Remarks .= ", " . $item['remark'];
                        }
                    }
                } elseif (isset($ProxyGroup['content']['noclass'])) {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == "v2ray") {
                            continue;
                        }
                        if ($item['class'] != $ProxyGroup['content']['noclass']) {
                            $AllRemark[] = $item['remark'];
                            $Remarks .= ", " . $item['remark'];
                        }
                    }
                } else {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == "v2ray") {
                            continue;
                        }
                        $AllRemark[] = $item['remark'];
                    }
                }
                if (isset($ProxyGroup['content']['regex'])) {
                    $Remarks = "";
                    foreach ($AllRemark as $item) {
                        if (preg_match($ProxyGroup['content']['regex'], $item)) {
                            $Remarks .= ", " . $item;
                        }
                    }
                }
                $text1 = isset($ProxyGroup['content']['text1']) && $ProxyGroup['content']['text1'] != "" ? ", " . $ProxyGroup['content']['text1'] : "";
                $text2 = isset($ProxyGroup['content']['text2']) && $ProxyGroup['content']['text2'] != "" ? ", " . $ProxyGroup['content']['text2'] : "";
                $url = isset($ProxyGroup['url']) && $ProxyGroup['url'] != "" ? ", url = " . $ProxyGroup['url'] : "";
                $interval = isset($ProxyGroup['interval']) && $ProxyGroup['interval'] != "" ? ", interval = " . $ProxyGroup['interval'] : "";
                $str .= $ProxyGroup['name'] . " = " . $ProxyGroup['type'] . $text1 . $Remarks . $text2 . $url . $interval;
            } elseif ($ProxyGroup['type'] == "ssid") {
                $wifi = "";
                foreach ($ProxyGroup['content'] as $key => $value) {
                    $wifi .= ", \"" . $key . "\" = " . $value;
                }
                $cellular = isset($ProxyGroup['cellular']) ? ", cellular = " . $ProxyGroup['cellular'] : "";
                $str .= $ProxyGroup['name'] . " = " . $ProxyGroup['type'] . ", default = " . $ProxyGroup['default'] . $cellular . $wifi;
            } else {
                $str .= "";
            }
            $return .= "\n$str";
        }
        return $return;
    }

    public static function SurgeConfRule($Rules)
    {
        $return = "";
        if (isset($Rules['source']) && $Rules['source'] != "") {
            $sourceURL = trim($Rules['source']);
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match("/^https:\/\/((gist\.)?github\.com|gitlab\.com)/i", $sourceURL)) {
                $return = @file_get_contents($sourceURL);
                if (!$return) {
                    $return = "// 远程规则加载失败\nGEOIP,CN,DIRECT\nFINAL,DIRECT,dns-failed";
                }
            } else {
                $return = "// 远程规则仅支持 github 以及 gitlab\nGEOIP,CN,DIRECT\nFINAL,DIRECT,dns-failed";
            }
        }
        return $return;
    }



    // 待续 Clash 以及 Quantumult...



}
