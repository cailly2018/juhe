<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class HyfpaywebController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'Hyfpayweb';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（汇丰银行支付宝H5）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $re = $this->orderadd($parameter,1);
        $return = $re['return'];
        $res = $re['data'];

        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        date_default_timezone_set("PRC");
        $arraystr = array(
            'bank_id'	=> 969 ,//必填，详见商户后台API管理
            'out_trade_no'=>  $return['orderid'],//必填，您的订单号, 30个字符以内
            'mch_id'=>  $return["mch_id"],//必填，商户号, 10个数字字符
            'total_fee'	=>  $return["amount"],//必填，订单金额,
            'return_url'	=> $return["callbackurl"]?$return["callbackurl"]:$callbackurl,//必填，同步返回地址, 详见备注2
            'notify_url'	=> $notifyurl,//必填，异步通知地址
            'apply_date'	=> date('Y-m-d H:i:s',time()),//必填，订单交易时间, 取值： date("Y-m-d H:i:s");
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);
        $arraystr['client_ip'] =  get_client_ip();
        $arraystr['body'] =  '实物_数码家电';
        $result= $this->httpRequest($return["gateway"].'Pay_Index.html',$arraystr,'post');

        if($result['returncode'] == 0000) {
            $res = $this->orderadd1($res);
            if ($res) {
                if ($return['pay_memberid'] == 10112 || $return['pay_memberid'] == '10112') {
                    header("Location: ".$result ['out_pay_url']);
                } else {
                    $datas = array('code' => 0, 'msg' => '调起成功', 'payurl' => $result['out_pay_url']);
                }
            }
        }else {

            $datas = array('code' => 1, 'msg' => '调起失败', 'payurl' => '');
        }
        $this->showmessage('',$datas,1);

    }
    private  function orderadd1($data){
        $Order =M('order');
        //添加订单
        if ($Order->add($data)) {
            return 1;
        } else {
            $this->showmessage('系统错误');
        }
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

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $data  = $_POST;
        $status    = trim($_REQUEST['returncode']);
        $sign = $data['sign'];
        unset($data['sign']);
        if(empty($data['out_trade_no'])){
            die('fail');
        }
        $order_where = [
             'pay_status'          => 0,
             'pay_orderid'         => $data['out_trade_no']
        ];

        $m_Order =  M("Order");
        $order = $m_Order->where($order_where)->find();
        $signs =$this->_createSign($data,  $order["key"]);

        if($sign !=$signs){
            die('fail');
        }
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents('ckurl.txt',"order_id=".$data['out_trade_no'].'---'.$status."\r\n\r\n",FILE_APPEND);
        if($status==0000){
            $this->EditMoney($data['out_trade_no'], $this->types, 0);
            die('ok');
        }else{
            die('fail');
        }
    }

    //订单查询
    function orderQuery(){

        $Channel  = $_REQUEST['Channel'];
        $order  = $_REQUEST['order'];

        $member = M('member')->where(['id' => $order['pay_memberid']-10000])->find();
        $channel_account = M('channel_account')->where(['channel_id' => $Channel['id']])->find();
        if(empty($member) || empty($channel_account) || empty($Channel) || empty($order)){
            return 0;
        }

        date_default_timezone_set("PRC");
        $arraystr = array(
            'mch_id'	=>  $channel_account["mch_id"],//商户编号
            'out_trade_no'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);
        $url= $this->httpRequest($Channel["gateway"].'Pay_pay_orderQurey.html' ,$arraystr,'post');
        if($url['status']==0){
            //成功/关闭/已退款/失败
            if($url['pay_status']== 1 || $url['pay_status']== 2  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }
    function  callbackurl(){
        echo '支付成功';die;
    }
}