<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class LzfpayDfController extends Controller
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

        $bank = array('中国银行','工商银行','建设银行','中信银行','民生银行','兴业银行','农业银行','交通银行','北京银行','平安银行'
,'招商银行','光大银行','广发银行','浦发银行','华夏银行','邮政储蓄银行','上海银行','广州银行','上海农村商业银行','渤海银行','北京农商银行','
南京银行','东亚银行','宁波银行','深圳发展银行','杭州银行','徽商银行','浙商银行'

);
        $bank_car =array('BOC','ICBC','CCB','CITIC','CMBC','CIB','ABC','BOCM','BOB','PAB','CMB','CEB','GDB','SPDB','HXB',
            'PSBC','SHB','GZCB','SRCB','CBHB','BJRCB','NJCB','BEA','NBCB','SDB','HZB','HSB','CZB'
        );

        $BankType ='';
        $bankname = $dataInfo['bankname'];

        foreach ($bank as $k=>$v) {
            if (strpos($v, $bankname) !== false || strpos( $bankname,$v) !== false) {
                $BankType = $bank_car[$k];
            }
        }

        $data['merchantid'] = $payInfo['mch_id'];//商户号
        $data['merc_ord_no'] = $dataInfo['orderid'] ;//商户代付单号
        $data['version'] = '1.0' ;
        $data['reqtime'] =date('YmdHis',time());
        $data['tranamt'] = $dataInfo['money'];//交易金额
        $data['bankcode'] = $BankType;//交易金额
        $data['cardno'] = $dataInfo['banknumber'];
        $data['cardname'] = $dataInfo['bankfullname'];//交易金额
        $data['sign'] = $this->encrypt($data);
        $jsonArray = $this->doPost($payInfo['exec_gateway'],$data,'post');
        if($jsonArray['returncode'] == '0000' ){

            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['msg'],'status'=>5);
        }
    }
    function notifyurl(){

      /*  $r =  '{"merchantid":"519000122","merc_ord_no":"I0301343273896761","version":"1.0","ord_no":"ac1101432677283659776","tranamt":"10","reqtime":"20190304155708","returncode":"0000","returnmsg":"PAY_SUCCESS","status":"00"}';
        $RR = json_decode($r,true);*/

        $orderids['merchantid']   = $_REQUEST['merchantid'];
        $orderids['merc_ord_no']   = $_REQUEST['merc_ord_no'];
        $orderids['version']  = $_REQUEST['version'];
        $orderids['ord_no']   = $_REQUEST['ord_no'];
        $orderids['tranamt']   = $_REQUEST['tranamt'];
        $orderids['reqtime']   = $_REQUEST['reqtime'];
        $orderids['returncode']   = $_REQUEST['returncode'];
        $orderids['returnmsg']   = $_REQUEST['returnmsg'];
        $orderids['status']   = $_REQUEST['status'];
        $rr = json_encode($orderids);
        $sign= $_REQUEST['sign'];

        file_put_contents('createSign.txt',$sign . "dataLzf=" . $rr."\r\n\r\n",FILE_APPEND);

       /* if($sign !=$this->encrypt($orderids)){
            die("fail");
        }*/
        $orderid   = $_REQUEST['merc_ord_no'];
        $status    = trim($_REQUEST['status']);

        if($status == '00') {
            $status = 2;
        }
        if($status == '02'){
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

        die("SUCCESS");
    }

    //脚本自动处理查询代付订单
    function Query(){
        $Wttklist = M("Wttklist");
        $map['code'] = 'LzfpayDf';
        $map['status'] = 1;
        $withdraw = $Wttklist->where($map)->select();
        if($withdraw){
            foreach ($withdraw as $key => $v){

                $pfa_list = M("PayForAnother")->where(['id'=>$v['df_id']])->lock(true)->find();

                $this-> PaymentQuery($v, $pfa_list,2);
            }
        }
        echo 'success';
    }
    //代付查询
    public function PaymentQuery($v, $pfa_list ,$s=1)
    {
        $data['merchantid'] = $pfa_list['mch_id'];
        $data['merc_ord_no'] = $v['orderid'];
        $data['version'] = '1.0';
        $data['reqtime'] =date('YmdHms',time());
        $data['sign'] =$this->encrypt($data);
        $re = $this->doPost( $pfa_list['query_gateway'],$data);
        if($re['returncode'] == '0000'){
            if($re['status']=='02'){
                $re['pay_status'] = 3;
            }
            if($re['status']=='00'){
                $re['pay_status'] = 2;
            }
            if($re['status']=='01' ){
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
            'merchantid'	=>  $Channel["mch_id"],//商户编号
            'version'	=>  '1.0',//商户编号
            'reqtime' => date('YmdHms',time()),
        );


        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->encrypt($arraystr);
        $re= $this->doPost($Channel["query_gateway_money"] ,$arraystr,'post');
        if($re['returncode'] != '0000' ){
            return  0;
        }
        return $re['availamt'];
    }

    /**
     * @desc 默认post请求
     * @access public
     * @return array
     */
    public function doPost( $url, $params=array(), $method='post' )
    {
        return $this->httpRequest( $url, $params, $method );
    }
    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array(), $method = 'post', $connectTimeout=5, $readTimeout=10 )
    {
        header("Content-Type:text/html; charset=GBK");
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
       $result = iconv("UTF-8", "GBK//IGNORE",$result);
       /* json_decode($result,true);
        echo json_last_error();*/
        return json_decode($result,true);

    }



    /**
     * 加密方法
     * @param $data
     * @return string
     */
    protected function encrypt($data){

        $private_key = file_get_contents(BASE_PATH.'/rsa_private_key.pem');
        if (empty($private_key))
        {
            $this->showmessage('密钥不存在误');
        }

        $pkeyid = openssl_get_privatekey($private_key);
        if (empty($pkeyid))
        {
            $this->showmessage('密钥有误');
        }

        $sign = '';
        openssl_sign($this->SortToString($data), $sign, $private_key, OPENSSL_ALGO_MD5);
        $signData = base64_encode($sign);//最终的签名
        openssl_free_key($pkeyid);
        return $signData;
    }
    //直接运行代码如下
    function SortToString($data){
        ksort($data);
        $temp = [];
        foreach($data as $i => $v){
            if(isset($v)){
                if(is_array($v)){
                    $temp[] = $i . "=" . $this->SortToString($v);
                }else{
                    $temp[] = $i . "=" . $v;
                }
            }
        }
        return join("&", $temp);
    }


}