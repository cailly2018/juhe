<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class NM2PayDfController extends Controller
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

        $data['mchid'] = $payInfo['mch_id'];//商户号
        $data['accountname'] = $dataInfo['bankfullname'];
        $data['bankname'] = $dataInfo['bankname'];
        $data['cardnumber'] = $dataInfo['banknumber'];
        $data['city'] = $dataInfo['shi'];
        $data['money'] = $dataInfo['money'];//交易金额
        $data['out_trade_no'] = $dataInfo['orderid'] ;//商户代付单号
        $data['notifyurl'] = $this->_site . 'Payment_NM2PayDf_notifyurl.html';//异步回调地址
        $data['province'] = $dataInfo['sheng'];
        $data['subbranch'] = $dataInfo['bankzhiname'];
        $data['extends'] =array('id'=>$payInfo['mch_id']);
        $data['pay_md5sign'] = $this->createSign($data, $payInfo['signkey']);
        $jsonArray = $this->request($payInfo['exec_gateway'],$data,'post');
        if($jsonArray['status'] == 'success' ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['msg'],'status'=>5);
        }
    }
    function notifyurl(){

        $returnCode   = $_REQUEST['returnCode'];
        if($returnCode !=0){
            die("fail");
        }
        $orderid   = $_REQUEST['out_trade_no'];
        $status    = trim($_REQUEST['status']);

        if($status == 2) {
            $status = 2;
        }
        if($status == 3){
            $status =3;
        }
        $where = ['orderid' => $orderid];
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

        die("success");
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
        $map['code'] = 'NM2PayDf';
        $map['status'] = 1;
        $withdraw = $Wttklist->where($map)->select();

        //print_r($withdraw);die;
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
          $data['mchid'] = $pfa_list['mch_id'];
          $data['out_trade_no'] = $v['orderid'];
          $data['pay_md5sign'] = $this->createSign($data, $pfa_list['signkey']);

          $re = $this->request( $pfa_list['query_gateway'],$data,'get');
          if($re['status'] == 'success'){
              if($re['refCode']==2 || $re['refCode']==5){
                  $re['pay_status'] = 3;
              }
              if($re['refCode']==1){
                  $re['pay_status'] = 2;
              }
              if($re['refCode']==3 ||$re['refCode']==4 ){
                  $re['pay_status'] = 1;
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
        $arraystr = array(
            'mchid'	=>  $Channel["mch_id"],//商户编号
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['pay_md5sign'] =$this->createSign($arraystr,  $Channel["signkey"]);
        $re= $this->request($Channel["query_gateway_money"] ,$arraystr,'post');
        if($re['status'] != 'success' ){
            return  0;
        }
        return $re['data']['balance'];
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

}