<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;

class KJpayController extends PayController{

    protected $types;
    protected $merchantPrivateKey;
    public function __construct(){
        parent::__construct();
        $this->types = 'KJpay';

    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（快捷支付）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $re = $this->orderadd($parameter,1);
        $return = $re['return'];
        $res = $re['data'];

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        date_default_timezone_set("PRC");
        $amount = $return["amount"];
        $arraystr = [
            'versionNo' => 1,//交易流水号
            'mchNo' => $return["mch_id"],
            'price' =>  $amount,
            'subject' =>  '文具',
            'description' =>  '学习用品，文具',
            'orderDate' =>  date('YmdHms',time()),
            'tradeNo' =>$return['orderid'],//交易流水号
            'notifyUrl' => $notifyurl,
            'callbackUrl' => $return["callbackurl"],
            'payType' => '02',
            'payerName' => '10',
            'payIdCard' => '11',
            ];

        $plainReqPayload  =  json_encode($arraystr, JSON_UNESCAPED_UNICODE);
        $dats['mchNo'] = $return["mch_id"];
        $dats['payload'] = $this->encrypt($plainReqPayload,$return['signkey']);
        $dats['sign'] = strtoupper(md5($dats['payload'].$return['appsecret']));
        $result =  $this-> doPost($return['gateway'].'/dgateway/ws/trans/nocard/makeOrder', $dats);
        $result = json_decode($result, true);
        $rrd = $this->decrypt($result['payload'], $return['signkey']);
        $result = json_decode($rrd, true);

        if($result['status'] == '00') {
            $res = $this->orderadd1($res);
            if ($res) {
                if ($return['pay_memberid'] == 10112 || $return['pay_memberid'] == '10112') {
                    header("Location: ".$result ['payUrl']);
                } else {
                    $datas = array('code' => 0, 'msg' => '调起失败', 'payurl' => $result['payUrl']);
                }
            }
        }else {

            $datas = array('code' => 1, 'msg' => '调起失败', 'payurl' => '');
        }
        $this->showmessage('',$datas,1);

    }

    //解密
    public static function decrypt($data, $key) {
        $data = base64_decode($data);
        $iv='0102030405060708';
        $decrypted = openssl_decrypt($data,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
        return  $decrypted;
    }
    //加密
    public static function encrypt($data, $key) {
        $iv='0102030405060708';
        $data =  urldecode($data);
        $data =openssl_encrypt($data, 'AES-128-CBC',$key,OPENSSL_RAW_DATA, $iv);
        return base64_encode($data);
    }

    private  function orderadd1($data){
        $Order =M('order');
        //添加订单
        if ($Order->add($data)) {
            return 1;
        } else {
            $this->showmessage('系统错误');
        }
    }

    // 请求数据
    function doPost($contextPath, $data) {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $this->url . $contextPath );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_SSLVERSION, 1 );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
            'Content-type:application/json;charset=UTF-8'
        ) );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $resp = curl_exec ( $ch );
        return $resp;
    }


    // 服务器点对点返回
    public function notifyurl(){

        $xml = file_get_contents('php://input');
        if(empty($xml)){
            die('fail');
        }
        $result = json_decode($xml, true);
        $channel = M('channel')->where(['code' =>$this->types ])->find();
        $channel_accoun = M('channel_account')->where(['channel_id' =>$channel['id'] ])->find();
        $rrd = $this->decrypt($result['payload'], $channel_accoun['signkey']);
        $result = json_decode($rrd, true);

         if($result['status']== 00){
             if(empty($result['tradeNo'])){
                 die('fail');
             }
             $PayName = $this->types;
             $this->EditMoney($result['tradeNo'],$PayName, 0);
             die('success');
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
            'versionNo'	=>  1,//商户编号
            'mchNo'	=> $channel_account["mch_id"],
            'tradeNo'	=>  $order["pay_orderid"],//商户编号
        );

        $plainReqPayload  =  json_encode($arraystr, JSON_UNESCAPED_UNICODE);
        $dats['mchNo'] = $channel_account["mch_id"];
        $dats['payload'] = $this->encrypt($plainReqPayload,$channel_account['signkey']);
        $dats['sign'] = strtoupper(md5($dats['payload'].$channel_account['appsecret']));
        $result =  $this-> doPost($Channel['gateway'].'/dgateway/ws/trans/nocard/orderQuery', $dats);
        $result = json_decode($result, true);
        $rrd = $this->decrypt($result['payload'], $channel_account['signkey']);
        $result = json_decode($rrd, true);

/*
        00：交易成功
01：交易中，等待用户付款
02：交易失败，参见结果描述
09：交易中，等待用户付款*/

        if($result['status']=='00' ||$result['status']==00){
            //成功/关闭/已退款/失败
            $this->EditMoney($result['tradeNo'], $this->types, 0);
            return 1;
        }
        return 2;
    }
    function  callbackurl(){
        echo '支付完成';
    }
}