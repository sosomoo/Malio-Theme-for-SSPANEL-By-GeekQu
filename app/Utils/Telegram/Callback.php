<?php

namespace App\Utils\Telegram;

use App\Models\{LoginIp, Node, Ip, Payback, UserSubscribeLog};
use App\Services\Config;
use App\Utils\{Tools, QQWry};


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

        // 触发源消息发送时间
        $MessageSendTime = $Callback->getMessage()->getDate();

        // 消息是否可编辑
        $AllowEditMessage = (time() < $MessageSendTime + 172800
            ? true
            : false);

        $Data = [
            'CallbackData'      => $Callback->getData(),                            // 回调数据
            'ChatID'            => $Callback->getMessage()->getChat()->getId(),     // 消息会话 ID
            'MessageID'         => $Callback->getMessage()->getMessageId(),         // 触发源信息 ID
            'AllowEditMessage'  => $AllowEditMessage,                               // 消息是否可编辑
        ];

        if ($Data['ChatID'] < 0) {
            // 群组
            if (Config::get('new_telegram_group_quiet') === true) {
                // 群组中不回应
                return;
            }
        }

        switch (true) {
            case (strpos($Data['CallbackData'], 'user.') === 0 && $user != null):
                // 用户相关
                self::UserHandler($user, $bot, $Callback, $Data, $SendUser);
                break;
            default:
                // 回调数据处理
                self::CallbackDataHandler($user, $bot, $Callback, $Data, $SendUser);
                break;
        }
    }

    /**
     *
     * 回调数据处理
     *
     */
    public static function CallbackDataHandler($user, $bot, $Callback, $Data, $SendUser)
    {
        switch ($Data['CallbackData']) {
            case 'index':
                // 主菜单
                $temp = Reply::getInlinekeyboard($user, 'index');
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'message_id'                => $Data['MessageID'],
                    'text'                      => $temp['text'],
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => json_encode(
                        [
                            'inline_keyboard' => $temp['keyboard']
                        ]
                    ),
                ];
                if ($Data['AllowEditMessage']) {
                    // 消息可编辑
                    Process::SendPost('editMessageText', $sendMessage);
                    return;
                }
                break;
            case 'general.pricing':
                // 产品介绍
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'message_id'                => $Data['MessageID'],
                    'text'                      => '产品介绍',
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => json_encode(
                        [
                            'inline_keyboard' => [
                                Reply::getInlinekeyboard()
                            ]
                        ]
                    ),
                ];
                if ($Data['AllowEditMessage']) {
                    // 消息可编辑
                    Process::SendPost('editMessageText', $sendMessage);
                    return;
                }
                break;
            case 'general.terms':
                // 服务条款
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'message_id'                => $Data['MessageID'],
                    'text'                      => '服务条款',
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => json_encode(
                        [
                            'inline_keyboard' => [
                                Reply::getInlinekeyboard()
                            ]
                        ]
                    ),
                ];
                if ($Data['AllowEditMessage']) {
                    // 消息可编辑
                    Process::SendPost('editMessageText', $sendMessage);
                    return;
                }
                break;
            default:
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'text'                      => '发生错误.',
                    'parse_mode'                => 'Markdown',
                ];
                break;
        }

        $bot->sendMessage($sendMessage);
    }

    /**
     *
     * 用户相关回调数据处理
     *
     */
    public static function UserHandler($user, $bot, $Callback, $Data, $SendUser)
    {
        $CallbackDataExplode = explode('.', $Data['CallbackData']);
        $op_1 = $CallbackDataExplode[1];
        switch ($op_1) {
            case 'edit':
                // 资料编辑
                $op_2 = $CallbackDataExplode[2];
                switch ($op_2) {
                    case 'update_link':
                        // 重置订阅链接
                        $temp = Reply::getInlinekeyboard($user, 'index');
                        $user->clean_link();
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => '订阅链接重置成功.',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $temp['keyboard']
                                ]
                            ),
                        ];
                        break;
                    case 'update_passwd':
                        // 重置链接密码
                        $temp = Reply::getInlinekeyboard($user, 'index');
                        $user->passwd = Tools::genRandomChar(8);
                        if ($user->save()) {
                            $sendMessage = [
                                'chat_id'                   => $Data['ChatID'],
                                'message_id'                => $Data['MessageID'],
                                'text'                      => '连接密码更新成功.' . PHP_EOL . '新密码为：' . $user->passwd,
                                'parse_mode'                => 'Markdown',
                                'disable_web_page_preview'  => false,
                                'reply_to_message_id'       => null,
                                'reply_markup'              => json_encode(
                                    [
                                        'inline_keyboard' => $temp['keyboard']
                                    ]
                                ),
                            ];
                        } else {
                            $sendMessage = [
                                'chat_id'                   => $Data['ChatID'],
                                'message_id'                => $Data['MessageID'],
                                'text'                      => '出现错误，连接密码更新失败，请联系管理员.',
                                'parse_mode'                => 'Markdown',
                                'disable_web_page_preview'  => false,
                                'reply_to_message_id'       => null,
                                'reply_markup'              => json_encode(
                                    [
                                        'inline_keyboard' => $temp['keyboard']
                                    ]
                                ),
                            ];
                        }
                        break;
                    case 'encrypt':
                        // 加密方式更改
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => 'ing.',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => null
                        ];
                        break;
                    case 'protocol':
                        // 协议更改
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => 'ing.',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => null
                        ];
                        break;
                    case 'obfs':
                        // 混淆更改
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => 'ing.',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => null
                        ];
                        break;
                    case 'sendemail':
                        // 每日邮件设置更改
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => 'ing.',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => null
                        ];
                        break;
                    default:
                        $temp = Reply::getInlinekeyboard($user, 'user.edit');
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => '您可在此编辑您的资料或连接信息：',
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $temp['keyboard']
                                ]
                            ),
                        ];
                        break;
                }
                if ($Data['AllowEditMessage']) {
                    // 消息可编辑
                    Process::SendPost('editMessageText', $sendMessage);
                    return;
                }
                break;
            case 'subscribe':
                // 订阅中心
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'message_id'                => $Data['MessageID'],
                    'text'                      => 'ing.',
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                break;
            case 'invite':
                // 分享计划
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'message_id'                => $Data['MessageID'],
                    'text'                      => 'ing.',
                    'parse_mode'                => 'Markdown',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                break;
            default:
                // 用户中心
                $op_2 = $CallbackDataExplode[2];
                switch ($op_2) {
                    case 'login_log':
                        // 登录记录
                        $iplocation = new QQWry();
                        $totallogin = LoginIp::where('userid', '=', $user->id)->where('type', '=', 0)->orderBy('datetime', 'desc')->take(10)->get();
                        $userloginip = [];
                        foreach ($totallogin as $single) {
                            $location = $iplocation->getlocation($single->ip);
                            $userloginip[] = date('Y-m-d H:i:s', $single->datetime) . ' 在 「' . $single->ip . '」 ' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']);
                        }
                        $text = ('以下是您最近 10 次的登录记录：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode(PHP_EOL, $userloginip));
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => [
                                        Reply::getInlinekeyboard()
                                    ]
                                ]
                            ),
                        ];
                        if ($Data['AllowEditMessage']) {
                            // 消息可编辑
                            Process::SendPost('editMessageText', $sendMessage);
                            return;
                        }
                        break;
                    case 'usage_log':
                        // 使用记录
                        $iplocation = new QQWry();
                        $total = Ip::where('datetime', '>=', time() - 300)->where('userid', '=', $user->id)->get();
                        $userip = [];
                        foreach ($total as $single) {
                            $single->ip = Tools::getRealIp($single->ip);
                            $is_node = Node::where('node_ip', $single->ip)->first();
                            if ($is_node) {
                                continue;
                            }
                            $location = $iplocation->getlocation($single->ip);
                            $userip[$single->ip] = '「' . $single->ip . '」 ' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']);
                        }
                        $text = ('以下是您最近 5 分钟的使用 IP：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode(PHP_EOL, $userip));
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => [
                                        Reply::getInlinekeyboard()
                                    ]
                                ]
                            ),
                        ];
                        if ($Data['AllowEditMessage']) {
                            // 消息可编辑
                            Process::SendPost('editMessageText', $sendMessage);
                            return;
                        }
                        break;
                    case 'rebate_log':
                        // 返利记录
                        $paybacks = Payback::where('ref_by', $user->id)->orderBy('datetime', 'desc')->take(10)->get();
                        $temp = [];
                        foreach ($paybacks as $payback) {
                            $temp[] = '#' . $payback->id . '：' . ($payback->user() != null ? $payback->user()->user_name : '已注销') . '：' . $payback->ref_get . ' 元';
                        }
                        $text = ('以下是您最近 10 条返利记录：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode(PHP_EOL, $temp));
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => [
                                        Reply::getInlinekeyboard()
                                    ]
                                ]
                            ),
                        ];
                        if ($Data['AllowEditMessage']) {
                            // 消息可编辑
                            Process::SendPost('editMessageText', $sendMessage);
                            return;
                        }
                        break;
                    case 'subscribe_log':
                        // 订阅记录
                        $iplocation = new QQWry();
                        $logs = UserSubscribeLog::orderBy('id', 'desc')->where('user_id', $user->id)->take(10)->get();
                        $temp = [];
                        foreach ($logs as $log) {
                            $location = $iplocation->getlocation($log->request_ip);
                            $temp[] = $log->subscribe_type . '：' . $log->request_ip . ':' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']) . '：' . $log->request_time;
                        }
                        $text = ('以下是您最近 10 条订阅记录：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode(PHP_EOL, $temp));
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => [
                                        Reply::getInlinekeyboard()
                                    ]
                                ]
                            ),
                        ];
                        if ($Data['AllowEditMessage']) {
                            // 消息可编辑
                            Process::SendPost('editMessageText', $sendMessage);
                            return;
                        }
                        break;
                    default:
                        $temp = Reply::getInlinekeyboard($user, 'user.index');
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $temp['text'],
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $temp['keyboard']
                                ]
                            ),
                        ];
                        if ($Data['AllowEditMessage']) {
                            // 消息可编辑
                            Process::SendPost('editMessageText', $sendMessage);
                            return;
                        }

                        break;
                }
                break;
        }
        $bot->sendMessage($sendMessage);
    }
}
