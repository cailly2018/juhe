<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Payment\Controller;

/**
 * 用户中心首页控制器
 * Class IndexController
 * @package User\Controller
 */
class IndexController extends PaymentController{

    protected $merchantPrivateKey;
    protected $reapalPublicKey;


    public function __construct(){
        parent::__construct();
        $_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->merchantPrivateKey= BASE_PATH.'itrus001_pri.pem';
        // 融宝公钥
        $this->reapalPublicKey = BASE_PATH.'itrus001.pem';
    }
    protected function get_random($len = 3)
    {
        //range 是将10到99列成一个数组
        $numbers = range(10, 99);
        //shuffle 将数组顺序随即打乱
        shuffle($numbers);
        //取值起始位置随机
        $start = mt_rand(1, 10);
        //取从指定定位置开始的若干数
        $result = array_slice($numbers, $start, $len);
        $random = "";
        for ($i = 0; $i < $len; $i++) {
            $random = $random . $result[$i];
        }
        return $random;
    }
    function all(){
        $pay_orderid = 'E' . date("YmdHis") .$this->get_random(4);    //订单号
        //$pay_amount = "0.01";    //交易金额
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间$this->_site .
        $pay_notifyurl = "http://www.dlaravel.com/";   //服务端返回地址
        $pay_callbackurl = "http://www.dlaravel.com/admin/login";  //页面跳转返回地址
        $banknumber ='6226623005303338';
        $bankname ='中国光大银行';
        //扫码

        $native = array(
            'pay_memberid'    => 10118,
            'pay_orderid'     => $pay_orderid,
            'pay_amount'      => 1,
            'pay_applydate'   => $pay_applydate,
            'pay_bankcode'    => 902,
            'pay_notifyurl'   => $pay_notifyurl,
            'pay_callbackurl' => $pay_callbackurl,
        );

        $Md5key = M('Member')->where(['id'=>$native['pay_memberid']-10000])->getField('apikey');

        ksort($native);

        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $native['pay_md5sign'] = strtoupper(md5($md5str . "key=" . $Md5key));

        $url  = 'http://n1.nutbe.cn/Pay_index.html';

        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $native);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($options)){
            curl_setopt_array($ch, $options);
        }
        $data=curl_exec($ch);
        curl_close($ch);

        echo $data;

    }




    function test(){

        $apiKey = 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $merchant_id = '100000000000147';
        //签约接口
        $qian['merchant_id'] = $merchant_id;
        $qian['order_no'] = '32131432423554';
        $qian['check_code'] = '123456';
        $qian['version'] = '1.0.0';
        //生成签名
        $data = $this->orderstr($qian);
        $qian['sign'] =  $this->sign_rsa($data,$this->merchantPrivateKey);
        $qianyue = $this->allsend($qian,$apiKey,$merchant_id,'https://testapi.reapal.com/delivery/sign',1);
        echo '<pre>';
        print_r($qianyue);die;
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
        $result= $this->sendHttpRequest($request,$url,1);

        $encryptkey = $this->RSADecryptkey($result['encryptkey'],$this->merchantPrivateKey);

        $result =  $this->AESDecryptResponse($encryptkey,$result['data']);

        $datas  = json_decode($result,true);
        return $datas;

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


    public function index(){

        //验证传来的数据
        $post_data = verifyData($this->verify_data_);


        if($post_data['opt'] == 'exec'){
            //判断是否登录
            isLogin();
            $opts = 2;
        }else{
            $post_data['opt'] = 'exec';
            $opts = 1;
        }


        //获取要操作的订单id
        $post_data['id'] = explode(',', rtrim($post_data['id'], ',') );

        //根据操作查询不同状态的订单
        if ($post_data['opt'] == 'exec') {
            $status = 0;
        } else {
            $status = ['in', '1, 4'];
        }
        $where = ['id'=>['in', $post_data['id']], 'status'=>$status];

        $wttk_lists = $this->selectOrder($where);

		$post_data['code'] = $post_data['opt'] == 'exec'?$post_data['code']:$wttk_lists[0]['df_id'];

		//获取要代付的通道信息
        $pfa_list = $this->findPaymentType($post_data['code']);

        //检查代付金额与用户金额是否相同
        //$this->checkMoney($wttk_lists['userid'] , $wttk_lists['money']);
		
        //判断代付通道的文件是否存在
        $code = $pfa_list['code'];
        $code || showError('代付渠道不存在！');


        $file = APP_PATH . 'Payment/Controller/' . $code . 'Controller.class.php';


        is_file($file) || showError('代付渠道不存在！');
        //循环存在代付通道的文件限制一次只能操作15条数据
        $opt = ucfirst( $post_data['opt']);

        if($opts==2){
            if($opt == 'Exec' && !session('admin_submit_df')) {
                showError('未通过身份验证！');
            }
        }

        if( count($wttk_lists)<= 15){

            $fp = fopen($file, "r");
            foreach($wttk_lists as $k => $v){
                //开启文件锁防止多人操作重复提交
                if( flock($fp,LOCK_EX) ) {
                    if($opt == 'Exec') {
                        //加锁防止重复提交
                        $res = M('Wttklist')->where(['id'=>$v['id'], 'df_lock'=>0])->setField('df_lock',1);
                        if(!$res) {
                            continue;
                        }
                    }
                    $v['money'] = round($v['money'],2);
                    $v['pay'] = json_encode($pfa_list);
                    $result = R('Payment/'.$code.'/Payment' , array($v));

                    if($result['status']==5) {
                        if($opt == 'Exec') {
                            M('Wttklist')->where(['id' => $v['id']])->setField('df_lock', 0);
                        }
                        $maps['sum'] = array('gt',0);
                        $maps['id'] = array('eq',$v['id']);

                        $ressss =  M('Wttklist')->where($maps)->find();
                        if($ressss){
                            $sum = $ressss['sum']-1;
                            M('Wttklist')->where(['id' => $v['id']])->save(['sum'=>$sum,'memo'=>$result['msg']]);
                        }

                        showError($result['msg']);
                    }
                    if(is_array($result) && $result['status'] <5){
                        $cost = $pfa_list['rate_type'] ? bcmul($v['tkmoney'], $pfa_list['cost_rate'], 2):$pfa_list['cost_rate'];
                        $data = [
                            'memo'       => $result['msg'],
                            'df_id'     => $pfa_list['id'],
                            'code'      => $pfa_list['code'],
                            'df_name'   => $pfa_list['title'],
                            'channel_mch_id' =>$pfa_list['mch_id'],
                            'cost_rate' => $pfa_list['cost_rate'],
                            'cost'      => $cost,
                            'rate_type'=>$pfa_list['rate_type'],
                        ];

                        $this->handle($v['id'], $result['status'], $data);
                        $Member     = M('Member');
                        $memberInfo = $Member->where(['id' => $v['userid']])->find();
                        if( $memberInfo['parentid']>1){
                            $parentid = $Member->where(array('id'=>$memberInfo['parentid']))->find();
                            if( $parentid['parentid'] >1){
                                $parentid = $Member->where(array('id'=>$parentid['parentid']))->find();
                            }
                            $bdmoney = $parentid['balance']+$v['sxfmoney'];
                            //提现时间
                            $time = date("Y-m-d H:i:s");
                            $mcDatas = [
                                "userid"     => $parentid['id'],
                                'ymoney'     => $parentid['balance'],
                                "money"      => $v['sxfmoney'],
                                'gmoney'     => $bdmoney,
                                "datetime"   => $time,
                                "transid"    => $v['orderid'],
                                "orderid"    => $v['orderid'],
                                "lx"         => 10,
                                'contentstr' => date("Y-m-d H:i:s") . '委托提现操作手续费',
                            ];
                            //组织上级收到的手续费流水
                            $Member->where(['id' => $parentid['id']])->save(['balance' => $bdmoney]);
                            M('Moneychange')->add($mcDatas);
                        }

                    }
                }

                if($opt == 'Exec') {
                    M('Wttklist')->where(['id' => $v['id']])->setField('df_lock', 0);
                }
                flock($fp,LOCK_UN);
            }
            fclose($fp);

            if($opt == 'Query') {
                showSuccess($result['msg']);
            } else {
                showSuccess('请求成功！');
            }
            exit;
        }
        if($opt == 'Exec') {
            session('admin_submit_df', null);
        }
        showError('只能同时请求15条代付数据！');
    }

    //定时任务-查询上游代付订单
    public function evenQuery(){
        $where = ['status'=>1];
        $wttk_lists = $this->selectOrder($where);
        foreach($wttk_lists as $k => $v){
            $file = APP_PATH . 'Payment/Controller/' . $v['code'] . 'Controller.class.php';
            if( is_file($file) ){
                $pfa_list = $this->findPaymentType($v['df_id']);
                $result = R('Payment/'.$v['code'].'/PaymentQuery', [$v, $pfa_list]);
                $result!==FALSE || showError('服务器请求失败！');
                if(is_array($result)){
                    $data = [
                        'msg'       => $result['msg'],
                        'df_id'     => $pfa_list['id'],
                        'code'      => $pfa_list['code'],
                        'df_name'   => $pfa_list['title'],
                    ];
                    $this->handle($v['id'], $result['status'], $data);
                }
            }
            sleep(3);
        }
    }

    //批量查询代付订单状态
    public function batchQuery(){
        //判断是否登录
        isLogin();
        $id = I('post.id', '');


        //获取要查询的订单id
        $id = explode(',', rtrim($id, ',') );

        if(empty($id)) {
            showError('请选择订单！');
        }
        $where['id'] = ['in', $id];
        $where['status'] = 1;
        $wttk_lists = M('Wttklist')->where($where)->select();
        if(empty($wttk_lists)) {
            showError('所选订单不能查询！');
        }
        $success = 0;
        foreach($wttk_lists as $k => $v){
            $file = APP_PATH . 'Payment/Controller/' . $v['code'] . 'Controller.class.php';
            if( file_exists($file) ){
                $pfa_list = M('PayForAnother')->where(['id'=>$v['df_id']])->find();
                if(empty($pfa_list)) {
                    continue;
                }
                $result = R('Payment/'.$v['code'].'/PaymentQuery', [$v, $pfa_list]);
                if(FALSE === $result) {
                    continue;
                } else {
                    if(is_array($result)){
                        $success++;
                        $data = [
                            'msg'       => $result['msg'],
                            'df_id'     => $pfa_list['id'],
                            'code'      => $pfa_list['code'],
                            'df_name'   => $pfa_list['title'],
                        ];
                        $this->handle($v['id'], $result['status'], $data);
                    }
                }
            } else {
                continue;
            }
        }
        if($success == 0) {
            showError('查询失败！');
        } else {
            showSuccess('查询成功,请在页面刷新后查看订单状态！');
        }
    }
}
