<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class MyCommand.
 */
class MyCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'my';

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

        if ($ChatID < 0) {
            // 群组
            if (Config::get('new_telegram_group_quiet') === true) {
                // 群组中不回应
                return;
            }
            if ($ChatID != Config::get('new_telegram_group_chatid')) {
                // 非我方群组
                return;
            }
        }

        // 发送 '输入中' 会话状态
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // 触发用户
        $SendUser = [
            'id'       => $Message->getFrom()->getId(),
            'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];

        $User = User::where('telegram_id', $SendUser['id'])->first();
        if ($User == null) {
            // 回送信息
            $this->replyWithMessage(
                [
                    'text'       => '您未绑定本站账号，您可以进入网站的 **资料编辑**，在右下方绑定您的账号.',
                    'parse_mode' => 'Markdown',
                ]
            );
            return;
        }

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
        $text = [
            '您当前的流量状况：',
            '',
            '今日已使用[' . $User->TodayusedTrafficPercent(). '%]：' . $User->TodayusedTraffic(),
            '之前已使用[' . $User->LastusedTrafficPercent(). '%]：' . $User->LastusedTraffic(),
            '流量约剩余[' . $User->unusedTrafficPercent(). '%]：' . $User->unusedTraffic(),
        ];

        // 回送信息
        $this->replyWithMessage(
            [
                'text'                  => implode(PHP_EOL, $text),
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function Privacy($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        $text = [
            '您当前的流量状况：',
            '',
            '今日已使用[' . $User->TodayusedTrafficPercent(). '%]：' . $User->TodayusedTraffic(),
            '之前已使用[' . $User->LastusedTrafficPercent(). '%]：' . $User->LastusedTraffic(),
            '流量约剩余[' . $User->unusedTrafficPercent(). '%]：' . $User->unusedTraffic(),
        ];

        // 回送信息
        $this->replyWithMessage(
            [
                'text'                  => implode(PHP_EOL, $text),
                'parse_mode'            => 'Markdown',
            ]
        );
    }
}
