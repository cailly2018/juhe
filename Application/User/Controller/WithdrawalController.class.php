<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Think\Page;
use Think\Upload;

/**
 * 商家结算控制器
 * Class WithdrawalController
 * @package User\Controller
 */

class WithdrawalController extends UserController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 结算记录
     */
/*    public function index()
    {
        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status' => 1, 'pay_product_user.userid' => $this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where   = array();
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq', $tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq', $T);
        }
        $bankfullname = I("request.bankfullname");
        if($bankfullname) {
            $where['bankfullname'] = array('eq', $bankfullname);
        }
        $status = I("request.status");
        if ($status != "") {
            $where['status'] = array('eq', $status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);
            $where['sqdatetime']   = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime) = explode('|', $successtime);
            $where['cldatetime']   = ['between', [$sstime, $setime ? $setime : date('Y-m-d')]];
        }
        //查下级用户

        $whereS['parentid'] =$this->fans['uid'];

        $userid =  M('member')->where($whereS)->getField('id', true);

        array_push($userid,$this->fans['uid']);
        if(empty($userid)){
            $userid = array($this->fans['uid']);
        }

        $where['userid'] = array('in',$userid);//$this->fans['uid'];

        $count           = M('Tklist')->where($where)->count();
        $page            = new Page($count, 15);
        $list            = M('Tklist')
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $map['userid'] = array('in',$userid);
        //统计今日代付信息
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //今日代付总金额

        $beginToday =  date('Y-m-d H:i:s',$beginToday);
        $endToday =  date('Y-m-d H:i:s',$endToday);
        $map['cldatetime'] = array('between', array($beginToday, $endToday));
        $map['status'] = 2;
        $stat['totay_total'] = round(M('Tklist')->where($map)->sum('money'),2);
        //今日提款待结算
        unset($map['cldatetime']);
        $map['sqdatetime'] = array('between', array($beginToday, $endToday));
        $map['status'] = ['in', '0,1'];
        $stat['totay_wait'] = round(M('Tklist')->where($map)->sum('money'),2);
        //今日提款成功笔数
        unset($map['sqdatetime']);
        $map['status'] = 2;
        $stat['totay_success_count'] = M('Tklist')->where($map)->count();
        //今日提款失败笔数
        $map['status'] = 3;
        $stat['totay_fail_count'] = M('Tklist')->where($map)->count();
        $this->assign('stat', $stat);
        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }*/
