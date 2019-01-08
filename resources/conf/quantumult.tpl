{if $quantumult == 3}
[Proxy]
{$ss_group}{$ssr_group}{$v2ray_group}

[POLICY]
{$quan_proxy_group}
{$quan_auto_group}
{$quan_domestic_group}
{$quan_others_group}
{$quan_apple_group}
{$quan_direct_group}

[Rule]
{include file='rule/Apple.conf'}
{include file='rule/PROXY.conf'}
{include file='rule/DIRECT.conf'}

GEOIP,CN,🍂 Domestic
FINAL,☁️ Others

{elseif $quantumult == 2}
[SOURCE]
{$config["appName"]}_v2, server ,{$subUrl}?quantumult=1, false, true, false
{$config["appName"]}_ss, server ,{$subUrl}?sub=2, false, true, false
{$config["appName"]}_ssr, server ,{$subUrl}?sub=1, false, true, false
Hackl0us Rules, filter, https://raw.githubusercontent.com/Hackl0us/Surge-Rule-Snippets/master/LAZY_RULES/Quantumult.conf, true

{/if}

[DNS]
system, 119.29.29.29, 223.6.6.6, 80.80.81.81, 1.1.1.1

[STATE]
STATE,AUTO
