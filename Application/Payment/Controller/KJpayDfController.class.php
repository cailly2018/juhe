<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class KJpayDfController extends Controller
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
        $data['versionNo'] = 1 ;//接口版本号
        $data['mchNo'] = $payInfo['mch_id'];//机构号
        $data['price'] = sprintf("%.2f",$dataInfo['money']);//交易金额
        $data['orderDate'] = date('YmdHis',time());
        $data['tradeNo'] =$dataInfo['orderid'];
        $data['notifyUrl'] = $this->_site . 'Payment_KJpayDf_notifyurl.html';//异步回调地址
        $data['mode'] = 'S0';
        $data['accCardNo'] = $dataInfo['banknumber'];//银行账号
        $data['accName'] =$dataInfo['bankfullname'];
        $data['purpose'] ='test';

        $plainReqPayload  =  json_encode($data, JSON_UNESCAPED_UNICODE);
        $dats['mchNo'] = $payInfo["mch_id"];
        $dats['payload'] = $this->encrypt($plainReqPayload,$payInfo['signkey']);
        $dats['sign'] = strtoupper(md5($dats['payload'].$payInfo['appsecret']));
        $result =  $this-> doPost($payInfo['exec_gateway'], $dats);

        $result = json_decode($result, true);

        if($result['code'] == 0 ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$result['message'],'status'=>5);
        }
    }
    function notifyurl(){
        $xml = file_get_contents('php://input');
        $result = json_decode($xml, true);
        $another = M('pay_for_another')->where(['code' =>'KJpayDf' ])->find();
        $rrd = $this->decrypt($result['payload'], $another['signkey']);
        $result = json_decode($rrd, true);

        if(empty($result['tradeNo']) ){
            die('fail');
        }
        if( $result['status']  == 00) {
            $status = 2;
        }
        if($result['status']  == 02){
            $status =3;
        }
        $where = ['orderid' =>    $result['tradeNo'] ];
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
        die('success');
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
        $map['code'] = 'KJpayDf';
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
        $arraystr = array(
            'versionNo'=>  1,
            'mchNo'	=>  $pfa_list['mch_id'],//商户编号
            'tradeNo'=> $v['orderid'],
        );
        $plainReqPayload  =  json_encode($arraystr, JSON_UNESCAPED_UNICODE);
        $dats['mchNo'] = $pfa_list["mch_id"];
        $dats['payload'] = $this->encrypt($plainReqPayload,$pfa_list['signkey']);
        $dats['sign'] = strtoupper(md5($dats['payload'].$pfa_list['appsecret']));
        $result =  $this-> doPost($pfa_list["query_gateway"], $dats);
        $result = json_decode($result, true);
        $rrd = $this->decrypt($result['payload'], $pfa_list['signkey']);
        $re = json_decode($rrd, true);

        if($re['status']=='00' ){
          $re['pay_status'] = 2;
        }
        if($re['status']=='01' || $re['status']=='09'){
          $re['pay_status'] = 1;
        }
        if($re['status']=='02'){
          $re['pay_status'] = 3;
        }
       $this->editwtStatus($v['id'],$re['pay_status'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);

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

    //解密
    public static function decrypt($data, $key) {
        $data = base64_decode($data);
        $iv='0102030405060708';
        $decrypted = openssl_decrypt($data,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
        return  $decrypted;
    }
    //加密
    public static function encrypt($data, $key) {
        $iv='0102030405060708';
        $data =  urldecode($data);
        $data =openssl_encrypt($data, 'AES-128-CBC',$key,OPENSSL_RAW_DATA, $iv);
        return base64_encode($data);
    }
    // 请求数据
    function doPost($contextPath, $data) {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $this->url . $contextPath );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_SSLVERSION, 1 );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
            'Content-type:application/json;charset=UTF-8'
        ) );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $resp = curl_exec ( $ch );
        return $resp;
    }

    function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'versionNo'=>  1,//商户订单号
            'mchNo'	=>  $Channel["mch_id"],//商户编号
        );

        $plainReqPayload  =  json_encode($arraystr, JSON_UNESCAPED_UNICODE);
        $dats['mchNo'] = $Channel["mch_id"];
        $dats['payload'] = $this->encrypt($plainReqPayload,$Channel['signkey']);

        $dats['sign'] = strtoupper(md5($dats['payload'].$Channel['appsecret']));
        $result =  $this-> doPost($Channel["query_gateway_money"], $dats);
        $result = json_decode($result, true);
        $rrd = $this->decrypt($result['payload'], $Channel['signkey']);
        $result = json_decode($rrd, true);

        if(!$result){
            return  0;
        }
        $money = $result['creditLines'] - $result['curOutAmt'];
         if($money>0){
             $money = $money/100;
         }
        return $money;
    }
}