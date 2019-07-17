<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>邀请注册 &mdash; {$config["appName"]}</title>

  <style>
    .hero.hero-bg-image::before {
      background-color: rgba(0, 0, 0, 0.4);
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>邀请注册</h1>
          </div>
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card card-hero">
                  <div class="card-header" style="border-radius: 3px;box-shadow: 0 2px 6px #ffc36f;border: none;background-image: linear-gradient(to bottom, #ffa425, #ffc36f) ">
                    <div class="card-icon">
                      <i class="fas fa-laugh-squint" style="color:#ffc36f"></i>
                    </div>
                    <h4 class="mt-2">¥ {$paybacks_sum}</h4>
                    <div class="card-description">累计获得返利金额</div>
                  </div>
                </div>

                <div class="hero text-white hero-bg-image hero-bg-parallax mb-4" data-background="/theme/malio/img/soroush-karimi-crjPrExvShc-unsplash.jpg" style="background-image: url(&quot;/theme/malio/assets/img/soroush-karimi-crjPrExvShc-unsplash.jpg&quot;);">
                  <div class="hero-inner">
                    <h2>当您邀请朋友注册时</h2>
                    {if $config['code_payback'] > 0}
                    <p class="lead">每次TA充值时，您都会获得TA的充值金额 <b>{$config["code_payback"]}%</b> 的返利</p>
                    {/if}
                    {if $config['invite_gift'] > 0}
                    <p class="lead">您将一次性获得 <b>{$config["invite_gift"]}GB</b> 流量奖励</p>
                    {/if}
                    {if $config['invite_get_money'] > 0}
                    <p class="lead">TA将获得 <b>{$config["invite_get_money"]}</b> 元奖励作为初始资金</p>
                    {/if}
                    <div class="mt-4">
                      <a href="##" data-clipboard-text="{$config["baseUrl"]}/auth/register?code={$code->code}" class="btn btn-outline-white btn-lg btn-round btn-icon icon-left copy-text"><i class="far fa-copy"></i> 复制邀请链接</a>
                      {if $user->invite_num >= 0}<div class="mt-2 ml-2" style="font-size: 0.8em;color: rgba(255, 255, 255, 0.486)">剩余 {$user->invite_num} 次邀请次数</div>{/if}
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header">
                    <h4>邀请链接设置</h4>
                  </div>
                  <div class="card-body">
                    <div class="buttons">
                      {if $config['invite_price']>=0}
                      <a href="##" class="btn btn-primary" data-toggle="modal" data-target="#buy-invite-modal">购买邀请次数</a>
                      {/if}
                      {if $config['custom_invite_price']>=0}
                      <a href="##" class="btn btn-primary"  data-toggle="modal" data-target="#custom-invite-modal">定制邀请链接</a>
                      {/if}
                      <button class="btn btn-primary" data-confirm="提示|确定要要重置邀请链接吗？点击确定后会重置并自动刷新本页。" data-confirm-yes="location.href='/user/inviteurl_reset'">重置邀请链接</button>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header">
                    <h4>返利记录</h4>
                  </div>
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">被邀请用户ID</th>
                          <th scope="col">获得返利</th>
                        </tr>
                      </thead>
                      <tbody>
                        {if count($paybacks) == 0}
                        <tr>
                            <td colspan="3"><strong>无返利记录</strong></td>
                        </tr>
                        {else}
                        {foreach $paybacks as $payback}
                        <tr>
                          <td>{$payback->id}</td>
                          <td>{$payback->userid}</td>
                          <td>{$payback->ref_get} 元</td>
                        </tr>
                        {/foreach}
                        {/if}
                      </tbody>
                    </table>
                    <div class="pagination-render float-right">
                      {$paybacks->render()}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      {include file='user/footer.tpl'}
    </div>
  </div>

  {include file='user/scripts.tpl'}
</body>

{if $config['invite_price']>=0}
<div class="modal fade" tabindex="-1" role="dialog" id="buy-invite-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">购买邀请次数</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <p>价格为{$config['invite_price']}元/次</p>
          <label>请输入想要购买的次数</label>
          <input id="buy-invite-num" type="number" class="form-control">
        </div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="buyInvite()">确定</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
{/if}

{if $config['custom_invite_price']>=0}
<div class="modal fade" tabindex="-1" role="dialog" id="custom-invite-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">定制邀请链接</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <p>价格为{$config['custom_invite_price']}元/次</p>
          <label>输入链接后缀</label>
          <input id="custom-invite-link" type="text" class="form-control">
        </div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="customInviteConfirm()">确定</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
{/if}

</html>