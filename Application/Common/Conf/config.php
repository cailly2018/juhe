<?php
return array(
    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'b5MV=IsXUKxc2@u]"l(hdpg$?o0E1NGr4;BP76Wf', //默认数据加密KEY
    'COOKIE_EXPIRE' => 3600,
    'COOKIE_SECURE' => false,
    'LOAD_EXT_CONFIG' => 'website,db,tags,route,disable,version,paytype,merchants,planning,additional',
    'DEFAULT_MODULE' => 'Home',
    /* 全局过滤配置 */
    'DEFAULT_FILTER' =>  'strip_tags,htmlspecialchars',
    'MODULE_DENY_LIST'=>  array('Common','Runtime'),
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'URL_PATHINFO_DEPR' => '_', //PATHINFO URL分割符

    //默认错误跳转对应的模板文件
    //'TMPL_ACTION_ERROR' => THINK_PATH . 'Tpl/dispatch_jump.tpl',
    //默认成功跳转对应的模板文件
    //'TMPL_ACTION_SUCCESS' => THINK_PATH . 'Tpl/dispatch_jump.tpl',

    'TMPL_TEMPLATE_SUFFIX' => '.html',
    'URL_HTML_SUFFIX' => 'html',
    'TOKEN_ON'      =>    true,
    'TMPL_L_DELIM' => '<{',
    'TMPL_R_DELIM' => '}>',
    'SHOW_PAGE_TRACE'=>false,
    'INVITECODE' => 6,//验证码的长度
    'user'=>'user',//普通用户url
    'agent'=>'agent',//代理url
    'imageDriver'=>'gd',//二维码画图 Supported: "gd", "imagick"

    'DATA_CACHE_PREFIX' => 'tp',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//缓存类型
    'REDIS_HOST'=>'47.244.11.62',
    'REDIS_RW_SEPARATE' => false, //是否开启Redis读写分离
    'REDIS_PORT'=>'6379',//端口号
    'REDIS_TIMEOUT'=>'300',//超时时间
    'REDIS_PERSISTENT'=>false,//是否长连接
    'REDIS_AUTH'=>'cailly512',//AUTH认证密码
    'DATA_CACHE_TIME'=> 10800, // 数据缓存有效期 0表示永久缓存
    'SMS_GD'=>array(
        'id'=>1593,
        'name'=>'nmwedzf',
        'psw'=>'nm2019jd3w',
    )

) ;
?>