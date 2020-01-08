<?php

namespace App\Utils\Telegram;

use App\Models\TelegramTasks;
use App\Services\Config;
use App\Utils\Tools;

class TelegramTools
{
    /**
     * 用户识别搜索字段
     *
     * @return array
     */
    public static function getUserSearchMethods()
    {
        return [
            'id'    => [],
            'email' => Config::get('remark_user_search_email'),
            'port'  => Config::get('remark_user_search_port'),
        ];
    }

    /**
     * 操作字段
     *
     * @return array
     */
    public static function getUserActionOption()
    {
        return [
            'is_admin'          => Config::get('remark_user_option_is_admin'),
            'enable'            => Config::get('remark_user_option_enable'),
            'money'             => Config::get('remark_user_option_money'),
            'port'              => Config::get('remark_user_option_port'),
            'transfer_enable'   => Config::get('remark_user_option_transfer_enable'),
            'passwd'            => Config::get('remark_user_option_passwd'),
            'method'            => Config::get('remark_user_option_method'),
            'protocol'          => Config::get('remark_user_option_protocol'),
            'protocol_param'    => Config::get('remark_user_option_protocol_param'),
            'obfs'              => Config::get('remark_user_option_obfs'),
            'obfs_param'        => Config::get('remark_user_option_obfs_param'),
            'invite_num'        => Config::get('remark_user_option_invite_num'),
            'node_group'        => Config::get('remark_user_option_node_group'),
            'class'             => Config::get('remark_user_option_class'),
            'class_expire'      => Config::get('remark_user_option_class_expire'),
            'expire_in'         => Config::get('remark_user_option_expire_in'),
            'node_speedlimit'   => Config::get('remark_user_option_node_speedlimit'),
            'node_connector'    => Config::get('remark_user_option_node_connector'),
        ];
    }

    /**
     * 待定
     *
     * @return mixed
     */
    public static function getUserActionMethod()
    {
    }

    /**
     * 获取用户邮箱
     *
     * @param string $email  邮箱
     * @param int    $ChatID 会话 ID
     *
     * @return string
     */
    public static function getUserEmail($email, $ChatID)
    {
        if (Config::get('enable_user_email_group_show') === true || $ChatID > 0) {
            return $email;
        }
        $a = strpos($email, '@');
        if ($a === false) {
            return $email;
        }
        $string = substr($email, $a);
        return ($a === 1 ? '*' . $string : substr($email, 0, 1) . str_pad('', $a - 1, '*') . $string);
    }

    /**
     * 分割字符串
     *
     * @param string $Str       源字符串
     * @param string $Delimiter 分割定界符
     * @param int    $Quantity  最大返回数量
     *
     * @return array
     */
    public static function StrExplode($Str, $Delimiter, $Quantity = 10)
    {
        $return = [];
        $Str = trim($Str);
        for ($x = 0; $x <= 10; $x++) {
            if (strpos($Str, $Delimiter) !== false && count($return) < $Quantity - 1) {
                $temp = substr($Str, 0, strpos($Str, $Delimiter));
                $return[] = $temp;
                $Str = trim(substr($Str, strlen($temp)));
            } else {
                $return[] = $Str;
                break;
            }
        }
        return $return;
    }

    /**
     * 查找字符串是否是某个方法的别名
     *
     * @param array  $MethodGroup 方法别名的数组
     * @param string $Search      被搜索的字符串
     *
     * @return string
     */
    public static function getOptionMethod($MethodGroup, $Search)
    {
        $useMethod = '';
        foreach ($MethodGroup as $MethodName => $Remarks) {
            if (strlen($MethodName) === strlen($Search)) {
                if (stripos($MethodName, $Search) === 0) {
                    $useMethod = $MethodName;
                    break;
                }
            }
            if (count($Remarks) >= 1) {
                foreach ($Remarks as $Remark) {
                    if (strlen($Remark) === strlen($Search) && stripos($Remark, $Search) === 0) {
                        $useMethod = $MethodName;
                        break 2;
                    }
                }
            }
        }
        return $useMethod;
    }

    /**
     * 使用 $Value 给定的运算式与 $Source 计算结果
     *
     * @param string $Source         源数值
     * @param string $Value          运算式含增改数值
     * @param bool   $FloatingNumber 是否格式化为浮点数
     *
     * @return string|null
     */
    public static function ComputingMethod($Source, $Value, $FloatingNumber = false)
    {
        if (
            (strpos($Value, '+') === 0
                ||
                strpos($Value, '-') === 0
                ||
                strpos($Value, '*') === 0
                ||
                strpos($Value, '/') === 0)
            &&
            is_numeric(substr($Value, 1))
        ) {
            $Source = eval('return $Source ' . substr($Value, 0, 1) . '= ' . substr($Value, 1) . ';');
        } else {
            if (is_numeric($Value)) {
                $Source = $Value;
            } else {
                $Source = null;
            }
        }
        if ($Source !== null) {
            $Source = ($FloatingNumber === false
                ? number_format($Source, 0, '.', '')
                : number_format($Source, 2, '.', ''));
        }
        return $Source;
    }

    /**
     * 使用 $Value 给定的运算式及流量单位与 $Source 计算结果
     *
     * @param string $Source 源数值
     * @param string $Value  运算式含增改数值
     *
     * @return int|null
     */
    public static function TrafficMethod($Source, $Value)
    {
        if (
            strpos($Value, '+') === 0
            ||
            strpos($Value, '-') === 0
            ||
            strpos($Value, '*') === 0
            ||
            strpos($Value, '/') === 0
        ) {
            $operator = substr($Value, 0, 1);
            if (!in_array($operator, ['*', '/'])) {
                $number = Tools::flowAutoShowZ(substr($Value, 1));
            } else {
                $number = substr($Value, 1, strlen($Value) - 1);
                if (!is_numeric($number)) return null;
            }
            if ($number === null) {
                return null;
            }
            $Source = eval('return $Source ' . $operator . '= ' . $number . ';');
        } else {
            if (is_numeric($Value)) {
                if ((int) $Value === 0) {
                    $Source = 0;
                } else {
                    $Source = Tools::flowAutoShowZ($Value . 'KB');
                }
            } else {
                $Source = Tools::flowAutoShowZ($Value);
            }
        }
        return $Source;
    }

    /**
     * 字符串数组转 TG HTML 等宽字符串
     *
     * @param array $strArray 字符串数组
     *
     * @return string
     */
    public static function StrArrayToCode($strArray)
    {
        return implode(
            PHP_EOL,
            array_map(
                function ($item) {
                    return ('<code>' . $item . '</code>');
                },
                $strArray
            )
        );
    }

    /**
     * 删除消息
     *
     * @return void
     */
    public static function DeleteMessage($ChatID, $MessageID)
    {
        $task = new TelegramTasks();
        $task->type          = 1;
        $task->chatid        = $ChatID;
        $task->messageid     = $MessageID;
        $task->executetime   = (time() + (60 *1));
        $task->datetime      = time();
        $task->save();
    }
}
