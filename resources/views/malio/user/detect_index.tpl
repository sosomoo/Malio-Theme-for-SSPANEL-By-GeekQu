<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>审计规则 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>审计规则</h1>
          </div>
          <div class="section-body">
            <h2 class="section-title">说明</h2>
            <p class="section-lead">
              为了爱与和平，也同时为了系统的正常运行，特制定了如下过滤规则，当您使用节点执行这些动作时，您的通信就会被截断。<br>
              关于隐私：注意，我们仅用以下规则进行实时匹配和记录匹配到的规则，您的通信方向和通信内容我们不会做任何记录，请您放心。也请您理解我们对于这些不当行为的管理，谢谢
            </p>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>规则</h4>
                  </div>
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>名称</th>
                          <th>描述</th>
                          <th>正则表达式</th>
                          <th>类型</th>
                        </tr>
                      </thead>
                      <tbody>
                        {foreach $rules as $rule}
                        <tr>
                          <td>#{$rule->id}</td>
                          <td>{$rule->name}</td>
                          <td>{$rule->text}</td>
                          <td>{$rule->regex}</td>
                          {if $rule->type == 1}
                          <td>数据包明文匹配</td>
                          {/if}
                          {if $rule->type == 2}
                          <td>数据包 hex 匹配</td>
                          {/if}
                        </tr>
                        {/foreach}
                      </tbody>
                    </table>
                    <div class="pagination-render float-right">
                      {$rules->render()}
                    </div>
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

</body>

</html>