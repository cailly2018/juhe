<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/22
 * Time: 19:26
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class YYpayWxsm2Controller extends PayController{

    protected $types;

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct(){
        parent::__construct();
        $this->types = 'YYpayWxsm2';
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");

        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(

            'code' => $this->types,
            'title' => '收银台支付（云易支付微信扫码2）',
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
        $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        header('Content-Type:text/html;charset=utf8');
        $arraystr = array(
            'mch_id'	=>  $return["mch_id"],//商户编号
            'order_num'	=>  $return['orderid'],//商户订单号
            'pay_amount'	=> $return["amount"]*100,//订单金额,
            'pay_type'	=>  'solid_code',//交易币种
            'notify_url'	=>  $notifyurl,
            'return_url'	=>  $callbackurl,
            'ext'=>  '商品',
            'body'=>  '商品'
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);

        $url= $return["gateway"] . "?".http_build_query($arraystr);
        if($return["gateway"]){
            $this->orderadd1($re['data']);
        }

        header("location:" .$url);
        echo $url;
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

    
    public function callbackurl(){

        file_put_contents($this->types.'callbackurl.txt',"order_id=".$_REQUEST["sdorderno"]."\r\n\r\n",FILE_APPEND);

        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["order_num"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["sdorderno"], $this->types, 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl(){

        header('Content-Type:text/html;charset=GB2312');
        $orderid   = trim($_REQUEST['order_num']);
        $status    = trim($_REQUEST['pay_status']);
        $pay_amount    = trim($_REQUEST['pay_amount']);

        file_put_contents('test.txt',"order_id=".$status."\r\n\r\n",FILE_APPEND);


        //订单号为必须接收的参数，若没有该参数，则返回错误
        if(empty($orderid)){
            die("fail1");		//签名不正确，则按照协议返回数据
        }
        if($status=='success'){
            //先修改订单金额
            // 通道名称
            $PayName = $this->types;
            $pay_amount = $pay_amount/100;
            $this->clgm($orderid,$pay_amount,$PayName);
            $this->EditMoney($orderid, $this->types, 0);
            die("success");

        }else{
            die("fail1");
        }
    }
}