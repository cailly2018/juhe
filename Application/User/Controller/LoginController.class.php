<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Think\Verify;

/**
 * 用户登录控制器
 * Class LoginController
 * @package Home\Controller
 */
class LoginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 代理商户登录
     */
    public function index()
    {
        $loginUrl = U(__MODULE__ . "/Login/checklogin");

        $module = strtolower(trim(__MODULE__, '/'));
        $type = "商户";
        $bg = "shanghu.png";

        if ($module == C('user')) {

            //普通商户
            $type = "商户";
            $bg = "shanghu.png";
        } else if ($module == C('agent')) {
            //代理商户
            $type = "代理";
            $bg = "daili.png";
        }
        $this->assign('type', $type);
        $this->assign('bg', $bg);
        $this->assign('loginUrl', $loginUrl);
        $this->display();
    }

    public function test()
    {

        $payid =  I('request.payid', 0, 'intval');
        $pay_memberid = "10057";   //商户ID
        if($payid){
          $pay_memberid = $payid;
        }
        $mid =  I('request.mid', 0, 'intval');

        if($mid){
            $pay_memberid = $mid;
        }
        $pay_orderid = 'E' . date("YmdHis") .$this->get_random(4);    //订单号
        //$pay_amount = "0.01";    //交易金额
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间$this->_site .
        $pay_notifyurl = "http://www.yourdomain.com/demo/server.php";   //服务端返回地址
        $pay_callbackurl = "http://www.juhe.com/manage_Index_index.html";  //页面跳转返回地址
       // $pay_notifyurl = $this->_site."Pay_Charges_notify.php";   //服务端返回地址
       // $pay_callbackurl = $this->_site."Pay_Charges_callback.php";  //页面跳转返回地址

        //扫码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_applydate" => $pay_applydate,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
        );

     //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1])->select();

        $loginout = U(__MODULE__ . "/Login/loginout");

        $this->assign('products ', $products );
        $this->assign('loginout', $loginout);
        $this->assign('native', $native);
        $this->display();
    }

    function get_random($len = 3)
    {
        //range 是将10到99列成一个数组
        $numbers = range(10, 99);
        //shuffle 将数组顺序随即打乱
        shuffle($numbers);
        //取值起始位置随机
        $start = mt_rand(1, 10);
        //取从指定定位置开始的若干数
        $result = array_slice($numbers, $start, $len);
        $random = "";
        for ($i = 0; $i < $len; $i++) {
            $random = $random . $result[$i];
        }
        return $random;
    }



    public function  checkmd5str()
    {
        //POST参数
        $native = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'pay_amount'      => I('request.pay_amount', ''),
            'pay_applydate'   => I('request.pay_applydate', ''),
            'pay_bankcode'    => I('request.pay_bankcode', ''),
            'pay_notifyurl'   => I('request.pay_notifyurl', ''),
            'pay_callbackurl' => I('request.pay_callbackurl', ''),
        );
   
        $Md5key = M('Member')->where(['id'=>$native['pay_memberid']-10000])->getField('apikey');
 
    //    $Md5key = "3ep5oez9eznh9x0fkxwb9hfnxf3scd9j";   //密钥

        ksort($native);

        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        echo json_encode($sign);

    }

    /**
     * 登录验证
     */
    public function checklogin()
    {
        if (IS_POST) {
            $module = strtolower(trim(__MODULE__, '/'));
            if ($module == C('user')) {

                //普通商户
                $this->check([4], '普通商户');
            } else if ($module == C('agent')) {
                //代理商户
                $this->check([5, 6, 7], '代理商户');
            }
        }
    }

    /**
     * 检查登录
     * @param  [type] 代理类型 4=>普通商户 5=>代理商户
     * @return [type]
     */
    private function check($type, $typeName = '普通商户')
    {
        $username     = I('post.username', '', 'trim');
        $password     = I('post.password', '', 'trim');
        $varification = I('post.varification', '', 'trim');
        $cookiename   = I('post.cookiename');
        if (!$username || !$password || !$varification) {
            $this->error('用户名、密码、验证码不能为空！');
        }
        //验证码
        $verify = new Verify();
        if (!$verify->check($varification)) {
            $this->error('验证码输入有误！');
        }
        $fans = M('Member')->where(['username' => $username])->find();

        //判断是白名单登录
        $ip = get_client_ip();
        if (trim($fans['login_ip'])) {
            $ipItem = explode("\r\n", $fans['login_ip']);
            if (!in_array($ip, $ipItem)) {
                $this->error('登录IP错误');
            }
        }


        $loginWarningNum = M('Websiteconfig')->getField('login_warning_num');
        if ($fans['login_error_num'] >= $loginWarningNum && $fans['status'] != 1) {
            $this->error('您今天密码输错超过' . $loginWarningNum . '次，已锁定，请联系管理员或24小时后解锁');
        }

        //不存在
        if (!$fans || $fans['status'] != 1) {
            $this->error('商户已被禁用！');
        }

        //判断用户登录最后一次错误时间是否在昨天
        $lastErrorTime = date('Ymd', $fans['last_error_time']);
        $today         = date('Ymd');
        if ($lastErrorTime > $today) {
            //如果是昨天未超过错误登录次数，重置为0
            M('Member')->where(['id' => $fans['id']])->save(['login_error_num' => 0]);
        }

//echo md5($password . $fans['salt']);die;

        //密码验证
        if (md5($password . $fans['salt']) != $fans['password']) {

            //如果用户密码错误，记录错误次数跟时间
            $loginErrorNum = $fans['login_error_num'] + 1;
            $loginData     = [
                'login_error_num' => ['exp', 'login_error_num+1'],
                'last_error_time' => time(),
            ];

            //超过一定次数，修改
            if ($loginErrorNum >= $loginWarningNum && $loginWarningNum != 0) {
                $loginData['status'] = 0;
            }
            M('Member')->where(['id' => $fans['id']])->save($loginData);
            $this->error('密码输入有误！');
        } else {
            $session_random = randpw(32);
            M('Member')->where(['id' => $fans['id']])->save(['login_error_num' => 0, 'session_random' => $session_random]);
        }
        //用户登录
        $user_auth = [
            'uid'      => $fans['id'],
            'username' => $fans['username'],
            'groupid'  => $fans['groupid'],
            'password' => $fans['password'],
            'session_random' => $session_random
        ];
        session('user_auth', $user_auth);
        ksort($user_auth); //排序
        $code = http_build_query($user_auth); //url编码并生成query字符串
        $sign = sha1($code);
        session('user_auth_sign', $sign);

        // 登录记录
        $rows['userid']        = $fans['id'];
        $rows['logindatetime'] = date("Y-m-d H:i:s");
        //旧的获取地区数据
        // $Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
        // $location = $Ip->getlocation(); // 获取某个IP地址所在的位置

        $location             = \Org\Net\NIpLocation::find($ip); //返回式一个数组，索引0 国家 1省份 2城市
        $rows['loginip']      = $ip;
        $rows['loginaddress'] = $location[1] . "-" . $location[2];
        $rows['type'] = 0;
        //常用地址
        $localCountry = [];
        //获取最近登录地址
        $latestLoginData = M("Loginrecord")->where(['userid' => $fans['id'], 'type'=>0])->order('id desc')->limit(3)->select();
        $address         = @array_column((array) $latestLoginData, 'loginaddress', 'id');
        $country         = @array_map(function ($item) {
            $adress = explode('-', $item);
            return $adress[1]; //0为省份 1为城市
        }, $address);
        //获取数组中的重复数据
        $repeatItem = @array_unique($country);
        if ($repeatItem) {
            //获取最近三次登录重复的地址
            $localCountry = array_diff_assoc($country, $repeatItem);
        }
        //如果异地登录就发送通知信息
        $sms_is_open = smsStatus();
        $product     = ['time' => date('Y-m-d H:i:s'), 'address' => $location[1] . $location[2]];
        if ($localCountry && !in_array($location[2], $localCountry) && $fans['mobile'] && $sms_is_open) {
            $ret = $this->sendStr('loginWarning', $fans['mobile'], $product);
        } else if ($localCountry && !in_array($location[2], $localCountry) && $fans['email']) {
            $message = "您的账号于{$product['time']}登录异常，异常登录地址：{$product['address']}，如非本人操纵，请及时修改账号密码。";
            $ret     = sendEmail($fans['email'], C('WEB_TITLE'), $message);
        }

        M("Loginrecord")->add($rows);

        $this->success('登录成功', U(__MODULE__ . '/Index/index'));
    }

    /**
     * 登出
     */
    public function loginout()
    {
        $user_auth = session('user_auth');
        $url       = 'index.html';
        session('user_auth', null);
        session('user_auth_sign', null);
        session('user_google_auth', null);
        $this->success('正在退出...', $url);
    }

    /**
     * 注册
     */
    public function register()
    {
        $this->display();
    }

    public function userInfo()
    {
        $regionlist = M("region")->where(['parent_id'=>1])->select();
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getProvince',array(),'POST');
/*echo '<pre>';
print_r($regionlist['data']);die;*/
        $this->assign("shenglist", $regionlist['data']);

        $this->display();
    }
    function cargetCity(){

         $name   = I('post.areaId');
         $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getCity',array('parent'=>$name),'POST');
         echo json_encode(array('data'=> $regionlist['data']));
    }
    function cargetArea(){

        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getArea',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function getCity(){
        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getCity',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function getArea(){

        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getArea',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function registerCha(){
        $data =   $_POST['b'];
        $data['cardno'] = $data['cardno'];
        $data['cardno'] = openssl_encrypt($data['cardno'], 'des-ede3-cbc', 'f9b08f4246f4981a7964eb74', false, '01234567');
        $data['legelcertno'] = openssl_encrypt($data['legelcertno'], 'des-ede3-cbc', 'f9b08f4246f4981a7964eb74', false, '01234567');
        $data['payname'] = openssl_encrypt($data['payname'], 'des-ede3-cbc', 'f9b08f4246f4981a7964eb74', false, '01234567');
        $buslicpic =  explode('--',$data['buslicpic']);
        $data['buslicpic'] = $buslicpic[0];
        $legfrontpic =  explode('--',$data['legfrontpic']);
        $data['legfrontpic'] = $legfrontpic[0];
        $legbackpic =  explode('--',$data['legbackpic']);
        $data['legbackpic'] = $legbackpic[0];
        $handpic =  explode('--',$data['handpic']);
        $data['handpic'] = $handpic[0];
        $doorpic =  explode('--',$data['doorpic']);
        $data['doorpic'] = $doorpic[0];
        $accopenpic =  explode('--',$data['accopenpic']);
        $data['accopenpic'] = $accopenpic[0];
        $cashierpic =  explode('--',$data['cashierpic']);
        $data['cashierpic'] = $cashierpic[0];

        $r =   $this->request('http://platform.shanglianchuangfu.com/api-v1-user/register',$data,'post');
        if($r['code'] == 0){
           $this->error($r['msg']);
        }else{
/*
            $r['data']['buslicpic'] = $buslicpic[1];
            $r['data']['legfrontpic'] = $legfrontpic[1];
            $r['data']['legbackpic'] = $legbackpic[1];
            $r['data']['handpic'] = $handpic[1];
            $r['data']['doorpic'] = $doorpic[1];
            $r['data']['accopenpic'] = $accopenpic[1];
            $r['data']['cashierpic'] = $cashierpic[1];*/
             M('injian')->save($r['data']);
            $this->success('充值成功');
        }

    }
    public function uploadImg()
    {
        if (IS_POST) {

            $upload           = new \Think\Upload();
            $upload->maxSize  = 5097152;
            $upload->exts     = array('jpg', 'gif', 'png');
            $upload->savePath = '/jinjian/';
            $info             = $upload->uploadOne($_FILES['file']);

            if (!$info) {
                // 上传错误提示错误信息
                $this->error($upload->getError());
            } else {
                $logo = BASE_PATH.'Uploads' . $info['savepath'] . $info['savename'];
                $logoS = $logo;
                $logo = str_replace( '/','\\', $logo);
                $this->ajaxReturn(['status' => 1, 'msg' => "上传成功",'url'=> $this-> upload($logo).'--'.$logoS]);
            }
        }
    }
    function upload($file){

          $ch = curl_init();
          curl_setopt($ch,CURLOPT_URL, "http://platform.shanglianchuangfu.com/api-v1-user/upload.html");
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
          curl_setopt($ch,CURLOPT_POST,true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, [
              'file' => new \CurlFile($file)
          ]);
          $result = curl_exec($ch);
          curl_close($ch);
          $result =  json_decode($result,true);
          $url ='';
          if($result['code'] ==1){
             $url = $result['data']['file'];
          }

          return $url;
      }
    /**
     * @desc 默认post请求
     * @access public
     * @return array
     */
    public function request( $url, $params=array(), $method='post' )
    {

        return $this->httpRequest( $url, $params, $method );
    }

    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array(), $method = 'post', $connectTimeout=5, $readTimeout=10 )
    {
        if( !function_exists('curl_init') )
            return 901 ;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $connectTimeout + $readTimeout );
        curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'API PHP5 Client (curl) ' . phpversion() );

        if( is_array($params) || is_object($params) )
            $query_string = http_build_query($params);
        else
            $query_string = $params;

        $method = strtolower($method);
        switch( $method )
        {
            case 'get':
                if( false === strstr( $url, '?' ) )
                    $url = $url.'?'.$query_string;
                else
                    $url = $url.'&'.$query_string;
                curl_setopt($ch, CURLOPT_URL, $url );
                break;
            case 'post':
                curl_setopt( $ch, CURLOPT_URL, $url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
                break;
            default:
                return  900 ;
                break;
        }
        $starttime = microtime(true);
        $result = curl_exec($ch);
        $endtime = microtime(true);
        $this->exectime = $endtime-$starttime;
        $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_error = curl_error($ch);
        curl_close($ch);
        if( $curl_error )
        {
            return $curl_error;
        }
        if( $httpcode != 200 )
        {
            return $httpcode;
        }
        $result =  json_decode($result,true);
        return $result;
    }

    /**
     * 注册表单
     */
    public function checkRegister()
    {
        if (IS_POST) {

            $pname        = I('post.pname', '', 'trim');
            $licence        = I('post.licence', '', 'trim');
            $corporation        = I('post.corporation', '', 'trim');
            $IDnumber        = I('post.IDnumber', '', 'trim');
            $telnumber        = I('post.telnumber', '', 'trim');
            $CompanyAddress        = I('post.CompanyAddress', '', 'trim');
            if (empty($pname)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '公司名字不能为空']);
            }

            if (empty($licence)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '营业执照号不能为空']);
            }
            if (empty($corporation)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '法人不能为空']);
            }
            if (empty($IDnumber)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '身份证号不能为空']);
            }
            if (empty($telnumber)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '联系方式不能为空']);
            }
            if (empty($CompanyAddress)) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '公司地址不能为空']);
            }

            $username        = I('post.username', '', 'trim');
            $password        = I('post.password', '', 'trim');
            $confirmpassword = I('post.confirmpassword', '', 'trim');
            $email           = I('post.email', '', 'trim');
            $invitecode      = I('post.invitecode', '', 'trim');

            if ($password != $confirmpassword) {
                $this->ajaxReturn(['errono' => 10002, 'msg' => '密码输入不一致!']);
            }

            //邀请码验证
            if ($this->siteconfig['invitecode']) {
                $verifycode = M('Invitecode')
                    ->where(['invitecode' => $invitecode, 'status' => 1, 'yxdatetime' => array('egt', time())])
                    ->find();
                if (!$verifycode) {
                    $this->ajaxReturn(array('errorno' => 10001, 'msg' => '邀请码无效!'));
                }
            }
            $isuserid = M("Member")->where(['username' => $username])->getField("id");
            if ($isuserid) {
                $this->ajaxReturn(array('errorno' => 10005, 'msg' => '用户名重复!'));
            }

            $user = [
                'username'   => $username,
                'password'   => $password,
                'email'      => $email,
                'verifycode' => $verifycode,
                'mobile' => $telnumber,
                'address' => $CompanyAddress,
                'sfznumber' => $IDnumber,
                'pname' => $pname,
                'licence' => $licence,
                'corporation' => $corporation,
                'realname' => $licence,
            ];
            $userdata = generateUser($user, $this->siteconfig);

            $newuid = M('Member')->add($userdata);


            //添加用户组权限
            /**
             * 不需要使用用户权限
             * author: feng
             * create: 2017/10/21 10:47
             */
            //M('AuthGroupAccess')->add(['uid'=>$newuid,'group_id'=>$_verfycode['regtype'] ? $_verfycode['regtype'] :4]);

            //失效邀请码
            $_failinvitecode = array('syusernameid' => $newuid, 'sydatetime' => time(), 'status' => 2);
            M('Invitecode')->where(['invitecode' => $invitecode])->save($_failinvitecode);
            if($this->siteconfig['register_need_activate']) {
                //发送注册激活邮件
                $returnEmail = sendRegemail($username, $email, $userdata['activate'], $this->siteconfig);
                if ($returnEmail) {
                    $tel    = $this->siteconfig["tel"];
                    $qqlist = $this->siteconfig['qq'];
                    $mail   = explode('@', $email)[1];
                    $this->ajaxReturn(array('errorno' => 0, 'need_activate'=>1,'msg' => array('tel' => $tel, 'qq' => $qqlist, 'email' => $email, 'mail' => 'http://mail.' . $mail)));
                } else {
                    $this->ajaxReturn(['errorno' => 10003, 'msg' => $returnEmail]);
                }
            } else {
                $this->ajaxReturn(['errorno' => 0, 'need_activate'=>0,'msg' => '注册成功！']);
            }
        } else {
            $this->ajaxReturn(array('errorno' => 10004, 'msg' => '注册失败'));
        }
    }

    /**
     * 用户名验证
     */
    public function checkuser()
    {
        $username = I("post.username");
        $userid   = M("Member")->where(['username' => $username])->getField("id");
        $valid    = true;
        if ($userid) {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        } else {
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * email 验证
     */
    public function checkemail()
    {
        $email = I("post.email");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('valid' => false));
            die;
        }
        $userid = M("Member")->where(array('email' => $email))->getField("id");
        $valid  = true;
        if ($userid) {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        } else {
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * 邀请码验证
     */
    public function checkinvitecode()
    {
        $invite_code         = I("post.invitecode");
        $Invitecode          = M("Invitecode");
        $where['invitecode'] = $invite_code;
        $where['status']     = 1;
        $where['yxdatetime'] = array('egt', time());
        $id                  = $Invitecode->where($where)->getField("id");
        $valid               = true;
        if ($id) {
            echo json_encode(array('valid' => $valid));
        } else {
            $valid = false;
            echo json_encode(array('valid' => $valid));
        }
    }

    /**
     * 验证码
     */
    public function verifycode()
    {
        $config = array(
            'length'   => 5, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'useImgBg' => false, // 使用背景图片
            'useZh'    => false, // 使用中文验证码
            'useCurve' => false, // 是否画混淆曲线
            'useNoise' => true, // 是否添加杂点
        );
        ob_clean();
        $verify = new Verify($config);
        $verify->entry();
    }

    /**
     * 验证码验证
     */
    public function checkverify()
    {
        $code   = I("request.code", "");
        $verify = new Verify();
        if ($verify->check($code)) {
            exit("ok");
        } else {
            exit("no");
        }
    }

    public function forgetpwd()
    {
        if (IS_POST) {
            $username        = I("post.username");
            $password        = I('post.password');
            $confirmpassword = I('post.confirmpassword');
            $email           = I('post.email');
            $code            = I('post.varification', '', 'trim');
            if (!$username) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '用户名不能为空'));
            }
            if (!$email) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '邮箱不能为空'));
            }
            if (!$code) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '验证码不能为空'));
            }
            if (!$password || !$confirmpassword) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '密码不能为空'));
            }
            if ($password != $confirmpassword) {
                $this->ajaxReturn(['status' => 0, 'msg' => '密码输入不一致!']);
            }
            $codemodel = M("user_code")->where(['username' => $username, 'email' => $email, 'code' => $code, 'status' => 0, 'type' => 0, 'endtime' => array('gt', time())])->order('id desc')->find();
            if (!$codemodel) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '验证码不正确或过期'));
            }
            $member = M("member")->field('id,salt')->where(array("username" => $username, "email" => $email))->find();
            if ($member && M("member")->where("id=" . $member["id"])->setField("password", md5($password . $member['salt'])) !== false) {
                M("user_code")->where(['id' => $codemodel["id"]])->save(array("status" => 1, "uptime" => time()));
                $this->ajaxReturn(['status' => 1, 'msg' => '修改成功!']);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '修改失败!']);
            }

        }
        $this->display();
    }

    /**
     * 发送邮箱验证码
     * author: feng
     * create: 2017/10/19 10:21
     */
    public function sendUserCode()
    {
        $username = I("post.username");
        $email    = I("post.email");
        if (!$username) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '用户名不能为空'));
        }
        if (!$email) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '邮箱不能为空'));
        }
        $member = M("member")->where(array("username" => $username, "email" => $email))->find();
        if (!$member) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '用户或邮箱不正确'));
        }
        $code        = rand(10000, 99999);
        $returnEmail = sendFindpwdemail($username, $email, $code, $this->siteconfig);
        if ($returnEmail) {
            $curTime = time();
            $data    = array("type" => "0",
                "code"                  => $code,
                "username"              => $username,
                "email"                 => $email,
                "status"                => 0,
                "ctime"                 => time(),
                "endtime"               => ($curTime + 600),
            );
            if (M("user_code")->add($data)) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '发送邮件成功'));
            }
        }
        $this->ajaxReturn(array('status' => 0, 'msg' => '发送邮件失败'));

    }

}
