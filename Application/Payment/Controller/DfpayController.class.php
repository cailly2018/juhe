<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class DfpayController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';

        if(  $this->_site == 'http://juhe.nutbe.cn'){
            $this->showmessage('网关错误！');
        }
    }

    function getBalance(){
        $sign = I('request.pay_md5sign');
        if(!$sign) {
            $this->showmessage("缺少签名参数");
        }
        $mchid = I("request.mchid", 0);

        if(!$mchid) {
            $this->showmessage('商户ID不能为空！');
        }
        $mchids['mchid']=$mchid;
        $user_id =  $mchid - 10000;
        //用户信息
        $this->merchants = D('Member')->where(array('id'=>$user_id))->find();


        $signs = $this->_Sign($mchids, $this->merchants['apikey']);

        if($signs !=$sign){
            $this->showmessage("签名错误");
        }
        if(empty($this->merchants)) {
            $this->showmessage('商户不存在！');
        }

      /*  if($this->merchants['df_domain'] != '') {
            $referer = getHttpReferer();
            if(!checkDfDomain($referer, $this->merchants['df_domain'])) {
                $this->showmessage('请求来源域名与报备域名不一致！');
            }
        }
        if($this->merchants['df_ip'] != '' && !checkDfIp($this->merchants['df_ip'])) {
            $this->showmessage('IP地址与报备IP不一致！');
        }*/

        $data['id'] =$this->merchants['id'];
        $data['balance'] =$this->merchants['balance'];

        echo json_encode(array('status'=>'success','data'=>$data,'msg'=>'查询成功'));

    }
    protected function _Sign($data, $key)
    {

        $sign          = '';
        ksort($data);
        foreach ($data as $k => $vo) {
            if(!empty($vo)){
                $sign .= $k.'='.$vo.'&';
            }
        }

        return strtoupper(MD5($sign.'key='.$key));
    }

    function open_charge($user_id){
        $res      = M('Member')->where(['id' => $user_id,'open_charge'=>1])->find();
        if(empty($res)){
            $this->showmessage('此功能未开启');
        }
    }

    /**
     * 创建代付申请
     * @param $parameter
     * @return array
     */
    public function add()
    {

        if (empty($_POST)) {
            $this->showmessage('no data!');
        }
        $siteconfig = M("Websiteconfig")->find();
        if(!$siteconfig['df_api']) {
            $this->showmessage('代付API未开启！');
        }
        $sign = I('request.pay_md5sign');
        if(!$sign) {
            $this->showmessage("缺少签名参数");
        }
        $mchid = I("post.mchid", 0);
        if(!$mchid) {
            $this->showmessage('商户ID不能为空！');
        }

        $user_id =  $mchid - 10000;
        //用户信息
        $this->merchants = D('Member')->where(array('id'=>$user_id))->find();

        if(empty($this->merchants)) {
            $this->showmessage('商户不存在！');
        }
        if(!$this->merchants['df_api']) {
            $this->showmessage('商户未开启此功能！');
        }
        $this->open_charge($user_id);
        if($this->merchants['df_domain'] != '') {
            $referer = getHttpReferer();
            if(!checkDfDomain($referer, $this->merchants['df_domain'])) {
                $this->showmessage('请求来源域名与报备域名不一致！');
            }
        }
        $ip = get_client_ip();
        if($this->merchants['df_ip'] != '' && !checkDfIp($this->merchants['df_ip'])) {
            $this->showmessage('IP地址与报备IP不一致！'.$ip.'--'.$this->merchants['df_ip']);
        }
        $money = I("post.money", 0);
        if($money<=0) {
            $this->showmessage('金额错误');
        }
        if($money< 10) {
            $this->showmessage('总金额不能小于10元');
        }
        if($money > 50000) {
            $this->showmessage('总金额不能大于50003元');
        }

        $bankname = I("post.bankname", '');
        if(!$bankname) {
            $this->showmessage('银行名称不能为空！');
        }
        $subbranch = I("post.subbranch", '');
        if(!$subbranch) {
            $this->showmessage('支行名称不能为空');
        }
        $accountname = I("post.accountname", '');
        if(!$accountname) {
            $this->showmessage('开户名不能为空！');
        }
        $cardnumber = I("post.cardnumber", '');
        if(!$cardnumber) {
            $this->showmessage('银行卡号不能为空！');
        }
        $province = I("post.province");
        if(!$province) {
            $this->showmessage('省份不能为空！');
        }
        $city = I("post.city");
        if(!$city) {
            $this->showmessage('城市不能为空！');
        }
        $out_trade_no = I("post.out_trade_no", '');
        if(!$out_trade_no) {
            $this->showmessage('订单号不能为空！');
        }
        $Order = M("df_api_order");
        $count = $Order->where(['out_trade_no'=>$out_trade_no, 'userid'=>$user_id])->count();
        if($count>0) {
            $this->showmessage('存在重复订单号！');
        }

        $extends = I("post.extends", '');
        $notifyurl = I("post.notifyurl", '');
        //当前可用代付渠道
        $channel_ids = M('pay_for_another')->where(['status' => 1])->getField('id', true);

        if($channel_ids) {
            //获取渠道扩展字段
            $fields = M('pay_channel_extend_fields')->where(['channel_id'=>['in',$channel_ids]])->select();
            if(!empty($fields)) {
                if(!$extends) {
                    $this->showmessage('扩展字段不能为空！');
                }
                $extend_fields_array = json_decode(base64_decode($extends), true);
                foreach($fields as $k => $v) {
                    if(!isset($extend_fields_array[$v['name']]) || $extend_fields_array[$v['name']]=='') {
                        $this->showmessage('扩展字段【'.$v['alias'].'】不能为空！');
                    }
                }
            }
        }
        //验签
        if ($this->verrify($_POST)) {
            M()->startTrans();
            $data['userid']        = $user_id;
            $data['trade_no']      = $this->getOrderId();
            $data['out_trade_no']  = $out_trade_no;
            $data['money']         = $money;
            $data['bankname']      = $bankname;
            $data['subbranch']     = $subbranch;
            $data['accountname']   = $accountname;
            $data['cardnumber']    = $cardnumber;
            $data['province']      = $province;
            $data['city']          = $city;
            $data['ip']            = get_client_ip();
            if($this->merchants['df_auto_check']) {//自动通过审核
                $data['check_status'] = 1;

            } else {
                $data['check_status']  = 0;
            }
            $data['extends']       = base64_decode($extends);
            $data['notifyurl']     = $notifyurl;
            $data['create_time'] = time();
            //添加订单

            $res = $Order->add($data);

            if ($res) {
                if($this->merchants['df_auto_check']) {
                    $result = $this->dfPass($data, $res);
                    if($result['status'] == 0) {
                        M()->rollback();
                        $this->showmessage($result['msg']);
                    } else {
                        M()->commit();
                    }
                } else {
                    M()->commit();
                }

                header('Content-Type:application/json; charset=utf-8');
                $data = array('status' => 'success', 'msg' => '代付申请成功', 'transaction_id'=>$data['trade_no']);
                echo json_encode($data);
                exit;
            } else {
                $this->showmessage('系统错误');
            }
        } else {
            $this->showmessage('签名验证失败', $_POST);
        }
    }

    //代付查询
    public function query()
    {
        $out_trade_no = I('request.out_trade_no');
        $sign = I('request.pay_md5sign');
        if(!$sign) {
            $this->showmessage("缺少签名参数");
        }
        if(!$out_trade_no){
            $this->showmessage("缺少订单号");
        }
        $mchid = I("request.mchid");
        if(!$mchid) {
            $this->showmessage("缺少商户号");
        }
        $user_id = $mchid - 10000;
        //用户信息
        $this->merchants = D('Member')->where(array('id'=>$user_id))->find();
        if(empty($this->merchants)) {
            $this->showmessage('商户不存在！');
        }
        if(!$this->merchants['df_api']) {
            $this->showmessage('商户未开启此功能！');
        }
       if($this->merchants['df_domain'] != '') {
            $referer = getHttpReferer();
            if(!checkDfDomain($referer, $this->merchants['df_domain'])) {
                $this->showmessage('请求来源域名与报备域名不一致！');
            }
        }
        if($this->merchants['df_ip'] != '' && !checkDfIp($this->merchants['df_ip'])) {
            $this->showmessage('IP地址与报备IP不一致！');
        }
        $request = [
            'mchid'=>$mchid,
            'out_trade_no'=>$out_trade_no
        ];

        $signature = $this->createSign($this->merchants['apikey'],$request);
        if($signature != $sign){
            $this->showmessage('验签失败!');
        }
        $order = M('df_api_order')->where(['out_trade_no'=>$out_trade_no,
            'userid'=>$user_id])->find();
        if(!$order){
			$return = [
				'status'=>'error',
				'msg'=>'请求成功',
				'refCode'=>'7',
				'refMsg'=>'交易不存在',
			];
			echo json_encode($return);exit;
        }elseif($order['check_status']==0){
            $refCode = '6';
            $refMsg = "待审核";
        }elseif($order['check_status']==2) {
            $refCode = '5';
            $refMsg = "审核驳回";

        }else{
            if($order['df_id'] > 0) {
                $df_order = M('wttklist')->where(['id'=>$order['df_id'], 'userid'=>$user_id])->find();
                if($df_order['status'] == 0) {
                    $refCode = '4';
                    $refMsg = "待处理";
                } elseif($df_order['status'] == 1) {
                    $refCode = '3';
                    $refMsg = "处理中";
                } elseif($df_order['status'] == 2) {
                    $refCode = '1';
                    $refMsg = "成功";
                } elseif($df_order['status'] == 3) {
                    $refCode = '2';
                    $refMsg = "失败";
                } elseif($df_order['status'] == 4) {
                    $refCode = '2';
                    $refMsg = "失败";
                } else {
                    $refCode = '8';
                    $refMsg = "未知状态";
                }
            }
        }
        $return = [
            'status'=>'success',
            'msg'=>'请求成功',
            'mchid'=>$mchid,
            'out_trade_no'=>$order['out_trade_no'],
            'amount'=>$order['money'],
            'transaction_id'=>$order['trade_no'],
            'refCode'=>$refCode,
            'refMsg'=>$refMsg,
        ];
        if($refCode == 1) {
            $return['success_time'] = $df_order['cldatetime'];
        }
        $return['sign'] = $this->createSign($this->merchants['apikey'],$return);
        echo json_encode($return);
    }

    /**
     * 自动审核提交代付请求到后台
     *
     * @return string
     */
    private function dfPass($data, $df_api_id) {

        $Member = M('Member');
        $info   = $Member->where(['id' => $data['userid']])->lock(true)->find();

        //判断是否设置了节假日不能提现
        $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
        if ($tkHolidayList) {
            $today = date('Ymd');
            foreach ($tkHolidayList as $k => $v) {
                if ($today == date('Ymd', $v)) {
                    return ['status' => 0 ,'msg'=>'节假日暂时无法提款！'];
                }
            }
        }
        //结算方式：
        $Tikuanconfig = M('Tikuanconfig');
        $tkConfig     = $Tikuanconfig->where(['userid' => $data['userid'], 'tkzt' => 1])->find();

        $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

        //判断是否开启提款设置
        if (!$defaultConfig) {
            return ['status' => 0 ,'msg'=>'提款已关闭！'];
        }

        //判断是否设置个人规则
        if (!$tkConfig || $tkConfig['tkzt'] != 1 || $tkConfig['systemxz'] != 1) {
            $tkConfig = $defaultConfig;
        } else {
            //个人规则，但是提现时间规则要按照系统规则
            $tkConfig['allowstart'] = $defaultConfig['allowstart'];
            $tkConfig['allowend']   = $defaultConfig['allowend'];
        }

        //判断是t1还是t0
        $t = $tkConfig['t1zt'] ? 1 : 0;

        //是否在许可的提现时间
        $hour = date('H');
        //判断提现时间是否合法
        if ($tkConfig['allowend'] != 0) {
            if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                return ['status' => 0 ,'msg'=>'不在提现时间，请换个时间再来!'];
            }
        }

        //单笔最小提款金额
        $tkzxmoney = $tkConfig['tkzxmoney'];
        //单笔最大提款金额
        $tkzdmoney = $tkConfig['tkzdmoney'];

        //查询代付表跟提现表的条件
        $map['userid']     = $data['userid'];
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
            $errorTxt = "超出商户当日提款次数！";
        }

        //判断提款额度
        $dayzdmoney = bcadd($wttkSum, $tkSum, 2);
        if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
            $errorTxt = "超出商户当日提款额度！";
        }

        //计算手续费
        $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($data['money'], $tkConfig['sxfrate'], 2), 100, 2);

        $balance = $info['balance']-$sxfmoney;

        if (!isset($errorTxt)) {
            if ($balance < $data['money']) {
                $errorTxt = '金额错误，可用余额不足!';
            }
            if ($data['money'] < $tkzxmoney || $data['money'] > $tkzdmoney) {
                $errorTxt = '提款金额不符合提款额度要求!';
            }
            $dayzdmoney = bcadd($data['money'], $dayzdmoney, 2);
            if ($dayzdmoney >= $tkConfig['dayzdmoney']) {
                $errorTxt = "超出当日提款额度！";
            }


            //实际提现的金额
            $money =$data['money'];// bcsub($data['money'], $sxfmoney, 2);
            //获取订单号
            $orderid = $this->getOrderId();

            //提现时间
            $time = date("Y-m-d H:i:s");

            //提现记录
            $wttkData = [
                'orderid'      => $orderid,
                "bankname"     => $data["bankname"],
                "bankzhiname"  => $data["subbranch"],
                "banknumber"   => $data["cardnumber"],
                "bankfullname" => $data['accountname'],
                "sheng"        => $data["province"],
                "shi"          => $data["city"],
                "userid"       => $data['userid'],
                "sqdatetime"   => $time,
                "status"       => 0,
                "t"            => $t,
                'tkmoney'      => $data['money'],
                'sxfmoney'     => $sxfmoney,
                "money"        => $money,
                "additional"   => '',
                "out_trade_no" => $data['out_trade_no'],
                "df_api_id"    => $df_api_id,
                "extends"      => $data['extends'],
            ];


            $tkmoney = abs(floatval($data['money']));
            $ymoney  = $info['balance'];
            $balance = bcsub($balance, $tkmoney, 2);
            $mcData = [
                "userid"     => $data['userid'],
                'ymoney'     => $ymoney,
                "money"      => $data['money'],
                'gmoney'     => $balance,
                "datetime"   => $time,
                "transid"    => $orderid,
                "orderid"    => $orderid,
                "lx"         => 6,
                'contentstr' => date("Y-m-d H:i:s") . '委托提现操作',
            ];
        }

        if (!isset($errorTxt)) {

            $res1 = $Member->where(['id' => $data['userid']])->save(['balance' => $balance]);
            $res2 = $Wttklist->add($wttkData);
            $res3 = M("df_api_order")->where(['check_status'=>1,'userid'=>$data['userid'],'id'=> $df_api_id])->save(['df_id'=>$res2, 'check_status'=>1,'check_time'=>time()]);
            $res4 = M('Moneychange')->add($mcData);
            /* if( $data['userid']>1){
                   $parenti = $Member->where(array('id'=>$data['userid']))->find();
                   if( $parenti['parentid'] >1){
                       $parentid = $Member->where(array('id'=>$parenti['parentid']))->find();
                   }
                   $bdmoney = $parentid['balance']+$sxfmoney;

                  $mcDatas = [
                       "userid"     => $parentid['id'],
                       'ymoney'     => $parentid['balance'],
                       "money"      => $sxfmoney,
                       'gmoney'     => $bdmoney,
                       "datetime"   => $time,
                       "transid"    => $orderid,
                       "orderid"    => $orderid,
                       "lx"         => 10,
                       'contentstr' => date("Y-m-d H:i:s") . '委托提现操作手续费',
                   ];
                //组织上级收到的手续费流水
               $sx= $Member->where(['id' => $parentid['id']])->save(['balance' => $bdmoney]);
                $sxls = M('Moneychange')->add($mcDatas);
            }*/

            if ($res1 && $res2 && $res3 && $res4 /* &&$sx && $sxls8*/)  {
                return ['status' => 1,'msg'=>'提交成功'];
            }
            return (['status' => 0, 'msg' => '提交失败']);
        } else {
            return ['status' => 0, 'msg' => $errorTxt];
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
     *  验证签名
     * @return bool
     */
    protected function verify($param)
    {
        $md5key        = $this->merchants['apikey'];
        $md5keysignstr = $this->createSign($md5key, $param);
        $pay_md5sign   = I('request.pay_md5sign');

        file_put_contents('2key.txt',$pay_md5sign.'---平台的'.$md5keysignstr."\r\n\r\n",FILE_APPEND);
        if ($pay_md5sign == $md5keysignstr) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * 创建签名
     * @param $Md5key
     * @param $list
     * @return string
     */
    protected function createSign($Md5key, $list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            if (!empty($val) && $key != 'pay_md5sign') {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        file_put_contents('2key.txt',$md5str . "key=" . $Md5key."\r\n\r\n",FILE_APPEND);

        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        return $sign;
    }

    /**
     * 错误返回
     * @param string $msg
     * @param array $fields
     */
    protected function showmessage($msg = '', $fields = array())
    {
        header('Content-Type:application/json; charset=utf-8');
        $data = array('status' => 'error', 'msg' => $msg, 'data' => $fields);
        echo json_encode($data, 320);
        exit;
    }

    function notifyurlSum3(){

        $where['cldatetime'] = array(array('gt',date('Y-m-d 00:00:00',time())),array('lt',date('Y-m-d H:i:s',time())));
        $where['notifyurl_sum']=  array('gt',0);
        $where['status'] = array('in','2,3');
        $lists = M('Wttklist')->where($where)->select();
       // echo M()->getLastSql();die;
        file_put_contents('bf1.txt',"order_id=".date('Y-m-d 00:00:00',time()) ."\r\n\r\n",FILE_APPEND);
        if($lists){
           foreach ($lists as $key=>$list){
              $where = ['out_trade_no' => $list['out_trade_no']];
              $list1 = M('df_api_order')->where($where)->find();
              $arra['orderid'] = $list['out_trade_no'];
              $arra['status'] = $list['status'];
              if($list1['notifyurl']){
                  $url= $list1['notifyurl'] . "?".http_build_query($arra);
                  file_put_contents('bf.txt',"order_id=".$url ."\r\n\r\n",FILE_APPEND);
                  $ch = curl_init();
                  $timeout = 10;
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                  $contents = curl_exec($ch);
                 // $contents ='SUCCESS';
                  $data_y['memo'] = $contents;
                  $data_y['additional'] = $contents;
                  $data_y['notifyurl_sum'] = $list['notifyurl_sum']-1;
                  $map['id'] =$list['id'];
                  M('Wttklist')->where($map)->save($data_y);
                  echo '执行完毕！';
              }
           }
        }else{
            echo '查无数据可执行';
        }
    }

    function clean15(){
        $smp['pay_applydate'] = array('elt',strtotime("-15 day"));
        M('Order')->where($smp)->delete();

        $smp['sqdatetime'] = array('elt',date('Y-m-d H:i:s',strtotime("-15 day")));

        M('wttklist')->where($smp)->delete();

        $smp['create_time'] = array('elt',strtotime("-15 day"));
        M('df_api_order')->where($smp)->delete();

        echo '执行成功';
    }
}