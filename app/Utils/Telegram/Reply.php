<?php

namespace App\Utils\Telegram;

class Reply
{

    public static function getInlinekeyboard($user, $type)
    {
        $back = [
            [
                'text'          => '回主菜单',
                'callback_data' => 'index'
            ]
        ];
        switch ($type) {
            case 'index':
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
                        'text'          => '用户中心',
                        'callback_data' => 'user.index'
                    ],
                    [
                        'text'          => '资料编辑',
                        'callback_data' => 'user.edit'
                    ]
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
                        'keyboard'  => [
                            $isLogin,
                        ],
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
        }

        return $return;
    }
}
