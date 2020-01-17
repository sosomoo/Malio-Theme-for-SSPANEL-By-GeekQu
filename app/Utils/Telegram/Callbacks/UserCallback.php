<?php

namespace App\Utils\Telegram\Callbacks;

use App\Controllers\LinkController;
use App\Models\{LoginIp, Node, Ip, InviteCode, Payback, UserSubscribeLog};
use App\Services\Config;
use App\Utils\{Tools, QQWry};
use App\Utils\Telegram\{Reply, TelegramTools};

class UserCallback
{
    /**
     *
     * 用户相关回调数据处理
     *
     */
    public static function handler($bot, $Callback, $Data, $SendUser)
    {
        $user = TelegramTools::getUser($SendUser['id']);
        if ($user == null) {
            if ($Data['ChatID'] < 0) {
                return TelegramTools::SendPost(
                    'answerCallbackQuery',
                    [
                        'callback_query_id' => $Callback->getId(),
                        'text'              => '您好，您尚未绑定账户，无法进行操作.',
                        'show_alert'        => true,
                    ]
                );
            } else {
                return Callback::CallbackDataHandler($bot, $Callback, $Data, $SendUser);
            }
        }
        if ($user->telegram_id != $SendUser['id']) {
            return TelegramTools::SendPost(
                'answerCallbackQuery',
                [
                    'callback_query_id' => $Callback->getId(),
                    'text'              => '您好，您无法操作他人的账户.',
                    'show_alert'        => true,
                ]
            );
        }

        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
        $op_1 = $Operate[1];
        switch ($op_1) {
            case 'edit':
                // 资料编辑
                self::UserEdit($user, $bot, $Callback, $Data, $SendUser);
                break;
            case 'subscribe':
                // 订阅中心
                self::UserSubscribe($user, $bot, $Callback, $Data, $SendUser);
                break;
            case 'invite':
                // 分享计划
                self::UserInvite($user, $bot, $Callback, $Data, $SendUser);
                break;
            case 'checkin':
                // 签到
                self::UserCheckin($user, $bot, $Callback, $Data, $SendUser);
                break;
            default:
                // 用户中心
                self::UserCenter($user, $bot, $Callback, $Data, $SendUser);
                break;
        }
    }

