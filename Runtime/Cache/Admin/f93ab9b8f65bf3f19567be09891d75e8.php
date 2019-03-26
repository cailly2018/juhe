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
              <h5>实况</h5>
              <div class="ibox-tools">
                <i class="layui-icon" onclick="location.replace(location.href);" title="刷新" style="cursor:pointer;">ဂ</i>
              </div>
            </div>
            <div class="ibox-content">

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                           
                            <th style="text-align: left;">通道名称/子账号</th>
                            <th>交易时间</th>
                            <th>今天交易金额/可交易金额</th>
                            <th>上线情况</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                               
                                <td style="text-align: left;">├─ <b style="color:red;">[通道]</b><?php echo ($vo["title"]); ?></td>
                                <td><?php echo ($vo["start_time"]); ?>时-<?php echo ($vo["end_time"]); ?>时</td>
                                <td><?php echo ($vo["paying_money"]); ?>元/<?php echo ($vo["all_money"]); ?>元</td>
                                <td><?php echo ($vo["offline_status"]); ?></td>
                            </tr>
                            <?php if($vo[account]): if(is_array($vo[account])): $i = 0; $__LIST__ = $vo[account];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><tr>
                                        <td style="text-align: left;">└─----<i style="color:red;">[子账号]</i> <?php echo ($sub["title"]); ?></td>
                                        <td><?php echo ($sub["start_time"]); ?>时---<?php echo ($sub["end_time"]); ?>时</td>
                                        <td><?php echo ($sub["paying_money"]); ?>元/<?php echo ($sub["all_money"]); ?>元</td>
                                        <td><?php echo ($sub["offline_status"]); ?></td>                                          
                                    </tr><?php endforeach; endif; else: echo "" ;endif; endif; endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="page"><?php echo ($page); ?></div>
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

</script>
</body>
</html>