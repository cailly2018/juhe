<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="description" content="预签约">
    <meta name="keywords" content="预签约">
    <title>预签约</title>
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
        <form id='editForm' class="form-inline" method="post">
            <div class="row">
                <div class="input-field col s12 center">
                    <h5>预签约</h5>
                </div>
            </div>
          <!--  <div class="row margin">
                <div class="input-field col s10" style="padding-left: 50px;">
                    <select name="bankname" onclick="doCheck(this)" id="bankname" style="width: 160px;">
                        <option value="">请选择开户行</option>
                        <?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vobank): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vobank["bankname"]); ?>"><?php echo ($vobank["bankname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

            </div>-->
            <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">P</i>
                    <input id="card_no" name="card_no" value="622908333044406998"  type="text" onkeyup="value=value.replace(/[^\d\.]/g,'')"onblur="value=value.replace(/[^\d\.]/g,'')">
                    <label for="card_no">银行卡</label>
                </div>
            </div>
          <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">P</i>
                    <input id="owner" name="owner" value="蔡晓丽"  type="text" >
                    <label for="owner">开户名称</label>
                </div>
            </div>

            <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">P</i>
                    <input id="cvv2" name="cvv2" value=""  type="text" >
                    <label for="cvv2">安全码</label>
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">P</i>
                    <input id="cert_no" name="cert_no" value="450521198905127823"  type="text" >
                    <label for="cert_no">身份证号</label>
                </div>
            </div>

            <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">P</i>
                    <input id="validthru" name="validthru" value=""  type="text" >
                    <label for="validthru">有效日期</label>
                </div>
            </div>
            <div class="row margin">
                <div class="input-field col s8">
                    <i class="prefix">P</i>
                    <input id="phone" name="phone" value="13724269315"  type="text" onkeyup="value=value.replace(/[^\d\.]/g,'')"onblur="value=value.replace(/[^\d\.]/g,'')">
                    <label for="phone">手机号码</label>
                </div>

            </div>
            <div class="row margin">
                <div class="input-field col s10">
                    <i class="prefix">￥</i>
                    <input id="pay_amount" name="pay_amount" value="1"  type="text" onkeyup="value=value.replace(/[^\d\.]/g,'')"onblur="value=value.replace(/[^\d\.]/g,'')">
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
                <button type="button" onclick="register_check()" class="btn waves-effect waves-light col s12"  style="border-radius:5px; margin-top: 25px;">发送验证码</a>
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
               // register_check();
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
        data.owner =$('#owner').val();
        data.cvv2 =$('#cvv2').val();
        data.cert_no =$('#cert_no').val();
        data.validthru =$('#validthru').val();
        data.phone =$('#phone').val();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/Pay_Charges_pay.html",
            data: data,
            success: function (datas) {
                 $('#pay_md5sign').val(datas.sign);
                authentication( data.pay_orderid);
            }
        },'json');
   }
   function authentication(pay_orderid) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/Pay_index.html",
            data: $('#editForm').serialize(),
            success: function (res) {
                if(res.status == 0){
                    location.href="/Pay_Charges_testqianyue2_mid_"+pay_orderid+".html"
                }else {
                    alert(res.msg);
                }
            }
        },'json');
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