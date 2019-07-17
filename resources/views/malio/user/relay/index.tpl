<!DOCTYPE html>
<html lang="en">

<head>
  {include file='user/head.tpl'}

  <title>中转规则 &mdash; {$config["appName"]}</title>

</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      {include file='user/navbar.tpl'}

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>中转规则</h1>
            <div class="section-header-breadcrumb">
              <a href="/user/relay/create" class="btn btn-primary">添加规则</a>
            </div>
          </div>
          <div class="section-body">
            <h2 class="section-title">说明</h2>
            <p class="section-lead">
              中转规则一般由中国中转至其他国外节点<br>
              请设置端口号为您自己的端口<br>
              优先级越大，代表其在多个符合条件的规则并存时会被优先采用，当优先级一致时，先添加的规则会被采用<br>
              节点不设置中转时，这个节点就可以当作一个普通的节点来做代理使用<br>
            </p>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>所有规则</h4>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12 col-sm-12 col-md-2">
                        <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                          <li class="nav-item">
                            <a class="nav-link active show" id="home-tab4" data-toggle="tab" href="#home4" role="tab" aria-controls="home" aria-selected="true">规则表</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" id="profile-tab4" data-toggle="tab" href="#profile4" role="tab" aria-controls="profile" aria-selected="false">链路表</a>
                          </li>
                        </ul>
                      </div>
                      <div class="col-12 col-sm-12 col-md-8">
                        <div class="tab-content no-padding" id="myTab2Content">
                          <div class="tab-pane fade active show" id="home4" role="tabpanel" aria-labelledby="home-tab4">
                            <div class="table-responsive">
                              <table class="table table-striped">
                                <tbody>
                                  <tr>
                                    <th>起源节点</th>
                                    <th>目标节点</th>
                                    <th>端口</th>
                                    <th>优先级</th>
                                    <th>操作</th>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: Introduction
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-success">处理中</div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              {$rules->render()}
                            </div>
                          </div>
                          <div class="tab-pane fade" id="profile4" role="tabpanel" aria-labelledby="profile-tab4">
                            <div class="table-responsive">
                              <table class="table table-striped">
                                <tbody>
                                  <tr>
                                    <th>端口</th>
                                    <th>始发节点</th>
                                    <th>终点节点</th>
                                    <th>途径节点</th>
                                    <th>状态</th>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: Introduction
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#">回复</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-success">处理中</div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: Installing
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#">编辑</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-warning">已回复</div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: MVC
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#">编辑</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-secondary">已关闭</div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: CRUD
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#">编辑</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-secondary">已关闭</div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Laravel 5 Tutorial: Deployment
                                      <div class="table-links">
                                        <a href="#">查看</a>
                                        <div class="bullet"></div>
                                        <a href="#">编辑</a>
                                        <div class="bullet"></div>
                                        <a href="#" class="text-danger">关闭</a>
                                      </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td>
                                      <div class="badge badge-secondary">已关闭</div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              {$rules->render()}
                            </div>
                          </div>
                        </div>
                      </div>
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

</html>