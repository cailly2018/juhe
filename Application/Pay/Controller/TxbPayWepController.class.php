<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/14
 * Time: 16:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class TxbPayWepController extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $types ='TxbPayWep';

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$types.'_notifyurl.html';

        $parameter = array(

            'code' => $types,
            'title' => '收银台支付（提现宝支付宝H5）',
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

        $arraystr = array(
            "version" => '1.0',
            "spid" => $return["mch_id"],//商户编号
            'spbillno' => (string) $return['orderid'],
            "tranAmt" => $return["amount"]*100,//交易金额
            "payType" => 'pay.alipay.wap',
            "notifyUrl" => $return['notifyurl'],
            "backUrl" => $callbackurl,
            "productName" =>'商品',
        );
        //签名.
        $arraystr['sign']=$this->_createSign($arraystr,$return['signkey']);


        $res=$this->_createForm($return["gateway"],$arraystr);
        echo $res;
        return;
    }


    // 服务器点对点返回
    public function notifyurl(){

       //接收传送的数据
        $fileContent = file_get_contents("php://input");

         ### 把xml转换为数组
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        //先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。
        $value_array = json_decode(json_encode(simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOCDATA)), true);


        $orderid        = trim($value_array['spbillno']);
        $opstate        = trim($value_array['retcode']);

        $types ='TxbPayWep';
        file_put_contents($types.'notifyurl.txt',"order_id=".$orderid."\r\n\r\n",FILE_APPEND);' ';
        if($opstate=='0' ||$opstate==0 ) {
            $this->EditMoney($orderid, $types, 0);
            $this->AllCallback($orderid);
            die("success");
        }
        die("fail");
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
        $vos ='<xml>';
        foreach ($data as $k => $vo) {
            $vos.= '<'.$k.'>'.$vo.'</'.$k.'>';
        }

        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onLoad="document.pay.submit()">
                    <form method="post" action="' . $url . '" name="pay">';

        $str.='<input type="hidden" name="req_data" value="' . $vos . '</xml>">';

        $str .= '</form>
                    <body>
                </html>';
        return $str;
    }
}