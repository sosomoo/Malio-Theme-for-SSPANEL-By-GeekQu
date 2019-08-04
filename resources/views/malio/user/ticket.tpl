<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>工单列表 &mdash; {$config["appName"]}</title>
  <style>
    .table-links a {
      font-weight: normal;
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
            <h1>工单列表</h1>
            <div class="section-header-breadcrumb">
              <a href="/user/ticket/create" class="btn btn-primary btn-icon icon-left"><i class="fas fa-plus"></i> 新建工单</a>
            </div>
          </div>
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>所有工单</h4>
                  </div>
                  <div class="card-body">

                    <div class="table-responsive">
                      <table class="table table-striped">
                        <tbody>
                          <tr>
                            <th>标题</th>
                            <th>创建时间</th>
                            <th>状态</th>
                          </tr>
                          {foreach $tickets as $ticket}
                          <tr>
                            <td>
                              <a href="/user/ticket/{$ticket->id}/view" style="color:#6a757e">{$ticket->title}</a>
                            </td>
                            <td>{$ticket->datetime()}</td>
                            <td>
                              {if $ticket->status==1}
                              <div class="badge badge-success">处理中</div>
                              {else}
                              <div class="badge badge-secondary">已关闭</div>
                              {/if}
                            </td>
                          </tr>
                          {/foreach}
                        </tbody>
                      </table>
                      <div class="pagination-render float-right">
                        {$tickets->render()}
                      </div>
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