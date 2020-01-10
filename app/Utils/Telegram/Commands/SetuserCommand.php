<?php

namespace App\Utils\Telegram\Commands;

use App\Models\{User, TelegramTasks};
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

        if (Config::get('enable_delete_user_cmd') === true) {
            TelegramTools::DeleteMessage([
                'chatid'      => $ChatID,
                'messageid'   => $Message->getMessageId(),
                'executetime' => (time() + Config::get('delete_message_time'))
            ]);
        }

        if (!in_array($SendUser['id'], Config::get('telegram_admins'))) {
            $AdminUser = User::where('is_admin', 1)->where('telegram_id', $SendUser['id'])->first();
            if ($AdminUser == null) {
                // 非管理员回复消息
                if (Config::get('enable_not_admin_reply') === true && Config::get('not_admin_reply_msg') != '') {
                    $response = $this->replyWithMessage(
                        [
                            'text'                  => Config::get('not_admin_reply_msg'),
                            'parse_mode'            => 'HTML',
                            'reply_to_message_id'   => $MessageID,
                        ]
                    );
                    // 消息删除任务
                    TelegramTools::DeleteMessage([
                        'chatid'      => $ChatID,
                        'messageid'   => $response->getMessageId(),
                        'executetime' => (time() + Config::get('delete_admin_message_time'))
                    ]);
                    return;
                }
            }
        }

        // 发送 '输入中' 会话状态
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        return self::Reply($arguments, $SendUser, $Message, $MessageID, $ChatID);
    }

    public function Reply($arguments, $SendUser, $Message, $MessageID, $ChatID)
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
                $response = $this->replyWithMessage(
                    [
                        'text'                  => Config::get('no_user_found'),
                        'parse_mode'            => 'HTML',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                // 消息删除任务
                TelegramTools::DeleteMessage([
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                    'executetime' => (time() + Config::get('delete_admin_message_time'))
                ]);
                return;
            }

            if ($arguments == '') {
                // 无参数时回复用户信息
                $strArray = [
                    '#' . $User->id . ' ' . $User->user_name . ' 的用户信息',
                    '',
                    '用户邮箱：' . TelegramTools::getUserEmail($User->email, $ChatID),
                    '账户余额：' . $User->money,
                    '是否启用：' . $User->enable,
                    '用户等级：' . $User->class,
                    '剩余流量：' . $User->unusedTraffic(),
                    '等级到期：' . $User->class_expire,
                    '账户到期：' . $User->expire_in,
                    '',
                    '回复此消息无需打命令可修改用户数据.'
                ];
                $response = $this->replyWithMessage(
                    [
                        'text'                  => TelegramTools::StrArrayToCode($strArray),
                        'parse_mode'            => 'HTML',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                // 消息删除任务
                TelegramTools::DeleteMessage([
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                    'executetime' => (time() + Config::get('delete_admin_message_time'))
                ]);
                // 储存 Bot 所发的 Message ID
                TelegramTools::FindUserSave([
                    'userid'      => $User->id,
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                ]);
                return;
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
            $response = $this->replyWithMessage(
                [
                    'text'                  => TelegramTools::StrArrayToCode($strArray),
                    'parse_mode'            => 'HTML',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
            // 消息删除任务
            TelegramTools::DeleteMessage([
                'chatid'      => $ChatID,
                'messageid'   => $response->getMessageId(),
                'executetime' => (time() + Config::get('delete_admin_message_time'))
            ]);
            return;
        }

        // 命令格式：
        // - /setuser [用户识别码] 选项 操作值

        // ############## 命令解析 ##############
        $UserCode = '';
        if ($User == null) {
            $Options = TelegramTools::StrExplode($arguments, ' ', 3);
            if (count($Options) < 3) {
                $response = $this->replyWithMessage(
                    [
                        'text'                  => '没有提供选项或操作值.',
                        'parse_mode'            => 'HTML',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                // 消息删除任务
                TelegramTools::DeleteMessage([
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                    'executetime' => (time() + Config::get('delete_admin_message_time'))
                ]);
                return;
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
                $response = $this->replyWithMessage(
                    [
                        'text'                  => '没有提供选项或操作值.',
                        'parse_mode'            => 'HTML',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                // 消息删除任务
                TelegramTools::DeleteMessage([
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                    'executetime' => (time() + Config::get('delete_admin_message_time'))
                ]);
                return;
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

                $response = $this->replyWithMessage(
                    [
                        'text'                  => Config::get('no_user_found'),
                        'parse_mode'            => 'HTML',
                        'reply_to_message_id'   => $MessageID,
                    ]
                );
                // 消息删除任务
                TelegramTools::DeleteMessage([
                    'chatid'      => $ChatID,
                    'messageid'   => $response->getMessageId(),
                    'executetime' => (time() + Config::get('delete_admin_message_time'))
                ]);
                return;
            }
        }
        $Email = TelegramTools::getUserEmail($User->email, $ChatID);
        // ############## 用户识别码处理 ##############

        // ############## 字段选项处理 ##############
        $OptionMethods = TelegramTools::getUserActionOption();
        $useOptionMethod = TelegramTools::getOptionMethod($OptionMethods, $Option);
        if ($useOptionMethod == '') {
            $response = $this->replyWithMessage(
                [
                    'text'                  => Config::get('data_method_not_found'),
                    'parse_mode'            => 'HTML',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
            // 消息删除任务
            TelegramTools::DeleteMessage([
                'chatid'      => $ChatID,
                'messageid'   => $response->getMessageId(),
                'executetime' => (time() + Config::get('delete_admin_message_time'))
            ]);
            return;
        }
        // ############## 字段选项处理 ##############

        $reply = TelegramTools::OperationUser($User, $useOptionMethod, $value, $ChatID);
        $response = $this->replyWithMessage(
            [
                'text'                  => $reply['msg'],
                'parse_mode'            => 'HTML',
                'reply_to_message_id'   => $MessageID,
            ]
        );
        // 消息删除任务
        TelegramTools::DeleteMessage([
            'chatid'      => $ChatID,
            'messageid'   => $response->getMessageId(),
            'executetime' => (time() + Config::get('delete_admin_message_time'))
        ]);
        return;
    }
}
