<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/10/11
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;

class AlipayWapController extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_AlipayWap_notifyurl.html';

        file_put_contents('20181012.txt',$_SERVER["HTTP_REFERER"]."\r\n\r\n",FILE_APPEND);

        $parameter = array(
            'code' => 'AlipayWap',
            'title' => '收银台支付（支付宝 wapS）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        //支付金额
        $pay_amount = I("request.pay_amount", 0);

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        $callbackurl = $this->_site . 'Pay_AlipayWap_callbackurl.html?order_id='.$return['orderid'];
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        //获取请求的url地址
        $url=$return["gateway"];

        $arraystr = array(
            "version" => "1.0",//版本号
            'customerid' => $return["mch_id"],//商户编号
            "sdorderno" => $return["orderid"],//商户订单号
            "total_fee" => number_format($return["amount"],2,'.',''),//订单金额
            "paytype" => 'alipaywap',//支付类型
            "notifyurl" =>  $return['notifyurl'],
            "remark" => '收银台支付（支付宝 wapS）',
            "bankcode" => '',
            "sign" => '',
            "get_code" => '1321',
            "returnurl" => $return['callbackurl'],
        );

        $userkey = $return["signkey"];
        $version = $arraystr['version'];
        $customerid = $arraystr['customerid'];
        $sdorderno = $arraystr["sdorderno"];
        $total_fee = $arraystr["total_fee"];
        $notifyurl = $return['notifyurl'];
        $returnurl = $arraystr["returnurl"];

        $sign=md5('version='.$version.'&customerid='.$customerid.'&total_fee='.$total_fee.'&sdorderno='.$sdorderno.'&notifyurl='.$notifyurl.'&returnurl='.$returnurl.'&'.$userkey);

        $arraystr['sign']=$sign;
      //  $token=$return['signkey'];
       // $arraystr["key"] = md5($arraystr["notify_url"] .$arraystr["order_id"] .$arraystr["order_name"]. $arraystr["price"] .$arraystr["return_url"] .$token .$arraystr["type"] . $arraystr["uid"]);
        $res=$this->_createForm($url,$arraystr);
        echo $res;
        return;
    }

    public function callbackurl(){
        file_put_contents('AlipayWapcallbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["sdorderno"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], 'AlipayWap', 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl(){
        $status=$_POST['status'];
        $customerid=$_POST['customerid'];
        $sdorderno=$_POST['sdorderno'];
        $total_fee=$_POST['total_fee'];
        $paytype=$_POST['paytype'];
        $sdpayno=$_POST['sdpayno'];
        $remark=$_POST['remark'];
        $sign=$_POST['sign'];
       // $userkey = 'ab36e8cca6f4a25e050a7ae81fdab287af9d53ba';
        $userkey = M('Member')->where(['id'=>$customerid-10000])->getField('apikey');
        $mysign=md5('customerid='.$customerid.'&status='.$status.'&sdpayno='.$sdpayno.'&sdorderno='.$sdorderno.'&total_fee='.$total_fee.'&paytype='.$paytype.'&'.$userkey);

       // if($sign==$mysign){
            file_put_contents('AlipayWapnotifyurl.txt',"code=".$status."\r\n\r\n",FILE_APPEND);
            file_put_contents('AlipayWapnotifyurl.txt',"order_id=".$sdorderno."\r\n\r\n",FILE_APPEND);
            if($status=='1'){
                $this->EditMoney($sdorderno, 'AlipayWap', 0);
                $this->AllCallback($sdorderno);
                exit('success');
            } else {
                exit('fail');
            }
        /*} else {
            exit('fail');
        }*/
    }

    protected function _createForm($url, $data)
    {
        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onLoad="document.pay.submit()"">
                    <form method="post" action="' . $url . '" name="pay">';

        foreach ($data as $k => $vo) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $vo . '">';
        }

        $str .= '</form>
                    <body>
                </html>';
        return $str;
    }

}