<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>支付结果 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>支付结果</h1>
          </div>
          <div class="section-body">
            <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    {if ($success == 1)}
                        <h5>已充值成功 {$money} 元</h5>
                    {else}
                        <h5>正在处理您的支付，请您稍等。此页面会自动刷新，或者您可以选择关闭此页面，余额将自动到账</h5>
                        <script>
                            setTimeout('window.location.reload()', 5000);
                        </script>
                    {/if}
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

</body>

</html>