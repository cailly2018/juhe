<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class GMpayDfController extends Controller
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
        $this->types = 'GMpayDf';
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

        $data['outTradeNo'] =  $dataInfo['orderid'] ;//商户代付单号
        $data['notifyUrl'] = $this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $data['bankCardNo'] =$dataInfo['banknumber'];//收款人账户名
        $data['bankCardHolder'] =$dataInfo['bankfullname'];//收款人账户名
        $data['amount'] = $dataInfo['money'];//交易金额
        $data['summary'] = $payInfo['mch_id'];
        $data['bankCode'] = '312312';

        $data['sign'] = $this->_createSign($data, $payInfo['signkey']);
        $jsonArray = $this->httpRequest($payInfo['exec_gateway'],$data);

        if($jsonArray['returnCode'] == 0 ){
            if($jsonArray['resultCode']==0){
                return  array('msg'=>'success','status'=>1);
            }else{
                return array('msg'=>$jsonArray['info'],'status'=>5);
            }
        }else{
            return array('msg'=>$jsonArray['info'],'status'=>5);
        }
    }
    function notifyurl(){

        header('Content-Type:text/html;charset=utf-8');
        $fileContent = file_get_contents("php://input");
        $data = json_decode($fileContent,true);
        if($data['returnCode'] !=0){
            die('FAIL');
        }
        if($data['resultCode'] !=0){
            die('FAIL');
        }
        $orderid   = $data['outTradeNo'];
        $status    =  $data['status'];

        //已退票
        if($status == 'SUCCESS') {
            $status = 2;
        }
        if($status == 'FAIL'){
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
    }

    //脚本自动处理查询代付订单
    function Query(){

        $Wttklist = M("Wttklist");
        $map['code'] =  $this->types;
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

          $data['agentType'] = '00';
          $data['mchId'] = $pfa_list['mch_id'];
          $data['outTradeNo'] = $v['orderid'];

          $data['sign'] = $this->_createSign($data,$pfa_list['signkey']);
          $re = $this->httpRequest( $pfa_list['query_gateway'],$data);
          file_put_contents('Q_PaymentQuery.txt',"order_id=".$v['orderid'].'--'.date('YmdHis',time())."\r\n\r\n",FILE_APPEND);

          if($re['returnCode'] == '0' ||$re['returnCode'] == 0 ) {
              if ($re['resultCode'] == '0' || $re['resultCode'] == 0) {

                  if ($re['status'] == 'SUCCESS') {
                      $re['pay_status'] = 2;
                  }
                  if ($re['status'] == 'PENDING' ||$re['status'] == 'WAITING') {
                      $re['pay_status'] = 1;
                  }
                  if ($re['status'] == 'FAIL') {
                      $re['pay_status'] = 3;
                  }
                  $this->editwtStatus($v['id'], $re['pay_status'], $v['userid'], $v['tkmoney'] + $v['sxfmoney'], $v['orderid'], $s);
              }
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
             /*  if( $memberInfo['parentid']>1){
                    $parentid = $Member->where(array('id'=>$memberInfo['parentid']))->find();
                    if( $parentid['parentid'] >1){
                        $parentid = $Member->where(array('id'=>$parentid['parentid']))->find();
                    }
                    $bdmoney = $parentid['balance']+$withdraw['sxfmoney'];
                    //提现时间
                    $time = date("Y-m-d H:i:s");
                    $mcDatas = [
                        "userid"     => $parentid['id'],
                        'ymoney'     => $parentid['balance'],
                        "money"      => $withdraw['sxfmoney'],
                        'gmoney'     => $bdmoney,
                        "datetime"   => $time,
                        "transid"    => $withdraw['orderid'],
                        "orderid"    => $withdraw['orderid'],
                        "lx"         => 10,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作手续费',
                    ];
                    //组织上级收到的手续费流水
                    $sx= $Member->where(['id' => $parentid['id']])->save(['balance' => $bdmoney]);
                    $sxls = M('Moneychange')->add($mcDatas);
                }*/
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
        $arraystr = array(

            'mchId'	=> $Channel['mch_id'],//商户编号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] = $this->_createSign($arraystr, $Channel['signkey']);
        $re= $this->httpRequest($Channel["query_gateway_money"] ,$arraystr,'post');
        if($re['resultCode'] != 0){
            return  0;
        }
        return $re['data']['balanceAmt'];
    }
    protected function _createSign ($data,$apiKey) {
        global $appSecret;
        $sign = $appSecret;
        ksort($data);
        foreach ($data as $key => $val) {
            $sign .= $key.'='.$val.'&';
        }
        $sign = strtoupper(MD5($sign.'key='.$apiKey));
        return $sign;
    }

    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array() )
    {
        if( !function_exists('curl_init') )
            return 901 ;
        $data_json = json_encode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response =  json_decode($response,true);
        return $response;
    }

}