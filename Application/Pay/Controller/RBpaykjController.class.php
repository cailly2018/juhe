<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class RBpaykjController extends PayController{

    protected $types;
    protected $merchantPrivateKey;
    protected $reapalPublicKey;
    protected $url;
    protected $version;


    public function __construct(){
        parent::__construct();
        $this->types = 'RBpaykj';
        // 商户私钥
        $this->merchantPrivateKey= BASE_PATH.'100000001304482.pem';
        $this->merchantPrivateKey= BASE_PATH.'user-rsa.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';
        $this->url = '';
        $this->version = '1.0.0';

    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
        if(empty($bankname)){
            $this->showmessage('请求选择银行');
        }
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（融宝快捷支付）',
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

        $phone = I("request.phone");
        $card_no = I("request.card_no");
        $owner = I("request.owner");
        $cert_no = I("request.cert_no");
        $validthru = I("request.validthru");
        $cvv2 = I("request.cvv2");

        if(empty($phone)){
            $this->showmessage('手机号码不能为空');
        }
        if(empty($card_no)){
            $this->showmessage('银行卡不能为空');
        }
        if(empty($owner)){
            $this->showmessage('客户名称不能为空');
        }


         $phone = '13724269315';
         $card_no = '6226880117979385';
         $owner = '蔡晓丽';
         $cert_no = '450521198905127823';
         $cvv2 = '059';
         $validthru = '0622';

/*        $phone = '13220482188';
        $card_no = '6225413375128320';
        $owner = '韩梅梅';
        $cert_no = '210302196001012114';
        $cvv2 = '521';
        $validthru = '0420';*/

        $arraystr = array(
            'merchant_id'	=> $return["mch_id"],//商户编号
            'member_id'=>  $return['pay_memberid'],//?
            'order_no'=>  $return['orderid'],//商户订单号'11180521000182596',//商户订单号
            'phone'=> $phone,//银行预留手机号
            'card_no'=> $card_no,//银行卡号
            'owner'=> $owner,//持卡人姓名
            'cert_no'=> $cert_no,//身份证号
            'cert_type'=> '01',//证件类型
            'cvv2'=> $cvv2,//信用卡背后的3位数字
            'validthru'=> $validthru,//信用卡背后的3位数字
            'version'=>$this->version,
        );
     //   $return['signkey'] = '861g55g9bg77669afe940e569584ggc59dbg5da2ggecb259bgbg22g3dadaa8fa';

        //预签约接口
        $this->url = $return["gateway"];
        $this->send($arraystr,$return["signkey"],$return["mch_id"], $this->url.'delivery/authentication');

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


    /**
     *  验证签名
     * @return bool
     */
    protected function sverify()
    {
        //POST参数
        $requestarray = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'check_code'      => I('request.check_code', ''),
            'pay_mac'      => I('request.pay_mac', ''),

        );
        $member = M('member')->where(['out_trade_id'=>$requestarray['pay_memberid']])->find();
        $md5keysignstr = $this->createSign($member['apikey'], $requestarray);
        $pay_md5sign   = I('request.pay_md5sign');

        if ($pay_md5sign == $md5keysignstr) {
            return true;
        } else {
            return false;
        }
    }
    function all($qianyue){
        if($qianyue['result_code']!='0000'){
            $this->showmessage($qianyue["result_msg"]);
        }else{
            header('Content-Type:application/json; charset=utf-8');
            $data = array('status' => '0', 'msg' => $qianyue['result_msg'], 'data' => array('order_no'=>$qianyue['order_no']));
            echo json_encode($data, 320);
        }
    }
    //签约
    public function checkCode(){
        $order_no = I('request.pay_orderid');
        $pay_memberid = I('request.pay_memberid');
        $check_code = I('request.check_code');
        if(empty($order_no)){
            $this->showmessage('订单不能为空');
        }
        if(empty($check_code)){
            $this->showmessage('验证码不能为空');
        }
        if($this->sverify() == false){
            $this->showmessage('签名验证失败', $_POST);
        }

        $order = M('order')->where(['out_trade_id'=>$order_no,'pay_memberid'=>$pay_memberid])->find();

        if(empty($order)){
            $this->showmessage('订单不存在');
        }
        $channel = M('channel')->where(['code'=>$order['pay_tongdao']])->find();
        //签约接口
        $qian['merchant_id'] = $order['memberid'];
        $qian['order_no'] = $order_no;
        $qian['check_code'] =$check_code;
        $qian['version'] = $this->version;

        //生成签名
        $data = $this->orderstr($qian);
        $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $this->url = $channel["gateway"];
        $qianyue = $this->allsend($qian, $order['key'],$order['memberid'], $this->url.'/delivery/sign',1);
        if($qianyue['result_code']!='0000'){
            //把协议号保存到订单里去
           M('order')->where(['id'=>$order['id']])->save(['sign_no'=>$qianyue['data']['sign_no']]);
        }

        $this->all($qianyue);
    }
    //支付
    public function payMoney(){
        //支付接口
        $order_no = I('request.pay_orderid');
        $pay_memberid = I('request.pay_memberid');

        $mac = I('request.pay_mac');
        if(empty($order_no)){
            $this->showmessage('订单不能为空');
        }
        if(empty($mac)){
            $this->showmessage('mac地址不能为空');
        }
        if($this->sverify() == false){
            $this->showmessage('签名验证失败', $_POST);
        }

        $order = M('order')->where(['out_trade_id'=>$order_no,'pay_memberid'=>$pay_memberid])->find();
        if(empty($order)){
            $this->showmessage('订单不存在');
        }
        $channel = M('channel')->where(['code'=>$order['pay_tongdao']])->find();

       // $mac ='18:de:d7:a4:99:6b';
        $zf['merchant_id'] = $order['memberid'];
        $zf['member_id'] =  $order['pay_memberid'];
        $zf['order_no'] =$order_no;
        $zf['transtime'] =date('YmdHis',time());
        $zf['currency'] ='156';
        $zf['total_fee'] =$order['pay_amount']*100;
        $zf['sign_no'] = $order['sign_no'];//签约协议号
        $zf['title'] = '笔记本';//商品名称
        $zf['body'] = '笔记本';//商品描述
        $zf['terminal_type'] = 'mobile';//终端类型
        $zf['terminal_info'] = $mac;//终端类型
        $zf['member_ip'] =get_client_ip();//ip地址
        $zf['seller_email'] = '286517247@qq.com';
        $zf['notify_url'] = $order['pay_notifyurl'];
        $zf['time_expire'] = '';
        $zf['version'] = $this->version;
        $data = $this->orderstr($zf);
        $zf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);


        $this->url = $channel["gateway"];
        $zfs = $this->allsend($zf,$order['key'],$order['memberid'], $this->url.'delivery/pay',1);

        $this->all($zfs);
    }
    //确认支付
    public function ConfirmPayment(){
        $order_no = I('request.pay_orderid');
        $pay_memberid = I('request.pay_memberid');
        $check_code = I('request.check_code');
        if(empty($order_no)){
            $this->showmessage('订单不能为空');
        }

        if(empty($check_code)){
            $this->showmessage('验证码不能为空');
        }

        if($this->sverify() == false){
            $this->showmessage('签名验证失败', $_POST);
        }

        $order = M('order')->where(['out_trade_id'=>$order_no,'pay_memberid'=>$pay_memberid])->find();
        if(empty($order)){
            $this->showmessage('订单不存在');
        }
        $channel = M('channel')->where(['code'=>$order['pay_tongdao']])->find();

        $qrzf['merchant_id'] =$order['memberid'];
        $qrzf['order_no'] = $order_no;
        $qrzf['check_code'] =$check_code;
        $qrzf['version'] = $this->version;;
        $data = $this->orderstr($qrzf);
        $qrzf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $this->url = $channel["gateway"];
        $qrzf = $this->allsend($qrzf,$order['key'],$order['memberid'], $this->url.'delivery/smspay',1);
        if($qrzf['result_code']!='0000'){
         //解约
            $this->Rescission($order_no,$order['memberid']);
        }
        $this->all($qrzf);

    }
    //解约
    function  Rescission($order_no,$memberid){

        $order = M('order')->where(['out_trade_id'=>$order_no,'pay_memberid'=>$memberid])->getField('pay_memberid','sign_no');
        if(empty($order)){
            $this->showmessage('订单不存在');
        }
        $channel = M('channel')->where(['code'=>$order['pay_tongdao']])->getField('gateway');
        //解约接口
        $qian['merchant_id'] =$memberid;
        $qian['member_id'] = $order['pay_memberid'];
        $qian['sign_no'] = $order['sign_no'];
        $qian['version'] = $this->version;
        //生成签名
        $data = $this->orderstr($qian);
        $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $this->url = $channel["gateway"];
        $qianyue = $this->allsend($qian, $order['key'],$order['memberid'], $this->url.'/delivery/querycontract',1);

    }

    function send($paramArr,$apiKey,$merchant_id,$url){
        //生成签名
        $data = $this->orderstr($paramArr);
        $paramArr['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);

        $datas =  $this->allsend($paramArr,$apiKey,$merchant_id,$url,1);

        if($datas['result_code']!='0000'){
            $this->showmessage($datas["result_msg"]);
        }else{
            header('Content-Type:application/json; charset=utf-8');
            $data = array('status' => '0', 'msg' => $datas['result_msg'], 'data' => array('order_no'=>$datas['order_no']));
            echo json_encode($data, 320);
        }
        //签约接口
       die;
        //签约接口
       /* $qian['merchant_id'] = $merchant_id;
        $qian['order_no'] = $paramArr['order_no'];
        $qian['check_code'] = '123456';
        $qian['version'] = $paramArr['version'];

        //生成签名
        $data = $this->orderstr($qian);
        $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $qianyue = $this->allsend($qian,$apiKey,$merchant_id, $this->url.'/delivery/sign',1);
        echo '<pre>';
        print_r($qianyue);*/
        //支付接口


   /*     $mac ='18:de:d7:a4:99:6b';
        $zf['merchant_id'] = $merchant_id;
        $zf['member_id'] = '12345';
        $zf['order_no'] = '11180521000182596';
        $zf['transtime'] =date('YmdHis',time());
        $zf['currency'] ='156';
        $zf['total_fee'] = 1*100;
        $zf['sign_no'] = 'RB1805200000717126';//签约协议号
        $zf['title'] = '笔记本';//商品名称
        $zf['body'] = '笔记本';//商品描述
        $zf['terminal_type'] = 'mobile';//终端类型
        $zf['terminal_info'] = $mac;//终端类型
        $zf['member_ip'] =get_client_ip();//ip地址
        $zf['seller_email'] = '286517247@qq.com';//ip地址
        $zf['notify_url'] = 'hh';
        $zf['time_expire'] = '';
        $zf['version'] = $paramArr['version'];
        $data = $this->orderstr($zf);
        $zf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $zfs = $this->allsend($zf,$apiKey,$merchant_id, $this->url.'delivery/pay',1);

        echo '<pre>';
        print_r($zfs);*/

        $qrzf['merchant_id'] =$merchant_id;
        $qrzf['order_no'] =$paramArr['order_no'];
        $qrzf['check_code'] ='123456';
        $qrzf['version'] =$paramArr['version'];
        $data = $this->orderstr($qrzf);
        $qrzf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $qrzf = $this->allsend($qrzf,$apiKey,$merchant_id, $this->url.'delivery/smspay',1);

        echo '<pre>';
        print_r($qrzf);die;


    }
    //签名函数
    function orderstr ($paramArr) {
        global $appSecret;
        $sign = $appSecret;

        ksort($paramArr);
        foreach ($paramArr as $key => $val) {

            if ($key != '' && $val != '' && $key != 'sign' && $key != 'sign_type') {
                $sign .= $key.'='.$val.'&';
            }
        }
        $sign = substr ( $sign,0,(strlen ( $sign )-1));
        return $sign;
    }

    /**RSA签名函数

     * $data为待签名数据，比如URL

     * 签名用游戏方的保密私钥，必须是没有经过pkcs8转换的.结果需要用base64编码以备在URL中传输

     * return Sign 签名

     */
    function sign_rsa($data,$merchantPrivateKey) {
        $algo = "SHA256";

        $priKey = file_get_contents($merchantPrivateKey);

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey );

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, $algo );
        openssl_free_key($res);
        $sign = base64_encode($sign);

        return $sign;

    }
    function allsend($paramArr,$apiKey,$merchant_id,$url,$int){

        $paramArr['sign_type'] = 'RSA';
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request = array();
        $request['merchant_id'] = $merchant_id;

        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey, $this->reapalPublicKey);

        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey,$paramArr);
        $result= $this->sendHttpRequest($request,$url,$int);
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
    }
    function sendHttpRequest($params, $url ,$int =1) {
        $opts = $this->getRequestParamString ( $params );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
        curl_setopt ( $ch, CURLOPT_SSLVERSION, $int);
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
        return json_decode($html,true);
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