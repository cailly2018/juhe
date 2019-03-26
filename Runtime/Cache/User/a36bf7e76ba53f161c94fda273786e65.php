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
<link href="/Public/Front/css/fileinput.min.css" rel="stylesheet">
<link href="/Public/Front/css/theme.css" rel="stylesheet">

<link rel="stylesheet" href="web/cj/css/bootstrap-grid.min.css">
<!--<link rel="stylesheet" type="text/css" href="web/cj/css/htmleaf-demo.css">-->
<link rel="stylesheet" href="web/cj/dist/zoomify.min.css">
<!--<link rel="stylesheet" href="web/cj/css/style.css">-->


<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>申请认证</h5>
            </div>
            <div class="ibox-content">
                <?php if($authorized == 1): ?><p class="bg-info" style="padding:10px 0px 10px 30px">您已成功认证</p><?php endif; ?>
                <?php if($authorized == 2): ?><p class="bg-info" style="padding:10px 0px 10px 30px">已提交认证，等待审核！</p><?php endif; ?>

                    <div style=" margin-bottom: 10px; margin-top: 20px;">
                    <div class="layui-inline">
                        <label class="layui-form-label">公司名称：</label>
                        <div class="layui-input-inline" style="width: 265px;">
                            <input type="text" name="p[pname]" lay-verify=""  readonly="readonly"autocomplete="off"
                                   class="layui-input" value="<?php echo ($p["pname"]); ?>">
                        </div>
                    </div>
                    <div class="layui-inline">
                            <label class="layui-form-label">营业执照号：</label>
                            <div class="layui-input-inline" style="width: 265px;">
                                <input type="text" name="p[licence]" lay-verify=""  readonly="readonly" autocomplete="off"
                                       class="layui-input" value="<?php echo ($p["licence"]); ?>">
                            </div>
                     </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">法人姓名：</label>
                        <div class="layui-input-inline" style="width: 265px;">
                            <input type="text" name="p[realname]" lay-verify="" readonly="readonly" autocomplete="off"
                                   class="layui-input" value="<?php echo ($p["realname"]); ?>">
                        </div>
                    </div>


                    <div class="layui-inline">
                        <label class="layui-form-label">身份证号码：</label>
                        <div class="layui-input-inline" style="width: 265px;">
                            <input type="text" name="p[sfznumber]" lay-verify=""  readonly="readonly" autocomplete="off"
                                   class="layui-input" value="<?php echo ($p["sfznumber"]); ?>">
                        </div>
                    </div>

                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">公司联系地址：</label>
                        <div class="layui-input-block ">
                            <input type="text" name="p[address]" lay-verify="title" autocomplete="off"
                                   placeholder="公司联系地址" class="layui-input" value="<?php echo ($p["address"]); ?>">
                        </div>
                    </div>
                  <!--  <div class="layui-form-item">
                        <div style="margin-left: 11%;" class="example">
                            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?><img  style="width: 100px;height: 100px;margin-left:20px;" src="<?php echo ($b["path"]); ?>" ><?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </div>-->
                    <div class="layui-form-item">
                        <label class="layui-form-label">身份证正面：</label>
                        <div class="layui-upload">
                            <?php if($authorized != 1): ?><button type="button" class="layui-btn " style="background-color:#1d94e4"  id="test1">上传图片</button><?php endif; ?>
                                <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;" class="example">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="
                                    <?php if($demo1 != ''): echo ($demo1); ?>
                                        <?php else: ?>
                                    /web/up/images/imgadd.png<?php endif; ?>
                                    " id="demo1">
                                    <p id="demoText1"></p>
                                </div>
                            </div>
                        </div>
                        <label class="layui-form-label">身份证反面：</label>
                        <div class="layui-upload">
                            <?php if($authorized != 1): ?><button type="button" class="layui-btn " style="background-color:#1d94e4"  id="test2">上传图片</button><?php endif; ?>
                                <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;" class="example">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="
                                    <?php if($demo2 != ''): echo ($demo2); ?>
                                    <?php else: ?>
                                    /web/up/images/imgadd.png<?php endif; ?>
                                    " id="demo2">
                                    <p id="demoText2"></p>
                                </div>
                            </div>
                        </div>

                        <label class="layui-form-label">营业执照：</label>
                        <div class="layui-upload">
                            <?php if($authorized != 1): ?><button type="button" class="layui-btn "  style="background-color:#1d94e4" id="test3">上传图片</button><?php endif; ?>
                                <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;" class="example">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="
                                    <?php if($demo3 != ''): echo ($demo3); ?>
                                    <?php else: ?>
                                    /web/up/images/imgadd.png<?php endif; ?>
                                    " id="demo3">

                                    <p id="demoText3"></p>
                                </div>
                            </div>
                        </div>
                        <label class="layui-form-label">银行卡正面：</label>
                        <div class="layui-upload">
                            <?php if($authorized != 1): ?><button type="button" class="layui-btn "  style="background-color:#1d94e4" id="test4">上传图片</button><?php endif; ?>
                            <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;" class="example">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="
                                    <?php if($demo4 != ''): echo ($demo4); ?>
                                    <?php else: ?>
                                    /web/up/images/imgadd.png<?php endif; ?>
                                    " id="demo4">
                                    <p id="demoText4"></p>
                                </div>
                            </div>
                        </div>
                        <label class="layui-form-label">银行卡反面：</label>
                        <div class="layui-upload">
                            <?php if($authorized != 1): ?><button type="button" class="layui-btn " style="background-color:#1d94e4"  id="test5">上传图片</button><?php endif; ?>
                                <div class="layui-upload-list">
                                <div style="width: 100px;margin-left: 11%;" class="example">
                                    <img class="layui-upload-img" style="width: 100px;height: 100px;" src="
                                    <?php if($demo5 != ''): echo ($demo5); ?>
                                    <?php else: ?>
                                    /web/up/images/imgadd.png<?php endif; ?>
                                    " id="demo5">
                                    <p id="demoText5"></p>
                                </div>
                            </div>
                        </div>
                        <?php if($authorized == 0): ?><div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit="" id="regBtn" lay-filter="save">提交审核</button>
                            </div>
                        </div><?php endif; ?>

                        <!--
                                            <blockquote class="layui-elem-quote">
                                                <p class="text-danger">请上传：身份证正反面、&lt;!&ndash;正面手持身份证、&ndash;&gt;营业执照、银行卡正反面图片。</p>
                                            </blockquote>
                                            <input id="input-ke-1" name="auth[]" type="file" multiple class="file-loading" accept="image">-->
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
<script src="/Public/Front/js/fileinput.min.js"></script>
<script src="/Public/Front/js/fileinput_locale_zh.js"></script>
<script src="/Public/Front/js/theme.js"></script>
<script>
    /*layui.use([ 'layer','element'], function() {
        var layer = layui.layer //弹层
            ,element = layui.element; //元素操作

    });*/
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
            ,url: '<?php echo U("Account/upload");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo1').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                if(res['status'] == 1){
                    tes(res['url'],1,res['filename'])
                }else {
                    return layer.msg(res['info']);
                }
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
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test2'
            ,url: '<?php echo U("Account/upload");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo2').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                if(res['status'] == 1){
                    tes(res['url'],2,res['filename'])
                }else {
                    return layer.msg(res['info']);
                }
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
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test3'
            ,url: '<?php echo U("Account/upload");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo3').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                if(res['status'] == 1){
                    tes(res['url'],3,res['filename'])
                }else {
                    return layer.msg(res['info']);
                }
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
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test4'
            ,url: '<?php echo U("Account/upload");?>'
            ,before: function(obj){
                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $('#demo4').attr('src', result); //图片链接（base64）
                });
            }
            ,done: function(res){
                if(res['status'] == 1){
                    tes(res['url'],4,res['filename'])
                }else {
                    return layer.msg(res['info']);
                }
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
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test5'
            ,url: '<?php echo U("Account/upload");?>'
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
                if(res['status'] == 1){
                    tes(res['url'],5,res['filename'])
                }else {
                    return layer.msg(res['info']);
                }

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
        function tes(url,type,filename) {

            $.ajax({
                type: 'POST',
                url: "<?php echo U('Account/reupload');?>",
                data: {url:url,type:type,filename:filename},
                dataType:  'json',
                success:function(e){
                    console.log(e.data);
                    return layer.msg(e['info']);
                }
            });
        }
        //监听提交
        form.on('submit(save)', function(data){
            layer.confirm('现在去申请认证吗？', function (data) {

                $.ajax({
                    type: 'POST',
                    url: "<?php echo U('Account/certification');?>",
                    data: '',
                    dataType:  'json',
                    success:function(e){
                        if(e['status'] == 1){
                            layer.msg(e['msg']);
                            setTimeout("location.reload()",5000);

                        }else {
                            return layer.msg(e['msg']);
                        }
                    }
                },'json');

            });
        });

    });

    $("#input-ke-1").fileinput({
        language: 'zh',
        theme: "explorer",
        uploadUrl: "<?php echo U('Account/upload');?>",
        allowedFileExtensions: ['jpg', 'png', 'gif'],
        overwriteInitial: false,
        initialPreviewAsData: true,
        maxFileCount: 5,
    }).on('filebatchuploadcomplete', function(event, data) {
        layer.confirm('现在去申请认证吗？', function (index) {
            window.location.href='<?php echo U("Account/certification");?>';
        });
    });


</script>


<script src="http://libs.useso.com/js/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>
<script>window.jQuery || document.write('<script src="web/cj/js/jquery-2.1.1.min.js"><\/script>')</script>
<script src="web/cj/dist/zoomify.min.js"></script>
<script type="text/javascript">
    $('.example img').zoomify();

</script>


</body>
</html>