<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title><?php echo ($sitename); ?>---用户管理中心</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/Front/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Public/Front/css/font-awesome.min.css" rel="stylesheet">
    <link href="/Public/Front/css/animate.css" rel="stylesheet">
    <link href="/Public/Front/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Front/js/plugins/layui/css/layui.css">
    <style>
        .layui-form-label {width:110px;padding:4px}
        .layui-form-item .layui-form-checkbox[lay-skin="primary"]{margin-top:0;}
        .layui-form-switch {width:54px;margin-top:0px;}
    </style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated">
      <div class="ibox-content">
        <table class="layui-table">
          <tr><td>订单号：</td><td><strong class="text-danger"><?php echo ($order["out_trade_no"]); ?></strong></td></tr>
          <tr><td>金额：</td><td><strong class=""><?php echo ($order["money"]); ?></strong></td></tr>
          <tr><td>银行名称：</td><td><strong class="text-success"><?php echo ($order["bankname"]); ?></strong></td></tr>
          <tr><td>支行名称：</td><td><strong class="text-success"><?php echo ($order["subbranch"]); ?></strong></td></tr>
          <tr><td>开户名：</td><td><strong class="text-success"><?php echo ($order["accountname"]); ?></strong></td></tr>
          <tr><td>银行卡号：</td><td><strong class="text-success"><?php echo ($order["cardnumber"]); ?></strong></td></tr>
          <tr><td>省：</td><td><strong class="text-success"><?php echo ($order["province"]); ?></strong></td></tr>
          <tr><td>市：</td><td><strong class="text-success"><?php echo ($order["city"]); ?></strong></td></tr>
          <?php if($order[check_time] > 0): ?><tr><td>审核时间：</td><td><strong class="text-success"><?php echo (date('Y-m-d H:i:s', $order[check_time])); ?></strong></td></tr><?php endif; ?>
          <?php if(($order["reject_reason"]) != ""): ?><tr><td>驳回理由：</td><td><strong class="text-danger"><?php echo ($order["reject_reason"]); ?></strong></td></tr><?php endif; ?>
          <tr><td>审核状态：</td><td>
            <?php switch($order[check_status]): case "0": ?><strong class="text-warning">待处理</strong><?php break;?>
              <?php case "1": ?><strong class="text-success">审核通过</strong><?php break;?>
              <?php case "2": ?><strong class="text-danger">审核驳回</strong><?php break; endswitch;?>
          </td></tr>
          <?php if(($order[status]) != ""): ?><tr><td>代付状态：</td><td>
            <?php switch($order[status]): case "0": ?><strong class="text-warning">待处理</strong><?php break;?>
              <?php case "1": ?><strong class="text-warning">处理中</strong><?php break;?>
              <?php case "2": ?><strong class="text-success">成功</strong><?php break;?>
              <?php case "3": ?><strong class="text-danger">失败</strong><?php break; endswitch;?>
          </td></tr><?php endif; ?>
            <?php if(($order["memo"]) != ""): ?><tr><td>代付备注：</td><td><strong class="text-warning"><?php echo ($order["memo"]); ?></strong></td></tr><?php endif; ?>
        </table>
      </div>
</div>
<script src="/Public/Front/js/jquery.min.js"></script>
<script src="/Public/Front/js/bootstrap.min.js"></script>
<script src="/Public/Front/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/Public/Front/js/content.js"></script>
<script src="/Public/Front/js/plugins/layui/layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/x-layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/Util.js" charset="utf-8"></script>
<script>
layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function() {
        var laydate = layui.laydate //日期
            , laypage = layui.laypage //分页
            ,layer = layui.layer //弹层
            , table = layui.table; //表格
    });
</script>
</body>
</html>