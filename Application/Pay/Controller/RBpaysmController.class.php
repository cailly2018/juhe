<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class RBpaysmController extends PayController{

    protected $types;
    protected $merchantPrivateKey;
    protected $reapalPublicKey;


    public function __construct(){
        parent::__construct();
        $this->types = 'RBpaysm';
        // 商户私钥
        $this->merchantPrivateKey= BASE_PATH.'100000001304482.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';

    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
/*
        if(empty($bankname)){
            $this->showmessage('请求选择银行');
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
        // 订单号，可以为空，如果为空，由系统统一的生成
        $re = $this->orderadd($parameter,1);
        $return = $re['return'];
        $this->orderadd1($re['data']);

        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;

        date_default_timezone_set("PRC");
        $mac ='18:de:d7:a4:99:6b';
        $arraystr = array(
            'merchant_id'	=>  $return["mch_id"],//商户编号
            'order_no'=>  $return['orderid'],//商户订单号
            'transtime'	=> date('Y-m-d H:i:s',time()),//时间戳格式
            'currency'	=> 156,
            'total_fee'	=>  $return["amount"]*100,//订单金额,
            'title'	=> '铅笔，笔记本',
            'body'	=> '学习用品',
            'terminal_type'	=> 'mobile',
            'terminal_info'	=>  $mac,
            'member_ip'	=> get_client_ip(),//ip地址
            'seller_email'	=> '286517247@qq.com',
            'notify_url'	=> $notifyurl,
            'token_id'	=>  $this->uuid(),
        );

        $this->send($arraystr,$return["signkey"],$return["mch_id"],$return["gateway"].'weixinPay');

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


    function send($paramArr,$apiKey,$merchant_id,$url){
        $datas =  $this->allsend($paramArr,$apiKey,$merchant_id,$url);

        if($datas['result_code']=='0000'){
            $data['orderid'] = $paramArr['order_no'];
            $data['amount'] = $paramArr['total_fee']/100;
            $data['out_trade_id'] = $paramArr['orderNo'];
            $data['datetime'] = date('Y-n-d H:i;s',time());
            $data['subject'] = $paramArr['title'];

            $this->showQRcode($datas['code_img_url'], $data, $view = 'weixin',$datas['code_img_url']);
        }else{
            echo '<pre>';
            print_r($datas);die;
        }

    }

    function allsend($paramArr,$apiKey,$merchant_id,$url){
        //生成签名
        $sign = $this->_createSign($paramArr,$apiKey);
        $paramArr['sign'] = $sign;
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request = array();
        $request['merchant_id'] = $merchant_id;
        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey, $this->reapalPublicKey);
        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey,$paramArr);
        $result= $this->sendHttpRequest($request,$url);

        $encryptkey = $this->RSADecryptkey($result['encryptkey'],$this->merchantPrivateKey);
        $result =  $this->AESDecryptResponse($encryptkey,$result['data']);
        $datas  = json_decode($result,true);
        return $datas;

    }
    protected function _createSign ($data,$apiKey) {
        global $appSecret;
        $sign = $appSecret;
        ksort($data);
        if (isset($data['sign_type'])){
            unset($data['sign_type']);
        }

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
     * 生成一个随机的字符串作为AES密钥
     *
     * @param number $length
     * @return string
     */
    function generateAESKey($length=16){
        $baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $AESKey = '';
        $_len = strlen($baseString);
        for($i=1;$i<=$length;$i++){
            $AESKey .= $baseString[rand(0, $_len-1)];
        }
        return $AESKey;
    }
    /**
     * 通过RSA，使用融宝公钥，加密本次请求的AESKey
     *
     * @return string
     */
    function RSAEncryptkey($encryptKey,$reapalPublicKey){
        $public_key= $this->getPublicKey($reapalPublicKey);

        $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的

        openssl_public_encrypt($encryptKey,$encrypted,$pu_key);//公钥加密

        return base64_encode($encrypted);

    }
    function getPublicKey($cert_path) {
        $pkcs12 = file_get_contents ( $cert_path );
        return $pkcs12;
    }
    /**
     * 通过AES加密请求数据
     *
     * @param array $query
     * @return string
     */
    function AESEncryptRequest($encryptKey,array $query){

        return $this->encrypt(json_encode($query),$encryptKey);
    }
    function encrypt($data, $key) {
        return openssl_encrypt($data, 'AES-128-ECB', $key,0, '');

        return $data;
    }
    function sendHttpRequest($params, $url) {
        $opts = $this->getRequestParamString ( $params );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
        curl_setopt ( $ch, CURLOPT_SSLVERSION, 3);
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
            'Content-type:application/x-www-form-urlencoded;charset=UTF-8'
        ) );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );

        /**
         * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
         */
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

        // 运行cURL，请求网页
        $html = curl_exec ( $ch );
        curl_close ( $ch );
        return json_decode($html,true);;
    }

    /**
     * 组装报文
     *
     * @param unknown_type $params
     * @return string
     */
    function getRequestParamString($params) {
        $params_str = '';
        foreach ( $params as $key => $value ) {
            $params_str .= ($key . '=' . (!isset ( $value ) ? '' : urlencode( $value )) . '&');
        }
        return substr ( $params_str, 0, strlen ( $params_str ) - 1 );
    }

    /**
     * 通过RSA，使用融宝公钥，加密本次请求的AESKey
     *
     * @return string
     */
    function RSADecryptkey($encryptKey,$merchantPrivateKey){
        $private_key= $this->getPrivateKey($merchantPrivateKey);

        $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id

        openssl_private_decrypt(base64_decode($encryptKey),$decrypted,$pi_key);//私钥解密
        return $decrypted;

    }
    function getPrivateKey($cert_path) {
        $pkcs12 = file_get_contents ( $cert_path );
        return $pkcs12;
    }
    /**
     * 通过AES解密请求数据
     *
     * @param array $query
     * @return string
     */
    function AESDecryptResponse($encryptKey,$data){
        return $this->decrypt($data,$encryptKey);

    }
    function decrypt($data, $key) {
        return openssl_decrypt($data, 'AES-128-ECB', $key,0, '');

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


    // 服务器点对点返回
    public function notifyurl(){
        $result['encryptkey']   = trim($_REQUEST['encryptkey']);
        $result['data']    = trim($_REQUEST['data']);
        $encryptkey = $this->RSADecryptkey($result['encryptkey'],$this->merchantPrivateKey);
        $result =  $this->AESDecryptResponse($encryptkey,$result['data']);

        if(empty($result)){
            die('fail');
        }
        $orderid =  $result['order_no'];
        $orderList = M('Order')->where(['pay_orderid' => $orderid])->find();
        $Sign = $result['sign'];
        unset($result['sign']);

        $md5Sign =  $this->_createSign($result,$orderList['key']);
        if($Sign !=$md5Sign){
            die('fail');
        }
        $status =  $result['status'];

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$orderid.'---'.$status."\r\n\r\n",FILE_APPEND);

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
            'merchant_id'	=>  $order["memberid"],//商户编号
            'order_no'=>  $order['pay_orderid'],//商户订单号
            'version'=>  '1.0.0',
            'sign_type'=>  'MD5',
        );

        $datas = $this->allsend($arraystr,$order["key"],$order["memberid"],$Channel["gateway"].'search');
        echo '<pre>';
        print_r($datas);die;
        if($datas['result_code']=='0000'){
            //成功/关闭/已退款/失败
            if($datas['status'] == 'completed')
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
        }
        return 2;
    }



}