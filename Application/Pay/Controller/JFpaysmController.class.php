<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class JFpaysmController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'JFpaysm';

    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（极支付宝扫码）',
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
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;

        date_default_timezone_set("PRC");
        $arraystr = array(
            'appid'=>  $return['appid'],//商户订单号
            'mch_id'=>  $return["mch_id"],//商户编号
            'nonce_str'=>  'h9o8u9p',//商户编号
            'out_trade_no'=>  $return['orderid'],//商户订单号
            'pay_type'	=> 'ALIPAY',//随机串
            'sign_type'	=> 'MD5',//随机串
            'notify_url'	=> $notifyurl,
            'total_fee'	=>  $return["amount"],//订单金额,
            'timestamp'	=> date('YmdHis',time()),//时间戳格式
        );
        $arraystr['remark'] =  '实物_数码家电';
        $arraystr['title'] =  '实物_数码家电';
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);

        $re= $this->_createForm($return["gateway"].'/api/v1/openapi/pay/gateway',$arraystr);

        if($re['code'] ==  0){
            //请求上游成功了才把数据写到表里
            $res =  $this->orderadd1($res);
            if($res){
                if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){
                    $data['orderid'] = $arraystr['out_trade_no'];
                    $data['amount'] = $return["amount"];
                    $data['out_trade_id'] =$return['orderid'];
                    $data['datetime'] = date('Y-n-d H:i:s',time());
                    $data['subject'] =  $arraystr['title'];
                    $this->showQRcode($re['data']['code_url'], $data, 'alipay',$re['data']['code_url']);
                }else{
                    import("Vendor.phpqrcode.phpqrcode", '', ".php");
                    $QR = "Uploads/codepay/" . $return["orderid"] . ".png"; //已经生成的原始二维码图
                    \QRcode::png($re['data']['pay_url'], $QR, "L", 20);
                    echo json_encode(array('code'=>0, 'msg'=>'调起成功', 'payurl'=> $this->_site . $QR));die;
                }
            }

        }else{
            echo json_encode(array('code'=>1, 'msg'=>'调起失败','payurl'=>'','data'=>array()));die;
        }

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

    protected function _createForm($url, $data)
    {
        $postData = http_build_query($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        $r = curl_exec($curl);
        curl_close($curl);
        return json_decode($r,true);
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $data['appid']   = trim($_REQUEST['appid']);
        $data['mch_id']   = trim($_REQUEST['mch_id']);
        $data['sign_type']   = trim($_REQUEST['sign_type']);
        $sign   = trim($_REQUEST['sign']);
        $data['billno']   = trim($_REQUEST['billno']);
        $data['out_trade_no']   = trim($_REQUEST['out_trade_no']);
        $data['total_fee']   = trim($_REQUEST['total_fee']);
        $data['fee']   = trim($_REQUEST['fee']);
        $data['pay_time']   = trim($_REQUEST['pay_time']);
        $data['trade_status']   = $_REQUEST['trade_status'];
        $data['remark']   = $_REQUEST['remark'];
        if(empty($sign)){
            die('fail');
        }
        if(empty($data['out_trade_no'])){
            die('fail');
        }
        $key  = M('order')->where(['pay_orderid' => $data['out_trade_no']])->field('id,key')->find();


        if(empty($key)){
            die('fail');
        }
        $signs =$this->_createSign($data,  $key["key"]);
        if($signs!=$sign){
            die('fail');
        }

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$data['out_trade_no'].'---'.  $data['trade_status'] ."\r\n\r\n",FILE_APPEND);

        if(empty($data['out_trade_no'])){
            die('fail');
        }
        if($data['trade_status']==1){
            $this->EditMoney($data['out_trade_no'], $this->types, 0);
            die('SUCCESS');
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
            'appid'=>  '800102',//商户订单号
            'mch_id'	=>  $channel_account["mch_id"],//商户编号
            'nonce_str'=>  'h9o8u9p',
            'sign_type'=>  'MD5',
            'timestamp'	=> date('YmdHis',time()),//时间戳格式
            'out_trade_no'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,$channel_account["signkey"]);

        $url= $this->_createForm($Channel["gateway"].'/api/v1/openapi/pay/query' ,$arraystr);

        if($url['code']=='0'){
            //成功/关闭/已退款/失败
            if($url['data']['trade_status']== 1  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }


    function  callbackurl(){
        $orderid       = trim($_REQUEST['orderid']);
        $pay_memberid       = trim($_REQUEST['pay_memberid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['out_trade_id' => $orderid,'pay_memberid' => $pay_memberid])->find(); //获取订单信息

       // echo M()->getLastSql();die;
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }
    }
}