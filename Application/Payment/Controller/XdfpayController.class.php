<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class XdfpayController extends Controller
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

        $data['userid'] = $payInfo['mch_id'];//商户编号
        $data['cusNo'] = $dataInfo['orderid'] ;//代付订单号
        $data['applyMoney'] = $dataInfo['money'];//代付金额
        $data['payeeAccount'] = $dataInfo['banknumber'];//银行卡号

        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $md5str= rtrim($md5str, "&").$payInfo['signkey'];

        $data['bankCode'] = 1022;//开户银行编号
        $bankfullname = iconv("utf-8","gb2312//IGNORE",$dataInfo['bankfullname']);
      
        $data['payeeUserName'] = $bankfullname;//开户姓名
        $data['sign'] =MD5($md5str);//MD5签名

        $url = $payInfo['exec_gateway'].'?';
        $url =  $url.http_build_query($data);
        $result = file_get_contents($url) ;

        $result = iconv("gb2312", "utf-8//IGNORE",$result);

        if($result=='success'){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$result,'status'=>5);
        }
    }

    //代付查询
    public function query()
    {
        $out_trade_no = I('request.out_trade_no');
        $sign = I('request.pay_md5sign');
        if(!$sign) {
            $this->showmessage("缺少签名参数");
        }
        if(!$out_trade_no){
            $this->showmessage("缺少订单号");
        }
        $mchid = I("request.mchid");
        if(!$mchid) {
            $this->showmessage("缺少商户号");
        }
        $user_id = $mchid - 10000;
        //用户信息
        $this->merchants = D('Member')->where(array('id'=>$user_id))->find();
        if(empty($this->merchants)) {
            $this->showmessage('商户不存在！');
        }
        if(!$this->merchants['df_api']) {
            $this->showmessage('商户未开启此功能！');
        }
        if($this->merchants['df_domain'] != '') {
            $referer = getHttpReferer();
            if(!checkDfDomain($referer, $this->merchants['df_domain'])) {
                $this->showmessage('请求来源域名与报备域名不一致！');
            }
        }
        if($this->merchants['df_ip'] != '' && !checkDfIp($this->merchants['df_ip'])) {
            $this->showmessage('IP地址与报备IP不一致！');
        }
        $request = [
            'mchid'=>$mchid,
            'out_trade_no'=>$out_trade_no
        ];

        $signature = $this->createSign($this->merchants['apikey'],$request);
        if($signature != $sign){
            $this->showmessage('验签失败!');
        }
        $order = M('df_api_order')->where(['out_trade_no'=>$out_trade_no,
            'userid'=>$user_id])->find();
        if(!$order){
			$return = [
				'status'=>'error',
				'msg'=>'请求成功',
				'refCode'=>'7',
				'refMsg'=>'交易不存在',
			];
			echo json_encode($return);exit;
        }elseif($order['check_status']==0){
            $refCode = '6';
            $refMsg = "待审核";
        }elseif($order['check_status']==2) {
            $refCode = '5';
            $refMsg = "审核驳回";

        }else{
            if($order['df_id'] > 0) {
                $df_order = M('wttklist')->where(['id'=>$order['df_id'], 'userid'=>$user_id])->find();
                if($df_order['status'] == 0) {
                    $refCode = '4';
                    $refMsg = "待处理";
                } elseif($df_order['status'] == 1) {
                    $refCode = '3';
                    $refMsg = "处理中";
                } elseif($df_order['status'] == 2) {
                    $refCode = '1';
                    $refMsg = "成功";
                } elseif($df_order['status'] == 3) {
                    $refCode = '2';
                    $refMsg = "失败";
                } elseif($df_order['status'] == 4) {
                    $refCode = '2';
                    $refMsg = "失败";
                } else {
                    $refCode = '8';
                    $refMsg = "未知状态";
                }
            }
        }
        $return = [
            'status'=>'success',
            'msg'=>'请求成功',
            'mchid'=>$mchid,
            'out_trade_no'=>$order['out_trade_no'],
            'amount'=>$order['money'],
            'transaction_id'=>$order['trade_no'],
            'refCode'=>$refCode,
            'refMsg'=>$refMsg,
        ];
        if($refCode == 1) {
            $return['success_time'] = $df_order['cldatetime'];
        }
        $return['sign'] = $this->createSign($this->merchants['apikey'],$return);
        echo json_encode($return);
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
            return 901 ;//$this->Msg_model->set( '系统不支持curl' );

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
                return  900 ;//$this->Msg_model->set( '不支持的请求方式' );
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
            return $curl_error;// $this->Msg_model->set( '请求结果出错'.$curl_error );
        }
        if( $httpcode != 200 )
        {
            return $httpcode; //$this->Msg_model->set( '返回http状态码错误'.$httpcode );
        }
        return $result;
    }


}