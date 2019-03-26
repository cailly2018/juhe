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
                <h5>商户代付申请管理（通过API提交）</h5>
                <div class="ibox-tools">
                    <i class="layui-icon" onclick="location.replace(location.href);" title="刷新"
                       style="cursor:pointer;">ဂ</i>
                </div>
            </div>
            <!--条件查询-->
            <div class="ibox-content">
                <form class="layui-form" action="" method="get" autocomplete="off" id="orderform">
                    <input type="hidden" name="m" value="User">
                    <input type="hidden" name="c" value="Withdrawal">
                    <input type="hidden" name="a" value="check">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <input type="text" name="out_trade_no" autocomplete="off" placeholder="请输入订单号"
                                       class="layui-input" value="<?php echo ($_GET['out_trade_no']); ?>">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" name="accountname" autocomplete="off" placeholder="请输入开户名"
                                       class="layui-input" value="<?php echo ($_GET['accountname']); ?>">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" name="create_time" id="create_time"
                                       placeholder="申请时间" value="<?php echo ($_GET['create_time']); ?>">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" name="check_time" id="check_time"
                                       placeholder="审核时间" value="<?php echo ($_GET['check_time']); ?>">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <select name="check_status">
                                    <option value="">全部审核状态</option>
                                    <option value="0" <?php if($_GET['check_status'] == '0'): ?>selected<?php endif; ?>>待处理</option>
                                    <option value="1" <?php if($_GET['check_status'] == '1'): ?>selected<?php endif; ?>>审核通过</option>
                                    <option value="2" <?php if($_GET['check_status'] == '2'): ?>selected<?php endif; ?>>审核驳回</option>
                                </select>
                            </div>

                            <div class="layui-input-inline">
                                <select name="status">
                                    <option value="">全部代付状态</option>
                                    <option value="0" <?php if($_GET['status'] == '0'): ?>selected<?php endif; ?>>待处理</option>
                                    <option value="1" <?php if($_GET['status'] == '1'): ?>selected<?php endif; ?>>处理中</option>
                                    <option value="2" <?php if($_GET['status'] == '2'): ?>selected<?php endif; ?>>成功</option>
                                    <option value="3" <?php if($_GET['status'] == '3'): ?>selected<?php endif; ?>>失败</option>
                                </select>
                            </div>

                        <div class="layui-inline">
                            <button type="submit" class="layui-btn"><span
                                    class="glyphicon glyphicon-search"></span> 搜索
                            </button>
                        </div>
                    </div>
                        <p style="margin-bottom:10px">
                            <a href="javascript:;" id="checkAll" class="layui-btn layui-btn-sm layui-btn-danger">全选</a>
                            <a href="javascript:;" id="submitAllOrder" class="layui-btn layui-btn-sm layui-btn-info">批量通过</a>
                            <a href="javascript:;" id="rejectAllOrder" class="layui-btn layui-btn-sm layui-btn-danger">批量驳回</a>
                        </p>
                </form>
                <blockquote class="layui-elem-quote" style="font-size:14px;padding;8px;">
                    今日代付总金额：<span class="label label-info"><?php echo ($stat["totay_total"]); ?>元</span> 今日代付待审核总金额：<span class="label label-warning"><?php echo ($stat["totay_wait"]); ?>元</span>今日代付待审核笔数：<span class="label label-warning"><?php echo ($stat["totay_wait_count"]); ?>笔</span>
                    今日代付待平台审核总金额：<span class="label label-info"><?php echo ($stat["totay_platform_wait"]); ?>元</span> 今日代付待平台审核总笔数：<span class="label label-warning"><?php echo ($stat["totay_fail_count"]); ?>笔</span>
                    今日代付成功总金额：<span class="label label-info"><?php echo ($stat["totay_success_sum"]); ?>元</span> 今日代付成功总笔数：<span class="label label-info"><?php echo ($stat["totay_success_count"]); ?>笔</span>
                    今日代付失败总笔数：<span class="label label-danger"><?php echo ($stat["totay_fail_count"]); ?>笔</span>
                </blockquote>
                <!--交易列表-->
                <table class="layui-table" lay-data="{width:'100%',limit:<?php echo ($rows); ?>,id:'userData'}" id="tab">
                    <thead>
                    <tr>
                        <th lay-data="{field:'check' , width:60}"> </th>
                        <th lay-data="{field:'out_trade_no', width:200}">商户订单号</th>
                        <th lay-data="{field:'money', width:100,style:'color:#060;'}">金额</th>
                        <th lay-data="{field:'bankname', width:110}">银行名称</th>
                        <th lay-data="{field:'subbranch', width:100,style:'color:#060;'}">支行名称</th>
                        <th lay-data="{field:'accountname', width:110}">开户名</th>
                        <th lay-data="{field:'check_status', width:100}">审核状态</th>
                        <th lay-data="{field:'status', width:160}">代付状态</th>
                        <th lay-data="{field:'check_time', width:170}">处理时间</th>
                        <th lay-data="{field:'create_time', width:170}">申请时间</th>
                        <th lay-data="{field:'op',width:250}">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                            <td><input type="checkbox"  title="" value="<?php echo ($vo["id"]); ?>" class='checkIds' lay-skin="primary"></td>
                            <td><?php echo ($vo["out_trade_no"]); ?></td>
                            <td><?php echo ($vo["money"]); ?> 元</td>
                            <td><?php echo ($vo["bankname"]); ?></td>
                            <td><?php echo ($vo["subbranch"]); ?></td>
                            <td><?php echo ($vo["accountname"]); ?></td>
                            <td><?php switch($vo[check_status]): case "0": ?><strong class="text-danger">待处理</strong><?php break;?>
                                <?php case "1": ?><strong class="text-success">审核通过</strong><?php break;?>
                                <?php case "2": ?><strong class="text-warning">审核驳回</strong><?php break; endswitch;?></td>
                            <td>
                                <?php switch($vo[status]): case "0": ?><strong class="text-warning">待处理</strong><?php break;?>
                                    <?php case "1": ?><strong class="text-warning">处理中</strong><?php break;?>
                                    <?php case "2": ?><strong class="text-success">成功</strong><?php break;?>
                                    <?php case "3": ?><strong class="text-danger">失败</strong><?php break;?>
                                    <?php default: endswitch;?>
                            </td>
                            <td><?php if($vo[check_time] > 0): echo (date('Y-m-d H:i:s',$vo["check_time"])); endif; ?></td>
                            <td><?php echo (date('Y-m-d H:i:s',$vo["create_time"])); ?></td>
                            <td class="layui-input-inline">
                                <a class="layui-btn layui-btn-mini" onclick="javascript:order_view('代付订单号:<?php echo ($vo["out_trade_no"]); ?>','<?php echo U('User/Withdrawal/showDf',['id'=>$vo[id]]);?>',600,400)"" >查看</a>
                                <?php if($vo['check_status'] == 0): ?><a class="layui-btn layui-btn-warm layui-btn-mini"  onclick="df_op('代付订单号:<?php echo ($vo["out_trade_no"]); ?>','<?php echo U('User/Withdrawal/dfPass',['id'=>$vo[id]]);?>',510,280)">审核通过</a><?php endif; ?>
                                 <?php if($vo['check_status'] == 0 OR ($vo['check_status'] == 1 and $vo['status'] == 0)): ?><a class="layui-btn layui-btn-danger layui-btn-mini"  onclick="df_op('代付订单号:<?php echo ($vo["out_trade_no"]); ?>','<?php echo U('User/Withdrawal/dfReject',['id'=>$vo[id]]);?>',510,280)">审核驳回</a><?php endif; ?>
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <!--交易列表-->
                <div class="page">  
                    <form class="layui-form" action="" method="get" id="pageForm"  autocomplete="off">
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
            elem: '#create_time'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });
        //日期时间范围
        laydate.render({
            elem: '#check_time'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });

    });
    /*订单-查看*/
    function order_view(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
    function df_op(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
    $('#checkAll').on('click', function(){
        var child = $('table').next().find('tbody input[type="checkbox"]');  ;
        child.each(function(){
            if($(this).prop("disabled")==false){
                $(this).attr('checked', true);
                $(this).next('.layui-form-checkbox').addClass('layui-form-checked');
            }
        });
    });
    $('#submitAllOrder').on('click', function(){
        var id = '';
        $('.checkIds').each(function(){
            var _this = $(this);
            if( _this.is(':checked')  ){
                id = id + _this.val() + '_';
            }
        });
        if(id != '') {
            id=id.substring(0,id.length-1);
        }
        if(id){
            var url = "<?php echo U('/User/Withdrawal/dfPassBatch');?>"+"?id="+id;
            x_admin_show('代付申请批量审核',url,600,200);
        }else{
            layer.msg('请选择代付申请', {icon: 2, time: 1000},function () {});
        }
    });
    $('#rejectAllOrder').on('click', function(){
        var id = '';
        $('.checkIds').each(function(){
            var _this = $(this);
            if( _this.is(':checked')  ){
                id = id + _this.val() + '_';
            }
        });
        if(id != '') {
            id=id.substring(0,id.length-1);
        }
        if(id){
            var url = "<?php echo U('/User/Withdrawal/dfRejectBatch');?>"+"?id="+id;
            x_admin_show('代付申请批量驳回',url,600,200);
        }else{
            layer.msg('请选择代付申请', {icon: 2, time: 1000},function () {});
        }
    });
</script>
</body>
</html>