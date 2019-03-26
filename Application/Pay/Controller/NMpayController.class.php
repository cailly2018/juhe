<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class NMpayController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'NMpay';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
     /*   if(empty($bankname)){
            $this->showmessage('请求选择银行');
        }
        $bank = M('systembank')->select();
        $bankcode ='';
        foreach ($bank as $key=>$v){
            if($v['bankname'] ==$bankname ){
                $bankcode = $v['bankcode'];
            }
        }*/
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（糯米支付宝wap）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;

        date_default_timezone_set("PRC");
        $arraystr = array(
            'pay_memberid'	=>  $return["mch_id"],//商户编号
            'pay_orderid'=>  $return['orderid'],//商户订单号
            'pay_applydate'	=> date('YmdHis',time()),//时间戳格式
            'pay_bankcode'	=> '905',
            'pay_notifyurl'	=> $notifyurl,
            'pay_callbackurl'	=> $callbackurl,
            'pay_amount'	=>  $return["amount"],//订单金额,
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['pay_md5sign'] =$this->_createSign($arraystr,  $return["signkey"]);
        $arraystr['pay_productname'] =  '实物_数码家电';
        $d = json_encode($arraystr);
        file_put_contents('log.txt',"order_id=".$d."\r\n\r\n",FILE_APPEND);

        $url= $this->_createForm($return["gateway"],$arraystr);
        header("location:" .$url);

        return;

    }

    protected function _createSign($data, $key)
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

    protected function _createForm($url, $data)
    {
        $postData = http_build_query($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        $r = curl_exec($curl);
        curl_close($curl);
        return $r;
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid   = trim($_REQUEST['pay_orderid']);
        $status    = trim($_REQUEST['status']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$_REQUEST["pay_orderid"].'---'.$status."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            die('fail');
        }
        if($status==1){
            $this->EditMoney($orderid, $this->types, 0);
           die('success');
        }else{
            die('fail');
        }
    }

    //订单查询
    function orderQuery(){

        $Channel  = $_REQUEST['Channel'];
        $order  = $_REQUEST['order'];

        $member = M('member')->where(['id' => $order['pay_memberid']-10000])->find();
        $channel_account = M('channel_account')->where(['channel_id' => $Channel['id']])->find();
        if(empty($member) || empty($channel_account) || empty($Channel) || empty($order)){
            return 0;
        }

        date_default_timezone_set("PRC");
        $arraystr = array(
            'pay_memberid'	=>  $channel_account["mch_id"],//商户编号
            'pay_orderid'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['pay_md5sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);

        $url= $this->_createForm($Channel["gateway"] ,$arraystr);

        if($url['returncode']=='00'){
            //成功/关闭/已退款/失败
            if($url['trade_state']== 'SUCCESS'  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }

}