<?php

namespace App\Utils\Telegram;

use App\Utils\TelegramSessionManager;
use App\Utils\Telegram\Process;
use App\Services\Config;

class Message
{
    public static function MessageMethod($bot, $Message)
    {
        // 触发用户
        $SendUser = [
            'id'       => $Message->getFrom()->getId(),
            'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];

        $user = TelegramTools::getUser($SendUser['id']);

        // 消息会话 ID
        $ChatID = $Message->getChat()->getId();

        // 消息内容
        $MessageData = $Message->getText();
        if ($MessageData != null) {
            $MessageData = trim($MessageData);
            if (is_numeric($MessageData) && strlen($MessageData) == 6) {
                if ($user != null) {
                    $uid = TelegramSessionManager::verify_login_number($MessageData, $user->id);
                    if ($uid != 0) {
                        $bot->sendMessage(
                            [
                                'chat_id' => $ChatID,
                                'text'    => '登录验证成功，邮箱：' . $user->email,
                            ]
                        );
                    } else {
                        $bot->sendMessage(
                            [
                                'chat_id' => $ChatID,
                                'text'    => '登录验证失败，数字无效',
                            ]
                        );
                    }
                }
            }
            return;
        }

        $NewChatMember = $Message->getNewChatParticipant();
        if ($NewChatMember != null) {
            self::NewChatParticipant($user, $bot, $Message, $ChatID, $NewChatMember);
        }
    }

    public static function NewChatParticipant($user, $bot, $Message, $ChatID, $NewChatMember)
    {
        $Member = [
            'id'       => $NewChatMember->getId(),
            'name'     => $NewChatMember->getFirstName() . ' ' . $NewChatMember->getLastName(),
            'username' => $NewChatMember->getUsername(),
        ];
        if ($NewChatMember->getUsername() == Config::get('telegram_bot')) {
            // 机器人加入新群组
            if (Config::get('allow_to_join_new_groups') !== true && !in_array($ChatID, Config::get('group_id_allowed_to_join'))) {
                // 退群
                $bot->sendMessage(
                    [
                        'text'                  => '不约，叔叔我们不约.',
                        'chat_id'               => $ChatID,
                        'reply_to_message_id'   => $Message->getMessageId(),
                    ]
                );
                TelegramTools::SendPost(
                    'kickChatMember',
                    [
                        'chat_id'   => $ChatID,
                        'user_id'   => $Member['id'],
                    ]
                );
                if (count(Config::get('telegram_admins')) >= 1) {
                    foreach (Config::get('telegram_admins') as $id) {
                        $bot->sendMessage(
                            [
                                'text'      => '由于您的设定，Bot 退出了一个群组.' . PHP_EOL . PHP_EOL . '群组名称：' . $Message->getChat()->getTitle(),
                                'chat_id'   => $id
                            ]
                        );
                    }
                }
            } else {
                $bot->sendMessage(
                    [
                        'text'                  => '同志们好，同志们辛苦了.',
                        'chat_id'               => $ChatID,
                        'reply_to_message_id'   => $Message->getMessageId(),
                    ]
                );
            }
        } else {
            // 新成员加入群组
            $NewUser = TelegramTools::getUser($Member['id']);
            $deNewChatMember = json_decode($NewChatMember, true);
            if (
                Config::get('group_bound_user') === true
                &&
                $ChatID == Config::get('telegram_chatid')
                &&
                $NewUser == null
                &&
                $deNewChatMember['is_bot'] == false
            ) {
                $bot->sendMessage(
                    [
                        'text'                  => '由于 ' . $Member['name'] . ' 未绑定账户，将被移除.',
                        'chat_id'               => $ChatID,
                        'reply_to_message_id'   => $Message->getMessageId(),
                    ]
                );
                TelegramTools::SendPost(
                    'kickChatMember',
                    [
                        'chat_id'   => $ChatID,
                        'user_id'   => $Member['id'],
                    ]
                );
                return;
            }
            if (Config::get('enable_welcome_message') === true) {
                $text = ($NewUser->class >= 1 ? '欢迎 VIP' . $NewUser->class . ' 用户 ' . $Member['name'] . '回到组织.' : '欢迎 ' . $Member['name']);
                $bot->sendMessage(
                    [
                        'text'                  => $text,
                        'chat_id'               => $ChatID,
                        'reply_to_message_id'   => $Message->getMessageId(),
                    ]
                );
            }
        }
    }
}
