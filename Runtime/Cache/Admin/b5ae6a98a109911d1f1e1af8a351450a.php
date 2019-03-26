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
            <div class="ibox float-e-margins">
                <!-- Content -->
                <div class="ibox-content">
					<blockquote class="layui-elem-quote layui-quote-nm">
					<p>可用余额：<span class="text-danger"><?php echo ($info["balance"]); ?></span>元</p>
					<p>冻结余额：<span class="text-danger"><?php echo ($info["blockedbalance"]); ?></span>元</p>
					</blockquote>
                    <form class="layui-form" action="" autocomplete="off" id="incr">
                        <input type="hidden" name="uid" value="<?php echo ($info['id']); ?>">
                        <input type="hidden" name="cztype" value="7">
                        <div class="layui-form-item">
                            <label class="layui-form-label">冻结金额：</label>
                            <div class="layui-input-block">
                                <input type="text" name="bgmoney" lay-verify="required" autocomplete="off"
                                       placeholder="请输入金额" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item layui-form-text">
                            <label class="layui-form-label">备注：</label>
                            <div class="layui-input-block">
                                <textarea placeholder="请输入内容" lay-verify="required" class="layui-textarea" name="memo"></textarea>
                            </div>
                        </div>
                        <div class="layui-form-item" id="auto_unfreeze_time">
                            <label class="layui-form-label">自动解冻时间：</label>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" name="unfreeze_time" id="unfreeze_time" placeholder="自动解冻时间" style="width:300px">
                                <div class="layui-form-mid layui-word-aux">留空则不自动解冻</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit="" lay-filter="save">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Content -->
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
layui.use(['form','layer','laydate'], function(){
        var form = layui.form
            ,laydate = layui.laydate
            ,layer = layui.layer;
    laydate.render({
        elem: '#unfreeze_time'
        ,type: 'datetime'
        ,min: "<?php echo date('Y-m-d H:i:s');?>"
    });
    form.on('radio(cztype)',function(data){
        if(data.value == 7) {
            $('#auto_unfreeze_time').show();
        } else {
            $('#auto_unfreeze_time').hide();
        }
    });
    //监听提交
    form.on('submit(save)', function(data){
        $.ajax({
            url:"<?php echo U('User/frozenMoney');?>",
            type:"post",
            data:$('#incr').serialize(),
            success:function(res){
                if(res.status){
                    layer.alert("编辑成功", {icon: 6},function () {
                        location.replace(location.href);
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                }else{
                    layer.alert(res.msg?res.msg:"操作失败", {icon: 5});
                }
            }
        });
        return false;
    });
});
</script>
</body>
</html>