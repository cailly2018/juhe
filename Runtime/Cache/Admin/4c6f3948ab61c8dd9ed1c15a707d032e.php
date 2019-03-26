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
		<form class="layui-form" method="post" autocomplete="off" action="" id="menuForm">
			<input type="hidden" name="cid" value="<?php echo ($c["id"]); ?>">
			<div class="layui-form-item">
				<label class="layui-form-label">栏目名称</label>
				<div class="layui-input-inline">
					<input type="text" name="c[name]" lay-verify="required" placeholder="请输入栏目名称" autocomplete="off"
						   class="layui-input" value="<?php echo ($c["name"]); ?>">
				</div>
			</div>
			<div class="layui-form-item">
				<label class="layui-form-label">上级栏目：</label>
				<div class="layui-input-block">
					<select name="c[pid]" lay-filter="aihao">
						<option value="">顶级栏目</option>
						<?php if(is_array($cates)): $i = 0; $__LIST__ = $cates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ca): $mod = ($i % 2 );++$i;?><option <?php if($ca['id'] == $c['pid']): ?>selected<?php endif; ?>
							value="<?php echo ($ca["id"]); ?>"><?php echo ($ca["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</div>
			</div>
			<div class="layui-form-item">
				<label class="layui-form-label">状态：</label>
				<div class="layui-input-block">
					<input type="radio" <?php if($c[status] == 1): ?>checked<?php endif; ?> name="c[status]" value="1" title="显示"
											checked="">
					<input type="radio" <?php if($c[status] == 0): ?>checked<?php endif; ?> name="c[status]" value="0"
					title="隐藏">
				</div>
			</div>
			<div class="layui-form-item">
				<div class="layui-input-block">
					<button class="layui-btn" lay-submit lay-filter="addmenu">提交保存</button>
				</div>
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
    layui.use(['layer', 'form','laydate'], function(){
        var form = layui.form
            ,laydate = layui.laydate
            ,$ = layui.jquery
            ,layer = layui.layer;
        //监听提交
        form.on('submit(addmenu)', function(data){
            $.ajax({
                url:"<?php echo U('Content/saveEditCategory');?>",
                type:'post',
                data:$('#menuForm').serialize(),
                success:function(res){
                    if(res.status){
                        layer.alert("编辑成功", {icon: 6},function () {
                            parent.location.reload();
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }else{
                        layer.msg(res.msg ? res.msg : "操作失败!", {icon: 5},function () {
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