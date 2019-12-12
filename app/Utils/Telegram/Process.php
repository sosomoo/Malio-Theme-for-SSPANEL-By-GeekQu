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
                ]
            );
            $update = $bot->commandsHandler(true);

            if ($update->getCallbackQuery() !== null) {
                $id = $update->getCallbackQuery()->getFrom()->getId();
                $user = User::where('telegram_id', $id)->first();
                Callback::CallbackQueryMethod($user, $bot, $update->getCallbackQuery());
            }

            // $Message = $update->getMessage();
            // $MessageData = $Message->getText();

        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
