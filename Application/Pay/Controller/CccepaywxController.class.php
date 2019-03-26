<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class CccepaywxController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'Cccepaywx';
    }

    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（Cccepaywx）',
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
        $res = $re['data'];

        //$callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        date_default_timezone_set("PRC");

        $arraystr = array(
            'merno'	=>  $return['mch_id'] ,
            'sn'=>  $return['orderid'],//必填，您的订单号, 30个字符以内
            'acode'=>  'WX',
            'money'	=>  $return["amount"],//必填，订单金额,
            'urlCallback'	=>$notifyurl
        );

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $return["signkey"]);
        $result =  $this->httpRequest($return["gateway"],$arraystr);
        $this->orderadd1($res);
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
        return MD5(rtrim($sign,'&').$key);
    }
    private function httpRequest( $url, $data=array())
    {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式

        curl_setopt($ch, CURLOPT_POSTFIELDS,  $query_string = http_build_query($data));
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }

    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');

        $data  = $_POST;
        $sign = $data['sign'];
        unset($data['sign']);
        if(empty($data['sn'])){
            die('fail');
        }
        $order_where = [
             'pay_status'          => 0,
             'pay_orderid'         => $data['sn']
        ];

        $m_Order =  M("Order");
        $order = $m_Order->where($order_where)->find();
        $signs =$this->_createSign($data,  $order["key"]);

        if($sign !=$signs){
            die('ERROR');
        }
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents('ckurl.txt',"order_id=".$data['sn']."\r\n\r\n",FILE_APPEND);

        $PayName = $this->types;
        $pay_amount = $data['money'];
        $this->clgm($data['sn'],$pay_amount,$PayName);

        $this->EditMoney($data['sn'], $PayName, 0);
        die('SUCCESS');
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
            'mch_id'	=>  $channel_account["mch_id"],//商户编号
            'out_trade_no'=>  $order['pay_orderid'],//商户订单号
        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->_createSign($arraystr,  $channel_account["signkey"]);
        $url= $this->httpRequest($Channel["gateway"].'Pay_pay_orderQurey.html' ,$arraystr,'post');
        if($url['status']==0){
            //成功/关闭/已退款/失败
            if($url['pay_status']== 1 || $url['pay_status']== 2  ){
                $this->EditMoney( $order['pay_orderid'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }
    function  callbackurl(){
        echo '支付成功';die;
    }
}