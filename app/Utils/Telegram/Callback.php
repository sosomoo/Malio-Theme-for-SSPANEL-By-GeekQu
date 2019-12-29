<?php

namespace App\Utils\Telegram;

use App\Controllers\LinkController;
use App\Models\{LoginIp, Node, Ip, InviteCode, Payback, UserSubscribeLog};
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
                break;
            default:
                $sendMessage = [
                    'chat_id'                   => $Data['ChatID'],
                    'text'                      => '发生错误.',
                    'parse_mode'                => 'Markdown',
                ];
                $Data['AllowEditMessage'] = false;
                break;
        }

        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            Process::SendPost('editMessageText', $sendMessage);
            return;
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
        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
        $op_1 = $Operate[1];
        switch ($op_1) {
            case 'edit':
                // 资料编辑
                $op_2 = $Operate[2];
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
                        $keyboard = [
                            Reply::getInlinekeyboard()
                        ];
                        if (Config::get('protocol_specify') === true) {
                            if (isset($CallbackDataExplode[1])) {
                                if (in_array($CallbackDataExplode[1], Config::getSupportParam('method')) && Config::get('protocol_specify') === true) {
                                    $temp = $user->setMethod($CallbackDataExplode[1]);
                                    if ($temp['ok'] === true) {
                                        $text = '您当前的加密方式为：' . $user->method . PHP_EOL . PHP_EOL . $temp['msg'];
                                    } else {
                                        $text = '发生错误，请重新选择.' . PHP_EOL . PHP_EOL . $temp['msg'];
                                    }
                                } else {
                                    $text = '发生错误，请重新选择.';
                                }
                            } else {
                                $Encrypts = [];
                                foreach (Config::getSupportParam('method') as $value) {
                                    $Encrypts[] = [
                                        'text'          => $value,
                                        'callback_data' => 'user.edit.encrypt|' . $value
                                    ];
                                }
                                $Encrypts = array_chunk($Encrypts, 2);
                                $keyboard = [];
                                foreach ($Encrypts as $Encrypt) {
                                    $keyboard[] = $Encrypt;
                                }
                                $keyboard[] = Reply::getInlinekeyboard();
                                $text = '您当前的加密方式为：' . $user->method;
                            }
                        } else {
                            $text = '当前不允许私自更改.';
                        }
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $keyboard
                                ]
                            ),
                        ];
                        break;
                    case 'protocol':
                        // 协议更改
                        $keyboard = [
                            Reply::getInlinekeyboard()
                        ];
                        if (Config::get('protocol_specify') === true) {
                            if (isset($CallbackDataExplode[1])) {
                                if (in_array($CallbackDataExplode[1], Config::getSupportParam('protocol')) && Config::get('protocol_specify') === true) {
                                    $temp = $user->setProtocol($CallbackDataExplode[1]);
                                    if ($temp['ok'] === true) {
                                        $text = '您当前的协议为：' . $user->protocol . PHP_EOL . PHP_EOL . $temp['msg'];
                                    } else {
                                        $text = '发生错误，请重新选择.' . PHP_EOL . PHP_EOL . $temp['msg'];
                                    }
                                } else {
                                    $text = '发生错误，请重新选择.';
                                }
                            } else {
                                $Protocols = [];
                                foreach (Config::getSupportParam('protocol') as $value) {
                                    $Protocols[] = [
                                        'text'          => $value,
                                        'callback_data' => 'user.edit.protocol|' . $value
                                    ];
                                }
                                $Protocols = array_chunk($Protocols, 1);
                                $keyboard = [];
                                foreach ($Protocols as $Protocol) {
                                    $keyboard[] = $Protocol;
                                }
                                $keyboard[] = Reply::getInlinekeyboard();
                                $text = '您当前的协议为：' . $user->protocol;
                            }
                        } else {
                            $text = '当前不允许私自更改.';
                        }
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $keyboard
                                ]
                            ),
                        ];
                        break;
                    case 'obfs':
                        // 混淆更改
                        $keyboard = [
                            Reply::getInlinekeyboard()
                        ];
                        if (Config::get('protocol_specify') === true) {
                            if (isset($CallbackDataExplode[1])) {
                                if (in_array($CallbackDataExplode[1], Config::getSupportParam('obfs')) && Config::get('protocol_specify') === true) {
                                    $temp = $user->setObfs($CallbackDataExplode[1]);
                                    if ($temp['ok'] === true) {
                                        $text = '您当前的协议为：' . $user->obfs . PHP_EOL . PHP_EOL . $temp['msg'];
                                    } else {
                                        $text = '发生错误，请重新选择.' . PHP_EOL . PHP_EOL . $temp['msg'];
                                    }
                                } else {
                                    $text = '发生错误，请重新选择.';
                                }
                            } else {
                                $Obfss = [];
                                foreach (Config::getSupportParam('obfs') as $value) {
                                    $Obfss[] = [
                                        'text'          => $value,
                                        'callback_data' => 'user.edit.obfs|' . $value
                                    ];
                                }
                                $Obfss = array_chunk($Obfss, 1);
                                $keyboard = [];
                                foreach ($Obfss as $Obfs) {
                                    $keyboard[] = $Obfs;
                                }
                                $keyboard[] = Reply::getInlinekeyboard();
                                $text = '您当前的协议为：' . $user->obfs;
                            }
                        } else {
                            $text = '当前不允许私自更改.';
                        }
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $keyboard
                                ]
                            ),
                        ];
                        break;
                    case 'sendemail':
                        // 每日邮件设置更改
                        $keyboard = [
                            [
                                [
                                    'text'          => '更改开启/关闭',
                                    'callback_data' => 'user.edit.sendemail.update'
                                ]
                            ],
                            Reply::getInlinekeyboard()
                        ];
                        $op_3 = $Operate[3];
                        switch ($op_3) {
                            case 'update':
                                $user->sendDailyMail = ($user->sendDailyMail == 0 ? 1 : 0);
                                if ($user->save()) {
                                    $text = '设置更改成功，每日邮件接收当前设置为：';
                                    $text .= ($user->sendDailyMail == 0 ? '不发送' : '发送');
                                } else {
                                    $text = '发生错误.';
                                }
                                break;
                            default:
                                $text = '每日邮件接收当前设置为：';
                                $text .= ($user->sendDailyMail == 0 ? '不发送' : '发送');
                                break;
                        }
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $keyboard
                                ]
                            ),
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
                break;
            case 'subscribe':
                // 订阅中心
                if (isset($CallbackDataExplode[1])) {
                    $temp = [];
                    $temp['keyboard'] = [
                        Reply::getInlinekeyboard()
                    ];
                    $UserApiUrl = LinkController::getSubinfo($user, 0)['link'];
                    switch ($CallbackDataExplode[1]) {
                        default:
                            $temp['text'] = '该订阅链接为：' . PHP_EOL . PHP_EOL . $UserApiUrl . $CallbackDataExplode[1];
                            break;
                    }
                } else {
                    $temp = Reply::getInlinekeyboard($user, 'user.subscribe');
                }
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
                break;
            case 'invite':
                // 分享计划
                $op_2 = $Operate[2];
                switch ($op_2) {
                    case 'get':
                        $Data['AllowEditMessage'] = false;
                        $code = InviteCode::where('user_id', $user->id)->first();
                        if ($code == null) {
                            $user->addInviteCode();
                            $code = InviteCode::where('user_id', $user->id)->first();
                        }
                        $inviteUrl = Config::get('baseUrl') . '/auth/register?code=' . $code->code;
                        $text = '['. $inviteUrl . '](' . $inviteUrl . ')';
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => $text,
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => null
                        ];
                        break;
                    default:
                        if (!$paybacks_sum = Payback::where('ref_by', $user->id)->sum('ref_get')) {
                            $paybacks_sum = 0;
                        }
                        $text = [
                            '**分享计划，您每邀请 1 位用户注册：**',
                            '',
                            '- 您会获得 **' . Config::get('invite_gift') . 'G** 流量奖励.',
                            '- 对方将获得 **' . Config::get('invite_get_money') . ' 元** 奖励作为初始资金.',
                            '- 对方充值时您还会获得对方充值金额的 **' . Config::get('code_payback') . '%** 的返利.',
                            '',
                            '已获得返利：' . $paybacks_sum . ' 元.',
                        ];
                        $keyboard = [
                            [
                                [
                                    'text'          => '获取我的邀请链接',
                                    'callback_data' => 'user.invite.get'
                                ]
                            ],
                            Reply::getInlinekeyboard()
                        ];
                        $sendMessage = [
                            'chat_id'                   => $Data['ChatID'],
                            'message_id'                => $Data['MessageID'],
                            'text'                      => implode(PHP_EOL, $text),
                            'parse_mode'                => 'Markdown',
                            'disable_web_page_preview'  => false,
                            'reply_to_message_id'       => null,
                            'reply_markup'              => json_encode(
                                [
                                    'inline_keyboard' => $keyboard
                                ]
                            ),
                        ];
                        break;
                }
                break;
            default:
                // 用户中心
                $op_2 = $Operate[2];
                switch ($op_2) {
                    case 'login_log':
                        // 登录记录
                        $iplocation = new QQWry();
                        $totallogin = LoginIp::where('userid', '=', $user->id)->where('type', '=', 0)->orderBy('datetime', 'desc')->take(10)->get();
                        $userloginip = [];
                        foreach ($totallogin as $single) {
                            $location = $iplocation->getlocation($single->ip);
                            $loginiplocation = iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']);
                            if (!in_array($loginiplocation, $userloginip)) {
                                $userloginip[] = $loginiplocation;
                            }
                        }
                        $text = ('以下是您最近 10 次的登录位置：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode('、', $userloginip));
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
                        break;
                    case 'rebate_log':
                        // 返利记录
                        $paybacks = Payback::where('ref_by', $user->id)->orderBy('datetime', 'desc')->take(10)->get();
                        $temp = [];
                        foreach ($paybacks as $payback) {
                            $temp[] = '`#' . $payback->id . '：' . ($payback->user() != null ? $payback->user()->user_name : '已注销') . '：' . $payback->ref_get . ' 元`';
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
                        break;
                    case 'subscribe_log':
                        // 订阅记录
                        $iplocation = new QQWry();
                        $logs = UserSubscribeLog::orderBy('id', 'desc')->where('user_id', $user->id)->take(10)->get();
                        $temp = [];
                        foreach ($logs as $log) {
                            $location = $iplocation->getlocation($log->request_ip);
                            $temp[] = '`' . $log->request_time . ' 在 <' . $log->request_ip . '> ' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']) . '访问了 ' . $log->subscribe_type . ' 订阅`';
                        }
                        $text = ('以下是您最近 10 条订阅记录：' .
                            PHP_EOL .
                            PHP_EOL .
                            implode(PHP_EOL . PHP_EOL, $temp));
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
                        break;
                }
                break;
        }
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            Process::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }
}
