#---------------------------------------------------#
## 更新：{date("Y-m-d h:i:s")}
## 感谢：https://github.com/Hackl0us/SS-Rule-Snippet
## 链接：{$userapiUrl}
#---------------------------------------------------#

# HTTP 代理端口
port: 8234

# SOCKS5 代理端口
socks-port: 8235

# Linux 和 macOS 的 redir 代理端口
redir-port: 8236

# 允许局域网的连接
allow-lan: true

# 规则模式：Rule（规则） / Global（全局代理）/ Direct（全局直连）
mode: Rule

# 设置日志输出级别 (默认级别：info，级别越高日志输出量越大，越倾向于调试)
# 四个级别：info / warning / error / debug
log-level: info

# Clash 的 RESTful API
external-controller: '0.0.0.0:8233'

# RESTful API 的口令
secret: 'MixsChina'

dns:
  enable: true
  ipv6: false
  # listen: 0.0.0.0:53
  # enhanced-mode: redir-host
  nameserver:
     - 114.114.114.114
     - 223.5.5.5
     - tls://dns.rubyfish.cn:853
  fallback:
     - 114.114.114.114
     - tls://dns.rubyfish.cn:853
     - 8.8.8.8

# Clash DNS 请求逻辑：
# (1) 当访问一个域名时， nameserver 与 fallback 列表内的所有服务器并发请求，得到域名对应的 IP 地址。
# (2) clash 将选取 nameserver 列表内，解析最快的结果。
# (3) 若解析结果中，IP 地址属于 国外，那么 Clash 将选择 fallback 列表内，解析最快的结果。

Proxy:
{foreach $confs as $conf}
  - {json_encode($conf,JSON_UNESCAPED_SLASHES)}
{/foreach}

Proxy Group:
  - { name: "Auto", type: url-test, proxies: {$proxies|json_encode}, url: "http://www.gstatic.com/generate_204", interval: 300 }
  - { name: "Load-Balance", type: load-balance, proxies: {$proxies|json_encode} }
  {append var='proxies' value='Auto'}
  {append var='proxies' value="Load-Balance"}

  - { name: "Back_China_Auto", type: url-test, proxies: {$back_china_proxies|json_encode}, url: "http://www.gstatic.com/generate_204", interval: 300 }
  - { name: "Back_China_Load-Balance", type: load-balance, proxies: {$back_china_proxies|json_encode} }
  {append var='back_china_proxies' value='Back_China_Auto'}
  {append var='back_china_proxies' value="Back_China_Load-Balance"}

  - { name: "Proxy", type: select, proxies: {$proxies|json_encode} }
  - { name: "Back_China_Proxy", type: select, proxies: {$back_china_proxies|json_encode} }
  - { name: "Domestic", type: select, proxies: ["DIRECT","Proxy"] }
  - { name: "China_media", type: select, proxies: ["Domestic","Proxy","Back_China_Proxy"]}
  - { name: "Global_media", type: select, proxies: ["Proxy"]}
  - { name: "Others", type: select, proxies: ["Proxy","Domestic"]}

{include file='rule/Rule.yml'}
