#---------------------------------------------------#
## 更新：{date("Y-m-d h:i:s")}
## 感谢：https://github.com/Hackl0us/SS-Rule-Snippet
## 链接：{$userapiUrl}
#---------------------------------------------------#

# HTTP 代理端口
port: {if array_key_exists("port",$opts)}{$opts['port']}{else}7890{/if} 

# SOCKS5 代理端口
socks-port: {if array_key_exists("socks-port",$opts)}{$opts['socks-port']}{else}7891{/if} 

# Linux 和 macOS 的 redir 代理端口
redir-port: {if array_key_exists("redir-port",$opts)}{$opts['redir-port']}{else}7892{/if} 

# 允许局域网的连接
allow-lan: true

# 规则模式：Rule（规则） / Global（全局代理）/ Direct（全局直连）
mode: Rule

# 设置日志输出级别 (默认级别：silent，即不输出任何内容，以避免因日志内容过大而导致程序内存溢出）。
# 5 个级别：silent / info / warning / error / debug。级别越高日志输出量越大，越倾向于调试，若需要请自行开启。
log-level: {if array_key_exists("log-level",$opts)}{$opts['log-level']}{else}silent{/if} 

# Clash 的 RESTful API
external-controller: '0.0.0.0:9090'

# RESTful API 的口令
secret: '{if array_key_exists("secret",$opts)}{$opts['secret']}{else}{/if}' 

# 您可以将静态网页资源（如 clash-dashboard）放置在一个目录中，clash 将会服务于 `RESTful API/ui`
# 参数应填写配置目录的相对路径或绝对路径。
# external-ui: folder

{if array_key_exists("dns",$opts) && $opts['dns']==1}
dns:
  enable: true
  ipv6: false
  # listen: 0.0.0.0:53
  # enhanced-mode: redir-host
  nameserver:
    - 1.2.4.8
    - 223.5.5.5
    - 114.114.114.114
  fallback:
    - tls://dns.rubyfish.cn:853

# Clash DNS 请求逻辑：
# (1) 当访问一个域名时， nameserver 与 fallback 列表内的所有服务器并发请求，得到域名对应的 IP 地址。
# (2) clash 将选取 nameserver 列表内，解析最快的结果。
# (3) 若解析结果中，IP 地址属于 国外，那么 Clash 将选择 fallback 列表内，解析最快的结果。

# 注意：
# (1) 如果您为了确保 DNS 解析结果无污染，请仅保留列表内以 tls:// 开头的 DNS 服务器，但是通常对于国内没有太大必要。
# (2) 如果您不在乎可能解析到污染的结果，更加追求速度。请将 nameserver 列表的服务器插入至 fallback 列表内，并移除重复项。
{/if}

Proxy:
{foreach $confs as $conf}
  - {json_encode($conf,320)}
{/foreach}

Proxy Group:
- { name: "Auto", type: fallback, proxies: {json_encode($proxies,320)}, url: "http://www.gstatic.com/generate_204", interval: 300 }
{$tmp[] = "Auto"}
{assign 'proxies' $tmp|array_merge:$proxies}
{if count($back_china_proxies)!=0}
- { name: "Back_China_Auto", type: fallback, proxies: {json_encode($back_china_proxies,320)}, url: "http://www.gstatic.com/generate_204", interval: 300 }
{append var='back_china_proxies' value='Back_China_Auto'}
- { name: "Back_China_Proxy", type: select, proxies: {json_encode($back_china_proxies,320)} }
{/if}
- { name: "Proxy", type: select, proxies: {json_encode($proxies,320)} }
- { name: "Domestic", type: select, proxies: ["DIRECT","Proxy"] }
{$AsianTV=["Domestic","Proxy"]}
{if count($back_china_proxies)!=0}{append var='AsianTV' value='Back_China_Proxy'}{/if}
- { name: "AsianTV", type: select, proxies: {json_encode($AsianTV,320)} }
- { name: "GlobalTV", type: select, proxies: ["Proxy"]}
- { name: "Others", type: select, proxies: ["Proxy","Domestic"]}

{include file='rule/Rule.yml'}