function testSms(){

    $info = C('SMS_GD');
    $time=date('YmdHis',time());//时间戳，系统当前时间字符串，年月日时分秒
    $md5=md5($info['name'].$info['psw'].$time);//MD5加密，账号+密码+时间戳
    $str1='【创信】祝您生活愉快';//短信内容
    $res = $this->sendNew( $info['id'],$time,$md5,'13724269315',$str1);
    if($res){
        echo '成功';
    }
}

    /**
     * 导出提款记录
     */
    public function exportorder()
    {
        $where = array();

        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq', $tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq', $T);
        }
        $status = I("request.status", 0, 'intval');
        if ($status) {
            $where['status'] = array('eq', $status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);
            $where['sqdatetime']   = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime) = explode('|', $successtime);
            $where['cldatetime']   = ['between', [$sstime, $setime ? $setime : date('Y-m-d')]];
        }
        $where['userid'] = $this->fans['uid'];
        $title           = array('类型', '商户编号', '结算金额', '手续费', '到账金额', '银行名称', '支行名称', '银行卡号', '开户名', '所属省', '所属市', '申请时间', '处理时间', '状态', "备注");
        $data            = M('Tklist')->where($where)->select();

        foreach ($data as $item) {
            switch ($item['status']) {
                case 0:
                    $status = '未处理';
                    break;
                case 1:
                    $status = '处理中';
                    break;
                case 2:
                    $status = '已打款';
                    break;
            }
            switch ($item['t']) {
                case 0:
                    $tstr = 'T + 0';
                    break;
                case 1:
                    $tstr = 'T + 1';
                    break;
            }

            $list[] = array(
                't'            => $tstr,
                'memberid'     => $item['userid'] + 10000,
                'tkmoney'      => $item['tkmoney'],
                'sxfmoney'     => $item['sxfmoney'],
                'money'        => $item['money'],
                'bankname'     => $item['bankname'],
                'bankzhiname'  => $item['bankzhiname'],
                'banknumber'   => $item['banknumber'],
                'bankfullname' => $item['bankfullname'],
                'sheng'        => $item['sheng'],
                'shi'          => $item['shi'],
                'sqdatetime'   => $item['sqdatetime'],
                'cldatetime'   => $item['cldatetime'],
                'status'       => $status,
                "memo"         => $item["memo"],
            );
        }
        exportCsv($list, $title);
    }

    /**
     *  申请结算
     */
    /*public function clearing()
    {
        //查询是否开启短信验证
        $sms_is_open = smsStatus();

        if ($sms_is_open) {
            $this->assign('sendUrl', U('User/Withdrawal/clearingSend'));
        }
        $this->assign('sms_is_open', $sms_is_open);

        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid' => $this->fans['uid']])->find();
        if (!$tkconfig || $tkconfig['tkzt'] != 1) {
            $tkconfig = M('Tikuanconfig')->where(['issystem' => 1])->find();
        }

        //可用余额
        $info = M('Member')->where(['id' => $this->fans['uid']])->find();

        //银行卡
        $bankcards = M('Bankcard')->where(['userid' => $this->fans['uid']])->select();

        $this->assign('tkconfig', $tkconfig);
        $this->assign('bankcards', $bankcards);
        $this->assign('info', $info);
        $this->display();
    }*/

    /**
     * 发送申请结算验证码信息
     */
    public function clearingSend()
    {
        $res = $this->send('clearing', $this->fans['mobile'], '申请结算');
        $this->ajaxReturn(['status' => $res['code']]);
    }

    /**
     * 计算手续费
     */
    public function calculaterate()
    {
        if (IS_POST && I('post.userid') == $this->fans['uid']) {
            $type    = I('post.tktype');
            $feilv   = I('post.feilv');
            $balance = I('post.balance');
            $money   = I('post.money');

            if ($balance < $money) {
                $this->ajaxReturn(['status' => 0, 'msg' => '金额输入错误!']);
            }
            //结算方式：
            $tkconfig = M('Tikuanconfig')->where(['userid' => $this->fans['uid']])->find();

            if (!$tkconfig || $tkconfig['tkzt'] != 1) {
                $tkconfig = M('Tikuanconfig')->where(['issystem' => 1])->find();
            }
            //单笔最小提款金额
            if ($tkconfig['tkzxmoney'] > $money) {
                $this->ajaxReturn(['status' => 0, 'msg' => '单笔最低提款额度：' . $tkconfig['tkzxmoney']]);
            }
            //单笔最大提款金额
            if ($tkconfig['tkzdmoney'] < $money) {
                $this->ajaxReturn(['status' => 0, 'msg' => '单笔最大提款额度：' . $tkconfig['tkzdmoney']]);
            }
            if ($type) {
                $data['amount']    = $money - $feilv;
                $data['brokerage'] = $feilv;
            } else {
                $data['amount']    = $money * (1 - ($feilv / 100));
                $data['brokerage'] = $money * ($feilv / 100);
            }
            $this->ajaxReturn(['status' => 1, 'data' => $data]);
        }
    }

    /**
     * 提现申请
     */
    public function saveClearing()
    {
        if (IS_POST) {

            //验证验证码
            $code        = I('post.code');
            $sms_is_open = smsStatus(); //短信开启状态
            if ($sms_is_open) {
                if (session('send.clearing') == $code && $this->checkSessionTime('clearing', $code)) {
                    session('send',null);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
                }
            }

            //判断是否设置了节假日不能提现
            $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
            if ($tkHolidayList) {
                $today = date('Ymd');
                foreach ($tkHolidayList as $k => $v) {
                    if ($today == date('Ymd', $v)) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '节假日暂时无法提款！']);
                    }

                }
            }

            //用户的ID
            $userid = session('user_auth.uid');
            $u      = I('post.u');

            //个人信息
            $Member = M('Member');
            $info   = $Member->where(['id' => $userid])->lock(true)->find();

            //结算方式：
            $Tikuanconfig = M('Tikuanconfig');
            $tkConfig     = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

            $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

            //判断是否开启提款设置
            if (!$defaultConfig) {
                $this->ajaxReturn(['status' => 0, 'msg' => '提款已关闭！']);
            }

            //判断是否用户是否开启个人规则
            if (!$tkConfig || $tkConfig['tkzt'] != 1) {
                //没有个人规则，默认系统提现规则
                $tkConfig = $defaultConfig;

            } else {
                //个人规则，但是提现时间规则要按照系统规则
                $tkConfig['allowstart'] = $defaultConfig['allowstart'];
                $tkConfig['allowend']   = $defaultConfig['allowend'];
            }

            //判断结算方式
            $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
            //判断是否T+7,T+30
            if($t==7) {//T+7每周一结算
                if(date('w') != 1) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '请在周一申请结算!']);
                }
            } elseif($t==30) { //月结
                if(date('j') != 1) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '请在每月1日申请结算!']);
                }
            }

            //判断判断用户是否有选择银行卡
            !$u['bank'] && $this->ajaxReturn(['status' => 0, 'msg' => '请选择结算银行卡!']);

            //银行卡信息
            $bank = M('Bankcard')->where(['id' => $u['bank'], 'userid'=>$userid])->find();
            if(empty($bank)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '银行卡不存在！']);
            }
            //支付密码
            md5($u['password']) != $info['paypassword'] && $this->ajaxReturn(['status' => 0, 'msg' => '支付密码有误!']);

            //是否在许可的提现时间
            $hour = date('H');

            //判断提现时间是否合法
            if ($tkConfig['allowend'] != 0) {
                if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '不在结算时间内，算时间段为'.$tkConfig['allowstart'].':00 - '.$tkConfig['allowend'].':00']);
                }
            }

            //将金额转为绝对值，防止sql注入
            $tkmoney = abs(floatval($u["money"]));
            if ($tkmoney <= 0) {
                $this->ajaxReturn(['status' => 0, 'msg' => '金额不正确']);
            }

            if ($tkmoney > $info["balance"]) {
                $this->ajaxReturn(['status' => 0, 'msg' => '余额不足！']);
            }

            //单笔最小提款金额
            if ($tkConfig['tkzxmoney'] > $tkmoney) {
                $this->ajaxReturn(['status' => 0, 'msg' => '单笔最低提款额度：' . $tkConfig['tkzxmoney']]);
            }

            //单笔最大提款金额
            if ($tkConfig['tkzdmoney'] < $tkmoney) {
                $this->ajaxReturn(['status' => 0, 'msg' => '单笔最大提款额度：' . $tkConfig['tkzdmoney']]);
            }

            //今日总金额，总次数
            $today = date('Y-m-d');

            //查询代付表跟提现表的条件
            $map['userid']     = $userid;
            $map['sqdatetime'] = ['egt', date('Y-m-d')];

            //查询提现表的数据
            $Tklist = M('Tklist');
            $tkNum  = $Tklist->where($map)->count();
            $tkSum  = $Tklist->where($map)->sum('tkmoney');

            //查询代付表的数据
            $Wttklist = M('Wttklist');
            $wttkNum  = $Wttklist->where($map)->count();
            $wttkSum  = $Wttklist->where($map)->sum('tkmoney');

            //总次数
            $dayzdnum = $tkConfig['dayzdnum'];
            //判断代付表跟提现表的提现次数是否大于规定的次数
            if (bcadd($tkNum, $wttkNum, 2) >= $dayzdnum) {
                $this->ajaxReturn(['status' => 0, 'msg' => "超出当日提款次数！"]);
            }

            //当日最大总金额
            $dayzdmoney = $tkConfig['dayzdmoney'];
            //判断代付表跟提现表的提现金额是否大于规定的金额数
            $todaySum = bcadd($wttkSum, $tkSum, 2);
            if ($todaySum >= $dayzdmoney) {
                $this->ajaxReturn(['status' => 0, 'msg' => "超出当日提款额度！"]);
            }
            if(($todaySum + $tkmoney) > $dayzdmoney) {
                $this->ajaxReturn(['status' => 0, 'msg' => "提现额度不足！您今日剩余提现额度：".($dayzdmoney - $todaySum)."元"]);
            }
            //单人单卡最高提现额度检查
            if($tkConfig['daycardzdmoney'] > 0) {
                $map['banknumber'] = $bank['cardnumber'];
                $tkCardSum  = $Tklist->where($map)->sum('tkmoney');
                $wttkCardSum  = $Wttklist->where($map)->sum('tkmoney');
                $todayCardSum = bcadd($tkCardSum, $wttkCardSum, 2);
                if ($todayCardSum >= $tkConfig['daycardzdmoney']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => "该银行卡今日提现已超额！"]);
                }
                if(($todayCardSum + $tkmoney) > $tkConfig['daycardzdmoney']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => "银行卡提现额度不足！该银行卡今日剩余提现额度：".($tkConfig['daycardzdmoney'] - $todayCardSum)."元"]);
                }
            }

            //开启事物
            M()->startTrans();
            //减用户余额
            $balance = bcsub($info['balance'], $tkmoney, 2);
            $res     = $Member->where(['id' => $userid])->save(['balance' => $balance]);
            if ($res) {

                //获取订单号
                $orderid = $this->getOrderId();

                //提现时间
                $time = date("Y-m-d H:i:s");

                //计算手续费
                $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($tkmoney, $tkConfig['sxfrate']), 100, 2);

                //实际提现的金额
                $money = bcsub($tkmoney, $sxfmoney, 2);

                //提交提现记录
                $data = [
                    'orderid'      => $orderid,
                    'bankname'     => trim($bank['bankname']),
                    'bankzhiname'  => trim($bank['subbranch']),
                    'banknumber'   => trim($bank['cardnumber']),
                    'bankfullname' => trim($bank['accountname']),
                    'sheng'        => trim($bank['province']),
                    'shi'          => trim($bank["city"]),
                    'userid'       => $userid,
                    'sqdatetime'   => $time,
                    'status'       => 0,
                    'tkmoney'      => $tkmoney,
                    'sxfmoney'     => $sxfmoney,
                    'money'        => $money,
                    't'            => $t,
                ];
                $result = $Tklist->add($data);

                if ($result) {
                    //提交流水记录
                    $rows = [
                        'userid'     => $userid,
                        'ymoney'     => $info['balance'],
                        'money'      => $tkmoney,
                        'gmoney'     => $balance,
                        'datetime'   => $time,
                        'transid'    => $orderid,
                        'orderid'    => $orderid,
                        'lx'         => '6',
                        'contentstr' => $time . '提现操作',
                    ];
                    $result = M('Moneychange')->add($rows);
                    if ($result) {
                        M()->commit();
                        $this->ajaxReturn(['status' => $res]);
                    }
                }

            }

            M()->rollback();
            $this->ajaxReturn(['status' => 0]);
        }
    }

    /**
     *  委托结算记录
     */
    public function payment()
    {
        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status' => 1, 'pay_product_user.userid' => $this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where   = array();
        $bankfullname = I("request.bankfullname");
        if($bankfullname) {
            $where['bankfullname'] = array('eq', $bankfullname);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq', $tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq', $T);
        }
        $status = I("request.status");
        if ($status != "") {
            $where['status'] = array('eq', $status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);

            $where['sqdatetime']   = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime) = explode('|', $successtime);
            $where['cldatetime']   = ['between', [$sstime, $setime ? $setime : date('Y-m-d')]];
        }
        //查下级用户
        $whereS['parentid'] =$this->fans['uid'];

        $userid =  M('member')->where($whereS)->getField('id', true);

        array_push($userid,$this->fans['uid']);
        if(empty($userid)){
            $userid = array($this->fans['uid']);
        }

        $where['userid'] = array('in',$userid);//$this->fans['uid'];
        $count           = M('Wttklist')->where($where)->count();
        $page            = new Page($count, 15);
        $list            = M('Wttklist')
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();

        //统计今日代付信息
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $map['userid'] = array('in',$userid);
        //今日代付总金额
        $beginToday =  date('Y-m-d H:i:s',$beginToday);
        $endToday =  date('Y-m-d H:i:s',$endToday);
        $map['cldatetime'] = array('between', array($beginToday, $endToday));
        $map['status'] = 2;
        $stat['totay_total'] = round(M('Wttklist')->where($map)->sum('money'),2);

       // echo M()->getLastSql(); die;
        //今日代付待结算
        unset($map['cldatetime']);
        $map['sqdatetime'] = array('between', array($beginToday, $endToday));
        $map['status'] = ['in', '0,1'];
        $stat['totay_wait'] = round(M('Wttklist')->where($map)->sum('money'),2);
        //今日代付成功笔数
        unset($map['sqdatetime']);
        $map['status'] = 2;
        $stat['totay_success_count'] = M('Wttklist')->where($map)->count();
        //今日代付失败笔数
        $map['status'] = 3;
        $stat['totay_fail_count'] = M('Wttklist')->where($map)->count();
        $this->assign('stat', $stat);
        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }

    //导出委托提款记录
    public function exportweituo()
    {
        $where = array();

        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['payapiid'] = array('eq', $tongdao);
        }
        $T = I("request.T");
        if ($T != "") {
            $where['t'] = array('eq', $T);
        }
        $status = I("request.status", 0, 'intval');
        if ($status) {
            $where['status'] = array('eq', $status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);
            $where['sqdatetime']   = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime) = explode('|', $successtime);
            $where['cldatetime']   = ['between', [$sstime, $setime ? $setime : date('Y-m-d')]];
        }
        $where['userid'] = $this->fans['uid'];
        $title           = array('类型', '商户编号', '结算金额', '手续费', '到账金额', '银行名称', '支行名称', '银行卡号', '开户名', '所属省', '所属市', '申请时间', '处理时间', '状态', "备注");
        $data            = M('Wttklist')->where($where)->select();

        foreach ($data as $item) {
            switch ($item['status']) {
                case 0:
                    $status = '未处理';
                    break;
                case 1:
                    $status = '处理中';
                    break;
                case 2:
                    $status = '已打款';
                    break;
                case 3:
                    $status = "已驳回";
                    break;
            }
            switch ($item['t']) {
                case 0:
                    $tstr = 'T + 0';
                    break;
                case 1:
                    $tstr = 'T + 1';
                    break;
            }

            $list[] = array(
                't'            => $tstr,
                'memberid'     => $item['userid'] + 10000,
                'tkmoney'      => $item['tkmoney'],
                'sxfmoney'     => $item['sxfmoney'],
                'money'        => $item['money'],
                'bankname'     => $item['bankname'],
                'bankzhiname'  => $item['bankzhiname'],
                'banknumber'   => $item['banknumber'],
                'bankfullname' => $item['bankfullname'],
                'sheng'        => $item['sheng'],
                'shi'          => $item['shi'],
                'sqdatetime'   => $item['sqdatetime'],
                'cldatetime'   => $item['cldatetime'],
                'status'       => $status,
                "memo"         => $item["memo"],
            );
        }
        exportCsv($list, $title);
    }

    /**
     *  委托结算
     */
    public function entrusted()
    {
        //查询是否开启短信验证
        $sms_is_open = smsStatus();
        if ($sms_is_open) {
            $this->assign('sendUrl', U('User/Withdrawal/entrustedSend'));
        }
        $this->assign('sms_is_open', $sms_is_open);
        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid' => $this->fans['uid']])->find();
        if (!$tkconfig || $tkconfig['tkzt'] != 1) {
            $tkconfig = M('Tikuanconfig')->where(['issystem' => 1])->find();
        }
        //可用余额
        $info = M('Member')->where(['id' => $this->fans['uid']])->find();

        $this->assign('tkconfig', $tkconfig);
        $this->assign('info', $info);
        $this->display();
    }
    /**
     * 发送委托结算验证码信息
     */
    public function entrustedSendas()
    {

        $info = C('SMS_GD');
        $time=date('YmdHis',time());//时间戳，系统当前时间字符串，年月日时分秒
        $md5=md5($info['name'].$info['psw'].$time);//MD5加密，账号+密码+时间戳
        $str1='【糯米】你正在进行提现操作，请保存好验证码:';//短信内容
        $res = $this->sendNew( 'entrusted',$info['id'],$time,$md5,$this->fans['mobile'],$str1);
        $this->ajaxReturn(['status' => $res['code']]);

       /* $res = $this->send('entrusted', $this->fans['mobile'], '委托结算',5);
        $this->ajaxReturn(['status' => $res['code']]);*/
    }

    /**
     *  保存委托申请
     */
    public function saveEntrusted()
    {
        if (IS_POST) {
            $userid   = session('user_auth.uid');
            $password = I('post.password', '');
            $u        = I('post.u');

            //验证验证码
            $code        = I('post.code');
            $sms_is_open = smsStatus(); //短信开启状态
            if ($sms_is_open) {
                if (session('send.entrusted') == $code && $this->checkSessionTime('entrusted', $code)) {
                    session('send', null);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
                }
            }

            if (!$password) {
                $this->error('支付密码有误！');
            }
            //上传文件
            $upload           = new Upload();
            $upload->maxSize  = 3145728;
            $upload->exts     = array('xls', 'xlsx');
            $upload->savePath = '/wtjsupload/' . $this->fans['memberid'] . "/" . date("Ymd", time()) . "/";
            $upload->saveName = array();
            if (empty($_FILES["file"])) {
                $this->error('请上传文件！');
            }

            if (file_exists($upload->savePath . $_FILES["file"]['name'])) {
                $this->error('不能上传同名文件！');
            }

            $info = $upload->uploadOne($_FILES["file"]);
            if (!$info) // 上传错误提示错误信息
            {
                $this->error($upload->getError());
            }

            $file  = './Uploads/wtjsupload/' . $this->fans['memberid'] . "/" . date("Ymd", time()) . "/" . $info['savename'];
            $excel = $this->importExcel($file);

            if (!$excel) {
                $this->error("没有数据！");
            }

            //查询用户数据
            $Member = M('Member');
            $info   = $Member->where(['id' => $userid])->lock(true)->find();

            //支付密码
            if (md5($password) != $info['paypassword']) {
                $this->error('支付密码有误!');
            }

            //判断是否设置了节假日不能提现
            $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
            if ($tkHolidayList) {
                $today = date('Ymd');
                foreach ($tkHolidayList as $k => $v) {
                    if ($today == date('Ymd', $v)) {
                        $this->error('节假日暂时无法提款！');
                    }

                }
            }
            //结算方式：
            $Tikuanconfig = M('Tikuanconfig');
            $tkConfig     = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

            $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

            //判断是否开启提款设置
            if (!$defaultConfig) {
                $this->error('提款已关闭！');
            }

            //判断是否设置个人规则
            if (!$tkConfig || $tkConfig['tkzt'] != 1 || $tkConfig['systemxz'] != 1) {
                $tkConfig = $defaultConfig;
            } else {
                //个人规则，但是提现时间规则要按照系统规则
                $tkConfig['allowstart'] = $defaultConfig['allowstart'];
                $tkConfig['allowend']   = $defaultConfig['allowend'];
            }

            //判断结算方式
            $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
            //判断是否T+7,T+30
            if($t==7) {//T+7每周一结算
                if(date('w') != 1) {
                    $this->error('请在周一申请结算!');
                }
            } elseif($t==30) { //月结
                if(date('j') != 1) {
                    $this->error('请在每月1日申请结算!');
                }
            }
            //是否在许可的提现时间
            $hour = date('H');
            //判断提现时间是否合法
            if ($tkConfig['allowend'] != 0) {
                if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                    $this->error('不在结算时间内，算时间段为'.$tkConfig['allowstart'].':00 - '.$tkConfig['allowend'].':00');
                }
            }

            //单笔最小提款金额
            $tkzxmoney = $tkConfig['tkzxmoney'];
            //单笔最大提款金额
            $tkzdmoney = $tkConfig['tkzdmoney'];

            //查询代付表跟提现表的条件
            $map['userid']     = $userid;
            $map['sqdatetime'] = ['between', [date('Y-m-d').' 00:00:00', date('Y-m-d').' 23:59:59']];

            //统计提现表的数据
            $Tklist = M('Tklist');
            $tkNum  = $Tklist->where($map)->count();
            $tkSum  = $Tklist->where($map)->sum('tkmoney');

            //统计代付表的数据
            $Wttklist = M('Wttklist');
            $wttkNum  = $Wttklist->where($map)->count();
            $wttkSum  = $Wttklist->where($map)->sum('tkmoney');

            //判断是否超过当天次数
            $count    = count($excel);
            $dayzdnum = $tkNum + $wttkNum + $count;
            if ($dayzdnum >= $tkConfig['dayzdnum']) {
                $errorTxt = "超出当日提款次数！";
            }

            //判断提款额度
            $dayzdmoney = bcadd($wttkSum, $tkSum, 2);
            if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                $errorTxt = "超出当日提款额度！";
            }

            $balance = $info['balance'];
            $tkmoneysum = 0;
            $cardsum = [];
            foreach ($excel as $k => $v) {
                if (!isset($errorTxt)) {
                    $tkmoneysum += $v['tkmoney'];
                    //个人信息
                    if ($balance < $v['tkmoney']) {
                        $errorTxt = '金额错误，可用余额不足!';
                        break;
                    }

                    if ($v['tkmoney'] < $tkzxmoney || $v['tkmoney'] > $tkzdmoney) {
                        $errorTxt = '提款金额不符合提款额度要求!';
                        break;
                    }

                    $dayzdmoney = bcadd($v['tkmoney'], $dayzdmoney, 2);
                    if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                        $errorTxt = "超出当日提款额度！";
                        break;
                    }
                    if (bcadd($tkmoneysum, $dayzdmoney, 2) >= $tkConfig['dayzdmoney']) {
                        $errorTxt = "提现额度不足！您今日剩余提现额度：".($tkConfig['dayzdmoney'] - $dayzdmoney)."元";
                        break;
                    }
                    if(!isset($cardsum[$v['banknumber']])) {
                        $cardsum[$v['banknumber']] = 0;//本次银行卡提现总额
                    }
                    $cardsum[$v['banknumber']] += $v['tkmoney'];
                    //单人单卡最高提现额度检查
                    if($tkConfig['daycardzdmoney'] > 0) {
                        $map['banknumber'] = $v["banknumber"];
                        $tkCardSum  = $Tklist->where($map)->sum('tkmoney');
                        $wttkCardSum  = $Wttklist->where($map)->sum('tkmoney');
                        $todayCardSum = bcadd($tkCardSum, $wttkCardSum, 2);
                        if ($todayCardSum >= $tkConfig['daycardzdmoney']) {
                            $errorTxt = "该银行卡今日提现已超额！";
                            break;
                        }
                        if(($todayCardSum + $cardsum[$v['banknumber']]) > $tkConfig['daycardzdmoney']) {
                            $errorTxt = "尾号".substr($v["banknumber"], -4)."的银行卡提现额度不足！该银行卡今日剩余提现额度：".($tkConfig['daycardzdmoney'] - $todayCardSum)."元";
                            break;
                        }
                    }
                    //计算手续费
                    $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($v['tkmoney'], $tkConfig['sxfrate'], 2), 100, 2);

                    //实际提现的金额
                    $money = bcsub($v['tkmoney'], $sxfmoney, 2);

                    //获取订单号
                    $orderid = $this->getOrderId();

                    //提现时间
                    $time = date("Y-m-d H:i:s");

                    //提现记录
                    $wttkData[] = [
                        'orderid'      => $orderid,
                        "bankname"     => trim($v["bankname"]),
                        "bankzhiname"  => trim($v["bankzhiname"]),
                        "banknumber"   => trim($v["banknumber"]),
                        "bankfullname" => trim($v['bankfullname']),
                        "sheng"        => trim($v["sheng"]),
                        "shi"          => trim($v["shi"]),
                        "userid"       => $userid,
                        "sqdatetime"   => $time,
                        "status"       => 0,
                        "t"            => $t,
                        'tkmoney'      => $v['tkmoney'],
                        'sxfmoney'     => $sxfmoney,
                        "money"        => $money,
                        "additional"   => trim($v['additional']),

                    ];

                    $tkmoney = abs(floatval($v['tkmoney']));
                    $ymoney  = $balance;
                    $balance = bcsub($balance, $tkmoney, 2);

                    $mcData[] = [
                        "userid"     => $userid,
                        'ymoney'     => $ymoney,
                        "money"      => $v['tkmoney'],
                        'gmoney'     => $balance,
                        "datetime"   => $time,
                        "transid"    => $orderid,
                        "orderid"    => $orderid,
                        "lx"         => 6,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作',
                    ];
                } else {
                        $this->error($errorTxt);
                }
            }

            if (!isset($errorTxt)) {
                M()->startTrans();
                $res = $Member->where(['id' => $userid])->save(['balance' => $balance]);
                if ($res) {
                    $result = $Wttklist->addAll($wttkData);
                    if ($result) {
                        M('Moneychange')->addAll($mcData);
                        M()->commit();
                        $this->success('委托结算提交成功！');
                        exit;
                    }
                }
                M()->rollback();
                $this->success('委托结算提交成功！');
            } else {
                $this->error($errorTxt);
            }

        }
    }

    /**
     * 导入EXCEL
     */
    public function importExcel($file)
    {
        header("Content-type: text/html; charset=utf-8");
        vendor("PHPExcel.PHPExcel");
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel   = $objReader->load($file, $encode = 'utf-8');
        $sheet         = $objPHPExcel->getSheet(0);
        $highestRow    = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        for ($i = 2; $i <= $highestRow; $i++) {
            $data[$i]['bankname']     = $objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();
            $data[$i]['bankzhiname']  = $objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue();
            $data[$i]['bankfullname'] = $objPHPExcel->getActiveSheet()->getCell("C" . $i)->getValue();
            $data[$i]['banknumber']   = $objPHPExcel->getActiveSheet()->getCell("D" . $i)->getValue();
            $data[$i]['sheng']        = $objPHPExcel->getActiveSheet()->getCell("E" . $i)->getValue();
            $data[$i]['shi']          = $objPHPExcel->getActiveSheet()->getCell("F" . $i)->getValue();
            $data[$i]['tkmoney']      = $objPHPExcel->getActiveSheet()->getCell("G" . $i)->getValue();

            /**
             *User:chen
             */
            //获取模板的额外参数
            $additional = [];
            $k          = 7;
            $num        = ord($highestColumn) - 65;

            while ($k <= $num) {

                $letter = chr(65 + $k);

                $res = $objPHPExcel->getActiveSheet()->getCell($letter . $i)->getValue();
                if ($res) {
                    $additional[] = $res;
                } else {
                    break;
                }

                $k++;
            }

            $data[$i]['additional'] = json_encode($additional, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    public function excel($filePath, $a, $t, $paypaiid, $sxf, $sxflx)
    {

        vendor("PHPExcel.PHPExcel");

        //$filePath = "Book1.xls";
        //建立reader对象
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }

        //建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
        $PHPExcel = $PHPReader->load($filePath);

        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();

        $summoney = 0; //总金额

        switch ($a) {
            case 1: //获取总金额
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {
                    for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                        $addr = $colIndex . $rowIndex;
                        $cell = $currentSheet->getCell($addr)->getValue();
                        if ($cell instanceof PHPExcel_RichText) {
                            //富文本转换字符串
                            $cell = $cell->__toString();
                        }
                        if ($colIndex == "G") {
                            $summoney = $summoney + floatval($cell);
                        }
                    }
                }
                return $summoney;
                break;
            case 2:
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {
                    //金额
                    $addr = "G" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $tkmoney  = floatval($cell);
                    $tkmoney  = sprintf("%.2f", $tkmoney);
                    $sxfmoney = 0;

                    if ($sxflx == 1) {
                        $sxfmoney = $sxf;
                    } else {
                        $sxfmoney = $tkmoney * ($sxf / 100);
                    }

                    $sxfmoney                           = sprintf("%.2f", $sxfmoney);
                    ($tkmoney - $sxfmoney) > 0 ? $money = ($tkmoney - $sxfmoney) : $money = 0; //实际到账金额

                    //银行名称
                    $addr = "A" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankname = $cell;

                    //支行名称
                    $addr = "B" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankzhiname = $cell;

                    //开户名
                    $addr = "C" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $bankfullname = $cell;

                    //银行账号
                    $addr = "D" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $banknumber = $cell;

                    //所在省
                    $addr = "E" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $sheng = $cell;

                    //所在市
                    $addr = "F" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $shi = $cell;

                    if (!is_numeric($banknumber)) {
                        $this->error('银行账号格式错误');
                    }
                    $Apimoney      = M("Apimoney");
                    $yuemoney      = $Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->getField("money");
                    $data          = array();
                    $data["money"] = sprintf("%.2f", ($yuemoney - $tkmoney));
                    if ($Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->save($data)) {
                        //写入提款记录
                        $Wttklist             = M("Wttklist");
                        $data                 = array();
                        $data["bankname"]     = $bankname;
                        $data["bankzhiname"]  = $bankzhiname;
                        $data["banknumber"]   = intval($banknumber);
                        $data["bankfullname"] = $bankfullname;
                        $data["sheng"]        = $sheng;
                        $data["shi"]          = $shi;
                        $data["userid"]       = session("userid");
                        $data["sqdatetime"]   = date("Y-m-d H:i:s");
                        $data["status"]       = 0;
                        $data["tkmoney"]      = $tkmoney;
                        $data["sxfmoney"]     = $sxfmoney;
                        $data["t"]            = $t;
                        $data["money"]        = $money;
                        $data["payapiid"]     = $paypaiid;
                        $res                  = $Wttklist->add($data);
                        if ($res) {
                            $ArrayField = array(
                                "userid"   => session("userid"),
                                "ymoney"   => $yuemoney,
                                "money"    => $tkmoney * (-1),
                                "gmoney"   => ($yuemoney - $tkmoney),
                                "datetime" => date("Y-m-d H:i:s"),
                                "tongdao"  => $paypaiid,
                                "transid"  => "",
                                "orderid"  => "",
                                "lx"       => 10,
                            );
                            $Moneychange = M("Moneychange");
                            foreach ($ArrayField as $key => $val) {
                                $data[$key] = $val;
                            }
                            $Moneychange->add($data);
                            // exit("ok");
                        }
                    }
                }
                unlink($filePath);
                $this->success("委托结算提交成功！", U('Tikuan/wttklist'));
                break;
        }

    }

    public function exceldf($filePath, $a, $t, $paypaiid, $sxf, $sxflx)
    {

        vendor("PHPExcel.PHPExcel");

        //$filePath = "Book1.xls";

        //建立reader对象
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }

        //建立excel对象，此时你即可以通过excel对象读取文件，也可以通过它写入文件
        $PHPExcel = $PHPReader->load($filePath);

        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();

        $summoney = 0; //总金额

        switch ($a) {
            case 1: //获取总金额
                /////////////////////////////////////////////////////////
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {
                    for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                        $addr = $colIndex . $rowIndex;
                        $cell = $currentSheet->getCell($addr)->getValue();
                        if ($cell instanceof PHPExcel_RichText) {
                            //富文本转换字符串
                            $cell = $cell->__toString();
                        }
                        if ($colIndex == "G") {
                            $summoney = $summoney + floatval($cell);
                        }

                    }
                }

                return $summoney;
                ////////////////////////////////////////////////////////
                break;
            case 2:
                /////////////////////////////////////////////////////////
                //循环读取每个单元格的内容。注意行从1开始，列从A开始
                $batchContent  = "";
                $keynum        = 0;
                $batchsummoney = 0;
                for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {

                    $addr = "G" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //金额
                        $cell = $cell->__toString();
                    }

                    $tkmoney = floatval($cell);

                    $batchsummoney = $batchsummoney + $tkmoney;

                    $tkmoney = sprintf("%.2f", $tkmoney);

                    if ($sxflx == 1) {
                        $sxfmoney = $sxf;
                    } else {
                        $sxfmoney = $tkmoney * $sxf;
                    }
                    $sxfmoney                         = sprintf("%.2f", $sxfmoney);
                    $tkmoney - $sxfmoney > 0 ? $money = $tkmoney - $sxfmoney : $money = 0; //实际到账金额

                    $addr = "D" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //银行名称
                        $cell = $cell->__toString();
                    }
                    $bankname = $cell;

                    $addr = "E" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //分行名称
                        $cell = $cell->__toString();
                    }
                    $bankfenname = $cell;

                    $addr = "F" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //支行名称
                        $cell = $cell->__toString();
                    }
                    $bankzhiname = $cell;

                    $addr = "C" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //用户名
                        $cell = $cell->__toString();
                    }
                    $bankfullname = $cell;

                    $addr = "B" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //银行卡号
                        $cell = $cell->__toString();
                    }
                    $banknumber = $cell;

                    $addr = "H" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $sheng = $cell;

                    $addr = "I" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $shi = $cell;

                    $addr = "J" . $rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) {
                        //手机号
                        $cell = $cell->__toString();
                    }
                    $shoujihao = $cell;

                    $zhlx = "私";

                    $bizhong      = "CNY";
                    $keynum       = $keynum + 1;
                    $batchContent = $batchContent . "$keynum,$banknumber,$bankfullname,$bankname,$bankfenname,$bankzhiname,$zhlx,$tkmoney,$bizhong,$sheng,$shi,$shoujihao,,,,,,|";

                    $Apimoney      = M("Apimoney");
                    $yuemoney      = $Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->getField("money");
                    $data          = array();
                    $data["money"] = sprintf("%.2f", ($yuemoney - $tkmoney));
                    if ($Apimoney->where("userid=" . session("userid") . " and payapiid=" . $paypaiid)->save($data)) {
                        /**
                         * 写入提款记录
                         */
                        $Dflist               = M("Dflist");
                        $data                 = array();
                        $data["bankname"]     = $bankname;
                        $data["bankfenname"]  = $bankfenname;
                        $data["bankzhiname"]  = $bankzhiname;
                        $data["banknumber"]   = $banknumber;
                        $data["bankfullname"] = $bankfullname;
                        $data["sheng"]        = $sheng;
                        $data["shi"]          = $shi;
                        $data["userid"]       = session("userid");
                        $data["sqdatetime"]   = date("Y-m-d H:i:s");
                        $data["cldatetime"]   = date("Y-m-d H:i:s");
                        $data["status"]       = 2;
                        $data["tkmoney"]      = $tkmoney;
                        $data["sxfmoney"]     = $sxfmoney;
                        $data["t"]            = $t;
                        $data["money"]        = $money;
                        $data["payapiid"]     = $paypaiid;
                        if ($Dflist->add($data)) {
                            $ArrayField = array(
                                "userid"   => session("userid"),
                                "ymoney"   => $yuemoney,
                                "money"    => $tkmoney * (-1),
                                "gmoney"   => ($yuemoney - $tkmoney),
                                "datetime" => date("Y-m-d H:i:s"),
                                "tongdao"  => $paypaiid,
                                "transid"  => "",
                                "orderid"  => "",
                                "lx"       => 11,
                            ) // 代付结算
                            ;
                            $Moneychange = M("Moneychange");
                            foreach ($ArrayField as $key => $val) {
                                $data[$key] = $val;
                            }
                            $Moneychange->add($data);
                            // exit("ok");
                        }
                    }

                }
                /////////////////////////////////////////////////////////////////

                // vendor("RongBao.RSA");
                vendor("Rsa");
                $pubKeyFile = './cer/tomcat.cer';
                $prvKeyFile = './cer/100000000003161.p12';

                // $pubKeyFile ="D:\\wwwroot\\vhosts\\vip.bank-pay.com.cn\\Application\\User\\Controller\\cer\\tomcat.cer";
                // $prvKeyFile = "D:\\wwwroot\\vhosts\\vip.bank-pay.com.cn\\Application\\User\\Controller\\cer\\100000000003161.p12";

                $rsa = new \RSA($pubKeyFile, $prvKeyFile);

                $content = $batchContent;

                // echo($content."<br>");

                $batchContent = '';
                $length       = strlen($content);
                // echo("[".$length."]");
                for ($i = 0; $i < $length; $i += 100) {
                    //  echo(substr($content,$i,100)."<br>");
                    $x = $rsa->encrypt(substr($content, $i, 100));
                    $batchContent .= "$x";
                }

                // exit("$pubKeyFile<br>$prvKeyFile-----".$batchContent);

                $_input_charset = "utf8";
                $batchBizid     = "100000000003161";
                $batchVersion   = "00";
                $batchBiztype   = "00000";
                $batchDate      = date("Ymd");
                //$batchCurrnum = "100000000003161".date("YmdHis").randpw(10,"NUMBER");
                $batchCurrnum = randpw(3, "NUMBER") . date("YmdHis") . randpw(3, "NUMBER");
                $batchCount   = $keynum;
                $batchAmount  = sprintf("%.2f", $batchsummoney);
                $signType     = "MD5";

                $keystr = "652de6dgff5f983cg09df820c960e97acc20165dd76e3c56dcf6d2e80d3e183e";

                $dataArr['batchBizid']     = $batchBizid;
                $dataArr['batchVersion']   = $batchVersion;
                $dataArr['batchBiztype']   = $batchBiztype;
                $dataArr['batchDate']      = $batchDate;
                $dataArr['batchCurrnum']   = $batchCurrnum;
                $dataArr['batchCount']     = $batchCount;
                $dataArr['batchAmount']    = $batchAmount;
                $dataArr['batchContent']   = $batchContent;
                $dataArr['_input_charset'] = $_input_charset;

                $string = '';
                if (is_array($dataArr)) {
                    foreach ($dataArr as $key => $val) {
                        $string .= $key . '=' . $val . '&';
                    }
                }
                $string = trim($string, '&');

                $sign = md5($string . $keystr);

                ////////////////////////////////////////////////////////////////
                unlink($filePath);
                $fkgate = "http://entrust.reapal.com/agentpay/pay";
                /*  $datastr = "_input_charset=$_input_charset&batchBizid=$batchBizid&batchVersion=$batchVersion&batchBiztype=$batchBiztype&batchDate=$batchDate&batchCurrnum=$batchCurrnum&batchCount=$batchCount&batchAmount=$batchAmount&batchContent=$batchContent&signType=$signType&sign=$sign";
                exit($datastr);
                $tjurl = $fkgate."?".$datastr;
                $contents = fopen($tjurl, "r");
                $contents = fread($contents, 100);
                if (strpos($contents, 'succ')) {
                exit("代付成功！");
                } */
                ##################################################################
                // echo "发送地址：",$request_url,"\n";

                $dataArr["signType"] = $signType;
                $dataArr["sign"]     = $sign;

                $context = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($dataArr),
                    ),
                );
                # var_dump($context);
                $streamPostData = stream_context_create($context);

                $httpResult = file_get_contents($fkgate, false, $streamPostData);
                if (strpos($httpResult, 'succ')) {
                    $this->success("代付成功！");
                } else {
                    $this->error($httpResult);
                }
                ##################################################################
                //$this->success("委托结算提交成功！");
                ////////////////////////////////////////////////////////
                break;
        }

    }

    /**
     * 获得订单号
     *
     * @return string
     */
    public function getOrderId()
    {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $i         = intval(date('Y')) - 2010 - 1;

        return $year_code[$i] . date('md') .
            substr(time(), -5) . substr(microtime(), 2, 5) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }

    /**
     *  代付申请
     */
    public function dfapplyshu(){
        //查询是否开启短信验证
        $sms_is_open = smsStatus();
        $sms_is_open =1;
        if($sms_is_open){
            $this->assign('sendUrl',U('User/Withdrawal/entrustedSendas'));
        }
        $this->assign('sms_is_open',$sms_is_open);
        //结算方式：
        $tkconfig = M('Tikuanconfig')->where(['userid'=>$this->fans['uid']])->find();
        if(!$tkconfig || $tkconfig['tkzt']!=1){
            $tkconfig = M('Tikuanconfig')->where(['issystem'=>1])->find();
        }
        //可用余额
        $info = M('Member')->where(['id'=>$this->fans['uid']])->find();
        //银行卡
        $bankcards = M('Bankcard')->where(['userid' => $this->fans['uid']])->select();
        //当前可用代付渠道
        $channel_ids = M('pay_for_another')->where(['status' => 1])->getField('id', true);
        //获取渠道扩展字段
        $extend_fields = [];
        if($channel_ids){
            $fields = M('pay_channel_extend_fields')->where(['channel_id'=>['in',$channel_ids]])->select();
            foreach($fields as $k => $v) {
                if(!isset($extend_fields[$v['name']])) {
                    $extend_fields[$v['name']] = $v['alias'];
                }
            }
        }
        $this->assign('tkconfig', $tkconfig);
        $this->assign('bankcards', $bankcards);
        $this->assign('extend_fields', $extend_fields);
        $this->assign('info',$info);
        $this->display('dfapply');
    }


    public function dfsave() {
        if(IS_POST) {
            $userid   = session('user_auth.uid');
            $password = I('post.password', '');

            if (!$password) {
                $this->error('支付密码不能为空！');
            }

            $data = I('post.item');
            if(empty($data)) {
                $this->error('代付申请数据不能为空！');
            }
            //查询用户数据
            $Member = M('Member');
            $info   = $Member->where(['id' => $userid])->lock(true)->find();
            //支付密码
            if (md5($password) != $info['paypassword']) {
                $this->error('支付密码有误!');
            }
            //验证验证码
            $code        = I('post.code');
            $sms_is_open = smsStatus(); //短信开启状态
            $sms_is_open = 0;
            if ($sms_is_open) {
                if (session('send.entrusted') == $code && $this->checkSessionTime('entrusted', $code)) {
                    session('send', null);
                } else {
                    $this->error('验证码错误');
                }
            }

            //判断是否设置了节假日不能提现
            $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
            if ($tkHolidayList) {
                $today = date('Ymd');
                foreach ($tkHolidayList as $k => $v) {
                    if ($today == date('Ymd', $v)) {
                        $this->error('节假日暂时无法提款！');
                    }

                }
            }
            //结算方式：
            $Tikuanconfig = M('Tikuanconfig');
            $tkConfig     = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

            $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

            //判断是否开启提款设置
            if (!$defaultConfig) {
                $this->error('提款已关闭！');
            }

            //判断是否设置个人规则
            if (!$tkConfig || $tkConfig['tkzt'] != 1 || $tkConfig['systemxz'] != 1) {
                $tkConfig = $defaultConfig;
            } else {
                //个人规则，但是提现时间规则要按照系统规则
                $tkConfig['allowstart'] = $defaultConfig['allowstart'];
                $tkConfig['allowend']   = $defaultConfig['allowend'];
            }

            //判断结算方式
            $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
            //判断是否T+7,T+30
            if($t==7) {//T+7每周一结算
                if(date('w') != 1) {
                    $this->error( '请在周一申请结算!');
                }
            } elseif($t==30) { //月结
                if(date('j') != 1) {
                    $this->error('请在每月1日申请结算!');
                }
            }

            //是否在许可的提现时间
            $hour = date('H');
            //判断提现时间是否合法
            if ($tkConfig['allowend'] != 0) {
                if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                    $this->error('不在结算时间内，算时间段为'.$tkConfig['allowstart'].':00 - '.$tkConfig['allowend'].':00');
                }

            }

            //单笔最小提款金额
            $tkzxmoney = $tkConfig['tkzxmoney'];
            //单笔最大提款金额
            $tkzdmoney = $tkConfig['tkzdmoney'];

            //查询代付表跟提现表的条件
            $map['userid']     = $userid;
            $map['sqdatetime'] = ['between', [date('Y-m-d').' 00:00:00', date('Y-m-d').' 23:59:59']];

            //统计提现表的数据
            $Tklist = M('Tklist');
            $tkNum  = $Tklist->where($map)->count();
            $tkSum  = $Tklist->where($map)->sum('tkmoney');

            //统计代付表的数据
            $Wttklist = M('Wttklist');
            $wttkNum  = $Wttklist->where($map)->count();
            $wttkSum  = $Wttklist->where($map)->sum('tkmoney');

            //判断是否超过当天次数
            $count    = count($data);
            $dayzdnum = $tkNum + $wttkNum + $count;
            if ($dayzdnum >= $tkConfig['dayzdnum']) {
                $errorTxt = "超出当日提款次数！";
            }

            //判断提款额度
            $dayzdmoney = bcadd($wttkSum, $tkSum, 2);
            if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                $errorTxt = "超出当日提款额度！";
            }

            $balance = $info['balance'];
            $tkmoneysum = 0;
            $cardsum = [];
            foreach ($data as $k => $v) {
                if (!isset($errorTxt)) {
                    $tkmoneysum += $v['tkmoney'];
                    $bankCard = M('bankcard')->where(['id'=>$v['bank'], 'userid'=>$userid])->find();
                    if(empty($bankCard)) {
                        $errorTxt = "银行卡不存在！";
                        break;
                    }
                    if(!isset($cardsum[$v['bank']])) {
                        $cardsum[$v['bank']] = 0;//本次银行卡提现总额
                    }
                    $cardsum[$v['bank']] += $v['tkmoney'];
                    //个人信息
                    if ($balance < $v['tkmoney']) {
                        $errorTxt = '金额错误，可用余额不足!';
                        break;
                    }

                    if ($v['tkmoney'] < $tkzxmoney || $v['tkmoney'] > $tkzdmoney) {
                        $errorTxt = '提款金额不符合提款额度要求!';
                        break;
                    }
                    if (bcadd($tkmoneysum, $dayzdmoney, 2) >= $tkConfig['dayzdmoney']) {
                        $errorTxt = "提现额度不足！您今日剩余提现额度：".($tkConfig['dayzdmoney'] - $dayzdmoney)."元";
                        break;
                    }
                    //单人单卡最高提现额度检查
                    if($tkConfig['daycardzdmoney'] > 0) {
                        $map['banknumber'] = $bankCard['cardnumber'];
                        $tkCardSum  = $Tklist->where($map)->sum('tkmoney');
                        $wttkCardSum  = $Wttklist->where($map)->sum('tkmoney');
                        $todayCardSum = bcadd($tkCardSum, $wttkCardSum, 2);
                        if ($todayCardSum >= $tkConfig['daycardzdmoney']) {
                            $errorTxt = "该银行卡今日提现已超额！";
							break;
                        }
                        if(($todayCardSum + $cardsum[$v['bank']]) > $tkConfig['daycardzdmoney']) {
                            $errorTxt = "尾号".substr($bankCard['cardnumber'], -4)."的银行卡提现额度不足！该银行卡今日剩余提现额度：".($tkConfig['daycardzdmoney'] - $todayCardSum)."元";
							break;                       
					   }
                    }
                    //计算手续费
                    $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($v['tkmoney'], $tkConfig['sxfrate'], 2), 100, 2);

                    /*//实际提现的金额
                    $money = bcsub($v['tkmoney'], $sxfmoney, 2);*/

                    //获取订单号
                    $orderid = $this->getOrderId();

                    //提现时间
                    $time = date("Y-m-d H:i:s");

                    //扩展字段
                    $extends = '';
                    if(isset($v['extend'])) {
                        $extends = json_encode($v['extend']);
                    }
                    //提现记录
                    $wttkData[] = [
                        'orderid'      => $orderid,
                        "bankname"     => $bankCard["bankname"],
                        "bankzhiname"  => $bankCard["subbranch"],
                        "banknumber"   => $bankCard["cardnumber"],
                        "bankfullname" => $bankCard['accountname'],
                        "sheng"        => $bankCard["province"],
                        "shi"          => $bankCard["city"],
                        "userid"       => $userid,
                        "sqdatetime"   => $time,
                        "status"       => 0,
                        "t"            => $t,
                        'tkmoney'      => $v['tkmoney'],
                        'sxfmoney'     => $sxfmoney,
                        "money"        => $v['tkmoney'],
                        "additional"   => trim($v['additional']),
                        "extends"      => $extends,
                    ];

                    $tkmoney = abs(floatval($v['tkmoney']));
                    $ymoney  = $balance;
                    $balance = bcsub($balance, $tkmoney, 2);

                    $mcData[] = [
                        "userid"     => $userid,
                        'ymoney'     => $ymoney,
                        "money"      => $v['tkmoney']+$sxfmoney,
                        'gmoney'     => $balance-$sxfmoney,
                        "datetime"   => $time,
                        "transid"    => $orderid,
                        "orderid"    => $orderid,
                        "lx"         => 6,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作',
                    ];
                } else {
					$this->error($errorTxt);
				}
            }

            if (!isset($errorTxt)) {
                M()->startTrans();
                $res = $Member->where(['id' => $userid])->save(['balance' => $balance-$sxfmoney]);
                if ($res) {
                    $result = $Wttklist->addAll($wttkData);
                    if ($result) {
                        M('Moneychange')->addAll($mcData);
                        M()->commit();
                    }
                }
                M()->rollback();
                $this->success('委托结算提交成功！');
            } else {
                $this->error($errorTxt);
            }

        }
    }
    //获取手续费
    public function getsxf(){
        $res = M("Tikuanconfig")->find();
        $money = $_REQUEST['value'];
        if($res){
            if($res['tktype']){
                $money = $money+ $res['sxffixed'];
            }else{
                $money = $money+($money*$res['sxfrate'])/100;
            }
        }

        echo $money;exit;

    }
    public function getallmoney(){
        $data = I('post.item');

        if(empty($data)) {
            echo 0;exit;
        }
        $res = M("Tikuanconfig")->find();
        $allmonfey =0;
        foreach ($data as $key=>$v){

            if($res){
                if($res['tktype']){
                    $allmonfey = $allmonfey+$v['tkmoney']+ $res['sxffixed'];
                }else{
                    $allmonfey = $allmonfey+($v['tkmoney']*$res['sxfrate'])/100;
                }
            }

        }
        echo $allmonfey;die;
    }




    //代付审核
    public function check() {

        $df_api = M("Websiteconfig")->getField('df_api');
        if(!$df_api) {
            $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
        }
        $Member = M('Member');
        $info   = $Member->where(['id' => $this->fans['uid']])->find();
        if(!$info['df_api']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
        }
        $where = array();
        $out_trade_no= I("request.out_trade_no");
        if ($out_trade_no) {
            $where['O.out_trade_no'] = $out_trade_no;
        }
        $accountname= I("request.accountname","");
        if($accountname != ""){
            $where['accountname'] = array('like',"%$accountname%");
        }
        $check_status = I("request.check_status", '');
        if ($check_status != '') {
            $where['check_status'] = array('eq',intval($check_status));
        }
        $status = I("request.status",'');
        if ($status != '') {
            $where['status'] = array('eq',intval($status));
        }
        $create_time = urldecode(I("request.create_time"));
        if ($create_time) {
            list($cstime,$cetime) = explode('|',$create_time);
            $where['create_time'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $check_time = urldecode(I("request.check_time"));
        if ($check_time) {
            list($sstime,$setime) = explode('|',$check_time);
            $where['check_time'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $where['O.userid'] = $this->fans['uid'];
        $count = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($where)->count();
        $size  = 15;
        $rows  = I('get.rows', $size);
        if (!$rows) {
            $rows = $size;
        }
        $page = new Page($count, $rows);
        $list = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($where)
            ->field('O.*,W.status')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();

        //统计今日下游商户代付信息
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //今日代付总金额
        $map['O.create_time'] = array('between', array($beginToday, $endToday));
        $stat['totay_total'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->sum('O.money');
        //今日代付待审核总金额
        $map['O.check_status'] = 0;
        $stat['totay_wait'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->sum('O.money');
        //今日代付待审核笔数
        $map['O.check_status'] = 0;
        $stat['totay_wait_count'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->sum('O.money');
        unset($map['W.check_status']);
        //今日代付待平台审核总金额
        $map['W.status'] = ['in','0,1'];
        $stat['totay_platform_wait'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->sum('O.money');
        //今日代付待平台审核总笔数
        $map['W.status'] = ['in','0,1'];
        $stat['totay_success_count'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->count();
        //今日代付成功总金额
        unset($map['O.check_status']);
        $map['W.status'] = 2;
        $stat['totay_success_sum'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->sum('O.money');
        //今日代付成功总笔数
        $map['W.status'] = 2;
        $stat['totay_success_count'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->count();
        //今日代付失败笔数
        $map['W.status'] = ['in', '3,4'];
        $stat['totay_fail_count'] = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
            ->where($map)
            ->count();
        foreach($stat as $k => $v) {
            $stat[$k] += 0;
        }


        $this->assign('stat', $stat);
        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }

    //查看代付详情
    public function showDf() {
        $df_api = M("Websiteconfig")->getField('df_api');
        if(!$df_api) {
            $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
        }
        $Member = M('Member');
        $info   = $Member->where(['id' => $this->fans['uid']])->find();
        if(!$info['df_api']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
        }
        $id = I("get.id",0,'intval');
        if($id){
            $order = M('df_api_order')
                ->alias('as O')
                ->join('LEFT JOIN `'.C('DB_PREFIX').'wttklist` AS W ON W.df_api_id = O.id')
                ->where(['O.id'=>$id, 'O.userid'=>$this->fans['uid']])
                ->field('O.*,W.status')
                ->find();
        }
        $this->assign('order',$order);
        $this->display();
    }

    //审核通过代付
    public function dfPass() {
        $df_api = M("Websiteconfig")->getField('df_api');
        if(!$df_api) {
            $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
        }
        $Member = M('Member');
        $info   = $Member->where(['id' => $this->fans['uid']])->find();
        if(!$info['df_api']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
        }
        $id = I("request.id", 0, 'intval');
        if (IS_POST) {
            if (!$id) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
            }
            $userid   = session('user_auth.uid');
            $password = I('post.password', '');
            //查询用户数据
            $Member = M('Member');
            $info   = $Member->where(['id' => $userid])->find();
            if (!$password) {
                $this->ajaxReturn(['status' => 0 ,'msg'=>'请输入支付密码!']);
            }
            if (md5($password) != $info['paypassword']) {
                $this->ajaxReturn(['status' => 0 ,'msg'=>'支付密码有误!']);
            }
            //开启事务
            M()->startTrans();
            $where['id'] = $id;
            $where['userid'] = $this->fans['uid'];
            $withdraw = M("df_api_order")->where($where)->lock(true)->find();
            if(empty($withdraw)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '代付申请不存在']);
            }
            if($withdraw['check_status'] == 1){
                $this->ajaxReturn(['status' => 0,'msg'=>'代付已通过审核，请不要重复提交']);
            } elseif($withdraw['check_status'] == 2) {
                $this->ajaxReturn(['status' => 0 ,'msg'=>'该代付已驳回，不能审核通过']);
            } else {
                //判断是否设置了节假日不能提现
                $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
                if ($tkHolidayList) {
                    $today = date('Ymd');
                    foreach ($tkHolidayList as $k => $v) {
                        if ($today == date('Ymd', $v)) {
                            $this->ajaxReturn(['status' => 0 ,'msg'=>'节假日暂时无法提款！']);
                        }
                    }
                }
                //结算方式：
                $Tikuanconfig = M('Tikuanconfig');
                $tkConfig     = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

                $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

                //判断是否开启提款设置
                if (!$defaultConfig) {
                    $this->ajaxReturn(['status' => 0 ,'msg'=>'提款已关闭！']);
                }

                //判断是否设置个人规则
                if (!$tkConfig || $tkConfig['tkzt'] != 1 || $tkConfig['systemxz'] != 1) {
                    $tkConfig = $defaultConfig;
                } else {
                    //个人规则，但是提现时间规则要按照系统规则
                    $tkConfig['allowstart'] = $defaultConfig['allowstart'];
                    $tkConfig['allowend']   = $defaultConfig['allowend'];
                }

                //判断结算方式
                $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
                //判断是否T+7,T+30
                if($t==7) {//T+7每周一结算
                    if(date('w') != 1) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '请在周一申请结算!']);
                    }
                } elseif($t==30) { //月结
                    if(date('j') != 1) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '请在每月1日申请结算!']);
                    }
                }

                //是否在许可的提现时间
                $hour = date('H');
                //判断提现时间是否合法
                if ($tkConfig['allowend'] != 0) {
                    if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '不在结算时间内，算时间段为'.$tkConfig['allowstart'].':00 - '.$tkConfig['allowend'].':00']);
                    }

                }

                //单笔最小提款金额
                $tkzxmoney = $tkConfig['tkzxmoney'];
                //单笔最大提款金额
                $tkzdmoney = $tkConfig['tkzdmoney'];

                //查询代付表跟提现表的条件
                $map['userid']     = $userid;
                $map['sqdatetime'] = ['between', [date('Y-m-d').' 00:00:00', date('Y-m-d').' 23:59:59']];

                //统计提现表的数据
                $Tklist = M('Tklist');
                $tkNum  = $Tklist->where($map)->count();
                $tkSum  = $Tklist->where($map)->sum('tkmoney');

                //统计代付表的数据
                $Wttklist = M('Wttklist');
                $wttkNum  = $Wttklist->where($map)->count();
                $wttkSum  = $Wttklist->where($map)->sum('tkmoney');

                //判断是否超过当天次数
                $dayzdnum = $tkNum + $wttkNum + 1;
                if ($dayzdnum >= $tkConfig['dayzdnum']) {
                    $errorTxt = "超出当日提款次数！";
                }

                //判断提款额度
                $dayzdmoney = bcadd($wttkSum, $tkSum, 2);
                if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                    $errorTxt = "超出当日提款额度！";
                }
                //计算手续费
                $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($withdraw['money'], $tkConfig['sxfrate'], 2), 100, 2);

                $balance = $info['balance']-$sxfmoney;


                if (!isset($errorTxt)) {
                    if ($balance < $withdraw['money']) {
                        $errorTxt = '金额错误，可用余额不足!';
                    }
                    if ($withdraw['money'] < $tkzxmoney || $withdraw['money'] > $tkzdmoney) {
                        $errorTxt = '提款金额不符合提款额度要求!';
                    }
                    $dayzdmoney = bcadd($withdraw['money'], $dayzdmoney, 2);
                    if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                        $errorTxt = "超出当日提款额度！";
                    }


                    //实际提现的金额
                    $money = $withdraw['money'];//bcsub($withdraw['money'], $sxfmoney, 2);
                    //获取订单号
                    $orderid = $this->getOrderId();

                    //提现时间
                    $time = date("Y-m-d H:i:s");

                    //提现记录
                    $wttkData = [
                        'orderid'      => $orderid,
                        "bankname"     => $withdraw["bankname"],
                        "bankzhiname"  => $withdraw["subbranch"],
                        "banknumber"   => $withdraw["cardnumber"],
                        "bankfullname" => $withdraw['accountname'],
                        "sheng"        => $withdraw["province"],
                        "shi"          => $withdraw["city"],
                        "userid"       => $userid,
                        "sqdatetime"   => $time,
                        "status"       => 0,
                        "t"            => $t,
                        'tkmoney'      => $withdraw['money'],
                        'sxfmoney'     => $sxfmoney,
                        "money"        => $money,
                        "additional"   => '',
                        "out_trade_no" => $withdraw['out_trade_no'],
                        "df_api_id"    => $withdraw['id'],
                        "extends"      => $withdraw['extends'],
                        "notifyurl" => $withdraw['notifyurl'],
                    ];

                    $tkmoney = abs(floatval($withdraw['money']));
                    $ymoney  = $info['balance'];
                    $balance = bcsub($balance, $tkmoney, 2);
                    $mcData = [
                        "userid"     => $userid,
                        'ymoney'     => $ymoney,
                        "money"      => $withdraw['money'],
                        'gmoney'     => $balance,
                        "datetime"   => $time,
                        "transid"    => $orderid,
                        "orderid"    => $orderid,
                        "lx"         => 6,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作',
                    ];
                }
                if (!isset($errorTxt)) {
                    M()->startTrans();
                    $res1 = $Member->where(['id' => $userid])->save(['balance' => $balance]);
                    $res2 = $Wttklist->add($wttkData);
                    $res3 = M("df_api_order")->where(['check_status'=>0,'userid'=>$userid,'id'=> $id])->save(['df_id'=>$res2, 'check_status'=>1,'check_time'=>time()]);
                    $res4 = M('Moneychange')->add($mcData);
                    if ($res1 && $res2 && $res3 && $res4) {
                        M()->commit();
                        $this->ajaxReturn(['status' => 1,'msg'=>'代付审核通过成功！']);
                    }
                    M()->rollback();
                    $this->ajaxReturn(['status' => 0]);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => $errorTxt]);
                }
            }
        } else {
            $info = M('df_api_order')->where(['id' => $id])->find();
            $this->assign('info', $info);
            $this->display();
        }
    }

    //驳回代付
    public function dfReject() {
        $df_api = M("Websiteconfig")->getField('df_api');
        if(!$df_api) {
            $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
        }
        $id = I("request.id", 0, 'intval');
        if (IS_POST) {
            if (!$id) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
            }
            $Member = M('Member');
            $info   = $Member->where(['id' => $this->fans['uid']])->find();
            if(!$info['df_api']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
            }
            $reject_reason = I('post.reject_reason');
            if(!$reject_reason) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请填写驳回理由']);
            }
            //开启事务
            M()->startTrans();
            $withdraw = M("df_api_order")->where(['id' => $id, 'userid' => $this->fans['uid']])->lock(true)->find();
            if(empty($withdraw)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '代付申请不存在']);
            }
            $data           = [];
            $data["status"] = 3;
            if($withdraw['check_status'] == 1 && $withdraw['df_id']>0){
                $df_order = M('wttklist')->where(['id'=>$withdraw['df_id'], 'userid'=>$this->fans['uid']])->lock(true)->find();
                if(!empty($df_order)) {
                    if($df_order['status'] != 0) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '后台已处理代付，不能驳回']);
                    } else {
                        //将金额返回给商户
                        $Member     = M('Member');
                        $memberInfo = $Member->where(['id' => $this->fans['uid']])->lock(true)->find();
                        $res        = $Member->where(['id' => $this->fans['uid']])->save(['balance' => array('exp', "balance+{$df_order['tkmoney']}")]);
                        if (!$res) {
                            M()->rollback();
                            $this->ajaxReturn(['status' => 0]);
                        }
                        //2,记录流水订单号
                        $arrayField = array(
                            "userid"     => $this->fans['uid'],
                            "ymoney"     => $memberInfo['balance'],
                            "money"      => $df_order['tkmoney'],
                            "gmoney"     => $memberInfo['balance'] + $df_order['tkmoney'],
                            "datetime"   => date("Y-m-d H:i:s"),
                            "tongdao"    => 0,
                            "transid"    => $id,
                            "orderid"    => $id,
                            "lx"         => 12,
                            'contentstr' => '商户代付驳回',
                        );
                        $res = M('Moneychange')->add($arrayField);
                        if (!$res) {
                            M()->rollback();
                            $this->ajaxReturn(['status' => 0]);
                        }
                        $res = M("df_api_order")->where(['check_status'=>1,'userid'=>$this->fans['uid'],'id'=> $id])->save(['check_status'=>2,'reject_reason' => $reject_reason, 'check_time'=>time()]);
                        if (!$res) {
                            M()->rollback();
                            $this->ajaxReturn(['status' => 0]);
                        }
                        $data["cldatetime"] = date("Y-m-d H:i:s");
                        $data["memo"] = $reject_reason;
                        //修改代付的数据
                        $res    = M('wttklist')->where(['id'=>$withdraw['df_id'], 'status'=>0])->save($data);
                        if ($res) {
                            M()->commit();
                            $this->ajaxReturn(['status' => $res, 'msg' => '驳回成功']);
                        }
                        M()->rollback();
                        $this->ajaxReturn(['status' => 0]);
                    }
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => '驳回失败']);
                }
            } elseif($withdraw['check_status'] == 2) {
                $this->ajaxReturn(['status' => 0 ,'msg'=>'该代付已驳回，请不要重复提交']);
            } else {
                $res = M("df_api_order")->where(['id'=>$id,'userid' => $this->fans['uid'],'check_status' => 0])->save(['check_status'=>2,'reject_reason' => $reject_reason, 'check_time'=>time()]);
                if ($res) {
                    M()->commit();
                    $this->ajaxReturn(['status' => $res, 'msg' => '驳回成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['status' => 0]);
                }
            }
        } else {
            $info = M('df_api_order')->where(['id' => $id])->find();
            $this->assign('info', $info);
            $this->display();
        }
    }

    //批量通过代付审核
    public function dfPassBatch() {

        if (IS_POST) {
            $df_api = M("Websiteconfig")->getField('df_api');
            if (!$df_api) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
            }
            $userid = $this->fans['uid'];
            $Member = M('Member');
            $info = $Member->where(['id' => $userid])->find();
            if (!$info['df_api']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
            }
            $id = I('post.id', '');
            if (!$id) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请选择代付申请']);
            }
            $id_array = explode('_', $id);
            if (empty($id_array)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
            }
            $password = I('post.password', '');

            if (!$password) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请输入支付密码!']);
            }
            if (md5($password) != $info['paypassword']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '支付密码有误!']);
            }
            //判断是否设置了节假日不能提现
            $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
            if ($tkHolidayList) {
                $today = date('Ymd');
                foreach ($tkHolidayList as $k => $v) {
                    if ($today == date('Ymd', $v)) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '节假日暂时无法提款！']);
                    }
                }
            }
            //结算方式：
            $Tikuanconfig = M('Tikuanconfig');
            $tkConfig = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

            $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

            //判断是否开启提款设置
            if (!$defaultConfig) {
                $this->ajaxReturn(['status' => 0, 'msg' => '提款已关闭！']);
            }

            //判断是否设置个人规则
            if (!$tkConfig || $tkConfig['tkzt'] != 1 || $tkConfig['systemxz'] != 1) {
                $tkConfig = $defaultConfig;
            } else {
                //个人规则，但是提现时间规则要按照系统规则
                $tkConfig['allowstart'] = $defaultConfig['allowstart'];
                $tkConfig['allowend'] = $defaultConfig['allowend'];
            }

            //判断结算方式
            $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
            //判断是否T+7,T+30
            if($t==7) {//T+7每周一结算
                if(date('w') != 1) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '请在周一申请结算!']);
                }
            } elseif($t==30) { //月结
                if(date('j') != 1) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '请在每月1日申请结算!']);
                }
            }

            //是否在许可的提现时间
            $hour = date('H');
            //判断提现时间是否合法
            if ($tkConfig['allowend'] != 0) {
                if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '不在提现时间，请换个时间再来!']);
                }

            }
            //单笔最小提款金额
            $tkzxmoney = $tkConfig['tkzxmoney'];
            //单笔最大提款金额
            $tkzdmoney = $tkConfig['tkzdmoney'];
            $success = $fail = 0;
            //开启事务
            M()->startTrans();
            foreach ($id_array as $v) {
                if(!$v) {
                    continue;
                }
                $where['id'] = $v;
                $where['userid'] = $this->fans['uid'];
                $withdraw = M("df_api_order")->where($where)->lock(true)->find();
                if (empty($withdraw)) {
                    $fail++;
                    continue;
                }
                if ($withdraw['check_status'] == 1) {
                    $fail++;
                    continue;
                } elseif ($withdraw['check_status'] == 2) {
                    $fail++;
                    continue;
                } else {
                    //查询代付表跟提现表的条件
                    $map['userid'] = $userid;
                    $map['sqdatetime'] = ['between', [date('Y-m-d').' 00:00:00', date('Y-m-d').' 23:59:59']];

                    //统计提现表的数据
                    $Tklist = M('Tklist');
                    $tkNum = $Tklist->where($map)->count();
                    $tkSum = $Tklist->where($map)->sum('tkmoney');

                    //统计代付表的数据
                    $Wttklist = M('Wttklist');
                    $wttkNum = $Wttklist->where($map)->count();
                    $wttkSum = $Wttklist->where($map)->sum('tkmoney');

                    //判断是否超过当天次数
                    $dayzdnum = $tkNum + $wttkNum + 1;
                    if ($dayzdnum >= $tkConfig['dayzdnum']) {
                        $fail++;
                        continue;
                    }

                    //判断提款额度
                    $dayzdmoney = bcadd($wttkSum, $tkSum, 2);
                    if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                        $fail++;
                        continue;
                    }
                    $balance = $Member->where(['id' => $userid])->getField('balance');

                    if ($balance <= $withdraw['money']) {
                        $fail++;
                        continue;
                    }
                    if ($withdraw['money'] < $tkzxmoney || $withdraw['money'] > $tkzdmoney) {
                        $fail++;
                        continue;
                    }
                    $dayzdmoney = bcadd($withdraw['money'], $dayzdmoney, 2);
                    if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                        $fail++;
                        continue;
                    }
                    //计算手续费
                    $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($withdraw['money'], $tkConfig['sxfrate'], 2), 100, 2);
                    //实际提现的金额
                    $money = bcsub($withdraw['money'], $sxfmoney, 2);
                    //获取订单号
                    $orderid = $this->getOrderId();

                    //提现时间
                    $time = date("Y-m-d H:i:s");

                    //提现记录
                    $wttkData = [
                        'orderid' => $orderid,
                        "bankname" => $withdraw["bankname"],
                        "bankzhiname" => $withdraw["subbranch"],
                        "banknumber" => $withdraw["cardnumber"],
                        "bankfullname" => $withdraw['accountname'],
                        "sheng" => $withdraw["province"],
                        "shi" => $withdraw["city"],
                        "userid" => $userid,
                        "sqdatetime" => $time,
                        "status" => 0,
                        "t" => $t,
                        'tkmoney' => $withdraw['money'],
                        'sxfmoney' => $sxfmoney,
                        "money" => $money,
                        "additional" => '',
                        "out_trade_no" => $withdraw['out_trade_no'],
                        "df_api_id" => $withdraw['id'],
                        "extends" => $withdraw['extends'],
                    ];

                    $tkmoney = abs(floatval($withdraw['money']));
                    $ymoney = $balance;
                    $balance = bcsub($balance, $tkmoney, 2);
                    $mcData = [
                        "userid" => $userid,
                        'ymoney' => $ymoney,
                        "money" => $withdraw['money'],
                        'gmoney' => $balance,
                        "datetime" => $time,
                        "transid" => $orderid,
                        "orderid" => $orderid,
                        "lx" => 6,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作',
                    ];

                    M()->startTrans();
                    $res1 = $Member->where(['id' => $userid])->save(['balance' => $balance]);
                    $res2 = $Wttklist->add($wttkData);
                    $res3 = M("df_api_order")->where(['check_status'=>0,'userid'=>$userid,'id'=> $v])->save(['df_id' => $res2, 'check_status' => 1, 'check_time' => time()]);
                    $res4 = M('Moneychange')->add($mcData);
                    if ($res1 && $res2 && $res3 && $res4) {
                        M()->commit();
                        $success++;
                    } else {
                        M()->rollback();
                        $fail++;
                    }
                }
            }
            if ($success > 0 && $fail == 0) {
                $this->ajaxReturn(['status' => 1, 'msg' => '审核成功！']);
            } elseif ($success > 0 && $fail > 0) {
                $this->ajaxReturn(['status' => 1, 'msg' => '部分成功，成功:' . $success . '条，失败：' . $fail . '条']);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '审核失败！']);
            }
        } else {
            $id = I('get.id');
            $this->assign('id',$id);
            $this->display();
        }
    }

    //批量驳回代付审核
    public function dfRejectBatch() {

        if (IS_POST) {
            $df_api = M("Websiteconfig")->getField('df_api');
            if (!$df_api) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该功能尚未开启']);
            }
            $userid = $this->fans['uid'];
            $Member = M('Member');
            $info = $Member->where(['id' => $userid])->find();
            if (!$info['df_api']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '商户未开启此功能!']);
            }
            $id = I('post.id', '');
            if (!$id) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请选择代付申请']);
            }
            $id_array = explode('_', $id);
            if (empty($id_array)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
            }
            $password = I('post.password', '');

            if (!$password) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请输入支付密码!']);
            }
            if (md5($password) != $info['paypassword']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '支付密码有误!']);
            }
            $success = $fail = 0;
            foreach ($id_array as $v) {
                try {
                    if (!$v) {
                        $fail++;
                        continue;
                    }
                    M()->startTrans();
                    $withdraw = M("df_api_order")->where(['id' => $v, 'userid' => $this->fans['uid']])->lock(true)->find();
                    if (empty($withdraw)) {
                        $fail++;
                        continue;
                    }
                    $data = [];
                    $data["status"] = 3;
                    if ($withdraw['check_status'] == 1 && $withdraw['df_id'] > 0) {
                        $df_order = M('wttklist')->where(['id' => $withdraw['df_id'], 'userid' => $this->fans['uid']])->lock(true)->find();
                        if (!empty($df_order)) {
                            if ($df_order['status'] != 0) {
                                continue;
                            } else {
                                //将金额返回给商户
                                $Member = M('Member');
                                $memberInfo = $Member->where(['id' => $this->fans['uid']])->lock(true)->find();
                                $res = $Member->where(['id' => $this->fans['uid']])->save(['balance' => array('exp', "balance+{$df_order['tkmoney']}")]);
                                if (!$res) {
                                    M()->rollback();
                                    $fail++;
                                    continue;
                                }
                                //2,记录流水订单号
                                $arrayField = array(
                                    "userid" => $this->fans['uid'],
                                    "ymoney" => $memberInfo['balance'],
                                    "money" => $df_order['tkmoney'],
                                    "gmoney" => $memberInfo['balance'] + $df_order['tkmoney'],
                                    "datetime" => date("Y-m-d H:i:s"),
                                    "tongdao" => 0,
                                    "transid" => $v,
                                    "orderid" => $v,
                                    "lx" => 12,
                                    'contentstr' => '商户代付驳回',
                                );
                                $res = M('Moneychange')->add($arrayField);
                                if (!$res) {
                                    M()->rollback();
                                    $fail++;
                                    continue;
                                }
                                $res = M("df_api_order")->where(['check_status' => 1, 'userid' => $this->fans['uid'], 'id' => $v])->save(['check_status' => 2, 'reject_reason' => '', 'check_time' => time()]);
                                if (!$res) {
                                    M()->rollback();
                                    $fail++;
                                    continue;
                                }
                                $data["cldatetime"] = date("Y-m-d H:i:s");
                                $data["memo"] = '';
                                //修改代付的数据
                                $res = M('wttklist')->where(['id' => $withdraw['df_id'], 'status' => 0])->save($data);
                                if ($res) {
                                    M()->commit();
                                    $success++;
                                    continue;
                                }
                                M()->rollback();
                                $fail++;
                                continue;
                            }
                        } else {
                            $fail++;
                            continue;
                        }
                    } elseif ($withdraw['check_status'] == 2) {
                        $fail++;
                        continue;
                    } else {
                        $res = M("df_api_order")->where(['id' => $v, 'userid'=>$this->fans['uid'], 'check_status'=>0])->save(['check_status' => 2, 'check_time' => time()]);
                        if ($res) {
                            M()->commit();
                            $success++;
                            continue;
                        } else {
                            M()->rollback();
                            $fail++;
                            continue;
                        }
                    }
                } catch (\Exception $e) {
                    M()->rollback();
                    $fail++;
                    continue;
                }
            }
            if ($success > 0 && $fail == 0) {
                $this->ajaxReturn(['status' => 1, 'msg' => '驳回成功！']);
            } elseif ($success > 0 && $fail > 0) {
                $this->ajaxReturn(['status' => 1, 'msg' => '部分驳回成功，成功:' . $success . '条，失败：' . $fail . '条']);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '驳回失败！']);
            }
        } else {
            $id = I('get.id');
            $this->assign('id',$id);
            $this->display();
        }
    }
}
