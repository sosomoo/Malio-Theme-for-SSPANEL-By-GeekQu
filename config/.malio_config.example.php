<?php

/*

__    __     ______     __         __     ______    
/\ '-./  \   /\  __ \   /\ \       /\ \   /\  __ \   
\ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \ \ \/\ \  
 \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\  \ \_____\ 
  \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/ 
                                                     
                  made by @editXY

*/


// 通用设置
$Malio_Config['theme_color'] = 'purple';    // 主题颜色，可选值为 purple, blue, darkblue, orange, pink TODO:
$Malio_Config['google_analytics_code'] = 'UA-123456789-1';    // Google 统计代码，留空为不开启，code格式为 'UA-123456789-1'
$Malio_Config['login_style'] = 'wallpaper';    // 登录页面的样式，可选 simple 和 wallpaper
$Malio_Config['login_slogan'] = '这是一句好听顺嘴而且不长不短刚刚好的Slogan<br>甚至可以写第二行';    // 仅在登录页面样式为 wallpaper 时生效，可使用 HTML 标签
$Malio_Config['enable_landing_page'] = true;    // 是否启用着陆页用于介绍本站

// 面板设置
$Malio_Config['enable_webapi_ip_verification'] = false;    // 当节点通过 webapi 连接时，不验证节点IP是否与数据库中的IP相同。关闭此选项会降低安全性。
$Malio_Config['enable_webapi_email_hash'] = true;    // 启用后，当节点通过 webapi 连接时，传出去的邮件地址会经过md5加密。


// Crisp 设置
$Malio_Config['enable_crisp'] = false;   // 是否启用 Crisp 在线客服系统 https://crisp.chat
$Malio_Config['crisp_wesite_id'] = '18b46e92-eb21-76d3-bfb7-8f2ae9adba64';    // Crisp 的网站ID，格式为 '18b46e92-eb21-76d3-bfb7-8f2ae9adba64'


// 侧边栏
$Malio_Config['enable_relay'] = false;   // 是否显示中转规则  (这个页面还没写好)
$Malio_Config['enable_ticket'] = true;   // 是否显示工单系统
$Malio_Config['enable_detect'] = true;   // 是否显示审计系统
$Malio_Config['enable_invite'] = true;   // 是否显示邀请注册
$Malio_Config['enable_sidebar_button'] = true;    // 是否显示底部 Telegram 按钮
$Malio_Config['telegram_group'] = 'https://t.me/SSUnion';   // Telegram 按钮的链接
$Malio_Config['telegram_group_class'] = 0;   // 显示底部 Telegram 按钮的用户最低等级，用户等级小于这个数字的将不会显示 Telegram 按钮


// 首页
$Malio_Config['enable_share'] = true;   // 是否显示共享账号
$Malio_Config['share_account'] = [    // 一个array为一个共享账号
    array(
        'name' => 'Netflix',
        'account' => 'malio@nintendo.jp',
        'passwd' => 'yahaha~'
    ),
    array(
        'name' => 'HBO',
        'account' => 'malio@nintendo.jp',
        'passwd' => 'yahaha~'
    ),
    array(
        'name' => 'Hulu',
        'account' => 'malio@nintendo.jp',
        'passwd' => 'yahaha~'
    ),
];


// 商店
$Malio_Config['shop_style'] = 'plans';    // 商店的显示风格， legacy为SSPANEL原版，plans为新版
$Malio_Config['shop_sub_title'] = '竭尽全力为您提供优质的服务';   // 商店的小标题，可以使用 HTML 标签

$Malio_Config['plan_1_name'] = '标准版';    // 第一个会员计划的名字
$Malio_Config['plan_1_pricing'] = 9.9;    // 第一个会员计划的价格
$Malio_Config['plan_1_traffic'] = 50;    // 第一个会员计划的流量，单位为GB
$Malio_Config['plan_1_online'] = 2;    // 第一个会员计划在线客户端数量
$Malio_Config['plan_1_feature'] = [    // 一个array为一个特性，support设置为false的话就是不支持
    array(
        'name' => '工单技术支持',
        'support' => true
    ),
    array(
        'name' => '国际标准节点',
        'support' => true
    ),
    array(
        'name' => '国内中转节点',
        'support' => false
    ),
    array(
        'name' => 'IPLC专线节点',
        'support' => false
    ),
];

