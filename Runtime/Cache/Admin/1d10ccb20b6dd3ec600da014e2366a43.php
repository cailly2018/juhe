<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title><?php echo C("WEB_TITLE");?></title>
<link rel="shortcut icon" href="favicon.ico">
<link href="/Public/Front/css/bootstrap.min.css" rel="stylesheet">
<link href="/Public/Front/css/font-awesome.min.css" rel="stylesheet">
<link href="/Public/Front/css/animate.css" rel="stylesheet">
<link href="/Public/Front/css/style.css" rel="stylesheet">
<link rel="stylesheet" href="/Public/Front/js/plugins/layui/css/layui.css"  media="all">
<style>
.layui-form-switch {width:54px;}
</style>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <a href="javascript: history.back();" class="pull-left" style="margin-right: 10px;" style=""><i class="layui-icon">&#xe65a;</i></a>
                        <h5><?php echo ($channel['title']); ?>扩展字段管理</h5>
                        <div class="row">
                            <div class="col-sm-2 pull-right">
                                <a href="javascript:;" id="addField" class="layui-btn">添加扩展字段</a>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>

                                        <th>编号</th>
                                        <th>渠道代码</th>
                                        <th>字段名称</th>
                                        <th>别名</th>
                                        <th>备注</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$p): $mod = ($i % 2 );++$i;?><tr>
                                        <td><?php echo ($p["id"]); ?></td>
                                        <td><?php echo ($p["code"]); ?></td>
                                        <td><?php echo ($p["name"]); ?></td>
                                        <td><?php echo ($p["alias"]); ?></td>
                                        <td><?php echo ($p["remark"]); ?></td>
                                        <td>
                                            <div class="layui-btn-group">
                                                <button class="layui-btn layui-btn-small" onclick="admin_edit('编辑扩展字段','<?php echo U('PayForAnother/editExtendFields',array('id'=>$p[id]));?>')">编辑</button>
                                                <button class="layui-btn layui-btn-small" onclick="admin_del(this,'<?php echo $p[id];?>')">删除</button>
                                            </div>
                                        </td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>    
                                </tbody>
                            </table>
                        </div>
                        <div class="page"><?php echo ($page); ?></div>
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
<!-- 注意：如果你直接复制所有代码到本地，上述js路径需要改成你本地的 -->
<script>
layui.use(['layer', 'form'], function(){
  var form = layui.form
  ,layer = layui.layer;
  
  //监听提交
  $('#addField').on('click',function(){
    var w=640,h;
    if (h == null || h == '') {
        h=($(window).height() - 50);
    };
    layer.open({
        type: 2,
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        area: [w+'px', h +'px'],
        shade:0.4,
        title: "添加扩展字段",
        content: "<?php echo U('PayForAnother/addExtendFields', array('id' => $channel['id']));?>"
    });
  });
});
 //编辑
 function admin_edit(title,url){
    var w=800,h;
    if (h == null || h == '') {
        h=($(window).height() - 50);
    };
    layer.open({
        type: 2,
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        area: [w+'px', h +'px'],
        shade:0.4,
        title: title,
        content: url
    });
 }
 /*删除*/
function admin_del(obj,id){
    layer.confirm('确认要删除吗？',function(index){
        $.ajax({
            url:"<?php echo U('PayForAnother/delExtendFields');?>",
            type:'post',
            data:'id='+id,
            success:function(res){
                if(res.status){
                $(obj).parents("tr").remove();
                layer.msg('已删除!',{icon:1,time:1000});
                }
            }
        });
    });
}
</script>
</body>
</html>