<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>商店 &mdash; {$config["appName"]}</title>

  <style>
    .colors .active {
      background-color: #6777ef !important;
      color: white !important;
      box-shadow: 0 2px 6px #acb5f6;
    }

    .colors .color {
      color: #6777ef;
      background-color: transparent;
      background-image: none;
      cursor: pointer;
      border: 1px solid #6777ef;
      border-radius: 4px;
      font-size: 1rem;
    }

    #payment-selection #alipay {
      color: #029de3;
      border: 1px solid #029de3;
    }

    #payment-selection #wechat {
      color: #00b235;
      border: 1px solid #00b235;
    }

    #payment-selection #qqpay {
      color: #11b7f5;
      border: 1px solid #11b7f5;
    }

    #payment-selection #alipay[class*="active"] {
      background: #029de3 !important;
      box-shadow: 0 2px 6px #029ce370;
      border: 1px solid #029de3;
    }

    #payment-selection #wechat[class*="active"] {
      background: #00b235 !important;
      box-shadow: 0 2px 6px #00b23570;
      border: 1px solid #00b235;
    }

    #payment-selection #qqpay[class*="active"] {
      background: #11b7f5 !important;
      box-shadow: 0 2px 6px #11b8f570;
      border: 1px solid #11b7f5;
    }

    #payment-selection .fas,
    .far,
    .fab,
    .fal {
      font-size: 1rem;
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      {if $malio_config['shop_style'] == 'plans'}
      <!-- Main Content -->
      <div id="main-page" class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>商店</h1>
            {if $malio_config['shop_enable_traffic_package'] == true && $user->class > 0}
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active">
                <a href="#" class="btn btn-icon icon-left btn-primary" data-toggle="modal" data-target="#traffic-package-modal"><i class="fas fa-gas-pump"></i> 购买流量叠加包</a>
              </div>
            </div>
            {/if}
          </div>
          <div class="section-body">
            <h2 class="section-title">选择合适的会员订阅计划</h2>
            <p class="section-lead">{$malio_config['shop_sub_title']}</p>

            <div class="row">
              {if $malio_config['shop_enable_trail_plan'] == true && $user->class < 0}
              <div class="col-12 col-md-3 col-lg-3">
                <div class="pricing {if $malio_config['shop_enable_trail_plan'] == true}pricing-highlight{/if}">
                  <div class="pricing-title">
                    新用户试用
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>免费</div>
                      <div>一次性</div>
                    </div>
                    <div class="pricing-details">
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_trail_traffic']}GB 使用流量</div>
                      </div>
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_trail_online']}个 在线客户端</div>
                      </div>
                      {foreach $malio_config['plan_trail_feature'] as $feature}
                      <div class="pricing-item">
                        {if $feature['support'] == true}
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        {else}
                        <div class="pricing-item-icon bg-danger text-white"><i class="fas fa-times"></i></div>
                        {/if}
                        <div class="pricing-item-label">{$feature['name']}</div>
                      </div>
                      {/foreach}
                    </div>
                  </div>
                  <div class="pricing-cta">
                    <a href="##" onclick="buyConfirm({$malio_config['shop_trail_plan_shopid']})">开始试用 <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
              {/if}
              <div class="col-12 {if $malio_config['shop_enable_trail_plan'] == true && $user->class < 0}col-md-3 col-lg-3{else}col-md-4 col-lg-4{/if}">
                <div class="pricing">
                  <div class="pricing-title">
                    {$malio_config['plan_1_name']}
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>¥{$malio_config['plan_1_pricing']}</div>
                      <div>每月</div>
                    </div>
                    <div class="pricing-details">
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_1_traffic']}GB 使用流量</div>
                      </div>
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_1_online']}个 在线客户端</div>
                      </div>
                      {foreach $malio_config['plan_1_feature'] as $feature}
                      <div class="pricing-item">
                        {if $feature['support'] == true}
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        {else}
                        <div class="pricing-item-icon bg-danger text-white"><i class="fas fa-times"></i></div>
                        {/if}
                        <div class="pricing-item-label">{$feature['name']}</div>
                      </div>
                      {/foreach}
                    </div>
                  </div>
                  <div class="pricing-cta go-to-buy-page">
                    <a href="#">订阅 <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
              {if $malio_config['enable_plan_2'] == true}
              <div class="col-12 {if $malio_config['shop_enable_trail_plan'] == true && $user->class < 0}col-md-3 col-lg-3{else}col-md-4 col-lg-4{/if}">
                <div class="pricing {if $malio_config['shop_enable_trail_plan'] == false || $user->class >= 0}pricing-highlight{/if}">
                  <div class="pricing-title">
                    {$malio_config['plan_2_name']}
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>¥{$malio_config['plan_2_pricing']}</div>
                      <div>每月</div>
                    </div>
                    <div class="pricing-details">
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_2_traffic']}GB 使用流量</div>
                      </div>
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_2_online']}个 在线客户端</div>
                      </div>
                      {foreach $malio_config['plan_2_feature'] as $feature}
                      <div class="pricing-item">
                        {if $feature['support'] == true}
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        {else}
                        <div class="pricing-item-icon bg-danger text-white"><i class="fas fa-times"></i></div>
                        {/if}
                        <div class="pricing-item-label">{$feature['name']}</div>
                      </div>
                      {/foreach}
                    </div>
                  </div>
                  <div class="pricing-cta go-to-buy-page">
                    <a href="#">订阅 <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
              {/if}
              {if $malio_config['enable_plan_3'] == true}
              <div class="col-12 {if $malio_config['shop_enable_trail_plan'] == true && $user->class < 0}col-md-3 col-lg-3{else}col-md-4 col-lg-4{/if}">
                <div class="pricing">
                  <div class="pricing-title">
                    {$malio_config['plan_3_name']}
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>¥{$malio_config['plan_3_pricing']}</div>
                      <div>每月</div>
                    </div>
                    <div class="pricing-details">
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_3_traffic']}GB 使用流量</div>
                      </div>
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$malio_config['plan_3_online']}个 在线客户端</div>
                      </div>
                      {foreach $malio_config['plan_3_feature'] as $feature}
                      <div class="pricing-item">
                        {if $feature['support'] == true}
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        {else}
                        <div class="pricing-item-icon bg-danger text-white"><i class="fas fa-times"></i></div>
                        {/if}
                        <div class="pricing-item-label">{$feature['name']}</div>
                      </div>
                      {/foreach}
                    </div>
                  </div>
                  <div class="pricing-cta go-to-buy-page">
                    <a href="#">订阅 <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
              {/if}
            </div>
          </div>
        </section>
      </div>
      <div id="buy-page" class="main-content" style="display:none">
        <section class="section">
          <div class="section-header">
            <div class="section-header-back">
              <a href="##" onclick="backToShop()" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>购买会员计划</h1>
          </div>
          <div class="section-body">
            <div class="invoice">
              <div class="invoice-print">

                <div class="row" id="plan-selection">
                  <div class="col-md-12">
                    <div class="section-title" style="margin-top: 0;">选择会员订阅计划</div>
                    <div class="colors">
                      <div id="plan_1" class="color col-12 col-md-2 col-lg-2 active" onclick="selectItem('plan','plan_1')">
                        {$malio_config['plan_1_name']}
                      </div>
                      {if $malio_config['enable_plan_2'] == true}
                      <div id="plan_2" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('plan','plan_2')">
                        {$malio_config['plan_2_name']}
                      </div>
                      {/if}
                      {if $malio_config['enable_plan_3'] == true}
                      <div id="plan_3" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('plan','plan_3')">
                        {$malio_config['plan_3_name']}
                      </div>
                      {/if}
                    </div>
                  </div>
                </div>

                <div class="row mt-4" id="time-selection">
                  <div class="col-12">
                    <div class="section-title">选择会员时长</div>
                    <div class="colors">
                      <div id="1month" class="color col-12 col-md-2 col-lg-2 active" onclick="selectItem('time','1month')">
                        1个月
                      </div>
                      <div id="3month" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('time','3month')">
                        3个月
                      </div>
                      <div id="6month" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('time','6month')">
                        6个月
                      </div>
                      <div id="12month" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('time','12month')">
                        12个月
                      </div>
                    </div>
                  </div>
                </div>

                {if $malio_config['shop_enable_autorenew'] == true}
                <div class="row mt-4" id="autorenew-selection">
                  <div class="col-12">
                    <div class="section-title">自动续费</div>
                    <div class="colors row">
                      <div id="autorenew-off" class="color col-12 col-md-2 col-lg-2 active" onclick="selectItem('autorenew','autorenew-off')">
                        关闭
                      </div>
                      <div id="autorenew-on" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('autorenew','autorenew-on')">
                        开启
                      </div>
                    </div>
                  </div>
                </div>
                {/if}

                <div class="row mt-4" id="payment-selection">
                  <div class="col-12">
                    <div class="section-title">选择支付方式</div>
                    <div class="colors row">
                      <div id="alipay" class="color col-12 col-md-2 col-lg-2 active" onclick="selectItem('payment','alipay')">
                        <i class="fab fa-alipay" style="font-size: 1.1rem;vertical-align: -1px;margin-right: 2px;"></i> 支付宝
                      </div>
                      {if $config['payment_system'] != 'f2fpay' && $config['payment_system'] != 'spay'}
                      <div id="wechat" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('payment','wechat')">
                        <i class="malio-wechat-pay" style="font-size: 1.1rem;vertical-align: -1px;"></i> 微信支付
                      </div>
                      {/if}
                      {if $config['payment_system'] == 'bitpayx'}
                      <div id="crypto" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('payment','crypto')">
                        <i class="fab fa-btc"></i> 数字货币
                      </div>
                      {/if}
                      {if $config['payment_system'] == 'codepay' || $config['payment_system'] == 'flyfoxpay'}
                      <div id="qqpay" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('payment','qqpay')">
                        <i class="fab fa-qq"></i> QQ支付
                      </div>
                      {/if}
                    </div>
                    <p class="text-muted">* 默认抵扣账户余额</p>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-lg-8">
                    <div class="section-title">购买须知</div>
                    <p class="text-muted">
                      {$malio_config['buyer_reading']}
                    </p>
                  </div>
                  <div class="col-lg-4 text-right">
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">商品名称</div>
                      <div id="shop-name" class="invoice-detail-value">Null</div>
                    </div>
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">总价</div>
                      <div id="total" class="invoice-detail-value">Null</div>
                    </div>
                    <div id="coupon-row" class="invoice-detail-item" style="display: none">
                      <div class="invoice-detail-name">优惠码</div>
                      <div id="coupon-money" class="invoice-detail-value">Null</div>
                    </div>
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">余额支付</div>
                      <div id="account-money" class="invoice-detail-value">¥ -{$user->money}</div>
                    </div>
                    <hr class="mt-2 mb-2">
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">还需要支付</div>
                      <div id="pay-amount" class="invoice-detail-value invoice-detail-value-lg">Null</div>
                    </div>
                  </div>
                </div>

                <hr class="mt-2">
                <div class="text-md-right">
                  <div class="float-lg-left mb-lg-0 mb-3">
                    <button id="coupon-btn" class="btn btn-primary btn-icon icon-left" data-toggle="modal" data-target="#coupon-modal"><i class="fas fa-tag"></i> 使用优惠码</button>
                  </div>
                  <button id="pay-confirm" class="btn btn-warning btn-icon icon-left"><i class="fas fa-check"></i> 立即支付</button>
                </div>

              </div>
            </div>
          </div>
      </div>
      {elseif $malio_config['shop_style'] == 'legacy'}
      <div id="main-page" class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>商店</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active">
                {if $malio_config['shop_enable_coupon'] == true}
                <a id="coupon-btn" href="#" class="btn btn-icon icon-left btn-primary" data-toggle="modal" data-target="#coupon-modal"><i class="fas fa-tag"></i> 使用优惠码</a>
                {/if}
                {if $malio_config['shop_enable_traffic_package'] == true && $user->class > 0}
                <a href="#" class="btn btn-icon icon-left btn-primary" data-toggle="modal" data-target="#traffic-package-modal"><i class="fas fa-gas-pump"></i> 购买流量叠加包</a>
                {/if}
              </div>
            </div>
          </div>
          <div class="section-body">
            <h2 class="section-title">选择合适的会员订阅计划</h2>
            <p class="section-lead">{$malio_config['shop_sub_title']}</p>

            <div class="row">
              {foreach $shops as $shop}
              {if $malio_config['shop_trail_plan_shopid'] == $shop->id && $user->class > 0}
              {continue}
              {/if}
              <div class="col-12 col-md-4 col-lg-4">
                <div class="pricing pricing-highlight">
                  <div class="pricing-title">
                    {$shop->name}
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>¥{$shop->price}</div>
                    </div>
                    <div class="pricing-details">
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$shop->bandwidth()}GB 使用流量</div>
                      </div>
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$shop->class_expire()}天 会员时长</div>
                      </div>
                      {if {$shop->connector()} != '0' }
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$shop->connector()}个 在线客户端</div>
                      </div>
                      {/if}
                      {if {$shop->speedlimit()} != '0' }
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$shop->speedlimit()} 最高速率</div>
                      </div>
                      {/if}
                      {foreach $shop->content_extra() as $service}
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$service[1]}</div>
                      </div>
                      {/foreach}
                    </div>
                  </div>
                  <div class="pricing-cta">
                    <a href="##" data-toggle="modal" data-target="#legacy-modal" onclick="legacySelect({$shop->id})">购买 <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
              {/foreach}
            </div>
          </div>
        </section>
      </div>
      {/if}
    </div>
    </section>
  </div>
  {include file='user/footer.tpl'}
  </div>
  </div>

  {include file='user/scripts.tpl'}
  <script src="https://cdn.jsdelivr.net/npm/kjua@0.1.2/dist/kjua.min.js"></script>

  {if $malio_config['shop_style'] == 'plans'}
  <script>
    var userMoney = '{$user->money}';
    var paymentSystem = "{$config['payment_system']}";
    updateCheckoutInfo();
  </script>
  {/if}

  {if $malio_config['shop_enable_coupon'] == true}
  <div class="modal fade" tabindex="-1" role="dialog" id="coupon-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">使用优惠码</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>请输入优惠码</label>
            <input id="coupon-code" type="text" class="form-control" onclick="hideFeedback('coupon-feedback')">
            <div id="coupon-feedback" class="invalid-feedback">
              feedback
            </div>
          </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
          <button onclick="updateCoupon()" type="button" class="btn btn-primary">使用</button>
          <button onclick="cancelCoupon()" type="button" class="btn btn-secondary" data-dismiss="modal">取消使用</button>
        </div>
      </div>
    </div>
  </div>
  {/if}
