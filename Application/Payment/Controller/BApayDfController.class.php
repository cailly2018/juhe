<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class BApayDfController extends Controller
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
        $this->types = 'BApayDf';
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


        $data['mchid'] =  $payInfo['mch_id'];//商户号
        $data['submchid'] =  $payInfo['mch_id'];//商户号
        $data['orderno'] =  $dataInfo['orderid'] ;//商户代付单号
        $data['amount'] = (string)$dataInfo['money'];//交易金额
        $data['waytype'] = '';//使用的支付通道，请您登录代理商后台查看自己可以使用的通道，将通道代码填入此处，此处应该填入代付通道
        $data['notifyurl'] = $this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $data['payname'] =$dataInfo['bankfullname'];//收款人账户名
        $data['cardno'] = $dataInfo['banknumber'];//银行账号
        $data['bankno'] = $dataInfo['banknumber'];//银行账号
        $data['bankname'] =$dataInfo['bankname'];//银行名称
        $data['city'] =$dataInfo['bankname'];//银行名称
        $data['typecode'] =$dataInfo['bankname'];//银行名称

        $data['sign'] = $this->_createSign($data, $payInfo['signkey']);
        $jsonArray = $this->request($payInfo['exec_gateway'],$data,'get');

        if($jsonArray['result'] == 'success' ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['info'],'status'=>5);
        }
    }
    function notifyurl(){


    /*    "code": 1,
           "msg": "获取支付参数成功",
           "data": {
            "totalamount": "200.00",
               "amount": "194.80",
               "agentorderno": "1535521750",
               "orderno": "391csallipayQR2531535964797",
               "paystatus": 0|1|2//0是代付失败，1是代付完成，2是代付已受理
           },
        */


        $orderid   = $_REQUEST['order_num'];
        $status    = trim($_REQUEST['status']);
        $p = json_encode($_POST);
        file_put_contents('ch.txt',"order_id=".$p ."\r\n\r\n",FILE_APPEND);
        //已退票
        if($status == '成功') {
            $status = 2;
        }
        if($status == '失败' || $status = '已退票'){
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


    protected function _createSign($data,$key)
    {
        $data['dog_sk']= $key;
        $sign          = '';
        krsort($data);
        foreach ($data as $k => $vo) {
            $sign .= $vo;
        }
        return strtoupper(md5($sign));
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



    /**
     * @desc 默认post请求
     * @access public
     * @return array
     */
    public function request( $url, $params=array(), $method='post' )
    {

        return $this->httpRequest( $url, $params, $method );
    }
    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array(), $method = 'post', $connectTimeout=5, $readTimeout=10 )
    {
        if( !function_exists('curl_init') )
            return 901 ;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $connectTimeout + $readTimeout );
        curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'API PHP5 Client (curl) ' . phpversion() );

        if( is_array($params) || is_object($params) )
            $query_string = http_build_query($params);
        else
            $query_string = $params;

        $method = strtolower($method);
        switch( $method )
        {
            case 'get':
                if( false === strstr( $url, '?' ) )
                    $url = $url.'?'.$query_string;
                else
                    $url = $url.'&'.$query_string;
                curl_setopt($ch, CURLOPT_URL, $url );
                break;
            case 'post':
                curl_setopt( $ch, CURLOPT_URL, $url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
                break;
            default:
                return  900 ;
                break;
        }
        $starttime = microtime(true);
        $result = curl_exec($ch);
        $endtime = microtime(true);
        $this->exectime = $endtime-$starttime;
        $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_error = curl_error($ch);
        curl_close($ch);
        if( $curl_error )
        {
            return $curl_error;
        }
        if( $httpcode != 200 )
        {
            return $httpcode;
        }
        return json_decode($result,true);
    }

    function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'service_id'	=> 'tf56pay.enterprise.queryEnterpriseAccountBanlance',//商户编号
            'appid'	=> $Channel['mch_id'],//商户编号
            'tf_timestamp'	=> date('YmdHis',time()),//时间戳格式
            'sign_type'	=>  'MD5',//商户生成签名字符串所使用的签名算法类型
            'version'	=>  '1.0',
            'accountnumber'	=> '8800010291609',//传化支付账号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['tf_sign'] =$this->_createSign($arraystr,  $Channel["signkey"]);
        $re= $this->request($Channel["query_gateway_money"] ,$arraystr,'post');

        if($re['result'] == 'error'){
            return  0;
        }
        return $re['data']['balance'];
    }
}