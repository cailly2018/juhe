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
		<form class="layui-form layui-form-pane" action="" autocomplete="off" id="editPassword">
		  <div class="layui-form-item">
			<label class="layui-form-label">原密码：</label>
			<div class="layui-input-inline">
			  <input type="password" name="ypassword" autocomplete="off" lay-verify="required" placeholder="" class="layui-input">
			</div>
		  </div>
		  <div class="layui-form-item">
			<label class="layui-form-label">新密码：</label>
			<div class="layui-input-inline">
			  <input type="password" name="newpassword" autocomplete="off" lay-verify="required" placeholder="" class="layui-input">
			</div>
		  </div>
		  <div class="layui-form-item">
			<label class="layui-form-label">重复新密码：</label>
			<div class="layui-input-inline">
			  <input type="password" name="newpasswordok" lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
			</div>
		  </div>
		  <div class="layui-form-item">
			<button class="layui-btn" lay-submit="" lay-filter="add">提交修改</button>
		  </div>
		</form>
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
layui.use(['layer', 'form'], function(){
  var form = layui.form
  ,layer = layui.layer
  ,$ = layui.jquery;
			
  //监听提交
  form.on('submit(add)', function(data){
	$.ajax({
		url:"<?php echo U('System/editPassword');?>",
		type:"post",
		data:$('#editPassword').serialize(),
		success:function(res){
			if(res.status){
				layer.alert("操作成功", {icon: 6},function () {
					parent.location.reload();
					var index = parent.layer.getFrameIndex(window.name);
					parent.layer.close(index);
				});
			}else{
				layer.msg(res.msg?res.msg:"操作失败!", {icon: 5},function () {
					var index = parent.layer.getFrameIndex(window.name);
					parent.layer.close(index);
				});
				return false;
			}
		}
	});
	return false;
  });
});
</script>
</body>
</html>