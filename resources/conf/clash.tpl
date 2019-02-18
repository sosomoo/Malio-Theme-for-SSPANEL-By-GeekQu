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

# 设置日志输出级别 (默认级别：silent，即不输出任何内容，以避免因日志内容过大而导致程序内存溢出）。
# 5 个级别：silent / info / warning / error / debug。级别越高日志输出量越大，越倾向于调试，若需要请自行开启。
log-level: {if array_key_exists("log-level",$opts)}{$opts['log-level']}{else}silent{/if}

# Clash 的 RESTful API
external-controller: '0.0.0.0:8233'

# RESTful API 的口令
secret: '{if array_key_exists("secret",$opts)}{$opts['secret']}{else}MixsChina{/if}'

# 您可以将静态网页资源（如 clash-dashboard）放置在一个目录中，clash 将会服务于 `${API}/ui`
# 参数应填写配置目录的相对路径或绝对路径。
# external-ui: folder

{if $opts['dns']==1}
dns:
  enable: true
  ipv6: false
  # listen: 0.0.0.0:53
  # enhanced-mode: redir-host
  nameserver:
    - 1.2.4.8
    - 223.5.5.5
    - 114.114.114.114
    - tls://dns.rubyfish.cn:853
  fallback:
    - tls://dns.rubyfish.cn:853
    - tls://dns.google

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
  - {json_encode($conf,JSON_UNESCAPED_SLASHES)}
{/foreach}

Proxy Group:
  - { name: "Auto", type: url-test, proxies: {$proxies|json_encode}, url: "http://www.gstatic.com/generate_204", interval: 300 }
{append var='proxies' value='Auto'}
  - { name: "Proxy", type: select, proxies: {$proxies|json_encode} }
  - { name: "Domestic", type: select, proxies: ["DIRECT","Proxy"] }
  - { name: "China_media", type: select, proxies: ["Domestic","Proxy"]}
  - { name: "Global_media", type: select, proxies: ["Proxy"]}
  - { name: "Others", type: select, proxies: ["Proxy","Domestic"]}

{include file='rule/Rule.yml'}

# Clash for Windows 自定义系统代理需要绕过的域名或IP。

cfw-bypass:
  - 'music.163.com'
  - '*.music.126.net'
  - localhost
  - 127.*
  - 10.*
  - 172.16.*
  - 172.17.*
  - 172.18.*
  - 172.19.*
  - 172.20.*
  - 172.21.*
  - 172.22.*
  - 172.23.*
  - 172.24.*
  - 172.25.*
  - 172.26.*
  - 172.27.*
  - 172.28.*
  - 172.29.*
  - 172.30.*
  - 172.31.*
  - 192.168.*
  - <local>