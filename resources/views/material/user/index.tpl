
{include file='user/main.tpl'}
{$ssr_prefer = URL::SSRCanConnect($user, 0)}

{$pre_user = URL::cloneUser($user)}

{$user = URL::getSSRConnectInfo($pre_user)}
{$ssr_url_all = URL::getAllUrl($pre_user, 0, 0)}
{$ssr_url_all_mu = URL::getAllUrl($pre_user, 1, 0)}

{$user = URL::getSSConnectInfo($pre_user)}
{$ss_url_all = URL::getAllUrl($pre_user, 0, 2)}
{$ss_url_all_mu = URL::getAllUrl($pre_user, 1, 1)}

{$ssd_url_all =URL::getAllSSDUrl($user)}

{$v2_url_all = URL::getAllVMessUrl($user)}

	<main class="content">

		<div class="content-header ui-content-header">
			<div class="container">
				<h1 class="content-heading">用户中心</h1>
			</div>
		</div>
		<div class="container">
			<section class="content-inner margin-top-no">
				<div class="ui-card-wrap">

						<div class="col-xx-12 col-xs-6 col-lg-3">
								<div class="card user-info">
									<div class="user-info-main">
										<div class="nodemain">
											<div class="nodehead node-flex">
												<div class="nodename">帐号等级</div>
												<a href="/user/shop" class="card-tag tag-orange">升级</a>
											</div>
											<div class="nodemiddle node-flex">
												<div class="nodetype">
													{if $user->class!=0}
													<dd>VIP {$user->class}</dd>
													{else}
													<dd>普通用户</dd>
													{/if}
												</div>
											</div>
										</div>
										<div class="nodestatus">
											<div class="infocolor-red">
												<i class="icon icon-md t4-text">stars</i>
											</div>
										</div>
									</div>
									<div class="user-info-bottom">
										<div class="nodeinfo node-flex">
										{if $user->class!=0}
											<span><i class="icon icon-md">add_circle</i>到期流量清空</span>
										{else}
										    <span><i class="icon icon-md">add_circle</i>升级解锁VIP节点</span>
										{/if}
										</div>
									</div>
								</div>
							</div>
							<div class="col-xx-12 col-xs-6 col-lg-3">
								<div class="card user-info">
									<div class="user-info-main">
										<div class="nodemain">
											<div class="nodehead node-flex">
												<div class="nodename">余额</div>
												<a href="/user/code" class="card-tag tag-green">充值</a>
											</div>
											<div class="nodemiddle node-flex">
												<div class="nodetype">
													{$user->money} CNY
												</div>
											</div>
										</div>
										<div class="nodestatus">
											<div class="infocolor-green">
												<i class="icon icon-md">account_balance_wallet</i>
											</div>
										</div>
									</div>
									<div class="user-info-bottom">
										<div class="nodeinfo node-flex">
											<span href="/user/shop"><i class="icon icon-md">attach_money</i>账户内可用余额</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xx-12 col-xs-6 col-lg-3">
								<div class="card user-info">
									<div class="user-info-main">
										<div class="nodemain">
											<div class="nodehead node-flex">
												<div class="nodename">在线设备数</div>
											</div>
											<div class="nodemiddle node-flex">
												<div class="nodetype">
													{if $user->node_connector!=0}
													<dd>{$user->online_ip_count()} / {$user->node_connector}</dd>
													{else}
													<dd>{$user->online_ip_count()} / 不限制 </dd>
													{/if}
												</div>
											</div>
										</div>
										<div class="nodestatus">
											<div class="infocolor-yellow">
												<i class="icon icon-md">phonelink</i>
											</div>
										</div>
									</div>
									<div class="user-info-bottom">
										<div class="nodeinfo node-flex">
											<span><i class="icon icon-md">donut_large</i>在线设备/设备限制数</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xx-12 col-xs-6 col-lg-3">
								<div class="card user-info">
									<div class="user-info-main">
										<div class="nodemain">
											<div class="nodehead node-flex">
												<div class="nodename">端口速率</div>
											</div>
											<div class="nodemiddle node-flex">
												<div class="nodetype">
													{if $user->node_speedlimit!=0}
													<dd><code>{$user->node_speedlimit}</code>Mbps</dd>
													{else}
													<dd>无限制</dd>
													{/if}
												</div>
											</div>
										</div>
										<div class="nodestatus">
											<div class="infocolor-blue">
												<i class="icon icon-md">settings_input_component</i>
											</div>
										</div>
									</div>
									<div class="user-info-bottom">
										<div class="nodeinfo node-flex">
											<span><i class="icon icon-md">signal_cellular_alt</i>账户最高下行网速</span>
										</div>
									</div>
								</div>
							</div>

						<div class="col-xx-12 col-sm-8">

							<div class="card">
								<div class="card-main">
									<div class="card-inner margin-bottom-no">
                                    <p class="card-heading"> <i class="icon icon-md">notifications_active</i>公告栏</p>
										{if $ann != null}
										<p>{$ann->content}</p>
										<br/>
										<strong>查看所有公告请<a href="/user/announcement">点击这里</a></strong>
										{/if}
										{if $config["enable_admin_contact"] == 'true'}
										<p class="card-heading">管理员联系方式</p>
										{if $config["admin_contact1"]!=null}
										<p>{$config["admin_contact1"]}</p>
										{/if}
										{if $config["admin_contact2"]!=null}
										<p>{$config["admin_contact2"]}</p>
										{/if}
										{if $config["admin_contact3"]!=null}
										<p>{$config["admin_contact3"]}</p>
										{/if}
										{/if}
									</div>
								</div>
							</div>


							<div class="card quickadd">
								<div class="card-main">
									<div class="card-inner margin-bottom-no">

										<div class="cardbtn-edit">
											<div class="card-heading"><i class="icon icon-md">phonelink</i> <a id="quickadd" href="#quickadd"></a> 快速使用</div>
										</div>

										<nav class="tab-nav margin-top-no">
											<ul class="nav nav-list">
												<li class="active">
													<a class="" data-toggle="tab" href="#sub_center"><i class="icon icon-lg">info_outline</i>&nbsp;订阅中心</a>
												</li>
												<li>
													<a class="" data-toggle="tab" href="#info_center"><i class="icon icon-lg">flight_takeoff</i>&nbsp;连接信息</a>
												</li>
											</ul>
										</nav>

										<div class="card-inner">
											<div class="tab-content">

												<div class="tab-pane fade" id="info_center">
													<p>您的链接信息：</p>
													{if URL::SSRCanConnect($user)}
													<dl class="dl-horizontal">
														<p><dt>端口</dt>
														<dd>{$user->port}</dd></p>
														<p><dt>密码</dt>
														<dd>{$user->passwd}</dd></p>
														<p><dt>自定义加密</dt>
														<dd>{$user->method}</dd></p>
														<p><dt>自定义协议</dt>
														<dd>{$user->protocol}</dd></p>
														<p><dt>自定义混淆</dt>
														<dd>{$user->obfs}</dd></p>
														<p><dt>自定义混淆参数</dt>
														<dd>{$user->obfs_param}</dd></p>
													</dl>
													<hr/>
													<p>您当前为 SSR 协议模式，请您选用支持 SSR 协议的客户端来连接。</p>
													{else}
													<dl class="dl-horizontal">
														<p><dt>端口</dt>
														<dd>{$user->port}</dd></p>
														<p><dt>密码</dt>
														<dd>{$user->passwd}</dd></p>
														<p><dt>自定义加密</dt>
														<dd>{$user->method}</dd></p>
														<p><dt>自定义混淆</dt>
														<dd>{$user->obfs}</dd></p>
														<p><dt>自定义混淆参数</dt>
														<dd>{$user->obfs_param}</dd></p>
													</dl>
													<hr/>
													<p>您当前为 SS 协议模式，请您选用支持 SS 协议的客户端来连接。</p>
													{/if}
												</div>

												<div class="tab-pane fade active in" id="sub_center">
													<nav class="tab-nav margin-top-no">
														<ul class="nav nav-list">
															<li class="active">
																<a class="" data-toggle="tab" href="#sub_center_windows"><i class="icon icon-lg">desktop_windows</i>&nbsp;Windows</a>
															</li>
															<li>
																<a class="" data-toggle="tab" href="#sub_center_mac"><i class="icon icon-lg">laptop_mac</i>&nbsp;MacOS</a>
															</li>
															<li>
																<a class="" data-toggle="tab" href="#sub_center_ios"><i class="icon icon-lg">phone_iphone</i>&nbsp;iOS</a>
															</li>
															<li>
																<a class="" data-toggle="tab" href="#sub_center_android"><i class="icon icon-lg">android</i>&nbsp;Android</a>
															</li>
														</ul>
													</nav>
													<div class="tab-pane fade active in" id="sub_center_windows">
														<p><span class="icon icon-lg text-white">filter_1</span> [ SSR ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$ssr_url_all}{$ssr_url_all_mu}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> [ VMess ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$v2_url_all}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p>如您的应用不在下方的支持列表，那么请使用上方的使用方案：</p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_1</span> <a class="btn-dl" href="https://github.com/CGDF-Github/SSD-Windows/releases"><i class="material-icons">save_alt</i> GET</a> SSD - [ SS ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Windows/ShadowsocksD"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssd"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> <a class="btn-dl" href="https://github.com/Fndroid/clash_for_windows_pkg/releases"><i class="material-icons">save_alt</i> GET</a> Clash for Windows - [ SS/VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Windows/Clash-for-Windows"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["clash"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a>.<a class="btn-dl" href="{$subInfo["clash"]}"><i class="material-icons icon-sm">how_to_vote</i>配置下载</a></p>
													</div>
													<div class="tab-pane fade" id="sub_center_mac">
														<p><span class="icon icon-lg text-white">filter_1</span> [ SSR ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$ssr_url_all}{$ssr_url_all_mu}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> [ VMess ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$v2_url_all}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p>如您的应用不在下方的支持列表，那么请使用上方的使用方案：</p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_1</span> <a class="btn-dl" href="https://nssurge.com/mac/v3/Surge-latest.zip"><i class="material-icons">save_alt</i> GET</a> Surge - [ SS ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/macOS/Surge"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["surge3"]}"><i class="material-icons icon-sm">how_to_vote</i>3.x 托管</a>.<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["surge_node"]}"><i class="material-icons icon-sm">how_to_vote</i>3.x 节点</a>.<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["surge2"]}"><i class="material-icons icon-sm">how_to_vote</i>2.x 托管</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> <a class="btn-dl" href="https://github.com/yichengchen/clashX/releases"><i class="material-icons">save_alt</i> GET</a> ClashX - [ SS/VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/macOS/ClashX"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["clash"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a>.<a class="btn-dl" href="{$subInfo["clash"]}"><i class="material-icons icon-sm">how_to_vote</i>配置下载</a></p>
													</div>
													<div class="tab-pane fade" id="sub_center_ios">
														<p><span class="icon icon-lg text-white">filter_1</span> [ SSR ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$ssr_url_all}{$ssr_url_all_mu}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> [ VMess ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$v2_url_all}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p>如您的应用不在下方的支持列表，那么请使用上方的使用方案：</p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_1</span> <a class="btn-dl" href="https://itunes.apple.com/us/app/surge-3/id1442620678?ls=1&mt=8"><i class="material-icons">save_alt</i> GET</a> Surge - [ SS ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/iOS/Surge"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="btn-dl" href="surge3:///install-config?url={urlencode($subInfo["surge3"])}"><i class="material-icons icon-sm">how_to_vote</i>3.x 一键</a>.<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["surge_node"]}"><i class="material-icons icon-sm">how_to_vote</i>3.x 节点</a>.<a class="btn-dl" href="surge:///install-config?url={urlencode($subInfo["surge2"])}"><i class="material-icons icon-sm">how_to_vote</i>2.x 一键</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> <a class="btn-dl" href="https://itunes.apple.com/us/app/kitsunebi-proxy-utility/id1446584073?ls=1&mt=8"><i class="material-icons">save_alt</i> GET</a> Kitsunebi - [ SS/VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/iOS/Kitsunebi"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>V2 订阅</a>.<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray_ss"]}"><i class="material-icons icon-sm">how_to_vote</i>SS + V2 混合订阅</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_3</span> <a class="btn-dl" href="https://itunes.apple.com/us/app/quantumult/id1252015438?ls=1&mt=8"><i class="material-icons">save_alt</i> GET</a> Quantumult - [ SS/SSR/VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/iOS/Quantumult_sub"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["quantumult_v2"]}"><i class="material-icons icon-sm">how_to_vote</i>V2 订阅</a>.<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>SSR 订阅</a>.<a id="quan_sub" class="copy-config btn-dl" onclick=Copyconfig("{$subInfo["quantumult_sub"]}","#quan_sub","quantumult://settings?configuration=clipboard")><i class="material-icons icon-sm">how_to_vote</i>完整订阅配置</a>.<a id="quan_conf" class="copy-config btn-dl" onclick=Copyconfig("{$subInfo["quantumult_conf"]}","#quan_conf","quantumult://settings?configuration=clipboard")><i class="material-icons icon-sm">how_to_vote</i>完整策略组配置</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_4</span> <a class="btn-dl" href="https://itunes.apple.com/us/app/shadowrocket/id932747118?mt=8"><i class="material-icons">save_alt</i> GET</a> Shadowrocket - [ SS/SSR/VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/iOS/Shadowrocket"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="btn-dl" onclick=AddSub("{$subInfo["v2ray"]}","sub://")><i class="material-icons icon-sm">how_to_vote</i>V2 订阅</a>.<a class="btn-dl" onclick=AddSub("{$subInfo["ssr"]}","sub://")><i class="material-icons icon-sm">how_to_vote</i>SSR 订阅</a>.<a class="btn-dl" onclick=AddSub("{$subInfo["v2ray_ss_ssr"]}","sub://")><i class="material-icons icon-sm">how_to_vote</i>SS(R) + V2 订阅</a></p>
													</div>
													<div class="tab-pane fade" id="sub_center_android">
														<p><span class="icon icon-lg text-white">filter_1</span> [ SSR ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$ssr_url_all}{$ssr_url_all_mu}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> [ VMess ]：</p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>订阅链接</a>.<a class="copy-text btn-dl" data-clipboard-text="{$v2_url_all}"><i class="material-icons icon-sm">how_to_vote</i>全部 URL</a></p>
														<hr/>
														<p>如您的应用不在下方的支持列表，那么请使用上方的使用方案：</p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_1</span> <a class="btn-dl" href="https://github.com/CGDF-Github/SSD-Android/releases"><i class="material-icons">save_alt</i> GET</a> SSD - [ SS ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Android/ShadowsocksD"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssd"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_2</span> <a class="btn-dl" href="#"><i class="material-icons">save_alt</i> GET</a> SSR - [ SSR ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Android/ShadowsocksR"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["ssr"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_3</span> <a class="btn-dl" href="https://github.com/2dust/v2rayNG/releases"><i class="material-icons">save_alt</i> GET</a> V2rayNG - [ VMess ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Android/v2rayNG"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["v2ray"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝链接</a></p>
														<hr/>
														<p><span class="icon icon-lg text-white">filter_4</span> <a class="btn-dl" href="https://rink.hockeyapp.net/recruit/2113783c503645abb0a5ec6317e1a169"><i class="material-icons">save_alt</i> GET</a> Surfboard - [ SS ]：</p>
															<p>教程文档：<a class="btn-dl" href="/doc/#/Android/Surfboard"><i class="material-icons icon-sm">how_to_vote</i>点击查看</a></p>
															<p>使用方式：<a class="copy-text btn-dl" data-clipboard-text="{$subInfo["surfboard"]}"><i class="material-icons icon-sm">how_to_vote</i>拷贝托管</a>.<a class="btn-dl" href="{$subInfo["surfboard"]}"><i class="material-icons icon-sm">how_to_vote</i>配置下载</a></p>
													</div>
												</div>

											</div>
										</div>

									</div>
								</div>
							</div>

						</div>

						<div class="col-xx-12 col-sm-4">

							<div class="card">
								<div class="card-main">
									<div class="card-inner margin-bottom-no">
										<p class="card-heading"><i class="icon icon-md">account_circle</i>账号使用情况</p>
										<dl class="dl-horizontal">


											<p><dt>等级过期时间</dt>
                                              {if $user->class_expire!="1989-06-04 00:05:00"}
											<dd><i class="icon icon-md">event</i>&nbsp;{$user->class_expire}</dd>
                                              {else}
                                              <dd><i class="icon icon-md">event</i>&nbsp;不过期</dd>
                                              {/if}
											</p>
                                          	<p><dt>等级有效期</dt>
                                              <i class="icon icon-md">event</i>
                                              <span class="label-level-expire">剩余</span>
											  <code><span id="days-level-expire"></span></code>
                                              <span class="label-level-expire">天</span>
                                            </p>

											<p><dt>帐号过期时间</dt>
											  <dd><i class="icon icon-md">event</i>&nbsp;{$user->expire_in}</dd>
                                            </p>
                                            <p><dt>账号有效期</dt>
                                              <i class="icon icon-md">event</i>
                                              <span class="label-account-expire">剩余</span>
											  <code><span id="days-account-expire"></span></code>
											  <span class="label-account-expire">天</span>
                                           </p>

											<p><dt>上次使用</dt>
                                              {if $user->lastSsTime()!="从未使用喵"}
											<dd><i class="icon icon-md">event</i>&nbsp;{$user->lastSsTime()}</dd>
                                              {else}
                                              <dd><i class="icon icon-md">event</i>&nbsp;从未使用</dd>
                                              {/if}</p>
                                            <p><dt>上次签到时间：</dt>
                                            <dd><i class="icon icon-md">event</i>&nbsp;{$user->lastCheckInTime()}</dd></p>


											<p id="checkin-msg"></p>

											{if $geetest_html != null}
												<div id="popup-captcha"></div>
											{/if}
											{if $recaptcha_sitekey != null && $user->isAbleToCheckin()}
                                                <div class="g-recaptcha" data-sitekey="{$recaptcha_sitekey}"></div>
                                            {/if}


									<div class="card-action">
										<div class="usercheck pull-left">
											{if $user->isAbleToCheckin() }

												<div id="checkin-btn">
													<button id="checkin" class="btn btn-brand btn-flat"><span class="icon">check</span>&nbsp;点我签到&nbsp;
													<div><span class="icon">screen_rotation</span>&nbsp;或者摇动手机签到</div>
													</button>
												</div>


											{else}

												<p><a class="btn btn-brand disabled btn-flat" href="#"><span class="icon">check</span>&nbsp;今日已签到</a></p>


											{/if}
										</div>
									</div>
										</dl>
									</div>

								</div>
							</div>

							<div class="card">
								<div class="card-main">
									<div class="card-inner">

										{*<div id="traffic_chart" style="height: 300px; width: 100%;"></div>

                                       <script src="/assets/js/canvasjs.min.js"> </script>
										<script type="text/javascript">
											var chart = new CanvasJS.Chart("traffic_chart",



											{
                                         theme: "light1",


												title:{
													text: "流量使用情况",
													fontFamily: "Impact",
													fontWeight: "normal"
													},
												legend:{
													verticalAlign: "bottom",
													horizontalAlign: "center"
												},
												data: [
												{
													startAngle: -15,
													indexLabelFontSize: 20,
													indexLabelFontFamily: "Garamond",
													indexLabelFontColor: "darkgrey",
													indexLabelLineColor: "darkgrey",
													indexLabelPlacement: "outside",
                                                    yValueFormatString: "##0.00\"%\"",
													type: "pie",
													showInLegend: true,
													dataPoints: [
														{if $user->transfer_enable != 0}
														{
															y: {$user->last_day_t/$user->transfer_enable*100},label: "过去已用", legendText:"过去已用 {number_format($user->last_day_t/$user->transfer_enable*100,2)}% {$user->LastusedTraffic()}", indexLabel: "过去已用 {number_format($user->last_day_t/$user->transfer_enable*100,2)}% {$user->LastusedTraffic()}"
														},
														{
															y: {($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100},label: "今日已用", legendText:"今日已用 {number_format(($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100,2)}% {$user->TodayusedTraffic()}", indexLabel: "今日已用 {number_format(($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100,2)}% {$user->TodayusedTraffic()}"
														},
														{
															y: {($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100},label: "剩余可用", legendText:"剩余可用 {number_format(($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100,2)}% {$user->unusedTraffic()}", indexLabel: "剩余可用 {number_format(($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100,2)}% {$user->unusedTraffic()}"
														}
														{/if}
													]
												}
												]
											});

											chart.render();
										</script> *}

										<div class="progressbar">
	                                         <div class="before"></div>
	                                         <div class="bar tuse color3" style="width:calc({($user->transfer_enable==0)?0:($user->u+$user->d-$user->last_day_t)/$user->transfer_enable*100}%);"><span></span></div>
											 <div class="label-flex">
												<div class="label la-top"><div class="bar ard color3"><span></span></div><span class="traffic-info">今日已用</span><code class="card-tag tag-red">{$user->TodayusedTraffic()}</code></div>
											 </div>
										</div>
										<div class="progressbar">
										    <div class="before"></div>
										    <div class="bar ard color2" style="width:calc({($user->transfer_enable==0)?0:$user->last_day_t/$user->transfer_enable*100}%);"><span></span></div>
										    <div class="label-flex">
										       <div class="label la-top"><div class="bar ard color2"><span></span></div><span class="traffic-info">过去已用</span><code class="card-tag tag-orange">{$user->LastusedTraffic()}</code></div>
										    </div>
								        </div>
										<div class="progressbar">
											<div class="before"></div>
											<div class="bar remain color" style="width:calc({($user->transfer_enable==0)?0:($user->transfer_enable-($user->u+$user->d))/$user->transfer_enable*100}%);"><span></span></div>
											<div class="label-flex">
											   <div class="label la-top"><div class="bar ard color"><span></span></div><span class="traffic-info">剩余流量</span><code class="card-tag tag-green" id="remain">{$user->unusedTraffic()}</code></div>
											</div>
									   </div>


									</div>

								</div>
							</div>

								</div>
							</div>
						{include file='dialog.tpl'}

					</div>


				</div>
			</section>
		</div>
	</main>







{include file='user/footer.tpl'}

<script src="https://cdn.jsdelivr.net/npm/shake.js@1.2.2/shake.min.js"></script>

<script>

function DateParse(str_date) {
		var str_date_splited = str_date.split(/[^0-9]/);
		return new Date (str_date_splited[0], str_date_splited[1] - 1, str_date_splited[2], str_date_splited[3], str_date_splited[4], str_date_splited[5]);
}

/*
 * Author: neoFelhz & CloudHammer
 * https://github.com/CloudHammer/CloudHammer/make-sspanel-v3-mod-great-again
 * License: MIT license & SATA license
 */
function CountDown() {
    var levelExpire = DateParse("{$user->class_expire}");
    var accountExpire = DateParse("{$user->expire_in}");
    var nowDate = new Date();
    var a = nowDate.getTime();
    var b = levelExpire - a;
    var c = accountExpire - a;
    var levelExpireDays = Math.floor(b/(24*3600*1000));
    var accountExpireDays = Math.floor(c/(24*3600*1000));
    if (levelExpireDays < 0 || levelExpireDays > 315360000000) {
        document.getElementById('days-level-expire').innerHTML = "无限期";
        for (var i=0;i<document.getElementsByClassName('label-level-expire').length;i+=1){
            document.getElementsByClassName('label-level-expire')[i].style.display = 'none';
        }
    } else {
        document.getElementById('days-level-expire').innerHTML = levelExpireDays;
    }
    if (accountExpireDays < 0 || accountExpireDays > 315360000000) {
        document.getElementById('days-account-expire').innerHTML = "无限期";
        for (var i=0;i<document.getElementsByClassName('label-account-expire').length;i+=1){
            document.getElementsByClassName('label-account-expire')[i].style.display = 'none';
        }
    } else {
        document.getElementById('days-account-expire').innerHTML = accountExpireDays;
    }
}
</script>

<script>

$(function(){
	new Clipboard('.copy-text');
});

$(".copy-text").click(function () {
	$("#result").modal();
	$("#msg").html("已拷贝订阅链接，请您继续接下来的操作。");
});

function AddSub(url,jumpurl="") {
	let tmp = window.btoa(url);
	window.location.href = jumpurl + tmp;
}

function Copyconfig(url,id,jumpurl="") {
    $.ajax({
        url: url,
        type: 'get',
        async: false,
        success: function(res) {
            if(res) {
                $("#result").modal();
                $("#msg").html("获取成功。");
                $(id).data('data', res);
				console.log(res);
            } else {
                $("#result").modal();
                $("#msg").html("获取失败,请稍后再试");
            }
        }
    });
    const clipboard = new Clipboard('.copy-config', {
        text: function() {
            return $(id).data('data');
        }
    });
    clipboard.on('success', function(e) {
				$("#result").modal();
				if (jumpurl != "") {
					$("#msg").html("已经复制成功,您将跳转到app设置");
					window.setTimeout(function () {
						window.open(jumpurl)
					}, 1000);

				} else {

					$("#msg").html("已经复制成功");
				}
			}
    );
    clipboard.on("error",function(e){
		console.error('Action:', e.action);
		console.error('Trigger:', e.trigger);
		console.error('Text:', e.text);
			}
	);

}

 {if $user->transfer_enable-($user->u+$user->d) == 0}
window.onload = function() {
    $("#result").modal();
    $("#msg").html("您的流量已经用完或账户已经过期了，如需继续使用，请进入商店选购新的套餐~");
};
 {/if}

{if $geetest_html == null}

var checkedmsgGE = '<p><a class="btn btn-brand disabled btn-flat waves-attach" href="#"><span class="icon">check</span>&nbsp;已签到</a></p>';
window.onload = function() {
    var myShakeEvent = new Shake({
        threshold: 15
    });

    myShakeEvent.start();
  	CountDown()

    window.addEventListener('shake', shakeEventDidOccur, false);

    function shakeEventDidOccur () {
		if("vibrate" in navigator){
			navigator.vibrate(500);
		}

        $.ajax({
                type: "POST",
                url: "/user/checkin",
                dataType: "json",{if $recaptcha_sitekey != null}
                data: {
                    recaptcha: grecaptcha.getResponse()
                },{/if}
                success: function (data) {
                    if (data.ret) {
					$("#checkin-msg").html(data.msg);
					$("#checkin-btn").html(checkedmsgGE);
					$("#result").modal();
					$("#msg").html(data.msg);
					$('#remain').html(data.traffic);
					$('.bar.remain.color').css('width',(data.unflowtraffic-({$user->u}+{$user->d}))/data.unflowtraffic*100+'%');
				} else {
					$("#result").modal();
					$("#msg").html(data.msg);
				}
                },
                error: function (jqXHR) {
					$("#result").modal();
                    $("#msg").html("发生错误：" + jqXHR.status);
                }
            });
    }
};


$(document).ready(function () {
	$("#checkin").click(function () {
		$.ajax({
			type: "POST",
			url: "/user/checkin",
			dataType: "json",{if $recaptcha_sitekey != null}
            data: {
                recaptcha: grecaptcha.getResponse()
            },{/if}
			success: function (data) {
				if (data.ret) {
					$("#checkin-msg").html(data.msg);
					$("#checkin-btn").html(checkedmsgGE);
					$("#result").modal();
					$("#msg").html(data.msg);
					$('#remain').html(data.traffic);
					$('.bar.remain.color').css('width',(data.unflowtraffic-({$user->u}+{$user->d}))/data.unflowtraffic*100+'%');
				} else {
					$("#result").modal();
					$("#msg").html(data.msg);
				}
			},
			error: function (jqXHR) {
				$("#result").modal();
				$("#msg").html("发生错误：" + jqXHR.status);
			}
		})
	})
})


{else}


window.onload = function() {
    var myShakeEvent = new Shake({
        threshold: 15
    });

    myShakeEvent.start();

    window.addEventListener('shake', shakeEventDidOccur, false);

    function shakeEventDidOccur () {
		if("vibrate" in navigator){
			navigator.vibrate(500);
		}

        c.show();
    }
};



var handlerPopup = function (captchaObj) {
	c = captchaObj;
	captchaObj.onSuccess(function () {
		var validate = captchaObj.getValidate();
		$.ajax({
			url: "/user/checkin", // 进行二次验证
			type: "post",
			dataType: "json",
			data: {
				// 二次验证所需的三个值
				geetest_challenge: validate.geetest_challenge,
				geetest_validate: validate.geetest_validate,
				geetest_seccode: validate.geetest_seccode
			},
			success: function (data) {
				if (data.ret) {
					$("#checkin-msg").html(data.msg);
					$("#checkin-btn").html(checkedmsgGE);
					$("#result").modal();
					$("#msg").html(data.msg);
					$('#remain').html(data.traffic);
					$('.bar.remain.color').css('width',(data.unflowtraffic-({$user->u}+{$user->d}))/data.unflowtraffic*100+'%');
				} else {
					$("#result").modal();
					$("#msg").html(data.msg);
				}
			},
			error: function (jqXHR) {
				$("#result").modal();
				$("#msg").html("发生错误：" + jqXHR.status);
			}
		});
	});
	// 弹出式需要绑定触发验证码弹出按钮
	//captchaObj.bindOn("#checkin")
	// 将验证码加到id为captcha的元素里
	captchaObj.appendTo("#popup-captcha");
	// 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
};

initGeetest({
	gt: "{$geetest_html->gt}",
	challenge: "{$geetest_html->challenge}",
	product: "popup", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
	offline: {if $geetest_html->success}0{else}1{/if} // 表示用户后台检测极验服务器是否宕机，与SDK配合，用户一般不需要关注
}, handlerPopup);



{/if}



</script>
{if $recaptcha_sitekey != null}<script src="https://recaptcha.net/recaptcha/api.js" async defer></script>{/if}
