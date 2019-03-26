<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class YRTpayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;
    protected $private_key;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->private_key = '-----BEGIN PRIVATE KEY-----
MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAMSbYGpDkDjHbJeF
arwMRht30KJMoBcta3+71ZKlqdXKjK/GxV3tL7lj9ZM3xzvEm+br52M/iX3Dc7Nc
nRLRcpcVuoINzqzE+R16oAXTjbQQZ+xSeYg60K5ljmFRIyWZioCH+K4bAtxW2k0p
63g5zGwk6OePc80LmjWqeJ4Ht1lPAgMBAAECgYA55hgKs0LxtakBJkU8g9DRngNP
CInMyY9y2noW6bqOP0wXJ85Pzt3TFuDnLfH6Y/gVlTtbOwbehoS4OXn4ZL39xC5o
+dPoZjizROTXhTWLc1csExlSAjiFablA4tTsHpD3efmhA1q1uX1gHb/BlCyPgN4I
8svoXIVZNHfQYgGJ4QJBAOfuVMwMR1IBgEQo72rd3Do3GCezpm3dbl9FJfq9x/vP
jcmiu4Rl0pnccpAlBkDkyYPuq8M+boH/iSdYwB/SBK0CQQDZApkQxqoTm/cEYL67
SFH2TvkQi1hxLlxGLzMBo9PjD7TJdom8E0mrVeg/eU8q9lYaCHtZVCdMrA292FWv
NJlrAkBOCJAbPx5X5w1i4Wr8R70rERJdeUJLwK67+yX2IRhCDukjqE7zEtcy0Ury
WKKr/s4WR50eycigkHty85dgnWbZAkBmWeFTlj+VLBRfTjnnHv75StoRwYcfKpx+
xbgq59gB2eCMvInN2NMAKm51sYNzYefOM33p7dTzRprclXIkRoytAkB4MoYPYqNV
3IVkVYBhLLLXUAdto5va9LYDdlJtavgoppyn4mkaUKH/q2+7VqTQB2Sr8lLPLuwF
xLWETKayhtg+
-----END PRIVATE KEY-----
';
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

        $dataInfo['bankname'];
        $priKey = file_get_contents(BASE_PATH.'yrb.txt');
        $priKey= iconv('gb2312','utf-8',$priKey);
        $bank = explode("\n",$priKey);

        $bank_car =array();

        for ($i =1 ;$i< 1300;$i++){
            if($i<10){
               $rs =  'B000';
            }
            if($i<100 && $i>=10){
                $rs =  'B00';
            }
            if($i<1000 && $i>=100){
                $rs =  'B0';
            }if($i>1000 ){
                $rs =  'B';
            }
            $bank_car[] = $rs.$i;
        }

        $bankCode ='';
        $bankname = $dataInfo['bankname'];
        foreach ($bank as $k=>$v) {
            if (strpos($v, $bankname) !== false || strpos( $bankname,$v) !== false) {
                $bankCode = $bank_car[$k];
            }
        }

        $data['idMerchant'] = $payInfo['mch_id'] ;
        $data['typeChannel'] = $payInfo['appid'] ;
        $data['version'] = '1.0' ;
        $data['typeSign'] = 'RSA' ;
        $data['reqNo'] = $dataInfo['orderid']  ;
        $data['reqTime'] = date('YmdHis',time());//时间戳格式


        $data['orderNumber'] = $dataInfo['orderid'] ;//商户代付单号
        $data['dtPay'] = date('Ymd',time());//时间戳格式
        $data['receiverCardNo'] = $dataInfo['banknumber'];//银行账号
        $data['receiverName'] = $dataInfo['bankfullname'];
        $data['receiverType'] = '01';
        $data['receiverCurrency'] = 'CNY';
        $data['bankName'] = $dataInfo['bankfullname'];
        $data['bankCode'] = $bankCode;
        $data['amount'] =  $dataInfo['money'];//交易金额
        if($data['total_fee']>50000){
            $data['remark'] = '申请代付金额为：'.$data['total_fee'];
        }
        $orgData =  "[idMerchant=".$data['idMerchant']."&typeChannel=" .$data['typeChannel']. "&version=" .$data['version']. "&typeSign="
            .$data['typeSign']. "&reqNo=".$data['reqNo']. "&reqTime=" .$data['reqTime']. "&orderNumber=" .$data['orderNumber'].  "&dtPay="
            .$data['dtPay'].  "&receiverCardNo=" .$data['receiverCardNo'].  "&receiverName=" .$data['receiverName'].  "&receiverType="
            .$data['receiverType']. "&receiverCurrency=" .$data['receiverCurrency'].  "&bankName=" .$data['bankName'].  "&bankCode=".$data['bankCode'].  "&amount=" .$data['amount'].  "]";

        $data1 = strtoupper(md5($orgData));

        $pi_key =  openssl_pkey_get_private($this->private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        openssl_sign($data1,$encrypted,$pi_key,OPENSSL_ALGO_SHA1);//私钥加密
        $encrypted = base64_encode($encrypted);
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $data['sign'] =$encrypted;
        $data['order_name'] =$dataInfo['orderid'].'提现';
        $data['bankAccountType '] ='01';
        $data['notifyUrl'] = $this->_site . 'Payment_YRTpayDf_notifyurl.html';//异步回调地址
        $jsonArray =$this-> httpRequest($payInfo['exec_gateway'], $data);
        if($jsonArray['retCode'] == '0000' || $jsonArray['retCode']=='1001'){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['retMsg'],'status'=>5);
        }
    }
    function notifyurl(){
        $data['appid']   = trim($_REQUEST['appid']);
        $data['mch_id']   = trim($_REQUEST['mch_id']);
        $data['sign_type']   = trim($_REQUEST['sign_type']);
        $sign   = trim($_REQUEST['sign']);
        $data['billno']   = trim($_REQUEST['billno']);
        $data['out_trade_no']   = trim($_REQUEST['out_trade_no']);
        $data['total_fee']   = trim($_REQUEST['total_fee']);
        $data['fee']   = trim($_REQUEST['fee']);
        $data['pay_time']   = trim($_REQUEST['pay_time']);
        $data['trade_status']   = $_REQUEST['trade_status'];

        if(empty($data['out_trade_no']) ||empty( $data['trade_status']) ){
            die('fail');
        }

        if( $data['trade_status']  == 1) {
            $status = 2;
        }
        if($data['trade_status']  == 3){
            $status =3;
        }
        $where = ['orderid' =>    $data['out_trade_no'] ];
        $lists = M('Wttklist')->where($where)->find();

        if(empty($lists)){
            die('fail');
        }
        if(!empty($lists['out_trade_no'])){
            $where = ['out_trade_no' => $lists['out_trade_no']];
            $list = M('df_api_order')->where($where)->find();

            if(!$list){
                die('fail');
            }
            $arra['status'] = $status;
            $arra['orderid'] = $lists['out_trade_no'];

            if($list['notifyurl']){
                $url= $list['notifyurl'] . "?".http_build_query($arra);
                file_put_contents('huidiao.txt',"order_id=".$url ."\r\n\r\n",FILE_APPEND);
                $ch = curl_init();
                $timeout = 10;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $contents = curl_exec($ch);
            }
        }
        $this->editwtStatus($lists['id'],$status,$lists['userid'],$lists['tkmoney']+$lists['sxfmoney'],$lists['orderid'],0);
        die('SUCCESS');
    }


    protected function _createSign($data, $key)
    {
        $sign          = '';
        ksort($data);
        foreach ($data as $k => $vo) {
            if(!empty($vo)){
                $sign .= $k.'='.$vo.'&';
            }
        }
        return strtoupper(MD5($sign.'key='.$key));
    }


    //脚本自动处理查询代付订单
    function Query(){

        $Wttklist = M("Wttklist");
        $map['code'] = 'YRTpayDf';
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
        $data['idMerchant'] = $pfa_list['mch_id'] ;
        $data['typeChannel'] = $pfa_list['appid'] ;
        $data['version'] = '1.0' ;
        $data['typeSign'] = 'RSA' ;
        $data['reqNo'] = time()  ;
        $data['reqTime'] = date('YmdHis',time());//时间戳格式
        $data['orderNumber'] = $v['orderid'];//时间戳格式

        $orgData =  "[idMerchant=".$data['idMerchant']."&typeChannel=" .$data['typeChannel']. "&version=" .$data['version']. "&typeSign="
            .$data['typeSign']. "&reqNo=".$data['reqNo']. "&reqTime=" .$data['reqTime']. "&orderNumber=" .$data['orderNumber']. "]";
        $data1 = strtoupper(md5($orgData));

        $pi_key =  openssl_pkey_get_private($this->private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id

        openssl_sign($data1,$encrypted,$pi_key,OPENSSL_ALGO_SHA1);//私钥加密
        $encrypted = base64_encode($encrypted);
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $data['sign'] =$encrypted;
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $re= $this->httpRequest($pfa_list['query_gateway'],$data);

        file_put_contents('Q_PaymentQuery.txt',"order_id=".$v['orderid'].'--'.date('YmdHis',time())."\r\n\r\n",FILE_APPEND);

        if($re['retCode'] == 0000  || $re['retCode'] == '0000'){
           if($re['orderStatus']==00 ||$re['orderStatus']=='00' ){
              $re['pay_status'] = 2;
           }
           if($re['orderStatus']==01 || $re['orderStatus']=='01'){
              $re['pay_status'] = 1;
           }
           if($re['orderStatus']==02 || $re['orderStatus']==03 || $re['orderStatus']=='02'||$re['orderStatus']=='03'){
              $re['pay_status'] = 3;
           }
          $this->editwtStatus($v['id'],$re['pay_status'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);

           M("Wttklist")->where(['orderid' => $v['orderid']])->save(['memo' =>$re['errorMsg']]);
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

    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array() )
    {
        $data = json_encode($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        curl_close($curl);
        $result =  json_decode($result,true);
        return $result;

    }
    function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $data['idMerchant'] = $Channel['mch_id'] ;
        $data['typeChannel'] = $Channel['appid'] ;
        $data['version'] = '1.0' ;
        $data['typeSign'] = 'RSA' ;
        $data['reqNo'] = time()  ;
        $data['reqTime'] = date('YmdHis',time());//时间戳格式

        $orgData =  "[idMerchant=".$data['idMerchant']."&typeChannel=" .$data['typeChannel']. "&version=" .$data['version']. "&typeSign="
            .$data['typeSign']. "&reqNo=".$data['reqNo']. "&reqTime=" .$data['reqTime']. "]";
        $data1 = strtoupper(md5($orgData));

        $pi_key =  openssl_pkey_get_private($this->private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id

        openssl_sign($data1,$encrypted,$pi_key,OPENSSL_ALGO_SHA1);//私钥加密
        $encrypted = base64_encode($encrypted);
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $data['sign'] =$encrypted;
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $re= $this->httpRequest($Channel["query_gateway_money"] ,$data,'post');

        if($re['retCode'] != '0000' ){
            return  0;
        }
        return $re['accountBalance'];
    }
}