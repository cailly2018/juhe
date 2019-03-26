<?php if (!defined('THINK_PATH')) exit();?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="msapplication-tap-highlight" content="no">
  <meta name="description" content="拍拍支付贷">
  <meta name="keywords" content="拍拍支付贷">
  <title>拍拍支付贷</title>
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
      <form class="login-form" id="from">
        <div class="row">
          <div class="input-field col s12 center">
            <h4>充值中心</h4>
          
          </div>
        </div>
        <div class="row margin">
          <div class="input-field col s12">
            <i class=" prefix"></i>
            <input id="member" name="member" type="text">
            <label for="member" class="center-align">订单号</label>
          </div>
        </div>
        <div class="row margin">
          <div class="input-field col s12">
            <i class=" prefix"></i>
            <input id="rmember" name="rmember" type="text">
            <label for="rmember" class="center-align">确认订单号</label>
          </div>
        </div>
        <div class="row margin">
          <div class="input-field col s12">
            <i class=" prefix"></i>
            <input id="rmember" name="rmember" type="text">
            <label for="rmember" class="center-align">会员账号</label>
          </div>
        </div>
        <div class="row margin">
          <div class="input-field col s12">
            <i class="prefix">￥</i>
            <input id="money" name="money" type="text">
            <label for="money">金额</label>
          </div>
        </div>
         <div class="row margin">
            <div class="input-field col s12">
              <i class="prefix"></i>
                 <input name="Fruit" type="radio" value="" />苹果 
                 <br/>
                 <input name="Fruit" type="radio" value="" />桃子 
            </div>
         </div>
          <div class="input-field col s12">
            <a href="#" onclick="register_check()" class="btn waves-effect waves-light col s12" style="border-radius:5px;">充值</a>
          </div>
          <div class="input-field col s12">
            <p class="margin center medium-small sign-up"> 
            </p>
          </div>
        </div>
      </form>
    </div>
  </div>



  <!-- ================================================
    Scripts
    ================================================ -->
  <script>
 
  function register_check(){
      $.ajax({
          type: "POST",
          dataType: "json",
          url: "http://www.nutbe.cn/index/user/registerCheck.do",
          data: $('#from').serialize(),
          success: function (data) {
              console.log(data);
              if(data.code == '200'){
                  //swal("注册提示", data.msg, "success");
                  location.href="http://www.nutbe.cn/index/user/phoneCheck.do";
              }else{
                  if(data.code == '-18'){
                      play(['FILE_CACHE/download/sound/会员名过短1.mp3']);
                  }
                  if(data.code == '-19'){
                      play(['FILE_CACHE/download/sound/用户名重复2.mp3','FILE_CACHE/download/sound/用户名重复1.mp3']);
                  }
                  if(data.code == '-23'){
                      play(['FILE_CACHE/download/sound/六位密码1.mp3']);
                  }
                  if(data.code == '-20'){
                      play(['FILE_CACHE/download/sound/第二次密码错误1.mp3']);
                  }
                  if(data.code == '-21'){
                          play(['FILE_CACHE/download/sound/手机号错误1.mp3']);
                  }
                  if(data.code == '-22'){
                      play(['FILE_CACHE/download/sound/手机号已注册1.mp3']);
                  }
               
                  swal("注册提示", data.msg, "error");
              }
          },
          error: function(data) {
              alert("error:"+data.responseText);
           }
  });
  }
  </script>

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

</body>
</html>