<?php

namespace Pay\Controller;

use Think\Controller;
use Think\Db;

class PayController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;
    //代理提成
    protected $tc;
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->tc = 0;
    }
    function  getNotifyurl(){

        echo 'success';die;
    }
    function testsend(){
        $orderid =   $_GET['orderid'];
        $this->sendN($orderid);
    }
    function sendN($pay_orderid =''){

        $t = date('Y-m-d H:i:s',time()-1000);
        $tend = date('Y-m-d H:i:s',time());

        file_put_contents('sendNotifyurl1.txt',"time=".$t ."\r\n\r\n",FILE_APPEND);

        $order_where = [
            'pay_status'          => 1,
            //'pay_memberid'          => 10154,
            //'pay_orderid'          => '1101071103239131136',

        ];

        $order_where['pay_applydate'] = array(array('gt',$tend),array('lt',strtotime(date('Y-m-d H:i:s',time()))));

        $order_where['is_bu'] = array('elt',3);
        if(!empty($pay_orderid)){
            $order_where['pay_orderid'] = $pay_orderid;
            unset( $order_where['pay_applydate']);
        }
        $m_Order =  M("Order");
        $order_infos = $m_Order->where($order_where)->select();
        //print_r($order_infos);
     //   echo M()->getLastSql();die;
        if(empty($order_infos)){
            exit;
        }
        foreach ($order_infos as $key=>$order_info){
            $re['memberid'] = $order_info['pay_memberid'];
            $re['pay_orderid'] = $order_info['out_trade_id'];
            $re['amount'] = $order_info['pay_amount'];
            $re['transaction_id'] = $order_info['pay_orderid'];
            $re['returncode'] = '00';
            $re["status"]   = 1;

            $order_where = [
                'id'  =>  $order_info['pay_memberid']-10000,
            ];
            $member =M("member")->where($order_where) ->field('id,apikey')->find();

            $re["sign"]   = $this->createSign1($member['apikey'], $re);
            $res =   $this->sendRequest( $order_info['pay_notifyurl'], $re, 'get');

            file_put_contents('sendNotifyurl.txt','---请求次数'.json_encode($re)."res=".json_encode($res) ."\r\n\r\n",FILE_APPEND);

            $order_where = [
                'id'          => $order_info['id'],
                'pay_orderid' => $order_info["pay_orderid"],
            ];
            $sav['is_bu'] = $order_info['is_bu']+1;

            $contents = $res['msg'];
            if (strstr(strtolower($contents), "success") != false || strstr(strtolower($contents), "ok") != false) {
                file_put_contents('sendNotifyurl2.txt',"order_id=".$contents ."\r\n\r\n",FILE_APPEND);
                $sav['pay_status'] = 2;
            }
            $m_Order->where($order_where)->save($sav);
        }
        if(empty($pay_orderid)){
            exit;
        }

    }


    function test1(){

         $this->sendN('S309248452987614');
    }

    function  sendNotifyurl(){

        $this->sendN('');

    }
    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {

        set_time_limit(0);
        $method = strtoupper($method);
        $protocol = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $ch = curl_init();
        $defaults = [];
        if ('GET' == $method) {
            $geturl = $query_string ? $url . (stripos($url, "?") !== FALSE ? "&" : "?") . $query_string : $url;


            file_put_contents('sendNotifyurl2.txt',"huidiao=".$geturl ."\r\n\r\n",FILE_APPEND);


            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            if ($method == 'POST') {
                $defaults[CURLOPT_POST] = 1;
            } else {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        $defaults[CURLOPT_HEADER] = FALSE;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        $defaults[CURLOPT_FOLLOWLOCATION] = TRUE;
        $defaults[CURLOPT_RETURNTRANSFER] = TRUE;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 3;

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if ('https' == $protocol) {
            $defaults[CURLOPT_SSL_VERIFYPEER] = FALSE;
            $defaults[CURLOPT_SSL_VERIFYHOST] = FALSE;
        }

        curl_setopt_array($ch, (array)$options + $defaults);

        $ret = curl_exec($ch);
        $err = curl_error($ch);

        if (FALSE === $ret || !empty($err)) {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            return [
                'ret'   => FALSE,
                'errno' => $errno,
                'msg'   => $err,
                'info'  => $info,
            ];
        }
        curl_close($ch);

        return [
            'ret'  => TRUE,
            'msg'  => $ret,
            'info' => '',
        ];
    }


    /**
     *  处理固码的
     * @return bool
     */
    function clgm($orderid,$pay_amount){
        header('Content-Type:text/html;charset=utf8');
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息

        if($pay_amount==$order_info['pay_amount']){
            return;
        }
        $syschannel = M('channel')->where(['id' => $order_info['channel_id']])->find();
        $product = M('product')->where(['paytype' => $syschannel['paytype']])->find();

        //商户编号id
        $userid = $order_info['pay_memberid'] - 10000;
        //费率
        $_userrate = M('Userrate')
            ->where(["userid" => $userid ,"payapiid" => $product['id']])
            ->find();
        //---------------------------子账号风控start------------------------------------
        $channel_account_list        = M('channel_account')->where(['channel_id' => $syschannel['id'], 'status' => '1'])->select();

        $l_ChannelAccountRiskcontrol = new \Pay\Logic\ChannelAccountRiskcontrolLogic($pay_amount);
        $channel_account_item        = [];
        $error_msg                   = '已下线';
        foreach ($channel_account_list as $k => $v) {
            if ($v['offline_status'] && $v['control_status']) {
                //判断是自定义还是继承渠道的风控
                $temp_info               = $v['is_defined'] ? $v : $syschannel;
                $temp_info['account_id'] = $v['id']; //用于子账号风控类继承渠道风控机制时修改数据的id
                //子账号风控
                $l_ChannelAccountRiskcontrol->setConfigInfo($temp_info);
                $error_msg = $l_ChannelAccountRiskcontrol->monitoringData();
                if ($error_msg === true) {
                    $channel_account_item[] = $v;
                }
            } else if ($v['control_status'] == 0) {
                $channel_account_item[] = $v;
            }
        }
        if (empty($channel_account_item)) {
            $this->showmessage('账户:' . $error_msg);
        }
        // 计算权重
        if (count($channel_account_item) == 1) {
            $channel_account = current($channel_account_item);
        } else {
            $channel_account = getWeight($channel_account_item);
        }

        // 定制费率
        if ($channel_account['custom_rate']) {
            $syschannel['defaultrate'] = $channel_account['defaultrate'];
            $syschannel['fengding']    = $channel_account['fengding'];
            $syschannel['fengding']    = $channel_account['fengding'];
            $syschannel['rate']        = $channel_account['rate'];
        }

        //用户优先通道
        $feilv    = $_userrate['feilv'] ? $_userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
        $fengding = $_userrate['fengding'] ? $_userrate['fengding'] : $syschannel['fengding']; // 封顶手续费
        $fengding = $fengding == 0 ? 9999999 : $fengding; //如果没有设置封顶手续费自动设置为一个足够大的数字
        $pay_sxfamount    = (($pay_amount * $feilv) > ($pay_amount * $fengding)) ? ($pay_amount * $fengding) :
            ($pay_amount * $feilv); // 手续费

        $pay_shijiamount = $pay_amount - $pay_sxfamount; // 实际到账金额
        $cost            = bcmul($syschannel['rate'], $pay_amount, 2); //计算成本
        $data['pay_amount']          = $pay_amount; // 交易金额
        $data['pay_poundage']        = $pay_sxfamount; // 手续费
        $data['pay_actualamount']    = $pay_shijiamount; // 到账金额
        $data['cost']    = $cost;

        $r = $m_Order->where(['id'=>$order_info['id']])->save($data);
        if($r){
            return 1;
        }

        return ;
    }

    /**
     * 创建订单
     * @param $parameter
     * @return array
     */
    public function orderadd($parameter,$i=0)
    {
        $pay_amount = I("post.pay_amount", 0);
        if($pay_amount)

            //通道信息
            $this->channel = $parameter['channel'];
        //用户信息
        $usermodel       = D('Member');
        $this->merchants = $usermodel->get_Userinfo($this->channel['userid']);

        if(empty($this->merchants )){
            $this->showmessage('用户不存在，请检查');
        }
        $return          = array();
        // 通道名称
        $PayName = $parameter["code"];

        // 交易金额比例
        $moneyratio = $parameter["exchange"];

        //商户编号
        $return["memberid"] = $userid = $this->merchants['id'] + 10000;
        //费率
        $_userrate = M('Userrate')
            ->where(["userid" => $this->channel['userid'], "payapiid" => $this->channel['pid']])
            ->find();
        //银行通道费率
        $syschannel = M('Channel')
            ->where(['id' => $this->channel['api']])
            ->find();

        //---------------------------子账号风控start------------------------------------
        $channel_account_list        = M('channel_account')->where(['channel_id' => $syschannel['id'], 'status' => '1'])->select();

        $l_ChannelAccountRiskcontrol = new \Pay\Logic\ChannelAccountRiskcontrolLogic($pay_amount);
        $channel_account_item        = [];
        $error_msg                   = '已下线';
        foreach ($channel_account_list as $k => $v) {
            if ($v['offline_status'] && $v['control_status']) {
                //判断是自定义还是继承渠道的风控
                $temp_info               = $v['is_defined'] ? $v : $syschannel;
                $temp_info['account_id'] = $v['id']; //用于子账号风控类继承渠道风控机制时修改数据的id
                //子账号风控
                $l_ChannelAccountRiskcontrol->setConfigInfo($temp_info);
                $error_msg = $l_ChannelAccountRiskcontrol->monitoringData();
                if ($error_msg === true) {
                    $channel_account_item[] = $v;
                }
            } else if ($v['control_status'] == 0) {
                $channel_account_item[] = $v;
            }
        }
        if (empty($channel_account_item)) {
            $this->showmessage('账户:' . $error_msg);
        }
        //-------------------------子账号风控end-----------------------------------------

        // 计算权重
        if (count($channel_account_item) == 1) {
            $channel_account = current($channel_account_item);
        } else {
            $channel_account = getWeight($channel_account_item);
        }

        $syschannel['mch_id']    = $channel_account['mch_id'];
        $syschannel['signkey']   = $channel_account['signkey'];
        $syschannel['appid']     = $channel_account['appid'];
        $syschannel['appsecret'] = $channel_account['appsecret'];
        $syschannel['account']   = $channel_account['title'];

        // 定制费率
        if ($channel_account['custom_rate']) {
            $syschannel['defaultrate'] = $channel_account['defaultrate'];
            $syschannel['fengding']    = $channel_account['fengding'];
            $syschannel['fengding']    = $channel_account['fengding'];
            $syschannel['rate']        = $channel_account['rate'];
        }

        //平台通道
        $platform = M('Product')
            ->where(['id' => $this->channel['pid']])
            ->find();

        //回调参数
        $return = [
            "mch_id"       => $syschannel["mch_id"], //商户号
            "signkey"      => $syschannel["signkey"], // 签名密钥
            "appid"        => $syschannel["appid"], // APPID
            "appsecret"    => $syschannel["appsecret"], // APPSECRET
            "gateway"      => $syschannel["gateway"] ? $syschannel["gateway"] : $parameter["gateway"], // 网关
            "notifyurl"    => $syschannel["serverreturn"] ? $syschannel["serverreturn"] : $this->_site . "Pay_" .
                $PayName . "_notifyurl.html",
            "callbackurl"  => $syschannel["pagereturn"] ? $syschannel["pagereturn"] : $this->_site . "Pay_" .
                $PayName . "_callbackurl.html",
            'unlockdomain' => $syschannel['unlockdomain'] ? $syschannel['unlockdomain'] : '', //防封域名
            "pay_callbackurl"       => I('post.pay_callbackurl', ''), //商户号
        ];


        //用户优先通道
        $feilv    = $_userrate['feilv'] ? $_userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
        $fengding = $_userrate['fengding'] ? $_userrate['fengding'] : $syschannel['fengding']; // 封顶手续费
        $fengding = $fengding == 0 ? 9999999 : $fengding; //如果没有设置封顶手续费自动设置为一个足够大的数字

        //金额格式化

        if (!$pay_amount || !is_numeric($pay_amount) || $pay_amount <= 0) {
            $this->showmessage('金额错误');
        }
        $return["amount"] = floatval($pay_amount) * $moneyratio; // 交易金额

        $pay_sxfamount    = (($pay_amount * $feilv) > ($pay_amount * $fengding)) ? ($pay_amount * $fengding) :
            ($pay_amount * $feilv); // 手续费

        $pay_shijiamount = $pay_amount - $pay_sxfamount; // 实际到账金额
        $cost            = bcmul($syschannel['rate'], $pay_amount, 4); //计算成本

        //商户订单号
        $out_trade_id = $parameter['out_trade_id'];
        //生成系统订单号
        $pay_orderid = $parameter['orderid'] ? $parameter['orderid'] : get_requestord();

        //验签
        if ($this->verify()) {
            $Order                       = M("Order");
            $return['bankcode']          = $this->channel['pid'];
            $return['code']              = $platform['code']; //银行英文代码
            $return['orderid']           = $pay_orderid; // 系统订单号
            $return['out_trade_id']      = $out_trade_id; // 外部订单号
            $return['subject']           = $parameter['body']; // 商品标题
            $return['pay_orderuid']      = I('post.pay_orderuid', ''); // 商户平台用户id
            $return['pay_memberid']      = $userid; // 商户平台用户id
            $data['pay_orderuid']        = $return['pay_orderuid'];
            $data['pay_memberid']        = $userid;
            $data['pay_orderid']         = $return["orderid"];
            $data['pay_amount']          = $pay_amount; // 交易金额
            $data['pay_poundage']        = $pay_sxfamount; // 手续费
            $data['pay_actualamount']    = $pay_shijiamount; // 到账金额
            $data['pay_applydate']       = time();
            $data['pay_bankcode']        = $this->channel['pid'];
            $data['pay_bankname']        = $platform['name'];
            $data['pay_notifyurl']       = I('post.pay_notifyurl', '');
            $data['pay_callbackurl']     = I('post.pay_callbackurl', '');
            $data['pay_status']          = 0;
            $data['pay_tongdao']         = $syschannel['code'];
            $data['pay_zh_tongdao']      = $syschannel['title'];
            $data['pay_channel_account'] = $syschannel['account'];
            $data['pay_ytongdao']        = $parameter["code"];
            $data['pay_yzh_tongdao']     = $parameter["title"];
            $data['pay_tjurl']           = $_SERVER['HTTP_REFERER'];
            $data['pay_productname']     = I("request.pay_productname");
            $data['attach']              = I("request.pay_attach");
            $data['out_trade_id']        = $out_trade_id;
            $data['ddlx']                = I("post.ddlx", 0);
            $data['memberid']            = $return["mch_id"];
            $data['key']                 = $return["signkey"];
            $data['account']             = $return["appid"];
            $data['cost']                = $cost;
            $data['cost_rate']           = $syschannel['rate'];
            $data['channel_id']          = $this->channel['api'];
            $data['account_id']          = $channel_account['id'];

            if($Order->where(array('out_trade_id'=>$out_trade_id))->find()){
                $this->showmessage('已正在的下游单号，请重试.');
            }
            if($i ==1){

                /* $return['datetime'] = date('Y-m-d H:i:s', $data['pay_applydate']);
                   $return["status"]   = "success";*/
                return array('return'=>$return,'data'=>$data);
            }else{
                //添加订单
                if ($Order->add($data)) {
                    // echo 'ppppp';die;
                    $return['datetime'] = date('Y-m-d H:i:s', $data['pay_applydate']);
                    $return["status"]   = "success";
                    return $return;
                } else {
                    $this->showmessage('系统错误');
                }
            }
        } else {
            $this->showmessage('签名验证失败', $_POST);
        }
    }

    /**
     * 回调处理订单
     * @param $TransID
     * @param $PayName
     * @param int $returntype
     */
    protected function EditMoney($trans_id, $pay_name = '', $returntype = 1, $transaction_id = '')
    {
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $trans_id])->find(); //获取订单信息

        $userid     = intval($order_info["pay_memberid"] - 10000); // 商户ID
        $time       = time(); //当前时间

        //********************************************订单支付成功上游回调处理********************************************//
        if ($order_info["pay_status"] == 0) {
            //开启事物
            M()->startTrans();
            //查询用户信息
            $m_Member    = M('Member');
            $member_info = $m_Member->where(['id' => $userid])->lock(true)->find();
            //更新订单状态 1 已成功未返回 2 已成功已返回
            $res = $m_Order->where(['pay_orderid' => $trans_id, 'pay_status' => 0])
                ->save(['pay_status' => 1, 'pay_successdate' => $time]);
            if(!$res) {
                M()->rollback();
                return false;
            }
            //-----------------------------------------修改用户数据 商户余额、冻结余额start-----------------------------------
            //查询用户的提现规则
            $m_Tikuanconfig = M('Tikuanconfig');
            $tikuanconfig   = $m_Tikuanconfig->where(['userid' => $userid])->find();
            if (!$tikuanconfig || $tikuanconfig['tkzt'] != 1) {
                $tikuanconfig = $m_Tikuanconfig->where(['issystem' => 1])->find();
            }

            //要给用户增加的实际金额（扣除投诉保证金）
            $actualAmount = $order_info['pay_actualamount'];
            $complaintsDepositRule = $this->getComplaintsDepositRule($userid);
            if (isset($complaintsDepositRule['status']) && $complaintsDepositRule['status'] == 1) {
                if ($complaintsDepositRule['ratio'] > 100) {
                    $complaintsDepositRule['ratio'] = 100;
                }
                $depositAmount = round($complaintsDepositRule['ratio'] / 100 * $actualAmount, 2);
                $actualAmount -= $depositAmount;
            }

            //创建修改用户修改信息
            $member_data = [
                'last_paying_time'   => $time,
                'unit_paying_number' => ['exp', 'unit_paying_number+1'],
                'unit_paying_amount' => ['exp', 'unit_paying_amount+' . $actualAmount],
                'paying_money'       => ['exp', 'paying_money+' . $actualAmount],
            ];

            //判断用结算方式
            switch ($tikuanconfig['t1zt']) {
                case '0':
                    //t+0结算
                case '7':
                    //t+7 只限制提款和代付时间，每周一允许提款
                case '30':
                    //t+30 只限制提款和代付时间，每月第一天允许提款
                    $ymoney                 = $member_info['balance']; //改动前的金额
                    $gmoney                 = bcadd($member_info['balance'], $actualAmount, 2); //改动后的金额
                    $member_data['balance'] = ['exp', 'balance+' . $actualAmount]; //防止数据库并发脏读
                    break;
                case '1':
                    //t+1结算，记录冻结资金
                    $blockedlog_data = [
                        'userid'     => $userid,
                        'orderid'    => $order_info['pay_orderid'],
                        'amount'     => $actualAmount,
                        'thawtime'   => (strtotime('tomorrow') + rand(0, 7200)),
                        'pid'        => $order_info['pay_bankcode'],
                        'createtime' => $time,
                        'status'     => 0,
                    ];
                    $blockedlog_result = M('Blockedlog')->add($blockedlog_data);
                    if (!$blockedlog_result) {
                        M()->rollback();
                        return false;
                    }
                    $ymoney                        = $member_info['blockedbalance']; //原冻结资金
                    $gmoney                        = bcadd($member_info['blockedbalance'], $actualAmount, 2); //改动后的冻结资金
                    $member_data['blockedbalance'] = ['exp', 'blockedbalance+' . $actualAmount]; //防止数据库并发脏读

                    break;
                default:
                    # code...
                    break;
            }

            $member_result = $m_Member->where(['id' => $userid])->save($member_data);
            if ($member_result != 1) {
                M()->rollback();
                return false;
            }

            // 商户充值金额变动
            $moneychange_data = [
                'userid'     => $userid,
                'ymoney'     => $ymoney, //原金额或原冻结资金
                'money'      => $actualAmount,
                'gmoney'     => $gmoney, //改动后的金额或冻结资金
                'datetime'   => date('Y-m-d H:i:s'),
                'tongdao'    => $order_info['pay_bankcode'],
                'transid'    => $trans_id,
                'orderid'    => $order_info['out_trade_id'],
                'contentstr' => $order_info['out_trade_id'] . '订单充值,结算方式：+' . $tikuanconfig['t1zt'],
                'lx'         => 1,
                't'          => $tikuanconfig['t1zt']
            ];

            $moneychange_result = $this->MoenyChange($moneychange_data); // 资金变动记录D

            if ($moneychange_result == false /*|| $moneychange == false*/) {
                M()->rollback();
                return false;
            }

            // 记录投诉保证金
            if (isset($depositAmount) && $depositAmount > 0) {
                $depositResult = M('ComplaintsDeposit')->add([
                    'user_id'       => $userid,
                    'pay_orderid'   => $trans_id,
                    'out_trade_id'  => $order_info['out_trade_id'],
                    'freeze_money'  => $depositAmount,
                    'unfreeze_time' => time() + $complaintsDepositRule['freeze_time'],
                    'status'        => 0,
                    'create_at'     => time(),
                    'update_at'     => time(),
                ]);
                if ($depositResult == false) {
                    M()->rollback();
                    return false;
                }
            }
            M()->commit();

            // 通道ID
            $bianliticheng_data = [
                "userid"  => $userid, // 用户ID
                "transid" => $trans_id, // 订单号
                "money"   => $order_info["pay_amount"], // 金额
                "tongdao" => $order_info['pay_bankcode'],
            ];
            $this->bianliticheng($bianliticheng_data); // 提成处理

            $bianliticheng_data['order_info'] = $order_info;
            $this->ptsr($bianliticheng_data); // 平台收入处理

            //-----------------------------------------修改用户数据 商户余额、冻结余额end-----------------------------------

            //-----------------------------------------修改通道风控支付数据start----------------------------------------------
            $m_Channel     = M('Channel');
            $channel_where = ['id' => $order_info['channel_id']];
            $channel_info  = $m_Channel->where($channel_where)->find();
            //判断当天交易金额并修改支付状态
            $channel_res = $this->saveOfflineStatus(
                $m_Channel,
                $order_info['channel_id'],
                $order_info['pay_amount'],
                $channel_info
            );

            //-----------------------------------------修改通道风控支付数据end------------------------------------------------

            //-----------------------------------------修改子账号风控支付数据start--------------------------------------------
            $m_ChannelAccount      = M('ChannelAccount');
            $channel_account_where = ['id' => $order_info['account_id']];
            $channel_account_info  = $m_ChannelAccount->where($channel_account_where)->find();
            if ($channel_account_info['is_defined'] == 0) {
                //继承自定义风控规则
                $channel_info['paying_money'] = $channel_account_info['paying_money']; //当天已交易金额应该为子账号的交易金额
                $channel_account_info         = $channel_info;
            }
            //判断当天交易金额并修改支付状态
            $channel_account_res = $this->saveOfflineStatus(
                $m_ChannelAccount,
                $order_info['account_id'],
                $order_info['pay_amount'],
                $channel_account_info
            );
            if ($channel_account_info['unit_interval']) {
                $m_ChannelAccount->where([
                    'id' => $order_info['account_id'],
                ])->save([
                    'unit_paying_number' => ['exp', 'unit_paying_number+1'],
                    'unit_paying_amount' => ['exp', 'unit_paying_amount+' . $order_info['pay_actualamount']],
                ]);
            }

            //-----------------------------------------修改子账号风控支付数据end----------------------------------------------

        }

        //************************************************回调*******************************************//

        switch ($returntype) {
            case '0':
                break;
            case '1':
                $this->sendN($trans_id);
                break;
            default:
                break;
        }
    }

    /**
     * 下游回调处理订单
     * @param $TransID
     * @param $PayName
     * @param int $returntype
     */

    protected function AllCallback($sdorderno){
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $sdorderno])->find(); //获取订单信息
        if(!empty($order_info['pay_notifyurl'])){
            if($order_info['pay_status']==1 || $order_info['pay_status']==2){
                $order_info['pay_status']= 1;

            }
            $notifyurl = $order_info['pay_notifyurl'].'?pay_orderid='.$order_info['out_trade_id'].'&status='.$order_info['pay_status'];
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $notifyurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
            if($file_contents=='success'){
                $m_Order->where(array("pay_orderid" => $sdorderno))->save(array('pay_status'=>2));
            }
        }

    }

    //修改渠道跟账号风控状态
    protected function saveOfflineStatus($model, $id, $pay_amount, $info)
    {
        if ($info['offline_status'] && $info['control_status'] && $info['all_money'] > 0) {
            //通道是否开启风控和支付状态为上线
            $data['paying_money']     = bcadd($info['paying_money'], $pay_amount, 2);
            $data['last_paying_time'] = time();

            if ($data['paying_money'] >= $info['all_money']) {
                $data['offline_status'] = 0;
            }
            return $model->where(['id' => $id])->save($data);
        }
        return true;
    }

    /**
     *  验证签名
     * @return bool
     */
    protected function verify()
    {
        //POST参数
        $requestarray = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'pay_amount'      => I('request.pay_amount', ''),
            'pay_applydate'   => I('request.pay_applydate', ''),
            'pay_bankcode'    => I('request.pay_bankcode', ''),
            'pay_notifyurl'   => I('request.pay_notifyurl', ''),
            'pay_callbackurl' => I('request.pay_callbackurl', ''),
            /*'owner' => I('request.owner', ''),
            'cert_no' => I('request.cert_no', ''),
            'cvv2' => I('request.cvv2', ''),
            'cert_no' => I('request.cert_no', ''),
            'phone' => I('request.phone', ''),*/
        );



        $md5key        = $this->merchants['apikey'];
        $md5keysignstr = $this->createSign($md5key, $requestarray);
        $pay_md5sign   = I('request.pay_md5sign');



        if ($pay_md5sign == $md5keysignstr) {
            return true;
        } else {
            return false;
        }
    }

    public function setHtml($tjurl, $arraystr)
    {
        $str = '<form id="Form1" name="Form1" method="post" action="' . $tjurl . '">';
        foreach ($arraystr as $key => $val) {
            $str .= '<input type="hidden" name="' . $key . '" value="' . $val . '">';
        }
        $str .= '</form>';
        $str .= '<script>';
        $str .= 'document.Form1.submit();';
        $str .= '</script>';
        exit($str);
    }

    public function jiankong($orderid)
    {
        ignore_user_abort(true);
        set_time_limit(3600);
        $Order    = M("Order");
        $interval = 10;
        do {
            if ($orderid) {
                $_where['pay_status']  = 1;
                $_where['num']         = array('lt', 3);
                $_where['pay_orderid'] = $orderid;
                $find                  = $Order->where($_where)->find();
            } else {
                $find = $Order->where("pay_status = 1 and num < 3")->order("id desc")->find();
            }
            if ($find) {
                $this->EditMoney($find["pay_orderid"], $find["pay_tongdao"], 0);
                $Order->where("id=" . $find["id"])->save(['num' => ['exp', 'num+1']]);
            }

            sleep($interval);
        } while (true);
    }


    /**
     * 资金变动记录
     * @param $arrayField
     * @return bool
     */
    protected function MoenyChange($arrayField)
    {
        // 资金变动
        $Moneychange = M("Moneychange");
        foreach ($arrayField as $key => $val) {
            $data[$key] = $val;
        }
        $result = $Moneychange->add($data);
        return $result ? true : false;
    }

    /**
     * 平台收入
     * @param $arrayStr
     * @return bool
     */
    function ptsr($arrayStr){
        $ptsr   = M('websiteconfig')->where(['id' => '1'])->field('ptsr')->find();
        $parentP    = M('Member')->where(['id' =>  $ptsr['ptsr']])->field('id,balance')->find();

        $lr = $arrayStr['order_info']["pay_poundage"] -$arrayStr['order_info']["cost"]-$this->tc;

        if(empty($lr)){
            return;
        }
        //糯米资金变动记录
        $arrayFieldcz = array(
            "userid"   => $parentP['id'],
            "ymoney"   => $parentP['balance'],
            "money"    => $lr,
            "gmoney"   => $parentP['balance'] + $lr,
            "datetime" => date("Y-m-d H:i:s"),
            "tongdao"  => $arrayStr['order_info']['pay_bankcode'],
            "transid"  => $arrayStr["transid"],
            "orderid"  => "tx" . date("YmdHis"),
            "tcuserid" => $arrayStr["userid"],
            'contentstr' => $arrayStr['order_info']['out_trade_id'] . '订单充值,手续',
            "tcdengji" => 2,
            "lx"       => 88,
        );
        $par['balance'] =$parentP['balance'] + $lr;
        M('Member')->where(['id' => $ptsr['ptsr']])->save($par);
        $this->MoenyChange($arrayFieldcz); // 资金变动记录
    }

    /**
     * 佣金处理
     * @param $arrayStr
     * @param int $num
     * @param int $tcjb
     * @return bool
     */
    private function bianliticheng($arrayStr, $num = 3, $tcjb = 1)
    {
        if ($num <= 0) {
            return false;
        }
        $userid    = $arrayStr["userid"];
        $tongdaoid = $arrayStr["tongdao"];
        $feilvfind = $this->huoqufeilv($userid, $tongdaoid);

        if ($feilvfind["status"] == "error") {
            return false;
        } else {
            //商户费率（下级）
            $x_feilv    = $feilvfind["feilv"];
            $x_fengding = $feilvfind["fengding"];

            //代理商(上级)
            $parentid = M("Member")->where("id=" . $userid)->getField("parentid");
            if ($parentid <= 1) {
                return false;
            }
            $parentRate = $this->huoqufeilv($parentid, $tongdaoid);

            if ($parentRate["status"] == "error") {
                return false;
            } else {

                //代理商(上级）费率
                $s_feilv    = $parentRate["feilv"];
                $s_fengding = $parentRate["fengding"];

                if(empty($s_feilv) && empty($s_fengding)){
                    return false;
                }

                //费率差
                $ratediff = (($x_feilv * 1000) - ($s_feilv * 1000)) / 1000;

                if ($ratediff <= 0) {
                    return false;
                } else {
                    $parent    = M('Member')->where(['id' => $parentid])->field('id,balance')->find();
                    if(empty($parent)) {
                        return false;
                    }
                    $the = $arrayStr["money"] * $ratediff;
                    $this->tc =$this->tc+$the;


                    //代理佣金
                    $rows = [
                        'balance' => array('exp', "balance+{$the}"),
                    ];
                    M('Member')->where(['id' => $parentid])->save($rows);



                    //代理商资金变动记录
                    $arrayField = array(
                        "userid"   => $parentid,
                        "ymoney"   => $parent['balance'],
                        "money"    => $the,
                        "gmoney"   => $parent['balance'] + $the,
                        "datetime" => date("Y-m-d H:i:s"),
                        "tongdao"  => $tongdaoid,
                        "transid"  => $arrayStr["transid"],
                        "orderid"  => "tx" . date("YmdHis"),
                        "tcuserid" => $userid,
                        "tcdengji" => $tcjb,
                        "lx"       => 9,
                    );
                    $this->MoenyChange($arrayField); // 资金变动记录
                    $num                = $num - 1;
                    $tcjb               = $tcjb + 1;
                    $arrayStr["userid"] = $parentid;
                    $this->bianliticheng($arrayStr, $num, $tcjb);
                }
            }
        }
    }

    private function huoqufeilv($userid, $payapiid)
    {
        $return = array();
        //用户费率
        $userrate = M("Userrate")->where("userid=" . $userid . " and payapiid=" . $payapiid)->find();
        //支付通道费率
        $syschannel = M('Channel')->where(['id' => $payapiid])->find();

        $feilv    = $userrate['feilv'] ? $userrate['feilv'] : $syschannel['defaultrate']; // 交易费率
        $fengding = $userrate['fengding'] ? $userrate['fengding'] : $syschannel['fengding']; // 封顶手续费

        $return["status"]   = "ok";
        $return["feilv"]    = $feilv?$feilv:0;
        $return["fengding"] = $fengding?$feilv:0;
        return $return;
    }
    /**
     * 创建签名
     * @param $Md5key
     * @param $list
     * @return string
     */
    protected function createSign1($Md5key, $list)
    {
        ksort($list);
        $md5str = "";
        unset($list['sign']);
        foreach ($list as $key => $val) {
            if (!empty($val) ) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
      //  echo $md5str . "key=" . $Md5key;

        file_put_contents('createSign.txt',$md5str . "key=" . $Md5key."\r\n\r\n",FILE_APPEND);
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        return $sign;
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
            if (!empty($val) || $key !='sign') {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }

        //file_put_contents('createSign.txt',$md5str . "key=" . $Md5key."\r\n\r\n",FILE_APPEND);
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        return $sign;
    }


    public function bufa()
    {

        header('Content-type:text/html;charset=utf-8');
        $TransID    = I("get.TransID");
        $PayName    = I("get.tongdao");
        $bu_name    = I("get.buname");
        $m          = M("Order");
        $pay_status = $m->where(array("pay_orderid" => $TransID))->getField("pay_status");

        $order_info = $m->where(['pay_orderid' => $TransID])->find(); //获取订单信息

        $re['memberid'] = $order_info['pay_memberid'];
        $re['pay_orderid'] = $order_info['out_trade_id'];
        $re['amount'] = $order_info['pay_amount'];
        $re['transaction_id'] = $order_info['pay_orderid'];
        $re['returncode'] = '00';
        $re['attach'] = $order_info['attach'];


        if (intval($pay_status) == 1) {

            $this->EditMoney($TransID, $PayName, 0);
            $this->sendN($order_info['pay_orderid']);
            echo ("订单号：" . $TransID . "|" . $PayName . "已补发服务器点对点通知，请稍后刷新查看结果！<a href='javascript:window.close();'>关闭</a>");

        } else {
            echo "补发失败";
        }

    }

    /**
     * 扫码订单状态检查
     *
     */
    public function checkstatus()
    {
        $orderid = I("post.orderid");
        $Order   = M("Order");
        $order   = $Order->where(array('pay_orderid'=>$orderid))->find();

        if ($order['pay_status'] != 0) {
            echo json_encode(array('status' => 'ok', 'callback' => $this->_site . "Pay_" . $order['pay_tongdao'] . "_callbackurl.html?orderid="
                . $orderid . "&pay_memberid=" . $order['pay_memberid'] . '&bankcode=' . $order['pay_bankcode']));
            exit();
        } else {
            exit("no-$orderid");
        }
    }

    /**
     * 错误返回
     * @param string $msg
     * @param array $fields
     */
    protected function showmessage($msg = '', $fields = array(),$type=0)
    {
        header('Content-Type:application/json; charset=utf-8');

        $data = array('status' => 'error', 'msg' => $msg, 'data' => $fields);
        if($type==1){
            $data = $fields;
        }
        echo json_encode($data, 320);
        exit;
    }

    /**
     * 来路域名检查
     * @param $pay_memberid
     */
    protected function domaincheck($pay_memberid)
    {
        $referer      = $_SERVER["HTTP_REFERER"]; // 获取完整的来路URL
        $domain       = $_SERVER['HTTP_HOST'];
        $pay_memberid = intval($pay_memberid) - 10000;
        $User         = M("User");
        $num          = $User->where("id=" . $pay_memberid)->count();
        if ($num <= 0) {
            $this->showmessage("商户编号不存在");
        } else {
            $websiteid     = $User->where("id=" . $pay_memberid)->getField("websiteid");
            $Websiteconfig = M("Websiteconfig");
            $websitedomain = $Websiteconfig->where("websiteid = " . $websiteid)->getField("domain");

            if ($websitedomain != $domain) {
                $Userverifyinfo = M("Userverifyinfo");
                $domains        = $Userverifyinfo->where("userid=" . $pay_memberid)->getField("domain");
                if (!$domains) {
                    $this->showmessage("域名错误 ");
                } else {
                    $arraydomain = explode("|", $domains);
                    $checktrue   = true;
                    foreach ($arraydomain as $key => $val) {
                        if ($val == $domain) {
                            $checktrue = false;
                            break;
                        }
                    }
                    if ($checktrue) {
                        $this->showmessage("域名错误 ");
                    }
                }
            }
        }
    }

    protected function getParameter($title, $channel, $className, $exchange = 1)
    {
        if (substr_count($className, 'Controller')) {
            $length = strlen($className) - 25;
            $code   = substr($className, 15, $length);
        }
        $parameter = array(
            'code'         => $code, // 通道名称
            'title'        => $title, //通道名称
            'exchange'     => $exchange, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => I('request.pay_orderid', ''), //外部订单号
            'channel'      => $channel,
            'body'         => I('request.pay_productname', ''),
        );
        $return = $this->orderadd($parameter);
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"]     = $this->_site . 'Pay_' . $code . '_notifyurl.html';
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_' . $code . '_callbackurl.html';
        return $return;
    }

    protected function showQRcode($url, $return, $view = 'weixin',$img= 1)
    {
        if($img !=1){
            $this->assign("imgurl", $img);
        }else{
            import("Vendor.phpqrcode.phpqrcode", '', ".php");
            $QR = "Uploads/codepay/" . $return["orderid"] . ".png"; //已经生成的原始二维码图
            \QRcode::png($url, $QR, "L", 20);
            $this->assign("imgurl", $this->_site . $QR);
        }

        $this->assign('params', $return);
        $this->assign('url',   $this->_site);
        $this->assign('orderid', $return['orderid']);
        $this->assign('money', $return['amount']);
        $this->display("WeiXin/" . $view);
    }

    /**
     * 获取投诉保证金金额
     * @param $userid
     * @return array
     */
    private function getComplaintsDepositRule($userid)
    {
        $complaintsDepositRule = M('ComplaintsDepositRule')->where(['user_id' => $userid])->find();
        if (!$complaintsDepositRule || $complaintsDepositRule['status'] != 1) {
            $complaintsDepositRule = M('ComplaintsDepositRule')->where(['is_system' => 1])->find();
        }
        return $complaintsDepositRule ? $complaintsDepositRule : [];
    }


    function returnArray(){
        $returnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" => $_REQUEST["transaction_id"], // 流水号
            "returncode" => $_REQUEST["returncode"]
        );
        if ($_REQUEST["returncode"] == "00") {

            $m_Order    = M("Order");
            $order_info = $m_Order->where(['pay_orderid' => $returnArray['transaction_id']])->find(); //获取订单信息

            if($order_info && $order_info['pay_status']==1){
                //更新交易状态
                $order_where = [
                    'id'          => $order_info['id'],
                    'pay_orderid' => $order_info["pay_orderid"],
                ];
                $m_Order->where($order_where)->setField("pay_status", 2);

                return 1;
            }

        }
    }

    function orderQuery(){
        $order_where = [
           // 'pay_status'          => 0,
              'pay_orderid'         => 'S311876494816571'
        ];
        $order_where['pay_applydate'] = array(array('gt',strtotime(date('Y-m-d 00:00:00',time()))),array('lt',strtotime(date('Y-m-d H:i:s',time()))));

        $m_Order =  M("Order");
        $order = $m_Order->where($order_where)->select();

        if(empty($order)){
            return;
        }
        foreach ($order as $key=>$v){

            $Channel = M('Channel')->where(['code' => $v['pay_tongdao'], 'status' => 1])->find();
            if(empty($Channel)){
                $this->showmessage("抱歉......服务器飞去月球了");
            }

            if (!is_file(APP_PATH . '/' . MODULE_NAME . '/Controller/' . $Channel['code'] . 'Controller.class.php')) {
                $this->showmessage('通道不存在', ['pay_bankcode' => $Channel['id']]);
            }
            $_REQUEST = [
                'order'=>$v,
                'Channel'=>$Channel,
            ];

            R('Pay/'.$v['pay_tongdao'].'/orderQuery');
        }
        echo '执行结束';

    }

    function orderRefund(){
        $Md5key = 11;

        file_put_contents('md2.txt',  "key=" . $Md5key."\r\n\r\n",FILE_APPEND);


        $Order   = M("Order");

        $end = time()-24*60*60;

        //  $where['pay_status']  = 0;
        $where['pay_orderid']  = 'S123139870870817';
        //  $where['pay_applydate'] = array(array('gt',$end),array('lt',time())) ;

        $order   = $Order->where($where)->select();
        // echo M()->getLastSql();die;

        if(empty($order)){
            return;
        }
        foreach ($order as $key=>$v){

            $Channel = M('Channel')->where(['code' => $v['pay_tongdao'], 'status' => 1])->find();
            if(empty($Channel)){
                $this->showmessage("抱歉......服务器飞去月球了");
            }

            if (!is_file(APP_PATH . '/' . MODULE_NAME . '/Controller/' . $Channel['code'] . 'Controller.class.php')) {
                $this->showmessage('通道不存在', ['pay_bankcode' => $Channel['id']]);
            }
            $_REQUEST = [
                'order'=>$v,
                'Channel'=>$Channel,
            ];

            $r = R('Pay/'.$v['pay_tongdao'].'/refund');
        }

    }

    function moneyQuery(){

        $channel_id = $_REQUEST['channel_id']?$_REQUEST['channel_id']:264;

        $channel = M('channel')->where(['id' => $channel_id])->find();
        $channel_account = M('channel_account')->where(['channel_id' => $channel_id])->find();

        $_REQUEST = [
            'channel_account'=>$channel_account,
            'channel'=>$channel,
        ];
        $r = R('Pay/CHpay/moneyQuery');

        echo json_encode(['code'=>1,'money'=>$r]);
    }

}
