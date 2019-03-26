<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/10/11
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;

class XinzhifuWXH5Controller extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_XinzhifuWXH5_notifyurl.html';
        $parameter = array(
            'code' => 'Xinzhifu',
            'title' => '收银台支付（星支付wap）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $callbackurl = $this->_site . 'Pay_XinzhifuWXH5_callbackurl.html?order_id='.$return['orderid'];
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        //获取请求的url地址
        $url=$return["gateway"];

        $arraystr = array(
            "pay_version" => "vb1.0",//版本号
            'pay_memberid' => $return["mch_id"],//商户编号
            "pay_orderid" => $return["orderid"],//商户订单号
            "pay_applydate" => date('yyyyMMddHHmmss',time()),// 提交时间
            "pay_bankcode" => 1005 ,//  银行编码
            "pay_notifyurl" => $return['notifyurl'],
            "pay_amount" => number_format($return["amount"],2,'.',''),//订单金额
            "pay_md5sign" => '',//MD5签名
            "pay_attach" => '',
            "pay_agent" => '',
            "pay_productname" => '',
            "pay_productnum" => '',
            "pay_productdesc" => '',
            "pay_producturl" => '',
        );


        $urls = "pay_memberid=". $arraystr['pay_memberid']."&pay_bankcode=".$arraystr['pay_bankcode']."&pay_amount=". $arraystr['pay_amount']. "&pay_orderid=". $arraystr['pay_orderid']."&pay_notifyurl=".$arraystr['pay_notifyurl'];
        //签名
        $sign	= md5($urls. $return['signkey']);
        $arraystr['pay_md5sign']=$sign;
        $res=$this->_createForm($url,$arraystr);
        echo $res;
        return;
    }

    public function callbackurl(){
        file_put_contents('XinzhifuWXH5callbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["sdorderno"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], 'XinzhifuWXH5', 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl(){
        $orderid =I('request.orderid');
        $ovalue =I('request.ovalue');
        $sysorderid =I('request.sysorderid');
        $opstate =I('request.opstate');
        $attach =I('request.attach');
        $sign =I('request.sign');
        file_put_contents('Xinzhifunotifyurl.txt',"order_id=".$orderid."\r\n\r\n",FILE_APPEND);
        if($opstate==0){
            $this->EditMoney($orderid, 'Xinzhifu', 0);
            $this->AllCallback($orderid);
            exit('success');
        } else {
            exit('fail');
        }
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