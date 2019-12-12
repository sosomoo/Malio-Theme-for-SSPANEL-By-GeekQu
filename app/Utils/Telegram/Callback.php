<?php

namespace App\Utils\Telegram;

class Callback
{
    public static function CallbackQueryMethod($user, $bot, $Callback)
    {
        // 触发用户
        $SendUser = [
            'id'       => $Callback->getFrom()->getId(),
            'name'     => $Callback->getFrom()->getFirstName() . ' ' . $Callback->getFrom()->getLastName(),
            'username' => $Callback->getFrom()->getUsername(),
        ];

        // 消息会话 ID
        $ChatID = $Callback->getMessage()->getChat()->getId();

        // 回调数据
        $CallbackData = $Callback->getData();

        // 回调数据处理
        self::CallbackDataHandler($user, $bot, $Callback, $CallbackData, $ChatID, $SendUser);
    }

    /**
     * 
     * 回调数据处理
     * 
     */
    public static function CallbackDataHandler($user, $bot, $Callback, $CallbackData, $ChatID, $SendUser)
    {
        return;
    }
}
