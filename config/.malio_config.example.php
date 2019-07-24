<?php

/*

__    __     ______     __         __     ______    
/\ '-./  \   /\  __ \   /\ \       /\ \   /\  __ \   
\ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \ \ \/\ \  
 \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\  \ \_____\ 
  \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/ 
                                                     
                  made by @editXY

*/


// 版本信息说明（请勿更改）
$Malio_Config['config_migrate_notice'] = '';
$Malio_Config['version'] = '1';

// 通用设置
$Malio_Config['theme_color'] = 'purple';    // 主题颜色，可选值为 purple, blue, darkblue, orange, pink TODO:
$Malio_Config['google_analytics_code'] = 'UA-123456789-1';    // Google 统计代码，留空为不开启，code格式为 'UA-123456789-1'
$Malio_Config['login_style'] = 'wallpaper';    // 登录页面的样式，可选 simple 和 wallpaper
$Malio_Config['login_slogan'] = '这是一句好听顺嘴而且不长不短刚刚好的Slogan<br>甚至可以写第二行';    // 仅在登录页面样式为 wallpaper 时生效，可使用 HTML 标签
$Malio_Config['enable_landing_page'] = true;    // 是否启用着陆页用于介绍本站


// 面板设置
$Malio_Config['enable_webapi_ip_verification'] = false;    // 当节点通过 webapi 连接时，不验证节点IP是否与数据库中的IP相同。关闭此选项会降低安全性。
$Malio_Config['enable_webapi_email_hash'] = true;    // 启用后，当节点通过 webapi 连接时，传出去的邮件地址会经过md5加密。
$Malio_Config['code_required'] = true;    // 设置为true时，注册时邀请码是必须的。设置为false时，有无邀请码都可以注册，但是可以填邀请码（aff专用）


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
$Malio_Config['enable_index_subinfo'] = true;    // 是否在首页显示订阅链接复制或一键导入等按钮
$Malio_Config['index_subinfo_buttons_align'] = false;   // 是否开启首页订阅链接按钮的对齐
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
$Malio_Config['show_free_nodes'] = true;    // 是否显示免费节点（等级为0的节点）
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



// 着陆页设置
$Malio_Config['index_enable_promotion'] = true;   // 着陆页是否显示促销活动提示
$Malio_Config['index_promotion_text'] = '年付8折优惠活动，限时进行中';    // 着陆页促销活动文本
$Malio_Config['index_slogan'] = '全球网络中继服务，随时随处尽情使用';   // 着陆页slogan
$Malio_Config['index_sub_slogan'] = '通过我们的网络访问内容提供商、公司网络和公共云服务。';   // 着陆页slogan下面那行字
$Malio_Config['index_statistics_1_data'] = '100+';    // 着陆页的三个统计数据
$Malio_Config['index_statistics_1_name'] = '国际节点';    // 着陆页的三个统计数据
$Malio_Config['index_statistics_2_data'] = '25+';    // 着陆页的三个统计数据
$Malio_Config['index_statistics_2_name'] = '国家地区';    // 着陆页的三个统计数据
$Malio_Config['index_statistics_3_data'] = '6500+';    // 着陆页的三个统计数据
$Malio_Config['index_statistics_3_name'] = '满意用户';    // 着陆页的三个统计数据

$Malio_Config['index_more_features'] = [    // 着陆页里的更多特性，一个array为一个特性，icon来自fontawesome，https://fontawesome.com/icons?d=gallery&m=free
    array(
        'icon' => 'fas fa-ad',
        'feature' => '过滤常用网站广告、常用视频广告、其他流媒体网站广告',
    ),
    array(
        'icon' => 'fas fa-filter',
        'feature' => '智能分流系统，所有国内网站直线连接，增强用户体验',
    ),
    array(
        'icon' => 'fab fa-apple',
        'feature' => 'Apple服务加速 (App Store、Apple Music、iCloud、iTunes等)',
    ),
    array(
        'icon' => 'fas fa-tachometer-alt',
        'feature' => '国外常用网站加速 (Google/Youtube/Twitter/Instgram/Github等)',
    ),
    array(
        'icon' => 'fas fa-lock',
        'feature' => '在传输过程中使用最强的加密方式，保护用户数据和隐私',
    ),
    array(
        'icon' => 'fas fa-fire',
        'feature' => '与诸多平台上的优秀应用程序兼容，这些应用程序由许多创新公司和开发人员开发',
    ),
];

$Malio_Config['index_user_reviews'] = [    // 着陆页评价，一个array为一个评价，可以添加多个 
    array(
        'user' => '某一沙雕网友',
        'position' => '<a href="/">家里蹲大学</a> 学生',
        'review' => '我的妈我跟你说真的好用到飞起，我的妈我跟你说真的好用到飞起，我的妈我跟你说真的好用到飞起。素质三连。'
    ),
    array(
        'user' => 'CXK',
        'position' => '<a href="/">XX公司</a>唱跳练习生',
        'review' => '大家好，我是练习时长两年半的个人练习生，喜欢唱、跳、rap、篮球'
    ),
    array(
        'user' => '用户名',
        'position' => '职位',
        'review' => '第三个评价要咋写啊我编不下去了，你们记得改文案啊不然就。这个是占位符占位符🐈🐶'
    ),
];