<?php
namespace Pay\Controller;
use Org\Util\Ysenc;

class WJpayController extends   PayController{
    protected $merchantPrivateKey;
    protected $reapalPublicKey;
    protected $url;
    protected $info;
    protected $signkey;
    protected $mch_id;

    public function __construct()
    {
        parent::__construct();
        $this->merchantPrivateKey= BASE_PATH.'user-rsa.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';
        $this->types = 'WJpay';
        $this->info=$this->key();
        $this->url =  $this->info['gateway'];
        $this->mch_id = $this->info['mch_id'];
        $this->signkey = $this->info['signkey'];

    }
    function key(){
        $pay_channel =M('channel')->where(array('code'=>$this->types))->find();
        $channel =M('channel_account')->where(array('channel_id'=>$pay_channel['id']))->find();
        $channel['gateway'] = $pay_channel['gateway'];

        return $channel;

    }
    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（融宝支付支付宝）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $re = $this->orderadd($parameter,1);
        $re['data']['pay_orderid'] = date('Ymd',time()).$this->GetRandStr(10);
        $return = $re['return'];
        $return['orderid'] =  $re['data']['pay_orderid'] ;

        $this->orderadd1($re['data']);
        $this->assign('order', $return);
        $this->assign('orderid', $return['orderid']);
        $this->assign('title', $parameter['title']);
        $this->assign('amount', $return["amount"]);
        $this->assign('url', $this->_site . 'Pay_'.$this->types.'_presign.html');
        $this->display('Charges/WJ');
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
    //预签约
    public function presign()
    {

        $apiKey=  $this->info['signkey'];
        $merchant_id =   $this->mch_id ;

        $card_type = I("request.card_type");
        $phone = I("request.phone");
        $card_no = I("request.card_no");
        $owner = I("request.owner");
        $cert_no = I("request.cert_no");
        $validthru = I("request.validthru");
        $cvv2 = I("request.cvv2");
        $order_no = I("request.order_no");
        $amount = I("request.amount");

        $return['signkey'] = $apiKey;
        $return['mch_id'] = $merchant_id;

        $arraystr = array(
            'merchant_id'	=>  $return["mch_id"],//商户编号
            'member_id'=>  $return["mch_id"],//?
            'member_status'=> 0,//?
            'order_no'=>  '',//商户订单号
            'phone'=> $phone,//银行预留手机号
            'card_no'=> $card_no,//银行卡号
            'card_type'=>$card_type,//银行卡号
            'owner'=> $owner,//持卡人姓名
            'cert_no'=> $cert_no,//身份证号
            'cert_type'=> '01',//证件类型
            'cvv2'=> $cvv2,//信用卡背后的3位数字
            'validthru'=> $validthru,//信用卡背后的3位数字
            'version'=> '1.0.0',
        );
        $sign_no =M('sign_no');
        $info  =   $sign_no->where(['card'=>$card_no])->find();
 // echo M()->getLastSql();die;
    //    print_r($info);die;
        if(empty($info)){
            $datas =  $this->send($arraystr,$return["signkey"],$return["mch_id"], $this->url.'member/custmem/agreement/presign');
            if($datas['result_code']=='0000'){

                header("Location: ".$this->_site . 'Pay_'.$this->types.'_signS.html?order_no='.$datas['order_no'].'&amount='.$amount.'&dd='.$order_no.'&card_no='.$card_no);
            }else{
                $this->assign('msg',$datas['result_msg']);
                $this->display('Charges/fail');
            }
        }else{
            header("Location: ".$this->_site . 'Pay_'.$this->types.'_sign.html?order_no='.$card_no.'&amount='.$amount.'&dd='.$order_no.'&card_no='.$card_no.'&sign_no='.$info['sign_no']);
        }

    }
   function signS(){
       $order_no = I("request.order_no");
       $amount = I("request.amount");
       $dd = I("request.dd");
       $card_no = I("request.card_no");
       $this->assign('orderid', $order_no);
       $this->assign('dd', $dd);
       $this->assign('amount', $amount);
       $this->assign('card_no', $card_no);
       $this->assign('sign_no', '');
       $this->assign('title', '收银台');
       $this->assign('title1', '确认');
       $this->assign('url', $this->_site . 'Pay_'.$this->types.'_sign.html');
       $this->display('Charges/signS');
   }

