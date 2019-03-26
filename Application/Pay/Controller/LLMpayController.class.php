<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use http\Exception\UnexpectedValueException;
use Org\Util\Ysenc;


class LLMpayController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'LLMpay';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（乐联盟H5）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

/*

        	字符串	是	客户订单号。
amount	整型	是	充值金额，必须为整型。单位为元。
member_id	整型	是	客户编号，可在客户后台接口信息设置里获取。
sign	32位字符串	否	验证签名
notify_url	字符串	是	非必传参数，如果传递，需加入签名。如果传递则以此为通知地址。
redirect_url	字符串	是	非必传参数，如果传递，需加入签名。如果传递则以此为跳转地址。
is_h5	0/1	是	0或者1,0网页，1为H5页面。非必传参数，默认0，如果传递，需加入签名。如果传递则直接进入支付宝，不经过网页。[2018-10-31新增]

        */

        // 订单号，可以为空，如果为空，由系统统一的生成
        $re = $this->orderadd($parameter,1);
        $return = $re['return'];
        $res = $re['data'];

        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        date_default_timezone_set("PRC");
        $arraystr = array(
            'order_no'=>  $return['orderid'],//必填，您的订单号, 30个字符以内
            'amount'	=>  $return["amount"],//必填，订单金额,
            'member_id'=>  $return["mch_id"],//必填，商户号, 10个数字字符
            'notify_url'	=> $notifyurl,//必填，异步通知地址
            'redirect_url'	=> $return["callbackurl"]?$return["callbackurl"]:$callbackurl,//必填，同步返回地址, 详见备注2
            'is_h5'	=> 0
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);

        $result= $this->httpRequest($return["gateway"].'lepay/create_order',$arraystr,'post');

        if($result['status'] == 1) {
            $res = $this->orderadd1($res);
            if ($res) {
                if ($return['pay_memberid'] == 10112 || $return['pay_memberid'] == '10112') {
                    header("Location: ".$result ['pay_url']);
                } else {
                    $datas = array('code' => 0, 'msg' => '调起成功', 'payurl' => $result['pay_url']);
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

     /*   if(empty( $data['notify_url'])){
            unset($data['notify_url']);
        }
        if(empty( $data['redirect_url'])){
            unset($data['notify_url']);
        }*/

        //按照a-z顺序将字段进行排序。
        ksort($data);   //将MAP内的值合并（仅仅值合并）
        $sign_str=implode('',$data);
        //在字符串后增加查询密钥
        $sign_str.=$key;   //将字符串md5,小写
         $sign_str=md5($sign_str);
        return$sign_str;
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
        $status    =$data['order_status'];
        $sign = $data['sign'];
        unset($data['sign']);
        if(empty($data['order_no'])){
            die('fail');
        }
        $order_where = [
             'pay_status'          => 0,
             'pay_orderid'         => $data['order_no']
        ];

        $m_Order =  M("Order");
        $order = $m_Order->where($order_where)->find();
        $signs =$this->_createSign($data, $order["key"]);

        if($sign !=$signs){
            die('fail');
        }
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents('ckurl.txt',"order_id=".$data['out_trade_no'].'---'.$status."\r\n\r\n",FILE_APPEND);
        if($status==1){
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
            'member_id'	=>  $channel_account["mch_id"],//商户编号
            'order_no'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);
        $url= $this->httpRequest($Channel["gateway"].'lepay/query_order' ,$arraystr,'post');
        if($url['status']==1){
            //成功/关闭/已退款/失败
            if($url['info']['status']== 1  ){
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