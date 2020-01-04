<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use App\Utils\Telegram\Process;
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
                    return self::reply(Config::get('not_admin_reply_msg') ,$MessageID);
                }                
            }
        }

        // 发送 '输入中' 会话状态
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $User = null;
        $FindUser = null;
        if ($Message->getReplyToMessage() != null) {
            // 回复源消息用户
            $FindUser = [
                'id'       => $Message->getReplyToMessage()->getFrom()->getId(),
                'name'     => $Message->getReplyToMessage()->getFrom()->getFirstName() . ' ' . $Message->getReplyToMessage()->getFrom()->getLastName(),
                'username' => $Message->getReplyToMessage()->getFrom()->getUsername(),
            ];
            $User = Process::getUser($FindUser['id']);
            if ($User == null) {
                return self::reply(Config::get('no_user_found') ,$MessageID);
            }
        }

        // 命令格式：
        // - /setuser 选项 操作值 用户识别码

        // ############## 命令解析 ##############
        $Options = self::StrExplode($arguments, ' ');
        if (count($Options) < 2) {
            return self::reply('没有提供选项或操作值.' ,$MessageID);
        }
        if (count($Options) == 2 && $User == null) {
            return self::reply(Config::get('no_search_value_provided') ,$MessageID);
        }
        // 选项
        $Option = $Options[0];
        // 操作值
        $value = $Options[1];
        // 用户识别码
        $UserCode = '';
        if (count($Options) >= 3) {
            $UserCode = $Options[2];
        }
        // ############## 命令解析 ##############

        // ############## 用户识别码处理 ##############
        if ($User == null) {
            if ($UserCode == '') {
                return self::reply(Config::get('no_search_value_provided') ,$MessageID);
            }
            $useMethod = 'email';
            if (strpos($UserCode, ':') !== false) {
                $UserCodeExplode = explode(':', $UserCode);
                $Search = $UserCodeExplode[0];
                $UserCode = $UserCodeExplode[1];
                $SearchMethods = [
                    'id' => [
                        'ID'
                    ],
                    'email' => [
                        '邮箱'
                    ],
                    'port' => [
                        '端口'
                    ],
                ];
                $useTempMethod = self::getOptionMethod($SearchMethods, $Search);
                if ($useTempMethod != '') {
                    $useMethod = $useTempMethod;
                }
            }
            $User = Process::getUser($UserCode, $useMethod);
            if ($User == null) {
                return self::reply(Config::get('no_user_found') ,$MessageID);
            }
        }
        // ############## 用户识别码处理 ##############


        // ############## 字段选项处理 ##############
        $OptionMethods = [
            'is_admin' => [
                '设置管理员',
                '管理员',
            ],
            'enable' => [
                '启用',
                '用户启用',
            ],
            'money' => [
                '钱',
                '金钱',
                '余额'
            ],
            'port' => [
                '端口'
            ],
            'transfer_enable' => [
                '流量',
                '数据',
                '数据流量'
            ],
            'passwd' => [
                '连接密码'
            ],
            'method' => [
                '加密',
                '加密方式',
                '加密方法'
            ],
            'protocol' => [
                '协议'
            ],
            'protocol_param' => [
                '协参',
                '协议参数',
            ],
            'obfs' => [
                '混淆'
            ],
            'obfs_param' => [
                '混参',
                '混淆参数',
            ],
            'invite_num' => [
                '邀请数量'
            ],
            'node_group' => [
                '用户组',
                '用户分组',
            ],
            'class' => [
                '等级'
            ],
            'class_expire' => [
                '等级过期时间'
            ],
            'expire_in' => [
                '账户过期时间',
                '账号过期时间'
            ],
            'node_speedlimit' => [
                '限速'
            ],
            'node_connector' => [
                '客户端',
                '连接数',
            ],
        ];
        $useOptionMethod = self::getOptionMethod($OptionMethods, $Option);
        if ($useOptionMethod == '') {
            return self::reply(Config::get('data_method_not_found') ,$MessageID);
        }
        // ############## 字段选项处理 ##############

        // ############## 字段数据增改值处理 ##############
        switch ($useOptionMethod) {
            case 'money':
                $old = $User->money;
                $new = self::ComputingMethod($User->money, $value, true);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->money = $new;
                break;
            case 'class':
                $old = $User->class;
                $new = self::ComputingMethod($User->class, $value, false);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->class = $new;
                break;
            case 'invite_num':
                $old = $User->invite_num;
                $new = self::ComputingMethod($User->invite_num, $value, false);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->invite_num = $new;
                break;
            case 'node_group':
                $old = $User->node_group;
                $new = self::ComputingMethod($User->node_group, $value, false);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->node_group = $new;
                break;
            case 'node_speedlimit':
                $old = $User->node_speedlimit;
                $new = self::ComputingMethod($User->node_speedlimit, $value, false);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->node_speedlimit = $new;
                break;
            case 'node_connector':
                $old = $User->node_connector;
                $new = self::ComputingMethod($User->node_connector, $value, false);
                if ($new === null) {
                    return self::reply('处理出错.' ,$MessageID);
                }
                $User->node_connector = $new;
                break;
            default:
                return self::reply('尚不支持.' ,$MessageID);
                break;
        }

        if ($User->save()) {
            $text = [
                '修改用户：' . $User->email,
                '被修改项：' . $useOptionMethod,
                '修改前值：' . $old,
                '修改后值：' . $new,
            ];
            $this->replyWithMessage(
                [
                    'text'                  => implode(PHP_EOL, $text),
                    'parse_mode'            => 'Markdown',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
            return;
        } else {
            $this->replyWithMessage(
                [
                    'text'                  => '保存出错',
                    'parse_mode'            => 'Markdown',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
            return;
        }
        // ############## 字段数据增改值处理 ##############

        // if ($ChatID > 0) {
        //     // 私人
        //     self::Privacy($User, $SendUser, $ChatID, $Message, $MessageID);
        // } else {
        //     // 群组
        //     self::Group($User, $SendUser, $ChatID, $Message, $MessageID);
        // }
    }

    public function Group($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => 'Group',
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function Privacy($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => 'Privacy',
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function reply($Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => $Message,
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function StrExplode($Str, $Delimiter)
    {
        $return = [];
        $Str = trim($Str);
        for ($x = 0; $x <= 10; $x++) {
            if (strpos($Str, $Delimiter) !== false) {
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

    public function getOptionMethod($MethodGroup, $Search)
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

    public function ComputingMethod($Source, $Value, $FloatingNumber = false)
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
}
