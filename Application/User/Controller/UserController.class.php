<?php
namespace User\Controller;

class UserController extends BaseController
{
    public $fans;
    public function __construct()
    {
        parent::__construct();
        //验证登录
        $user_auth = session("user_auth");
        ksort($user_auth); //排序
        $code = http_build_query($user_auth); //url编码并生成query字符串
        $sign = sha1($code);
        if($sign != session('user_auth_sign') || !$user_auth['uid']){
            header("Location: ".U(__MODULE__.'/Login/index'));
        }
        //用户信息
        $this->fans = M('Member')->where(['id'=>$user_auth['uid']])->field('`id` as uid, `username`, `password`, `groupid`, `parentid`,`salt`,`balance`, `blockedbalance`, `email`, `realname`, `authorized`, `apidomain`, `apikey`, `status`, `mobile`, `receiver`, `agent_cate`,`df_api`,`login_ip`,`open_charge`,`google_secret_key`,`session_random`,`regdatetime`')->find();
		$this->fans['memberid'] = $user_auth['uid']+10000;
        if(session('user_auth') && $this->fans['google_secret_key'] &&  !session('user_google_auth')) {
            if(!(CONTROLLER_NAME == 'Account' && ACTION_NAME == 'unbindGoogle')
                &&!(CONTROLLER_NAME == 'Index' && ACTION_NAME == 'google')
                &&!(CONTROLLER_NAME == 'Login' && ACTION_NAME == 'verifycode')
                &&!(CONTROLLER_NAME == 'Account' && ACTION_NAME == 'unbindGoogleSend')
            ) {
                if(IS_AJAX){
                    $this->error('请进行谷歌身份验证', 'User/Index/google');
                }else{
                    $this->redirect('User/Index/google');
                }
            }
        }
        if(!session('user_auth.session_random') && $this->fans['session_random'] && session('user_auth.session_random') !=  $this->fans['session_random']) {
            session('user_auth', null);
            session('user_auth_sign', null);
            session('user_google_auth', null);
            $this->error('您的账号在别处登录，如非本人操作，请立即修改登录密码！','index.html');
        }
        $groupId = $this->groupId =  C('GROUP_ID');
        //获取用户的代理等级信息
        foreach($groupId as $k => $v){
            if($k>=$this->fans['groupid'])
                unset($groupId[$k]);
        }

        //今日下级普通用户成功交易总额
        //中级代理商户
        $xdl6 = $this->sqlgroupid($this->fans['uid'],6,0,1);
        $uid = '';
        if ($xdl6){
            //普通代理商户
            $x5 = $this->sqlgroupid(array('in',$xdl6),5,0,1);
            $x41 = '';
            if($x5){
                $x41 = $this->sqlgroupid(array('in',$x5),4,1,0);
            }
            $x42 = $this->sqlgroupid(array('in',$xdl6),4,1,1);
            $uid = $x41.$x42;
        }

        $stat['todaysum']  = 0;
        if($uid){
            $todayBegin = date('Y-m-d').' 00:00:00';
            $todyEnd = date('Y-m-d').' 23:59:59';
            $stat['todaysum'] = M('Order')->where(['pay_memberid'=> array('in',$uid),'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2'],'groupid'=>array('in','4,5')])->sum('pay_amount');

        }
        $todaysum  =  $stat['todaysum']? $stat['todaysum']:0;

        $this->assign('groupId',$groupId);
        $this->assign('todaysumall', $todaysum);
        $this->assign('fans',$this->fans);
    }
    private function dl($xdls,$i=0,$j=0){
        $xdl6s = '';
        foreach ($xdls as  $key=>$v){
            if($i==1){
                $id = $v['id']+10000;

            }else{
                $id =$v['id'];
            }
            $xdl6s .=$id.',';
        }
        if($j==1){
            $xdl6s = rtrim($xdl6s, ",");
        }
        return $xdl6s;
    }
    private function  sqlgroupid($ids,$groupid =6,$i=0,$j=0){

        $xdl6 = M('Member')->where(['parentid'=>$ids,'groupid'=>$groupid])->field('`id`, `parentid`')->select();
        $xdl5 = '';
        if ($xdl6) {
            $xdl5 = $this->dl($xdl6, $i, $j );
        }
        return  $xdl5;
    }
}
?>
