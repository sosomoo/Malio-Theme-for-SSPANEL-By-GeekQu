#!MANAGED-CONFIG {$userapiUrl}

#---------------------------------------------------#
## 更新：{date("Y-m-d h:i:s")}
#---------------------------------------------------#

[General]
loglevel = notify
dns-server = system, 119.29.29.29, 223.6.6.6, 80.80.80.80
skip-proxy = 127.0.0.1, 192.168.0.0/16, 10.0.0.0/8, 172.16.0.0/12, 100.64.0.0/10, 17.0.0.0/8, localhost, *.local, *.crashlytics.com
external-controller-access = MixChina@0.0.0.0:8233
allow-wifi-access = true
enhanced-mode-by-rule = false
exclude-simple-hostnames = true
ipv6 = true
replica = false
{if $surge == 3}
http-listen = 0.0.0.0:8234
socks5-listen = 0.0.0.0:8235
internet-test-url = http://baidu.com
proxy-test-url = http://bing.com
test-timeout = 3
{else}
interface = 0.0.0.0
socks-interface = 0.0.0.0
port = 8234
socks-port = 8235
{/if}

[Replica]
hide-apple-request = true
hide-crashlytics-request = true
hide-udp = false
use-keyword-filter = false

[Proxy]
🚀 Direct = direct
{$proxy_group}

[Proxy Group]

🍃 Proxy = select, 🍈 Select, 🏃 Auto, 🚀 Direct

🍂 Domestic = select, 🚀 Direct, 🍃 Proxy

☁️ Others = select, 🍃 Proxy, 🚀 Direct

🍎 Only = select, 🚀 Direct, 🍃 Proxy

🍈 Select = select{$proxy_name}

🏃 Auto = url-test{$proxy_name}, url = http://www.gstatic.com/generate_204, interval = 1200

[Rule]
{if $surge == 3}
RULE-SET,https://raw.githubusercontent.com/lhie1/Rules/master/Surge3/apple.list,🍎 Only
RULE-SET,https://raw.githubusercontent.com/lhie1/Rules/master/Surge3/proxy.list,🍃 Proxy
RULE-SET,https://raw.githubusercontent.com/lhie1/Rules/master/Surge3/domestic.list,🍂 Domestic
RULE-SET,SYSTEM,DIRECT
{else}
{include file='rule/Apple.conf'}
{include file='rule/PROXY.conf'}
{include file='rule/DIRECT.conf'}
{/if}

GEOIP,CN,🍂 Domestic
FINAL,☁️ Others,dns-failed

[Host]
localhost = 127.0.0.1
syria.sy = 127.0.0.1
*.1688.com = server:223.6.6.6
*.fliggy.com = server:223.6.6.6
*.aliqin.com = server:223.6.6.6
*.tmall.com = server:223.6.6.6
*.taobao.com = server:223.6.6.6
*.ali*.com = server:223.6.6.6
*.upyun.com = server:223.6.6.6
*.mmstat.com = server:223.6.6.6
*.jd.com = server:119.29.29.29
*.qq.com = server:119.29.29.29
*buyimg.com = server:119.29.29.29
*gtimg.* = server:119.29.29.29
{literal}
[URL Rewrite]
// Google_Service_HTTPS_Jump
^https?:\/\/(www\.)?g\.cn https://www.google.com 302
^https?:\/\/(www\.)?google\.cn https://www.google.com 302

// Wiki
^https?:\/\/.+.(m\.)?wikipedia\.org/wiki http://www.wikiwand.com/en 302
^https?:\/\/zh.(m\.)?wikipedia\.org/(zh-hans|zh-sg|zh-cn|zh(?=/)) http://www.wikiwand.com/zh 302
^https?:\/\/zh.(m\.)?wikipedia\.org/zh-[a-zA-Z]{2,} http://www.wikiwand.com/zh-hant 302

// Tiktok US
(?<=aweme\/v1\/)playwm play 302
(?<=&ac=)4G(?=.*) WIFI 302
(?<=_region=)CN(?=&) US 302
(?<=&app_version=)9..(?=.?.?&) 1 302
(?<=\?version_code=)9..(?=.?.?&) 1 302
(?<=&?watermark=)1(?=.*) 0 302
{/literal}
[Header Rewrite]
^https?://www.zhihu.com/question/ header-replace User-Agent Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.45 Safari/537.36
