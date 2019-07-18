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
          </div>
          <div class="section-body">
            <h2 class="section-title">选择合适的会员订阅计划</h2>
            <p class="section-lead">{$malio_config['shop_sub_title']}</p>

            <div class="row">
              <div class="col-12 col-md-4 col-lg-4">
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
              <div class="col-12 col-md-4 col-lg-4">
                <div class="pricing pricing-highlight">
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
              <div class="col-12 col-md-4 col-lg-4">
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
                      <div id="plan_2" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('plan','plan_2')">
                        {$malio_config['plan_2_name']}
                      </div>
                      <div id="plan_3" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('plan','plan_3')">
                        {$malio_config['plan_3_name']}
                      </div>
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

                <div class="row mt-4" id="payment-selection">
                  <div class="col-12">
                    <div class="section-title">选择支付方式</div>
                    <div class="colors row">
                      <div id="alipay" class="color col-12 col-md-2 col-lg-2 active" onclick="selectItem('payment','alipay')">
                        <i class="fab fa-alipay" style="font-size: 1.1rem;vertical-align: -1px;margin-right: 2px;"></i> 支付宝
                      </div>
                      {if $config['payment_system'] != 'f2fpay'}
                      <div id="wechat" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('payment','wechat')">
                        <i class="malio-wechat-pay" style="font-size: 1.1rem;vertical-align: -1px;"></i> 微信支付
                      </div>
                      {/if}
                      {if $config['payment_system'] == 'bitpayx'}
                      <div id="crypto" class="color col-12 col-md-2 col-lg-2" onclick="selectItem('payment','crypto')">
                        <i class="fab fa-btc"></i> 数字货币
                      </div>
                      {/if}
                    </div>
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
                      <div id="total" class="invoice-detail-value">¥ 0</div>
                    </div>
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">账号余额</div>
                      <div id="account-money" class="invoice-detail-value">¥ {$user->money}</div>
                    </div>
                    <hr class="mt-2 mb-2">
                    <div class="invoice-detail-item">
                      <div class="invoice-detail-name">还需要支付</div>
                      <div id="pay-amount" class="invoice-detail-value invoice-detail-value-lg">¥ 0</div>
                    </div>
                  </div>
                </div>

                <hr class="mt-2">
                <div class="text-md-right">
                  <button id="pay-confirm" class="btn btn-primary btn-icon icon-left">立即支付</button>
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
          </div>
          <div class="section-body">
            <h2 class="section-title">选择合适的会员订阅计划</h2>
            <p class="section-lead">{$malio_config['shop_sub_title']}</p>

            <div class="row">
              {foreach $shops as $shop}
              <div class="col-12 col-md-4 col-lg-4">
                <div class="pricing pricing-highlight">
                  <div class="pricing-title">
                    {$shop->name}
                  </div>
                  <div class="pricing-padding">
                    <div class="pricing-price">
                      <div>¥{$shop->price}</div>
                      <div>每月</div>
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
                      {if {$shop->connector()} > '0' }
                      <div class="pricing-item">
                        <div class="pricing-item-icon"><i class="fas fa-check"></i></div>
                        <div class="pricing-item-label">{$shop->connector()}个 在线客户端</div>
                      </div>
                      {/if}
                      {if {$shop->speedlimit()} == '0' }
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
                    <a href="##" onclick="buyConfirm({$shop->id})">购买 <i class="fas fa-arrow-right"></i></a>
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

  {if $malio_config['shop_style'] == 'plans'}
  <script>
    var shop = {
      'plan_1': {
        {foreach $shops as $shop}
        {if $shop->id == $malio_config['plan_shop_id']['plan_1']['1month']}
        "1month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_1']['3month']}
        "3month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_1']['6month']}
        "6month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_1']['12month']}
        "12month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {/foreach}
      },
      'plan_2': {
        {foreach $shops as $shop}
        {if $shop->id == $malio_config['plan_shop_id']['plan_2']['1month']}
        "1month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_2']['3month']}
        "3month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_2']['6month']}
        "6month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_2']['12month']}
        "12month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {/foreach}
      },
      'plan_3': {
        {foreach $shops as $shop}
        {if $shop->id == $malio_config['plan_shop_id']['plan_3']['1month']}
        "1month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_3']['3month']}
        "3month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_3']['6month']}
        "6month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {if $shop->id == $malio_config['plan_shop_id']['plan_3']['12month']}
        "12month": {
          'name': '{$shop->name}',
          'id': {$shop->id},
          'price': {$shop->price}
        },
        {/if}
        {/foreach}
      }
    }

    var userMoney = {$user->money};
    buying_price = 0;
    var paymentSystem = "{$config['payment_system']}";
    updateCheckoutInfo();

  </script>
  {/if}
</body>

{if $config['payment_system'] == 'bitpayx'}
<div class="modal fade" tabindex="-1" role="dialog" id="bitpayx-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">支付</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="text-align: center">
            点击下面按钮打开支付页面并扫描二维码支付<br>
            支付到账需要一段时间，请勿关闭此页面</div>
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>支付到账需要一段时间，请勿关闭此页面</p>
        <div id="f2fpay-qr" style="text-align: center"></div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <a id="to-alipay-app" href="##" type="button" target="blank" class="btn btn-primary">打开手机支付宝</a>
      </div>
    </div>
  </div>
</div>
{/if}
</html>