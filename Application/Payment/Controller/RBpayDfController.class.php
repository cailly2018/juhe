<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class RBpayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    protected $types;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->types = 'RBpayDf';
        // 商户私钥
        $this->merchantPrivateKey= BASE_PATH.'itrus001_pri.pem';
     //   $this->merchantPrivateKey= BASE_PATH.'100000001304482.pem';

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

        $batch_content[0] = 1;//序号
        $batch_content[1] =$dataInfo['banknumber'];//银行账户
        $batch_content[2] =$dataInfo['bankfullname'];
        $batch_content[3] =$dataInfo['bankname'];
        $batch_content[4] =$dataInfo['bankzhiname'];
        $batch_content[5] =$dataInfo['bankzhiname'];
        $batch_content[6] ='私';
        $batch_content[7] = $dataInfo['money'];
        $batch_content[8] = 'CNY';
        $batch_content[9] = $dataInfo['sheng'];
        $batch_content[10] = $dataInfo['shi'];

        $payInfo['mch_id'] = '100000000000147';
        //data
//序号,银行账户,开户名,开户行,分行,支行,公/私,金额,币种,省,市,手机号,证件类型,证件号,用户协议号,商户订单号,备注,会员号,绑卡Id
        $datas['charset'] ='UTF-8' ;
        $datas['trans_time'] =date('Y-m-d H:i:s',time());//时间戳格式
        $datas['notify_url'] =$this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $datas['batch_no'] =  time();//商户订单号
        $datas['batch_count'] =  $batch_content[0] ;
        $datas['pay_type'] ='1';
        $datas['batch_amount'] =  $batch_content[7];
        $datas['content'] = $batch_content[0] .",".$batch_content[1].",".$batch_content[2].",".  $batch_content[3].",".$batch_content[4] .",".$batch_content[5] .",".$batch_content[6].",".$batch_content[7].",".$batch_content[8].",".$batch_content[9] .",".$batch_content[10] .",18910116131,身份证,420321199202150718,'',". $dataInfo['orderid'].",'','',''";//
        $datas['sign_type'] ='RSA' ;


        $payInfo['exec_gateway'] = 'http://testagentpay.reapal.com/agentpay/agentpay/pay';
        $payInfo['signkey'] = 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $this->send($datas,$payInfo['mch_id'],$payInfo['exec_gateway'],$payInfo['signkey']);


    }

    function send($paramArr,$merchant_id,$url,$signkey)
    {
        $datas = $this->allsend($paramArr, $merchant_id, $url,$signkey);


        if($datas['result_code'] == '0000' ){

            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$datas['result_msg'],'status'=>5);
        }
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
        // echo $sign,"\n";
        $sign = substr ( $sign,0,(strlen ( $sign )-1));
        return $sign;
    }


    function allsend($paramArr,$merchant_id,$url,$signkey){
        //生成签名
        $datas = $this->orderstr($paramArr,$signkey);
        $sign = $this->sign_rsa($datas,$signkey);
        $paramArr['sign'] = $sign;

        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request = array();
        $request['merchant_id'] = $merchant_id;
        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey, $this->reapalPublicKey);

        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey,$paramArr);
        $request['version'] = '1.0';
        $result= $this->sendHttpRequest($request,$url);

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
    }   /**
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
        return json_decode($html,true);
    }


    function sign_rsa($data) {
        $algo = "SHA256";

        $priKey = file_get_contents($this->merchantPrivateKey);

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥

        $res = openssl_get_privatekey($priKey );
        //调用openssl内置签名方法，生成签名$sign

        openssl_sign($data, $sign, $res, $algo );

        openssl_free_key($res);

        $sign = base64_encode($sign);

        return $sign;

    }

    function notifyurl(){

        $merchantId = $_REQUEST['merchant_id'];
        $data = $_REQUEST['data'];
        $encryptkey = $_REQUEST['encryptkey'];

        file_put_contents('aa1.txt',"data=".$data."------encryptkey=".$encryptkey."\r\n\r\n",FILE_APPEND);

        $encryptkey = $this->RSADecryptkey($encryptkey,$this->merchantPrivateKey);
        $result =  $this->AESDecryptResponse($encryptkey,$data);
        $jsonObject  = json_decode($result,true);

        if(isset($jsonObject['sign'])){
            $sign= $jsonObject['sign'];

            $info = explode(',',$jsonObject['data']);
            $orderid   = $info[12];
            $status    = $info[13];
            if(empty($orderid)){
                echo json_encode(["result"=>"error","msg"=>"请求失败"]);exit;
            }
            //已退票
            if($status == '成功') {
                $status = 2;
            }
            if($status == '失败' ){
                $status =3;
            }
            $where = ['orderid' => $orderid];
            $lists = M('Wttklist')->where($where)->find();

            if(empty($lists)){
                echo json_encode(["result"=>"error","msg"=>"请求失败"]);exit;
            }
            if(!empty($lists['out_trade_no'])){
                $where = ['out_trade_no' => $lists['out_trade_no']];
                $list = M('df_api_order')->where($where)->find();

                if(!$list){
                    echo json_encode(["result"=>"error","msg"=>"请求失败"]);exit;
                }
                $arra['status'] = $status;
                $arra['orderid'] = $lists['out_trade_no'];
                if($list['notifyurl']){
                    $url= $list['notifyurl'] . "?".http_build_query($arra);
                    file_put_contents('66666.txt',"order_id=".$url ."\r\n\r\n",FILE_APPEND);
                    $ch = curl_init();
                    $timeout = 10;
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    $contents = curl_exec($ch);
                }
            }

            $this->editwtStatus($lists['id'],$status,$lists['userid'],$lists['tkmoney']+$lists['sxfmoney'],$lists['orderid'],0);
            echo json_encode(["result"=>"success","msg"=>"请求成功"]);exit;

//            if($this->verify_rsa($jsonObject,$sign)){
//
//                echo "验签通过！";
//            }else{
//                echo "验签失败！";
//            }



    }

    }
    function verify_rsa($paramArr, $sign)  {
        $algo = "SHA256";

        $data =$this->orderstr($paramArr);

        //读取公钥文件,也就是签名方公开的公钥，用来验证这个data是否真的是签名方发出的
        $pubKey = file_get_contents($this->reapalPublicKey);
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res,$algo );

        openssl_free_key($res);
        return $result;

    }


    //脚本自动处理查询代付订单
    function Query(){

        $Wttklist = M("Wttklist");
        $map['code'] = 'CHpayDf';
        $map['status'] = 1;

        $withdraw = $Wttklist->where($map)->select();
        if($withdraw){
            foreach ($withdraw as $key => $v){

                $pfa_list = M("PayForAnother")->where(['id'=>$v['df_id']])->lock(true)->find();

                $this-> PaymentQuery($v, $pfa_list,2);
            }
        }
        echo '执行成功';
    }
    //代付查询
    public function PaymentQuery($v, $pfa_list ,$s=1)
    {
          $data['service_id'] = 'tf56pay.enterprise.queryTradeStatus';
          $data['appid'] = $pfa_list['mch_id'];
          $data['tf_timestamp'] = date('YmdHis',time());//时间戳格式
          $data['businessnumber'] = $v['orderid'];
          $data['tf_sign'] = $this->_createSign($data, $pfa_list['signkey'],0);

          $re = $this->request( $pfa_list['query_gateway'],$data,'post');
          file_put_contents('Q_PaymentQuery.txt',"order_id=".$v['orderid'].'--'.date('YmdHis',time())."\r\n\r\n",FILE_APPEND);

          if($re['result'] == 'success'){
              if($re['data']['status']=='成功' ){
                  $re['pay_status'] = 2;
              }
              if($re['data']['status']=='处理中'){
                  $re['pay_status'] = 1;
              }
              if($re['data']['status']=='失败' ){
                  $re['pay_status'] = 3;
              }
              $this->editwtStatus($v['id'],$re['pay_status'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);
          }
    }

    public function editwtStatus($id,$status,$userid,$tkmoney,$orderid,$s=1)
    {
            $data           = [];
            $data["status"] = $status;

            $Wttklist = M("Wttklist");
            $map['id'] = $id;
            $withdraw = $Wttklist->where($map)->lock(true)->find();
            //开启事物
            M()->startTrans();
            M()->rollback();
            $Member     = M('Member');
            $memberInfo = $Member->where(['id' => $userid])->lock(true)->find();

        //判断状态
        switch ($status) {
            case '2':
                break;
            case '3'://处理失败
                $gmoney =  $memberInfo['balance'] + $tkmoney;
                //2,记录流水订单号
                $arrayField = array(
                    "userid"     => $userid,
                    "ymoney"     => $memberInfo['balance'],
                    "money"      => $tkmoney,
                    "gmoney"     => $gmoney,
                    "datetime"   => date("Y-m-d H:i:s"),
                    "tongdao"    => 0,
                    "transid"    => $id,
                    "orderid"    => $orderid,
                    "lx"         => 12,
                    'contentstr' => '处理失败',
                );
                //组织上级收到的手续费流水
                $sx= $Member->where(['id' => $userid])->save(['balance' =>$gmoney]);
                $res = M('Moneychange')->add($arrayField);

                break;
        }
        $data["cldatetime"] = date("Y-m-d H:i:s");


        $res = $Wttklist->where($map)->save($data);
        if ($res) {
            M()->commit();
            if($s==1){

                $this->ajaxReturn(['status' => $res]);
            }
        }


        M()->rollback();
        if($s==1){
            $this->ajaxReturn(['status' => 0]);
        }
    }


    function moneyQuery(){
       // $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'sign_type'	=>  'MD5',//商户生成签名字符串所使用的签名算法类型
            'charset'	=>  'charset',
            'version'	=>  '1.0.0',
        );
        $Channel['signkey'] = 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $Channel['mch_id'] = '100000000000147';
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $datas = $this->orderstr($arraystr,$Channel["signkey"]);
        $sign = $this->sign_rsa($datas,$Channel["signkey"]);
        $arraystr['sign'] = $sign;

        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request['merchant_id'] = $Channel['mch_id'];
        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey, $this->reapalPublicKey);
        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey,$arraystr);

        $re= $this->sendHttpRequest($request,'http://testagentpay.reapal.com/agentpay/agentpay/balancequery');

        $encryptkey = $this->RSADecryptkey($re['encryptkey'],$this->merchantPrivateKey);
        $result =  $this->AESDecryptResponse($encryptkey,$re['data']);
        $jsonObject  = json_decode($result,true);

        if($jsonObject['result_code'] != '0000'){
            return  0;
        }
        return $jsonObject['data']['balance'];
    }
}