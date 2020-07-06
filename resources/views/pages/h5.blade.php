<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>免押系统 - 信用就是金钱</title>
  <script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.inc.min.js"></script>
  <!-- 样式 -->
  <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div>
  <div class="container">
    <button id="J_btn" class="btn btn-default">支付</button>
  </div>
</div>
<!-- JS 脚本 -->
<script>
  var btn = document.querySelector('#J_btn');
  btn.addEventListener('click', function () {
    ap.tradePay({
      orderStr: 'timestamp={{ urlencode(date("Y-m-d H:i:s")) }}&method=alipay.trade.app.pay&app_id={{ config('services.alipay.merchant_id') }}'
    }, function (res) {
      ap.alert(res.resultCode);
    });
  });
</script>
</body>
</html>
