<?php

namespace App\Utils\Telegram;

class Reply
{

    public static function getInlinekeyboard($user = null, $type = null)
    {
        $back = [
            [
                'text'          => '回主菜单',
                'callback_data' => 'index'
            ]
        ];
        if ($type === null) return $back;
        switch ($type) {
            case 'index':
                // 主菜单
                $notLogin = [
                    [
                        'text'          => '产品介绍',
                        'callback_data' => 'general.pricing'
                    ],
                    [
                        'text'          => '服务条款',
                        'callback_data' => 'general.terms'
                    ]
                ];
                $isLogin = [
                    [
                        [
                            'text'          => '用户中心',
                            'callback_data' => 'user.index'
                        ],
                        [
                            'text'          => '资料编辑',
                            'callback_data' => 'user.edit'
                        ],
                    ],
                    [
                        [
                            'text'          => '订阅中心',
                            'callback_data' => 'user.subscribe'
                        ],
                        [
                            'text'          => '分享计划',
                            'callback_data' => 'user.invite'
                        ],
                    ],
                ];
                if ($user != null) {
                    if ($user->isAdmin()) {
                        $text = '尊敬的管理员您好：';
                    } else {
                        if ($user->class > 0) {
                            $text = '尊敬的 VIP ' . $user->class . '您好：';
                        } else {
                            $text = '尊敬的用户您好：';
                        }
                    }
                    $return = [
                        'text'      => $text,
                        'keyboard'  => $isLogin,
                    ];
                } else {
                    $text = '游客您好，以下是 BOT 菜单：' . PHP_EOL . PHP_EOL . '本站用户请前往用户中心进行 Telegram 绑定操作.';
                    $return = [
                        'text'      => $text,
                        'keyboard'  => [
                            $notLogin,
                        ],
                    ];
                }
                break;
            case 'user.index':
                // 用户中心
                if ($user->class > 0) {
                    $text = '尊敬的 VIP ' . $user->class . ' 您好：';
                } else {
                    $text = '尊敬的用户您好：';
                }
                $keyboard = [
                    [
                        [
                            'text'          => '登录记录',
                            'callback_data' => 'user.index.login_log'
                        ],
                        [
                            'text'          => '使用记录',
                            'callback_data' => 'user.index.usage_log'
                        ],
                    ],
                    [
                        [
                            'text'          => '返利记录',
                            'callback_data' => 'user.index.rebate_log'
                        ],
                        [
                            'text'          => '订阅记录',
                            'callback_data' => 'user.index.subscribe_log'
                        ],
                    ],
                    $back
                ];
                $text .= (PHP_EOL . PHP_EOL .
                    '当前余额：' . $user->money .
                    PHP_EOL .
                    '在线设备：' . ($user->node_connector != 0 ? $user->online_ip_count() . ' / ' . $user->node_connector : $user->online_ip_count() . ' / 不限制') .
                    PHP_EOL .
                    '端口速率：' . ($user->node_speedlimit != 0 ? $user->node_speedlimit . 'Mbps' : '无限制') .
                    PHP_EOL .
                    '上次使用：' . $user->lastSsTime() .
                    PHP_EOL .
                    '过期时间：' . $user->class_expire);
                $return = [
                    'text'      => $text,
                    'keyboard'  => $keyboard,
                ];
                break;
            case 'user.edit':
                // 资料编辑
                if ($user->class > 0) {
                    $text = '尊敬的 VIP ' . $user->class . '您好：';
                } else {
                    $text = '尊敬的用户您好：';
                }
                $keyboard = [
                    [
                        'text'          => '重置订阅链接',
                        'callback_data' => 'user.edit.update_link'
                    ],
                    [
                        'text'          => '重置链接密码',
                        'callback_data' => 'user.edit.update_passwd'
                    ]
                ];
                $return = [
                    'text'      => $text,
                    'keyboard'  => [
                        $keyboard,
                        $back
                    ],
                ];
                break;
        }

        return $return;
    }
}
