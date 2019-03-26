<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/14
 * Time: 16:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class TxbPsmController extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $types ='TxbPsm';

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$types.'_notifyurl.html';

        $parameter = array(

            'code' => $types,
            'title' => '收银台支付（提现宝支付宝扫码）',
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
            "service" => 'pay.alipay.native',
            "version" => '2.0',
            "charset" => 'UTF-8',
            "sign_type" => 'MD5',
            "mch_id" => $return["mch_id"],//商户编号
            'out_trade_no' => (string) $return['orderid'],
            "device_info" => 'test',
            "body" => '商品',
            "attach" => '商品',
            "total_fee" => $return["amount"]*100,//交易金额
            "mch_create_ip" => '14.192.9.127',//订单生成的机器 IP
            "notify_url" => $return['notifyurl'],
            "limit_credit_pay" => 0,
            "nonce_str" => 'fiisfofpihfpijfojfi',
        );
        //签名.
        $arraystr['sign']=$this->_createSign($arraystr,$return['signkey']);


        $res=$this->request_post($return["gateway"],$arraystr);


        $postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);

        $jsonStr = json_encode($postObj);
        $data = json_decode($jsonStr,true);


        $data['orderid'] = $arraystr['out_trade_no'];
        $data['amount'] = $return["amount"];
        $data['out_trade_id'] = $arraystr['out_trade_no'];
        $data['datetime'] = date('Y-n-d H:i;s',time());
        $data['subject'] = '商品';

        $this->showQRcode($data['code_url'], $data, 'alipay',$data['code_img_url']);


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

        $orderid        = trim($value_array['out_trade_no']);
        $opstate        = trim($value_array['status']);

        $types ='TxbPsm';
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
            if($k!='sign_type'){
                $sign .= $k.'='.$vo.'&';
            }
        }
        return strtoupper(MD5($sign.'key='.$key));
    }

    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $vos ='<xml>';
        foreach ($param as $k => $vo) {
            $vos.= '<'.$k.'>'.$vo.'</'.$k.'>';
        }
        $data = $vos.'</xml>';

        $ch = curl_init();
        $header[] = "Content-type: text/xml";//定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
        curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
        curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义请求类型
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //定义提交的数据，这里是XML文件
        $result = curl_exec($ch);
        curl_close($ch);//关闭
        return $result;

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