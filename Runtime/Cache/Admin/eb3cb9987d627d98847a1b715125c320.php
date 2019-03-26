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
    <div class="col-sm-12">
        <form class="layui-form" action="" id="profile">
            <input type="hidden" name="userid" value="<?php echo ($u["id"]); ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">用户级别：</label>
                <div class="layui-input-inline">
                    <select name="u[products]" lay-verify="required" lay-search="">
                        <option value=""></option>
                        <?php if(is_array($products)): foreach($products as $k=>$v): ?><option value="<?php echo ($v['code']); ?>_<?php echo ($v['title']); ?>"><?php echo ($v['title']); ?></option><?php endforeach; endif; ?>
                    </select>
                </div>
                <button class="layui-btn" lay-submit="submit" lay-filter="save">查询</button>
            </div>
        </form>
    </div>
    <div class="ibox-content">
        <table class="layui-table" lay-skin="line" id="mytable">
            <thead>
            <tr>
                <th>代付渠道</th>
                <th>余额</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

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
    layui.use(['layer', 'form','laydate'], function(){
        var form = layui.form
            ,laydate = layui.laydate
            ,layer = layui.layer;
        //监听提交
        form.on('submit(save)', function(data){
            $.ajax({
                url:"<?php echo U('User/saveQuery');?>",
                type:"post",
                data:$('#profile').serialize(),
                success:function(res){
                    var res = eval('(' + res + ')');

                    if(res.code){
                        layer.alert('查询成功');
                        $("#mytable").append("<tr><td>"+res.title+"</td><td>"+res.money+"</td></tr>")
                    }
                }
            });
            return false;
        });
    });
</script>
<!--统计代码，可删除-->
</body>
</html>