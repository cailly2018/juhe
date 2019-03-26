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
<div class="wrapper wrapper-content animated"><div class="layui-container">    <div class="layui-row">        <div class="layui-col-lg12">            <form class="layui-form" autocomplete="off" id="addCode">                <div class="layui-form-item">                    <label class="layui-form-label">邀请码：</label>                    <div class="layui-input-inline">                        <input type="text" name="invitecode" lay-verify="" id="invitecode" placeholder="请输入密码" autocomplete="off"                               class="layui-input" value="<?php echo ($invitecode); ?>">                    </div>                    <div class="layui-form-mid layui-word-aux" style="padding:0;">                        <button type="button" class="layui-btn layui-btn-warm" onclick="javascript:location.reload();">重新生成</button>                    </div>                </div>                <div class="layui-form-item">                    <label class="layui-form-label">到期时间：</label>                    <div class="layui-input-inline">                        <input type="text" name="yxdatetime" id="date" placeholder="" autocomplete="off" class="layui-input" value="<?php echo ($datetime); ?>">                    </div>                </div>                <div class="layui-form-item">                    <label class="layui-form-label">注册类型：</label>                    <div class="layui-input-inline">                        <select name="regtype">                            <?php if(is_array($groupId)): foreach($groupId as $k=>$v): ?><option value="<?php echo ($k); ?>"><?php echo ($v); ?></option><?php endforeach; endif; ?>                            </select>                    </div>                </div>                <div class="layui-form-item">                    <div class="layui-input-block">                        <button class="layui-btn" lay-submit="" lay-filter="code">立即提交</button>                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>                    </div>                </div>            </form>        </div>    </div></div></div>
<script src="/Public/Front/js/jquery.min.js"></script>
<script src="/Public/Front/js/bootstrap.min.js"></script>
<script src="/Public/Front/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/Public/Front/js/content.js"></script>
<script src="/Public/Front/js/plugins/layui/layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/x-layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/Util.js" charset="utf-8"></script><script>    layui.use(['layer', 'form','laydate'], function(){        var form = layui.form            ,laydate = layui.laydate            ,layer = layui.layer;        //时间选择器        laydate.render({            elem: '#date'            ,value:'<?php echo ($datetime); ?>'            ,type: 'datetime'        });      //监听提交      form.on('submit(code)', function(data){          $.ajax({              url:"<?php echo U('Agent/addInvitecode');?>",              type:"post",              data:$('#addCode').serialize(),              success:function(res){                  if(res.status){                      layer.alert("编辑成功", {icon: 6},function () {                          parent.location.reload();                          var index = parent.layer.getFrameIndex(window.name);                          parent.layer.close(index);                      });                  }              }          });          return false;      });    });</script></body></html>