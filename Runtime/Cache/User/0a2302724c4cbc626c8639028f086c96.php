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

    <?php if($fans['authorized'] ==0):?>
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5 style="color: red">您的信息未认证，请及时提交验证信息  </h5>
      </div>
    </div>
    <?php endif;?>


    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5>上次登录  </h5>
      </div>
      <div class="ibox-content">
        <p>登录IP：<?php echo ($lastlogin["loginip"]); ?>，登录地址：<?php echo ($lastlogin["loginaddress"]); ?>，登录时间：<?php echo ($lastlogin["logindatetime"]); ?></p>
        <?php if(!empty($ipItem)): ?><p>可登录IP：
            <?php if(is_array($ipItem)): foreach($ipItem as $k=>$v): ?>[<?php echo ($k); ?>]<?php echo ($v); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php endforeach; endif; ?>
        </p><?php endif; ?>
      </div>
    </div>

 <!--   <span style="color: red;"><?php if($fans.authorized ==0){echo '您的信息未认证，请及时提交验证信息';}?></span>-->


  </div>
</div>
<?php if(($fans[groupid]) == "4"): ?><div class="row">
  <div class="col-sm-3">
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h4 style="font-size: 18px;">今日总订单数</h4>
      </div>
      <div class="ibox-content" style="height: 67px">
        <h1 class="no-margins"><?php echo ($stat["todayordercount"]); ?></h1>
        <div class="stat-percent font-bold text-success">单</div>
      </div>
    </div>
  </div>
  <div class="col-sm-3" >
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h4 style="font-size: 18px;">今日已付订单数</h4>
      </div>
      <div class="ibox-content" style="height: 67px">
        <h1 class="no-margins"><?php echo ($stat["todayorderpaidcount"]); ?></h1>
        <div class="stat-percent font-bold text-success">单</div>
      </div>
    </div>
  </div>
    <div class="col-sm-3">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5 style="font-size: 18px;">今日未付订单</h5>
        </div>
        <div class="ibox-content" style="height: 67px">
          <h1 class="no-margins"><?php echo ($stat["todayordernopaidcount"]); ?></h1>
          <div class="stat-percent font-bold text-success">单</div>
        </div>
      </div>
  </div>
  <div class="col-sm-3" style="height: 140px">
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5 style="font-size: 18px;">今日提交金额</h5>
      </div>
      <div class="ibox-content" style="height: 67px">
        <h1 class="no-margins"><?php echo ($stat["todayordersum"]); ?></h1>
        <div class="stat-percent font-bold text-success">元</div>
      </div>
    </div>
  </div>
</div><?php endif; ?>
  <div class="row">
    <div class="col-sm-3" style="height: 140px">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5 style="font-size: 18px;">今日成功金额</h5>
        </div>
        <div class="ibox-content" style="height: 67px">
          <?php if(($fans[groupid]) == "7"): ?><h1 class="no-margins"><?php echo ($todaysumall); ?></h1><?php endif; ?>
          <?php if(($fans[groupid]) != "7"): ?><h1 class="no-margins"><?php echo ($stat["todayorderactualsum"]); ?></h1><?php endif; ?>
          <div class="stat-percent font-bold text-success">元</div>
        </div>
      </div>
    </div>
    <?php if(($fans[groupid]) == "4"): ?><div class="col-sm-3" style="height: 140px">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5 style="font-size: 18px;">商户收入金额</h5>
        </div>
        <div class="ibox-content" style="height: 67px">
          <h1 class="no-margins"><?php echo ($stat["allpay_poundage"]); ?></h1>
          <div class="stat-percent font-bold text-success">元</div>
        </div>
      </div>
    </div><?php endif; ?>
    <div class="col-sm-3">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5 style="font-size: 18px;">可提现</h5>
        </div>
        <div class="ibox-content">
          <h1 class="no-margins"><?php echo ($fans['balance']); ?></h1>
          <div class="stat-percent font-bold text-success">元</div>
          <small>可提现</small>
        </div>
      </div>
    </div>
    <div class="col-sm-3">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5 style="font-size: 18px;">冻结</h5>
        </div>
        <div class="ibox-content">
          <h1 class="no-margins"><?php echo ($fans['blockedbalance']); ?></h1>
          <div class="stat-percent font-bold text-info">元
          </div>
          <small>待解冻</small>
        </div>
      </div>
    </div>
