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
      <!--条件查询-->
      <div class="ibox-title">
        <h5>订单管理</h5>
        <div class="ibox-tools">
          <i class="layui-icon" onclick="location.replace(location.href);" title="刷新"
             style="cursor:pointer;">ဂ</i>
        </div>
      </div>
      <!--条件查询-->
      <div class="ibox-content">
        <form class="layui-form" action="" method="get" autocomplete="off" id="orderform">
          <input type="hidden" name="m" value="<?php echo ($model); ?>">
          <input type="hidden" name="c" value="Order">
          <input type="hidden" name="a" value="index" id="action">
          <input type="hidden" name="p" value="1">
          <div class="layui-form-item">
            <div class="layui-inline">
              <div class="layui-input-inline">
                <input type="text" name="memberid" autocomplete="off" placeholder="请输入商户号"
                       class="layui-input" value="<?php echo ($_GET['memberid']); ?>">
              </div>
              <div class="layui-input-inline">
                <input type="text" name="pay_orderid" autocomplete="off" placeholder="请输入系统订单号"
                       class="layui-input" value="<?php echo ($_GET['pay_orderid']); ?>">
              </div>

              <div class="layui-input-inline">
                <input type="text" name="orderid" autocomplete="off" placeholder="请输入下游订单号"
                       class="layui-input" value="<?php echo ($_GET['orderid']); ?>">
              </div>
              <div class="layui-input-inline">
                <input type="text" name="body" autocomplete="off" placeholder="请输入订单描述"
                       class="layui-input" value="<?php echo ($_GET['body']); ?>">
              </div>

              <div class="layui-input-inline" style="width:300px">
                <input type="text" class="layui-input" name="createtime" id="createtime"
                       placeholder="创建起始时间" value="<?php echo ($_GET['createtime']); ?>">
              </div>
              <div class="layui-input-inline" style="width:300px">
                <input type="text" class="layui-input" name="successtime" id="successtime"
                       placeholder="成功时间范围" value="<?php echo ($_GET['successtime']); ?>">
              </div>
            </div>
            <div class="layui-inline">
              <div class="layui-input-inline">
                <select name="tongdao">
                  <option value="">全部通道</option>
                  <?php if(is_array($tongdaolist)): $i = 0; $__LIST__ = $tongdaolist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if($_GET[tongdao] == $vo[id]): ?>selected<?php endif; ?>
                    value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <div class="layui-input-inline">
                <select name="bank">
                  <option value="">全部银行</option>
                  <?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if($_GET[bank] == $vo[id]): ?>selected<?php endif; ?>
                    value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
              <div class="layui-input-inline">
                <select name="status">
                  <option value="">全部状态</option>
                  <option <?php if($_GET[status] != '' && $_GET[status] == 0): ?>selected<?php endif; ?> value="0">未处理</option>
                  <option <?php if($_GET[status] == 1): ?>selected<?php endif; ?> value="1">成功，未返回</option>
                  <option <?php if($_GET[status] == 2): ?>selected<?php endif; ?> value="2">成功，已返回</option>
                  <option <?php if($_GET[status] == '1or2'): ?>selected<?php endif; ?> value="1or2">成功，已/未返回</option>
                </select>
              </div>
              <div class="layui-input-inline">
                <select name="ddlx">
                  <option value="">订单类型</option>
                  <option <?php if($_GET[ddlx] != '' && $_GET[ddlx] == 0): ?>selected<?php endif; ?> value="0">收款订单</option>
                  <option <?php if($_GET[ddlx] == 1): ?>selected<?php endif; ?> value="1">充值订单</option>
                </select>
              </div>
			  <div class="layui-input-inline">
                <select name="is_bu">
                  <option value="">补发类型</option>
                  <option <?php if($_GET[is_bu] != '' && $_GET[is_bu] == 0): ?>selected<?php endif; ?> value="0">自动</option>
                  <option <?php if($_GET[is_bu] == 1): ?>selected<?php endif; ?> value="1">手动</option>
                </select>
              </div>
			  
            </div>

            <div class="layui-inline">
              <button type="submit" class="layui-btn"><span
                      class="glyphicon glyphicon-search"></span> 搜索
              </button>
              <a href="javascript:;" id="export" class="layui-btn layui-btn-warm"><span class="glyphicon glyphicon-export"></span> 导出数据</a>
              <?php if(($isrootadmin) == "true"): ?><a href="javascript:;" id="delorder" onclick="chooseOrder_del()" class="layui-btn layui-btn-danger"><span class="glyphicon glyphicon-trash"></span> 选择删除无效订单</a><?php endif; ?>
            </div>
          </div>
        </form>
        <?php if($_GET['status'] == '1or2'): ?><blockquote class="layui-elem-quote" style="font-size:14px;padding;8px;">今日成功交易总额：<span class="label label-info"><?php echo ($stat["todaysum"]); ?>元</span>  今日平台收入：<span class="label label-info"><?php echo ($stat["platform"]); ?>元</span>
             今日代理收入：<span class="label label-info"><?php echo ($stat["agentIncome"]); ?>元</span>
          </blockquote><?php endif; ?>
        <blockquote class="layui-elem-quote" style="font-size:14px;padding;8px;">成功交易总金额：<span class="label label-info"><?php echo ($stamount); ?>元</span> 平台收入：<span class="label label-info"><?php echo ($strate); ?>元</span>
          商户收入总金额：<span class="label label-info"><?php echo ($strealmoney); ?>元</span> 成功订单数：<span class="label label-info"><?php echo ($success_count); ?></span> 失败订单数：<span class="label label-danger"><?php echo ($fail_count); ?></span>
          投诉保证金已返回金额：<span class="label label-info"><?php echo ($complaints_deposit_unfreezed); ?></span> 投诉保证金冻结金额：<span class="label label-danger"><?php echo ($complaints_deposit_freezed); ?></span>
        </blockquote>
        <!-- <div class="ibox float-e-margins chart item">
        <div class="ibox-title"><h5>交易统计</h5></div>
          <div class="ibox-content no-padding">
            <div class="panel-body">
              <div class="panel-group" id="version">
                <div class="col-lg-12"><div id="dmonth" style="height:280px;"></div></div>
              </div>
            </div>
          </div>
        </div> -->
        <div class="list item">
          <!--交易列表-->
          <table class="layui-table" lay-data="{width:'100%',limit:<?php echo ($rows); ?>,id:'userData'}">
            <thead>
            <tr>
              <th lay-data="{field:'key',width:60}"></th>
              <th lay-data="{field:'pay_memberid', width:110}">商户编号</th>
              <th lay-data="{field:'amount', width:100,style:'color:#060;'}">交易金额</th>
              <th lay-data="{field:'rate', width:90}">手续费</th>
              <th lay-data="{field:'actualamount', width:100,style:'color:#C00;'}">实际金额</th>
              <th lay-data="{field:'applydate', width:160}">提交时间</th>
              <th lay-data="{field:'successdate', width:160}">成功时间</th>
              <th lay-data="{field:'status', width:110}">状态</th>
              <th lay-data="{field:'zh_tongdao', width:120}">支付通道</th>

  <!--            <th lay-data="{field:'ddlx', width:90}">订单类型</th>-->
              <th lay-data="{field:'pay_orderid', width:240,style:'color:#060;'}">系统订单号</th>
              <th lay-data="{field:'out_trade_id', width:240,style:'color:#060;'}">下游订单号</th>

             <!-- <th lay-data="{field:'memberid', width:160}">通道商户号</th>-->
       <!--       <th lay-data="{field:'bankname', width:120}">支付银行</th>
              <th lay-data="{field:'tjurl', width:100}">来源地址</th>
              <th lay-data="{field:'body', width:150}">订单描述</th>
              <th lay-data="{field:'is_bu', width:110}">补发类型</th>-->
              <th lay-data="{field:'op',width:160}">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                <td><?php echo ($vo["id"]); ?></td>
                <td style="text-align:center;"><?php echo ($vo["pay_memberid"]); ?></td>
                <td style="text-align:center; color:#060"><?php echo ($vo["pay_amount"]); ?></td>
                <td style="text-align:center; color:#666"><?php echo ($vo["pay_poundage"]); ?></td>
                <td style="text-align:center; color:#C00"><?php echo ($vo["pay_actualamount"]); ?></td>
                <td style="text-align:center;"><?php echo (date('Y-m-d H:i:s',$vo["pay_applydate"])); ?></td>
                <td style="text-align:center;"><?php if($vo[pay_successdate]): echo (date('Y-m-d H:i:s',$vo["pay_successdate"])); else: ?> ---<?php endif; ?></td>
                <td style="text-align:center; color:#369"><?php echo (status($vo['pay_status'])); ?></td>
                <td style="text-align:center;"><?php echo ($vo["pay_zh_tongdao"]); ?></td>
               <!-- <td>
                  <?php switch($vo[ddlx]): case "1": ?>充值<?php break;?>
                    <?php default: ?>收款<?php endswitch;?>
                </td>-->
                <td style="text-align:center; color:#090;"><?php echo ($vo[pay_orderid]); ?>
                  <?php if($vo["del"] == 1): ?><span style="color: #f00;">×</span><?php endif; ?>
                </td>
                <td style="text-align:center; color:#090;"><?php echo ($vo[out_trade_id]?$vo[out_trade_id]:$vo[pay_orderid]); ?>
                  <?php if($vo["del"] == 1): ?><span style="color: #f00;">×</span><?php endif; ?>
                </td>
              <!--  <td style="text-align:center;"><?php echo ($vo["memberid"]); ?></td>-->
               <!-- <td style="text-align:center;"><?php echo ($vo["pay_bankname"]); ?></td>-->
              <!--  <td style="text-align:center;"><a href="<?php echo ($vo["pay_tjurl"]); ?>" target="_blank" title="<?php echo ($vo["pay_tjurl"]); ?>">
                  来源地址</a></td>
                <td style="text-align:center;"><?php echo ($vo["pay_productname"]); ?></td>

				<td style="text-align:center;"><?php if($vo[is_bu]): ?><span style="color:#060">手动[<?php echo ($vo["bu_admin_name"]); ?>,<?php echo (date('Y-m-d H:i:s',$vo["bu_time"])); ?>]</span><?php else: ?>自动<?php endif; ?></td>
               -->
                <td><a
                        href="javascript:order_view('系统订单号:<?php echo ($vo["pay_orderid"]); ?>','<?php echo U('Admin/Order/show',['oid'=>$vo[id]]);?>',600,400)">查看
                </a>
                  <?php if(($vo["pay_status"]) == "0"): ?><button class="layui-btn layui-btn-mini" onclick="setOrderPaid('设置订单为已支付','<?php echo U('Admin/Order/setOrderPaid',['orderid'=>$vo[id]]);?>',600,350)">设置为已支付</button><?php endif; ?>
                </td>
              </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
          </table>
          <!--交易列表-->
          <div class="page">  
              <form class="layui-form" action="" method="get" id="pageForm"  autocomplete="off">
                  <?php echo ($page); ?>                 
                  <select name="rows" style="height: 32px;" id="pageList" lay-ignore >
                      <option value="">显示条数</option>
                      <option <?php if($_GET[rows] != '' && $_GET[rows] == 15): ?>selected<?php endif; ?> value="15">15条</option>
                      <option <?php if($_GET[rows] == 30): ?>selected<?php endif; ?> value="30">30条</option>
                      <option <?php if($_GET[rows] == 50): ?>selected<?php endif; ?> value="50">50条</option>
                      <option <?php if($_GET[rows] == 80): ?>selected<?php endif; ?> value="80">80条</option>
                      <option <?php if($_GET[rows] == 100): ?>selected<?php endif; ?> value="100">100条</option>
                  </select>
              </form>
          </div>
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
<script src="/Public/Front/js/echarts.common.min.js"></script>
<script>
      $('#pageList').change(function(){
        $('#pageForm').submit();
    });
    layui.use(['laydate', 'laypage', 'layer', 'table', 'form'], function() {
        var laydate = layui.laydate //日期
            , laypage = layui.laypage //分页
            ,layer = layui.layer //弹层
            ,form = layui.form //表单
            , table = layui.table; //表格
        //日期时间范围
        laydate.render({
            elem: '#createtime'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });
        //日期时间范围
        laydate.render({
            elem: '#successtime'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });
    });
    /*订单-查看*/
    function order_view(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
    /*订单-批量删除*/
    function delAllOrder(title, url, w, h) {
        x_admin_show(title, url, w, h);
    }
    /*订单-设置订单状态为已支付*/
      function setOrderPaid(title, url, w, h) {
          x_admin_show(title, url, w, h);
      }
    $('#export').on('click',function(){
        window.location.href
            ="<?php echo U('Admin/Order/exportorder',array('memberid'=>$_GET[memberid],'orderid'=>$_GET[orderid],'createtime'=>$_GET[createtime],'successtime'=>$_GET[successtime],'tongdao'=>$_GET[tongdao],'bank'=>$_GET[bank],'status'=>$_GET[status],'ddlx'=>$_GET[ddlx]));?>";
    });

    function chooseOrder_del() {
        var createtime=$("#createtime").val();
        if(createtime==""){
            layer.alert('请选择删除无效订单创建时间段');
            return;
        }
        layer.confirm('删除操作为真实删除，确定要删除时间段从'+createtime.replace('|','到')+'的无效订单吗？',function(index){
            $.ajax({
                url:"<?php echo U('Order/delOrder');?>",
                type:'post',
                data:{'createtime':createtime},
                success:function(res){
                    if(res.status){
                        layer.alert('删除成功！',function () {
                            location.replace(location.href);
                        });
                    }else{
                        layer.alert('删除失败！');
                    }
                }
            });
        });
    }
    
    var myChart = echarts.init(document.getElementById('dmonth'));
    myChart.setOption({
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
</body>
</html>