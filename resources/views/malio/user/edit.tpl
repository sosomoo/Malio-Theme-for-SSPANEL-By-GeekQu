<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>连接设置 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>连接设置</h1>
          </div>
          <div class="section-body">
            <h2 class="section-title">您可以在这里修改节点连接设置</h2>
            <p class="section-lead">
              修改设置后您可能需要在客户端更新节点列表才能恢复使用
            </p>

            <div id="output-status"></div>
            <div class="row">
              <div class="col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h4>Jump to</h4>
                  </div>
                  <div class="card-body">
                    <ul class="nav nav-pills flex-column" id="settings" role="tablist">
                      <li class="nav-item"><a class="nav-link active" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="true">连接密码</a></li>
                      {if $malio_config['enable_method'] == true}<li class="nav-item"><a class="nav-link" id="method-tab" data-toggle="tab" href="#method" role="tab" aria-controls="method" aria-selected="false">加密方式</a></li>{/if}
                      {if $malio_config['enable_protocol'] == true}<li class="nav-item"><a class="nav-link" id="protocol-tab" data-toggle="tab" href="#protocol" role="tab" aria-controls="protocol" aria-selected="false">协议和混淆</a></li>{/if}
                      <li class="nav-item"><a class="nav-link" id="resetlink-tab" data-toggle="tab" href="#resetlink" role="tab" aria-controls="resetlink" aria-selected="false">重置订阅链接</a></li>
                      {if $malio_config['enable_reset_port'] == true && $config['port_price'] > 0}<li class="nav-item"><a class="nav-link" id="resetport-tab" data-toggle="tab" href="#resetport" role="tab" aria-controls="resetport" aria-selected="false">重置端口</a></li>{/if}
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="tab-content no-padding" id="settings2Content">
                  <div class="tab-pane fade active show" id="password" role="tabpanel" aria-labelledby="password-tab">
                    <div class="card">
                      <div class="card-header">
                        <h4>连接密码</h4>
                      </div>
                      <div class="card-body">
                        <p>您需要了解的是，修改此密码同时也会变更您 V2Ray 节点的 UUID，请注意及时更新托管订阅。<br>当前连接密码: <code id="ss-current-password">{$user->passwd}</code></p>
                        <div class="form-group">
                          <div class="input-group mb-3">
                            <input id="ss-password" type="text" class="form-control" placeholder="请输入新密码" aria-label="">
                            <div class="input-group-append">
                              <button id="ss-random-password" class="btn btn-warning" type="button">随机生成密码</button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="ss-password-confirm" class="btn btn-primary">提交更改</button>
                      </div>
                    </div>
                  </div>
                  {if $malio_config['enable_method'] == true}
                  <div class="tab-pane fade" id="method" role="tabpanel" aria-labelledby="method-tab">
                    <div class="card">
                      <div class="card-header">
                        <h4>加密方式</h4>
                      </div>
                      <div class="card-body">
                        <p>SS/SSD/SSR 支持的加密方式有所不同，请根据实际情况来进行选择<br>
                          当前加密方式: <code id="current-method">[{if URL::CanMethodConnect($user->method) == 2}SS/SSD{else}SS/SSR{/if} 可连接] {$user->method}</code>
                        </p>
                        <div class="form-group">
                          <select id="method-select" class="form-control">
                            {$method_list = $config_service->getSupportParam('method')}
                            {foreach $method_list as $method}
                            <option value="{$method}">[{if URL::CanMethodConnect($method) == 2}SS/SSD{else}SS/SSR{/if} 可连接] {$method}</option>
                            {/foreach}
                          </select>
                        </div>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="method-confirm" class="btn btn-primary">提交更改</button>
                      </div>
                    </div>
                  </div>
                  {/if}
                  {if $malio_config['enable_protocol'] == true}
                  <div class="tab-pane fade" id="protocol" role="tabpanel" aria-labelledby="protocol-tab">
                    <div class="card">
                      <div class="card-header">
                        <h4>协议和混淆</h4>
                      </div>
                      <div class="card-body">
                        <p>如果需要兼容 SS/SSD 请设置为 origin 或选择带_compatible的兼容选项<br>
                          auth_chain 系为实验性协议，可能造成不稳定或无法使用，开启前请询问是否支持<br>
                          当前协议: <code id="current-protocol">[{if URL::CanProtocolConnect($user->protocol) == 3}SS/SSD/SSR{else}SSR{/if} 可连接] {$user->protocol}</code>
                        </p>
                        <div class="form-group">
                          <select id="protocol-selection" class="form-control">
                            {$protocol_list = $config_service->getSupportParam('protocol')}
                            {foreach $protocol_list as $protocol}
                            <option value="{$protocol}">[{if URL::CanProtocolConnect($protocol) == 3}SS/SSD/SSR{else}SSR{/if} 可连接] {$protocol}</option>
                            {/foreach}
                          </select>
                        </div>
                      </div>
                      <hr>
                      <div class="card-body">
                        <p>如果需要兼容 SS/SSD 请设置为 plain 或选择带_compatible的兼容选项<br>
                          SS/SSD 和 SSR 支持的混淆类型有所不同，simple_obfs_* 为 SS/SSD 的混淆方式，其他为 SSR 的混淆方式<br>
                          如果使用 SS/SSD 作为客户端，请确保自己知道如何下载并使用混淆插件<br>
                          当前混淆方式: <code id="current-obfs">[{if URL::CanObfsConnect($user->obfs) >= 3}SS/SSD/SSR{elseif URL::CanObfsConnect($user->obfs) == 1}SSR{else}SS/SSD{/if} 可连接] {$user->obfs}</code>
                        </p>
                        <div class="form-group">
                          <select id="obfs" class="form-control">
                            {$obfs_list = $config_service->getSupportParam('obfs')}
                            {foreach $obfs_list as $obfs}
                            <option value="{$obfs}">[{if URL::CanObfsConnect($obfs) >= 3}SS/SSD/SSR{else}{if URL::CanObfsConnect($obfs) == 1}SSR{else}SS/SSD{/if}{/if} 可连接] {$obfs}</option>
                            {/foreach}
                          </select>
                        </div>
                      </div>
                      <hr>
                      <div class="card-body">
                        <p>当前当前混淆参数: <code id="current-obfs-param">{$user->obfs_param}</code>
                        </p>
                        <div class="form-group">
                          <input id="obfs-param" type="text" class="form-control" placeholder="请输入混淆参数" aria-label="">
                        </div>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="protocol-obfs-confirm" class="btn btn-primary">提交更改</button>
                      </div>
                    </div>
                  </div>
                  {/if}
                  <div class="tab-pane fade" id="resetlink" role="tabpanel" aria-labelledby="resetlink-tab">
                    <div class="card">
                      <div class="card-header">
                        <h4>重置订阅链接</h4>
                      </div>
                      <div class="card-body">
                        <p>点击会重置您的订阅链接，此操作不可逆，请谨慎操作。</p>
                        当前订阅链接: <code>{$config['baseUrl']}/{$ssr_sub_token}</code>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="reset-sub-link" class="btn btn-danger">重置订阅链接</button>
                      </div>
                    </div>
                  </div>
                  {if $malio_config['enable_reset_port'] == true && $config['port_price'] > 0}
                  <div class="tab-pane fade" id="resetport" role="tabpanel" aria-labelledby="resetport-tab">
                    <div class="card">
                      <div class="card-header">
                        <h4>重置端口</h4>
                      </div>
                      <div class="card-body">
                        <p>随机更换一个端口使用，价格：<code>{$config['port_price']}</code>元/次</p>
                        <p>重置后1分钟内生效</p>
                        <p>当前端口：<code id="current-port">{$user->port}</code></p>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="reset-port-confirm" class="btn btn-primary">重置端口</button>
                      </div>
                    </div>
                    {if $config['port_price_specify']>=0}
                    <div class="card">
                      <div class="card-header">
                        <h4>钦定端口</h4>
                      </div>
                      <div class="card-body">
                        <p>价格：<code>{$config['port_price_specify']}</code>元/次</p>
                        <p>端口范围：<code>{$config['min_port']}～{$config['max_port']}</code></p>
                        <p>当前端口：<code id="current-port-2">{$user->port}</code></p>

                        <div class="form-group">
                          <input id="port-specify" type="text" class="form-control" placeholder="请输入想钦定的端口号" aria-label="">
                        </div>
                      </div>
                      <div class="card-footer bg-whitesmoke text-md-right">
                        <button id="portspecify" class="btn btn-primary">提交更改</button>
                      </div>
                    </div>
                    {/if}
                  </div>
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