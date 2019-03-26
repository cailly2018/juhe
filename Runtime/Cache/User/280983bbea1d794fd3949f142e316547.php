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
                <h5>委托结算</h5>
            </div>
            <div class="ibox-content">
				<div class="layui-tab">
					  <ul class="layui-tab-title">
						<li><a href="<?php echo U("Withdrawal/dfapply");?>">表单提交方式</a></li>
						<li class="layui-this"><a href="<?php echo U("Withdrawal/entrusted");?>">EXCEL导入方式</a></li>
					  </ul>
				</div>
                <blockquote class="layui-elem-quote">
                    <span class="text-danger">可提现：<?php echo ($info['balance']); ?> 元</span>
                    <span style="margin:0 30px;" class="text-muted">冻结：<?php echo ($info['blockedbalance']); ?> 元</span>
                    <span class="text-warning">结算：<?php if($tkconfig[t1zt] == 1): ?>T+1<?php elseif($tkconfig[t1zt] == 0): ?>T+0<?php endif; ?></span>
                </blockquote>

                <form class="layui-form" action="<?php echo U('Withdrawal/saveEntrusted');?>" method="post" autocomplete="off"
                      enctype="multipart/form-data">
                    <input type="hidden" name="userid" value="<?php echo ($info[id]); ?>">
                    <input type="hidden" name="balance" id="balance" value="<?php echo ($info['balance']); ?>">
                    <input type="hidden" name="tktype" id="tktype" value="<?php echo ($tkconfig[tktype]); ?>">
                    <?php switch($tkconfig[tktype]): case "0": ?><input type="hidden" name="feilv" id="feilv" value="<?php echo ($tkconfig[sxfrate]); ?>"><?php break;?>
                        <?php case "1": ?><input type="hidden" name="feilv" id="feilv" value="<?php echo ($tkconfig[sxffixed]); ?>"><?php break; endswitch;?>

                    <div class="layui-form-item">
                        <label class="layui-form-label">上传文件：</label>
                        <div class="layui-input-inline">
                            <input type="file" accept="csv,xls,xlsx" name="file">
                        </div>
                        <div class="layui-form-mid layui-word-aux">上传委托结算Excel文件 <a href="/Uploads/model.xls" target="_blank">下载模板</a></div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">支付密码：</label>
                        <div class="layui-input-inline">
                            <input type="password" name="password" lay-verify="pass" placeholder="请输入支付密码" autocomplete="off" class="layui-input">
                        </div>
                    </div>

                    <script src="/Public/Front/js/jquery.min.js"></script>
<?php if($sms_is_open): ?><div class="layui-form-item">
    <label class="layui-form-label">手机验证码：</label>
    <div class="layui-input-inline">
        <input type="text" name="code" lay-verify="required" autocomplete="off"
               placeholder="" class="layui-input" value="">
    </div>
    <div class="layui-input-inline">
        <a href="javascript:;" id="sendBtn" data-bind='<?php echo ($first_bind_mobile); ?>' class="layui-btn" data-mobile="<?php echo ($fans[mobile]); ?>">发送验证码</a>
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
                        location.href = "<?php echo U('Account/profile');?>";
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
                            <button class="layui-btn" type="submit" lay-filter="save">提交申请</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div>
                </form>
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
    layui.use(['form', 'layer','element'], function(){
        var layer = layui.layer //弹层
            ,$ = layui.jquery
            ,form = layui.form
            ,element = layui.element; //元素操作;
    });
</script>
</body>
</html>