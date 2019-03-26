<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class YHpayDfController extends Controller
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
        $this->types = 'YHpayDf';
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
        $systembank =  M("systembank")->where(['bankname'=>$dataInfo['bankname']])->find();

        $content = array(
            'orderNo'=> $dataInfo['orderid'],
            'amount'=>  $dataInfo['money']*100,
            'bankCardNo'=>  $dataInfo['banknumber'],
            'bankBranchName'=> $dataInfo['bankzhiname'],//支行名称
            'bankUserName'=> $dataInfo['bankfullname'],//收款人账户名
            'bankName'=> $dataInfo['bankname'],//收款人账户名
            'bankNumber'=> '',//联行号
            'bankCode'=>  $systembank['bankcode'],//代码
            'accountType'=> '1',//代码
            'province'=> $dataInfo['sheng'],//省
            'city'=> $dataInfo['city'],//省
        );

        $data['appid'] = $payInfo['mch_id'];//商户编号
        $data['reqTrx'] = date('YmdHis',time());//商户编号
        $data['dateTime'] =  date('YmdHis',time());//时间戳格式 ;
        $data['service'] = 'pa_certification_sett';//	服务地址
        $data['version'] = '1';//商户编号
        $data['charset'] = 'UTF-8';//商户编号
        $data['signType'] = 'MD5';//商户编号
        $data['format'] = 'JSON';//商户编号
        $data['content'] = $this->encrypt(json_encode($content),$payInfo['signkey']) ;
        $data['sign'] = $this->_createSign($data, $payInfo['signkey']);

        $jsonArray = $this->request($payInfo['exec_gateway'],$data,'post');

        if($jsonArray['code'] != '0000' ){
            return array('msg'=>$jsonArray['ret_message'],'status'=>5);

        }else{
            return  array('msg'=>'success','status'=>1);
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


    protected function _createSign($data, $key)
    {
        $sign          = '';
        krsort($data);
        foreach ($data as $k => $vo) {
            if(!empty($vo)){
                $sign .= $vo;
            }
        }
        return strtoupper(MD5($sign));
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
                $this-> PaymentQuery($v, $pfa_list);
            }
        }
        echo '执行成功';
    }
    //代付查询
    public function PaymentQuery($v, $pfa_list)
    {
        $content=array(
            'orderNo'=>$v['orderid']
        );
        $data['appid'] = $pfa_list['mch_id'];//商户编号
        $data['reqTrx'] = date('YmdHis',time());//商户编号
        $data['dateTime'] =  date('YmdHis',time());//时间戳格式 ;
        $data['service'] = 'trade_query';//	服务地址
        $data['version'] = '1';//商户编号
        $data['charset'] = 'UTF-8';//商户编号
        $data['signType'] = 'MD5';//商户编号
        $data['format'] = 'JSON';//商户编号
        $data['content'] = $this->encrypt(json_encode($content),$pfa_list['signkey']) ;
        $data['sign'] = $this->_createSign($data, $pfa_list['signkey']);
        $re = $this->request( $pfa_list['query_gateway'],$data,'post');
        if($re['code'] == '0000'){
             if($re['data']['status']== '0000'){
                 $re['pay_status'] = 2;
             }
             if($re['data']['status']=='P999'){
                 $re['pay_status'] = 1;
             }
             if($re['data']['status']=='P888') {
                 $re['pay_status'] = 3;
             }
             $this->editwtStatus($v['id'],$re['pay_status'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);
        }
    }



    /*
       * 字符串加密 不写入文件
       */
    public function encrypt($input,$key)
    {

        $data =  openssl_encrypt(base64_encode($input), 'aes-128-ecb', hex2bin($key), OPENSSL_RAW_DATA);
        return strtoupper(bin2hex($data));

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
        $content = array(
            'orderNo'	=> time(),
        );

        $data['appid'] = $Channel['mch_id'];//商户编号
        $data['reqTrx'] = time();//商户编号
        $data['dateTime'] =  date('YmdHis',time());//时间戳格式 ;
        $data['service'] = 'balance_query';//	服务地址
        $data['version'] = '1';//商户编号
        $data['charset'] = 'UTF-8';//商户编号
        $data['signType'] = 'MD5';//商户编号
        $data['format'] = 'JSON';//商户编号
        $data['content'] =$this->encrypt(json_encode($content),$Channel['signkey']) ;//商户编号
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $data['sign'] = $this->_createSign($data, $Channel['signkey']);

        $re= $this->request($Channel["query_gateway_money"] ,$data,'post');


        if($re['code'] != '0000'){
            return  0;
        }

        return $re['data']['settAmount'];

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