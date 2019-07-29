<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>添加规则 &mdash; {$config["appName"]}</title>

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="/theme/malio/assets/modules/summernote/summernote-bs4.css">

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <div class="section-header-back">
              <a href="/user/relay" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>添加规则</h1>
          </div>

          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="form-group">
                      <label>起源节点</label>
                      <select class="form-control form-control-sm">
                        {foreach $source_nodes as $source_node}
                        <option>{$source_node->name}</option>
                        {/foreach}
                      </select>
                    </div>
                    <div class="form-group">
                      <label>目标节点</label>
                      <select class="form-control form-control-sm">
                        {foreach $dist_nodes as $dist_node}
                        <option>{$dist_node->name}</option>
                        {/foreach}
                      </select>
                    </div>
                    <div class="form-group">
                      <label>端口</label>
                      <select class="form-control form-control-sm">
                        {foreach $ports as $port}
                        <option>{$port}</option>
                        {/foreach}
                      </select>
                    </div>
                    <div class="form-group">
                      <label>优先级</label>
                      <input type="text" class="form-control">
                    </div>
                  </div>
                  <div class="card-footer bg-whitesmoke text-md-right">
                    <button class="btn btn-primary" id="save-btn">确定添加</button>
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

  <!-- JS Libraies -->
  <script src="/theme/malio/assets/modules/summernote/summernote-bs4.js"></script>

</body>

</html>