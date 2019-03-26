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
            <div class="ibox-title">
                <h5>银行卡管理</h5>
            </div>
            <div class="ibox-content">
                <table class="layui-table" lay-data="{height:313, id:'test3'}">
                    <thead>
                    <tr>
                        <th lay-data="{field:'id', width:60}">ID</th>
                        <th lay-data="{field:'bankname', width:120}">银行名称</th>
                        <th lay-data="{field:'subbransh', width:200}">支行名称</th>
                        <th lay-data="{field:'accountname', width:90}">开户名</th>
                        <th lay-data="{field:'cardnumber', width:180}">银行卡号</th>
                        <th lay-data="{field:'province', width:120}">所在省</th>
                        <th lay-data="{field:'city', width:120}">所在城市</th>
                        <th lay-data="{field:'alias', width:120}">别名</th>
                        <th lay-data="{field:'isdefault', width:100}">默认结算</th>
                        <th lay-data="{field:'memo', width:120}">备注</th>
                        <th lay-data="{field:'op', width:140}">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?><tr>
                                <td><?php echo ($b["id"]); ?></td>
                                <td><?php echo ($b["bankname"]); ?></td>
                                <td><?php echo ($b["subbranch"]); ?></td>
                                <td><?php echo ($b["accountname"]); ?></td>
                                <td><?php echo ($b["cardnumber"]); ?></td>
                                <td><?php echo ($b["province"]); ?></td>
                                <td><?php echo ($b["city"]); ?></td>
                                <td><?php echo ($b["alias"]); ?></td>
                                <td>
                                <div class="layui-input-inline">
                                    <input type="checkbox" name="open" data-id="<?php echo ($b["id"]); ?>"
                                    <?php if($b[isdefault] == 1): ?>checked<?php endif; ?>
                                           data-name="<?php echo ($b["bankname"]); ?>"
                                           lay-skin="switch"
                                           lay-filter="switchTest" lay-text="是|否">
                                </div>
                                </td>
                                <td>上次修改：<?php echo (date('Y-m-d',$b["updatetime"])); ?> <?php echo ($b["ip"]); ?> <?php echo ($b["ipaddress"]); ?></td>
                                <td>
                                    <button class="layui-btn layui-btn-small"
                                            onclick="bank_edit('编辑银行卡','<?php echo U('Account/addBankcard',['id'=>$b[id]]);?>',600,400)"><i
                                            class="layui-icon"></i></button>
                                    <button class="layui-btn layui-btn-small"
                                            onclick="bank_del(this,'<?php echo ($b[id]); ?>')"><i
                                            class="layui-icon"></i></button>
                                </td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>

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

                <button class="layui-btn" onclick="bank_add('添加银行卡','<?php echo U('Account/addBankcard');?>',600,400)">添加银行卡
                </button>
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
    layui.use(['laydate', 'form', 'layer', 'table', 'element'], function(){
        var laydate = layui.laydate //日期
            ,form = layui.form //分页
            ,layer = layui.layer //弹层
            ,table = layui.table //表格
            ,element = layui.element; //元素操作
        //监听单元格编辑
        table.on('edit(test3)', function(obj){
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,field = obj.field; //得到字段
            data[field] = value; //更新缓存中的值
            layer.msg(value);
        });
        //监听指定开关
        form.on('switch(switchTest)', function (data) {
            var isopen = this.checked ? 1 : 0,
                id = $(this).attr('data-id');
            $.ajax({
                url: "<?php echo U('Account/editBankStatus');?>",
                type: 'post',
                data: "id=" + id + "&isopen=" + isopen,
                success: function (res) {
                    if (res.status) {
                        location.reload();
                        layer.tips('温馨提示：开启成功', data.othis);
                    } else {
                        layer.tips('温馨提示：关闭成功', data.othis);
                    }
                }
            });
        });
    });
    /*添加-银行卡*/
    function bank_add(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
    /*编辑-银行卡*/
    function bank_edit(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
    /*删除-银行卡*/
    function bank_del(obj,id){
        layer.confirm('确认要删除吗？', function (index) {
            $.ajax({
                url:"<?php echo U('Account/delBankcard');?>",
                type:'post',
                data:'id='+id,
                success:function(res){
                    if(res.status){
                        $(obj).parents("tr").remove();
                        layer.msg('已删除!',{icon:1,time:1000});
                    }
                }
            });
        });
    }
    $('#pageList').change(function(){
        $('#pageForm').submit();
    });
</script>
</body>
</html>