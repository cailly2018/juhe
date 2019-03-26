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
  <div class="col-sm-12">
    <?php if($installpwd == $member['password']): ?><p class="bg-danger" style="padding:15px;">警告！您目前使用的是安装密码，请修改！</p><?php endif; ?>
      <p class="bg-success" style="padding:15px;"> 用户名：<strong style="color:#036"><?php echo ($member ["username"]); ?></strong> |【<span style="color:#F30">
            <?php switch($member['groupid']): case "1": ?>总管理员<?php break;?>
            <?php case "2": ?>运营管理员<?php break;?>
			<?php case "3": ?>财务管理员<?php break; endswitch;?>
        </span>】
      </p>
  </div>
</div>
 
<div class="row">

  <div class="col-md-6">
    <div class="ibox float-e-margins">
      <div class="ibox-title"><h5>今日交易统计</h5></div>
      <div class="ibox-content no-padding">
        <div class="panel-body">
          <div id="dday" style="height: 180px;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="ibox float-e-margins">
      <div class="ibox-title"><h5>7天交易统计</h5></div>
      <div class="ibox-content no-padding">
        <div class="panel-body">
          <div id="dweek" style="height: 180px;"></div></div>
      </div>
    </div>
  </div>

  <div class="col-md-12">
    <div class="ibox float-e-margins">
      <div class="ibox-title"><h5>月度交易统计</h5></div>
      <div class="ibox-content no-padding">
        <div class="panel-body">
          <div class="panel-group" id="version">
            <div class="col-lg-12"><div id="dmonth" style="height:280px;"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!-- 全局js -->
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
<script src="/Public/Front/js/echarts.common.min.js"></script>
<script>
    layui.use(['laypage', 'layer', 'form'], function () {
        var form = layui.form,
            layer = layui.layer,
            $ = layui.jquery;
        });
    function reset_pwd(title,url,w,h){
      x_admin_show(title,url,w,h);
    }
</script>
<script>
    var myChartday = echarts.init(document.getElementById('dday'));
    var myChartweek = echarts.init(document.getElementById('dweek'));
    var myChartmonth = echarts.init(document.getElementById('dmonth'));
    // 使用刚指定的配置项和数据显示图表。
    myChartday.setOption({
        title:{
            text:'实时统计(共<?php echo ($ddata["num"]); ?>笔)',
            x:'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            x: 'left',
            data:['今日交易金额','今日收入金额','今日支持金额']
        },
        series: [
            {
                name:'交易统计',
                type:'pie',
                radius: ['50%', '70%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '14',
                            fontWeight: 'bold'
                        }
                    }
                },
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data:[
                    {value:"<?php echo ((isset($ddata["amount"]) && ($ddata["amount"] !== ""))?($ddata["amount"]):0); ?>", name:'今日交易金额'},
                    {value:"<?php echo ((isset($ddata["rate"]) && ($ddata["rate"] !== ""))?($ddata["rate"]):0); ?>", name:'今日收入金额'},
                    {value:"<?php echo ((isset($ddata["total"]) && ($ddata["total"] !== ""))?($ddata["total"]):0); ?>", name:'今日支出金额'},
                ]
            }
        ]
    });
    myChartweek.setOption({
        title:{
            text:'7天统计(共<?php echo ($wdata["num"]); ?>笔)',
            x:'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            x: 'left',
            data:['7日交易金额','7日收入金额','7日支持金额']
        },
        series: [
            {
                name:'交易统计',
                type:'pie',
                radius: ['50%', '70%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '14',
                            fontWeight: 'bold'
                        }
                    }
                },
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data:[
                    {value:"<?php echo ((isset($wdata["amount"]) && ($wdata["amount"] !== ""))?($wdata["amount"]):0); ?>", name:'7日交易金额'},
                    {value:"<?php echo ((isset($wdata["rate"]) && ($wdata["rate"] !== ""))?($wdata["rate"]):0); ?>", name:'7日收入金额'},
                    {value:"<?php echo ((isset($wdata["total"]) && ($wdata["total"] !== ""))?($wdata["total"]):0); ?>", name:'7日支出金额'},
                ]
            }
        ]
    });
    myChartmonth.setOption({
        tooltip : {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#6a7985'
                }
            }
        },
        legend: {
            data:['交易金额','收入金额','支出金额']
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                data : [<?php echo (implode($mdata["mdate"],",")); ?>]
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'交易金额',
                type:'line',
                stack: '总量',
                areaStyle: {normal: {}},
                data:[<?php echo (implode($mdata["amount"],",")); ?>]
            },
            {
                name:'收入金额',
                type:'line',
                stack: '总量',
                areaStyle: {normal: {}},
                data:[<?php echo (implode($mdata["rate"],",")); ?>]
            },
            {
                name:'支出金额',
                type:'line',
                stack: '总量',
                areaStyle: {normal: {}},
                data:[<?php echo (implode($mdata["total"],",")); ?>]
            },
        ]
    });
</script>
<?php echo tongji(0);?>
</body>
</html>