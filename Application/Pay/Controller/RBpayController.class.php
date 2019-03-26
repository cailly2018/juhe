<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class RBpayController extends PayController{

    protected $types;


    public function __construct(){
        parent::__construct();
        $this->types = 'RBpay';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
        $mac = I("request.mac");
        $mac ='18:de:d7:a4:99:6b';
        $openid = I("request.openid");
        $openid = 'ohOJ21a4OK7KlTcvILjuRNTde4A4';
     /*   if(empty($bankname)){
            $this->showmessage('请求选择银行');
        }*/
        if(empty($mac)){
            $this->showmessage('mac地址不能为空');
        }
        if(empty($openid)){
            $this->showmessage('openid地址不能为空');
        }
      /*  $bank = M('systembank')->select();
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
            'title' => '收银台支付（融宝微信）',
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
            'merchant_id'	=>  $return["mch_id"],//商户编号
            'order_no'=>  $return['orderid'],//商户订单号
            'transtime'	=> date('Y-m-d H:i:s',time()),//时间戳格式
            'currency'	=> 156,
            'total_fee'	=>  $return["amount"]*100,//订单金额,
            'title'	=> '铅笔，笔记本',
            'body'	=> '学习用品',
            'client_type'	=> '0',
            'user_id'	=> $openid,
            'appid_source'	=> 'wx22ab9db768b13bf9',
            'store_phone'	=> '0755-25876822',
            'store_name'	=> '万伽商城',
            'store_id'	=> $return["mch_id"],
            'token_id'	=>  $this->uuid(),
            'terminal_type'	=> 'mobile',
            'terminal_info'	=>  $mac,
            'member_ip'	=> get_client_ip(),//ip地址
            'seller_email'	=> 1,
            'notify_url'	=> $notifyurl,
            'time_expire'	=> '1d',
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);
        echo '<pre>';
        echo $return["gateway"];
        echo '<br>';
        print_r($arraystr);die;
        $url= $this->request($return["gateway"],$arraystr,'post');

        return;

    }

    protected function uuid() {
        if (function_exists ( 'com_create_guid' )) {
            return com_create_guid ();
        } else {
            mt_srand ( ( double ) microtime () * 10000 ); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
            $charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) ); //根据当前时间（微秒计）生成唯一id.
            $hyphen = chr ( 45 ); // "-"
            $uuid = '' . //chr(123)// "{"
                substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
            //.chr(125);// "}"
            return $uuid;
        }
    }


    protected function _createSign ($data,$apiKey) {
        global $appSecret;
        $sign = $appSecret;
        ksort($data);
        foreach ($data as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key.'='.$val.'&';
            }
        }
        $sign = substr ( $sign,0,(strlen ( $sign )-1));
        $sign.=$appSecret;
        $sign = md5($sign.$apiKey);
        return $sign;
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
        return json_decode($result,true);
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid   = trim($_REQUEST['order_no']);
        $status    = trim($_REQUEST['status']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$_REQUEST["pay_orderid"].'---'.$status."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            die('fail');
        }
        if($status=='TRADE_FINISHED'){
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
            'merchant_id'	=>  $channel_account["mch_id"],//商户编号
            'order_no'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);

        $url= $this->_createForm($Channel["gateway"] ,$arraystr);



        if($url['status']=='00'){
            //成功/关闭/已退款/失败
            if($url['trade_state']== 'SUCCESS'  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }

}