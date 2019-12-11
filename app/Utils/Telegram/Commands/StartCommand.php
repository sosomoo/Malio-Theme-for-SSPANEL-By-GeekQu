<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use App\Utils\TelegramSessionManager;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class StratCommand.
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Start Command to get you started';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $Update = $this->getUpdate();
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
            // 消息内容
            $MessageText = $Message->getText();
            if (strpos($MessageText, ' ') !== false) {
                $MessageText = str_replace('/' . $this->name, '', $MessageText);
                $BindCode = trim($MessageText);
                if (strlen($BindCode) == 16) {
                    $Uid = TelegramSessionManager::verify_bind_session($BindCode);
                    if ($Uid == 0) {
                        $text = '绑定失败了呢，经检查发现：【' . $BindCode . '】的有效期为 10 分钟，您可以在我们网站上的 **资料编辑** 页面刷新后重试.';
                    } else {
                        $BinsUser              = User::where('id', $Uid)->first();
                        $BinsUser->telegram_id = $SendUser['id'];
                        $BinsUser->im_type     = 4;
                        $BinsUser->im_value    = $SendUser['username'];
                        $BinsUser->save();
                        if ($BinsUser->is_admin >= 1) {
                            $text = '尊敬的**管理员**您好，恭喜绑定成功。' . PHP_EOL .'当前绑定邮箱为：' . $BinsUser->email;
                        } else {
                            if ($BinsUser->class >= 1) {
                                $text = '尊敬的 **VIP ' . $BinsUser->class . '** 用户您好.' . PHP_EOL . '恭喜您绑定成功，当前绑定邮箱为：' . $BinsUser->email;
                            } else {
                                $text = '绑定成功了，您的邮箱为：' . $BinsUser->email;
                            }
                        }
                    }
                }
                // 回送信息
                $this->replyWithMessage(
                    [
                        'text'       => $text,
                        'parse_mode' => 'Markdown',
                    ]
                );
            }
            // 触发 /help
            $this->triggerCommand('help');
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
                    'text' => '喵喵喵.',
                ]
            );
        }
    }
}
