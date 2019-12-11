<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = '菜单';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $Update  = $this->getUpdate();
        $Message = $Update->getMessage();

        if ($Message->getChat()->getId() > 0) {
            // 私人会话

            // 发送 '输入中' 会话状态
            $this->replyWithChatAction(['action' => Actions::TYPING]);

            // 触发用户
            $SendUser = [
                'id'       => $Message->getFrom()->getId(),
                'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
                'username' => $Message->getFrom()->getUsername(),
            ];

            // 回送信息
            $this->replyWithMessage(
                [
                    'text' => '喵？',
                ]
            );
        } else {
            // 群组

            if (Config::get('new_telegram_group_quiet') === true) {
                // 群组中不回应
                return;
            }

            // 发送 '输入中' 会话状态
            $this->replyWithChatAction(['action' => Actions::TYPING]);

            // 回送信息
            $this->replyWithMessage(
                [
                    'text' => '喵？',
                ]
            );
        }
    }
}