$Malio_Config['enable_plan_2'] = true;    // 是否显示第二个会员计划
$Malio_Config['plan_2_name'] = '高级版';    // 第二个会员计划的名字
$Malio_Config['plan_2_pricing'] = 19.9;    // 第二个会员计划的价格
$Malio_Config['plan_2_traffic'] = 100;    // 第二个会员计划的流量，单位为GB
$Malio_Config['plan_2_online'] = 4;    // 第一个会员计划在线客户端数量
$Malio_Config['plan_2_feature'] = [    // 一个array为一个特性，support设置为false的话就是不支持
    array(
        'name' => '24/7 在线技术支持',
        'support' => true
    ),
    array(
        'name' => '国际标准节点',
        'support' => true
    ),
    array(
        'name' => '国内中转节点',
        'support' => true
    ),
    array(
        'name' => 'IPLC专线节点',
        'support' => false
    ),
];

$Malio_Config['enable_plan_3'] = true;    // 是否显示第三个会员计划
$Malio_Config['plan_3_name'] = '加强版';    // 第三个会员计划的名字
$Malio_Config['plan_3_pricing'] = 29.9;    // 第三个会员计划的价格
$Malio_Config['plan_3_traffic'] = 200;    // 第三个会员计划的流量，单位为GB
$Malio_Config['plan_3_online'] = 8;    // 第一个会员计划在线客户端数量
$Malio_Config['plan_3_feature'] = [    // 一个array为一个特性，support设置为false的话就是不支持
    array(
        'name' => '24/7 在线技术支持',
        'support' => true
    ),
    array(
        'name' => '国际标准节点',
        'support' => true
    ),
    array(
        'name' => '国内中转节点',
        'support' => true
    ),
    array(
        'name' => 'IPLC专线节点',
        'support' => true
    ),
];

// 每个会员计划不同时长所对应的商品ID（商品ID可以在管理面板的商品列表里找到），此项必须设置，不然商店购买功能无法正常工作
$Malio_Config['plan_shop_id'] = array(
    'plan_1' => array(
        '1month' => 1,
        '3month' => 2,
        '6month' => 3,
        '12month' => 4,
    ),
    'plan_2' => array(
        '1month' => 5,
        '3month' => 6,
        '6month' => 7,
        '12month' => 8,
    ),
    'plan_3' => array(
        '1month' => 9,
        '3month' => 10,
        '6month' => 11,
        '12month' => 12,
    ),
);

// 购买会员计划页面的购买须知文本，可使用 HTML 标签
$Malio_Config['buyer_reading'] = '
<div class="bullet"></div> 购买会员计划即代表同意《服务条款》和《使用政策》。<br>
<div class="bullet"></div> 流量每30天重置一次 (从购买日开始计算)，未使用的流量不结转到下个周期。
';


// 我的账号
$Malio_Config['enable_2fa'] = true;   // 是否显示二步验证的选项
$Malio_Config['enable_delete'] = true;   // 是否显示删除账号的选项
$Malio_Config['enable_telegram'] = true;   // 是否显示绑定 Telegram 账号的选项，禁用后登录页面的 “使用 Telegram 登录” 按钮会隐藏


// 节点列表
$Malio_Config['enable_node_load'] = true;   // 是否显示节点的负载
$Malio_Config['enable_online_user'] = true;   // 是否显示节点的在线人数
$Malio_Config['enable_node_traffic_rate'] = true;   // 是否显示节点的流量倍率
$Malio_Config['enable_node_speedlimit'] = true;   // 是否显示节点的限速


// 节点设置 -> 连接设置
$Malio_Config['enable_protocol'] = true;   // 是否显示自定义混淆和协议设置
$Malio_Config['enable_method'] = true;    // 是否显示自定义加密方式设置
$Malio_Config['enable_reset_port'] = true;   // 是否显示重置端口设置


// 下载和使用
$Malio_Config['windows_client'] = 'cfw';    // Windows 教程的首选客户端，可选 cfw, ssr
$Malio_Config['ios_client'] = 'quantumult';    // iOS 教程的首选客户端，可选 quantumult, shadowrocket, kitsunebi
$Malio_Config['ios_sub_type'] = 'v2ray';    // iOS 客户端的一键导入的订阅类型，可选ssr、v2ray
$Malio_Config['enable_ios_apple_id'] = true;    //  是否在 iOS 教程页面显示 Apple ID
$Malio_Config['ios_apple_id'] = 'malio@icloud.com';   //  iOS 教程页面的 Apple ID 账号
$Malio_Config['ios_apple_id_password'] = '1UPBOY~~';    // iOS 教程页面的 Apple ID 密码
$Malio_Config['android_client'] = 'kitsunebi';    // Android 教程的首选客户端，可选 ssr, kitsunebi, v2rayng, surfboard
$Malio_Config['mac_client'] = 'clashx';    // Windows 教程的首选客户端，可选 clashx, shadowsocksx-ng-r
$Malio_Config['linux_client'] = 'clash';    // Linux 教程的首选客户端，可选 clash, electron-ssr
$Malio_Config['enable_faq'] = true;    // 是否显示 FAQ 常见问题页面