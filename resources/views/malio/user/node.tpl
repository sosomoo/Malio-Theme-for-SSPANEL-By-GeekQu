<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>节点列表 &mdash; {$config["appName"]}</title>

  <style>
    dl,
    ol,
    ul {
      margin-bottom: 0;
    }

    .text-job {
      font-weight: normal;
    }

    .media-title {
      font-weight: bold;
    }

    .node-status:before {
      content: ' ';
      border-radius: 5px;
      height: 8px;
      width: 8px;
      display: inline-block;
      margin-top: 6px;
      margin-right: 8px;
    }

    .node-is-online:before {
      background-color: #63ed7a;
    }

    .node-is-offline:before {
      background-color: #fc544b;
    }

    .card-body .rounded-circle {
      box-shadow: 0 2px 6px #e6ecf1;
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
            <h1>节点列表</h1>
          </div>
          <div class="section-body">
            <h2 class="section-title">{$malio_config['plan_1_name']}节点</h2>
            <div class="row">
              {foreach $nodes as $node}
              {if $node['class'] == 1}
              <div class="col-12 col-sm-12 col-lg-6">
                {if $node['sort'] == 11}
                <div class="card"{if $user->class>0} data-toggle="modal" data-target="#node-modal-{$node['id']}"{/if}>
                {else}
                <div class="card" {if $user->class >0}onclick="urlChange('{$node['id']}',0,{if $relay_rule != null}{$relay_rule->id}{else}0{/if})"{/if}>
                {/if}
                  <div class="card-body">
                    <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
                      <li class="media">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="/theme/malio/assets/modules/flag-icon-css/flags/1x1/{substr($node['server'],0,2)}.svg">
                        <div class="media-body">
                          <div class="media-title node-status {if $node['online']=="1"}node-is-online"{elseif $node['online']=='0'}node-is-offline"{else}flash_off{/if}>{current(explode(" - ", $node['name']))}</div>
                          <div class="text-job text-muted">{$node['info']}</div>
                        </div>
                        <div class="media-items">
                          {if $malio_config['enable_online_user'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['online_user'] == -1} N/A{else} {$node['online_user']}{/if}</div>
                            <div class="media-label">在线</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_traffic_rate'] == true}
                          <div class="media-item">
                            <div class="media-value">x{$node['traffic_rate']}</div>
                            <div class="media-label">倍率</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_load'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['latest_load'] == -1}N/A{else}{$node['latest_load']}%{/if}</div>
                            <div class="media-label">负载</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_speedlimit'] == true}
                          <div class="media-item">
                            <div class="media-value">{if {$node['bandwidth']}==0}N/A{else}{$node['bandwidth']}{/if}</div>
                            <div class="media-label">限速</div>
                          </div>
                          {/if}
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              {/if}
              {/foreach}
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">{$malio_config['plan_2_name']}节点</h2>
            <div class="row">
              {foreach $nodes as $node}
              {if $node['class'] == 2}
              <div class="col-12 col-sm-12 col-lg-6">
                {if $node['sort'] == 11}
                <div class="card"{if $user->class>0} data-toggle="modal" data-target="#node-modal-{$node['id']}"{/if}>
                {else}
                <div class="card" {if $user->class >0}onclick="urlChange('{$node['id']}',0,{if $relay_rule != null}{$relay_rule->id}{else}0{/if})"{/if}>
                {/if}
                  <div class="card-body">
                    <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
                      <li class="media">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="/theme/malio/assets/modules/flag-icon-css/flags/1x1/{substr($node['server'],0,2)}.svg">
                        <div class="media-body">
                          <div class="media-title node-status {if $node['online']=="1"}node-is-online"{elseif $node['online']=='0'}node-is-offline"{else}flash_off{/if}>{current(explode(" - ", $node['name']))}</div>
                          <div class="text-job text-muted">{$node['info']}</div>
                        </div>
                        <div class="media-items">
                          {if $malio_config['enable_online_user'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['online_user'] == -1} N/A{else} {$node['online_user']}{/if}</div>
                            <div class="media-label">在线</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_traffic_rate'] == true}
                          <div class="media-item">
                            <div class="media-value">x{$node['traffic_rate']}</div>
                            <div class="media-label">倍率</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_load'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['latest_load'] == -1}N/A{else}{$node['latest_load']}%{/if}</div>
                            <div class="media-label">负载</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_speedlimit'] == true}
                          <div class="media-item">
                            <div class="media-value">{if {$node['bandwidth']}==0}N/A{else}{$node['bandwidth']}{/if}</div>
                            <div class="media-label">限速</div>
                          </div>
                          {/if}
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              {/if}
              {/foreach}
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">{$malio_config['plan_3_name']}节点</h2>
            <div class="row">
              {foreach $nodes as $node}
              {if $node['class'] == 3}
              <div class="col-12 col-sm-12 col-lg-6">
                {if $node['sort'] == 11}
                <div class="card"{if $user->class>0} data-toggle="modal" data-target="#node-modal-{$node['id']}"{/if}>
                {else}
                <div class="card" {if $user->class >0}onclick="urlChange('{$node['id']}',0,{if $relay_rule != null}{$relay_rule->id}{else}0{/if})"{/if}>
                {/if}
                  <div class="card-body">
                    <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
                      <li class="media">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="/theme/malio/assets/modules/flag-icon-css/flags/1x1/{substr($node['server'],0,2)}.svg">
                        <div class="media-body">
                          <div class="media-title node-status {if $node['online']=="1"}node-is-online"{elseif $node['online']=='0'}node-is-offline"{else}flash_off{/if}>{current(explode(" - ", $node['name']))}</div>
                          <div class="text-job text-muted">{$node['info']}</div>
                        </div>
                        <div class="media-items">
                          {if $malio_config['enable_online_user'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['online_user'] == -1} N/A{else} {$node['online_user']}{/if}</div>
                            <div class="media-label">在线</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_traffic_rate'] == true}
                          <div class="media-item">
                            <div class="media-value">x{$node['traffic_rate']}</div>
                            <div class="media-label">倍率</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_load'] == true}
                          <div class="media-item">
                            <div class="media-value">{if $node['latest_load'] == -1}N/A{else}{$node['latest_load']}%{/if}</div>
                            <div class="media-label">负载</div>
                          </div>
                          {/if}
                          {if $malio_config['enable_node_speedlimit'] == true}
                          <div class="media-item">
                            <div class="media-value">{if {$node['bandwidth']}==0}N/A{else}{$node['bandwidth']}{/if}</div>
                            <div class="media-label">限速</div>
                          </div>
                          {/if}
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              {/if}
              {/foreach}
            </div>
          </div>
          
        </section>
      </div>
      {include file='user/footer.tpl'}
    </div>
  </div>

  {foreach $nodes as $node}
  {if $user->class >= $node->class}
  {if $node['sort'] == 11}
  <div class="modal fade" tabindex="-1" role="dialog" id="node-modal-{$node['id']}">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{current(explode(" - ", $node['name']))}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {$v2server=URL::getV2Url($user, $node['raw_node'], 1)}
          <div class="mb-2">地址: <code>{$v2server['add']}</code></div>
          <div class="mb-2">端口: <code>{$v2server['port']}</code></div>
          <div class="mb-2">AlterId: <code>{$v2server['aid']}</code></div>
          <div class="mb-2">用户 UUID: <code>{$user->getUuid()}</code></div>
          <div class="mb-2">传输协议: <code>{if $v2server['net']=="tls"}tcp{else}{$v2server['net']}{/if}</code></div>
          {if $v2server['net']=="ws"}
          <div class="mb-2">路径: <code>{$v2server['path']}</code></div>
          {/if}
          {if $v2server['net']=="kcp"}
          <div class="mb-2">伪装方式: <code>{$v2server['type']}</code></div>
          {/if}
          {if ($v2server['net']=="ws" && $v2server['tls']=="tls")||$v2server['net']=="tls"||($v2server['net']=="tcp" && $v2server['tls']=="tls")}
          <div class="mb-2">TLS: <code>TLS</code></div>
          {/if}
          <div class="mb-2">VMess链接: <code><a class="copy-text" data-clipboard-text="{URL::getV2Url($user, $node['raw_node'])}">点击复制</a></code></div>
        </div>
      </div>
    </div>
  </div>
  {/if}
  {/if}
  {/foreach}

  {include file='user/scripts.tpl'}
</body>

<div class="modal fade" tabindex="-1" role="dialog" id="nodeinfo">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">节点信息</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe id="infoifram" seamless style="border: none;width: 100%;height: 600px;"></iframe>
      </div>
    </div>
  </div>
</div>

</html>