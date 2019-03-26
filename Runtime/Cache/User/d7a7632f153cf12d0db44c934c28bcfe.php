<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title><?php echo ($sitename); ?></title>
<!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
<link rel="shortcut icon" href="favicon.ico">
<link href="<?php echo ($siteurl); ?>Public/Front/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo ($siteurl); ?>Public/Front/css/font-awesome.min.css" rel="stylesheet">
<link href="<?php echo ($siteurl); ?>Public/Front/css/animate.css" rel="stylesheet">
<link href="<?php echo ($siteurl); ?>Public/Front/css/style.css" rel="stylesheet">
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
  <!--左侧导航开始-->
  <nav class="navbar-default navbar-static-side" role="navigation">
    <div class="nav-close"><i class="fa fa-times-circle"></i></div>
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
            <span  style="margin-left: 25%;"><img alt="image" class="img-circle" src="<?php echo ($siteconfig["logo"]); ?>"
                       style="width: 64px;height: 64px;"></span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <span class="clear">
                    <span class="block m-t-xs" style="margin-left: 25%;">
                        <strong class="font-bold"><?php echo ($fans["username"]); ?><br>(ID：<?php echo ($fans["memberid"]); ?>)</strong>
                    </span>
                    <span class="text-muted text-xs block" tyle="margin-left: 25%;" >
                        <?php if($fans['authorized'] == 0): ?><span >未认证</span>
                        <?php else: ?>
                            <span>认证用户</span><?php endif; ?>
                        <b class="caret"></b>logo
                    </span>
                </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="J_menuItem" href="<?php echo U("Account/editPassword");?>">修改密码</a> </li>
                        <li class="divider"></li>
                        <li><a href="<?php echo U("Login/loginout");?>">安全退出</a> </li>
                    </ul>
                </div>
                <div class="logo-element">MENU</div>
            </li>
            <li><a href="#"> <i class="fa fa-home"></i> <span
                    class="nav-label">管理中心</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="<?php echo U('Index/index');?>"> <span class="nav-label">控制面板</span> </a></li>
                         <?php if($fans[agent_cate] > 4): ?><li><a href="<?php echo U("Account/qrcode");?>" class="J_menuItem"><strong>台卡管理</strong></a></a> </li><?php endif; ?>
                    </ul>

            </li>

            <?php if($fans[groupid] > 4): ?><!--
            <li><a href="#"> <i class="fa fa-dollar"></i> <span
                    class="nav-label">我要收款</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Account/qrcode");?>" class="J_menuItem"><strong>收款二维码</strong></a></a> </li>

                    <li><a href="<?php echo U('Account/link');?>" class="J_menuItem"> <span class="nav-label">收款链接</span> </a></li>

                </ul>
            </li>
            --><?php endif; ?>

            <?php if($fans[groupid] == 4 and $fans[open_charge] == 1): ?><li><a href="#"> <i class="fa fa-dollar"></i> <span
                        class="nav-label">充值入口</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="<?php echo U("Account/qrcode");?>" class="J_menuItem"><strong>充值二维码</strong></a></a> </li>

                        <li><a href="<?php echo U('Account/link');?>" class="J_menuItem"> <span class="nav-label">充值链接</span> </a></li>

                    </ul>
                </li><?php endif; ?>
            <li><a href="#"> <i class="fa fa-volume-up"></i> <span
                    class="nav-label">公告</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Index/gonggao");?>" class="J_menuItem"><strong>站内公告</strong></a></a> </li>
                </ul>
            </li>
            <li><a href="#"> <i class="fa fa fa-user"></i> <span class="nav-label">账户管理</span> <span
                    class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Account/profile");?>" class="J_menuItem"><strong>基本信息</strong></a> </li>
                    <li><a href="<?php echo U("Account/bankcard");?>" class="J_menuItem"><strong>银行卡管理</strong></a> </li>
                    <li><a href="<?php echo U("Account/authorized");?>" class="J_menuItem"><strong>认证信息</strong></a> </li>
                    <li><a href="<?php echo U("Account/editPassword");?>" class="J_menuItem"><strong>登录密码</strong></a> </li>
                    <li><a href="<?php echo U("Account/editPaypassword");?>" class="J_menuItem"><strong>支付密码</strong></a> </li>
                    <li><a href="<?php echo U("Account/loginrecord");?>" class="J_menuItem"><strong>登录记录</strong></a> </li>
                    <li><a href="<?php echo U("Account/google");?>" class="J_menuItem"><strong>Google身份验证</strong></a> </li>
                </ul>
            </li>
            <li><a href="#"> <i class="fa fa-money"></i> <span class="nav-label">财务管理</span> <span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Account/changeRecord");?>" class="J_menuItem"><strong>资金记录</strong></a> </li>
                    <li><a href="<?php echo U("Account/channelFinance");?>" class="J_menuItem"><strong>通道分析</strong></a> </li>
                    <li><a href="<?php echo U("Account/complaintsDeposit");?>" class="J_menuItem"><strong>保证金明细</strong></a> </li>
                    <li><a href="<?php echo U("Account/frozenMoney");?>" class="J_menuItem"><strong>冻结资金明细</strong></a> </li>
                    <?php if(($fans[groupid]) == "4"): ?><li><a href="<?php echo U("Account/reconciliation");?>" class="J_menuItem"><strong>对账单</strong></a> </li><?php endif; ?>
                </ul>
            </li>
            <li><a href="#"> <i class="fa fa fa-check"></i> <span class="nav-label">结算管理</span> <span
                    class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">

                <!--    <?php $oss=array(112,153,156);?>
                    <?php if (in_array($fans['uid'],$oss)):?>
                    <?php endif ;?>
                    -->
                    <li><a href="<?php echo U("Withdrawal/dfapplyshu");?>" class="J_menuItem"><strong>代付申请</strong></a>     </li>


                    <li><a href="<?php echo U("Withdrawal/payment");?>" class="J_menuItem"><strong>代付记录</strong></a>     </li>

                    <?php if($siteconfig['df_api'] and $fans[df_api]): ?><li><a href="<?php echo U("Withdrawal/check");?>" class="J_menuItem"><strong>商户代付管理</strong></a>     </li><?php endif; ?>
                </ul>
            </li>
            <?php if(($fans[groupid]) != "4"): ?><li><a href="#"> <i class="fa fa fa-gears"></i> <span class="nav-label">订单管理</span> <span
                        class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="<?php echo U("Agent/order");?>" class="J_menuItem"><strong>所有订单</strong></a></li>
                       <li><a href="<?php echo U("Agent/order",['status'=>2]);?>" class="J_menuItem"><strong>成功订单</strong></a> </li>
                         <li><a href="<?php echo U("Agent/order",['status'=>4]);?>" class="J_menuItem"><strong>未支付订单</strong></a> </li>
                       </ul>
                </li><?php endif; ?>
            <?php if(($fans[groupid]) == "4"): ?><li><a href="#"> <i class="fa fa fa-sellsy"></i> <span class="nav-label">订单管理</span> <span
                    class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Order/index");?>" class="J_menuItem"><strong>所有订单</strong></a>
                    </li>
                    <li><a href="<?php echo U("Order/index",['status'=>2]);?>" class="J_menuItem"><strong>成功订单</strong></a>
                    </li>
                    <li><a href="<?php echo U("Order/index",['status'=>1]);?>" class="J_menuItem"><strong>手工补发</strong></a>
                    </li>
                    <li><a href="<?php echo U("Order/index",['status'=>4]);?>" class="J_menuItem"><strong>未支付订单</strong></a>
                    </li>
                </ul>
            </li><?php endif; ?>
         
             <?php if(($fans[groupid]) != "4"): ?><li><a href="#"> <i class="fa fa fa-gears"></i> <span class="nav-label">代理管理</span> <span
                        class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <?php if($siteconfig['invitecode']): ?><li><a href="<?php echo U("Agent/invitecode");?>" class="J_menuItem"><strong>注册邀请码</strong></a> </li><?php endif; ?>
                        <li><a href="<?php echo U("Agent/member");?>" class="J_menuItem"><strong>下级商户管理</strong></a> </li>
                        <li><a href="javascript:alert('正在开发完善中...');" class="J_menuItem" style="display:none;"><strong>提成记录</strong></a>
                        </li>
                    </ul>
                </li><?php endif; ?>

            <li><a href="#"> <i class="fa fa fa-bank"></i> <span class="nav-label">API管理</span> <span
                    class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="<?php echo U("Channel/index");?>" class="J_menuItem"><strong>查看通道费率</strong></a>  </li>
                    <?php if(($fans[groupid]) == "4"): ?><li><a href="<?php echo U("Channel/apidocumnet");?>" class="J_menuItem"><strong>API开发文档</strong></a>  </li><?php endif; ?>
                </ul>
            </li>


        </ul>
    </div>