    function sign(){
        $order_no = I("request.order_no");
        $check_code = I("request.check_code");
        $amount = I("request.amount");
        $dd = I("request.dd");
        $card_no = I("request.card_no");
        $sign_no = I("request.sign_no");
        $apiKey=  $this->info['signkey'];
        $merchant_id =   $this->mch_id ;

        $dats =array(
            'order_no'=>$dd,
            'Key'=>$apiKey,
            'merchant_id'=>$merchant_id,
            'url'=> $this->url,
            'amount'=> $amount*100,
        );


        if(empty($sign_no)){

            //签约接口
            $qian['merchant_id'] = $merchant_id;
            $qian['order_no'] =$order_no;
            $qian['check_code'] = $check_code;
            $qian['version'] = '1.0.0';

            //生成签名
            $data = $this->orderstr($qian);
            $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);

            $qianyue = $this->allsend($qian,$apiKey,$merchant_id, $this->url.'member/custmem/agreement/sign',1);
            if($qianyue['result_code'] == '0000'){

                $data_s['card'] =$card_no;
                $data_s['sign_no'] =$qianyue['sign_no'];
                $data_s['ctime'] =time();
                M('sign_no')->add($data_s);

                M('order')->where(['pay_orderid'=>$dd])->save(array('s_orderid'=>$qianyue['order_no'],'sign_no'=>$qianyue['sign_no']));

                $dats['sign_no'] = $qianyue['sign_no'];

            }else{

                $this->assign('orderid', $qianyue['order_no']);
                $this->assign('amount', $amount);
                $this->assign('msg', $qianyue['result_msg']);
                $this->display('Charges/fail');
            }
        }else{

            $dats['sign_no'] =$sign_no;
        }


        $r =   $this->pays($dats);
        $this->assign('orderid', $dd);
        $this->assign('amount', $amount);