</body>

{if $config['payment_system'] == 'bitpayx'}
<div class="modal fade" tabindex="-1" role="dialog" id="bitpayx-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
          点击“继续支付”打开支付页面支付<br>
          支付到账需要一段时间，请勿关闭或刷新此页面</div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-bitpayx" href="##" type="button" target="blank" class="btn btn-primary">继续支付</a>
      </div>
    </div>
  </div>
</div>
{/if}

{if $config['payment_system'] == 'f2fpay'}
<div class="modal fade" tabindex="-1" role="dialog" id="f2fpay-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">打开支付宝，扫码支付</h5>
      </div>
      <div class="modal-body">
        <p>支付到账需要一段时间，请勿关闭或刷新此页面</p>
        <div id="f2fpay-qr" style="text-align: center"></div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-alipay-app" href="##" type="button" target="blank" class="btn btn-primary">打开手机支付宝</a>
      </div>
    </div>
  </div>
</div>
{/if}
{if $config['payment_system'] == 'spay'}
<div class="modal fade" tabindex="-1" role="dialog" id="spay-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
          点击“继续支付”打开支付页面支付<br>
          支付到账需要一段时间，请勿关闭或刷新此页面</div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-spay" href="##" type="button" target="blank" class="btn btn-primary">继续支付</a>
      </div>
    </div>
  </div>
