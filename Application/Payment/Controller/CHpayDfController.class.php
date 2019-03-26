<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class CHpayDfController extends Controller
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

        $data['service_id'] = 'tf56enterprise.enterprise.payForCustomer';//商户号
        $data['appid'] = $payInfo['mch_id'];//商户号
        $data['tf_timestamp'] =  date('YmdHis',time());//时间戳格式
        $data['sign_type'] = 'MD5';//时间戳格式
        $data['tf_sign'] = '';//签名
        $data['businessnumber'] = $dataInfo['orderid'] ;//商户代付单号
        $data['subject'] = '电子产品(笔记本)' ;//商户代付单号
        $data['transactionamount'] = $dataInfo['money'];//交易金额
        $data['bankcardnumber'] = $dataInfo['banknumber'];//银行账号
        $data['bankcardname'] = $dataInfo['bankfullname'];//收款人账户名
        $data['bankname'] = $dataInfo['bankname'];//银行名称
        $data['bankcardtype'] = '个人';//银行卡类型
        $data['bankaccounttype'] = '储蓄卡';//银行卡借贷类型
        $data['terminal'] = 'WP';//终端类型
        $data['fromaccountnumber'] = '8800010291609';//会员账户号
        $data['backurl'] = $this->_site . 'Payment_YYpayDf_notifyurl.html';//异步回调地址
        $data['tf_sign'] = $this->_createSign($data, $payInfo['signkey']);
        $jsonArray = $this->request($payInfo['exec_gateway'],$data,'get');

        if($jsonArray['result'] == 'success' ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['info'],'status'=>5);
        }
    }
    function notifyurl(){

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