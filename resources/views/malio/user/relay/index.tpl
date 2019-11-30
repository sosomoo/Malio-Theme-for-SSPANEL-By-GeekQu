<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>中转规则 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>中转规则</h1>
            <div class="section-header-breadcrumb">
              <a href="/user/relay/create" class="btn btn-primary btn-icon icon-left"><i class="fas fa-plus"></i> 添加规则</a>
            </div>
          </div>
          <div class="section-body">
            <h2 class="section-title">说明</h2>
            <p class="section-lead">
              中转规则一般由中国中转至其他国外节点<br>
              请设置端口号为您自己的端口<br>
              优先级越大，代表其在多个符合条件的规则并存时会被优先采用，当优先级一致时，先添加的规则会被采用<br>
              节点不设置中转时，这个节点就可以当作一个普通的节点来做代理使用<br>
            </p>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>所有规则</h4>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12 col-sm-12 col-md-2">
                        <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                          <li class="nav-item">
                            <a class="nav-link active show" id="home-tab4" data-toggle="tab" href="#home4" role="tab" aria-controls="home" aria-selected="true">规则表</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" id="profile-tab4" data-toggle="tab" href="#profile4" role="tab" aria-controls="profile" aria-selected="false">链路表</a>
                          </li>
                        </ul>
                      </div>
                      <div class="col-12 col-sm-12 col-md-10">
                        <div class="tab-content no-padding" id="myTab2Content">
                          <div class="tab-pane fade active show" id="home4" role="tabpanel" aria-labelledby="home-tab4">
                            <div class="table-responsive">
                              <table class="table table-striped">
                                <tr>
                                  <th>起源节点</th>
                                  <th>目标节点</th>
                                  <th>端口</th>
                                  <th>优先级</th>
                                  <th>操作</th>
                                </tr>
                                {if count($rules) == 0}
                                <tr>
                                  <td colspan="5"><strong>无中转规则</strong></td>
                                </tr>
                                {else}
                                {foreach $rules as $rule}
                                <tr>
                                  {if $rule->source_node_id == 0}
                                  <td>所有节点</td>
                                  {else}
                                  <td>{$rule->Source_Node()->name}</td>
                                  {/if}
                                  {if $rule->Dist_Node() == null}
                                  <td>不进行中转</td>
                                  {else}
                                  <td>{$rule->Dist_Node()->name}</td>
                                  {/if}
                                  <td>{if $rule->port == 0}所有端口{else}{$rule->port}{/if}</td>
                                  <td>{$rule->priority}</td>
                                  <td>
                                    {if $rule->user_id != 0}
                                    <a href="/user/relay/{$rule->id}/edit" class="btn btn-primary">编辑</a>
                                    {/if}
                                    <a href="##" onclick="deleteRelayRule('{$rule->id}')" class="btn btn-secondary {if $rule->user_id != 0}ml-1{/if}">删除</a>
                                  </td>
                                </tr>
                                {/foreach}
                                {/if}
                              </table>
                              {$rules->render()}
                            </div>
                          </div>
                          <div class="tab-pane fade" id="profile4" role="tabpanel" aria-labelledby="profile-tab4">
                            <div class="table-responsive">
                              <table class="table table-striped">
                                <tr>
                                  <th>端口</th>
                                  <th>始发节点</th>
                                  <th>终点节点</th>
                                  <th>途径节点</th>
                                  <th>状态</th>
                                </tr>
                                {if count($pathset) == 0}
                                <tr>
                                  <td colspan="5"><strong>无链路信息</strong></td>
                                </tr>
                                {else}
                                {foreach $pathset as $path}
                                <tr>
                                  <td>{$path->port}</td>
                                  <td>{$path->begin_node->name}</td>
                                  <td>{$path->end_node->name}</td>
                                  <td>{$path->path}</td>
                                  <td>
                                    <div class="badge badge-{if $path->status == '通畅'}success{else}danger{/if}">{$path->status}</div>
                                  </td>
                                </tr>
                                {/foreach}
                                {/if}
                              </table>
                              {$rules->render()}
                            </div>
                          </div>
                        </div>
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