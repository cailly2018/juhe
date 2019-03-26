<?php
namespace Pay\Controller;
use Think\Controller;
class SignController extends Controller
{
    protected $merchantPrivateKey;
    protected $reapalPublicKey;
    protected $url;

    public function __construct()
    {

       // parent::__construct();
     /*   if (empty($_POST)) {
            $this->showmessage('no data!');
        }*/
        // 商户私钥
        $this->merchantPrivateKey= BASE_PATH.'100000001304482.pem';
        $this->merchantPrivateKey= BASE_PATH.'itrus001_pri.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';

    }

    public function index()
    {

        echo '<pre>';
        print_r($_POST);die;
        $phone = I("request.phone");
        $card_no = I("request.card_no");
        $owner = I("request.owner");
        $cert_no = I("request.cert_no");
        $validthru = I("request.validthru");
        $cvv2 = I("request.cvv2");
/*        if(empty($phone)){
            $this->showmessage('手机号码不能为空');
        }
        if(empty($card_no)){
            $this->showmessage('银行卡不能为空');
        }
        if(empty($owner)){
            $this->showmessage('客户名称不能为空');
        }*/


        $return['signkey'] = 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $return['mch_id'] = '100000000000147';
        $phone = '13220482188';
        $card_no = '6225413375128320';
        $owner = '韩梅梅';
        $cert_no = '210302196001012114';
        $cvv2 = '521';
        $validthru = '0420';

        $arraystr = array(
            'merchant_id'	=>  $return["mch_id"],//商户编号
            'member_id'=>  '12345',//?
            'order_no'=>  '11180521000182596',//商户订单号
            'phone'=> $phone,//银行预留手机号
            'card_no'=> $card_no,//银行卡号
            'owner'=> $owner,//持卡人姓名
            'cert_no'=> $cert_no,//身份证号
            'cert_type'=> '01',//证件类型
            'cvv2'=> $cvv2,//信用卡背后的3位数字
            'validthru'=> $validthru,//信用卡背后的3位数字
            'version'=> '1.0.0',
        );

        $this->url = 'https://testapi.reapal.com/';
        $this->send($arraystr,$return["signkey"],$return["mch_id"], $this->url.'delivery/authentication');
    }

    function sign(){
        $apiKey= 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $merchant_id = '100000000000147';
        //签约接口
        $qian['merchant_id'] = $merchant_id;
        $qian['order_no'] ='11180521000182596';
        $qian['check_code'] = '123456';
        $qian['version'] = '1.0.0';

        //生成签名
        $data = $this->orderstr($qian);
        $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);

        $this->url = 'https://testapi.reapal.com/';
        $qianyue = $this->allsend($qian,$apiKey,$merchant_id, $this->url.'/delivery/sign',1);
        echo '<pre>';
        print_r($qianyue);
    }
    //支付接口
    function pay(){
        $apiKey= 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $merchant_id = '100000000000147';
        $mac ='18:de:d7:a4:99:6b';
        $zf['merchant_id'] = $merchant_id;
        $zf['member_id'] = '12345';
        $zf['order_no'] = '111805210001825962';
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
        $zf['version'] = '1.0.0';
        $data = $this->orderstr($zf);
        $this->url = 'https://testapi.reapal.com/';
        $zf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $zfs = $this->allsend($zf,$apiKey,$merchant_id, $this->url.'delivery/pay',1);

   /*     Array
        (
            [member_id] => 12345
    [merchant_id] => 100000000000147
    [order_no] => 111805210001825962
    [result_code] => 0000
    [result_msg] => 扣款成功
        [sign] => rADMoSqTn4E6IJFskHXMPefCnrciMfjab3SY4yslhkNbX5tBpaZUqQhNpKmqhs771N8H2GCnQgt5dL90EOk8yVOakYHzCIi0ZVMaLWtBAsLFT+P890FJSQXB7l0NFhXWO9gRk0Ei+rinhhM8xhZBScOziIIn1RfGNZCAilItaUMsHPZrC0oKABnAGFaMaP+eMDWTzooezYaCTrnHknyYxCt4ftG8PW2gq1Z/MRWtGVh+ks03FMljFAPKq0/OT4asEQbgN+kXEVaxVncN5UBJssO05M36KrWkhOjViSz80mKV8A4aDTG0JNV6RBBqcYRB2taZm2pGrLqxzv3fWtCLgA==
        [sign_no] => RB1805200000717126
        [sign_type] => RSA
        [total_fee] => 100
)*/

        echo '<pre>';
        print_r($zfs);
    }

    function smspay(){
        $apiKey= 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $merchant_id = '100000000000147';
        $qrzf['merchant_id'] =$merchant_id;
        $qrzf['order_no'] ='111805210001825965';
        $qrzf['check_code'] ='123456';
        $qrzf['version'] ='1.0.0';
        $data = $this->orderstr($qrzf);
        $qrzf['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $this->url = 'https://testapi.reapal.com/';
        $qrzf = $this->allsend($qrzf,$apiKey,$merchant_id, $this->url.'delivery/smspay',1);

        echo '<pre>';
        print_r($qrzf);die;
    }

    function send($paramArr,$apiKey,$merchant_id,$url){
        //生成签名
        $data = $this->orderstr($paramArr);
        $paramArr['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $datas =  $this->allsend($paramArr,$apiKey,$merchant_id,$url,1);

        if($datas['result_code']=='0000'){
           // $data = array('status' => 0, 'msg' => '发送成功', 'data' => '');
            echo json_encode($datas); exit;
        }else{
           $this->showmessage($datas['result_msg']);
        }

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

}
