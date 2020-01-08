<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use App\Utils\Tools;
use App\Utils\Telegram\{Process, TelegramTools};
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class SetuserCommand.
 */
class SetuserCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'setuser';

    /**
     * @var string Command Description
     */
    protected $description = '';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $Update = $this->getUpdate();
        $Message = $Update->getMessage();

        // 消息 ID
        $MessageID = $Message->getMessageId();

        // 消息会话 ID
        $ChatID = $Message->getChat()->getId();

        // 触发用户
        $SendUser = [
            'id'       => $Message->getFrom()->getId(),
            'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];

        if (!in_array($SendUser['id'], Config::get('telegram_admins'))) {
            $AdminUser = User::where('is_admin', 1)->where('telegram_id', $SendUser['id'])->first();
            if ($AdminUser == null) {
                // 非管理员回复消息
                if (Config::get('enable_not_admin_reply') === true && Config::get('not_admin_reply_msg') != '') {
                    return self::reply(Config::get('not_admin_reply_msg'), $MessageID);
                }
            }
        }

        // 发送 '输入中' 会话状态
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        if ($ChatID > 0) {
            // 私人
            self::Privacy($arguments, $SendUser, $Message, $MessageID, $ChatID);
        } else {
            // 群组
            $response = self::Group($arguments, $SendUser, $Message, $MessageID, $ChatID);

            // 删除消息
            // TelegramTools::DeleteMessage($ChatID, $MessageID); // 删除用户的指令
            // TelegramTools::DeleteMessage($response->getChat()->getId(), $response->getMessageId()); // 删除 Bot 回复
        }
    }

    public function Group($arguments, $SendUser, $Message, $MessageID, $ChatID)
    {
        $User = null;
        $FindUser = null;
        $arguments = trim($arguments);
        if ($Message->getReplyToMessage() != null) {
            // 回复源消息用户
            $FindUser = [
                'id'       => $Message->getReplyToMessage()->getFrom()->getId(),
                'name'     => $Message->getReplyToMessage()->getFrom()->getFirstName() . ' ' . $Message->getReplyToMessage()->getFrom()->getLastName(),
                'username' => $Message->getReplyToMessage()->getFrom()->getUsername(),
            ];
            $User = Process::getUser($FindUser['id']);
            if ($User == null) {
                return self::reply(Config::get('no_user_found'), $MessageID);
            }
            if ($arguments == '') {
                // 无参数时回复用户信息
                return self::reply('用户信息.' . TelegramTools::getUserEmail($User->email, $ChatID), $MessageID);
            }
        }

        if ($arguments == '') {

            $strArray = [
                '/setuser [用户识别] [操作字段] [操作参数]',
                '',
                '用户识别：[使用回复消息方式则无需填写]',
                '- [' . implode(' | ', array_keys(TelegramTools::getUserSearchMethods())) . ']，可省略，默认：email',
                '- 例 1：/setuser admin@admin 操作字段 操作参数',
                '- 例 2：/setuser port:10086  操作字段 操作参数',
                '',
                '操作字段：',
                '[' . implode(' | ', array_keys(TelegramTools::getUserActionOption())) . ']',
                '',
                '操作参数：',
                '- 请查看对应选项支持的写法.',
            ];
            return $this->reply(TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
        }

        // 命令格式：
        // - /setuser [用户识别码] 选项 操作值

        // ############## 命令解析 ##############
        $UserCode = '';
        if ($User == null) {
            $Options = TelegramTools::StrExplode($arguments, ' ', 3);
            if (count($Options) < 3) {
                return self::reply('没有提供选项或操作值.', $MessageID);
            }
            // 用户识别码
            $UserCode = $Options[0];
            // 选项
            $Option = $Options[1];
            // 操作值
            $value = $Options[2];
        } else {
            $Options = TelegramTools::StrExplode($arguments, ' ', 2);
            if (count($Options) < 2) {
                return self::reply('没有提供选项或操作值.', $MessageID);
            }
            // 选项
            $Option = $Options[0];
            // 操作值
            $value = $Options[1];
        }
        // ############## 命令解析 ##############

        // ############## 用户识别码处理 ##############
        if ($User == null) {
            // 默认搜寻字段
            $useMethod = 'email';
            if (strpos($UserCode, ':') !== false) {
                // 如果指定了字段
                $UserCodeExplode = explode(':', $UserCode);
                $Search = $UserCodeExplode[0];
                $UserCode = $UserCodeExplode[1];
                $SearchMethods = TelegramTools::getUserSearchMethods();
                $useTempMethod = TelegramTools::getOptionMethod($SearchMethods, $Search);
                if ($useTempMethod != '') {
                    $useMethod = $useTempMethod;
                }
            }
            $User = Process::getUser($UserCode, $useMethod);
            if ($User == null) {
                return self::reply(Config::get('no_user_found'), $MessageID);
            }
        }
        $Email = TelegramTools::getUserEmail($User->email, $ChatID);
        // ############## 用户识别码处理 ##############

        // ############## 字段选项处理 ##############

        $OptionMethods = TelegramTools::getUserActionOption();
        $useOptionMethod = TelegramTools::getOptionMethod($OptionMethods, $Option);
        if ($useOptionMethod == '') {
            return self::reply(Config::get('data_method_not_found'), $MessageID);
        }
        // ############## 字段选项处理 ##############

        // ############## 字段数据增改值处理 ##############
        $old = $User->$useOptionMethod;
        $useOptionMethodName = $OptionMethods[$useOptionMethod][0];
        switch ($useOptionMethod) {
                // ##############
            case 'enable':
            case 'is_admin':
                $strArray = [
                    '// 支持的写法',
                    '// [启用|是] —— 字面意思',
                    '// [禁用|否] —— 字面意思',
                ];
                if (strpos($value, ' ') !== false) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                if (in_array($value, ['启用', '是'])) {
                    $User->$useOptionMethod = 1;
                    $new = '启用';
                } elseif (in_array($value, ['禁用', '否'])) {
                    $User->$useOptionMethod = 0;
                    $new = '禁用';
                } else {
                    return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                }
                $old = ($old ? '启用' : '禁用');
                break;
                // ##############
            case 'port':
                // 支持正整数或 0 随机选择
                if (!is_numeric($value) || strpos($value, '-') === 0) {
                    return self::reply('提供的端口非数值，如要随机重置请指定为 0.', $MessageID);
                }
                if ((int) $value === 0) {
                    $value = Tools::getAvPort();
                }
                $temp = $User->setPort($value);
                if ($temp['ok'] === false) {
                    $strArray = [
                        '目标用户：' . $Email,
                        '欲修改项：' . $useOptionMethodName . '[' . $useOptionMethod . ']',
                        '当前值为：' . $old,
                        '欲修改为：' . $value,
                        '错误详情：' . $temp['msg'],
                    ];
                    return self::reply(TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                }
                $new = $User->$useOptionMethod;
                $User->$useOptionMethod = $new;
                break;
                // ##############
            case 'transfer_enable':
                $strArray = [
                    '// 支持的写法，不支持单位 b，不区分大小写',
                    '// 支持的单位：kb | mb | gb | tb | pb',
                    '//  2kb —— 指定为该值得流量',
                    '// +2kb —— 增加流量',
                    '// -2kb —— 减少流量',
                    '// *2   —— 以当前流量做乘法，不支持填写单位',
                    '// /2   —— 以当前流量做除法，不支持填写单位',
                ];
                if (strpos($value, ' ') !== false) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                $new = TelegramTools::TrafficMethod($User->$useOptionMethod, $value);
                if ($new === null) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                $User->$useOptionMethod = $new;
                $old = Tools::flowAutoShow($old);
                $new = Tools::flowAutoShow($new);
                break;
                // ##############
            case 'expire_in':
            case 'class_expire':
                $strArray = [
                    '// 支持的写法，单位：天',
                    '// 使用天数设置不能包含空格',
                    '//  2 —— 以当前时间为基准的天数设置',
                    '// +2 —— 增加天数',
                    '// -2 —— 减少天数',
                    '// 指定日期，在日期与时间中含有一个空格',
                    '// 2020-02-30 —— 指定日期',
                    '// 2020-02-30 08:00:00 —— 指定日期精确到秒',
                ];
                if (
                    strpos($value, '+') === 0
                    ||
                    strpos($value, '-') === 0
                ) {
                    $operator = substr($value, 0, 1);
                    $number = substr($value, 1);
                    if (is_numeric($number)) {
                        $number *= 86400;
                        $old_time = strtotime($old);
                        $new = ($operator == '+' ? $old_time + $number : $old_time - $number);
                        $new = date('Y-m-d H:i:s', $new);
                    } else {
                        if (strtotime($value) === false) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                        $new = strtotime($value);
                        $new = date('Y-m-d H:i:s', $new);
                    }
                } else {
                    $number = $value;
                    if (is_numeric($value)) {
                        $number *= 86400;
                        $new = time() + $number;
                        $new = date('Y-m-d H:i:s', $new);
                    } else {
                        if (strtotime($value) === false) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                        $new = strtotime($value);
                        $new = date('Y-m-d H:i:s', $new);
                    }
                }
                $User->$useOptionMethod = $new;
                break;
                // ##############
            case 'obfs':
            case 'method':
            case 'protocol':
                // 支持系统中存在的协议、混淆、加密，且受可行性限制
                $MethodClass = 'set' . ucfirst($useOptionMethod);
                $temp = $User->$MethodClass($value);
                if ($temp['ok'] === true) {
                    $strArray = [
                        '目标用户：' . $Email,
                        '被修改项：' . $useOptionMethodName . '[' . $useOptionMethod . ']',
                        '修改前值：' . $old,
                        '修改后值：' . $User->$useOptionMethod,
                        '修改备注：' . $temp['msg'],
                    ];
                } else {
                    $strArray = [
                        '目标用户：' . $Email,
                        '欲修改项：' . $useOptionMethodName . '[' . $useOptionMethod . ']',
                        '当前值为：' . $old,
                        '欲修改为：' . $value,
                        '错误详情：' . $temp['msg'],
                    ];
                }
                return self::reply(TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                break;
                // ##############
            case 'passwd':
            case 'obfs_param':
            case 'protocol_param':
                // 参数值中不允许有空格
                if (strpos($value, ' ') !== false) return self::reply('处理出错，协议中含有空格等字符.', $MessageID);
                $new = $value;
                $User->$useOptionMethod = $new;
                break;
                // ##############
            case 'money':
                $strArray = [
                    '// 参数值中不允许有空格，结果会含小数 2 位',
                    '// +2  —— 增加余额',
                    '// -2  —— 减少余额',
                    '// *2  —— 以当前余额做乘法',
                    '// /2  —— 以当前余额做除法',
                ];
                $value = explode(' ', $value)[0];
                $new = TelegramTools::ComputingMethod($User->$useOptionMethod, $value, true);
                if ($new === null) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                $User->$useOptionMethod = $new;
                break;
                // ##############
            case 'class':
            case 'invite_num':
            case 'node_group':
            case 'node_connector':
            case 'node_speedlimit':
                $strArray = [
                    '// 参数值中不允许有空格',
                    '// +2  —— 增加值',
                    '// -2  —— 减少值',
                    '// *2  —— 以当前值做乘法',
                    '// /2  —— 以当前值做除法',
                ];
                $value = explode(' ', $value)[0];
                $new = TelegramTools::ComputingMethod($User->$useOptionMethod, $value, false);
                if ($new === null) return self::reply('处理出错，不支持的写法.' . PHP_EOL . PHP_EOL . TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
                $User->$useOptionMethod = $new;
                break;
                // ##############
            default:
                return self::reply('尚不支持.', $MessageID);
                break;
        }
        if ($User->save()) {
            $strArray = [
                '目标用户：' . $Email,
                '被修改项：' . $useOptionMethodName . '[' . $useOptionMethod . ']',
                '修改前为：' . $old,
                '修改后为：' . $new,
            ];
            return self::reply(TelegramTools::StrArrayToCode($strArray), $MessageID, 'HTML');
        } else {
            return $this->replyWithMessage(
                [
                    'text'                  => '保存出错',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
        }
        // ############## 字段数据增改值处理 ##############
    }

    public function Privacy($arguments, $SendUser, $Message, $MessageID, $ChatID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => 'Privacy',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function reply($Message, $MessageID, $ParseMode = '')
    {
        return $this->replyWithMessage(
            [
                'text'                  => $Message,
                'parse_mode'            => $ParseMode,
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }
}
