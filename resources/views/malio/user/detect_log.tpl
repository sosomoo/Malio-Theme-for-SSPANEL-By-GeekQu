<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>审计记录 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>审计记录</h1>
          </div>
          <div class="section-body">
            <h2 class="section-title">说明</h2>
            <p class="section-lead">
              系统中所有审计记录。<br>
              关于隐私：注意，我们仅用以下规则进行实时匹配和记录匹配到的规则，您的通信方向和通信内容我们不会做任何记录，请您放心。也请您理解我们对于这些不当行为的管理，谢谢
            </p>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>规则</h4>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <tr>
                          <th>ID</th>
                          <th>名称</th>
                          <th>描述</th>
                          <th>正则表达式</th>
                          <th>类型</th>
                        </tr>
                        {foreach $logs as $log}
                        {if $log->DetectRule() != null}
                        <tr>
                          <td>#{$log->id}</td>
                          <td>{$log->node_id}</td>
                          <td>{$log->Node()->name}</td>
                          <td>{$log->list_id}</td>
                          <td>{$log->DetectRule()->name}</td>
                          <td>{$log->DetectRule()->text}</td>
                          <td>{$log->DetectRule()->regex}</td>
                          {if $log->DetectRule()->type == 1}
                          <td>数据包明文匹配</td>
                          {/if}
                          {if $log->DetectRule()->type == 2}
                          <td>数据包 hex 匹配</td>
                          {/if}
                          <td>{date('Y-m-d H:i:s',$log->datetime)}</td>
                        </tr>
                        {/if}
                        {/foreach}
                      </table>
                    </div>
                    {if $rules != null}
                    <div class="pagination-render float-right">
                      {$rules->render()}
                    </div>
                    {/if}
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