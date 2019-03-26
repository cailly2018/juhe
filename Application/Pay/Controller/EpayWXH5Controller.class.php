<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/10/11
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class EpayWXH5Controller extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $types ='EpayWXH5';

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$types.'_notifyurl.html';

        $parameter = array(

            'code' => $types,
            'title' => '收银台支付（景程微信H5）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $callbackurl = $this->_site . 'Pay_'.$types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        //获取请求的url地址
        $url=$return["gateway"];


        $arraystr = array(
            "parter" => $return["mch_id"],//商户编号
            'type' => 1007,
            "value" => $return["amount"],//订单金额
            "orderid" => (string) $return['orderid'],// 提交时间
            "callbackurl" => $return['notifyurl'],
            "hrefbackurl" =>'',
            "payerIp" =>'',
            "attach" => '',
        );

        //
        $url = "parter=". $arraystr['parter']."&type=". $arraystr['type'] ."&value=". $arraystr['value']. "&orderid=".  $arraystr['orderid']."&callbackurl=". $arraystr['callbackurl'];
        //签名
        $sign	= md5($url. $return['signkey']);

        //最终url
        $url	= $return["gateway"] . "?" . $url . "&sign=" .$sign. "&hrefbackurl=". $arraystr['hrefbackurl'];


        //页面跳转
        header("location:" .$url);



        /*
           $arraystr['sign']=$sign;
           $res=$this->_createForm($url,$arraystr);
           echo $res;
           return;*/
    }

    public function callbackurl(){
        $types ='EpayWXH5';
        file_put_contents($types.'callbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["sdorderno"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], $types, 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid        = trim($_GET['orderid']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        if(empty($orderid)){
            die("opstate=-1");		//签名不正确，则按照协议返回数据
        }

        $types ='EpayWXH5';
        file_put_contents($types.'notifyurl.txt',"order_id=".$orderid."\r\n\r\n",FILE_APPEND);'
        ';
        $this->EditMoney($orderid, $types, 0);


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
                    <form method="get" action="' . $url . '" name="pay">';

        foreach ($data as $k => $vo) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $vo . '">';
        }

        $str .= '</form>
                    <body>
                </html>';
        return $str;
    }

}