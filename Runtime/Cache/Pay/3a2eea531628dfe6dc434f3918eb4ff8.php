<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="description" content="支付页面">
    <meta name="keywords" content="支付页面">
    <title>欢乐付</title>
    <!-- CORE CSS-->
    <link href="<?php echo ($siteurl); ?>web/static/css/materialize.min.css" type="text/css" rel="stylesheet">
    <link href="<?php echo ($siteurl); ?>web/static/css/style.min.css" type="text/css" rel="stylesheet">
    <!-- Custome CSS-->
    <link href="<?php echo ($siteurl); ?>web/static/css/custom/custom.min.css" type="text/css" rel="stylesheet">
    <link href="<?php echo ($siteurl); ?>web/static/css/layouts/page-center.css" type="text/css" rel="stylesheet">
    <!-- INCLUDED PLUGIN CSS ON THIS PAGE -->
    <link href="<?php echo ($siteurl); ?>web/static/js/plugins/prism/prism.css" type="text/css" rel="stylesheet">
    <link href="<?php echo ($siteurl); ?>web/static/js/plugins/perfect-scrollbar/perfect-scrollbar.css" type="text/css" rel="stylesheet">

    <link href="<?php echo ($siteurl); ?>web/static/js/plugins/sweetalert/sweetalert.css" type="text/css" rel="stylesheet" media="screen,projection">
    <link rel="icon" href="http://www.nutbe.cn/favicon.ico" />
</head>
<body class="cyan">
<!-- Start Page Loading -->
<div id="loader-wrapper">
    <div id="loader"></div>
    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>
</div>
<div id="login-page" class="row">
    <div class="col s12 z-depth-4 card-panel"  style="border-radius:8px;">
        <form class="form-inline" method="post"  action="/Pay_index.html">
            <div class="row">
                <div class="input-field col s12 center">
                    <h6>收银台</h6>
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s6" >
                    <img src="<?php echo ($imgurl); ?>" id="ewm" style="width:250px; height:250px;">
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s12">
                    <span style="color: red"><?php echo ($msg); ?></span>
                 </div>
                <div class="input-field col s12">
                    <span > <?php echo ($msg1); ?></span>
                </div>
            </div>

            <div class="input-field col s12">
                <p class="margin center medium-small sign-up">
                </p>
            </div>
        </form>
    </div>
</div>



<!-- jQuery Library -->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins/jquery-1.11.2.min.js"></script>
<!--materialize js-->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/materialize.min.js"></script>
<!--prism-->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins/prism/prism.js"></script>
<!--scrollbar-->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<!--sweetalert -->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins/sweetalert/sweetalert.min.js"></script>
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins.min.js"></script>
<!--custom-script.js - Add your own theme custom JS-->
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/custom-script.js"></script>
<script type="text/javascript" src="<?php echo ($siteurl); ?>web/static/js/plugins/formatter/jquery.formatter.min.js"></script>
<script>
    $(document).ready(function () {
    /*    var r = window.setInterval(function () {
            $.ajax({
                type: 'POST',
                url: '<?php echo U("Pay/checkstatus");?>',
                data: "orderid=<?php echo ($ddh); ?>",
                dataType: 'json',
                success: function (str) {
                    if (str.status == "ok") {
                        $("#ewm").attr("src", "Uploads/successpay.png");
                        window.clearInterval(r);
                        window.open(str.callback);
                    }
                }
            });
        }, 2000);*/
    });
</script>
</body>
</html>