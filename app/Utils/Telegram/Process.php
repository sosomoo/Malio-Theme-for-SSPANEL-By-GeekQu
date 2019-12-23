<?php

namespace App\Utils\Telegram;

use App\Models\User;
use App\Services\Config;
use Telegram\Bot\Api;
use Exception;

class Process
{
    public static function index()
    {
        try {
            $bot = new Api(Config::get('new_telegram_token'));
            $bot->addCommands(
                [
                    Commands\HelpCommand::class,
                    Commands\StartCommand::class,
                    Commands\PingCommand::class,
                    Commands\CheckinCommand::class,
                    Commands\MyCommand::class,
                    Commands\UnbindCommand::class,
                ]
            );
            $update = $bot->commandsHandler(true);
            if ($update->getCallbackQuery() !== null) {
                $id = $update->getCallbackQuery()->getFrom()->getId();
                $user = self::getUser($id);
                Callback::CallbackQueryMethod($user, $bot, $update->getCallbackQuery());
            }
            if ($update->getMessage() !== null) {
                $id = $update->getMessage()->getFrom()->getId();
                $user = self::getUser($id);
                Message::MessageMethod($user, $bot, $update->getMessage());
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    public static function getUser($telegram_id)
    {
        return User::where('telegram_id', $telegram_id)->first();
    }

    /**
     * Sends a POST request to Telegram Bot API.
     * 伪异步，无结果返回.
     *
     * @param array $params
     *
     * @return string
     */
    public static function SendPost($Method, $Params)
    {
        $URL = 'https://api.telegram.org/bot' . Config::get('new_telegram_token') . '/' . $Method;
        $POSTData = json_encode($Params);
        $C = curl_init();
        curl_setopt($C, CURLOPT_URL, $URL);
        curl_setopt($C, CURLOPT_POST, 1);
        curl_setopt($C, CURLOPT_HTTPHEADER, ['Content-Type:application/json; charset=utf-8']);
        curl_setopt($C, CURLOPT_POSTFIELDS, $POSTData);
        curl_setopt($C, CURLOPT_TIMEOUT, 1);
        curl_exec($C);
        curl_close($C);
    }
}
