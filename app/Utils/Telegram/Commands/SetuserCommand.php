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
                    $this->replyWithMessage(
                        [
                            'text'                  => Config::get('not_admin_reply_msg'),
                            'parse_mode'            => 'Markdown',
                            'reply_to_message_id'   => $MessageID,
                        ]
                    );
                }
                return;
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
                $this->replyWithMessage(
                    [
                        'text'                  => Config::get('no_user_found'),
                        'parse_mode'            => 'Markdown',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                return;
            }
        }

        // 命令格式：
        // - /setuser 选项 操作值 用户识别码

        // ############## 命令解析 ##############
        $MessageText    = trim($arguments);
        // 选项
        $Option         = substr($MessageText, 0, strpos($MessageText, ' '));
        $MessageText    = trim(substr($MessageText, strlen($Option)));
        // 用户识别码
        $UserCode       = '';
        if (strpos($MessageText, ' ') !== false) {
            // 操作值
            $value = substr($MessageText, 0, strpos($MessageText, ' '));
            $UserCode = trim(substr($MessageText, strlen($value)));
        } else {
            // 操作值
            $value = substr($MessageText, 0);
        }
        // ############## 命令解析 ##############

        // ############## 用户识别码处理 ##############
        if ($User == null) {
            if ($UserCode == '') {
                $this->replyWithMessage(
                    [
                        'text'                  => Config::get('no_search_value_provided'),
                        'parse_mode'            => 'Markdown',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                return;
            }
            $UserCodeExplode = explode(':', $UserCode);
            $Search = $UserCodeExplode[0];
            $SearchValue = $UserCodeExplode[1];
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
            $useMethods = '';
            foreach ($SearchMethods as $SearchMethod => $Methods) {
                if (strlen($SearchMethod) === strlen($Search)) {
                    if (stripos($SearchMethod, $Search) === 0) {
                        $useMethods = $SearchMethod;
                        break;
                    }
                }
                if (count($Methods) >= 1) {
                    foreach ($Methods as $Remark) {
                        if (strlen($Remark) === strlen($Search) && stripos($Remark, $Search) === 0) {
                            $useMethods = $SearchMethod;
                            break 2;
                        }
                    }
                }
            }
            if ($useMethods == '') {
                $useMethods == 'email';
            }
            $User = Process::getUser($SearchValue, $useMethods);
            if ($User == null) {
                $this->replyWithMessage(
                    [
                        'text'                  => Config::get('no_user_found'),
                        'parse_mode'            => 'Markdown',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                return;
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
        $useOptionMethods = '';
        foreach ($OptionMethods as $OptionMethod => $Methods) {
            if (strlen($OptionMethod) === strlen($Option)) {
                if (stripos($OptionMethod, $Option) === 0) {
                    $useOptionMethods = $OptionMethod;
                    break;
                }
            }
            if (count($Methods) >= 1) {
                foreach ($Methods as $Remark) {
                    if (strlen($Remark) === strlen($Option) && stripos($Remark, $Option) === 0) {
                        $useOptionMethods = $OptionMethod;
                        break 2;
                    }
                }
            }
        }
        if ($useOptionMethods == '') {
            $this->replyWithMessage(
                [
                    'text'                  => Config::get('data_method_not_found'),
                    'parse_mode'            => 'Markdown',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
            return;
        }
        // ############## 字段选项处理 ##############

        // ############## 字段数据增改值处理 ##############
        switch ($useOptionMethods) {
            case '':
                break;
            default:
                break;
        }
        // ############## 字段数据增改值处理 ##############

        if ($ChatID > 0) {
            // 私人
            self::Privacy($User, $SendUser, $ChatID, $Message, $MessageID);
        } else {
            // 群组
            self::Group($User, $SendUser, $ChatID, $Message, $MessageID);
        }
    }

    public function Group($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return;
    }

    public function Privacy($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return;
    }
}
