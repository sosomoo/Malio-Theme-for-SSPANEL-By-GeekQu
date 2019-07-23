<meta charset="UTF-8">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

<!-- General CSS Files -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.2/css/all.min.css">

<!-- Template CSS -->
<link rel="stylesheet" href="/theme/malio/css/style-{$malio_config['theme_color']}.css">
<link rel="stylesheet" href="/theme/malio/assets/css/components.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="/theme/malio/css/malio.css">

{if $malio_config['google_analytics_code'] != ''}
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$malio_config['google_analytics_code']}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  {literal}function gtag() {dataLayer.push(arguments);}{/literal}
  gtag('js', new Date());
  gtag('config', '{$malio_config['google_analytics_code']}');
</script>
{/if}
{if $config["sspanelAnalysis"] == 'true'}
{literal}
<!-- Google Analytics -->
<script>
    window.ga = window.ga || function () {
        (ga.q = ga.q || []).push(arguments)
    };
    ga.l = +new Date;
    ga('create', 'UA-143778338-1', 'auto');
    var hostDomain = window.location.host || document.location.host || document.domain;
    ga('set', 'dimension1', hostDomain);
    ga('send', 'pageview');
    (function () {
        function perfops() {
            var js = document.createElement('script');
            js.src = 'https://cdn.jsdelivr.net/npm/perfops-rom';
            document.body.appendChild(js);
        }
        if (document.readyState === 'complete') {
            perfops();
        } else {
            window.addEventListener('load', perfops);
        }
    })();
</script>
<script async src="https://www.google-analytics.com/analytics.js"></script>
<!-- End Google Analytics -->
{/literal}
{/if}
{if $malio_config['enable_crisp'] == true}
<!-- Crisp Chat  -->
<script type="text/javascript">
  window.$crisp = [];
  window.CRISP_WEBSITE_ID = "{$malio_config['crisp_wesite_id']}";
  (function () {
    d = document;
    s = d.createElement("script");
    s.src = "https://client.crisp.chat/l.js";
    s.async = 1;
    d.getElementsByTagName("head")[0].appendChild(s);
  })();
</script>
<script>
  function sendData() {
    $crisp.push(["set", "user:email", "{$user->email}"], ["set", "user:nickname", "{$user->user_name}"]);
    $crisp.push(["set", "session:data", [[
      ["等级", "{$user->class}"],
      ["等级过期日", "{substr($user->class_expire, 0, 10)}"],
      ["账号余额", "￥{$user->money}"],
      ["剩余流量", "{$user->unusedTraffic()}"]
    ]]]);
  }
  sendData();
</script>
{/if}