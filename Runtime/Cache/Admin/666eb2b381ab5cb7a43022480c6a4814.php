<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title><?php echo ($sitename); ?>---管理</title>
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
                <h5>T1冻结资金管理</h5>
                <div class="ibox-tools">
                    <i class="layui-icon" onclick="location.replace(location.href);" title="刷新"
                       style="cursor:pointer;">ဂ</i>
                </div>
            </div>
            <!--条件查询-->
            <div class="ibox-content">
                <form class="layui-form" action="" method="get" autocomplete="off" id="orderform">
                    <input type="hidden" name="m" value="<?php echo ($model); ?>">
                    <input type="hidden" name="c" value="Order">
                    <input type="hidden" name="a" value="changeRecord">
                    <input type="hidden" name="p" value="1">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <input type="text" name="orderid" autocomplete="off" placeholder="请输入订单号"
                                       class="layui-input" value="<?php echo ($_GET['orderid']); ?>">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" name="createtime" id="createtime"
                                       placeholder="起始时间" value="<?php echo (urldecode($_GET['createtime'])); ?>">
                            </div>
                        </div>


                        <div class="layui-inline">
                            <button type="submit" class="layui-btn"><span
                                    class="glyphicon glyphicon-search"></span> 搜索
                            </button>
                        </div>
                    </div>
                </form>
                <div class="layui-btn-group demoTable">
                    <button class="layui-btn" data-type="getCheckData">批量解冻</button>

                </div>
                <!--交易列表-->
                <table class="layui-table" lay-data="{width:'100%',id:'frozonData'}">
                    <thead>
                    <tr>
                        <th lay-data="{checkbox:true, fixed: true,field:'key'}">
                            选择
                        </th>
                        <th lay-data="{field:'id',width:60}">ID</th>
                        <th lay-data="{field:'orderid', width:240}">订单号</th>
                        <th lay-data="{field:'amount', width:100}">冻结金额</th>
                        <th lay-data="{field:'datetime', width:180}">解冻时间</th>
                        <th lay-data="{field:'tongdao', width:200}">通道</th>
                        <th lay-data="{field:'createtime', width:180}">添加时间</th>
                        <th lay-data="{field:'status', width:120}">状态</th>

                        <th lay-data="{field:'handle',width:100,fixed: 'right'}" lay-filter="contentstr">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                            <td></td>
                            <td><?php echo ($vo["id"]); ?></td>
                            <td style="text-align:center;"><?php echo ($vo["orderid"]); ?></td>


                            <td style="text-align:center;">
                                <?php if($vo["amount"] < 0): ?><span style="color:#F00">
                                <?php else: ?>
                                    <span style="color:#030"><?php endif; ?>
                                <?php echo ($vo["amount"]); ?>
                                </span>
                            </td>
                            <td style="text-align:center;"><?php echo (date('Y-m-d H:i:s',$vo["thawtime"])); ?></td>

                            <td style="text-align:center;"><?php echo (getProduct($vo["pid"])); ?></td>
                            <td style="text-align:center;"><?php echo (date('Y-m-d H:i:s',$vo["createtime"])); ?></td>
                            <td style="text-align:center;">
                                <?php if($vo["status"] == 1): ?>已解冻
                                    <?php else: ?>
                                    冻结中<?php endif; ?>
                            </td>
                            <td>
                                <?php if($vo["status"] == 1): else: ?>
                                    <button class="layui-btn layui-btn-small layui-btn-danger" onclick="frozen(this,'<?php echo ($vo["id"]); ?>')"
                                    >解冻</button><?php endif; ?>

                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <!--交易列表-->
                <div class="pagex"> <?php echo ($page); ?></div>
            </div>
        </div>
    </div>
</div>
</div>
<audio style="display: none" id="bgMusic" src="/web/tixing.mp3"></audio>
<script src="/Public/Front/js/jquery.min.js"></script>
<script src="/Public/Front/js/bootstrap.min.js"></script>
<script src="/Public/Front/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/Public/Front/js/content.js"></script>
<script src="/Public/Front/js/plugins/layui/layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/x-layui.js" charset="utf-8"></script>

<script>
    //setInterval("test()",5000);
    var myAuto = document.getElementById('bgMusic');
    function test() {
        $.ajax({
            type: "POST",
            url: "<?php echo U('Withdrawal/tixing');?>",
            async: true,
            data: "op=getdata",
            success: function(data, textStatus) {
                if(data.status>0){
                   // myAuto.play();
                }else{
                   // myAuto.pause();
                }
            }
        });
    }
</script>
<script>
    layui.use(['laydate', 'laypage', 'layer', 'table',['form']], function() {
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
        var $ = layui.$, active = {
            getCheckData: function(){ //获取选中数据
                var checkStatus = table.checkStatus('frozonData')
                    ,data = checkStatus.data;
                var msg="";
                var iscontinute=true;
                if(!(data&&data.length>0)){
                    layer.alert("请选择需要解冻的数据");
                    return;
                }
                var ids="";
                for(var i=0;i<data.length;i++) {
                    ids += (i>0?",":"")+data[i]["id"];

                }
                 $.ajax({
                        url :"<?php echo U('User/frozenHandles');?>",
                        data:{ids:ids},
                        type : "POST",
                        dataType : 'json',
                        success : function (result){
                           if(result.status){
                               layer.alert("解冻成功！",function () {
                                   location.replace(location.href);
                               });
                           }else if(result.count>0){
                               layer.alert("成功解冻"+result.count+"条数据，原因："+result.msg,function () {
                                   location.replace(location.href);
                               });
                           }else{
                               layer.alert("解冻失败",function () {
                                   location.replace(location.href);
                               });
                           }
                        }
                    });


                
            }

        };
        $('.demoTable .layui-btn').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });
    });


    /*解冻*/
    function frozen(obj, id) {
        layer.confirm('确认要解冻吗？', function (index) {
            $.ajax({
                url:"<?php echo U('User/frozenHandle');?>",
                type:'post',
                data:'id='+id,
                success:function(res){
                    if(res.status){
                        location.replace(location.href);
                        layer.msg('已解冻!',{icon:1,time:1000});
                    }
                }
            });
        });
    }


</script>
</body>
</html>