        if( $r['result_code'] == '3068'){
            $this->assign('sign_no', $sign_no);
            $this->assign('title', '确认收银台');
            $this->assign('title1', '确认支付');
            $this->assign('dd', $dd);
            $this->assign('url', $this->_site . 'Pay_'.$this->types.'_smspay.html');
            $this->display('Charges/signS');

        }else{

            $this->assign('msg', $r['result_msg']);
            $this->display('Charges/fail');
        }

    }


    //支付接口
    function pays($data){

        $zf['merchant_id'] = $data['merchant_id'];
        $zf['member_id'] = $data['merchant_id'];
        $zf['order_no'] = $data['order_no'];
        $zf['transtime'] =date('YmdHis',time());
        $zf['currency'] ='156';
        $zf['member_status'] = '0';
        $zf['business_type'] = 4;
        $zf['sign_no'] = $data['sign_no'];//签约协议号
        $zf['total_fee'] =$data['amount'];
        $zf['title'] = '笔记本';//商品名称
        $zf['body'] = '笔记本';//商品描述
        $zf['terminal_type'] = 'mobile';//终端类型

        //$zf['terminal_info'] = $mac;//终端类型
       /// $zf['member_ip'] =get_client_ip();//ip地址
       // $zf['seller_email'] = '286517247@qq.com';//ip地址
        $zf['notify_url'] =$this->_site . 'Pay_'.$this->types.'_notifyurl.html';
        $zf['time_expire'] = '';
        $zf['version'] = '1.0.0';
        $datas = $this->orderstr($zf);
        $zf['sign'] =  $this->sign_rsa($datas,$this->merchantPrivateKey);
        $zfs = $this->allsend($zf,$data['Key'],$data['merchant_id'], $data['url'].'member/custmem/agreement/pay',1);
        return $zfs;

    }

    function smspay(){

        $order_no = I("request.order_no");
        $check_code = I("request.check_code");
        $amount = I("request.amount");
        $dd = I("request.dd");

        $apiKey=  $this->info['signkey'];
        $merchant_id =   $this->mch_id ;
        $qrzf['merchant_id'] =$merchant_id;
        $qrzf['order_no'] =$dd;
        $qrzf['check_code'] =$check_code;
        $qrzf['sign_type'] ='RSA';
        $qrzf['version'] ='1.0.0';
        $data = $this->orderstr($qrzf);
        $qrzf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);

        $url = $this->url.'member/custmem/agreement/pay/confirm';
        $qrzfs = $this->allsend($qrzf,$apiKey,$merchant_id,$url ,1);

        $this->assign('orderid', $dd);
        $this->assign('amount', $amount);

        if($qrzfs['result_code'] == '0000'){

            $this->assign('msg', $qrzfs['result_msg']);
            $this->display('Charges/success1');

        }else{
            $this->assign('msg', $qrzfs['result_msg']);
            $this->display('Charges/fail');
        }

    }

    function send($paramArr,$apiKey,$merchant_id,$url){
        //生成签名
        $data = $this->orderstr($paramArr);
        $paramArr['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $datas =  $this->allsend($paramArr,$apiKey,$merchant_id,$url,1);

        if($datas['result_code']=='0000'){
           // $data = array('status' => 0, 'msg' => '发送成功', 'data' => '');
            return  $datas;
        }else{
           $this->showmessage($datas['result_msg']);
        }

    }

    function GetRandStr($length){
        $str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
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

        return $data;
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
        header('Content-Type:text/html;charset=GB2312');
        $r = $_POST;
        file_put_contents('ckurl.txt',"wj=".json_encode($r)."\r\n\r\n",FILE_APPEND);
     //  $data = '{"data":"MOF2O\/U3aNRXYIkEiqCsRi1ZeJEWW9ArGOe8gHAOHXeseBEjP8yDnC5Zf3y\/ggEOdbilhVhL9s6rmBm9TAucv8Hnt0636XXUnLG0X7ZRlJ3MD+kMIaayr1zqgcNSMUpSKClyuwXuZTYfzfpBEjnbsVvNuxJeo+NVLKeKQOhzkOrp26TnDMbvX9FvO5Y04Vy973guoIkugwAvH3EUxIxWe9MOKnBMjTfF2jwlxDNsyczrNbBmO7xw\/Zh6jX8Q2xA8NaWi8OmYKoTapgCSnUa481xp26JtMiBQ\/VVm3qySK8+XeyOtIPPI2zkBEwyv5c7kBT79UbxgZWR\/mCZ\/lbR3JESE9gnjsv4g0vsMYisfYCbIJp\/3CrYML3mBqJjBzJ7+iug\/4PvndJ3UUVrkEqJDpJfl3dX44ioM39SIrVJUC\/X0jpOSb7fkld8Aypw4WdFVUTf097RhpW8VaGyLMT3qTFiZhUQsd+u\/ra8A6YfsaqL6HkVjjsmeegn\/rgi+m61USOZFE5gpIu3GkiyUrEI9UjU+hwJN2VZ9BcQmrrFt2Sk3iY+RdvwrzlHCNk64H11scct6FDO4J5Hm1b7mEzQADxZ61bqxt5RdNA1zpCglYWGgXlUuBGqpJQE2XOfOmqnAWBHJMKwDn5xj2eGBYyYetI4Mglj0rZASqKtOFsWOQu1YqGg4Pb3318BnEtWrRdYJ","merchant_id":"100000001304600","encryptkey":"myBFcdkxf2vALjOXBg++M8hHxzidJA38LoKy0eu0S4gkEjEfGg0zypS8mNWHZaNGJB8B+T00Asi7hnMNfDQbgKYabCWHws93aCe9iL3Mn4M6eznJQXMA8r8egK6gJhP8JRcIb8bMcyuQ2QKkSGlkbX0saC4VgAyn\/HrxHUTQ\/\/I="}';

       // $r = json_decode($data,true);
        $encryptkey = $this->RSADecryptkey($r['encryptkey'],$this->merchantPrivateKey);

        $result =  $this->AESDecryptResponse($encryptkey,$r['data']);

        $result = json_decode($result,true);


        if($result){
            if($result['status'] =='TRADE_FINISHED'){
              //  $Order =M('order');
               // $r =   $Order->where(['s_orderid'=>$result['order_no']])->find();
                //if($r){
                    $this->EditMoney( $result['order_no'], $this->types, 0);
                    die('success');
               // }

                die('fail');
            }

        }die('fail');

    }
}
