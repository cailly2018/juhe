<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/10/11
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class WLPayWxH5Controller extends PayController{
    protected $types;
    public function __construct(){
        parent::__construct();
        $this->types = 'WLPayWxH';
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(

            'code' => $this->types,
            'title' => '收银台支付（网络支付微信H5）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        //获取请求的url地址
        $url=$return["gateway"];

        $arraystr = array(
            'p0_Cmd'	=>  'Buy',
            'p1_MerId'	=>  $return["mch_id"],//商户编号
            'p2_Order'	=>  $return['orderid'],//商户订单号
            'p3_Amt'	=>  $return["amount"],//订单金额,
            'p4_Cur'	=>  'CNY',//交易币种
            'p5_Pid'	=>  'TEST',//商品名称
            'p6_Pcat'	=>  'XUNI',//商品种类
            'p7_Pdesc'	=>  'no',//商品描述
            'p8_Url'	=>  $notifyurl,//商户接收支付成功数据的地址
            'p9_SAF'	=>   0,//送货地址
            'pa_MP'		=>  'no',//商户扩展信息
            'pd_FrpId'	=>   'wxwap',//支付通道编码
            'pr_NeedResponse'=>  1 //应答机制
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        foreach ($arraystr as $key=>$v ){
            $sbOld .= $v;
        }

        $arraystr['hmac']=$this->HmacMd5($sbOld,$return['signkey']);
        $res=$this->_createForm($url,$arraystr);
        echo $res;
        return;
    }

    private function HmacMd5($data,$key)
    {
        //需要配置环境支持iconv，否则中文参数不能正常处理
        $key = iconv("GB2312","UTF-8",$key);
        $data = iconv("GB2312","UTF-8",$data);

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    public function callbackurl(){

        file_put_contents($this->types.'callbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["sdorderno"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], $this->types, 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid        = trim($_REQUEST['r6_Order']);
        $code        = trim($_REQUEST['r1_Code']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        if(empty($orderid) || $code !=1){
            die("fail");
        }

        file_put_contents($this->types.'notifyurl.txt',"order_id=".$orderid."\r\n\r\n",FILE_APPEND);'
        ';
        $this->EditMoney($orderid, $this->types, 0);
        die("success");
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