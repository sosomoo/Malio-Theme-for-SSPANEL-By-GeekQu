<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>Android 使用教程 &mdash; {$config["appName"]}</title>

  <style>
    .btn-app {
      background: linear-gradient(to right, #224F87, #132748) !important;
      color: white !important;
      border-color: #224F87;
      box-shadow: 0 2px 6px rgba(34, 79, 135, 0.42);
      margin-bottom: 16px;
    }

    .card a {
      color: #224F87;
    }

    .steps {
      margin: 10px;
      padding: 0px;
    }

    li {
      list-style: none;
    }

    .step-no {
      font-size: 30px;
      font-weight: 700;
      color: #2E46AD;
    }

    .right-pic {
      text-align: right;
    }

    .tutorial-pic img {
      border-radius: 5px;
      width: 100%;
      max-width: 24rem;
    }

    @media (max-width: 767px) {
      .right-pic {
        display: block;
      }

      .tutorial-pic img {
        margin-top: 16px;
        border-radius: 5px;
        max-width: 100%;
      }

      .qrcode-btn {
        display: none
      }

      .hide-on-mobie {
        display: none;
      }
    }

    .faq h6 {
      color: #191d21;
    }

    .faq h6:before {
      content: ' ';
      border-radius: 5px;
      height: 6px;
      width: 6px;
      background-color: #2E46AD;
      display: inline-block;
      float: left;
      margin-top: 6px;
      margin-right: 8px;
    }

    .faq p {
      font-weight: normal !important;
    }
  </style>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <div class="section-header-back">
              <a href="/user/tutorial" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Android 使用教程</h1>
            {if $malio_config['display_more_app_button'] == true || $malio_config['enable_windows_gaming_tutorial'] == true}
            <div class="section-header-breadcrumb">
              {if $malio_config['display_more_app_button'] == true}
              <div class="btn-group dropleft">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  其他客户端
                </button>
                <div class="dropdown-menu dropleft">
                  <a class="dropdown-item has-icon" href="/user/tutorial?os=android&client=ssr"><i class="malio-ssr"></i>SSR</a>
                  <a class="dropdown-item has-icon" href="/user/tutorial?os=android&client=kitsunebi"><i class="malio-kitsunebi"></i>Kitsunebi</a>
                  <a class="dropdown-item has-icon" href="/user/tutorial?os=android&client=v2rayng"><i class="malio-v2rayng"></i>v2rayNG</a>
                  <a class="dropdown-item has-icon" href="/user/tutorial?os=android&client=surfboard"><i class="malio-surfboard"></i>Surfboard</a>
                </div>
              </div>
              {/if}
            </div>
            {/if}
          </div>
          <div class="section-body">
            <div class="row mt-sm-4">
              <div class="col-12">
                {if !URL::SSCanConnect($user, $mu) && !(in_array("v2ray",$malio_config['support_sub_type']))}
                <div class="alert alert-warning alert-has-icon">
                  <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                  <div class="alert-body">
                    <div class="alert-title">无法使用此客户端</div>
                    您的加密、混淆和协议不兼容 SS 客户端。如需使用，请在 <a href="/user/edit"><u>连接设置</u></a> 页面更改 “加密方式”、“混淆和协议” 为 SS 可连接的选项。
                  </div>
                </div>
                {/if}
                <div class="card">
                  <div class="card-body">
                    <ul class="steps">
                      <li>
                        <div class="row">
                          <div class="left-text col-xs-12 col-md-6 col-lg-6">
                            <label class="step-no">1.</label>
                            <p>下载并安装 Clash for Android 客户端。</p>
                            <a href="/client-download/clash-for-android.apk" class="btn btn-icon icon-left btn-primary btn-app btn-lg btn-round" target="blank"><i class="fas fa-download"></i> 下载客户端</a>
                          </div>
                          <div class="right-pic col-xs-12 col-md-6 col-lg-6">
                            <div class="tutorial-pic hide-on-mobie">
                              <img style="border:1px solid #f0f0f0" src="/theme/malio/img/tutorial/blank-blue.jpg">
                            </div>
                          </div>
                        </div>
                      </li>
                      <li>
                        <hr>
                        <div class="row">
                          <div class="left-text col-xs-12 col-md-6 col-lg-6">
                            <label class="step-no">2.</label>
                            <p>打开客户端，点击 Profiles，点击 + New Profile，选择 URL，然后将 Name 填写为 {$config['appName']} ，将托管配置链接粘贴到URL的框内，点击右上角 <i class="fas fa-check"></i> 按钮即可。</p>
                            <a href="##" class="btn btn-icon icon-left btn-primary btn-app copy-text btn-lg btn-round" data-clipboard-text="{$subInfo['clash']}"><i class="malio-clash"></i> 复制 Clash 托管配置链接</a>
                          </div>
                          <div class="right-pic col-xs-12 col-md-6 col-lg-6">
                            <div class="tutorial-pic">
                              <img src="/theme/malio/img/tutorial/blank-blue.jpg">
                            </div>
                          </div>
                        </div>
                      </li>
                      <li>
                        <hr>
                        <div class="row">
                          <div class="left-text col-xs-12 col-md-6 col-lg-6">
                            <label class="step-no">3.</label>
                            <p>会到主界面，点击顶部灰色卡片，卡片颜色变成蓝色即代表代理已开启。开启后，可在第二个卡片 Proxy 里切换节点。</p>
                          </div>
                          <div class="right-pic col-xs-12 col-md-6 col-lg-6">
                            <div class="tutorial-pic">
                              <img style="border:1px solid #f0f0f0" src="/theme/malio/img/tutorial/blank-blue.jpg">
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      {include file='user/footer.tpl'}
    </div>
  </div>

  {include file='user/scripts.tpl'}

  <script>
    function importSublink(client) {
      if (client == 'clash') {
        oneclickImport('clash', '{$subInfo["clash"]}{if $malio_config["enable_sub_extend"] == true}&extend=1{/if}')
      };
    }
    appName = "{$config['appName']}";
  </script>
</body>

</html>