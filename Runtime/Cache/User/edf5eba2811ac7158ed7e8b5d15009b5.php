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
        <div class="ibox-content">
            <form class="layui-form" action="" autocomplete="off" id="bankform">
                <input type="hidden" name="id" value="<?php echo ($_GET['id']); ?>">
                <div class="layui-form-item">
                    <label class="layui-form-label">开户行：</label>
                    <div class="layui-input-block">
                        <select name="b[bankname]" lay-filter="" lay-search="" lay-verify="required">
                            <option value="">选择开户行</option>
                            <?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vobank): $mod = ($i % 2 );++$i;?><option <?php if($b['bankname'] == $vobank['bankname']): ?>selected<?php endif; ?>
                                value="<?php echo ($vobank["bankname"]); ?>"><?php echo ($vobank["bankname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支行名称：</label>
                    <div class="layui-input-block">
                        <input type="text" name="b[subbranch]" lay-verify="" autocomplete="off" placeholder=""
                               class="layui-input" value="<?php echo ($b["subbranch"]); ?>" lay-verify="required">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">开户名：</label>
                    <div class="layui-input-block">
                        <input type="text" name="b[accountname]" lay-verify="" autocomplete="off" placeholder=""
                               class="layui-input" value="<?php echo ($b["accountname"]); ?>" lay-verify="required">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">银行卡号：</label>
                    <div class="layui-input-block">
                        <input type="number" name="b[cardnumber]" lay-verify="" autocomplete="off" placeholder=""
                               class="layui-input" value="<?php echo ($b["cardnumber"]); ?>" lay-verify="required">
                    </div>
                </div>
                <div class="layui-form-item">
                    <form class="layui-form">
                    <div class="layui-inline">
                        <label class="layui-form-label">所属省：</label>
                        <div class="layui-input-inline" >
                            <select name="b[province]" lay-filter="province"  >
                            <option value="">选择所属省</option>
                            <?php if(is_array($shenglist)): $i = 0; $__LIST__ = $shenglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" <?php if($b['province'] == $r['region_name']): ?>selected<?php endif; ?>
                                value="<?php echo ($r["region_name"]); ?>"><?php echo ($r["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">所属城市：</label>
                        <div class="layui-input-inline">
                            <select name="b[city]" id="citys" lay-filter="city">
                                <option  value="">选择所属城市</option>
                                <?php if(is_array($reglist)): $i = 0; $__LIST__ = $reglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" <?php if($b['city'] == $r['region_name']): ?>selected<?php endif; ?>
                                    value="<?php echo ($r["region_name"]); ?>"><?php echo ($r["region_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>

                        </div>
                    </div>
                        </form>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">别名：</label>
                    <div class="layui-input-block">
                        <input type="text" name="b[alias]" lay-verify="" autocomplete="off" placeholder=""
                               class="layui-input" value="<?php echo ($b["alias"]); ?>">
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
                        <button class="layui-btn" lay-submit="" lay-filter="save">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form>
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
<script  type="text/javascript">
    layui.use(['laydate', 'form', 'layer', 'table', 'element'], function() {
        var laydate = layui.laydate //日期
            , form = layui.form //分页
            , layer = layui.layer //弹层
            , table = layui.table //表格
            , element = layui.element; //元素操作
        //监听提交
        form.on('submit(save)', function(data){
            $.ajax({
                url:"<?php echo U('Account/addBankcard');?>",
                type:"post",
                data:$('#bankform').serialize(),
                success:function(res){
                    if(res.status){
                        layer.alert("编辑成功", {icon: 6},function () {
                            parent.location.reload();
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }else{
                        layer.alert("操作失败", {icon: 5},function () {
                            parent.location.reload();
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }
                }
            });
            return false;
        });
        var form = layui.form;
        form.on('select(province)', function(data){
            var areaId=data.elem.value;

            $.ajax({
                type: 'POST',
                url: "<?php echo U('Account/getcode');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[city]']").empty();
                    var html = "<option value=''>选择所属城市</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.region_name + "'>" + k.region_name + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[city]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });



    });


</script>



</body>
</html>