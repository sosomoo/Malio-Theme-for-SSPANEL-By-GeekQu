<!-- General JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tooltip.js@1.3.2/dist/umd/tooltip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.18.1/min/moment.min.js"></script>

<!-- JS Libraies -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.25.6/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bowser@1.9.4/bowser.min.js"></script>

<script src="/theme/malio/js/malio.js?{$malio_config['malio_js_version']}"></script>

<script>
  var isWeixin = function () {
    var ua = navigator.userAgent.toLowerCase();
    return ua.match(/MicroMessenger/i) == "micromessenger";
  };
  if (isWeixin() == true) {
    $('body').html('<h5 style="margin:20px">不支持在微信内访问<br>请点击右上角菜单<br>选择浏览器打开</h5>')
  }
  function joinTelegramGroup() {
    {if $malio_config['force_user_to_bind_tg_when_join_group'] == true && $user->telegram_id == 0}
    Swal.fire({
      title: '提示',
      html: '加入群组之前需要在 <a href="/user/profile">我的账号</a> 页面绑定 Telegram 账号，未绑定则无法加入群组。',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: '确定加入',
      cancelButtonText: '取消'
    }).then((result) => {
      if (result.value) {
        window.open("{$malio_config['telegram_group']}")
      }
    })
    {else}
    window.open("{$malio_config['telegram_group']}")
    {/if}
  }
</script>

{if $malio_config['enable_crisp'] == true}
<script>
  function sendData() {
    $crisp.push(["set", "user:email", "{$user->email}"], ["set", "user:nickname", "{$user->user_name}"]);
    $crisp.push(["set", "session:data", [[
      ["Class", "{$user->class}"],
      ["Class Expire", "{$user->class_expire}"],
      ["Money", "￥{$user->money}"],
      ["Unused Traffic", "{$user->unusedTraffic()}"]
    ]]]);
  }
  sendData();
</script>
{/if}

{if $malio_config['enable_chatra'] == true}
<script>
  window.ChatraIntegration = {
    name: '{$user->user_name}',
    email: '{$user->email}',
    '等级': '{$user->class}',
    '等级过期': '{$user->class_expire}',
    '账号余额': '￥{$user->money}',
    '剩余流量': '{$user->unusedTraffic()}'
  };
  var userUUID = '{$user->uuid}';
</script>
{/if}

<script>
  var code = "UG93ZXJlZCBieSA8YSBocmVmPSIvc3RhZmYiPlNTUEFORUw8L2E+IDxkaXYgY2xhc3M9ImJ1bGxldCI+PC9kaXY+IFRoZW1lIGJ5IDxhIGhyZWY9Imh0dHBzOi8vdC5tZS9lZGl0WFkiIHRhcmdldD0iYmxhbmsiPmVkaXRYWTwvYT4=";
  $('#copyright').html(atob(code));
</script>