</div>
{/if}

{if $config['payment_system'] == 'codepay'}
<div class="modal fade" tabindex="-1" role="dialog" id="codepay-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
          点击“继续支付”打开支付页面支付<br>
          支付到账需要一段时间，请勿关闭或刷新此页面</div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-codepay" href="##" type="button" target="blank" class="btn btn-primary">继续支付</a>
      </div>
    </div>
  </div>
</div>
{/if}

{if $config['payment_system'] == 'tomatopay'}
<div class="modal fade" tabindex="-1" role="dialog" id="tmtpay-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
          点击“继续支付”打开支付页面支付<br>
          支付到账需要一段时间，请勿关闭或刷新此页面</div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-tmtpay" href="##" type="button" target="blank" class="btn btn-primary">继续支付</a>
      </div>
    </div>
  </div>
</div>
{/if}

{if $config['payment_system'] == 'flyfoxpay'}
<div class="modal fade" tabindex="-1" role="dialog" id="flyfox-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
          点击“继续支付”打开支付页面支付<br>
          支付到账需要一段时间，请勿关闭或刷新此页面</div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-flyfox" href="##" type="button" target="blank" class="btn btn-primary">继续支付</a>
      </div>
    </div>
  </div>
</div>
{/if}

{if $malio_config['shop_enable_traffic_package'] == true && $user->class > 0}
<div class="modal fade" tabindex="-1" role="dialog" id="traffic-package-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">请选择流量叠加包</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          {foreach $malio_config['shop_traffic_packages'] as $package}
          <div class="custom-control custom-radio">
            <input type="radio" value="{$package['shopid']}" id="tp-{$package['shopid']}" name="traffic-package-radio" class="custom-control-input">
            <label class="custom-control-label" for="tp-{$package['shopid']}"> {$package['price']} 元 {$package['traffic']}GB 流量叠加包</label>
          </div>
          {/foreach}
        </div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button onclick="buyTrafficPackage()" type="button" data-dismiss="modal" class="btn btn-primary">购买</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
{/if}

{if $malio_config['shop_style'] == 'legacy'}
<div class="modal fade" tabindex="-1" role="dialog" id="legacy-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">提示</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>确定购买此套餐？</p>
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="legacy-autorenew">
          <label class="custom-control-label" for="legacy-autorenew">开启自动续费</label>
        </div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button onclick="legacyBuy()" type="button" target="blank" class="btn btn-primary" data-dismiss="modal">确定</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
{/if}
</html>