<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta charset="UTF-8">
  <title>{$config["appName"]}</title>
  <link rel="stylesheet" href="//unpkg.com/docsify/themes/vue.css">
</head>
<body>
  <nav>
    <a href="/">回到主页</a>
      <li><a href="/user/">用户中心</a>
        <ul>
          <li><a href="/user/edit">资料编辑</a></li>
          <li><a href="/user/node">节点中心</a></li>
          <li><a href="/user/code">充值捐赠</a></li>
          <li><a href="/user/shop">套餐购买</a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <div id="docs">加载中...</div>
  <script>
    window.$docsify = {
      name: '文档中心',
      alias: {
            '/.*/_sidebar.md': '/_sidebar.md'
      },
      basePath: '暂时留空',
      auto2top: true,
      loadSidebar: true,
      autoHeader: true,
      homepage: 'index.md',
      nameLink: '/doc/',
      el: '#docs'
      //...
    }
  </script>
  <script src="//unpkg.com/docsify/lib/docsify.min.js"></script>
  <script src="//unpkg.com/docsify/lib/plugins/emoji.js"></script>
</body>
</html>