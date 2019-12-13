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
}
