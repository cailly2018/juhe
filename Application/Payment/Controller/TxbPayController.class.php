<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class TxbPayController extends Controller
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

        $data['version'] = '1.0';//版本号
        $data['spid'] = $payInfo['mch_id'];//商户号
        $data['spbillno'] = $dataInfo['orderid'] ;//商户代付单号
        $data['sp_reqtime'] = date('Ymdhms',time());//请求时间
        $data['tran_amt'] = $dataInfo['money']*100;//交易金额
        $data['acct_name'] = $dataInfo['bankzhiname'];//收款人姓名
        $data['acct_id'] = $dataInfo['banknumber'];//收款人账号
        $data['acct_type'] = 0;//账号类型
        $data['cert_type'] = 1;//账号类型
        $data['cert_id'] = '450521188906212398';//证件号码
        $data['mobile'] = '13724268315';//收款人手机号码
        $data['bank_name'] = $dataInfo['bankname'];//开户行名称
        $data['bank_branch_name'] = '212';//支行名称 对公必传
        $data['bank_settle_no'] = '212';//支行联行号
        $data['notify_url'] = $this->_site . 'Pay_TxbPay_notifyurl.html';
        $data['memo'] = '代付订单';
        $data['sign'] = $this->_createSign($data, $payInfo['signkey']);

        $jsonArray = $this->request_post( $payInfo['exec_gateway'],$data);

        if($jsonArray['retcode'] == 0 || $jsonArray['retcode'] =='0' ){

            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['retmsg'],'status'=>5);
        }
    }
    function notifyurl(){

       //接收传送的数据
        $fileContent = file_get_contents("php://input");

        ### 把xml转换为数组
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        //先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。
        $value_array = json_decode(json_encode(simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOCDATA)), true);


        $orderid        = trim($value_array['spbillno']);
        $opstate        = trim($value_array['serialno_state']);

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

    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $vos ='<xml>';
        foreach ($param as $k => $vo) {
            $vos.= '<'.$k.'>'.$vo.'</'.$k.'>';
        }
        $data = $vos.'</xml>';

        $ch = curl_init();
        $header[] = "Content-type: text/xml";//定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
        curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
        curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //定义提交的数据，这里是XML文件
        $result = curl_exec($ch);
        curl_close($ch);//关闭

        $postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);

        $jsonStr = json_encode($postObj);

        $jsonArray = json_decode($jsonStr,true);


        return $jsonArray;

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
        $map['code'] = 'TxbPay';
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

          $data['version'] = '1.0';
          $data['spid'] = $pfa_list['mch_id'];
          $data['spbillno'] = $v['orderid'];
          $data['sp_reqtime'] =  date('YdmHis',time());
          $data['sign'] = $this->_createSign($data, $pfa_list['signkey']);
          $data['transaction_id'] = '';

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
                if( $memberInfo['parentid']>1){
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
                }
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


    function moneyQuery(){
        $Channel  = $_REQUEST['channel'];
        $arraystr = array(
            'spid'	=> $Channel['mch_id'],//商户编号
            'spbillno'	=> date('YmdHis',time()),//时间戳格式
            'sp_reqtime'	=> date('YmdHis',time()),//时间戳格式
            'version'	=>  '1.0',
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $Channel["signkey"]);
        $re= $this->request($Channel["query_gateway_money"] ,$arraystr,'post');

        if($re['retcode'] != '0'){
            return  0;
        }
        return $re['available _balance']/100;
    }
}