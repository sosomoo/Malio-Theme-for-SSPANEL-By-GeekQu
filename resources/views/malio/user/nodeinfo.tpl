<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>节点信息 &mdash; {$config["appName"]}</title>

</head>

<body style="background: #fff;overflow-x:hidden;">
  <div id="app">
    <div class="main-wrapper">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="ssr-tab" data-toggle="tab" href="#ssr" role="tab" aria-controls="ssr" aria-selected="true">ShadowrsocksR</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="ss-tab" data-toggle="tab" href="#ss" role="tab" aria-controls="ss" aria-selected="false">Shadowsocks</a>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade active show" id="ssr" role="tabpanel" aria-labelledby="ssr-tab">
          <div class="row mt-2">
            {if URL::SSRCanConnect($user, $mu)}
            <div class="col-12 col-sm-3 col-md-3">
              <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="ssr-info-tab" data-toggle="tab" href="#ssr-info" role="tab" aria-controls="ssr-info" aria-selected="true">信息</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ssr-qrcode-tab" data-toggle="tab" href="#ssr-qrcode" role="tab" aria-controls="ssr-qrcode" aria-selected="false">二维码</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ssr-link-tab" data-toggle="tab" href="#ssr-link" role="tab" aria-controls="ssr-link" aria-selected="false">链接</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ssr-json-tab" data-toggle="tab" href="#ssr-json" role="tab" aria-controls="ssr-json" aria-selected="false">JSON</a>
                </li>
              </ul>
            </div>
            <div class="col-12 col-sm-9 col-md-9">
              <div class="tab-content no-padding" id="myTab2Content">
                <div class="tab-pane fade active show" id="ssr-info" role="tabpanel" aria-labelledby="ssr-info-tab">
                  {$ssr_item = URL::getItem($user, $node, $mu, $relay_rule_id, 0)}
                  {if $ssr_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <p>服务器地址：<code>{$ssr_item['address']}</code><br>
                    服务器端口：<code>{$ssr_item['port']}</code><br>
                    加密方式：<code>{$ssr_item['method']}</code><br>
                    密码：<code>{$ssr_item['passwd']}</code><br>
                    协议：<code>{$ssr_item['protocol']}</code><br>
                    协议参数：<code>{$ssr_item['protocol_param']}</code><br>
                    混淆：<code>{$ssr_item['obfs']}</code><br>
                    混淆参数：<code>{$ssr_item['obfs_param']}</code><br></p>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ssr-qrcode" role="tabpanel" aria-labelledby="ssr-qrcode-tab">
                  {if $ssr_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <div id="ssr-qrcode-img"></div>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ssr-link" role="tabpanel" aria-labelledby="ssr-link-tab">
                  {if $ssr_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <p><a href="##" class="copy-text" data-clipboard-text="{URL::getItemUrl($ssr_item, 0)}">点我复制配置链接</a>
                  </p>
                  <p><a href="{URL::getItemUrl($ssr_item, 0)}">iOS 上用 Safari
                      打开点我即可直接添加</a></p>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ssr-json" role="tabpanel" aria-labelledby="ssr-json-tab">
                  {if $ssr_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <pre style="color:#e83e8c">
{
  "server": "{$ssr_item['address']}",
  "local_address": "127.0.0.1",
  "local_port": 1080,
  "timeout": 300,
  "workers": 1,
  "server_port": {$ssr_item['port']},
  "password": "{$ssr_item['passwd']}",
  "method": "{$ssr_item['method']}",
  "obfs": "{$ssr_item['obfs']}",
  "obfs_param": "{$ssr_item['obfs_param']}",
  "protocol": "{$ssr_item['protocol']}",
  "protocol_param": "{$ssr_item['protocol_param']}"
}
</pre>
                  {/if}
                </div>
              </div>
            </div>
            {else}
            <p>您好，您目前的 加密方式，混淆，或者协议设置在 ShadowsocksR 客户端下无法连接。请您选用 Shadowsocks
              客户端来连接，或者到 资料编辑 页面修改后再来查看此处。</p>
            <p>同时, ShadowsocksR 单端口多用户的连接不受您设置的影响,您可以在此使用相应的客户端进行连接~</p>
            {/if}
          </div>
        </div>
        <div class="tab-pane fade" id="ss" role="tabpanel" aria-labelledby="ss-tab">
          <div class="row mt-2">
            {if URL::SSCanConnect($user, $mu)}
            <div class="col-12 col-sm-3 col-md-3">
              <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="ss-info-tab" data-toggle="tab" href="#ss-info" role="tab" aria-controls="ss-info" aria-selected="true">信息</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ss-qrcode-tab" data-toggle="tab" href="#ss-qrcode" role="tab" aria-controls="ss-qrcode" aria-selected="false">二维码</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ss-link-tab" data-toggle="tab" href="#ss-link" role="tab" aria-controls="ss-link" aria-selected="false">链接</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="ss-json-tab" data-toggle="tab" href="#ss-json" role="tab" aria-controls="ss-json" aria-selected="false">JSON</a>
                </li>
              </ul>
            </div>
            <div class="col-12 col-sm-9 col-md-9">
              <div class="tab-content no-padding" id="myTab2Content">
                <div class="tab-pane fade active show" id="ss-info" role="tabpanel" aria-labelledby="ss-info-tab">
                  {$ss_item = URL::getItem($user, $node, $mu, $relay_rule_id, 1)}
                  {if $ss_item['obfs']=="v2ray" && URL::CanMethodConnect($user->method)!=2}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <p>服务器地址：<code>{$ss_item['address']}</code><br>
                    服务器端口：<code>{$ss_item['port']}</code><br>
                    加密方式：<code>{$ss_item['method']}</code><br>
                    密码：<code>{$ss_item['passwd']}</code><br>
                    混淆：<code>{$ss_item['obfs']}</code><br>
                    混淆参数：<code>{$ss_item['obfs_param']}</code><br>
                  </p>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ss-qrcode" role="tabpanel" aria-labelledby="ss-qrcode-tab">
                  {if $ss_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <div id="ss-qr-win"></div>
                  <p>Windows专用👆</p>
                  <p>其他平台扫这个👇</p>
                  <div id="ss-qr"></div>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ss-link" role="tabpanel" aria-labelledby="ss-link-tab">
                  {if $ss_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
                  <p><a href="##" class="copy-text" data-clipboard-text="{URL::getItemUrl($ss_item, 1)}">点我复制配置链接</a>
                  </p>
                  <p><a href="{URL::getItemUrl($ss_item, 1)}">iOS 上用 Safari 打开点我即可直接添加</a>
                  </p>
                  {/if}
                </div>
                <div class="tab-pane fade" id="ss-json" role="tabpanel" aria-labelledby="ss-json-tab">
                  {if $ss_item['obfs']=="v2ray"}
                  <p>您好，Shadowsocks V2Ray-Plugin 节点需要您的加密方式使用 AEAD 系列。请您到 资料编辑
                    页面修改后再来查看此处。</p>
                  {else}
<pre style="color:#e83e8c">
  {
      "server": "{$ss_item['address']}",
      "local_address": "127.0.0.1",
      "local_port": 1080,
      "timeout": 300,
      "workers": 1,
      "server_port": {$ss_item['port']},
      "password": "{$ss_item['passwd']}",
      "method": "{$ss_item['method']}",
      "plugin": "{URL::getJsonObfs($ss_item)}"
  }
</pre>
                  {/if}
                </div>
              </div>
            </div>
            {else}
            <p>您好，您目前的 加密方式，混淆，或者协议设置在 Shadowsocks 客户端下无法连接。请您选用 ShadowsocksR 客户端来连接，或者到资料编辑 页面修改后再来查看此处。</p>
            {/if}
          </div>
        </div>
      </div>
    </div>
  </div>

  {include file='user/scripts.tpl'}

  <script src="http://cdn.jsdelivr.net/npm/jquery-qrcode2@1.0.0/dist/jquery-qrcode.min.js"></script>

  <script>
    {if URL::SSCanConnect($user, $mu)}
    var text_qrcode = '{URL::getItemUrl($ss_item, 1)}',
    text_qrcode_win = '{URL::getItemUrl($ss_item, 2)}';

    jQuery('#ss-qr').qrcode({
      "text": '{URL::getItemUrl($ss_item, 1)}'
    });

    jQuery('#ss-qr-win').qrcode({
      "text": '{URL::getItemUrl($ss_item, 2)}'
    });
    {/if}

    {if URL::SSRCanConnect($user, $mu)}
    jQuery('#ssr-qrcode-img').qrcode({
      "text": '{URL::getItemUrl($ssr_item, 0)}'
    });
    {/if}
  </script>

</body>

</html>