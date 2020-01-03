<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class UnbindCommand.
 */
class UnbindCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'unbind';

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

        $User = User::where('telegram_id', $SendUser['id'])->first();

        if ($ChatID > 0) {
            // 私人

            // 发送 '输入中' 会话状态
            $this->replyWithChatAction(['action' => Actions::TYPING]);

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

            // 消息内容
            $MessageText = trim($arguments);

            if ($MessageText == $User->email) {
                $temp = $User->TelegramReset();
                $text = $temp['msg'];
                // 回送信息
                $this->replyWithMessage(
                    [
                        'text'          => $text,
                        'parse_mode'    => 'Markdown',
                    ]
                );
                return;
            }

            $text = '发送 **/unbind 账户邮箱** 进行解绑.';
            if ($MessageText != '') {
                $text = '键入的 Email 地址与您的账户不匹配.';
            }

            // 回送信息
            $this->replyWithMessage(
                [
                    'text'                  => $text,
                    'parse_mode'            => 'Markdown',
                ]
            );
        }
    }
}
