<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>登录 &mdash; {$config["appName"]}</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.2/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="/theme/malio/assets/css/style.css">
  <link rel="stylesheet" href="/theme/malio/assets/css/components.css">
</head>

<body>
  {if $malio_config['login_style'] == 'simple'}
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand">
              <img src="/theme/malio/assets/img/stisla-fill.svg" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            <div class="card card-primary">
              <div class="card-header">
                <h4>登录</h4>
              </div>
              <form action="javascript:void(0);" method="POST" class="needs-validation" novalidate="">

                <div class="card-body">
                  <div class="form-group">
                    <label for="email">邮箱</label>
                    <input id="email" type="email" class="form-control" name="email" tabindex="1" required autofocus>
                    <div class="invalid-feedback">
                      请填写邮箱
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="d-block">
                      <label for="password" class="control-label">密码</label>
                      <div class="float-right">
                        <a href="/password/reset" class="text-small">
                          忘记密码？
                        </a>
                      </div>
                    </div>
                    <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
                    <div class="invalid-feedback">
                      请填写密码
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me">
                      <label class="custom-control-label" for="remember-me">记住我</label>
                    </div>
                  </div>

                  <div class="form-group">
                    <button type="submit" id="login" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      芝麻开门
                    </button>
                  </div>
              </form>
              {if $malio_config['enable_telegram'] == true}
              <div class="text-center mt-4 mb-3">
                <div class="text-job text-muted">或者</div>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-info btn-lg btn-block" tabindex="4" style="box-shadow:none;">
                  <i class="fab fa-telegram-plane"></i> 使用 Telegram 登录
                </button>
              </div>
              {/if}

            </div>
          </div>
          <div class="mt-5 text-muted text-center">
            还没有账号？ <a href="/auth/register">马上注册 👉</a>
          </div>
          <div class="simple-footer">
            Copyright &copy; 2019 {$config["appName"]}
            <div class="mt-2">
              Powered by <a href="/staff">SSPANEL</a>
              <div class="bullet"></div>
              Theme by <a href="https://t.me/editXY" target="blank">editXY</a>
            </div>
          </div>
        </div>
      </div>
  </div>
  </section>
  </div>
  {/if}

  {if $malio_config['login_style'] == 'wallpaper'}
  <div id="app">
    <section class="section">
      <div class="d-flex flex-wrap align-items-stretch">
        <div class="col-lg-4 col-md-6 col-12 order-lg-1 min-vh-100 order-2 bg-white">
          <div class="p-4 m-3">
            <img src="/theme/malio/assets/img/stisla-fill.svg" alt="logo" width="80" class="shadow-light rounded-circle mb-5 mt-2">
            <h4 class="text-dark font-weight-normal">欢迎使用 <span class="font-weight-bold">{$config["appName"]}</span></h4>
            <p class="text-muted">{$malio_config['login_slogan']}</p>
            <form action="javascript:void(0);" method="POST" class="needs-validation" novalidate="">
              <div class="form-group">
                <label for="email">邮箱</label>
                <input id="email" type="email" class="form-control" name="email" tabindex="1" required autofocus>
                <div class="invalid-feedback">
                  请填写邮箱
                </div>
              </div>

              <div class="form-group">
                <div class="d-block">
                  <label for="password" class="control-label">密码</label>
                  {if $malio_config['enable_telegram'] == true}
                  <div class="float-right">
                    <a href="/password/reset" class="text-small">
                      忘记密码？
                    </a>
                  </div>
                  {/if}
                </div>
                <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
                <div class="invalid-feedback">
                  请填写密码
                </div>
              </div>

              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me">
                  <label class="custom-control-label" for="remember-me">记住我</label>
                </div>
              </div>

              <div class="form-group text-right">
                {if $malio_config['enable_telegram'] == false}
                <a href="/password/reset" class="float-left mt-3">
                  忘记密码？
                </a>
                {/if}
                {if $malio_config['enable_telegram'] == true}
                <a href="##" class="float-left mt-3">
                  Telegram 登录
                </a>
                {/if}
                <button id="login" type="submit" class="btn btn-primary btn-lg btn-icon icon-right" tabindex="4">
                  芝麻开门
                </button>
              </div>

              <div class="mt-5 text-center">
                还没有账号？ <a href="/auth/register">马上注册 👉</a>
              </div>
            </form>

            <div class="text-center mt-5 text-small">
              Copyright &copy; 2019 {$config["appName"]}
              <div class="mt-2">
                Powered by <a href="/staff">SSPANEL</a>
                <div class="bullet"></div>
                Theme by <a href="https://t.me/editXY" target="blank">editXY</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-8 col-12 order-lg-2 order-1 min-vh-100 background-walk-y position-relative overlay-gradient-bottom" data-background="/theme/malio/assets/img/unsplash/login-bg.jpg">
          <div class="absolute-bottom-left index-2">
            <div class="text-light p-5 pb-2">
              <div class="mb-5 pb-3">
                <h1 class="mb-2 display-4 font-weight-bold">Good Mornig</h1>
                <h5 class="font-weight-normal text-muted-transparent">Bali, Indonesia</h5>
              </div>
              Photo by <a class="text-light" target="_blank" href="https://unsplash.com/photos/a8lTjWJJgLA">Justin Kauffman</a> on <a class="text-light" target="_blank" href="https://unsplash.com">Unsplash</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  {/if}

  <!-- General JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tooltip.js@1.3.2/dist/umd/tooltip.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.18.1/min/moment.min.js"></script>
  <script src="/theme/malio/assets/js/stisla.js"></script>

  <!-- JS Libraies -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.25.6/dist/sweetalert2.all.min.js"></script>

  <!-- Page Specific JS File -->

  <!-- Template JS File -->
  <script src="/theme/malio/assets/js/scripts.js"></script>

  <script>
    function login() {
      if (!$("#password").val() || !$("#email").val()) {
        return false;
      }
      $.ajax({
        type: "POST",
        url: "/auth/login",
        dataType: "json",
        data: {
          email: $("#email").val(),
          passwd: $("#password").val(),
          code: $("#code").val(),
          remember_me: $("#remember-me:checked").val()
        },
        success: function (data) {
          if (data.ret == 1) {
            window.location.assign('/user')
          } else {
            swal('出错了', '密码或邮箱不正确', 'error');
          }
        }
      });
    }
    $("html").keydown(function (event) {
      if (event.keyCode == 13) {
        login();
      }
    });
    $("#login").click(function () {
      login();
    });
  </script>

</body>

</html>