    /**
     *
     * 用户中心
     *
     */
    public static function UserCenter($user, $bot, $Callback, $Data, $SendUser)
    {
        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
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
                $text = ('<strong>以下是您最近 10 次的登录位置：</strong>' .
                    PHP_EOL .
                    PHP_EOL .
                    implode('、', $userloginip));
                $sendMessage = [
                    'text'                      => $text,
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
                    $userip[$single->ip] = '[' . $single->ip . '] ' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']);
                }
                $text = ('<strong>以下是您最近 5 分钟的使用 IP：</strong>' .
                    PHP_EOL .
                    PHP_EOL .
                    implode(PHP_EOL, $userip));
                $sendMessage = [
                    'text'                      => $text,
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
                    $temp[] = '<code>#' . $payback->id . '：' . ($payback->user() != null ? $payback->user()->user_name : '已注销') . '：' . $payback->ref_get . ' 元</code>';
                }
                $text = ('<strong>以下是您最近 10 条返利记录：</strong>' .
                    PHP_EOL .
                    PHP_EOL .
                    implode(PHP_EOL, $temp));
                $sendMessage = [
                    'text'                      => $text,
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
                    $temp[] = '<code>' . $log->request_time . ' 在 [' . $log->request_ip . '] ' . iconv('gbk', 'utf-8//IGNORE', $location['country'] . $location['area']) . ' 访问了 ' . $log->subscribe_type . ' 订阅</code>';
                }
                $text = ('<strong>以下是您最近 10 条订阅记录：</strong>' .
                    PHP_EOL .
                    PHP_EOL .
                    implode(PHP_EOL . PHP_EOL, $temp));
                $sendMessage = [
                    'text'                      => $text,
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
                    'text'                      => $temp['text'],
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
        $sendMessage = array_merge(
            $sendMessage,
            [
                'chat_id'       => $Data['ChatID'],
                'message_id'    => $Data['MessageID'],
                'parse_mode'    => 'HTML',
            ]
        );
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            TelegramTools::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }

    /**
     *
     * 用户编辑
     *
     */
    public static function UserEdit($user, $bot, $Callback, $Data, $SendUser)
    {
        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
        $op_2 = $Operate[2];
        switch ($op_2) {
            case 'update_link':
                // 重置订阅链接
                $temp = Reply::getInlinekeyboard($user, 'user.subscribe');
                $user->clean_link();
                $sendMessage = [
                    'text'                      => '订阅链接重置成功，请在下方重新更新订阅.',
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
                $user->passwd = Tools::genRandomChar(8);
                if ($user->save()) {
                    $temp = Reply::getInlinekeyboard($user, 'user.subscribe');
                    $sendMessage = [
                        'text'                      => '连接密码更新成功，请在下方重新更新订阅.' . PHP_EOL . PHP_EOL . '新的连接密码为：' . $user->passwd,
                        'disable_web_page_preview'  => false,
                        'reply_to_message_id'       => null,
                        'reply_markup'              => json_encode(
                            [
                                'inline_keyboard' => $temp['keyboard']
                            ]
                        ),
                    ];
                } else {
                    $temp = Reply::getInlinekeyboard();
                    $sendMessage = [
                        'text'                      => '出现错误，连接密码更新失败，请联系管理员.',
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
                    'text'                      => $text,
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
                    'text'                      => $text,
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
                    'text'                      => $text,
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
                            $text .= '<strong>';
                            $text .= ($user->sendDailyMail == 0 ? '不发送' : '发送');
                            $text .= '</strong>';
                        } else {
                            $text = '发生错误.';
                        }
                        break;
                    default:
                        $text = '每日邮件接收当前设置为：';
                        $text .= '<strong>';
                        $text .= ($user->sendDailyMail == 0 ? '不发送' : '发送');
                        $text .= '</strong>';
                        break;
                }
                $sendMessage = [
                    'text'                      => $text,
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => json_encode(
                        [
                            'inline_keyboard' => $keyboard
                        ]
                    ),
                ];
                break;
            case 'unbind':
                // Telegram 账户解绑
                $Data['AllowEditMessage'] = false;
                $sendMessage = [
                    'text'                      => '发送 <strong>/unbind 账户邮箱</strong> 进行解绑.',
                    'disable_web_page_preview'  => false,
                    'reply_to_message_id'       => null,
                    'reply_markup'              => null
                ];
                break;
            case 'unban':
                // 群组解封
                TelegramTools::SendPost(
                    'unbanChatMember',
                    [
                        'chat_id'   => Config::get('telegram_chatid'),
                        'user_id'   => $SendUser['id'],
                    ]
                );
                return TelegramTools::SendPost(
                    'answerCallbackQuery',
                    [
                        'callback_query_id' => $Callback->getId(),
                        'text'              => '已提交解封，如您仍无法加入群组，请联系管理员.',
                        'show_alert'        => true,
                    ]
                );
                break;
            default:
                $temp = Reply::getInlinekeyboard($user, 'user.edit');
                $text = '您可在此编辑您的资料或连接信息：' . PHP_EOL . PHP_EOL;
                $text .= '端口：' . $user->port . PHP_EOL;
                $text .= '密码：' . $user->passwd . PHP_EOL;
                $text .= '加密：' . $user->method . PHP_EOL;
                $text .= '协议：' . $user->protocol . PHP_EOL;
                $text .= '混淆：' . $user->obfs;
                $sendMessage = [
                    'text'                      => $text,
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
        $sendMessage = array_merge(
            $sendMessage,
            [
                'chat_id'       => $Data['ChatID'],
                'message_id'    => $Data['MessageID'],
                'parse_mode'    => 'HTML',
            ]
        );
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            TelegramTools::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }

    /**
     *
     * 用户订阅
     *
     */
    public static function UserSubscribe($user, $bot, $Callback, $Data, $SendUser)
    {
        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
        // 订阅中心
        if (isset($CallbackDataExplode[1])) {
            $temp = [];
            $temp['keyboard'] = [
                Reply::getInlinekeyboard()
            ];
            $UserApiUrl = LinkController::getSubinfo($user, 0)['link'];
            switch ($CallbackDataExplode[1]) {
                case '?clash=1':
                    $temp['text'] = '您的 Clash 配置文件.' . PHP_EOL . '同时，您也可使用该订阅链接：' . $UserApiUrl . $CallbackDataExplode[1];
                    $token = LinkController::GenerateSSRSubCode($user->id, 0);
                    $filename = 'Clash_' . $token . '_' . time() . '.yaml';
                    $filepath = BASE_PATH . '/storage/SendTelegram/' . $filename;
                    $fh = fopen($filepath, 'w+');
                    $string = LinkController::getClash($user, 1, [], [], false, 0);
                    fwrite($fh, $string);
                    fclose($fh);
                    $bot->sendDocument(
                        [
                            'chat_id'       => $Data['ChatID'],
                            'document'      => $filepath,
                            'caption'       => $temp['text'],
                        ]
                    );
                    unlink($filepath);
                    break;
                case '?clash=2':
                    $temp['text'] = '您的 ClashR 配置文件.' . PHP_EOL . '同时，您也可使用该订阅链接：' . $UserApiUrl . $CallbackDataExplode[1];
                    $token = LinkController::GenerateSSRSubCode($user->id, 0);
                    $filename = 'ClashR_' . $token . '_' . time() . '.yaml';
                    $filepath = BASE_PATH . '/storage/SendTelegram/' . $filename;
                    $fh = fopen($filepath, 'w+');
                    $string = LinkController::getClash($user, 2, [], [], false, 0);
                    fwrite($fh, $string);
                    fclose($fh);
                    $bot->sendDocument(
                        [
                            'chat_id'       => $Data['ChatID'],
                            'document'      => $filepath,
                            'caption'       => $temp['text'],
                        ]
                    );
                    unlink($filepath);
                    break;
                case '?quantumult=3':
                    $temp['text'] = '点击打开配置文件，选择分享 <strong>拷贝到 Quantumult</strong>，选择更新配置.';
                    $token = LinkController::GenerateSSRSubCode($user->id, 0);
                    $filename = 'Quantumult_' . $token . '_' . time() . '.conf';
                    $filepath = BASE_PATH . '/storage/SendTelegram/' . $filename;
                    $fh = fopen($filepath, 'w+');
                    $string = LinkController::GetQuantumult($user, 3, [], [], false, 0);
                    fwrite($fh, $string);
                    fclose($fh);
                    $bot->sendDocument(
                        [
                            'chat_id'       => $Data['ChatID'],
                            'document'      => $filepath,
                            'caption'       => $temp['text'],
                        ]
                    );
                    unlink($filepath);
                    break;
                case '?surge=2':
                    $temp['text'] = '点击打开配置文件，选择分享 <strong>拷贝到 Surge</strong>，点击启动.';
                    $token = LinkController::GenerateSSRSubCode($user->id, 0);
                    $filename = 'Surge_' . $token . '_' . time() . '.conf';
                    $filepath = BASE_PATH . '/storage/SendTelegram/' . $filename;
                    $fh = fopen($filepath, 'w+');
                    $string = LinkController::getSurge($user, 2, [], [], false, 0);
                    fwrite($fh, $string);
                    fclose($fh);
                    $bot->sendDocument(
                        [
                            'chat_id'       => $Data['ChatID'],
                            'document'      => $filepath,
                            'caption'       => $temp['text'],
                        ]
                    );
                    unlink($filepath);
                    break;
                case '?surge=3':
                    $temp['text'] = '点击打开配置文件，选择分享 <strong>拷贝到 Surge</strong>，点击启动.';
                    $token = LinkController::GenerateSSRSubCode($user->id, 0);
                    $filename = 'Surge_' . $token . '_' . time() . '.conf';
                    $filepath = BASE_PATH . '/storage/SendTelegram/' . $filename;
                    $fh = fopen($filepath, 'w+');
                    $string = LinkController::getSurge($user, 3, [], [], false, 0);
                    fwrite($fh, $string);
                    fclose($fh);
                    $bot->sendDocument(
                        [
                            'chat_id'       => $Data['ChatID'],
                            'document'      => $filepath,
                            'caption'       => $temp['text'],
                        ]
                    );
                    unlink($filepath);
                    break;
                default:
                    $temp['text'] = '该订阅链接为：' . PHP_EOL . PHP_EOL . $UserApiUrl . $CallbackDataExplode[1];
                    break;
            }
        } else {
            $temp = Reply::getInlinekeyboard($user, 'user.subscribe');
        }
        $sendMessage = [
            'text'                      => $temp['text'],
            'disable_web_page_preview'  => false,
            'reply_to_message_id'       => null,
            'reply_markup'              => json_encode(
                [
                    'inline_keyboard' => $temp['keyboard']
                ]
            ),
        ];
        $sendMessage = array_merge(
            $sendMessage,
            [
                'chat_id'       => $Data['ChatID'],
                'message_id'    => $Data['MessageID'],
                'parse_mode'    => 'HTML',
            ]
        );
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            TelegramTools::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }

    /**
     *
     * 分享计划
     *
     */
    public static function UserInvite($user, $bot, $Callback, $Data, $SendUser)
    {
        $CallbackDataExplode = explode('|', $Data['CallbackData']);
        $Operate = explode('.', $CallbackDataExplode[0]);
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
                $text = '<a href="' . $inviteUrl . '">' . $inviteUrl . '</a>';
                $sendMessage = [
                    'text'                      => $text,
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
                    '<strong>分享计划，您每邀请 1 位用户注册：</strong>',
                    '',
                    '- 您会获得 <strong>' . Config::get('invite_gift') . 'G</strong> 流量奖励.',
                    '- 对方将获得 <strong>' . Config::get('invite_get_money') . ' 元</strong> 奖励作为初始资金.',
                    '- 对方充值时您还会获得对方充值金额的 <strong>' . Config::get('code_payback') . '%</strong> 的返利.',
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
                    'text'                      => implode(PHP_EOL, $text),
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
        $sendMessage = array_merge(
            $sendMessage,
            [
                'chat_id'       => $Data['ChatID'],
                'message_id'    => $Data['MessageID'],
                'parse_mode'    => 'HTML',
            ]
        );
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            TelegramTools::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }

    /**
     *
     * 每日签到
     *
     */
    public static function UserCheckin($user, $bot, $Callback, $Data, $SendUser)
    {
        $checkin = $user->checkin();
        $text = $checkin['msg'];
        // 回送信息
        $sendMessage = [
            'text'                  => $text,
            'reply_to_message_id'   => $Data['MessageID'],
            'parse_mode'            => 'Markdown',
            'reply_markup'          => json_encode(
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text'          => '已签到',
                                'callback_data' => 'user.checkin.' . $SendUser['id']
                            ]
                        ],
                    ]
                ]
            ),
        ];
        $sendMessage = array_merge(
            $sendMessage,
            [
                'chat_id'       => $Data['ChatID'],
                'message_id'    => $Data['MessageID'],
                'parse_mode'    => 'HTML',
            ]
        );
        if ($Data['AllowEditMessage']) {
            // 消息可编辑
            TelegramTools::SendPost('editMessageText', $sendMessage);
            return;
        }
        $bot->sendMessage($sendMessage);
    }
}
