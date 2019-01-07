[Proxy]
{$ss_group}{$ssr_group}{$v2ray_group}

[POLICY]
{$quan_proxy_group}
{$quan_auto_group}
{$quan_domestic_group}
{$quan_others_group}
{$quan_apple_group}
{$quan_direct_group}

[DNS]
system, 119.29.29.29, 223.6.6.6, 80.80.81.81, 1.1.1.1

[Rule]
{include file='rule/Apple.conf'}
{include file='rule/PROXY.conf'}
{include file='rule/DIRECT.conf'}

GEOIP,CN,🍂 Domestic
FINAL,☁️ Others

[GLOBAL]

[STATE]
STATE,AUTO