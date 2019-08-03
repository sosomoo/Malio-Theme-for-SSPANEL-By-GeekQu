<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>用户中心 &mdash; {$config["appName"]}</title>

  <!-- C3 chart css -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/c3@0.6.8/c3.min.css">

  <style>
    .card-header i {
      vertical-align: 1px;
      font-size: 1rem;
    }

    .section .section-header .section-header-breadcrumb {
      flex-basis: 0;
    }

    .wizard-step-active {
      cursor: pointer;
    }

    .btn-quantumult {
      background: linear-gradient(to right, black, black) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.38);
      margin-bottom: 16px;
    }
    .btn-shadowrocket {
      background: linear-gradient(to right, #3671b9, #3671b9) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #3671b99a;
      margin-bottom: 16px;
    }
    .btn-kitsunebi {
      background: linear-gradient(to right, #f2885b, #e83c9a) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 4px #ff567885;
      margin-bottom: 16px;
    }
    .btn-ssr {
      background: linear-gradient(to right, #e780a3, #e780a3) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #e780a2b0;
      margin-bottom: 16px;
    }
    .btn-v2ray {
      background: linear-gradient(to right, #df268f, #a73178) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #df268f63;
      margin-bottom: 16px;
    }
    .btn-ss {
      background: linear-gradient(to right, #187abb, #187abb) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #3671b99a;
      margin-bottom: 16px;
    }
    .btn-surge {
      background: linear-gradient(to right, #5c97f0, #b769f3) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #8d7cfab2;
      margin-bottom: 16px;
    }
    .btn-clash {
      background: linear-gradient(to right, #49BCFC, #3B92F8) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px rgba(73, 189, 252, 0.521);
      margin-bottom: 16px;
    }
    .btn-surfboard {
      background: linear-gradient(to right, #303030, #303030) !important;
      color: white !important;
      border-color: transparent;
      border: none;
      box-shadow: 0 2px 6px #3030306e;
      margin-bottom: 16px;
    }
    {if $malio_config['index_subinfo_buttons_align'] == true}
    .buttons a {
      width: 230px;
    }
    {/if}
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      {if $user->class != -1}
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>用户中心</h1>
            <div class="section-header-breadcrumb">
              <div id="checkin-div" class="breadcrumb-item active">
                {if $user->isAbleToCheckin() }
                <a href="#" onclick="checkin()" class="btn btn-icon icon-left btn-primary"><i class="far fa-edit"></i> 每日签到</a>
                {else}
                <a href="#" class="btn btn-icon disabled icon-left btn-primary"><i class="far fa-edit"></i> 已签到</a>
                {/if}
              </div>
            </div>
          </div>
          {if substr($user->unusedTraffic(),0,-2) <= 0 and $user->class != 0}
            <div class="alert alert-warning">
              您的流量已用尽，无法继续使用本站服务。如需更多流量，请前往会员商店购买流量叠加包。
            </div>
            {/if}
            {if $user->class == 0}
            <div class="alert alert-warning">
              您的会员计划已过期，请及时续费。
            </div>
            {/if}
            {if substr($user->unusedTraffic(),0,-2) <= 5 && substr($user->unusedTraffic(),0,-2) > 0 && {substr($user->unusedTraffic(),-2)} == 'GB'}
              <div class="alert alert-primary">
                您的可用流量不足5GB，如需更多流量，可前往会员商店购买流量叠加包。
              </div>
              {/if}
              {if $user->lastSsTime() == '从未使用喵' and $user->class>0}
              <div class="alert alert-primary">
                <a href="/user/tutorial" class="alert-link" style="font-weight:400">新手上路？<b>点我下载客户端</b>，轻松上手！</a>
              </div>
              {/if}
              <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                      <i class="fas fa-crown"></i>
                    </div>
                    <div class="card-wrap">
                      <div class="card-header">
                        <h4>会员时长</h4>
                      </div>
                      <div class="card-body">
                        {if $user->class_expire!="1989-06-04 00:05:00"}
                        <span class="counter">{$class_left_days}</span> 天
                        {else}
                        永久
                        {/if}
                      </div>
                    </div>
                    <div class="card-stats">
                      <div class="card-stats-title" style="padding-top: 0;padding-bottom: 4px;">
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">
                              {if $user->class == 1}{$malio_config['plan_1_name']}{/if}
                              {if $user->class == 2}{$malio_config['plan_2_name']}{/if}
                              {if $user->class == 3}{$malio_config['plan_3_name']}{/if}
                              {if $user->class == 0}已过期{/if}
                              : 
                              {if $user->class_expire!="1989-06-04 00:05:00"}{substr($user->class_expire, 0, 10)}{else}永久{/if}
                            </li>
                          </ol>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="card card-statistic-2">
                    <div class="card-icon shadow-success bg-success">
                      <i class="fas fa-tint"></i>
                    </div>
                    <div class="card-wrap">
                      <div class="card-header">
                        <h4>可用流量</h4>
                      </div>
                      <div class="card-body">
                        <span class="counter">{substr($user->unusedTraffic(),0,-2)}</span> {substr($user->unusedTraffic(),-2)}
                      </div>
                      <div class="card-stats">
                        <div class="card-stats-title" style="padding-top: 0;padding-bottom: 4px;">
                          <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                              <li class="breadcrumb-item active" aria-current="page">今日已用: {$user->TodayusedTraffic()}</li>
                            </ol>
                          </nav>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="card card-statistic-2">
                    <div class="card-icon shadow-info bg-info">
                      <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="card-wrap">
                      <div class="card-header">
                        <h4>在线设备数</h4>
                      </div>
                      <div class="card-body">
                        <span class="counter">{$user->online_ip_count()}</span> / {if $user->node_connector == 0}∞{else}<span class="counterup">{$user->node_connector}</span>{/if}
                      </div>
                      <div class="card-stats">
                        <div class="card-stats-title" style="padding-top: 0;padding-bottom: 4px;">
                          <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                              <li class="breadcrumb-item active" aria-current="page">上次使用时间: {if $user->lastSsTime() == '从未使用喵'}从未使用过{else}{substr($user->lastSsTime(), 5)}{/if}</li>
                            </ol>
                          </nav>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                  <div class="card card-statistic-2">
                    <div class="card-icon shadow-warning bg-warning">
                      <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-wrap">
                      <div class="card-header">
                        <h4>钱包余额</h4>
                      </div>
                      <div class="card-body">
                        ¥ <span class="counter">{$user->money}</span>
                      </div>
                      <div class="card-stats">
                        <div class="card-stats-title" style="padding-top: 0;padding-bottom: 4px;">
                          <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                              <li class="breadcrumb-item active" aria-current="page">总返利金额: ¥ {$paybacks_sum}</li>
                            </ol>
                          </nav>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-7 col-lg-7">
                  <div class="card">
                    <div class="card-header">
                      <h4><i class="fas fa-bullhorn"></i> 最新公告</h4>
                    </div>
                    <div class="card-body">
                      {$ann->content}
                    </div>
                  </div>

                  <div class="card">
                    <div class="card-header">
                      <h4><i class="fas fa-chart-bar" style="vertical-align: -1px;"></i> 查看最近72小时流量使用情况</h4>
                      <div class="card-header-action" id="loadTrafficChart-div">
                        <a href="##" onclick="loadTrafficChart()" class="btn btn-primary" style="display: inline-block">
                          加载数据
                        </a>
                      </div>
                    </div>
                    <div id="chartCardbox" class="card-body">
                      <div id="scatter-plot"></div>
                    </div>
                  </div>
                </div>
                
                <div class="col-12 col-md-5 col-lg-5">
                  <div class="card">
                    <div class="card-header">
                      <h4><i class="fas fa-chart-pie"></i> 流量使用情况</h4>
                    </div>
                    <div class="card-body">
                      <div id="pie-chart"></div>
                    </div>
                  </div>

                  {if $malio_config['enable_share'] == true}
                  <div class="card">
                    <div class="card-header">
                      <h4><i class="fas fa-share"></i> 共享账号</h4>
                    </div>
                    <div class="card-body">
                      <div id="accordion">
                        {$number = 0}
                        {foreach $malio_config['share_account'] as $account}
                        {$number = $number + 1}
                        <div class="accordion">
                          <div class="accordion-header collapsed" role="button" data-toggle="collapse" data-target="#panel-body-{$number}" aria-expanded="false">
                            <h4>{$account['name']}</h4>
                          </div>
                          <div class="accordion-body collapse" id="panel-body-{$number}" data-parent="#accordion">
                            <p class="mb-0">
                              账号: <a href="##" class="copy-text" data-clipboard-text="{$account['account']}">{$account['account']}</a><br>
                              密码: <a href="##" class="copy-text" data-clipboard-text="{$account['passwd']}">*********(点击复制)</a>
                            </p>
                          </div>
                        </div>
                        {/foreach}
                      </div>
                    </div>
                  </div>
                  {/if}

                  {if $malio_config['enable_index_subinfo'] == true}
                  <div class="card">
                    <div class="card-header">
                      <h4><i class="fas fa-bolt"></i> 便捷导入</h4>
                    </div>
                    <div class="card-body">
                      <div class="buttons">
                        {if (in_array("ss",$malio_config['support_sub_type'])) || (in_array("v2ray",$malio_config['support_sub_type']))}
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-clash btn-lg btn-round" onclick="importSublink('clash')"><i class="malio-clash"></i> 一键导入 ClashX / CFW 配置</a>
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-kitsunebi copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['kitsunebi']}"><i class="malio-kitsunebi"></i> 复制 Kitsunebi 订阅链接</a>
                        {/if}
                        {if $malio_config['quantumult_mode'] == 'single'}
                        <a href="##" id="quan_sub" class="btn btn-icon icon-left btn-primary btn-quantumult btn-lg btn-round copy-config" onclick="importSublink('quantumult')"><i class="malio-quantumult"></i> 一键导入 Quantumult 配置</a>
                        {elseif $malio_config['quantumult_mode'] == 'all'}
                        <a href="##" id="quan_sub" class="btn btn-icon icon-left btn-primary btn-quantumult btn-lg btn-round copy-config" onclick="Copyconfig(&quot;{$subInfo['quantumult_sub']}&quot;,&quot;#quan_sub&quot;,&quot;quantumult://settings?configuration=clipboard&quot;)"><i class="malio-quantumult"></i> 一键导入 Quantumult 配置</a>
                        {/if}
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-shadowrocket btn-lg btn-round" onclick="importSublink('shadowrocket')"><i class="malio-shadowrocket"></i> 一键导入 Shadowrocket 配置</a>
                        {if (in_array("v2ray",$malio_config['support_sub_type']))}
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-v2ray copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['v2ray']}"><i class="malio-v2rayng"></i> 复制 V2Ray 订阅链接</a>
                        {/if}
                        {if (in_array("ss",$malio_config['support_sub_type']))}
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-ss copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['ss']}"><i class="malio-ssr"></i> 复制 SS 订阅链接</a>
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-quantumult copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['ssd']}"><i class="malio-ssr"></i> 复制 SSD 订阅链接</a>
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-surge btn-lg btn-round" onclick="importSublink('surge2')"><i class="malio-surge"></i> 一键导入 Surge 2 配置</a>
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-surge btn-lg btn-round" onclick="importSublink('surge3')"><i class="malio-surge"></i> 一键导入 Surge 3 配置</a> 
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-surfboard btn-lg btn-round" onclick="importSublink('surfboard')"><i class="malio-surfboard"></i> 一键导入 Surfboard 配置</a>
                        {/if}
                        {if (in_array("ssr",$malio_config['support_sub_type']))}
                        <a href="##" class="btn btn-icon icon-left btn-primary btn-ssr copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['ssr']}"><i class="malio-ssr"></i> 复制 SSR 订阅链接</a>
                        {/if}
                      </div>
                    </div>
                  </div>
                  {/if}

                </div>
              </div>
        </section>
      </div>
      {else}
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h3 class="animated tada mt-5 text-center" style="color:#191d21">👋 欢迎，{$user->user_name}</h3>
                  <h5 class="mb-5 mt-2 text-center" style="color:#191d21d5">按照下面步骤开始使用吧!</h5>
                  <div class="row mt-4">
                    <div class="col-12 col-lg-8 offset-lg-2">
                      <div class="wizard-steps">
                        <div class="wizard-step wizard-step-active" onclick="location='/user/shop'">
                          <div class="wizard-step-icon">
                            <i class="fas fa-shopping-cart"></i>
                          </div>
                          <div class="wizard-step-label">
                            前往商店购买会员订阅计划或免费试用
                          </div>
                        </div>
                        <div class="wizard-step wizard-step-active" onclick="location='/user/tutorial'">
                          <div class="wizard-step-icon">
                            <i class="fas fa-download"></i>
                          </div>
                          <div class="wizard-step-label">
                            下载客户端并按照教程安装
                          </div>
                        </div>
                        <div class="wizard-step wizard-step-success">
                          <div class="wizard-step-icon">
                            <i class="fas fa-grin-squint"></i>
                          </div>
                          <div class="wizard-step-label">
                            开开心心看世界
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      {/if}
      {include file='user/footer.tpl'}
    </div>
  </div>

  {include file='user/scripts.tpl'}

  <!-- Counter Up  -->
  <script src="https://cdn.jsdelivr.net/npm/waypoints@4.0.0/lib/jquery.waypoints.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/counterup@1.0.2/jquery.counterup.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bowser@1.9.4/bowser.min.js"></script>

  <!-- C3 Chart -->
  <script src="https://cdn.jsdelivr.net/npm/d3@3.5.0/d3.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/c3@0.4.10/c3.min.js"></script>
  <script>
    trafficDountChat(
      '{$user->LastusedTraffic()}',
      '{$user->TodayusedTraffic()}',
      '{$user->unusedTraffic()}',
      '{number_format($user->last_day_t/$user->transfer_enable*100,2)}',
      '{number_format(($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100,2)}',
      '{number_format(($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100,2)}'
    )

    $('.counter').counterUp({
      delay: 10,
      time: 1000
    });

    function importSublink(client) {
      if (client == 'quantumult') {
        oneclickImport('quantumult', '{if $malio_config["quantumult_sub_type"]=="v2ray"}{$subInfo["v2ray"]}{elseif $malio_config["quantumult_sub_type"]=="ss"}{$subInfo["ss"]}{elseif $malio_config["quantumult_sub_type"]=="ssr"}{$subInfo["ssr"]}{/if}');
      }
      if (client == 'shadowrocket') {
        oneclickImport('shadowrocket','{$subInfo["shadowrocket"]}')
      };
      if (client == 'surfboard') {
        oneclickImport('surfboard','{$subInfo["surfboard"]}')
      };
      if (client == 'surge2') {
        oneclickImport('surge','{$subInfo["surge2"]}')
      };
      if (client == 'surge3') {
        oneclickImport('surge3','{$subInfo["surge3"]}')
      };
      if (client == 'clash') {
        oneclickImport('clash','{$subInfo["clash"]}')
      };
    }

    appName = "{$config['appName']}";

    setTimeout(loadTrafficChart(), 3000);
</script>
<script>
  function Copyconfig(url, id, jumpurl = "") {
    $.ajax({
      url: url,
      type: 'GET',
      async: false,
      success: function (res) {
        if (res) {
          $("#result").modal();
          $("#msg").html("获取成功");
          $(id).data('data', res);
          console.log(res);
        } else {
          $("#result").modal();
          $("#msg").html("获取失败，请稍后再试");
        }
      }
    });
    const clipboard = new ClipboardJS('.copy-config', {
      text: function () {
        return $(id).data('data');
      }
    });
    clipboard.on('success', function (e) {
      swal({
        type: 'success',
        title: '复制成功，即将跳转到 APP',
        showConfirmButton: false,
        timer: 1500,
        onClose: () => {
          if (jumpurl != "") {
            window.setTimeout(function () {
              window.location.href = jumpurl;
            }, 1000);
          }
        }
      })
    });
    clipboard.on("error", function (e) {
      console.error('Action:', e.action);
      console.error('Trigger:', e.trigger);
      console.error('Text:', e.text);
    });
  }
</script>
</body>

</html>