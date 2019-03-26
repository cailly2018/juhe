<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
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
        <form class="form-inline" method="post"  action="/Pay_index.html">
            <div class="row">
                <div class="input-field col s12 center">
                    <h4>充值中心</h4>
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s6" style="padding-left: 50px;">
                    <select name="bankname" onclick="doCheck(this)" id="bankname" style="width: 160px;">
                        <option value="">请选择开户行</option>
                        <?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vobank): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vobank["bankname"]); ?>" <?php if($vobank["bankname"] < 中国农业银行 ): ?>selected<?php endif; ?> ><?php echo ($vobank["bankname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s12">
                    <i class="prefix">￥</i>
                    <input id="pay_amount" name="pay_amount" value="10"  type="text" onkeyup="value=value.replace(/[^\d\.]/g,'')"onblur="value=value.replace(/[^\d\.]/g,'')">
                    <label for="pay_amount">金额</label>
                </div>
            </div>

            <?php if(is_array($products)): $key = 0; $__LIST__ = $products;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$product): $mod = ($key % 2 );++$key;?><div class="input-field col s12">
                        <input name="pay_bankcode" onclick="register_check()"  type="radio" checked value="<?php echo ($product['id']); ?>" />
                        <span style="padding-left: 35px; "><?php echo ($product["name"]); ?></span>
                    </div><?php endforeach; endif; else: echo "" ;endif; ?>
            <div class="input-field col s12">
                <?php
 foreach ($native as $key => $val) { echo '<input type="hidden" id="' . $key . '" name="' . $key . '" value="' . $val . '">'; } ?>
                <input type="hidden" name="pay_md5sign"  id="pay_md5sign" value="">

                <button type="submit"  class="btn waves-effect waves-light col s12" style="border-radius:5px;">充值</a>
                </button>
            </div>
            <div class="input-field col s12">
                <p class="margin center medium-small sign-up">
                </p>
            </div>
        </form>
    </div>
</div>

<script>

    window.onload=function(){
        //监听金额的变化
        var otxt=document.getElementById("pay_amount");
        otxt.onkeyup=function(){
            if(this.value){
                register_check();
            }
        }
    }
    function register_check(){
        var  data = {};
        data.pay_memberid    = $('#pay_memberid').val();
        data.pay_orderid     = $('#pay_orderid').val();
        data.pay_amount      = $('#pay_amount').val();
        data.pay_applydate   = $('#pay_applydate').val();
        data.pay_bankcode    = $("input[name='pay_bankcode']:checked").val();
        data.pay_notifyurl   =$('#pay_notifyurl').val();
        data.pay_callbackurl =$('#pay_callbackurl').val();
        data.banknumber =$('#banknumber').val();
        data.bankname =$('#bankname').val();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/Pay_Charges_checkmd5str.html",
            data: data,
            success: function (datas) {
                $('#pay_md5sign').val(datas);

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