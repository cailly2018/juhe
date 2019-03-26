<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class AApayzfbController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'AApayzfb';
    }

    /**
     * @param $array
     */
    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（支付宝H5）',
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
        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
       /* $return["notifyurl"] || $return["notifyurl"] = $notifyurl;*/
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;

        date_default_timezone_set("PRC");
        $arraystr = array(
            'appid'        =>  $return["mch_id"],
            'pay_type'     => 'alipay_red',
            'out_trade_no' => $return['orderid'],
            'amount'       => sprintf("%.2f",$return["amount"]),
            'callback_url' => $notifyurl,
            'success_url'  => $callbackurl,
            'version'      => 'v1.0',

        );


        $arraystr = array(
            'out_trade_no'        =>  $return["mch_id"],
            'order_name'     => 'alipay_red',
            'total_amount' => $return['orderid'],
            'spbill_create_ip'       => sprintf("%.2f",$return["amount"]),
            'notify_url' => $notifyurl,
            'return_url'  => $callbackurl,

        );






        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->getSign( $return["signkey"],$arraystr);

        $result= $this->https_request($return["gateway"].'index/unifiedorder?format=json', 'POST', $arraystr);

        if($result['code'] == 200){
            $this->orderadd1($re['data']);
            if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){

                header("Location: ".$result ['url']);
            }else{
                $data =  array('code'=>0, 'msg'=>'调起成功', 'payurl'=> $result ['url']);
            }
        }else{
            $data =  array('code'=>1, 'msg'=>'调起失败', 'payurl'=> '');
        }

        $this->showmessage('',$data,1);
        return;
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


    function getSign($secret, $data)
    {

        // 去空
        $data = array_filter($data);

        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);

        //签名步骤二：在string后加入mch_key
        $string_sign_temp = $string_a . "&key=" . $secret;

        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);

        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);

        return $result;
    }


    function https_request($url, $method = 'GET', $data = array())
    {
        if ($method == 'POST') {
            //使用crul模拟
            $ch = curl_init();
            //禁用https
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        } else {
            if (ini_get('allow_fopen_url') == '1') {
                $result = file_get_contents($url);
            } else {
                //使用crul模拟
                $ch = curl_init();
                //允许请求以文件流的形式返回
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //禁用https
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch); //执行发送
                curl_close($ch);
            }
        }

        return json_decode($result,true);;
    }


    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid   = trim($_REQUEST['out_trade_no']);
        $status    = trim($_REQUEST['callbacks']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents('ckurl.txt',"order_id=".$_REQUEST["orderid"].'----'.$orderid.'---'.$status."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            die('fail');
        }
        if($status=='CODE_SUCCESS'){
            $this->EditMoney($orderid, $this->types, 0);
            die('success');
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
            'pay_memberid'	=>  $channel_account["mch_id"],//商户编号
            'pay_orderid'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['pay_md5sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);


        $url= $this->_createForm($Channel["gateway"].'Pay_Trade_query.html' ,$arraystr);

        if($url['returncode']=='00' ||$url['returncode']==00 ){
            //成功/关闭/已退款/失败
            if($url['trade_state']== 'SUCCESS'  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }
    function  callbackurl(){
        echo '支付成功';die;
        /*$orderid       = trim($_REQUEST['orderid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }*/

    }

}