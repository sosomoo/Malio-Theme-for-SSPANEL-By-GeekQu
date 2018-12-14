#!MANAGED-CONFIG {$userapiUrl}

[General]
loglevel = notify
dns-server = system, 119.29.29.29, 223.6.6.6, 80.80.80.80
skip-proxy = 127.0.0.1, 192.168.0.0/16, 10.0.0.0/8, 172.16.0.0/12, 100.64.0.0/10, 17.0.0.0/8, localhost, *.local, *.crashlytics.com
external-controller-access = MaxChina@0.0.0.0:8233
allow-wifi-access = true
interface = 0.0.0.0
socks-interface = 0.0.0.0
port = 8234
socks-port = 8235
enhanced-mode-by-rule = false
exclude-simple-hostnames = true
ipv6 = true
replica = false

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
{file_get_contents("https://raw.githubusercontent.com/lhie1/Rules/master/Auto/Apple.conf")}
{file_get_contents("https://raw.githubusercontent.com/lhie1/Rules/master/Auto/DIRECT.conf")}
{file_get_contents("https://raw.githubusercontent.com/lhie1/Rules/master/Auto/PROXY.conf")}

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

// Anti_ISP_JD_Hijack
^https?:\/\/coupon\.m\.jd\.com\/ https://coupon.m.jd.com/ 302
^https?:\/\/h5\.m\.jd\.com\/ https://h5.m.jd.com/ 302
^https?:\/\/item\.m\.jd\.com\/ https://item.m.jd.com/ 302
^https?:\/\/m\.jd\.com\/ https://m.jd.com/ 302
^https?:\/\/newcz\.m\.jd\.com\/ https://newcz.m.jd.com/ 302
^https?:\/\/p\.m\.jd\.com\/ https://p.m.jd.com/ 302
^https?:\/\/so\.m\.jd\.com\/ https://so.m.jd.com/ 302
^https?:\/\/union\.click\.jd\.com\/jda? http://union.click.jd.com/jda?adblock= header
^https?:\/\/union\.click\.jd\.com\/sem.php? http://union.click.jd.com/sem.php?adblock= header
^https?:\/\/www.jd.com\/ https://www.jd.com/ 302

// Anti_ISP_Taobao_Hijack
^https?:\/\/m\.taobao\.com\/ https://m.taobao.com/ 302

// Wiki
^https?:\/\/.+.(m\.)?wikipedia\.org/wiki http://www.wikiwand.com/en 302
^https?:\/\/zh.(m\.)?wikipedia\.org/(zh-hans|zh-sg|zh-cn|zh(?=/)) http://www.wikiwand.com/zh 302
^https?:\/\/zh.(m\.)?wikipedia\.org/zh-[a-zA-Z]{2,} http://www.wikiwand.com/zh-hant 302

// Other
^https?:\/\/cfg\.m\.ttkvod\.com\/mobile\/ttk_mobile_1.8\.txt http://ogtre5vp0.bkt.clouddn.com/Static/TXT/ttk_mobile_1.8.txt header
^https?:\/\/cnzz\.com\/ http://ogtre5vp0.bkt.clouddn.com/background.png? header
^https?:\/\/m\.qu\.la\/stylewap\/js\/wap\.js http://ogtre5vp0.bkt.clouddn.com/qu_la_wap.js 302
^https?:\/\/m\.yhd\.com\/1\/\? http://m.yhd.com/1/?adbock= 302
^https?:\/\/n\.mark\.letv\.com\/m3u8api\/ http://burpsuite.applinzi.com/Interface header
^https?:\/\/sqimg\.qq\.com\/ https://sqimg.qq.com/ 302
^https?:\/\/static\.m\.ttkvod\.com\/static_cahce\/index\/index\.txt http://ogtre5vp0.bkt.clouddn.com/Static/TXT/index.txt header
^https?:\/\/www\.iqshw\.com\/d\/js\/m http://burpsuite.applinzi.com/Interface header
^https?:\/\/www\.iqshw\.com\/d\/js\/m http://rewrite.websocket.site:10/Other/Static/JS/Package.js? header

// Tiktok
(?<=aweme\/v1\/)playwm play 302
(?<=&ac=)4G(?=.*) WIFI 302
(?<=_region=)CN(?=&) US 302
(?<=&app_version=)9..(?=.?.?&) 1 302
(?<=\?version_code=)9..(?=.?.?&) 1 302
(?<=&?watermark=)1(?=.*) 0 302
{/literal}
[Header Rewrite]
^*.bdimg.com header-del Referer
^*.qpic.cn header-replace User-Agent WeChat/6.5.22.32 CFNetwork/889.9 Darwin/17.2.0
^*.qpic.cn header-del Referer
^*.ph.126.net header-del Referer
^*.zhimg.com header-del Referer
^*.cnbetacdn.com header-del Referer
^*.zhiding.cn header-del Referer
^*.c114.com.cn header-del Referer
^https?://www.biquge.com.tw header-del Cookie
^https?://www.zhihu.com/question/ header-replace User-Agent Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.45 Safari/537.36

[MITM]