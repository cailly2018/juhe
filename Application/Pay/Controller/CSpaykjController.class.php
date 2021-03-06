<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/14
 * Time: 16:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class CSpaykjController extends PayController{

    public function __construct(){
        parent::__construct();
    }

    public function Pay($array){

        $types ='CSpaykj';

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$types.'_notifyurl.html';

        $parameter = array(

            'code' => $types,
            'title' => '收银台支付（CSPAY快捷支付）',
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
            "mch_id" => $return["mch_id"],//商户编号
            'total_fee' =>$return["amount"]*100,//交易金额
            "service" => 'SAND_FAST',//交易方式
            "out_trade_no" =>$return['orderid'],// 提交时间
            "sign_type" => 'MD5',
            "notify_url" => $return['notifyurl'],
            "body" =>'商品',
          /*  "in_cust_id" =>'',
            "in_acct_id" => '',*/
            "return_url" => $callbackurl,
            "realIp" => get_client_ip(),
          /*  "bankno" => '',*/
        );
        //签名
        $arraystr['sign']=$this->_createSign($arraystr,$return['signkey']);

        file_put_contents('cs.txt',"order_id=".json_encode($arraystr)."\r\n\r\n",FILE_APPEND);

        $res=$this->request_post($return["gateway"],$arraystr);
        return;
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

        return MD5($sign.'key='.$key);
    }



    // 服务器点对点返回
    public function notifyurl(){

        header('Content-Type:text/html;charset=GB2312');
        $orderid        = trim($_REQUEST['out_trade_no']);
        $opstate        = trim($_REQUEST['tradeStatus']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        if(empty($orderid)){
            die("fail");		//签名不正确，则按照协议返回数据
        }

        $types ='CSpaykj';
        file_put_contents($types.'notifyurl.txt',"order_id=".$orderid."\r\n\r\n",FILE_APPEND);
        if($opstate=='TRADE_SUCCESS') {
            $this->EditMoney($orderid, $types, 0);
            //$this->AllCallback($orderid);
            die("success");
        }
        die("fail");
    }
    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 'Content-type:text/html;charset=utf-8');//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $data = json_decode($data,true);
        echo  $this->_createForm($data["payinfo"]);
        return;
    }

    protected function _createForm($url)
    {

        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onload="javascript:document.forms[0].submit()">';
            $str .= $url;

            $str .= '
                    <body>
                </html>';
        return $str;
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