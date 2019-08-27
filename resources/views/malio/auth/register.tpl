<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>注册 &mdash; {$config["appName"]}</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.2/css/all.min.css">
  
  <!-- CSS Libraries -->
  <link rel="stylesheet" href="/theme/malio/assets/modules/selectric/public/selectric.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="/theme/malio/assets/css/style.css">
  <link rel="stylesheet" href="/theme/malio/assets/css/components.css">

  {if $malio_config['enable_crisp'] == true && $malio_config['enable_crisp_outside'] == true}
  {include file='crisp.tpl'}
  {/if}
</head>

<body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
            <div class="login-brand">
              <img src="/theme/malio/assets/img/stisla-fill.svg" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            <div class="card card-primary">
              <div class="card-header">
                <h4>注册</h4>
              </div>

              <div class="card-body">
                {if $config['register_mode'] == 'close'}
                <p>{$config["appName"]} 已停止新用户注册</p>
                {else}
                <form action="javascript:void(0);" method="POST" class="needs-validation" novalidate="">
                  <div class="row">
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="name">昵称</label>
                      <input id="name" type="text" class="form-control" name="name" required autofocus>
                      <div class="invalid-feedback">
                        请填写昵称
                      </div>
                    </div>
                    {if $enable_email_verify == 'false'}
                      {if $malio_config['enable_register_email_restrict'] == true}
                      <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                        <label for="email">邮箱</label>
                        <div class="input-group">
                          <input type="text" id="email" class="form-control col-7" required>
                          <select class="custom-select input-group-append col-5" id="email_postfix" required style="border-top-right-radius: .25rem;
                          border-bottom-right-radius: .25rem;">
                            {$email_first = true}
                            {foreach $malio_config['register_email_white_list'] as $email}
                            {if $email_first == true}
                            <option value="{$email}" selected="">{$email}</option>
                            {$email_first = false}
                            {else}
                            <option value="{$email}">{$email}</option>
                            {/if}
                            {/foreach}
                          </select>
                          <div class="invalid-feedback">
                              请填写邮箱
                          </div>
                        </div>
                      </div>
                      {else}
                      <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                        <label for="email">邮箱</label>
                        <input id="email" type="email" class="form-control" name="email" required>
                        <div class="invalid-feedback">
                          请填写邮箱
                        </div>
                      </div>
                      {/if}
                    {/if}

                    {if $enable_email_verify == 'true' && $config['register_mode'] == 'invite'}
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="code" class="d-block">邀请码 {if $malio_config['code_required'] == false}(选填){/if}</label>
                      <input id="code" type="text" class="form-control" name="code" {if $malio_config['code_required'] == true}required{/if}>
                      {if $malio_config['code_required'] == true}
                      <div class="invalid-feedback">
                        请填写邀请码
                      </div>
                      {/if}
                    </div>
                    {/if}
                  </div>

                  {if $enable_email_verify == 'true'}
                  <div class="row">
                    {if $malio_config['enable_register_email_restrict'] == true}
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="email">邮箱</label>
                      <div class="input-group">
                        <input type="text" id="email" class="form-control col-7" required>
                        <select class="custom-select input-group-append col-5" id="email_postfix" required style="border-top-right-radius: .25rem;
                          border-bottom-right-radius: .25rem;">
                          {$email_first = true}
                          {foreach $malio_config['register_email_white_list'] as $email}
                          {if $email_first == true}
                          <option value="{$email}" selected="">{$email}</option>
                          {$email_first = false}
                          {else}
                          <option value="{$email}">{$email}</option>
                          {/if}
                          {/foreach}
                        </select>
                        <div class="invalid-feedback">
                          请填写邮箱
                        </div>
                      </div>
                    </div>
                    {else}
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="email">邮箱</label>
                      <input id="email" type="email" class="form-control" name="email" required>
                      <div class="invalid-feedback">
                        请填写邮箱
                      </div>
                    </div>
                    {/if}
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="email">邮箱验证码</label>
                      <div class="input-group mb-3">
                        <input id="email_code" type="text" class="form-control" name="email" required>
                        <div class="input-group-append">
                          <button id="email_verify" class="btn btn-primary" type="button">获取验证码</button>
                        </div>
                      </div>
                      <div class="invalid-feedback">
                        请填写邮箱验证码
                      </div>
                    </div>
                  </div>
                  {/if}

                  <div class="row">
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="passwd" class="d-block">密码</label>
                      <input id="passwd" type="password" class="form-control pwstrength" data-indicator="pwindicator" name="passwd" required>
                      <div id="pwindicator" class="pwindicator">
                        <div class="bar"></div>
                        <div class="label"></div>
                      </div>
                      <div class="invalid-feedback">
                        请填写密码
                      </div>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="repasswd" class="d-block">重复密码</label>
                      <input id="repasswd" type="password" class="form-control" name="repasswd" required>
                      <div class="invalid-feedback">
                        请再次填写密码
                      </div>
                    </div>
                  </div>

                  {if $config['register_mode'] == 'invite' && $enable_email_verify == 'false'}
                  <div class="row">
                    <div class="form-group col-lg-6 col-sm-12 col-xs-12">
                      <label for="code" class="d-block">邀请码 {if $malio_config['code_required'] == false}(选填){/if}</label>
                      <input id="code" type="text" class="form-control" name="code" {if $malio_config['code_required'] == true}required{/if}>
                      {if $malio_config['code_required'] == true}
                      <div class="invalid-feedback">
                        请填写邀请码
                      </div>
                      {/if}
                    </div>
                  </div>
                  {/if}

                  {if $geetest_html != null}
                      <div class="rowtocol">
                          <div class="form-group form-group-label">
                              <div id="embed-captcha"></div>
                          </div>
                      </div>
                  {/if}

                  {if $recaptcha_sitekey != null}
                    <div class="form-group">
                      <div class="g-recaptcha" data-sitekey="{$recaptcha_sitekey}"></div>
                    </div>
                  {/if}

                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" name="agree" class="custom-control-input" id="agree" checked="checked">
                      <label class="custom-control-label" for="agree">注册即代表同意 <a href="/tos" target="blank">服务条款</a></label>
                    </div>
                  </div>

                  <div class="form-group">
                    <button id="register-confirm" type="submit" class="btn btn-primary btn-lg btn-block">
                      注册
                    </button>
                  </div>
                </form>
                {/if}
              </div>
            </div>
            <div class="mt-5 text-muted text-center">
                已经有账号了？ <a href="/auth/login">马上登录 👉</a>
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

  <!-- General JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tooltip.js@1.3.2/dist/umd/tooltip.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.18.1/min/moment.min.js"></script>

  <!-- JS Libraies -->
  <script src="/theme/malio/assets/modules/jquery-pwstrength/jquery.pwstrength.min.js"></script>
  <script src="/theme/malio/assets/modules/selectric/public/jquery.selectric.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.25.6/dist/sweetalert2.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="/theme/malio/js/malio.js?{$malio_config['malio_js_version']}"></script>

  <script>

  $(".pwstrength").pwstrength({
    texts: ['超级弱鸡', '弱鸡', '一般般', '有点强', '很强👌'] 
  });

    function login(email,passwd) {
      $.ajax({
          type:"POST",
          url:"/auth/login",
          dataType:"json",
          data:{
              email: email,
              passwd: passwd,
              code: '',
              remember_me: 1
          },
          success: function (data) {
            window.location.assign('/user')
          }
      });
    }

    function register() {
      // vaildation
      if (
        !$("#name").val() ||
        !$("#email").val() ||
        !$("#passwd").val() ||
        !$("#repasswd").val()
        ){
        $('#register-confirm').removeAttr("disabled","disabled")
        return false
      };
      // eof vaildation
      
      {if $geetest_html != null}
      validate = captcha.getValidate();
      if (typeof validate === 'undefined' || !validate) {
        swal('请验证身份', '请滑动验证码来完成验证。', 'info');
        $('#register-confirm').removeAttr("disabled","disabled")
        return;
      }
      {/if}

      code = $("#code").val();
      {if $config['register_mode'] != 'invite'}
      code = 0;
      if ((getCookie('code')) != '') {
        code = getCookie('code');
      }
      {/if}

      {if $malio_config['enable_register_email_restrict'] == true}
      var email = $("#email").val()+$("#email_postfix").val();
      {else}
      var email = $("#email").val();
      {/if}

      $.ajax({
          type: "POST",
          url: "/auth/register",
          dataType: "json",
          data: {
              email: email,
              name: $("#name").val(),
              passwd: $("#passwd").val(),
              repasswd: $("#repasswd").val(),
              {if $recaptcha_sitekey != null}
              recaptcha: grecaptcha.getResponse(),
              {/if}
              code: code{if $enable_email_verify == 'true'},
              emailcode: $("#email_code").val(){/if}{if $geetest_html != null},
              geetest_challenge: validate.geetest_challenge,
              geetest_validate: validate.geetest_validate,
              geetest_seccode: validate.geetest_seccode
              {/if}
          },
          success: function (data) {
              if (data.ret == 1) {
                swal({
                  type: 'success',
                  title: '注册成功',
                  showCloseButton: true,
                  onClose: () => {
                    login($("#email").val(), $("#passwd").val());
                  }
                })
              } else {
                $('#register-confirm').removeAttr("disabled")
                {if $geetest_html != null}
                captcha.reset();
                {/if}
                $("#code").val(code);
                swal({
                  type: 'error',
                  title: '提示',
                  showCloseButton: true,
                  text: data.msg
                })
              }
          }
      });
    }

    $("html").keydown(function (event) {
        if (event.keyCode == 13) {
          register()
        }
    });

    $('#register-confirm').click(function(){
      $('#register-confirm').attr("disabled","disabled")
      register()
    })
  </script>

  {if $enable_email_verify == 'true'}
  <script>
    var wait = 60;

    function time(o) {
      if (wait == 0) {
        o.removeAttr("disabled");
        o.text("获取验证码");
        wait = 60;
      } else {
        o.attr("disabled", "disabled");
        o.text("重新发送(" + wait + ")");
        wait--;
        setTimeout(function () {
            time(o)
          },
          1000)
      }
    }

    $("#email_verify").click(function () {
      time($("#email_verify"));

      {if $malio_config['enable_register_email_restrict'] == true}
      var email = $("#email").val()+$("#email_postfix").val();
      {else}
      var email = $("#email").val();
      {/if}

      $.ajax({
        type: "POST",
        url: "send",
        dataType: "json",
        data: {
          email: email
        },
        success: function (data) {
          if (data.ret) {
            swal({
              type: 'success',
              title: '已发送验证码',
              showCloseButton: true,
              text: '如长时间未收到，请查看邮件垃圾箱'
            })
          } else {
            swal({
              type: 'error',
              title: '发送验证码失败',
              showCloseButton: true,
              text: data.msg
            })
          }
        }
      })
    })
  </script>
  {/if}

  {if $geetest_html != null}
  <script src="//static.geetest.com/static/tools/gt.js"></script>
  <script>
    var handlerEmbed = function (captchaObj) {
      captchaObj.onSuccess(function () {
          validate = captchaObj.getValidate();
      });
      captchaObj.appendTo("#embed-captcha");
      captcha = captchaObj;
    };
    initGeetest({
      gt: "{$geetest_html->gt}",
      challenge: "{$geetest_html->challenge}",
      product: "embed",
      width: "100%",
      offline: {if $geetest_html->success}0{else}1{/if}
    }, handlerEmbed);
  </script>
  {/if}

  {if $recaptcha_sitekey != null}
    <script src="https://recaptcha.net/recaptcha/api.js" async defer></script>
  {/if}

<script>
    {*dumplin：轮子1.js读取url参数*}
    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return "";
    }

    {*dumplin:轮子2.js写入cookie*}
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }

    {*dumplin:轮子3.js读取cookie*}
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i].trim();
            if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
        }
        return "";
    }

    {*dumplin:读取url参数写入cookie，自动跳转隐藏url邀请码*}
    if (getQueryVariable('code') != '') {
        setCookie('code', getQueryVariable('code'), 30);
        window.location.href = '/auth/register';
    }

    {if $config['register_mode'] == 'invite'}
    {*dumplin:读取cookie，自动填入邀请码框*}
    if ((getCookie('code')) != '') {
        $("#code").val(getCookie('code'));
    }
    {/if}


</script>
</body>

</html>
