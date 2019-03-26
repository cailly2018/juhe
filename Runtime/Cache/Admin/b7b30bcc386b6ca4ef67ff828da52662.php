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
                <h5>基本设置</h5>
            </div>
            <div class="ibox-content">
        <form class="layui-form" action="" autocomplete="off" id="baseForm">
            <input type="hidden" name="id" id="id" value="<?php echo ($vo["id"]); ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">网站名称：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[websitename]" value="<?php echo ($vo["websitename"]); ?>" lay-verify="required" autocomplete="off" placeholder="聚合支付系统" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">网站地址：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[domain]" value="<?php echo ($vo["domain"]); ?>" lay-verify="required" placeholder="" autocomplete="off" class="layui-input" placeholder="例如：www.quefu.cn">
                </div>
            </div>

            <div class="layui-form-item">
                    <label class="layui-form-label">联系邮箱：</label>
                    <div class="layui-input-block">
                        <input type="email" name="config[email]" value="<?php echo ($vo["email"]); ?>"  lay-verify="email"
                               autocomplete="off"
                               class="layui-input" placeholder="例如：zhifu@quefu.cn">
                    </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">客服电话：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[tel]" value="<?php echo ($vo["tel"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="例如：400 0000 000 ">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">网站客服QQ：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[qq]" value="<?php echo ($vo["qq"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="多个QQ号用 | 分隔">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">网站后台目录：</label>
                <div class="layui-input-inline">
                    <input type="text" name="config[directory]" value="<?php echo ($vo["directory"]); ?>" placeholder="默认为Admin，留空为默认" autocomplete="off" class="layui-input" style="text-transform: capitalize;">
                </div>
                <div class="layui-form-mid layui-word-aux">注意：不能输入中文，只能是英文字母，不建议更改！</div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">网站登录地址：</label>
                <div class="layui-input-inline">
                    <input type="text" name="config[login]" value="<?php echo ($vo["login"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="多个QQ号用 | 分隔">
                </div>
                <div class="layui-form-mid layui-word-aux">注意：不能输入中文,只能是英文字母和数字，且字母开头，中间不能有字符出现.</div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">网站备案号：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[icp]" value="<?php echo ($vo["icp"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="网站备案号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">公司名称：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[company]" value="<?php echo ($vo["company"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="公司名称">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">商户logo：</label>
                <div class="layui-upload">
                  <button type="button" class="layui-btn" id="test1">上传图片</button>
                  <div class="layui-upload-list">
                    <div style="width: 100px;margin-left: 11%;">
                        <input type="hidden" name="config[logo]" lay-filter="required" id="wx_img" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                        <img class="layui-upload-img" style="width: 100px;height: 100px;" src="<?php echo ($vo["logo"]); ?>" id="demo1">
                        <p id="demoText"></p>
                    </div>
                  </div>
                </div>  
            </div>  
            <div class="layui-form-item" style="display: none;">
                <label class="layui-form-label">授权KEY：</label>
                <div class="layui-input-block">
                    <input type="text" name="config[serverkey]" value="<?php echo ($vo["serverkey"]); ?>" autocomplete="off"
                           class="layui-input" placeholder="授权KEY，用于系统升级">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">商户前台登录错误次数：</label>
                <div class="layui-input-inline">
                    <input type="text" name="config[login_warning_num]" value="<?php echo ($vo["login_warning_num"]); ?>" placeholder="3" autocomplete="off" class="layui-input" style="text-transform: capitalize;">
                </div>
                <div class="layui-form-mid layui-word-aux"></div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">商户代付：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[payingservice]" value="1" title="开启" <?php if($vo['payingservice'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[payingservice]" value="0" title="关闭" <?php if($vo['payingservice'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">商户认证：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[authorized]" value="1" title="开启" <?php if($vo['authorized'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[authorized]" value="0" title="关闭" <?php if($vo['authorized'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">邀请码注册：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[invitecode]" value="1" title="开启" <?php if($vo['invitecode'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[invitecode]" value="0" title="关闭" <?php if($vo['invitecode'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">提现通知：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[withdraw]" value="1" title="开启" <?php if($vo['withdraw'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[withdraw]" value="0" title="关闭" <?php if($vo['withdraw'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">允许重复订单：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[is_repeat_order]" value="1" title="开启" <?php if($vo['is_repeat_order'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[is_repeat_order]" value="0" title="关闭" <?php if($vo['is_repeat_order'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">IP登录白名单：</label>
                <div class="layui-input-block">
                    <textarea placeholder="" class="layui-textarea" name="config[login_ip]"><?php echo ($vo["login_ip"]); ?></textarea>
                    <span style="color: red;font-size: 15px;">*输入多个IP请换行，如果不输入默认所有IP可登录</span>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">网站统计代码：</label>
                <div class="layui-input-block">
                    <textarea placeholder="" class="layui-textarea" name="config[tongji]"><?php echo ($vo["tongji"]); ?></textarea>
                </div>
            </div>
			<div class="layui-form-item">
                <label class="layui-form-label">谷歌令牌登录验证：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[google_auth]" value="1" title="开启" <?php if($vo['google_auth'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[google_auth]" value="0" title="关闭" <?php if($vo['google_auth'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">代付API：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[df_api]" value="1" title="开启" <?php if($vo['df_api'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[df_api]" value="0" title="关闭" <?php if($vo['df_api'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
                <div class="layui-form-item">

                <label class="layui-form-label">使用随机商户号：</label>
                <div class="layui-input-block">


                    <input type="radio" name="config[random_mchno]" value="1" title="开启" <?php if($vo['random_mchno'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[random_mchno]" value="0" title="关闭" <?php if($vo['random_mchno'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
			<div class="layui-form-item">
                <label class="layui-form-label">用户注册是否需要激活：</label>
                <div class="layui-input-block">
                    <input type="radio" name="config[register_need_activate]" value="1" title="是" <?php if($vo['register_need_activate'] == 1): ?>checked<?php endif; ?>>
                    <input type="radio" name="config[register_need_activate]" value="0" title="否" <?php if($vo['register_need_activate'] == 0): ?>checked<?php endif; ?>>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
				<button class="layui-btn" lay-submit="" lay-filter="add">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
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
layui.use(['form', 'laydate','upload'], function(){
    var layer = layui.layer
        ,form = layui.form
        ,laydate = layui.laydate
     
        ,upload = layui.upload;
          
          //普通图片上传 
        var uploadInst = upload.render({
            elem: '#test1'
            ,url: '<?php echo U("System/uploadImg");?>'
            ,before: function(obj){
              //预读本地文件示例，不支持ie8
              obj.preview(function(index, file, result){
                $('#demo1').attr('src', result); //图片链接（base64）
              });
            }
            ,done: function(res){
              console.log(res);
              //如果上传失败
              $('#wx_img').val(res['data']);
              return layer.msg(res['msg']);
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

        //监听提交
        form.on('submit(add)', function(data){
            $.ajax({
                url:"<?php echo U('System/saveBase');?>",
                type:"post",
                data:$('#baseForm').serialize(),
                success:function(res){
                    if(res.status){
                        layer.alert("操作成功", {icon: 6},function () {
                            location.reload();
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }else{
                        layer.msg("操作失败!", {icon: 5},function () {
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