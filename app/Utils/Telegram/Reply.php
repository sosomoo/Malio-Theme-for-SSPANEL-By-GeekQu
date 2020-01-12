<?php

namespace App\Utils\Telegram;

use App\Services\Config;

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
                    [
                        [
                            'text'          => '签到',
                            'callback_data' => 'user.checkin.' . $user->telegram_id
                        ],
                    ],
                ];
                if ($user != null) {
                    $text = self::getUserTitle($user);
                    $text .= PHP_EOL . PHP_EOL;
                    $text .= self::getUserInfo($user);
                    if (Config::get('show_group_link') === true) {
                        $isLogin[] = [
                            [
                                'text'  => '加入用户群',
                                'url'   => Config::get('telegram_group_link')
                            ],
                        ];
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
                $text = self::getUserTitle($user);
                $text .= PHP_EOL . PHP_EOL;
                $text .= self::getUserTrafficInfo($user);
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
                $return = [
                    'text'      => $text,
                    'keyboard'  => $keyboard,
                ];
                break;
            case 'user.edit':
                // 资料编辑
                $text = self::getUserTitle($user);
                $keyboard = [
                    [
                        [
                            'text'          => '重置订阅链接',
                            'callback_data' => 'user.edit.update_link'
                        ],
                        [
                            'text'          => '重置链接密码',
                            'callback_data' => 'user.edit.update_passwd'
                        ]
                    ],
                    [
                        [
                            'text'          => '更改加密方式',
                            'callback_data' => 'user.edit.encrypt'
                        ],
                        [
                            'text'          => '更改协议类型',
                            'callback_data' => 'user.edit.protocol'
                        ]
                    ],
                    [
                        [
                            'text'          => '更改混淆类型',
                            'callback_data' => 'user.edit.obfs'
                        ],
                        [
                            'text'          => '每日邮件接收',
                            'callback_data' => 'user.edit.sendemail'
                        ],
                    ],
                    [
                        [
                            'text'          => '账户解绑',
                            'callback_data' => 'user.edit.unbind'
                        ],
                        [
                            'text'          => '群组解封',
                            'callback_data' => 'user.edit.unban'
                        ],
                    ],
                    $back
                ];
                $return = [
                    'text'      => $text,
                    'keyboard'  => $keyboard,
                ];
                break;
            case 'user.subscribe':
                // 订阅中心
                $text = '订阅中心.';
                $keyboard = [
                    [
                        [
                            'text'          => 'SSR 订阅',
                            'callback_data' => 'user.subscribe|?sub=1'
                        ],
                        [
                            'text'          => 'V2Ray 订阅',
                            'callback_data' => 'user.subscribe|?sub=3'
                        ]
                    ],
                    [
                        [
                            'text'          => 'Shadowrocket',
                            'callback_data' => 'user.subscribe|?shadowrocket=1'
                        ],
                        [
                            'text'          => 'Kitsunebi',
                            'callback_data' => 'user.subscribe|?kitsunebi=1'
                        ]
                    ],
                    [
                        [
                            'text'          => 'Clash',
                            'callback_data' => 'user.subscribe|?clash=1'
                        ],
                        [
                            'text'          => 'ClashR',
                            'callback_data' => 'user.subscribe|?clash=2'
                        ],
                    ],
                    [
                        [
                            'text'          => 'Surge 2',
                            'callback_data' => 'user.subscribe|?surge=2'
                        ],
                        [
                            'text'          => 'Surge 3',
                            'callback_data' => 'user.subscribe|?surge=3'
                        ],
                    ],
                    [
                        [
                            'text'          => 'Quantumult V2Ray',
                            'callback_data' => 'user.subscribe|?quantumult=1'
                        ],
                        [
                            'text'          => 'SSD',
                            'callback_data' => 'user.subscribe|?ssd=1'
                        ],
                    ],
                    [
                        [
                            'text'          => 'Quantumult Conf',
                            'callback_data' => 'user.subscribe|?quantumult=3'
                        ],
                        [
                            'text'          => 'Surfboard',
                            'callback_data' => 'user.subscribe|?surfboard=1'
                        ],
                    ],
                    $back
                ];
                $return = [
                    'text'      => $text,
                    'keyboard'  => $keyboard,
                ];
                break;
        }

        return $return;
    }


    public static function getUserTrafficInfo($user)
    {
        $text = [
            '您当前的流量状况：',
            '',
            '今日已使用 ' . $user->TodayusedTrafficPercent() . '% ：' . $user->TodayusedTraffic(),
            '之前已使用 ' . $user->LastusedTrafficPercent() . '% ：' . $user->LastusedTraffic(),
            '流量约剩余 ' . $user->unusedTrafficPercent() . '% ：' . $user->unusedTraffic(),
        ];
        return implode(PHP_EOL, $text);
    }

    public static function getUserInfo($user)
    {
        $text = [
            '当前余额：' . $user->money,
            '在线设备：' . ($user->node_connector != 0 ? $user->online_ip_count() . ' / ' . $user->node_connector : $user->online_ip_count() . ' / 不限制'),
            '端口速率：' . ($user->node_speedlimit != 0 ? $user->node_speedlimit . 'Mbps' : '无限制'),
            '上次使用：' . $user->lastSsTime(),
            '过期时间：' . $user->class_expire,
        ];
        return implode(PHP_EOL, $text);
    }

    public static function getUserTitle($user)
    {
        if ($user->class > 0) {
            $text = '尊敬的 VIP ' . $user->class . ' 您好：';
        } else {
            $text = '尊敬的用户您好：';
        }
        return $text;
    }

    public static function getUserInfoFromAdmin($user, $ChatID)
    {
        $strArray = [
            '#' . $user->id . ' ' . $user->user_name . ' 的用户信息',
            '',
            '用户邮箱：' . TelegramTools::getUserEmail($user->email, $ChatID),
            '账户余额：' . $user->money,
            '是否启用：' . $user->enable,
            '用户等级：' . $user->class,
            '剩余流量：' . $user->unusedTraffic(),
            '等级到期：' . $user->class_expire,
            '账户到期：' . $user->expire_in,
        ];
        return implode(PHP_EOL, $strArray);
    }
}
