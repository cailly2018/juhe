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
<div class="layui-container">
  <div class="layui-row">
    <div class="layui-col-lg12">
      <form class="layui-form" action="" autocomplete="off" id="inviteconfig">
        <input type="hidden" name="id" value="<?php echo ($data["id"]); ?>">
        <div class="layui-form-item">
          <label class="layui-form-label">状态：</label>
          <div class="layui-input-inline">
            <select name="invitezt">
              <option <?php if($data['invitezt'] == 1): ?>selected<?php endif; ?> value="1">正常</option>
              <option <?php if($data['invitezt'] == 0): ?>selected<?php endif; ?> value="0">关闭</option>
            </select>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">普通代理商：</label>
          <div class="layui-form-mid layui-word-aux">可生成邀请码</div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label"></label>
          <div class="layui-inline">
            <div class="layui-input-inline">
              <input type="text" name="invitetype5number" id="date1" autocomplete="off" class="layui-input"
                     value="<?php echo ($data["invitetype5number"]); ?>">
            </div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label"></label>
          <div class="layui-inline">
            <div class="layui-input-inline">
            <select name="invitetype5ff">
              <option <?php if($data['invitetype5ff'] == 1): ?>selected<?php endif; ?> value="1">可分配给下级</option>
              <option <?php if($data['invitetype5ff'] == 1): ?>selected<?php endif; ?> value="0">不可分配给下级</option>
            </select>
            </div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label"></label>
          <button class="layui-btn" lay-submit="" lay-filter="config">立即提交</button>
          <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
      </form>

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
    layui.use(['layer', 'form','laydate'], function(){
        var form = layui.form
            ,laydate = layui.laydate
            ,layer = layui.layer;
      //监听提交
      form.on('submit(config)', function(data){
          $.ajax({
              url:"<?php echo U('User/saveInviteConfig');?>",
              type:"post",
              data:$('#inviteconfig').serialize(),
              success:function(res){
                  if(res.status){
                      layer.alert("编辑成功", {icon: 6},function () {
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