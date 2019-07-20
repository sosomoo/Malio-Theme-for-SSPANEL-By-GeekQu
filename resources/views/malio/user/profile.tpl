<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>我的账号 &mdash; {$config["appName"]}</title>

  <style>
    .card-large-icons p {
      font-weight: 400;
    }
    .card-large-icons {
      width: 100%;
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
            <h1>我的账号</h1>
          </div>
          <div class="section-body">
            <h2 class="section-title">Hi, {$user->user_name}!</h2>
            <p class="section-lead">
              {$user->email}
            </p>
            <div class="row mt-sm-4">
              <div class="col-lg-6">
                <div class="card card-large-icons">
                  <div class="card-icon bg-primary text-white">
                    <i class="fas fa-lock"></i>
                  </div>
                  <div class="card-body">
                    <h4>修改密码</h4>
                    <p>定期修改为高强度密码以保护您的账号</p>
                    <a href="##" class="card-cta" data-toggle="modal" data-target="#change-password-modal">立即修改 <i class="fas fa-chevron-right"></i></a>
                  </div>
                </div>
              </div>
              {if $malio_config['enable_telegram'] == true}
              <div class="col-lg-6">
                <div class="card card-large-icons">
                  <div class="card-icon bg-primary text-white">
                    <i class="fab fa-telegram-plane"></i>
                  </div>
                  <div class="card-body">
                    <h4>绑定 Telegram</h4>
                    {if $user->telegram_id == 0}
                    <p>绑定后可使用 Telegram 快速登录网站和机器人 <a href="https://t.me/{$telegram_bot}" target="blank">@{$telegram_bot}</a></p>
                    {else}
                    <p>当前绑定 Telegram 账号<a href="https://t.me/{$user->im_value}" target="blank">@{$user->im_value}</a></p>
                    {/if}
                    <a href="##" class="card-cta" data-toggle="modal" data-target="#telegram-modal">{if $user->telegram_id == 0}立即绑定{else}绑定其他账号{/if} <i class="fas fa-chevron-right"></i></a>
                  </div>
                </div>
              </div>
              {/if}
              {if $malio_config['enable_2fa'] == true}
              <div class="col-lg-6">
                <div class="card card-large-icons">
                  <div class="card-icon bg-primary text-white">
                    <i class="fas fa-shield-alt"></i>
                  </div>
                  <div class="card-body">
                    <h4>二步验证</h4>
                    {if $user->ga_enable==1}
                    <p>您的账号已开启二步验证</p>
                    <a href="##" id="2fa-off" class="card-cta">关闭二步验证 <i class="fas fa-chevron-right"></i></a>
                    {else}
                    <p>为您的帐号添加一道额外的安全保障</p>
                    <a href="##" class="card-cta" data-toggle="modal" data-target="#ga-modal">立即开启 <i class="fas fa-chevron-right"></i></a>
                    {/if}
                  </div>
                </div>
              </div>
              {/if}
              {if $malio_config['enable_delete'] == true}
              <div class="col-lg-6">
                <div class="card card-large-icons">
                  <div class="card-icon bg-primary text-white">
                    <i class="fas fa-skull"></i>
                  </div>
                  <div class="card-body">
                    <h4>删除账号</h4>
                    <p>您的所有数据都会被删除，无法找回</p>
                    <a href="##" class="card-cta"  data-toggle="modal" data-target="#kill-modal">申请删除 <i class="fas fa-chevron-right"></i></a>
                  </div>
                </div>
              </div>
              {/if}
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>最近五分钟使用IP</h4>
                  </div>
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">IP</th>
                          <th scope="col">归属地</th>
                        </tr>
                      </thead>
                      <tbody>
                        {if empty($userip)}
                        <tr>
                          <td colspan="2"><strong>最近五分钟未使用服务</strong></td>
                        </tr>
                        {else}
                        {foreach $userip as $single=>$location}
                        <tr>
                          <td>{$single}</td>
                          <td>{$location}</td>
                        </tr>
                        {/foreach}
                        {/if}
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="card">
                  <div class="card-header">
                    <h4>最近十次登录IP</h4>
                  </div>
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">IP</th>
                          <th scope="col">归属地</th>
                        </tr>
                      </thead>
                      <tbody>
                        {foreach $userloginip as $single=>$location}
                        <tr>
                          <td>{$single}</td>
                          <td>{$location}</td>
                        </tr>
                        {/foreach}
                      </tbody>
                    </table>
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
  <script src="//cdn.jsdelivr.net/npm/jquery-qrcode2@1.0.0/dist/jquery-qrcode.min.js"></script>
</body>

<div class="modal fade" tabindex="-1" role="dialog" id="change-password-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">修改账号密码</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>原密码</label>
            <input id="oldpwd" type="password" class="form-control">
          </div>
          <div class="form-group">
            <label>新密码</label>
            <input id="pwd" type="password" class="form-control">
          </div>
          <div class="form-group">
            <label>再次输入新密码</label>
            <input id="repwd" type="password" class="form-control">
          </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="passwordConfirm()">确定</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>

{if $malio_config['enable_delete'] == true}
<div class="modal fade" tabindex="-1" role="dialog" id="kill-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">删除账号</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <div class="modal-body">
          <p class="text-danger">注意：您的所有数据都会被删除，无法找回。</p>
          <div class="form-group">
            <label>请输入账号登录密码确认</label>
            <input id="passwd" type="password" class="form-control">
          </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="killConfirm()">确定</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
{/if}

{if $malio_config['enable_telegram'] == true}
<div class="modal fade" tabindex="-1" role="dialog" id="telegram-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">绑定 Telegram 账号</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>将下面的二维码复制或截图发送给 Telegram 机器人 <a href="https://t.me/{$telegram_bot}" target="blank">@{$telegram_bot}</a></p>
        <div id="telegram-qr" style="text-align: center"></div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
<script>
  jQuery('#telegram-qr').qrcode({
    "text": 'mod://bind/{$bind_token}'
  });
</script>
{/if}

{if $malio_config['enable_2fa'] == true}
<div class="modal fade" tabindex="-1" role="dialog" id="ga-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">开启二步验证</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>请使用二步验证APP扫描二维码，推荐使用 Google Authenticator</p>
        <div id="ga-qr" style="text-align: center"></div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="twofaNext()">下一步</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="ga-setp-2-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">开启二步验证</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>请输入二步验证APP上的6位验证码</p>
        <div class="form-group">
          <label>6位验证码</label>
          <input id="ga-code" type="number" class="form-control">
        </div>
      </div>
      <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-primary" onclick="twofaConfirm()">开启</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>

<script>
  jQuery('#ga-qr').qrcode({
    "text": '{$user->getGAurl()}'
  });
</script>
{/if}
</html>