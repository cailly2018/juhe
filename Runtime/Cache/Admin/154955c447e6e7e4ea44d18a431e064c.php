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
            <div class="ibox-title">
                <h5>修改个人手机</h5>
            </div>
            <div class="ibox-content">
                <!--用户信息-->
                <form class="layui-form" action="" autocomplete="off" id="editmobile">
                    
   
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <?php if($editmobile): ?><label class="layui-form-label">新手机号码：</label>
                                <div class="layui-input-inline">
                                <input type="text" name="mobile"  lay-verify="phone" autocomplete="off"
                                       class="layui-input" id="mobile" value="" >
                                </div>
                            <?php else: ?>
                                <label class="layui-form-label">原手机号码：</label>
                                <div class="layui-input-inline">
                                <input type="text"  disabled lay-verify="phone" autocomplete="off"
                                       class="layui-input" value="<?php echo ($mobile); ?>" >
                                </div><?php endif; ?>
                        </div>
                    </div>
                    

                    <script src="/Public/Front/js/jquery.min.js"></script>
<script src="/Public/Front/js/Util.js"></script>
<?php if($sms_is_open): ?><div class="layui-form-item">
    <label class="layui-form-label">手机验证码：</label>
    <div class="layui-input-inline">
        <input type="text" name="code" lay-verify="required" autocomplete="off"
               placeholder="" class="layui-input" value="">
    </div>
    <div class="layui-input-inline">
        <a href="javascript:;" id="sendBtn" data-bind='<?php echo ($first_bind_mobile); ?>' class="layui-btn" data-mobile="<?php echo ($mobile); ?>">发送验证码</a>
    </div>
</div><?php endif; ?>
<script>
    $(function (){
        // 手机验证码发送
        $('#sendBtn').click(function(){
            var mobile = $(this).data('mobile');
            var first_bind = $(this).data('bind');
            var sendUrl = "<?php echo ($sendUrl); ?>";
            if(!mobile){
                //判断用户是否准备绑定手机号
                if(!first_bind){
                    layer.alert('请先填写手机号码',{icon: 5}, function() {
                        location.href = "<?php echo U('System/mobile');?>";
                    });
                }else{
                    layer.alert('请先填写手机号码',{icon: 5});
                }
                return;
            }
            sendSms(this, mobile, sendUrl);
        });
    })
</script>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="editmobile">立即提交</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div>
                </form>
                <!--用户信息-->
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
    setInterval("test()",5000);
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
    //短信验证是否开启
    var sms_is_open = "<?php echo ($sms_is_open); ?>";
    layui.use(['laydate', 'laypage', 'layer', 'form', 'element'], function() {
        var laydate = layui.laydate //日期
            ,layer = layui.layer //弹层
            ,form = layui.form //弹层
            , element = layui.element; //元素操作
        //日期
        laydate.render({
            elem: '#date'
        });

        $('#mobile').on('blur',function(){
            var mobile = $(this).val();
            $('#sendBtn').attr('data-mobile', mobile);
        });

        //监听提交
        form.on('submit(editmobile)', function(data){
            $.ajax({
            url:"<?php echo U('System/editMobileShow');?>",
            type:"post",
            data:$('#editmobile').serialize(),
            success:function(res){
                if(res['status'] && res['data'] == 'editNewMobile'){
                    layer.alert("提交成功", {icon: 6},function () {
                        parent.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    }); 
                }else if(res['status'] ){
                    layer.alert("下一步：填写新手机号码", {icon: 6},function () {
                        window.location.href = "<?php echo U('System/editMobileShow',['editnewmobile'=>1]);?>";
                    });
                }else{
                    layer.alert(res.msg ? res.msg :"操作失败", {icon: 5},function () {
                        parent.location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                }
            }
        });
            return false;
        });
    });
</script>
</body>
</html>