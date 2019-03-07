{if $quantumult == 3}
[SERVER]
{$proxys['ss']}{$proxys['ssr']}{$proxys['v2ray']}

[POLICY]
{$groups['proxy_group']}
{$groups['domestic_group']}
{$groups['others_group']}
{$groups['apple_group']}
{$groups['auto_group']}
{$groups['direct_group']}

[Rule]
{include file='rule/Apple.conf'}
{include file='rule/PROXY.conf'}
{include file='rule/DIRECT.conf'}

GEOIP,CN,🍂 Domestic
FINAL,☁️ Others

{elseif $quantumult == 2}
[SERVER]

[SOURCE]
{$appName}_v2, server ,{$subUrl}?quantumult=1, false, true, false
{$appName}_ss, server ,{$subUrl}?sub=2, false, true, false
{$appName}_ssr, server ,{$subUrl}?sub=1, false, true, false
Hackl0us Rules, filter, https://raw.githubusercontent.com/Hackl0us/Surge-Rule-Snippets/master/LAZY_RULES/Quantumult.conf, true

{/if}

[DNS]
system, 119.29.29.29, 223.6.6.6, 80.80.81.81, 1.1.1.1

[STATE]
STATE,AUTO
