<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class HyfpayDfController extends Controller
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
        $this->types = 'HyfpayDf';
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
        $data['bank_id'] =  947;//接口交易类型代码
        $data['out_trade_no'] =  $dataInfo['orderid'] ;//商户代付单号
        $data['mch_id'] =  $payInfo['mch_id'];//商户号
        $data['total_fee'] = $dataInfo['money'];//交易金额
        $data['return_url'] =  $this->_site . 'Payment_'.$this->types.'_callbackurl.html';
        $data['notify_url'] =  $this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $data['apply_date'] = date("Y-m-d H:i:s",time());
//沈熙华
        $data['sign'] = $this->_createSign($data,$payInfo['signkey']);//收款人账户名
        $data['body'] ='支付';//收款人账户名
        $data['rec_name'] =$dataInfo['bankfullname'];//收款人账户名
        $data['rec_cert_no'] = '150981197202284550';//持卡人身份证号
        $data['rec_phone'] = '19925274211';//持卡人手机号码
        $data['rec_bank_name'] = $dataInfo['bankname'];//银行名称
        $data['rec_zbank_name'] = $dataInfo['bankzhiname'];//结算支行
        $data['rec_bank_no'] = $dataInfo['banknumber'];//结算卡号
        $data['rec_prop'] = 1;
        $data['rec_cnaps'] = '103823115043';//联行号
        $data['rec_province'] =$dataInfo['sheng'] ;//联行号
        $data['rec_city'] =$dataInfo['shi'] ;//联行号
        $jsonArray = $this->httpRequest($payInfo['exec_gateway'],$data,'post');

        if($jsonArray['returncode'] == 0000 ){
            if($jsonArray['status'] =='success' ){
                return  array('msg'=>'success','status'=>1);
            }else{
                return array('msg'=>$jsonArray['msg'],'status'=>5);
            }
        }else{
            if($jsonArray['status'] =='false' ){
                return array('msg'=>$jsonArray['msg'],'status'=>5);
            }
        }

    }
    function notifyurl(){

        $xml = file_get_contents('php://input');
        $result = json_decode($xml, true);
        $another = M('pay_for_another')->where(['code' =>$this->types ])->find();
        $sign = $result['sign'];
        unset($result['sign']);
        $signs = $this->_createSign($result,$another['signkey']);

         if($sign !=$signs){
             die("fail");
         }
        $orderid   = $result['out_trade_no'];
        $status    = $result['returncode'];

        if($status == 0) {
            $status = 2;
        }else{
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

        die("ok");
    }
    //脚本自动处理查询代付订单
    function Query(){

        $Wttklist = M("Wttklist");
        $map['code'] = $this->types;
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
            'mch_id'	=>  $pfa_list["mch_id"],//商户编号
            'out_trade_no'=>  $v['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $pfa_list["signkey"]);
        $res =$this->httpRequest( $pfa_list["query_gateway"] ,$arraystr,'post');

        if($res['status']==0) {
            //成功/关闭/已退款/失败
            if ($res['pay_status'] == 1 || $res['pay_status'] == 2) {
                $re['pay_status'] = 2;
            }else{
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

    function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'mch_id'	=>  $Channel["mch_id"],//商户编号
            'nonce_str'=> time(),
            'user_account'=> '19925274211',
            'user_password'=> '123456',
        );
        $arraystr['sign'] = $this->_createSign( $arraystr,$Channel['signkey']) ;
        $jsonArray = $this->httpRequest($Channel['query_gateway_money'],$arraystr,'post');

        $money = 0;
        if($jsonArray['status']==0){
            $money = $jsonArray['balance'];
        }
        return $money;
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
    function  callbackurl(){
        echo '代付成功';die;
    }
    protected function httpRequest($url, $params,$method='get')
    {
        if( !function_exists('curl_init') )
            return 901 ;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 5 + 5 );
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
        return json_decode($result,true);;
    }

}