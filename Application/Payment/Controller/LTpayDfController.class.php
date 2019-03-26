<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class LTpayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
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


        $bank = array('建设银行','农业银行','工商银行','中国银行','浦发银行','光大银行','平安银行','兴业银行','邮政储蓄银行',
            '中信银行', '华夏银行', '招商银行', '广发银行', '北京银行', '上海银行', '民生银行', '交通银行');
        $bank_car =array('CCB','ABC','ICBC','BOC','SPDB','CEB','PINGAN','CIB','PSBC','CNCB','HXB','CMB','CGB','BCCB','SHB','CMBC','BCM');

        $BankType ='';
        $bankname = $dataInfo['bankname'];

        foreach ($bank as $k=>$v) {
            if (strpos($v, $bankname) !== false || strpos( $bankname,$v) !== false) {
                $BankType = $bank_car[$k];
            }
        }

        $datapay['Merch_Id'] =  $payInfo['mch_id'];
        $datapay['Version'] = 'V1.0.0';
        $datapay['Char_Type'] = 'UTF-8';
        $datapay['Sign_Type'] = 'MD5';
        $datapay['Cryp_Type'] = 'RSA';

        $arraystr = array(
            'pay_order_id'	=> $dataInfo['orderid'] ,//商户编号
            'pay_secret'=> $payInfo['appsecret'],
            'pay_card'=> $dataInfo['banknumber'],
            'pay_name'=> $dataInfo['bankfullname'],
            'pay_bankname'=>$dataInfo['bankname'],
            'pay_money'=> $dataInfo['money']*100,
            'pay_bankcode'=>$BankType,
            'return_url'=>$this->_site . 'Payment_LTpayDf_notifyurl.html'
        );
        
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $payInfo["signkey"]);
        $datapay['Data'] =$this->bank_public_encrypt(json_encode($arraystr));

        $result= $this->CurlPost($payInfo['exec_gateway'] ,$datapay);


        if($result['resCode'] !='FF' || $result['resCode'] =='00'  ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$result['msg'],'status'=>5);
        }
    }
    function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $data = $_POST;
        $re =   $this-> bank_private_decrypt($data['Data']);
        $res = json_decode($re,true);
        if($res['Err_Code'] != 0) {
            die("fail");
        }

        if($res['result_code']=='PAY_SUCCESS'){
            $status = 2;
        }
        if($res['result_code']=='PAY_FAIL'){
            $status = 3;
        }

        $where = ['orderid' => $res['pay_order_id']];
        $lists = M('Wttklist')->where($where)->find();

        if(empty($lists)){
            die("fail");
        }
        if(!empty($lists['out_trade_no'])){
            $where = ['out_trade_no' => $lists['out_trade_no']];
            $list = M('df_api_order')->where($where)->find();

            if(!$list){
                die("fail");
            }
            $arra['status'] = $status;
            $arra['orderid'] = $lists['out_trade_no'];
            $apikey = M("Member")->where("id=" . $lists['userid'])->getField("apikey");

            $arra['sign'] = $this->createSign( $arra,$apikey) ;
            if($list['notifyurl']){
                $url= $list['notifyurl'] . "?".http_build_query($arra);

                $ch = curl_init();
                $timeout = 10;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $contents = curl_exec($ch);
            }
        }

        $this->editwtStatus($lists['id'],$status,$lists['userid'],$lists['tkmoney']+$lists['sxfmoney'],$lists['orderid'],0);
        die("SUCCESS");
    }
    protected function createSign( $list,$Md5key)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            if (!empty($val) && $key != 'pay_md5sign') {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        file_put_contents('5key.txt',$md5str . "key=" . $Md5key."\r\n\r\n",FILE_APPEND);

        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        return $sign;
    }


    //脚本自动处理查询代付订单
    function Query(){
        $Wttklist = M("Wttklist");
        $map['code'] = 'LTpayDf';
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
        $datapay['Merch_Id'] =  $pfa_list["mch_id"];
        $datapay['Version'] = 'V1.0.0';
        $datapay['Char_Type'] = 'UTF-8';
        $datapay['Sign_Type'] = 'MD5';
        $datapay['Cryp_Type'] = 'RSA';

        $arraystr['Pay_Order_Id'] = $v['orderid'];
        $arraystr['Sign'] =$this->_createSign($arraystr,  $pfa_list["signkey"]);
        $datapay['Data'] =$this->bank_public_encrypt(json_encode($arraystr));

        $result= $this->CurlPost($pfa_list['query_gateway'],$datapay);
        if($result['Err_Code']==0){
              $re['pay_status'] = 3;
              if($result['Result']=='SUCCESS'){
                  $re['pay_status'] = 2;
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
        $Channel  = $_REQUEST['channel'];

        $datapay['Merch_Id'] =  $Channel["mch_id"];
        $datapay['Version'] = 'V1.0.0';
        $datapay['Char_Type'] = 'UTF-8';
        $datapay['Sign_Type'] = 'MD5';
        $datapay['Cryp_Type'] = 'RSA';

        $arraystr = array(
            'Sel_Order'	=>   date('Ymd',time()),//商户编号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['Sign'] =$this->_createSign($arraystr,  $Channel["signkey"]);
        $datapay['Data'] =$this->bank_public_encrypt(json_encode($arraystr));
        $result= $this->CurlPost($Channel['query_gateway_money'],$datapay);

        if($result['Result'] != 'SUCCESS' ){
            return  0;
        }
        return $result['Balance'];
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
}