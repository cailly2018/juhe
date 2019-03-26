<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class WJpayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    protected $types;
    protected $merchantPrivateKey;
    protected $reapalPublicKey;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->types = 'WJpayDf';
        $this->merchantPrivateKey= BASE_PATH.'user-rsa.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';
    }


    /**
     * 创建代付申请
     * @param $parameter
     * @return array
     */
    public function Payment($dataInfo)
    {


        if(empty($dataInfo['pay'])){
            return  array('msg'=>'缺少数据','status'=>5);
        }
        $payInfo = json_decode($dataInfo['pay'],true);

        $data['charset'] = 'UTF-8';
        $data['trans_time'] =  date('Y-m-d');
        $data['notify_url'] =  $this->_site . 'Payment_'. $this->types.'_notifyurl.html';//异步回调地址
        $data['return_url'] = $this->_site . 'Payment_'. $this->types.'_returnl.html';;
        $data['member_no'] = $payInfo['mch_id'];
        $data['merchant_order_no'] = $dataInfo['orderid'];
        $data['amount'] = $dataInfo['money']*100;
        $data['bind_id'] = $payInfo['appid'];
        $datas = $this->orderstr($data,$payInfo['signkey']);
        $data['sign'] =  $this->sign_rsa($datas,$this->merchantPrivateKey);

        $qrzfs = $this->allsend($data,$payInfo['signkey'],$payInfo['mch_id'],$payInfo['exec_gateway'] ,1);

    }

    //签名函数
    function orderstr ($paramArr,$appSecret) {

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
        echo '<pre>';
        print_r($result);die;


        $encryptkey = $this->RSADecryptkey($result['encryptkey'],$this->merchantPrivateKey);

        $result =  $this->AESDecryptResponse($encryptkey,$result['data']);


        $datas  = json_decode($result,true);

        return $datas;

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
    function  returnl(){
        echo 'success';die;
    }

}