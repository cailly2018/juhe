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
        <div class="ibox-content">
            <form class="layui-form" action="" autocomplete="off" id="bankform">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">代理商编号<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline" >
                            <input type="text" name="b[mchid]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="00020009">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商户名称<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[name]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商户法人姓名<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[legelname]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="<?php echo ($b["alias"]); ?>">
                        </div>
                    </div>

                    <div class="layui-inline">
                        <label class="layui-form-label">商户法人身份证号<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[legelcertno]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商户邮件<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[email]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>


                    <div class="layui-inline">
                        <label class="layui-form-label">商户客服电话<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[phone]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">收款卡对应银行代码<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <select name="b[bankno]" lay-filter="bankno"  >
                                <option value="">选择所银行</option>
                                <?php if(is_array($getBankList)): $i = 0; $__LIST__ = $getBankList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng"  value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">收款卡对应支行代码<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <select name="b[branchno]" lay-filter="branchno"  >
                                <option value="">选择所支行</option>

                            </select>

                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">收款卡号<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[cardno]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">电子收款卡号：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[ecardno]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">收款人姓名<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[payname]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">收款卡对应电话<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[payphone]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商户证件号<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[certno]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">收款卡地址：</label>
                        <div class="layui-input-inline" >
                            <select name="b[cardprovince]" lay-filter="cardprovince"  >
                                <option value="">选择所属省</option>
                                <?php if(is_array($shenglist)): $i = 0; $__LIST__ = $shenglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <select name="b[cardcity]" lay-filter="cardcity">
                                <option  value="">选择所属城市</option>
                                <?php if(is_array($reglist)): $i = 0; $__LIST__ = $reglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <select name="b[cardarea]"lay-filter="cardarea">
                                <option  value="">选择所属区</option>
                                <?php if(is_array($reglist)): $i = 0; $__LIST__ = $reglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="layui-form-item">

                    <div class="layui-inline">
                        <label class="layui-form-label">商户所在地址<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline" >
                            <select name="b[province]" lay-filter="province"  >
                                <option value="">选择所属省</option>
                                <?php if(is_array($shenglist)): $i = 0; $__LIST__ = $shenglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <select name="b[city]" lay-filter="city">
                                <option  value="">选择所属城市</option>
                                <?php if(is_array($reglist)): $i = 0; $__LIST__ = $reglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><option class="sheng" value="<?php echo ($r["value"]); ?>"><?php echo ($r["text"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <select name="b[area]" lay-filter="area">
                                <option  value="">选择所属区</option>
                                <?php if(is_array($reglist)): $i = 0; $__LIST__ = $reglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i; endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商户详细地址<span style="color: red">*</span>：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="b[address]" lay-verify="" autocomplete="off" placeholder=""
                                   class="layui-input" value="">
                        </div>
                    </div>

                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">商户类型：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="b[type]" checked value="1" title="个人">
                        <input type="radio" name="b[type]" value="2" title="企业" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">商户证件类型：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="b[certtype]" checked value="1" title="身份证">
                        <input type="radio" name="b[certtype]" value="2" title="营业执照" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商户营业执照照片<span style="color: red">*</span>：</label>
                    <form class="layui-form" action="" autocomplete="off" id="bankform1">
                        <div class="layui-upload">
                            <button type="button" class="layui-btn test" id="test1">上传图片</button>
                            <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;">
                                    <input type="hidden" name="b[buslicpic]" lay-filter="required" id="wx_img1" autocomplete="off"  class="layui-input" value="">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo1">
                                    <p id="demoText1"></p>
                                </div>
                            </div>
                        </div>
                    </form>
                    <label class="layui-form-label">商户身份证正面照片<span style="color: red">*</span>：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn test" id="test2">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[legfrontpic]" lay-filter="required" id="wx_img2" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo2">
                                <p id="demoText2"></p>
                            </div>
                        </div>
                    </div>

                    <label class="layui-form-label">商户身份证反面照片<span style="color: red">*</span>：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn test"  id="test3">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[legbackpic]" lay-filter="required" id="wx_img3" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo3">
                                <p id="demoText3"></p>
                            </div>
                        </div>
                    </div>
                    <label class="layui-form-label">商户手持身份证照片<span style="color: red">*</span>：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn"  id="test4">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[handpic]" lay-filter="required" id="wx_img4" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo4">
                                <p id="demoText4"></p>
                            </div>
                        </div>
                    </div>
                    <label class="layui-form-label">商户门头照照片：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn "  id="test5">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[doorpic]" lay-filter="required" id="wx_img5" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo5">
                                <p id="demoText5"></p>
                            </div>
                        </div>
                    </div>
                    <label class="layui-form-label">商户银行开户照片：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn "  id="test6">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[accopenpic]" lay-filter="required" id="wx_img6" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo6">
                                <p id="demoText6"></p>
                            </div>
                        </div>
                    </div>

                    <label class="layui-form-label">商户收银台照片：</label>
                    <div class="layui-upload">
                        <button type="button" class="layui-btn "  id="test7">上传图片</button>
                        <div class="layui-upload-list">
                            <div style="width: 100px;margin-left: 11%;">
                                <input type="hidden" name="b[cashierpic]" lay-filter="required" id="wx_img7" autocomplete="off"  class="layui-input" value="<?php echo ($vo["logo"]); ?>">
                                <img class="layui-upload-img" style="width: 100px;height: 100px;" src="/web/up/images/imgadd.png" id="demo7">
                                <p id="demoText7"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item layui-form-text">
                        <label class="layui-form-label">商户通道费率参数：</label>
                        <div class="layui-input-block">
                            <textarea placeholder="" class="layui-textarea" name="b[channelinfo]">bnwxQR:25--0;otherFul:25--0</textarea>
                        </div>
                    </div>

                    <div class="layui-input-block">

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
                        <button class="layui-btn" lay-submit="" lay-filter="save">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
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
<script  type="text/javascript">

        layui.use(['laydate', 'form', 'layer', 'table','upload', 'element'], function() {
        var laydate = layui.laydate //日期
            ,form = layui.form //分页
            ,layer = layui.layer //弹层
            ,table = layui.table //表格
            ,element = layui.element //元素操作
            ,laydate = layui.laydate
            ,upload = layui.upload;



        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test1'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,dataType:"json"
            ,before: function(obj){
                console.log(obj);
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo1').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img1').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }


        });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test2'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo2').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img2').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }

        });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test3'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo3').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img3').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }

    });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test4'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo4').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img4').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }
            ,error: function(){
                //演示失败状态，并实现重传
                var demoText = $('#demoText4');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            }
        });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test5'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo5').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img5').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }
            ,error: function(){
                //演示失败状态，并实现重传
                var demoText = $('#demoText5');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            }
        });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test6'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo6').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img6').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }
            ,error: function(){
                //演示失败状态，并实现重传
                var demoText = $('#demoText6');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            }
        });
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test7'
            ,url: '<?php echo U("IncomingParts/uploadImg");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo7').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                console.log(res);
                //如果上传失败
                $('#wx_img7').val(res['url']);
                return layer.msg(res['msg']);
                //上传成功
            }
            ,error: function(){
                //演示失败状态，并实现重传
                var demoText = $('#demoText7');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-mini demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function(){
                    uploadInst.upload();
                });
            }
        });
        //监听提交
        form.on('submit(save)', function(data){
            $.ajax({
                url:"<?php echo U('IncomingParts/registerCha');?>",
                type:"post",
                data:$('#bankform').serialize(),
                success:function(res){


                    if(res.code){
                        layer.alert("进件成功");false;

                    }else{
                        layer.alert(res.msg);false;
                    }
                }
            });
            return false;
        });
        var form = layui.form;
        form.on('select(cardprovince)', function(data){
            var areaId=data.elem.value;
            $.ajax({
                type: 'POST',
                url: "<?php echo U('IncomingParts/cargetCity');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[cardcity]']").empty();
                    var html = "<option value=''>选择所属城市</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.value + "'>" + k.text + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[cardcity]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });
        form.on('select(cardcity)', function(data){
            var areaId=data.elem.value;
            $.ajax({
                type: 'POST',
                url: "<?php echo U('IncomingParts/cargetArea');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[cardarea]']").empty();
                    var html = "<option value=''>选择所属城市</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.value + "'>" + k.text + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[cardarea]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });
        form.on('select(province)', function(data){
            var areaId=data.elem.value;
            $.ajax({
                type: 'POST',
                url: "<?php echo U('IncomingParts/getCity');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[city]']").empty();
                    var html = "<option value=''>选择所属城市</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.value + "'>" + k.text + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[city]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });
        form.on('select(city)', function(data){
            var areaId=data.elem.value;
            $.ajax({
                type: 'POST',
                url: "<?php echo U('IncomingParts/getArea');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[area]']").empty();
                    var html = "<option value=''>选择所属城市</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.value + "'>" + k.text + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[area]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });

        form.on('select(bankno)', function(data){
            var areaId=data.elem.value;

            $.ajax({
                type: 'POST',
                url: "<?php echo U('IncomingParts/getBankList');?>",
                data: {areaId:areaId},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    //empty() 方法从被选元素移除所有内容
                    $("select[name='b[branchno]']").empty();
                    var html = "<option value=''>选择支行</option>";
                    $(e.data).each(function (v, k) {
                        html += "<option value='" + k.value + "'>" + k.text + "</option>";
                    });
                    //把遍历的数据放到select表里面
                    $("select[name='b[branchno]']").append(html);
                    //从新刷新了一下下拉框
                    form.render('select');      //重新渲染
                }
            });
        });

    });


</script>



</body>
</html>