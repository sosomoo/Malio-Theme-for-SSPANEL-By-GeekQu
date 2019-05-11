<?php

namespace App\Controllers;

use App\Models\User;

/**
 *  ConfController
 */
class ConfController extends BaseController
{
    public static function SurgeConfs($User, $AllProxys, $Nodes, $Configs)
    {
        $General = ConfController::SurgeConfGeneral($Configs['General']);
        $Proxys = ConfController::SurgeConfProxy($Configs['Proxy']);
        $ProxyGroup = ConfController::SurgeConfProxyGroup($AllProxys, $Configs['ProxyGroup']);
        $Rule = ConfController::SurgeConfRule($Configs['Rule']);

        $Conf = "#!MANAGED-CONFIG "
        .Config::get('baseUrl').$_SERVER['REQUEST_URI'].
        "\n[General]"
        .$General.
        "\n[Proxy]"
        .$AllProxys.$Proxys.
        "\n[Proxy Group]"
        .$ProxyGroup.
        "\n[Rule]"
        .$Rule;

        return $Conf;
    }
    
    public static function SurgeConfGeneral($General)
    {
        $return = "";
        foreach ($General as $key => $value) {
            $return .= "\n$key = $value";
        }
        return $return;
    }

    public static function SurgeConfProxy($Proxys)
    {
        $return = "";
        foreach ($Proxys as $value) {
            if (!preg_match("/(\[General|Replica|Proxy|Proxy\sGroup|Rule|Host|URL\sRewrite|Header\sRewrite|MITM|Script\])/", $value)) {
                $return .= "\n$value";
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
                            $Remarks .= ", ".$item['remark'];
                        }
                    }
                } elseif (isset($ProxyGroup['content']['noclass'])) {
                    foreach ($Nodes as $item) {
                        if ($item['obfs'] == "v2ray") {
                            continue;
                        }
                        if ($item['class'] != $ProxyGroup['content']['noclass']) {
                            $AllRemark[] = $item['remark'];
                            $Remarks .= ", ".$item['remark'];
                        }
                    }
                }
                if (isset($ProxyGroup['content']['regex'])) {
                    $Remarks = "";
                    foreach ($AllRemark as $item) {
                        if (preg_match($ProxyGroup['content']['regex'], $item)) {
                            $Remarks .= ", ".$item;
                        }
                    }
                }
                $text1 = isset($ProxyGroup['content']['text1']) && $ProxyGroup['content']['text1'] != "" ? ", " .$ProxyGroup['content']['text1'] : "";
                $text2 = isset($ProxyGroup['content']['text2']) && $ProxyGroup['content']['text2'] != "" ? ", " .$ProxyGroup['content']['text2'] : "";
                $url = isset($ProxyGroup['url']) && $ProxyGroup['url'] != "" ? ", url = " .$ProxyGroup['url'] : "";
                $interval = isset($ProxyGroup['interval']) && $ProxyGroup['interval'] != "" ? ", interval = " .$ProxyGroup['interval'] : "";
                $str .= $ProxyGroup['name'] . " = " . $ProxyGroup['type'] . $text1 . $Remarks . $text2 . $url . $interval;
            } elseif ($ProxyGroup['type'] == "ssid") {
                $wifi = "";
                foreach ($ProxyGroup['content'] as $key => $value) {
                    $wifi .= ", \"" . $key . "\" = " . $value;
                }
                $cellular = isset($ProxyGroup['cellular']) ? ", cellular = " .$ProxyGroup['cellular'] : "";
                $str .= $ProxyGroup['name'] . " = " . $ProxyGroup['type'] . ", default = " . $ProxyGroup['default'] . $cellular . $wifi;
            } else {
                $str .= "";
            }
            $return .= "\n$str";
        }
    }

    public static function SurgeConfRule($Rules)
    {
        if (isset($Rules['source']) && $Rules['source'] != "") {
            // 远程规则仅支持 github 以及 gitlab
            if (preg_match("/^https:\/\/((gist\.)?github\.com|gitlab\.com)/i", $Rules['source'])) {
                $return = file_get_contents($Rules['source']);
            }
        } //elseif (isset($Rules['internal']) && $Rules['internal'] != "") {
            // 内置部分规则，暂时未写
        //}
        return $return;
    }



    // 待续 Clash 以及 Quantumult...



}
