<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class ODPPayController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'ODPPay';
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（ODP支付）',
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
            'channelUuid'	=>  "a3c10feaef2f11e8b27b000d3a80ddde",//商户编号
            'merchantCode'	=>   $return["mch_id"],
            'labelCode'	=> 'T'.$return["mch_id"],
            'terminalType'	=>  2,//交易流水号
            'outTradeNo'	=> $return['orderid'],
            'currentTime'	=>  time().'000',
            'userId'	=>  12,
            'money'	=>  sprintf("%.2f",$return["amount"]) ,
            'notifyUrl'	=>$notifyurl,
            'returnUrl'	=>$callbackurl,
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $s =  $return["signkey"].$arraystr['merchantCode'].$arraystr['labelCode'].$arraystr['terminalType'].$arraystr['outTradeNo'].$arraystr['channelUuid'].$arraystr['currentTime'].$arraystr['userId'].$arraystr['money'];

        $arraystr['sign'] =MD5($s);


        file_put_contents($this->types.'.txt',MD5($s)."----sign=".$s."\r\n\r\n",FILE_APPEND);
        $url= request_curl($return["gateway"] ,json_encode($arraystr),'post','Content-Type:application/json');

        $url =  json_decode($url ,true);


        if($url['status'] == 'true'){
            header("location:" .$url['data']);
        }else{
           echo  $url['msg'];
        }
        return;
    }


    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
/*
        "totalAmount":"20.00",
	"merchantCode":"J002",
	"merchantTradeNo":"dpat300748201809290002596172348",
	"tradeNo":"DORA201809290003004070617",
	"sign":"0a89fcc61155ac40bb474a57d4fbe9ad",
	"applyTime":"20180929000304",
	"paymentTime":"20180929000335",
	"status":"TRADE_SUCCESS"*/



        $status    =$_POST['status'];
        $sign= $_POST['sign'];
        $orderid= $_POST['tradeNo'];
        //订单号为必须接收的参数，若没有该参数，则返回错误
         if($status== 'TRADE_SUCCESS'){
             if(empty($orderid)){
                 die('fail');
             }
             $this->EditMoney($orderid, $this->types, 0);
             die('SUCCESS');
         }
        die('fail');
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
            'mchid'	=>  $channel_account["mch_id"],//商户编号
            'submchid'	=> $channel_account["mch_id"],//时间戳格式
            'orderno'	=> $order['pay_orderid'],//商户订单号
            'orderdate'	=>  date('Y-m-d',$order['pay_applydate']),//签名

        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->SortToString($arraystr);

        $url= $this->_createForm($Channel["gateway"] ,$arraystr);

        if($url['code']=='GP_00'){
            //成功/关闭/已退款/失败
            if($url['data']['status'] == '成功' &&$order['pay_orderid'] == $url['data']['businessnumber'] ){
                $this->EditMoney($url['data']['businessnumber'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }


}