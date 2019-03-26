<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class CHpayController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'CHpay';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
        if(empty($bankname)){
            $this->showmessage('请求选择银行');
        }
        $bank = M('systembank')->select();
        $bankcode ='';
        foreach ($bank as $key=>$v){
            if($v['bankname'] ==$bankname ){
                $bankcode = $v['bankcode'];
            }
        }
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（传化支付宝wap）',
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
            'appid'	=>  $return["mch_id"],//商户编号
            'tf_timestamp'	=> date('YmdHis',time()),//时间戳格式
            'service_id'	=> 'tf56pay.gateway.bankPay',//服务名称
            'tf_sign'	=> '',//签名
            'sign_type'	=>  'MD5',//商户生成签名字符串所使用的签名算法类型
            'terminal'	=>  'PC',//终端类型
            'backurl'	=>  $notifyurl,
            'fronturl'	=>  $return["pay_callbackurl"],
            'subject'	=>  '电子产品(笔记本)',
            'businesstype'	=>  '商家消费',//务类型，请参考第4章节
            'kind'	=>  '实物_数码家电',//消费场景，请参考第4章节
            'businessnumber'=>  $return['orderid'],//商户订单号
            'transactionamount'	=> $return["amount"],//订单金额,
            'toaccountnumber'	=> '8800010291609',//收款方会员账号,
            'bankcode'	=> $bankcode,//银行编号，请参考银行码表,
            'bankaccounttype'	=> '储蓄卡',//银行卡类型：储蓄卡、信用卡
            'accountproperty'	=> '对私',//账户属性：对公/对私
            'clientip'	=>  get_client_ip(),//客户端请求IP
            'merchtonline'	=>  0,//0-线上交易，1-线下交易（实体零售，扫码支付等）
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['tf_sign'] =$this->_createSign($arraystr,  $return["signkey"]);

        $d = json_encode($arraystr);
        file_put_contents('log.txt',"order_id=".$d."\r\n\r\n",FILE_APPEND);

        $url= $this->_createForm($return["gateway"] ,$arraystr);

        if($url['code'] == 'GP_00' && $url['biz_code']=='GPBIZ_00'){
            $url['data'] = json_decode($url['data'],true);
            $url['data']['htmldata'] =base64_decode(urldecode($url['data']['htmldata']));
            echo $url['data']['htmldata'];
        }
        else{
            echo json_encode($url);
        }
        return;
    }

    protected function _createSign($data, $key)
    {
        $data['dog_sk']= $key;
        $sign          = '';
        krsort($data);
        foreach ($data as $k => $vo) {
            $sign .= $vo;
        }
        return strtoupper(md5($sign));
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
        $url=json_decode($r,true);
        return $url;
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid   = trim($_REQUEST['businessnumber']);
        $status    = trim($_REQUEST['status']);
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$_REQUEST["businessnumber"].'---'.$status."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            echo json_encode(array('result'=>'fail','msg'=>'请求失败'));
        }
        if($status=='成功'){
            $this->EditMoney($orderid, $this->types, 0);
           echo json_encode(array('result'=>'success','msg'=>'请求成功'));
        }else{
            echo json_encode(array('result'=>'fail','msg'=>'请求失败'));
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
            'appid'	=>  $channel_account["mch_id"],//商户编号
            'tf_timestamp'	=> date('YmdHis',time()),//时间戳格式
            'service_id'	=> 'tf56pay.gateway.orderQuery',//服务名称
            'tf_sign'	=> '',//签名
            'sign_type'	=>  'MD5',//商户生成签名字符串所使用的签名算法类型
            'terminal'	=>  'WP',//终端类型
            'version'	=>  '1.0',
            'businessnumber'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['tf_sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);

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