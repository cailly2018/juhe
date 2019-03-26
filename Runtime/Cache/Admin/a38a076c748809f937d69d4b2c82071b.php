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
<link rel="stylesheet" href="/Public/Front/js/plugins/layui/css/layui.css">
<style>
.layui-form-label {width:110px;padding:4px}
.layui-table td, .layui-table th {padding:6px 10px;}
.layui-table td input {height:24px;line-height:24px;width:100px;}
.layui-form-item .layui-form-checkbox[lay-skin="primary"]{margin-top:0;}
</style>
<body>
    <div class="wrapper wrapper-content animated">
        <div class="row">
            <div class="col-sm-12">
			<form class="layui-form" action="" id="product">
			<input type="hidden" name="id" value="<?php echo ($pd["id"]); ?>">
			  <div class="layui-form-item">
				<label class="layui-form-label">通道名称：</label>
				<div class="layui-input-block">
				  <input type="text" name="pd[name]" autocomplete="off" lay-verify="required" value="<?php echo ($pd["name"]); ?>" placeholder="通道名称" class="layui-input">
				</div>
			  </div>
			  
			  <div class="layui-form-item">
				<label class="layui-form-label">通道代码（英文字符）：</label>
				<div class="layui-input-block">
				  <input type="text" name="pd[code]" autocomplete="off" lay-verify="required" value="<?php echo ($pd["code"]); ?>" placeholder="通道代码（英文字符）" class="layui-input">
				</div>
			  </div>
			  
			  <div class="layui-form-item">
				<label class="layui-form-label">分类：</label>
				<div class="layui-input-block">
				<select name="pd[paytype]" lay-verify="required" id="paytypes" lay-search="">
				  <option value="">直接选择或搜索选择</option>
				  <?php if(is_array($paytypes)): $i = 0; $__LIST__ = $paytypes;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?><option <?php if($pd[paytype] == $b[id]): ?>selected<?php endif; ?> value="<?php echo ($b["id"]); ?>"><?php echo ($b["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				  </select>
				</div>
			  </div>
			  
			  <div class="layui-form-item">
				<label class="layui-form-label">接口模式：</label>
				<div class="layui-input-block">
				  <input type="radio" name="pd[polling]" lay-filter="polling" <?php if($pd[polling] == 0): ?>checked<?php endif; ?> value="0" title="单独">
				  <input type="radio" name="pd[polling]" lay-filter="polling" <?php if($pd[polling] == 1): ?>checked<?php endif; ?> value="1" title="轮询">
				  <div id="selmodel" style="display:<?php if($pd[polling]): ?>none<?php endif; ?>;">
					<select name="pd[channel]" lay-verify="" id="channels" lay-search="">
					<option value="">直接选择或搜索选择</option>
					<?php if(is_array($channels)): $i = 0; $__LIST__ = $channels;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$c): $mod = ($i % 2 );++$i; if($c[paytype] == $pd[paytype]): ?><option <?php if($pd[channel] == $c[id]): ?>selected<?php endif; ?> value="<?php echo ($c["id"]); ?>"><?php echo ($c["title"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</select>
				  </div>
				  <table class="layui-table" lay-skin="line" id="pdtable" style="display:<?php if(!$pd[polling]): ?>none<?php endif; ?>;">
					  <thead>
						<tr>
							<th></th>
							<th>通道代码</th>
							<th>通道名称</th>
							<th>权重(1-9)</th>
						</tr>
					  </thead>
					  <tbody>
					  <?php if(is_array($channels)): $i = 0; $__LIST__ = $channels;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$c): $mod = ($i % 2 );++$i; if($c['paytype'] == $pd['paytype']): ?><tr>
							<td><input type="checkbox" name="w[<?php echo ($c["id"]); ?>][pid]" <?php if($pd['weight'][$c['id']][pid]): ?>checked<?php endif; ?>
								lay-skin="primary" value="<?php if($pd['weight'][$c['id']]): echo ($pd['weight'][$c['id']][pid]); else: echo ($c['id']); endif; ?>"></td>
							<td><?php echo ($c["id"]); ?></td>
							<td><?php echo ($c["title"]); ?></td>
							<td><input type="number" min="0" max="9" name="w[<?php echo ($c["id"]); ?>][weight]"
									   class="layui-input" value="<?php echo ($pd['weight'][$c['id']][weight]); ?>"></td>
						</tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					  </tbody>
				  </table>
				</div>
			  </div>

			  <div class="layui-form-item">
				<label class="layui-form-label">状态：</label>
				<div class="layui-input-block">
				  <input type="radio" name="pd[status]" <?php if($pd[status] == 1): ?>checked<?php endif; ?> value="1" title="开启" checked="">
				  <input type="radio" name="pd[status]" <?php if($pd[status] == 0): ?>checked<?php endif; ?> value="0" title="关闭">
				</div>
			  </div>
			  
			  <div class="layui-form-item">
				<label class="layui-form-label">用户端：</label>
				<div class="layui-input-block">
				  <input type="radio" name="pd[isdisplay]" <?php if($pd[isdisplay] == 1): ?>checked<?php endif; ?> value="1" title="显示" checked="">
				  <input type="radio" name="pd[isdisplay]" <?php if($pd[isdisplay] == 0): ?>checked<?php endif; ?> value="0" title="不显示">
				</div>
			  </div>
			  
			  <div class="layui-form-item">
				<div class="layui-input-block">
				  <button class="layui-btn" lay-submit="submit" lay-filter="add">提交保存</button>
				</div>
			  </div>
			</form>
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
var channels = <?php echo ($channellist); ?>;
layui.use(['layer', 'form'], function(){
  var form = layui.form
  ,layer = layui.layer;

  //监听radio
  form.on('radio(polling)', function(data){
		  //console.log(data.elem); //得到radio原始DOM对象
		  //console.log(data.value); //被点击的radio的value值
		var pty = $('#paytypes').val();
        var html = '';
		  if(!pty){
			layer.msg("未选择分类!");
			return false;
		  }
		if(data.value == 0){
			$('#selmodel').css('display','');
			$('#pdtable').css('display','none');
			html += '<option value="">直接选择或搜索选择</option>';
			for(var i in channels){
				if(pty==channels[i].paytype){
					html += '<option value='+channels[i].id+'>'+channels[i].title+'</option>';
				}
			}
			$('#channels').html(html);
		}else{
			$('#selmodel').css('display','none');
			$('#pdtable').css('display','');
			for(var i in channels){
				if(pty == channels[i].paytype){
					html += '<tr>';
					html += '<td><input type="checkbox" name="w['+channels[i].id+'][pid]" lay-skin="primary" value="'+channels[i].id+'"></td>';
					html += '<td>'+channels[i].id+'</td>'
					html += '<td>'+channels[i].title+'</td>';
					html += '<td><input type="number" min="0" max="9" name="w['+channels[i]
							.id+'][weight]" class="layui-input" value=""></td>';
					html += '</tr>';
				}
			}
			$('#pdtable > tbody').html(html);
		}
		form.render();
  });

    //全选
    form.on('checkbox(allChoose)', function(data){
        var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
        child.each(function(index, item){
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });

  //监听提交
  form.on('submit(add)', function(data){
    $.ajax({
		url:"<?php echo U('Channel/saveProduct');?>",
		type:"post",
		data:$('#product').serialize(),
		success:function(res){
			if(res.status){
				layer.alert("操作成功", {icon: 6},function () {
					parent.location.reload();
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
<!--统计代码，可删除-->
</body>
</html>