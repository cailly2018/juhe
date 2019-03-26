<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/22
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class ZHpaywxController extends PayController{

    protected $types;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->types = 'ZHpaywx';
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $parameter = array(

            'code' => $this->types,
            'title' => '收银台支付（中汇线微信宝扫码支付）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter,0);

       // $return = $re['return'];

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        header('Content-Type:text/html;charset=utf8');

        $arraystr = array(
            'agentId' => $return["mch_id"],//业务人员分配，发送报文的时候不够8位，右补0例如：22812340
            'mchId' => $return["appid"],//代理商签约商户编号例如：Z08000003122402
            'payServiceCode' =>'wechatPay',
            'outTradeNo' =>$return['orderid'],//交易流水号
            'totalFee' =>  $return["amount"]*100,
            'reqDate' =>  date('Y-m-d H:i:s',time()),
            'callBackUrl' => $notifyurl,//通知成功后返回success 通知三次若无响应则不在通知。通知间隔为10 ，20， 30 秒
        );

        $arraystr['signature'] = $this->encrypt($arraystr,$return['signkey']);
        
        $result =  $this->httpRequest($return['gateway'].'nativePay', $arraystr );

        if($result['respCode'] == '00'){
            //请求上游成功了才把数据写到表里
           // $this->orderadd1($re['data']);

            //支付完成的逻辑, 反悔了一个html文本，直接在界面上将这个html文本渲染即可
            $notifyurl = $result['orderURL'];

            if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){
                //生成二维码
                import("Vendor.phpqrcode.phpqrcode", '', ".php");
                $QR  = "Uploads/charges/" . time() . ".png"; //已经生成的原始二维码图
                \QRcode::png($notifyurl, $QR, "L", 20, 1);

                $this->assign("msg", '订单以支付金额为准');
                $this->assign("msg1", ' 保存到手机 微信打开扫一扫付款');
                $this->assign("title", '笔记本');
                $this->assign("imgurl", $this->_site .$QR);
                $this->assign("ddh", $orderid);
                $this->display("Charges/pay");
            }else{
                echo json_encode(array('code'=>0, 'msg'=>'调起成功', 'payurl'=>$notifyurl));die;
            }

        }else{
            echo json_encode(array('code'=>1, 'msg'=>'调起失败','payurl'=>''));die;

        }
    }

    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array() )
    {
        if( !function_exists('curl_init') )
            return 901 ;
        $data_json = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response =  json_decode($response,true);
        return $response;
    }


    function encrypt($data,$pass)
    {
        $data =   strtoupper($this->SortToString($data));
        $filePath = BASE_PATH.'/24249000.p12';
        if(!file_exists($filePath)) {
            return false;
        }

        $cer_key = file_get_contents($filePath); //获取密钥内容
        openssl_pkcs12_read($cer_key, $certs, $pass);
        openssl_sign($data, $signMsg, $certs['pkey']); //注册生成加密信息
        $signMsg = bin2hex($signMsg); //转为16进制
        return $signMsg;

    }

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


    private  function orderadd1($data){
        $Order =M('order');
        //添加订单
        if ($Order->add($data)) {
            return 1;
        } else {
            $this->showmessage('系统错误');
        }
    }
    // 服务器点对点返回
    public function notifyurl(){

        header('Content-Type:text/html;charset=utf-8');
        $fileContent = file_get_contents("php://input");

        // {"respDate":"2019-01-23 10:43:07","respMsg":"SUCCESS","respCode":"00","amount":"1","orderNo":"S123113742447562","remark1":null,"remark2":null,"signature":"7a519f2d4e72c73e032c9989a91248bcf9d6c8c6ae6994558f055156dc64077f7beb7f90b9af71afca58c9c4665e14459119ea867fa754fa925a61ee03f0e903b3b356eb849870db869757846cae30f1c0df6692e76656c1543a84c0f7cb3640f7c2298d990e5e06ef2986db54adbf3b743acee0d451d965ace50d1bebc18c44","transNo":"1300000000000031061"}

        $data = json_decode($fileContent,true);
        $code    = $data['respCode'];
        $orderid= $data['orderNo'];
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$fileContent.'---'.$code."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            die('fail');
        }
        if($data['respCode'] == '00') {
            $this->EditMoney($orderid, $this->types, 0);
            die('success ');
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
            'agentId'	=>  $channel_account["mch_id"],//商户编号
            'mchId'	=> $channel_account["appid"],//代理商签约商户编号
            'outTradeNo'	=> $order['pay_orderid'],//签名
            'reqDate'	=> date('Y-m-d H:i:s',time()),//签名

        );
        #进行签名处理，一定按照文档中标明的签名顺序进行

        $arraystr['signature'] = $this->encrypt($arraystr,$channel_account['signkey']);

        $url= $this->httpRequest($Channel["gateway"].'orderQuery',$arraystr);

        if($url['tradeState']=='SUCCESS'){
            //成功/关闭/已退款/失败
            if($url['data']['status'] == '成功' &&$order['pay_orderid'] == $url['data']['businessnumber'] ){
                $this->EditMoney($url['data']['businessnumber'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }

    function refund(){
        $Channel  = $_REQUEST['Channel'];
        $order  = $_REQUEST['order'];

        $member = M('member')->where(['id' => $order['pay_memberid']-10000])->find();
        $channel_account = M('channel_account')->where(['channel_id' => $Channel['id']])->find();
        if(empty($member) || empty($channel_account) || empty($Channel) || empty($order)){
            return 0;
        }

        date_default_timezone_set("PRC");
        $arraystr = array(
            'agentId'	=>  $channel_account["mch_id"],//商户编号
            'mchId'	=> $channel_account["appid"],//代理商签约商户编号
            'outTradeNo'	=>$order['pay_orderid'],
            'refundNo'	=> $order['pay_orderid'].$order['pay_orderid'],//签名
            'totalFee'	=> $order['pay_amount']*100,//签名
            'reqDate'	=> date('Y-m-d H:i:s',time()),//签名

        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['signature'] = $this->encrypt($arraystr,$channel_account['signkey']);

        $url= $this->httpRequest($Channel["gateway"].'refund',$arraystr);

        echo '<pre>';
        print_r($url);die;
        if($url['tradeState']=='SUCCESS'){
            //成功/关闭/已退款/失败
            if($url['data']['status'] == '成功' &&$order['pay_orderid'] == $url['data']['businessnumber'] ){
                $this->EditMoney($url['data']['businessnumber'], $this->types, 0);
                return 1;
            }
        }
        return 2;

    }

    function  callbackurl(){
        $orderid       = trim($_REQUEST['orderid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }

    }

}