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
  <div class="col-sm-8">
    <div class="ibox float-e-margins">
      <div class="ibox-title"><h5>收款信息设置</h5></div>
      <div class="ibox-content">
        <form class="layui-form" action="<?php echo U('User/Account/saveReceiver');?>" autocomplete="off" id="profile">
            <input type="hidden" name="id" value="<?php echo ($p["id"]); ?>">

            <div class="layui-form-item">

                <label class="layui-form-label">收款人名称：</label>
                <div class="layui-inline">
                <input type="text" id="test1" name="p[receiver]" lay-verify="title" placeholder="" autocomplete="off" class="layui-input" value="<?php echo ($p["receiver"]); ?>">
              </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit="" lay-filter="profile">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
      </div>
      <div class="ibox-title"><h5>二维码背景设置</h5></div>
      <div class="ibox-content">
        <div class="layui-upload">
          <button type="button" class="layui-btn" id="upload">上传图片</button><span style="color: red; margin-left: 10px;">二维码背景只能使用640*811大小的图片</span>
          <div class="layui-upload-list">
            <img class="layui-upload-img" id="demo1" style="max-width: 300px;">
            <p id="demoText"></p>
          </div>
        </div>  
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="ibox float-e-margins">
      <div class="ibox-title"><h5>商户收款二维码</h5></div>
      <div class="ibox-content">
        <div style="width:100%; text-align: center; padding: 20px 0">
            <img src="<?php echo ($imageurl); ?>" width="300" style="width: 100%; margin:0 auto;"/>
            <a href="<?php echo U('Account/downQrcode');?>" class="layui-btn">保存二维码</a>
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
    layui.use(['laydate', 'form', 'layer', 'table', 'element', 'upload'], function(){
        var laydate = layui.laydate //日期
            ,form = layui.form //分页
            ,layer = layui.layer //弹层
            ,table = layui.table //表格
            ,element = layui.element //元素操作
            ,upload = layui.upload; //上传
        form.on('switch(switchTest)', function(data){
                layer.tips('温馨提示：请注意开关状态的文字可以随意定义，而不仅仅是ON|OFF', data.othis)
                $('#test1:text').val();
              });

        //普通图片上传
        var uploadInst = upload.render({
          elem: '#upload'
          ,url: '<?php echo U("Account/uploadQrcode");?>'
          ,before: function(obj){
            //预读本地文件示例，不支持ie8
            obj.preview(function(index, file, result){
              $('#demo1').attr('src', result); //图片链接（base64）
            });
          }
          ,done: function(res){
            //如果上传失败
            if(res.code > 0){
              return layer.msg('上传失败');
            }
            layer.alert('上传成功',{icon: 6}, function(){
              location.href = location.href;
            });
            //上传成功
          }
          ,error: function(){
            //演示失败状态，并实现重传
            var demoText = $('#demoText');
            demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
            demoText.find('.demo-reload').on('click', function(){
              uploadInst.upload();
            });
          }
        });

    });


</script>
</body>
</html>