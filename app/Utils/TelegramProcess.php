<?php
    
    namespace App\Utils;
    
    use App\Models\User;
    use App\Services\Config;
    use App\Controllers\LinkController;
    use TelegramBot\Api\Client;
    use TelegramBot\Api\Exception;
    use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
    use App\Models\Code;
    use App\Models\Node;
    use App\Models\Link;
    use App\Models\InviteCode;
    use App\Models\Payback;
    use App\Models\Shop;
    use App\Models\Coupon;
    use App\Models\Bought;
    use App\Utils\Tools;
    
    class TelegramProcess
    {
        private static $all_rss = [
            'clean_link' => '重置订阅',
            '?sub=1' => 'SSR订阅',
            '?sub=3' => 'V2ray订阅',
            '?sub=5' => 'Shadowrocket',
            '?sub=4' => 'Kitsunebi or V2rayNG or BifrostV',
            '?surge=2' => 'Surge 2.x',
            '?surge=3' => 'Surge 3.x',
            '?ssd=1' => 'SSD',
            '?clash=1' => 'Clash',
            '?surfboard=1' => 'Surfboard',
            '?quantumult=3' => 'Quantumult(完整配置)'
        ];
    
        private static function callback_bind_method($bot, $callback)
        {
            $callback_data = $callback->getData();
            $message = $callback->getMessage();
            $reply_to = $message->getMessageId();
            $user = User::where('telegram_id', $callback->getFrom()->getId())->first();
            $reply_message = '？？？';
            if ($user != null) {
                switch (true) {
                    case $callback_data == '?quantumult=3':
                        $baseUrl = Config::get('subUrl');
                        $usertoken = LinkController::GenerateSSRSubCode($user->id, 0);
                        $subUrl = '?quantumult=3';
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
                            [
                                [
                                    ['text' => '点击跳转', 'url' => $baseUrl . '/jump.html?url=quantumult://settings?configuration=clipboard']
                                ]
                            ]
                        );
                        $filepath = '/tmp/tg_' . $usertoken . '.txt';
                        $fh = fopen($filepath, 'w+');
                        $string = file_get_contents($baseUrl.$usertoken.$subUrl);
                        fwrite($fh, $string);
                        fclose($fh);
                        $reply_message = "感谢使用本机器人";
                        $bot->sendMessage($user->get_user_attributes('telegram_id'), "两种方法:\n 方法一:\n  1.点击打开以下配置文件\n  2. 选择分享->拷贝到\"Quantumult\"\n  3.选择更新配置\n 方法二:\n  1.长按配置文件\n  2. 选择更多->分享->拷贝\n  3.点击跳转APP,到Quan中保存", $parseMode = null, $disablePreview = false, $replyToMessageId = null, $replyMarkup = $keyboard);
                        $bot->sendDocument($user->get_user_attributes('telegram_id'), new \CURLFile($filepath, '', 'quantumult_' . $usertoken . '.conf'));
                        unlink($filepath);
                        break;
                    case (strpos($callback_data, 'sub') or strpos($callback_data, 'surge') or strpos($callback_data, 'clash') or strpos($callback_data, 'surfboard')):
                        $ssr_sub_token = LinkController::GenerateSSRSubCode($user->id, 0);
                        $subUrl = Config::get('subUrl');
                        $reply_message = self::$all_rss[$callback_data] . ': ' . $subUrl . $ssr_sub_token . $callback_data . PHP_EOL;
                        break;
                    case ($callback_data == 'clean_link'):
                        $user->clean_link();
                        $reply_message = '链接重置成功';
                        break;
                }
                $bot->sendMessage($message->getChat()->getId(), $reply_message, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
            }
        }

        private static function needbind_method($bot, $message, $command, $user, $reply_to = null)
        {
            if ($user != null) {
                switch ($command) {
                    case 'my':
                        $bot->sendMessage($message->getChat()->getId(), "您当前的流量状况：
今日已使用 ".$user->TodayusedTraffic()." ".number_format(($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100, 2)."%
今日之前已使用 ".$user->LastusedTraffic()." ".number_format($user->last_day_t/$user->transfer_enable*100, 2)."%
未使用 ".$user->unusedTraffic()." ".number_format(($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100, 2)."%
					                        ", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'checkin':
                        if (!$user->isAbleToCheckin()) {
                            $bot->sendMessage($message->getChat()->getId(), "您今天已经签过到了！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $traffic = rand(Config::get('checkinMin'), Config::get('checkinMax'));
                        if(time() >= 1549209600 and time() <= 1550592000){
                            $user->transfer_enable = $user->transfer_enable + Tools::toMB($traffic * 10);
                            $traffics = $traffic * 10;
                            $sendtext = "签到成功！获得了 ".$traffics." MB 流量！新年签到福利，十倍流量十倍快乐！";
                        }elseif($user->class != 0){
                            $user->transfer_enable = $user->transfer_enable + Tools::toMB($traffic * 2);
                            $traffics = $traffic * 2;
                            $sendtext = "签到成功！获得了 ".$traffics." MB 流量！VIP签到福利，双倍流量双倍快乐！";
                        }else{
                            $user->transfer_enable = $user->transfer_enable + Tools::toMB($traffic);
                            $sendtext = "签到成功！获得了 ".$traffic." MB 流量！";
                        }
                        $user->last_check_in_time = time();
                        $user->save();
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setclass':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        //$bot->sendMessage($message->getChat()->getId(), $message->getText(), $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        $data = explode(" ",$message->getText());
                        /*
                        if(isset($message->getReplyToMessage()->getFrom()->getId())){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        */
                        if( count($data) == 2){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $class = $data[1];

                        if( strpos($class,'+') !== false)
                            $class = $usersss->class + str_replace("+","",$class);
                        else if( strpos($class,'-') !== false)
                            $class = $usersss->class - str_replace("-","",$class);

                        $usersss->class = $class;
                        //$sendtext = "设置成功！".$usersss->email." 等级设置为".$data[1];
                        $usersss->save();
                        //$sendtext = "设置成功！".$usersss->email." 等级设置为".$data[1];
                        $sendtext = "设置成功！等级设置为".$class;
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setdate':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $settime = $data[1];
                        if( date('Y-m-d H:i:s', strtotime($settime))  == $settime ){
                            $usersss->class_expire = $data[1];
                            $usersss->save();
                            $sendtext = "设置成功！等级到期时间设置为".$settime;
                        }else
                            $sendtext = "时间格式错误";

                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'info':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2 or count($data) == 1){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss != null ){
                            $infotype = $data[1];
                            $sendtext = "当前用户信息为";
                            $sendtext .= "\n用户名：".$usersss->user_name;
                            $sendtext .= "\n邮箱：".$usersss->email;
                            $sendtext .= "\n已用流量：".round(($usersss->u + $usersss->d)/1024/1024/1024,2)."G";
                            $sendtext .= "\n剩余流量：".round(($usersss->transfer_enable - $usersss->u - $usersss->d)/1024/1024/1024,2)."G";
                            $sendtext .= "\n余额剩余：".$usersss->money;
                            $sendtext .= "\n等级：".$usersss->class;
                            $sendtext .= "\n等级到期时间：".$usersss->class_expire;
                            if( $infotype == "all" ){
                                $sendtext .= "\n端口：".$usersss->port;
                                $sendtext .= "\n邀请额度：".$usersss->invite_num;
                                $sendtext .= "\n最后使用时间：".date('Y-m-d H:i:s', $usersss->t);
                                $sendtext .= "\n账号到期时间：".$usersss->expire_in;
                            }
                        }else
                            $sendtext = "该tg用户未绑定账号或邮箱不存在！";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'find':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2 ){
                            $Elink = Link::where("token", "=", $data[1])->first();
                            if( $Elink != null )
                                $id = $Elink->userid;
                            else{
                                $code = InviteCode::where('code', $data[1])->first();
                                if( $code != null )
                                    $id = $code->user_id;
                                else{
                                    $usert = User::where('id', $data[1])->orWhere("user_name", "=", $data[1])->orWhere("email", "=", $data[1])->orWhere("port", "=", $data[1])->first();
                                    if( $usert != null )
                                        $id = $usert->id;
                                    else{
                                        $bot->sendMessage($message->getChat()->getId(), "未找到该用户！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                                        break;
                                    }
                                }
                            }
                        }
                        $usersss = User::where('id', $id)->first();
                        $sendtext = "当前用户信息为";
                        $sendtext .= "\n用户名：".$usersss->user_name;
                        $sendtext .= "\n邮箱：".$usersss->email;
                        $sendtext .= "\n已用流量：".round(($usersss->u + $usersss->d)/1024/1024/1024,2)."G";
                        $sendtext .= "\n剩余流量：".round(($usersss->transfer_enable - $usersss->u - $usersss->d)/1024/1024/1024,2)."G";
                        $sendtext .= "\n余额剩余：".$usersss->money;
                        $sendtext .= "\n等级：".$usersss->class;
                        $sendtext .= "\n等级到期时间：".$usersss->class_expire;
                        $sendtext .= "\n端口：".$usersss->port;
                        $sendtext .= "\n邀请额度：".$usersss->invite_num;
                        $sendtext .= "\n最后使用时间：".date('Y-m-d H:i:s', $usersss->t);
                        $sendtext .= "\n账号到期时间：".$usersss->expire_in;
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setmoney':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2 ){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $setmoney = $data[1];

                        if( strpos($setmoney,'+') !== false)
                            $setmoney = $usersss->money + str_replace("+","",$setmoney);
                        else if( strpos($setmoney,'-') !== false)
                            $setmoney = $usersss->money - str_replace("-","",$setmoney);

                        $usersss->money = $setmoney;
                        $usersss->save();
                        $sendtext = "设置成功！余额设置为".$setmoney;
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'ban':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 1 ){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 2 ){
                            $usersss = User::where('email', $data[1])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }

                        $usersss->enable = 0;
                        $usersss->save();
                        $sendtext = "设置成功！该账户已禁用";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        $bot->kickChatMember($message->getChat()->getId(), $message->getFrom()->getId());
                        break;
                    case 'active':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 1 ){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 2 ){
                            $usersss = User::where('email', $data[1])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }

                        $usersss->enable = 1;
                        $usersss->save();
                        $sendtext = "设置成功！该账户已启用";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setconnector':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $setconnector = $data[1];

                        if( strpos($setconnector,'+') !== false)
                            $setconnector = $usersss->node_connector + str_replace("+","",$setconnector);
                        else if( strpos($setconnector,'-') !== false)
                            $setconnector = $usersss->node_connector - str_replace("-","",$setconnector);

                        $usersss->node_connector = $setconnector;
                        $usersss->save();
                        $sendtext = "设置成功！连接数设置为".$setconnector;
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setrole':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $is_admin = $data[1] == "admin" ? 1:0;
                        $usersss->is_admin = $is_admin;
                        $usersss->save();
                        $sendtext = "设置成功！用户设置为".$data[1];
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'setspeed':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $usersss = User::where('telegram_id', $message->getReplyToMessage()->getFrom()->getId())->first();
                        }else if( count($data) == 3 ){
                            $usersss = User::where('email', $data[2])->first();
                        }
                        if( $usersss == null ){
                            $bot->sendMessage($message->getChat()->getId(), "该tg用户未绑定账号或邮箱不存在！", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $setspeed = $data[1];
                        if( is_numeric($setspeed) ){
                            $usersss->node_speedlimit = $setspeed;
                            $usersss->save();
                            $sendtext = "设置成功！速度限制设置为".$setspeed."Mbps";
                        }else
                            $sendtext = "格式错误";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'delnode':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $nodeid = $data[1];
                            if( is_numeric($nodeid) ){
                                $node = Node::find($nodeid);
                                $node->delete();
                                $sendtext = "删除成功！";
                            }else $sendtext = "格式错误";
                        }
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'buy':

                        $data = explode(" ",$message->getText());
                        $code = $data[2];
                        $shop = Shop::where("id", $data[1])->where("status", 1)->first();
                        if ($shop == null) {
                            $sendtext = "非法请求";
                            $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }

                        if ( count($data) == 2 ) {
                            $credit = 0;
                        } elseif ( count($data) == 3 ) {
                            $coupon = $data[2];
                            $coupon = Coupon::where("code", $coupon)->first();
                            if ($coupon == null) $credit = 0;
                            else if ($coupon->onetime == 1) {
                                $onetime = true;
                                $credit = $coupon->credit;
                                if ($coupon->order($shop->id) == false) {
                                    $sendtext = "此优惠码不可用于此商品";
                                    $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                                    break;
                                }

                                if ($coupon->expire < time()) {
                                    $sendtext = "此优惠码已过期";
                                    $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                                    break;
                                }
                            }
                        }

                        $price = $shop->price * ((100 - $credit) / 100);

                        if ($user->money < $price) {
                            $sendtext = '喵喵喵~ 当前余额不足，总价为' . $price . '元。';
                            $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }

                        $user->money = $user->money - $price;


                        // if ($disableothers == 1) {
                        //     $boughts = Bought::where("userid", $user->id)->get();
                        //     foreach ($boughts as $disable_bought) {
                        //         $disable_bought->renew = 0;
                        //         $disable_bought->save();
                        //     }
                        // }
                        $bought = new Bought();
                        $bought->userid = $user->id;
                        $bought->shopid = $shop->id;
                        $bought->datetime = time();
                        $autorenew=0;
                        if ($autorenew == 0 || $shop->auto_renew == 0) {
                            $bought->renew = 0;
                        } else {
                            $bought->renew = time() + $shop->auto_renew * 86400;
                        }
                        if (isset($code)) {
                            $bought->coupon = $code;
                        }

                        if (isset($onetime)) {
                            $price = $shop->price;
                        }
                        $bought->price = $price;
                        $sendtext = "购买成功";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        $bought->save();
                        $shop->buy($user);
                        $user->save();
                        break;
                    case 'generatecode':
                        if (!$user->is_admin) {
                            $bot->sendMessage($message->getChat()->getId(), "您不是管理员", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                            break;
                        }
                        $data = explode(" ",$message->getText());
                        if( count($data) == 3){
                            $number = $data[1];
                            $n = $data[2];
                            if( is_numeric($n) and is_numeric($number) ){
                                $sendtext = "操作成功,充值码为：";
                                for ($i = 0; $i < $n; $i++) {
                                    $char = time() . Tools::genRandomChar(32);
                                    $code = new Code();
                                    $code->code = $char;
                                    $code->type = -1;
                                    $code->number = $number;
                                    $code->userid=0;
                                    $code->usedatetime="1989:06:04 02:30:00";
                                    $code->save();
                                    $sendtext .= "\n".$char;
                                }
                            }
                        }
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'invite':
                        $code = InviteCode::where('user_id', $user->id)->first();

                        $InviteUrl = Config::get('baseUrl').'/auth/register?code='.$code->code;
                        $sendtext = '您的邀请链接为：'.$InviteUrl;
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'nodes':
                        $sendtext = "节点列表：\n";
                        $Nodes = Node::where('type', 1)->orderBy('name')->get();
                        foreach ($Nodes as $Node) {
                            $sendtext .= "\n[".$Node->id."]".$Node->name;
                        }
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'store':

                        $sendtext = "商品详情：\n";
                        $shops = Shop::where("status", 1)->orderBy("name")->get();
                        foreach ($shops as $shop) {
                            $sendtext .= "\n[".$shop->id."]".$shop->name."[价格] ".$shop->price."[流量] ".$shop->bandwidth();
                        }
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'redeem':
                        $data = explode(" ",$message->getText());
                        if( count($data) == 2){
                            $code = $data[1];
                            $codeq = Code::where("code", "=", $code)->where("isused", "=", 0)->first();
                            if ($codeq == null) $sendtext = "此充值码错误";
                            else {
                                $codeq->isused = 1;
                                $codeq->usedatetime = date("Y-m-d H:i:s");
                                $codeq->userid = $user->id;
                                $codeq->save();
                                if ($codeq->type == -1) {
                                    $user->money = ($user->money + $codeq->number);
                                    $user->save();
                                    if ($user->ref_by != "" && $user->ref_by != 0 && $user->ref_by != null) {
                                        $gift_user = User::where("id", "=", $user->ref_by)->first();
                                        $gift_user->money = ($gift_user->money + ($codeq->number * (Config::get('code_payback') / 100)));
                                        $gift_user->save();
                                        $Payback = new Payback();
                                        $Payback->total = $codeq->number;
                                        $Payback->userid = $user->id;
                                        $Payback->ref_by = $user->ref_by;
                                        $Payback->ref_get = $codeq->number * (Config::get('code_payback') / 100);
                                        $Payback->datetime = time();
                                        $Payback->save();
                                    }
                                    $sendtext = "充值成功，充值的金额为" . $codeq->number . "元。";
                                }
                            }
                        }else $sendtext = "格式错误！";
                        $bot->sendMessage($message->getChat()->getId(), $sendtext, $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case 'topip':
                        $shop_name = array("null", "Pass Brozen", "Pass Siver", "Pass Gold", "Pass Platinum", "Pass Diamond", "Team License", "Enterprice License");
                        $online_time = time() - 120;
                        $ip_users = User::where('t', '>', $online_time)->get();
                        foreach ($ip_users as $ip_user) {
                            $data[] = array('ip' => $ip_user->online_ip_count(), 'user' => $ip_user);
                        }

                        $keysValue = [];
                        foreach ($data as $key => $row) {
                            $keysValue[$key] = $row['ip'];
                        }
                        array_multisort($keysValue, SORT_DESC, $data);

                        $i = 0;
                        $res_ip = '';
                        while ($i < 10) {
                            $ip_user = $data[$i]['user'];
                            $user_link = "该用户尚未绑定 Telegram";
                            if ($ip_user->telegram_id != null && $ip_user->telegram_id != '') {
                                try {
                                    $chat_user = $bot->getChatMember(Config::get('telegram_chatid'), $ip_user->telegram_id);
                                    $first_name = $chat_user->getUser()->getFirstName();
                                    $last_name =  $chat_user->getUser()->getLastName();
                                    $name = $ip_user->im_value != null ? $ip_user->im_value : $first_name.$last_name;
                                    $user_link = "[".$name."](tg://user?id=".$ip_user->telegram_id.")";
                                }
                                catch(\Exception $e) {
                                    echo 'Message: ' .$e->getMessage();
                                }
                            }
                            $res_ip .= "Username: ".$user_link.PHP_EOL."Email: ".$ip_user->email.PHP_EOL."Online IP: ".$data[$i]['ip'].PHP_EOL."Shop: ".$shop_name[$ip_user->class].PHP_EOL."----------------".PHP_EOL;
                            $i++;
                        }
                        $bot->sendMessage($message->getChat()->getId(),  $res_ip, $parseMode = "Markdown", $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        break;
                    case 'toptr':
                        $online_time = time() - 86400;
                        $tr_users = User::where('class', '>', 0)->where('t', '>', $online_time)->get();
                        foreach ($tr_users as $tr_user) {
                            $tr_t = (($tr_user->u + $tr_user->d) - $tr_user->last_day_t) / 1024 / 1024;
                            $data[] = array('tr' => $tr_t, 'user' => $tr_user);
                        }

                        $keysValue = [];
                        foreach ($data as $key => $row) {
                            $keysValue[$key] = $row['tr'];
                        }
                        array_multisort($keysValue, SORT_DESC, $data);

                        $i = 0;
                        $res_ip = '';
                        while ($i < 10) {
                            $ip_user = $data[$i]['user'];
                            $user_link = "该用户尚未绑定 Telegram";
                            if ($ip_user->telegram_id != null && $ip_user->telegram_id != '') {
                                try {
                                    $chat_user = $bot->getChatMember(Config::get('telegram_chatid'), $ip_user->telegram_id);
                                    $first_name = $chat_user->getUser()->getFirstName();
                                    $last_name =  $chat_user->getUser()->getLastName();
                                    $name = $ip_user->im_value != null ? $ip_user->im_value : $first_name.$last_name;
                                    $user_link = "[".$name."](tg://user?id=".$ip_user->telegram_id.")";
                                }
                                catch(\Exception $e) {
                                    echo 'Message: ' .$e->getMessage();
                                }
                            }
                            $res_ip .= "Username: ".$user_link.PHP_EOL."Email: ".$ip_user->email.PHP_EOL."Today Traffic: ".$data[$i]['tr']." MB".PHP_EOL."----------------".PHP_EOL;
                            $i++;
                        }
                        $bot->sendMessage($message->getChat()->getId(),  $res_ip, $parseMode = "Markdown", $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        break;
                    case 'prpr':
                        $prpr = array('⁄(⁄ ⁄•⁄ω⁄•⁄ ⁄)⁄', '(≧ ﹏ ≦)', '(*/ω＼*)', 'ヽ(*。>Д<)o゜', '(つ ﹏ ⊂)', '( >  < )');
                        $bot->sendMessage($message->getChat()->getId(), $prpr[mt_rand(0,5)], $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                        break;
                    case "rss":
                        $reply_to = $message->getMessageId();
                        $reply = [
                            'message' => '？？？',
                            'markup' => null,
                        ];
                        $reply['message'] = '点击以下按钮获取对应订阅: ';
                        $keys = [];
                        foreach (self::$all_rss as $key => $value) {
                            $keys[] = [['text' => $value, 'callback_data' => $key]];
                        }
                        $reply['markup'] = new InlineKeyboardMarkup(
                            $keys
                        );
                        $bot->sendMessage($message->getChat()->getId(), $reply['message'], $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to, $replyMarkup = $reply['markup']);
                        break;

                    default:
                        $bot->sendMessage($message->getChat()->getId(), "???", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
                }
            } else {
                $bot->sendMessage($message->getChat()->getId(), "您未绑定本站账号。", $parseMode = null, $disablePreview = false, $replyToMessageId = $reply_to);
            }
        }

        public static function telegram_process($bot, $message, $command)
        {
            $user = User::where('telegram_id', $message->getFrom()->getId())->first();
            if ($message->getChat()->getId() > 0) {
                //个人
                $commands = array("ping", "chat", "checkin", "help", "setclass", "setdate", "setmoney", "setconnector", "setrole", "setspeed", "generatecode", "delnode", "info", "nodes", "store", "buy", "redeem", "my", "invite", "store", "ban", "active", "find", "topip", "toptr","rss");
                if(in_array($command, $commands)){
                    $bot->sendChatAction($message->getChat()->getId(), 'typing');
                }
                switch ($command) {
                    case 'ping':
                        $bot->sendMessage($message->getChat()->getId(), 'Pong!您的 ID 是 '.$message->getChat()->getId().'!');
                        break;
                    case 'chat':
                        $bot->sendMessage($message->getChat()->getId(), Tuling::chat($message->getFrom()->getId(), substr($message->getText(), 5)));
                        break;
                    case 'my':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'topip':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'toptr':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'find':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'ban':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'active':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'store':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setclass':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setdate':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setmoney':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setconnector':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setrole':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setspeed':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'delnode':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'generatecode':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'info':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'checkin':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'sub':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'invite':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'nodes':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'redeem':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'buy':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'prpr':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'rss':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'help':
                        $help_list = "/help - 获取当前聊天中可用的指令列表
/my - 获取账户信息
/checkin - 签到
/invite - 获取邀请链接
/nodes - 获取节点列表(仅限私聊)
/store - 打开商店面板(仅限私聊)
/buy <商品ID> [优惠码] - 直接购买商品(仅限私聊)
/redeem <充值码> - 兑换充值码(仅限私聊)
/info [base/all] [邮箱(回复消息无需指定)] - 获取用户信息
/find <UID/用户名/邮箱/端口/订阅Token/邀请码> [类型] - 搜索用户
/ban [邮箱(回复消息无需指定)] - 禁用用户账户(别名/sban)
/active [邮箱(回复消息无需指定)] - 启用用户账户
/setclass <(+/-)等级> [邮箱(回复消息无需指定)] - 修改用户等级
/setdate <Y-m-d H:i:s> [邮箱(回复消息无需指定)] - 设置用户等级有效期
/setmoney <(+/-)数字> [邮箱(回复消息无需指定)] - 修改用户余额
/setconnector <(+/-)IP数> [邮箱(回复消息无需指定)] - 修改用户最大设备数
/setrole <user/admin> [邮箱(回复消息无需指定)] - 修改用户身份(设置面板管理员)
/setspeed <限速(Mbps)> [邮箱(回复消息无需指定)] - 修改用户限速
/generatecode <金额> <数量> - 生成充值码(别名/gencode)
/delnode <节点ID> - 删除节点";
                        $bot->sendMessage($message->getChat()->getId(), $help_list);
                        break;
                    default:
                        if ($message->getPhoto() != null) {
                            $bot->sendMessage($message->getChat()->getId(), "正在解码，请稍候。。。");
                            $bot->sendChatAction($message->getChat()->getId(), 'typing');

                            $photos = $message->getPhoto();

                            $photo_size_array = array();
                            $photo_id_array = array();
                            $photo_id_list_array = array();


                            foreach ($photos as $photo) {
                                $file = $bot->getFile($photo->getFileId());
                                $real_id = substr($file->getFileId(), 0, 36);
                                if (!isset($photo_size_array[$real_id])) {
                                    $photo_size_array[$real_id] = 0;
                                }

                                if ($photo_size_array[$real_id] < $file->getFileSize()) {
                                    $photo_size_array[$real_id] = $file->getFileSize();
                                    $photo_id_array[$real_id] = $file->getFileId();
                                    if (!isset($photo_id_list_array[$real_id])) {
                                        $photo_id_list_array[$real_id] = array();
                                    }

                                    array_push($photo_id_list_array[$real_id], $file->getFileId());
                                }
                            }

                            foreach ($photo_id_array as $key => $value) {
                                $file = $bot->getFile($value);
                                $qrcode_text = QRcode::decode("https://api.telegram.org/file/bot".Config::get('telegram_token')."/".$file->getFilePath());

                                if ($qrcode_text == null) {
                                    foreach ($photo_id_list_array[$key] as $fail_key => $fail_value) {
                                        $fail_file = $bot->getFile($fail_value);
                                        $qrcode_text = QRcode::decode("https://api.telegram.org/file/bot".Config::get('telegram_token')."/".$fail_file->getFilePath());
                                        if ($qrcode_text != null) {
                                            break;
                                        }
                                    }
                                }

                                if (substr($qrcode_text, 0, 11) == 'mod://bind/' && strlen($qrcode_text) == 27) {
                                    $uid = TelegramSessionManager::verify_bind_session(substr($qrcode_text, 11));
                                    if ($uid != 0) {
                                        $user = User::where('id', $uid)->first();
                                        $user->telegram_id = $message->getFrom()->getId();
                                        $user->im_type = 4;
                                        $user->im_value = $message->getFrom()->getUsername();
                                        $user->save();
                                        $bot->sendMessage($message->getChat()->getId(), "绑定成功。邮箱：".$user->email);
                                    } else {
                                        $bot->sendMessage($message->getChat()->getId(), "绑定失败，二维码无效。".substr($qrcode_text, 11));
                                    }
                                }

                                if (substr($qrcode_text, 0, 12) == 'mod://login/' && strlen($qrcode_text) == 28) {
                                    if ($user != null) {
                                        $uid = TelegramSessionManager::verify_login_session(substr($qrcode_text, 12), $user->id);
                                        if ($uid != 0) {
                                            $bot->sendMessage($message->getChat()->getId(), "登录验证成功。邮箱：".$user->email);
                                        } else {
                                            $bot->sendMessage($message->getChat()->getId(), "登录验证失败，二维码无效。".substr($qrcode_text, 12));
                                        }
                                    } else {
                                        $bot->sendMessage($message->getChat()->getId(), "登录验证失败，您未绑定本站账号。".substr($qrcode_text, 12));
                                    }
                                }

                                break;
                            }
                        } else {
                            if (is_numeric($message->getText()) && strlen($message->getText()) == 6) {
                                if ($user != null) {
                                    $uid = TelegramSessionManager::verify_login_number($message->getText(), $user->id);
                                    if ($uid != 0) {
                                        $bot->sendMessage($message->getChat()->getId(), "登录验证成功。邮箱：".$user->email);
                                    } else {
                                        $bot->sendMessage($message->getChat()->getId(), "登录验证失败，数字无效。");
                                    }
                                } else {
                                    $bot->sendMessage($message->getChat()->getId(), "登录验证失败，您未绑定本站账号。");
                                }
                                break;
                            }
                            $bot->sendMessage($message->getChat()->getId(), Tuling::chat($message->getFrom()->getId(), $message->getText()));
                        }
                }
            } else {
                //群组
                if (Config::get('telegram_group_quiet') == 'true') {
                    return;
                }
                $commands = array("ping", "chat", "checkin", "help", "setdate", "setmoney", "setclass", "setconnector", "setrole", "setspeed", "generatecode", "delnode", "info", "my", "invite", "ban", "active", "find", "topip", "toptr");
                if(in_array($command, $commands)){
                    $bot->sendChatAction($message->getChat()->getId(), 'typing');
                }
                switch ($command) {
                    case 'ping':
                        $bot->sendMessage($message->getChat()->getId(), 'Pong!这个群组的 ID 是 '.$message->getChat()->getId().'!', $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        break;
                    case 'chat':
                        if ($message->getChat()->getId() == Config::get('telegram_chatid')) {
                            $bot->sendMessage($message->getChat()->getId(), Tuling::chat($message->getFrom()->getId(), substr($message->getText(), 5)), $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        } else {
                            $bot->sendMessage($message->getChat()->getId(), '不约，叔叔我们不约。', $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        }
                        break;
                    case 'setclass':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'topip':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'toptr':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'find':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'ban':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'active':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'my':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setdate':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setmoney':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setconnector':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setrole':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'setspeed':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'delnode':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'info':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'checkin':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'invite':
                        TelegramProcess::needbind_method($bot, $message, $command, $user, $message->getMessageId());
                        break;
                    case 'help':
                        $help_list_group = "/help - 获取当前聊天中可用的指令列表
/my - 获取账户信息
/checkin - 签到
/invite - 获取邀请链接
/nodes - 获取节点列表(仅限私聊)
/store - 打开商店面板(仅限私聊)
/rss - 获取订阅(仅限私聊)
/buy <商品ID> [优惠码] - 直接购买商品(仅限私聊)
/redeem <充值码> - 兑换充值码(仅限私聊)
/info [base/all] [邮箱(回复消息无需指定)] - 获取用户信息
/find <UID/用户名/邮箱/端口/订阅Token/邀请码> [类型] - 搜索用户
/ban [邮箱(回复消息无需指定)] - 禁用用户账户(别名/sban)
/active [邮箱(回复消息无需指定)] - 启用用户账户
/setclass <(+/-)等级> [邮箱(回复消息无需指定)] - 修改用户等级
/setdate <Y-m-d H:i:s> [邮箱(回复消息无需指定)] - 设置用户等级有效期
/setmoney <(+/-)数字> [邮箱(回复消息无需指定)] - 修改用户余额
/setconnector <(+/-)IP数> [邮箱(回复消息无需指定)] - 修改用户最大设备数
/setrole <user/admin> [邮箱(回复消息无需指定)] - 修改用户身份(设置面板管理员)
/setspeed <限速(Mbps)> [邮箱(回复消息无需指定)] - 修改用户限速
/generatecode <金额> <数量> - 生成充值码(别名/gencode)
/delnode <节点ID> - 删除节点";
                        $bot->sendMessage($message->getChat()->getId(), $help_list_group, $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                        break;
                    default:
                        if ($message->getText() != null) {
                            if ($message->getChat()->getId() == Config::get('telegram_chatid')) {
                                $bot->sendMessage($message->getChat()->getId(), Tuling::chat($message->getFrom()->getId(), $message->getText()), $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                            } else {
                                $bot->sendMessage($message->getChat()->getId(), '不约，叔叔我们不约。', $parseMode = null, $disablePreview = false, $replyToMessageId = $message->getMessageId());
                            }
                        }
                        if ($message->getNewChatMember() != null && Config::get('enable_welcome_message') == 'true') {
                            $bot->sendMessage($message->getChat()->getId(), "欢迎 ".$message->getNewChatMember()->getFirstName()." ".$message->getNewChatMember()->getLastName(), $parseMode = null, $disablePreview = false);
                        }
                }
            }

           $bot->sendChatAction($message->getChat()->getId(), '');
        }
    
        public static function process()
        {
            try {
                $bot = new Client(Config::get('telegram_token'));
                // or initialize with botan.io tracker api key
                // $bot = new \TelegramBot\Api\Client('YOUR_BOT_API_TOKEN', 'YOUR_BOTAN_TRACKER_API_KEY');

                $command_list = array("ping", "chat", "help", "prpr", "checkin", "setclass", "setdate", "setmoney", "setconnector", "setrole", "setspeed", "generatecode", "delnode", "info", "nodes", "store", "buy", "redeem", "my", "invite", "store", "buy", "ban", "active", "find", "topip", "toptr","rss");
                foreach ($command_list as $command) {
                    $bot->command($command, function ($message) use ($bot, $command) {
                        TelegramProcess::telegram_process($bot, $message, $command);
                    });
                }

                $bot->on($bot->getEvent(function ($message) use ($bot) {
                    TelegramProcess::telegram_process($bot, $message, '');
                }), function () {
                    return true;
                });
    
                $bot->on($bot->getCallbackQueryEvent(function ($callback) use ($bot) {
                    TelegramProcess::callback_bind_method($bot, $callback);
                }), function () {
                    return true;
                });
    
                $bot->run();
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
    }
