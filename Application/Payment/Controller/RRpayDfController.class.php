<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class RRpayDfController extends Controller
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
        $this->types = 'RRpayDf';
    }

    function test(){
        $da['userOrderId'] = '20190410230112000';
        $da = json_encode($da);

        echo 'test:'.$this->encrypt($da,'');

        echo '<br>';
        echo '<br>';
        $arraystr = array(
            'cmd'	=> 'balance',//商户编号
        );
        $arr['companyId'] =  1297;
        $arraystr = json_encode($arraystr);
        $arr['data'] = $this->encrypt($arraystr, '8CC69FFD31CA8B42');
        $rr = json_decode('{"companyId":1297,"data":"oqbzmkTPjb9G0K5wHed0b0uCE3wCjVLq26DOApg1oGDn3x9+6ZVCGH6i3EEXMr5Mj4lDLXg+SfN5xGSFdinblaMJ1AX+/E5toKa3DpEwJec="}',true);

        $re= $this->httpRequest('http://juhe.nutbe.cn/Payment_RRpayDf_notifyurl.html' ,$rr,1);



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
        $data['userOrderId'] =  $dataInfo['orderid'] ;//商户代付单号
        $data['syncUrl'] = $this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $data['cardHolder'] =$dataInfo['bankfullname'];//收款人账户名
        $data['cardNo'] =$dataInfo['banknumber'];//收款人账户名
        $data['money'] = $dataInfo['money']*100;//交易金额

        $data1['companyId'] = $payInfo['mch_id'];
        $data = json_encode($data);
        $data1['data'] = $this->encrypt($data, $payInfo['signkey']);

        $jsonArray = $this->httpRequest($payInfo['exec_gateway'],$data1);
        if($jsonArray['status'] == 0 ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['msg'],'status'=>5);
        }
    }
    public function notifyurl(){

        header('Content-Type:text/html;charset=utf-8');
        $fileContent = file_get_contents("php://input");
      //  $fileContent =  '{"companyId":1297,"data":"oqbzmkTPjb9G0K5wHed0b0uCE3wCjVLq26DOApg1oGDn3x9+6ZVCGH6i3EEXMr5Mj4lDLXg+SfN5xGSFdinblaMJ1AX+\/E5toKa3DpEwJec="}';
        file_put_contents('ckurl.txt',$fileContent ."\r\n\r\n",FILE_APPEND);
        $data = json_decode($fileContent,true);

        if(!isset($data['companyId'])){
           die('FAIL');
        }
        $where1['code'] = $this->types;
        $where1['mch_id'] =$data['companyId'];
        $payfor = M('pay_for_another')->where($where1)->find();

        if(!$payfor){
            die('FAIL');
        }
        $re = $this->decrypt($data['data'],$payfor['signkey']);
        $re = json_decode($re,true);

        $orderid   = $re['userOrderId'];
        $status1    =  $re['orderStatus'];

        //已退票
        if($status1 == 0) {
            $status = 2;
        }
        if($status1 == 2){
            $status =3;
        }
        $where = ['orderid' => $orderid];
        $lists = M('Wttklist')->where($where)->find();


        if(empty($lists)){
            die("FAIL");
        }
        if(!empty($lists['out_trade_no'])){
            $where = ['out_trade_no' => $lists['out_trade_no']];
            $list = M('df_api_order')->where($where)->find();

            if($list){
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
        }

        $this->editwtStatus($lists['id'],$status,$lists['userid'],$lists['tkmoney']+$lists['sxfmoney'],$lists['orderid'],0);
        die("success");
    }

    //开始脚本自动处理查询代付订单
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
    public function PaymentQuery($v, $pfa_list ,$s=1)
    {
          $data['userOrderId'] = $v['orderid'];
          $arr['companyId'] =  $pfa_list['mch_id'];
          $arr['data'] = $this->encrypt(json_encode($data),$pfa_list['signkey']);
          $re = $this->httpRequest( $pfa_list['query_gateway'],$arr);

          if($re['status'] == '0' ||$re['status'] == 0 ) {
              if ($re['orderStatus'] == 0) {
                  $re['pay_status'] = 2;
              }
              if ($re['orderStatus'] ==  -1 ||$re['orderStatus'] ==  2) {
                  $re['pay_status'] = 1;
              }
              if ($re['orderStatus'] == 2 ||re['orderStatus'] == 3  ) {
                  $re['pay_status'] = 3;
              }
              $this->editwtStatus($v['id'], $re['pay_status'], $v['userid'], $v['tkmoney'] + $v['sxfmoney'], $v['orderid'], $s);
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
    //结束

    //余额查询
    public function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'cmd'	=> 'balance',//商户编号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arr['companyId'] =  $Channel['mch_id'];
        $arraystr = json_encode($arraystr);
        $arr['data'] = $this->encrypt($arraystr, $Channel['signkey']);

        $re= $this->httpRequest($Channel["query_gateway_money"] ,$arr);

        if($re['status'] != 0){
            return  0;
        }
        if($re['balance']>0){
            $re['balance'] = $re['balance']/100;
        }
        return $re['balance'];
    }

     //解密
    public static function decrypt($data, $key) {
        $encrypted = base64_decode($data);
        return openssl_decrypt($encrypted, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
    }
    //加密
    public static function encrypt($data, $key) {
        $data = openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }


    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array() ,$v=0)
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
        if($v==0){
            $response =  json_decode($response,true);
        }

        return $response;
    }

}