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
<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <!--条件查询-->
            <div class="ibox-title">
                <h5>保证金明细</h5>
                <div class="ibox-tools">
                    <i class="layui-icon" onclick="location.replace(location.href);" title="刷新"
                       style="cursor:pointer;">ဂ</i>
                </div>
            </div>
            <!--条件查询-->
            <div class="ibox-content">
                <form class="layui-form" action="" method="get" autocomplete="off" id="orderform">
                    <input type="hidden" name="m" value="User">
                    <input type="hidden" name="c" value="Account">
                    <input type="hidden" name="a" value="complaintsDeposit">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <input type="text" name="orderid" autocomplete="off" placeholder="请输入订单号"
                                       class="layui-input" value="<?php echo ($_GET['orderid']); ?>">
                            </div>
                            <div class="layui-input-inline" style="width: 300px;">
                                <input type="text" class="layui-input" name="createtime" id="createtime"
                                       placeholder="起始时间" value="<?php echo (urldecode($_GET['createtime'])); ?>">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option <?php if($_GET[status] != '' && $_GET[status] == 0): ?>selected<?php endif; ?> value="0">待解冻</option>
                                    <option <?php if($_GET[status] == 1): ?>selected<?php endif; ?> value="1">已解冻</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button type="submit" class="layui-btn"><span
                                    class="glyphicon glyphicon-search"></span> 搜索
                            </button>
                        </div>
                    </div>
                </form>
                <blockquote class="layui-elem-quote" style="font-size:14px;padding;8px;">
                    保证金总额：<span class="label label-info"><?php echo ($stats["all"]); ?> 元</span>
                    已解冻金额：<span class="label label-warning-light"><?php echo ($stats["freezed"]); ?> 元</span>
                    待解冻金额：<span class="label label-danger"><?php echo ($stats["unfreezed"]); ?> 元</span>

                </blockquote>
                <!--交易列表-->
                <table class="layui-table" lay-data="{width:'100%',limit:<?php echo ($rows); ?>,id:'userData'}">
                    <thead>
                    <tr>
                        <th lay-data="{field:'key',width:60}"></th>
                        <th lay-data="{field:'out_trade_id', width:240}">订单号</th>
                        <th lay-data="{field:'freeze_money', width:100,style:'color:#060;'}">金额</th>
                        <th lay-data="{field:'status', width:100,style:'color:#060;'}">状态</th>
                        <th lay-data="{field:'unfreeze_time', width:200}">计划解冻时间</th>
                        <th lay-data="{field:'real_unfreeze_time', width:200}">实际解冻时间</th>
                        <th lay-data="{field:'create_at', width:200}">冻结时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($vo["id"]); ?></td>
                            <td style="text-align:center;"><?php echo ($vo["out_trade_id"]); ?></td>
                            <td style="text-align:center; color:#090;"><?php echo ($vo["freeze_money"]); ?></td>
                            <td style="text-align:center;">
                                <?php switch($vo["status"]): case "1": ?><span style="color:green">已解冻</span><?php break;?>
                                    <?php case "0": ?><span style="color:red">待解冻</span><?php break; endswitch;?>
                            </td>
                            <td style="text-align:center;"><?php echo (date('Y-m-d H:i:s',$vo["unfreeze_time"])); ?></td>
                            <td style="text-align:center;"><?php if($vo[real_unfreeze_time] > 0): echo (date('Y-m-d H:i:s',$vo["real_unfreeze_time"])); endif; ?></td>
                            <td style="text-align:center;"><?php echo (date('Y-m-d H:i:s',$vo["create_at"])); ?></td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <!--交易列表-->
               <div class="page">
                    
                    <form class="layui-form" action="" method="get" id="pageForm" autocomplete="off">     
                        <?php echo ($page); ?>                            
                        <select name="rows" style="height: 32px;" id="pageList" lay-ignore >
                            <option value="">显示条数</option>
                            <option <?php if($_GET[rows] != '' && $_GET[rows] == 15): ?>selected<?php endif; ?> value="15">15条</option>
                            <option <?php if($_GET[rows] == 30): ?>selected<?php endif; ?> value="30">30条</option>
                            <option <?php if($_GET[rows] == 50): ?>selected<?php endif; ?> value="50">50条</option>
                            <option <?php if($_GET[rows] == 80): ?>selected<?php endif; ?> value="80">80条</option>
                            <option <?php if($_GET[rows] == 100): ?>selected<?php endif; ?> value="100">100条</option>
                            <option <?php if($_GET[rows] == 1000): ?>selected<?php endif; ?> value="1000">1000条</option>
                        </select>
                    </form>
                  
                </div> 
            </div>
        </div>
    </div>
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
    layui.use(['laydate', 'laypage', 'layer', 'table', 'form'], function() {
        var laydate = layui.laydate //日期
            , laypage = layui.laypage //分页
            ,layer = layui.layer //弹层
            ,form = layui.form //表单
            , table = layui.table; //表格
        //日期时间范围
        laydate.render({
            elem: '#createtime'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });
    });
    $('#export').on('click',function(){
        window.location.href
            ="<?php echo U('User/Account/exceldownload',array('orderid'=>$_GET[orderid],'createtime'=>$_GET[createtime],'tongdao'=>$_GET[tongdao],'bank'=>$_GET[bank]));?>";
    });
    $('#pageList').change(function(){
        $('#pageForm').submit();
    });
</script>
</body>
</html>