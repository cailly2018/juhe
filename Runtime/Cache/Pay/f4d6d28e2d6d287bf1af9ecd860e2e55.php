<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>支付失败</title>
    <link rel="stylesheet" href="/Public/weui/weui.min.css">
    <link rel="stylesheet" href="/Public/weui/base.css">
</head>
<body>
<div class="weui-msg">
    <div class="weui-msg__icon-area"><i class="weui-icon-success-no-circle weui-icon_msg" style="color: red;"></i></div>
    <div class="weui-msg__text-area">
        <h2 class="weui-msg__title">支付失败</h2>
        <p class="weui-msg__desc">订单号：<?php echo ($cache["orderid"]); ?>,支付金额<?php echo ($cache["amount"]); ?>元</p>
    </div>
    <div class="weui-msg__opr-area">
        <p class="weui-btn-area">
            <a href="<?php echo ($goback); ?>" class="weui-btn weui-btn_warn" >继续支付</a>
        </p>
    </div>
    <div class="weui-msg__extra-area">
        <div class="weui-footer">
            <p class="weui-footer__links">
                <a href="javascript:void(0);" class="weui-footer__link"></a>
            </p>
            <p class="weui-footer__text"></p>
        </div>
    </div>
</div>
</body>
</html>