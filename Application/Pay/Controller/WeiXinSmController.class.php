<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/12
 * Time: 10:04
 */

namespace Pay\Controller;


class WeiXinSmController extends PayController
{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){


        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_WeiXinSm_notifyurl.html'; //异步通知
        $callbackurl = $this->_site . 'Pay_WeiXinSm_callbackurl.html'; //跳转通知
        $parameter = array(
            'code' => 'WeiXinSm', // 通道名称
            'title' => '微信扫码支付-官方', //通道名称
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid' => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel' => $array,
            'body' => $body
        );
        $return = $this->orderadd($parameter);

        $callbackurl = $this->_site . 'Pay_WeiXinSm_callbackurl.html?order_id='.$return['orderid'];
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
            "paytype" => 'weixin',//支付类型
            "notifyurl" =>  $return['notifyurl'],
            "remark" => '收银台支付（微信扫码支付）',
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
        $res=$this->_createForm($url,$arraystr);
        echo $res;
        return;
    }
    public function callbackurl(){
        file_put_contents('WeiXinSmbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["sdorderno"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], 'WeiXinSm', 1);
        }else{
            exit("error");
        }
    }


    // 服务器点对点返回
    public function notifyurl(){

        $status=$_REQUEST['status'];
        $customerid=$_REQUEST['customerid'];
        $sdorderno=$_REQUEST['sdorderno'];
        $total_fee=$_REQUEST['total_fee'];
        $paytype=$_REQUEST['paytype'];
        $sdpayno=$_REQUEST['sdpayno'];
        $remark=$_REQUEST['remark'];
        $sign=$_REQUEST['sign'];
        $userkey = M('Member')->where(['id'=>$customerid-10000])->getField('apikey');
        $mysign=md5('customerid='.$customerid.'&status='.$status.'&sdpayno='.$sdpayno.'&sdorderno='.$sdorderno.'&total_fee='.$total_fee.'&paytype='.$paytype.'&'.$userkey);
        file_put_contents('WeiXinSmnotifyurl.txt',"code=".$status."\r\n\r\n",FILE_APPEND);
        file_put_contents('WeiXinSmnotifyurl.txt',"order_id=".$sdorderno."\r\n\r\n",FILE_APPEND);
            if($status=='1'){
                $this->EditMoney($sdorderno, 'WeiXinSm', 0);
                $this->AllCallback($sdorderno);
                exit('success');

            } else {
                exit('fail');
            }
    }


    protected function _createForm($url, $data)
    {

        header($url.'?'.http_build_query($data));

        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onLoad="document.pay.submit()">
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