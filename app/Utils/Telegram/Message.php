<?php

namespace App\Utils\Telegram;

class Message
{
    public static function MessageMethod($user, $bot, $Message)
    {
        // 触发用户
        $SendUser = [
            'id'       => $Message->getFrom()->getId(),
            'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];

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
                        $reply['message'] = '登录验证成功，邮箱：' . $user->email;
                    } else {
                        $reply['message'] = '登录验证失败，数字无效';
                    }
                }
            }
            return;
        }

        // // 图片内容
        // $PhotoData = $Message->getPhoto();
        // if ($PhotoData != null) {
        // }
    }
}
