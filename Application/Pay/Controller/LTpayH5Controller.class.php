<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class LTpayH5Controller extends PayController{

    protected $types;
    protected $reapalPublicKey;
    public function __construct(){
        parent::__construct();
        $this->types = 'LTpayH5';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $bankname = I("request.bankname");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（雷霆支付宝H5）',
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
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        date_default_timezone_set("PRC");

        $datapay['Merch_Id'] =  $return["mch_id"];
        $datapay['Version'] = 'V1.0.0';
        $datapay['Char_Type'] = 'UTF-8';
        $datapay['Sign_Type'] = 'MD5';
        $datapay['Cryp_Type'] = 'RSA';

        $pay_orderid = $return['out_trade_id'];
        $arraystr = array(
            'Merch_Order'=>  $pay_orderid ,//商户订单号
            'Subject'  =>  '实物_数码家电',
            'Total_Amount'	=>  $return["amount"]*100,//订单金额,
            'IP_Adr'=> get_client_ip(),
            'Pay_Type'=> '100002',
            'Acc_Type'=> 'D0',
            'Notify_Url'	=> $notifyurl,

        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['Sign'] =$this->_createSign($arraystr,  $return["signkey"]);
        $datapay['Data'] =$this->bank_public_encrypt(json_encode($arraystr));

        $result= $this->CurlPost($return["gateway"].'api/ltpay',$datapay);
        if($result['result_code'] == 0000){
            $re['data']['pay_orderid'] = $pay_orderid;
            $this->orderadd1($re['data']);
            if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){

                header("Location: ".$result ['H5_Url']);
            }else{
                $data =  array('code'=>0, 'msg'=>'调起成功', 'payurl'=> $result ['H5_Url']);
            }
        }else{
            $data =  array('code'=>1, 'msg'=>'调起失败', 'payurl'=> '');
        }

        $this->showmessage('',$data,1);
        return;

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

    protected function _createSign($data, $keys)
    {
        ksort($data);
        $company_sign = '';
        foreach($data as $key=>$val){
            $company_sign .= $key.'='.$val.'&';
        }
        $company_sign .= 'key='.$keys;

        return strtoupper(md5($company_sign));

    }

    //pkcs8公钥加密
    function bank_public_encrypt($data){
        $pubKey = file_get_contents( BASE_PATH.'LT/s/rsa_public_key_1024.pem');
        $res = openssl_get_publickey($pubKey);
        $info = openssl_pkey_get_details($res);
        $num = $info['bits'];
        $plainData = str_split($data, $num / 8 - 11);
        $encrypted = '';
        foreach ($plainData as $chunk) {
            $str = '';
            $encryption = openssl_public_encrypt($chunk, $str, $pubKey, OPENSSL_PKCS1_PADDING);
            if ($encryption === false) {
                return false;
            }
            $encrypted .= $str;
        }
        openssl_free_key($res);
        $encrypt = base64_encode($encrypted);
        return $encrypt;
    }
    //解密
    function bank_private_decrypt($data){
        $priKey = file_get_contents(BASE_PATH.'LT/rsa_private_key_2048.pem');
        $res = openssl_get_privatekey($priKey);
        $info = openssl_pkey_get_details($res);
        $num = $info['bits'];
        $decrypted = '';
        $plainData = str_split(base64_decode($data), $num / 8);
        foreach ($plainData as $chunk) {
            $str = '';
            $decryption = openssl_private_decrypt($chunk, $str, $res, OPENSSL_PKCS1_PADDING);
            if ($decryption === false) {
                return false;
            }
            $decrypted .= $str;
        }
        openssl_free_key($res);
        return $decrypted;
    }

    protected 	function CurlPost($url,$data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);

        curl_close($ch);
        return json_decode($result,true);

    }

        // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $data = $_POST;
        $re =   $this-> bank_private_decrypt($data['Data']);

        $res = json_decode($re,true);
        if($res['Err_Code'] == 0){
            if($res['Result']=='SUCCESS'){
                $this->EditMoney($res['Merch_Order'], $this->types, 0);
                die('SUCCESS');
            }else{
                die('fail');
            }
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
        $datapay['Merch_Id'] =  $channel_account["mch_id"];
        $datapay['Version'] = 'V1.0.0';
        $datapay['Char_Type'] = 'UTF-8';
        $datapay['Sign_Type'] = 'MD5';
        $datapay['Cryp_Type'] = 'RSA';
        $arraystr = array(
            'Settle_Date'	=> date('Ymd',time()),//商户编号
            'Merch_Order'=> $order['pay_orderid'],
            'Pay_Type'=> '100002',
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['Sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);
        $datapay['Data'] =$this->bank_public_encrypt(json_encode($arraystr));


        $result= $this->CurlPost($Channel["gateway"].'api/OrderQuery' ,$datapay);

        if($result['Err_Code']==0){
            //成功/关闭/已退款/失败
            if($result['Result']== 'SUCCESS'  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }
    function  callbackurl(){
        echo '支付成功';die;
        /*$orderid       = trim($_REQUEST['orderid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }*/

    }

}