</div>
  <div class="row">

    <div class="col-sm-12">
      <div class="ibox float-e-margins">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>站内公告</h5>
        </div>
        <div class="ibox-content" style="padding:14px 20px;">
          <ul class="list-unstyled">
            <?php if(is_array($gglist)): $i = 0; $__LIST__ = $gglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><mark><?php echo (time_format($vo["createtime"])); ?></mark> <a href="<?php echo U('Index/showcontent',['id'=>$vo['id']]);?>"><?php echo ($vo["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>日交易统计</h5>
        </div>
        <div class="ibox-content">
          <div id="main" style="height:300px"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-describedby="tscontent" data-backdrop="static" ajaxurl="<?php echo U("Dealmanages/dealindexload");?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close modalgb" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"> <span>订单号：</span> <span id="orderidModal" style="color:#060;"></span> </h4>
      </div>
      <div class="modal-body" id="dealcontent" style="color:#000; font-family:'微软雅黑';"> 
        <!--------------------------------------------------------------------------------------------------->
        <table class="table table-condensed">
           <tr style="display:none;">
            <td style="text-align:left;">订单号：<span style="color:#090;"></span></td>
          </tr>
          <tr>
            <td style="text-align:left;">交易金额：<span style="color:#060; font-weight:bold;"></span> 元</td>
          </tr>
          <tr>
            <td style="text-align:left;">手续费：<span style="color:#666; font-weight:bold;"></span> 元</td>
          </tr>
          <tr>
            <td style="text-align:left;">实际金额：<span style="color:#C00; font-weight:bold;"></span> 元</td>
          </tr>
          <tr>
            <td style="text-align:left;">提交时间：<span style="color:#F00;"></span></td>
          </tr>
          <tr>
            <td style="text-align:left;">成功时间：<span style="color:#F00;"></span></td>
          </tr>
           <tr>
            <td style="text-align:left;">交易通道：<span></span></td>
          </tr>
           <tr>
            <td style="text-align:left;">交易银行：<span></span></td>
          </tr>
          <tr>
            <td style="text-align:left;">提交地址：<span></span></td>
          </tr>
          <tr>
            <td style="text-align:left;">页面通知返回地址：<span></span></td>
          </tr>
           <tr>
            <td style="text-align:left;">服务器点对点返回地址：<span></span></td>
          </tr>
           <tr>
            <td style="text-align:left;">状态：<span></span>&nbsp;&nbsp;<span></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
        <!---------------------------------------------------------------------------------------------------> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default modalgb" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>

<!-- 全局js -->
<script src="<?php echo ($siteurl); ?>Public/Front/js/jquery.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/bootstrap.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/content.js?v=1.0.0"></script>
<script src="/Public/Front/js/echarts.common.min.js"></script>
<script type="text/javascript">
    var myChart = echarts.init(document.getElementById('main'));
    var option = {
        title : {
            text: '交易订单概况',
            subtext: '按天统计'
        },
        tooltip : {
            trigger: 'axis'
        },
        legend: {
            data:['成交','金额']
        },
        toolbox: {
            show : true,
            feature : {
                mark : {show: true},
                dataView : {show: true, readOnly: false},
                magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                restore : {show: true},
                saveAsImage : {show: true}
            }
        },
        calculable : true,
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                data : <?php echo ($category); ?>
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'成交',
                type:'line',
                smooth:true,
                itemStyle: {normal: {areaStyle: {type: 'default'}}},
                data:<?php echo ($dataone); ?>
            },
            {
                name:'金额',
                type:'line',
                smooth:true,
                itemStyle: {normal: {areaStyle: {type: 'default'}}},
                data:<?php echo ($datatwo); ?>
            }
        ]
    };

    // 为echarts对象加载数据
    myChart.setOption(option);
</script>
</body>
</html>