<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">
  <title><?php echo ($sitename); ?></title>
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
                <div class="dropdown profile-element" >
            <span style="margin-left: 25%;"><img alt="image" class="img-circle" src="<?php echo ($logo); ?>"
                       style="width: 64px;height: 64px;"></span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
              <span class="clear">
                  <span class="block m-t-xs" style="margin-left: 28%;">
                      <strong class="font-bold"><?php echo ($member["username"]); ?></strong>
                  </span>
              </span>
                    </a>
                    <span style="color:#F30;margin-left: 25%;">
                <?php switch($member["groupid"]): case "1": ?>总管理员<?php break;?>
				    <?php case "2": ?>运营管理员<?php break;?>
				    <?php case "3": ?>财务管理员<?php break; endswitch;?></span>
                </div>
                <div class="logo-element">MENU</div>
            </li>
            <?php if(is_array($navmenus)): $i = 0; $__LIST__ = $navmenus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nm): $mod = ($i % 2 );++$i;?><li><a href="<?php if(!count($nm[$nm['id']])): echo U($nm[menu_name]); else: ?>#<?php endif; ?>"> <i
                    class="<?php echo ($nm['icon']); ?>"></i> <span
                        class="nav-label"><?php echo ($nm['title']); ?></span>
                    <?php if($nm[$nm['id']]): ?><span class="fa arrow"></span><?php endif; ?></a>
                    <?php if($nm[$nm['id']]): ?><ul class="nav nav-second-level">
                        <?php if(is_array($nm[$nm['id']])): $i = 0; $__LIST__ = $nm[$nm['id']];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><li><a href="<?php echo U($sub[menu_name]);?>" class="J_menuItem"><i
                                    class="<?php echo ($sub['icon']); ?>"></i> <?php echo ($sub['title']); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul><?php endif; ?>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</nav>

  <!--左侧导航结束-->
  <!--右侧部分开始-->
  <div id="page-wrapper" class="gray-bg dashbard-1">
    <div class="row border-bottom">
      <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a></div>
        <ul class="nav navbar-top-links navbar-right">
          <li class="hidden-xs">  <a href="/" target="_blank"><i class="fa fa-home"></i> 网站首页 </a></li>
          <!-- <li class="hidden-xs">  <a href="<?php echo U('Admin/Index/clearCache');?>"><i class="fa fa-trash"></i> 清除缓存 </a></li> -->
          <li class="hidden-xs"> <a href="javascript:;" onclick="reset_pwd('修改密码','<?php echo U('System/editPassword');?>',360,320)"><i
                  class="fa fa-eye"></i>修改密码
          </a> </li>
          <li class="dropdown hidden-xs">
            <a  href="<?php echo U("Login/loginout");?>" class="right-sidebar-toggle" aria-expanded="false"> <i
                  class="fa fa-logout"></i> 退出 </a> </li>
        </ul>
      </nav>
    </div>
    <div class="row content-tabs">
      <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i> </button>
      <nav class="page-tabs J_menuTabs">
        <div class="page-tabs-content">
          <a href="javascript:;" class="active J_menuTab" data-id="<?php echo U('Admin/Index/main');?>">Dashboard</a> </div>
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
      <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<?php echo U('Admin/Index/main');?>"
              frameborder="0" data-id="<?php echo U('Admin/Index/main');?>" seamless></iframe>
    </div>
    <div class="footer">
      <div class="pull-right">&copy; 2017 <?php echo C('SOFT_NAME');?> (版本:<?php echo C('SOFT_VERSION');?>) <?php echo L("ADMIN_COPYRIGHT");?></div>
    </div>
  </div>
  <!--右侧部分结束-->
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
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/hplus.js"></script>
<script type="text/javascript" src="<?php echo ($siteurl); ?>Public/Front/js/contabs.js"></script>
<script src="<?php echo ($siteurl); ?>Public/Front/js/iNotify.js"></script>
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
    var iNotify = new iNotify({
        message: '有消息了。',//标题
        effect: 'flash', // flash | scroll 闪烁还是滚动
        interval: 300,
        audio:{
            //file: ['/Public/sound/msg.mp4','/Public/sound/msg.mp3','/Public/sound/msg.wav']
            file:'http://tts.baidu.com/text2audio?lan=zh&ie=UTF-8&spd=5&text=有客户申请提现啦'
        }
    });
    <?php if(($withdraw) == "1"): ?>setInterval(function() {
            $.ajax({
                type: "GET",
                url: "<?php echo U('Withdrawal/checkNotice');?>",
                cache: false,
                success: function (res) {
                    if (res.num>0) {
                        iNotify.setFavicon(res.num).setTitle('提现通知').notify({
                            title: "新通知",
                            body: "有客户，提现啦..."
                        }).player();
                    }
                }
            });
        },10000);<?php endif; ?>

</script>
<?php echo tongji(0);?>
</body>
</html>