<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" name="viewport">

  <title>{$config['appName']}</title>
  <link rel="shortcut icon" href="/">
  <link rel="stylesheet" href="/theme/malio/index/css/prism.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.2/css/all.min.css">
  <link rel="stylesheet" href="/theme/malio/index/css/chocolat.css">
  <link rel="stylesheet" href="/theme/malio/index/css/style.css">
  <link rel="stylesheet" href="/theme/malio/index/css/custom.css">
  <link rel="stylesheet" href="/theme/malio/index/landing/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@4.5.0/dist/css/swiper.min.css">

  {if $malio_config['enable_crisp'] == true && $malio_config['enable_crisp_outside'] == true}
  {include file='crisp.tpl'}
  {/if}
  {if $malio_config['enable_chatra'] == true && $malio_config['enable_crisp_outside'] == true}
  {include file='chatra.tpl'}
  {/if}
</head>

{if $malio_config['enable_landing_page'] == true}

<body class="">
  <nav class="navbar navbar-reverse navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand smooth" href="/">{$config['appName']}</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto ml-lg-3 align-items-lg-center">
          <li class="nav-item"><a href="#features" class="nav-link">特性</a></li>
          <li class="nav-item"><a href="#purchase" class="nav-link">价格</a></li>
          <li class="nav-item"><a href="#contact-support" class="nav-link">支持</a></li>
          {if $user->isLogin}
          <li class="nav-item d-lg-none d-md-block"><a href="/user" class="nav-link smooth">用户中心</a></li>
          {else}
          <li class="nav-item d-lg-none d-md-block"><a href="/auth/login" class="nav-link smooth">登录</a></li>
          {/if}
        </ul>
        <ul class="navbar-nav ml-auto align-items-lg-center d-none d-lg-block">
          <li class="ml-lg-3 nav-item">
            {if $user->isLogin}
            <a href="/user" class="btn btn-round smooth btn-icon icon-left">
              <i class="fab fa-fort-awesome"></i> 用户中心
            </a>
            {else}
            <a href="/auth/login" class="btn btn-round smooth btn-icon icon-left">
              <i class="fas fa-sign-in-alt"></i> 登录
            </a>
            {/if}
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="hero-wrapper" id="top">
    <div class="hero">
      <div class="container">
        <div class="text text-center text-lg-left">
          {if $malio_config['index_enable_promotion'] == true}
          <a href="/user/shop" class="headline">
            <div class="badge badge-danger">New</div>
            {$malio_config['index_promotion_text']} &nbsp; <i class="fas fa-chevron-right"></i>
          </a>
          {/if}
          <h1>{$malio_config['index_slogan']}</h1>
          <p class="lead">
            {$malio_config['index_sub_slogan']}
          </p>
          <div class="cta">
            <a class="btn btn-lg btn-warning btn-icon icon-right" href="/auth/register">开始使用 <i class="fas fa-chevron-right"></i></a> &nbsp;
          </div>
          {if $user->isLogin}
          <div class="mt-3 text-job d-lg-none">
              <a href="/user" style="color:rgba(255,255,255,.6);">
                进入用户中心
              </a>
            </div>
          {else}
          <div class="mt-3 text-job d-lg-none">
            <a href="/auth/login" style="color:rgba(255,255,255,.6);">
              已有账号？立即登录
            </a>
          </div>
          {/if}
        </div>
        <div class="image d-none d-lg-block">
          <img src="/theme/malio/index/landing/ill.svg" alt="img">
        </div>
      </div>
    </div>
  </div>
  <div class="callout container">
    <div class="row">
      <div class="col-md-6 col-12 mb-4 mb-lg-0">
        <div class="text-job text-muted text-14">为什么选择 {$config['appName']}</div>
        <div class="h1 mb-0 font-weight-bold mt-1" style="font-size: 2rem;">迄今为止</div>
      </div>
      <div class="col-4 col-md-2 text-center">
        <div class="h2 font-weight-bold">{$malio_config['index_statistics_1_data']}</div>
        <div class="text-uppercase font-weight-bold ls-2 text-primary">{$malio_config['index_statistics_1_name']}</div>
      </div>
      <div class="col-4 col-md-2 text-center">
        <div class="h2 font-weight-bold">{$malio_config['index_statistics_2_data']}</div>
        <div class="text-uppercase font-weight-bold ls-2 text-primary">{$malio_config['index_statistics_2_name']}</div>
      </div>
      <div class="col-4 col-md-2 text-center">
        <div class="h2 font-weight-bold">{$malio_config['index_statistics_3_data']}</div>
        <div class="text-uppercase font-weight-bold ls-2 text-primary">{$malio_config['index_statistics_3_name']}</div>
      </div>
    </div>
  </div>

  <section id="features">
    <div class="container">
      <div class="row mb-5 text-center">
        <div class="col-lg-10 offset-lg-1">
          <h2>为你 <span class="text-primary">量身定制</span> 的服务</h2>
          <p class="lead">可靠的基础设施，并能提供您喜爱的诸多功能</p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="features">
            <div class="feature">
              <div class="feature-icon">
                <i class="fas fa-mobile-alt"></i>
              </div>
              <h5>高速稳定</h5>
              <p>体验宛若身在海外的访问速度</p>
            </div>
            <div class="feature">
              <div class="feature-icon">
                <i class="fab fa-html5"></i>
              </div>
              <h5>便携设置
              </h5>
              <p>我们的服务适用于 macOS、iOS、Android、Windows 和 Linux。</p>
            </div>
            <div class="feature">
              <div class="feature-icon">
                <i class="fas fa-fire"></i>
              </div>
              <h5>节省成本</h5>
              <p>相比自托管服务可节省大量费用</p>
            </div>
            <div class="feature">
              <div class="feature-icon">
                <i class="fas fa-check"></i>
              </div>
              <h5>全球互联</h5>
              <p>通过 IXP 连接至全球内容提供商，更加快速

              </p>
            </div>
            <div class="feature">
              <div class="feature-icon">
                <i class="fas fa-columns"></i>
              </div>
              <h5>运营商友好
              </h5>
              <p>我们的产品和您的当地运营商兼容，适用于您的固网与移动网络。

              </p>
            </div>
            <div class="feature">
              <div class="feature-icon">
                <i class="fas fa-chevron-right"></i>
              </div>
              <h5>多应用兼容
              </h5>
              <p>提供全面且可靠的第三方应用程序兼容。

              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="design" class="section-design">
    <div class="container">
      <div class="row">
        <div class="col-lg-5 d-none d-lg-block">
          <img src="/theme/malio/index/img/data_center.svg" alt="user flow" class="img-fluid">
        </div>
        <div class="col-lg-7 pl-lg-5 col-md-12">
          <div class="badge badge-primary mb-3">CROSS DEVICES & PLATFORMS</div>
          <h2>在你心爱的电子设备中使用，无论是移动的手机还是固定的电脑，随时随处可用</h2>
          <p class="lead">{$config['appName']} 的服务适用于 macOS、iOS、Android、Windows 和 Linux，借助第三方客户端，可在手机、电脑、路由器、游戏机、电视盒子中使用。</p>
          <div class="mt-4">
            <a href="" class="link-icon">
              查看支持的第三方客户端 <i class="fas fa-chevron-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="components" class="section-design section-design-right">
    <div class="container">
      <div class="row">
        <div class="col-lg-7 pr-lg-5 pr-0">
          <div class="badge badge-primary mb-3">UNLOCK STREAMING MEDIA</div>
          <h2>解锁流媒体，观赏和聆听优质的内容</h2>
          <p class="lead">通过 {$config['appName']} 的服务，可以观看包括 Netflix、Hulu、HBO、TVB、Happyon、AbemaTV 等在内的多种流媒体视频，聆听包括 Spotify、Pandora 等在内的流媒体音乐。</p>
        </div>
        <div class="col-lg-5 d-none d-lg-block">
          <div class="abs-images">
            <img src="/theme/malio/index/img/netflix.png" alt="user flow" class="img-fluid rounded dark-shadow">
            <img src="/theme/malio/index/img/spotify.png" alt="user flow" class="img-fluid rounded dark-shadow">
            <img src="/theme/malio/index/img/hbo.png" alt="user flow" class="img-fluid rounded dark-shadow">
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="support-us" class="support-us section-design">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 d-none d-lg-block">
          <img src="/theme/malio/index/img/waiting_notifications.svg" alt="user flow" class="img-fluid" style="width:30rem">
        </div>
        <div class="col-lg-4 col-md-12">
          <h2>不仅仅如此，亦包含以下诸多特性</h2>
          <p class="lead">* 部分特性需要配合第三方客户端与托管规则使用</p>
          <ul class="list-icons">
            {foreach $malio_config['index_more_features'] as $feature}
            <li>
              <span class="card-icon bg-primary text-white">
                <i class="{$feature['icon']}"></i>
              </span>
              <span>{$feature['feature']}</span>
            </li>
            {/foreach}
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section id="purchase" class="bg-gr">
    <style>
      .pricing {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
        background-color: #fff;
        border-radius: 3px;
        border: none;
        position: relative;
        margin-bottom: 30px;
        text-align: center;
      }

      .pricing.pricing-highlight .pricing-title {
        background-color: #6777ef;
        color: #fff;
      }

      .pricing.pricing-highlight .pricing-cta a {
        background-color: #6777ef;
        color: #fff;
      }

      .pricing.pricing-highlight .pricing-cta a:hover {
        background-color: #394eea !important;
      }

      .pricing .pricing-padding {
        padding: 40px;
      }

      .pricing .pricing-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        background-color: #f3f6f8;
        color: #6777ef;
        border-radius: 0 0 3px 3px;
        display: inline-block;
        padding: 5px 15px;
      }

      .pricing .pricing-price {
        margin-bottom: 45px;
        color: #6c757d
      }

      .pricing .pricing-price div:first-child {
        font-weight: 600;
        font-size: 50px;
      }

      .pricing .pricing-details {
        text-align: left;
        display: inline-block;
        color: #6c757d;
      }

      .pricing .pricing-details .pricing-item {
        display: flex;
        margin-bottom: 15px;
      }

      .pricing .pricing-details .pricing-item .pricing-item-icon {
        width: 20px;
        height: 20px;
        line-height: 20px;
        border-radius: 50%;
        text-align: center;
        background-color: #63ed7a;
        color: #fff;
        margin-right: 10px;
      }

      .pricing .pricing-details .pricing-item .pricing-item-icon i {
        font-size: 11px;
      }

      .pricing .pricing-cta {
        margin-top: 20px;
      }

      .pricing .pricing-cta a {
        display: block;
        padding: 20px 40px;
        background-color: #f3f6f8;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        border-radius: 0 0 3px 3px;
      }

      .pricing .pricing-cta a .fas,
      .pricing .pricing-cta a .far,
      .pricing .pricing-cta a .fab,
      .pricing .pricing-cta a .fal,
      .pricing .pricing-cta a .ion {
        margin-left: 5px;
      }

      .pricing .pricing-cta a:hover {
        background-color: #e3eaef;
      }
    </style>
    <div class="container">
      <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-10 offset-lg-1 col-12 text-center">
          <h2 class="mt-3 text-white">出色的体验，意想不到的价格</h2>
          <p class="lead" style="color: rgba(255, 255, 255, 0.5);">
            不要把宝贵的时间，浪费在等待上。即刻开启全球网络中继服务，在任何时间任何地点访问全球互联网。
          </p>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-12 {if $malio_config['enable_plan_3'] == true}col-md-4 col-lg-4{else}col-md-6 col-lg-6{/if}">
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
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_1_traffic']}GB 使用流量</div>
                </div>
                <div class="pricing-item">
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_1_online']}个 在线客户端</div>
                </div>
                {foreach $malio_config['plan_1_feature'] as $feature}
                <div class="pricing-item">
                  {if $feature['support'] == true}
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$feature['name']}</div>
                  {else}
                  <div class="pricing-item-icon text-white" style="background: #98a6ad"><i class="fas fa-times"></i></div>
                  <div class="pricing-item-label text-muted"><del>{$feature['name']}</del></div>
                  {/if}
                </div>
                {/foreach}
              </div>
            </div>
            <div class="pricing-cta go-to-buy-page">
              <a href="/user/shop">订阅 <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        {if $malio_config['enable_plan_2'] == true}
        <div class="col-12 {if $malio_config['enable_plan_3'] == true}col-md-4 col-lg-4{else}col-md-6 col-lg-6{/if}">
          <div class="pricing">
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
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_2_traffic']}GB 使用流量</div>
                </div>
                <div class="pricing-item">
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_2_online']}个 在线客户端</div>
                </div>
                {foreach $malio_config['plan_2_feature'] as $feature}
                <div class="pricing-item">
                  {if $feature['support'] == true}
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$feature['name']}</div>
                  {else}
                  <div class="pricing-item-icon text-white" style="background: #98a6ad"><i class="fas fa-times"></i></div>
                  <div class="pricing-item-label text-muted"><del>{$feature['name']}</del></div>
                  {/if}
                </div>
                {/foreach}
              </div>
            </div>
            <div class="pricing-cta go-to-buy-page">
              <a href="/user/shop">订阅 <i class="fas fa-arrow-right"></i></a>
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
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_3_traffic']}GB 使用流量</div>
                </div>
                <div class="pricing-item">
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$malio_config['plan_3_online']}个 在线客户端</div>
                </div>
                {foreach $malio_config['plan_3_feature'] as $feature}
                <div class="pricing-item">
                  {if $feature['support'] == true}
                  <div class="pricing-item-icon" style="background:#6574f7"><i class="fas fa-check"></i></div>
                  <div class="pricing-item-label">{$feature['name']}</div>
                  {else}
                  <div class="pricing-item-icon text-white" style="background: #98a6ad"><i class="fas fa-times"></i></div>
                  <div class="pricing-item-label text-muted"><del>{$feature['name']}</del></div>
                  {/if}
                </div>
                {/foreach}
              </div>
            </div>
            <div class="pricing-cta go-to-buy-page">
              <a href="/user/shop">订阅 <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        {/if}
      </div>
  </section>

  <section id="try" class="section-dark">
    <div class="container ">
      <div class="swiper-container">
        <div class="swiper-wrapper">
          {foreach $malio_config['index_user_reviews'] as $review}
          <div class="swiper-slide">
            <div class="col-lg-8 offset-lg-2 text-center">
              <blockquote>{$review['review']}</blockquote>
              <div class="text-name mt-4">{$review['user']}</div>
              <div class="text-job mt-2">{$review['position']}</div>
            </div>
          </div>
          {/foreach}
        </div>
        <div class="swiper-button-prev" style="color: rgba(255,255,255,.6);background-image: none;"><i class="fas fa-chevron-left" style="font-size:20px;"></i></div>
        <div class="swiper-button-next" style="color: rgba(255,255,255,.6);background-image: none;"><i class="fas fa-chevron-right" style="font-size:20px;"></i></div>
      </div>
    </div>
  </section>

  <section class="download-section" class="bg-white">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-7">
          <h2>开始使用优秀的全球网络中继服务</h2>
          <p class="lead">Start using outstanding global network relay service.</p>
        </div>
        <div class="col-md-5 text-right">
          <a href="/auth/register" class="btn btn-primary btn-lg">立即注册</a>
        </div>
      </div>
    </div>
  </section>

  <section id="contact-support" class="before-footer">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <div class="card long-shadow">
            <div class="card-body d-flex p-45">
              <div class="card-icon bg-primary text-white">
                <i class="fas fa-headset"></i>
              </div>
              <div>
                <h5>售前咨询</h5>
                <p class="lh-sm">有任何关于会员计划的疑问？联系我们的售前咨询小组，马上为您解答。</p>
                <div class="mt-4 text-right">
                  <a href="/" class="link-icon">立即咨询 <i class="fas fa-chevron-right"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card long-shadow">
            <div class="card-body p-45 d-flex">
              <div class="card-icon bg-primary text-white">
                <i class="far fa-life-ring"></i>
              </div>
              <div>
                <h5>技术支持</h5>
                <p class="lh-sm">{$config['appName']} 在您的订阅周期内为您提供一定程度上的技术支持。</p>
                <div class="mt-4 text-right">
                  <a href="/" class="link-icon">联系技术顾问 <i class="fas fa-chevron-right"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-5">
          <h3 class="text-capitalize">{$config['appName']}</h3>
          <div class="pr-lg-5">
            <p>致力于为用户提供高速稳定的高性价比网络中继服务</p>
            <p>&copy; {$config['appName']}&nbsp;&bull;&nbsp;Powered by <a href="/staff">SSPANEL</a>&nbsp;&bull;&nbsp;Theme by <a href="https://t.me/editXY" target="blank">editXY</a></p>
            <div class="mt-4 social-links">
              <a href="/"><i class="fab fa-github"></i></a>
              <a href="/"><i class="fab fa-twitter"></i></a>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="row">
            <div class="col-md-4">
              <h4>用户中心</h4>
              <ul>
                <li><a href="/">商店</a></li>
                <li><a href="/">节点列表</a></li>
                <li><a href="/">邀请注册</a></li>
                <li><a href="/">下载和使用</a></li>
              </ul>
            </div>
            <div class="col-md-4">
              <h4>支持</h4>
              <ul>
                <li><a href="/">联系我们</a></li>
                <li><a href="/">新建工单</a></li>
                <li><a href="/">常见问题</a></li>
                <li><a href="/">加入 Telegram 群组</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script src="/theme/malio/index/js/jquery.min.js"></script>
  <script src="/theme/malio/index/js/popper.js"></script>
  <script src="/theme/malio/index/js/tooltip.js"></script>
  <script src="/theme/malio/index/js/bootstrap.min.js"></script>
  <script src="/theme/malio/index/js/prism.js"></script>
  <script src="/theme/malio/index/js/stisla.js"></script>
  <script src="/theme/malio/index/js/script.js"></script>


  <!--End mc_embed_signup-->
  <script src="https://cdn.jsdelivr.net/npm/swiper@4.5.0/dist/js/swiper.min.js"></script>
  <script src="/theme/malio/js/malio.js?{$malio_config['malio_js_version']}"></script>

  <script>
    var mySwiper = new Swiper('.swiper-container', {
      direction: 'horizontal',
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: true,
      },
    })
  </script>

</body>
{else}
{if $user->isLogin}
<script>
  window.location.href = '/user';
</script>
{else}
<script>
  window.location.href = '/auth/login';
</script>
{/if}
{/if}

</html>
