<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class WLPayController extends Controller
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
        $ka = array('工商银行','招商银行','农业银行','建设银行','交通银行','兴业银行','民生银行','光大银行','中国银行','中信银行','广发银行','上海浦东发展银行','中国邮政','平安银行','华夏银行');
        $kc = array('ICBC','CMBCHINA','ABC','CCB','BOCO','CIB','CMBC','CEB','BOC','ECITIC','GDB','SPDB','POST','PINGANBANK','HXB');

        $BankType ='';
        foreach ($ka as $k=>$v) {
            if (strpos($dataInfo['bankname'], $v) !== false) {
                $BankType = $kc[$k];
            }
        }

        $data =array(
            'p0_Cmd'=>1,//	业务类型	是	Max(20)	固定值“SettOrderPay”	1
            'p1_MerId'=>$payInfo['mch_id'],//	商户编号
            'p2_Order'=>$dataInfo['orderid'],//	商户订单号	是	Max(50)	提交的订单号必须在自身账户交易中唯一；
            'p3_Amt'=>$dataInfo['money'],//	代付金额	是	Max(20)	单位：元，精确到分，保留小数点后两位	4
            'p4_Name'=>$dataInfo['bankfullname'],//	收款人	是	Max(10)	收款人，使用url编码发送，使用原始值签名	5
            'p5_BankAddress'=> $dataInfo['bankzhiname'],//	开户分行	是	Max(20)	收款银行卡的开户分行，使用url编码发送，使用原始值签名	6
            'P6_BankAddress2'=>$dataInfo['bankzhiname'],//	开户支行	是	Max(20)	收款银行卡的开户支行，使用url编码发送，使用原始值签名	7
            'p7_BankCard'=>$dataInfo['banknumber'],//	收款银行卡	是	Max(21)	收款银行卡	8
            'p8_BankType'=>$BankType,//	收款银行卡编码	是	Max(200)	请按照文档下方“收款银行卡编码列表”所示参数提交
            'p9_Remark'=>'test',//	商户扩展信息	否	Max(200)	返回时原样返回，此参数如用到中文，请注意转码	10
            'p_Url'=>  $this->_site . 'Pay_WLPay_notifyurl.html',//	通知地址	是	Max(200)	代付成功后，[API支付平台]会通过该地址进行通知	11
            'p_Province'=>$dataInfo['sheng'],//	收款卡开户省	是	Max(20)	收款卡开户省，使用url编码发送，使用原始值签名	12
            'p_City'=>$dataInfo['shi'],//	收款卡开户市	是	Max(20)	收款卡开户市，使用url编码发送，使用原始值签名	13
            'p_IdCard'=>'450521188906212398',//	身份证号	否	Max(18)	需要验证身份证号时此项必填
            'p_Phone'=>'13724269315',//	手机号码	否	Max(11)	需要验证手机号时此项必填
            'hmac'=>'',//签名数据
        );
        $str =$data['p0_Cmd']. $data['p1_MerId'].$data['p2_Order'].$data['p3_Amt'].$data['p4_Name'].$data['p7_BankCard'].$data['p8_BankType'].$data['p_Url'].$payInfo['signkey'];
        iconv( "UTF-8", "gb2312" , $str);
        $data['hmac'] = MD5($str);
  /*      echo '<pre>';
        print_r($data);die;*/


        $jsonArray = $this->request_post( $payInfo['exec_gateway'],$data);


        if($jsonArray[' r2_TrxId']){

            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['retmsg'],'status'=>5);
        }
    }
    function notifyurl(){

        $orderid        = trim($_REQUEST['r4_Order']);
        $opstate        = trim($_REQUEST['r1_Code']);

        if($opstate == 1 ) {

            $where = ['orderid'=>$orderid];
            $lists = M('Wttklist')->where($where)->select();

            $_REQUEST = [
                'code'=>CONTROLLER_NAME,
                'id'=>$lists['id'],
                'opt' => 'exec',
            ];
            return R('Payment/Index/index');

            die("success");
        }
        die("fail");

    }

    function request_post($url = '', $param) {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $result = curl_exec($ch);//运行curl
        curl_close($ch);

        echo '<pre>';
        print_r($result);



    }


    protected function _createSign($data, $key)
    {
        $sign          = '';
        ksort($data);
        foreach ($data as $k => $vo) {
            $sign .= $k.'='.$vo.'&';

        }

        return strtoupper(MD5($sign.'key='.$key));
    }
    //脚本自动处理查询代付订单
    function Query(){
        $Wttklist = M("Wttklist");
        $map['status'] = 1;
        $map['code'] = CONTROLLER_NAME;
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

          $data['p0_Cmd'] = 'SettOrder';
          $data['p1_MerId'] = $pfa_list['mch_id'];
          $data['p2_Order'] = $v['orderid'];
          $data['hmac'] = md5($data['p0_Cmd'].$data['p1_MerId'].$data['p2_Order']);
          $re = $this->request_post( $pfa_list['query_gateway'],$data,$s);

          if($re['retcode'] == 0){
              if($re['serialno_state']==1){
                  $re['serialno_state'] = 2;
              }
              if($re['serialno_state']==2){
                  $re['serialno_state'] = 1;
              }
              if($re['serialno_state']==3 || $re['serialno_state']==4){
                  $re['serialno_state'] = 3;
              }
              $this->editwtStatus($v['id'],$re['serialno_state'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);
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
            /*    if( $memberInfo['parentid']>1){
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