</nav>
    
  <!--左侧导航结束-->
  <!--右侧部分开始-->
  <div id="page-wrapper" class="gray-bg dashbard-1">
    <div class="row border-bottom">
      <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
          <form role="search" class="navbar-form-custom" method="post" action="#">
            <div class="form-group">
              <input type="text" placeholder="请输入您需要查找的内容 …" class="form-control" name="top-search" id="top-search">
            </div>
          </form>
        </div>
        <ul class="nav navbar-top-links navbar-right">

          <li class="hidden-xs"> <i class="fa fa-user"></i> <?php echo ($fans["username"]); ?> </li>
          <li class="dropdown hidden-xs"> <a  href="<?php echo ($loginout); ?>" class="right-sidebar-toggle"
            aria-expanded="false"> <i class="fa fa-logout"></i> 退出 </a> </li>
        </ul>
      </nav>
    </div>
    <div class="row content-tabs">
      <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i> </button>
      <nav class="page-tabs J_menuTabs">
        <div class="page-tabs-content"> <a href="javascript:;" class="active J_menuTab"
                                           data-id="<?php echo U('Index/main');?>">Dashboard</a> </div>
      </nav>
      <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i> </button>
      <div class="btn-group roll-nav roll-right">
        <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span> </button>
        <ul role="menu" class="dropdown-menu dropdown-menu-right">
          <li class="J_tabShowActive"><a>定位当前选项卡</a> </li>
          <li class="divider"></li>
          <li class="J_tabCloseAll"><a>关闭全部选项卡</a> </li>
          <li class="J_tabCloseOther"><a>关闭其他选项卡</a> </li>
        </ul>
      </div>
      </div>
    <div class="row J_mainContent" id="content-main">
      <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<?php echo U('Index/main');?>" frameborder="0"
              data-id="<?php echo U('Index/main');?>" seamless></iframe>
    </div>
    <div class="footer">
      <div class="pull-right">&copy; 2011-2017 <?php echo ($siteconfig['company']); ?>(<?php echo C('SOFT_VERSION');?>)</div>
    </div>
  </div>
  <!--右侧部分结束-->
 
</div>
<!-- 全局js -->
<script src="<?php echo ($siteurl); ?>Public/Front/js/jquery.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/bootstrap.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/layer/layer.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/hplus.js"></script>
<script type="text/javascript" src="<?php echo ($siteurl); ?>Public/Front/js/contabs.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/pace/pace.min.js"></script>
</body>
</html>