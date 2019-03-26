<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class ZfbController extends PayController{

    protected $types;

    public function __construct(){
        parent::__construct();
        $this->types = 'Zfb';
    }

    /**
     * @param $array
     */
    public function Pay($array){
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（支付宝H5）',
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
       /* $return["notifyurl"] || $return["notifyurl"] = $notifyurl;*/
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;

        date_default_timezone_set("PRC");


        $url = 'http://smallpay.nutbe.cn/Pay';
        $key= 'mmkgsTNbzSiuaSjCqoAaMJYQAeOZzlvT';
       /* $callbackurl = '';
        $notifyurl = '';*/
        $id = '2019101';
        $arraystr = array(
            'fxid'        => $id,
            'fxddh'         =>  time(),
            'fxdesc'          =>  '文具',
            'fxfee'       =>  0.1,
            'fxnotifyurl'     => $notifyurl,
            'fxbackurl'  =>$callbackurl,
            'fxpay'  =>'RedPsm',
            'fxnotifystyle'  =>1,
            'fxnoback'=>1,
            'fxip'  =>get_client_ip(),
        );

        $arraystr['fxsign'] = md5($id.$arraystr['fxddh'].$arraystr['fxfee'].$arraystr['fxnotifyurl'].$key);


        $re =  file_get_contents($url.'?'.http_build_query( $arraystr ));

        print_r($re);die;
        $re =  json_decode($re,true);
        if($re){
            if($re['status']==1){

                header("Location: ".$re ['payurl']);
            }
        }
        die;

        $arraystr = array(
            'parter'        => '0bfac5ba28d7d2b80b26f441cc3c2195',
            'value'         =>  2,
            'type'          =>  'ali',
            'orderid'       =>  $return['orderid'],
            'notifyurl'     => $notifyurl,
            'getcode'      =>0,
            'callbackurl'  =>$callbackurl,
        );

        $arraystr['sign'] =  $this->_sign($arraystr, '932ab1d2e08f21ddf01802d9bee17172');


        $rr=   $return["gateway"].'?'. http_build_query( $arraystr );


        header("Location: ".$rr);
       $R =   $this ->_createForm($return["gateway"], $arraystr);
        echo '<pre>';
       print_r($R);die;


    }
    function  test(){



        $arraystr = array(
            'parter'        => '0bfac5ba28d7d2b80b26f441cc3c2195',
            'value'         =>  2,
            'type'          =>  'ali',
            'orderid'       =>  $return['orderid'],
            'notifyurl'     => $notifyurl,
            'getcode'      =>0,
            'callbackurl'  =>$callbackurl,
        );

        $arraystr['sign'] =  $this->_sign($arraystr, '932ab1d2e08f21ddf01802d9bee17172');


        $rr=   $return["gateway"].'?'. http_build_query( $arraystr );


        header("Location: ".$rr);
        $R =   $this ->_createForm($return["gateway"], $arraystr);
        echo '<pre>';
        print_r($R);die;



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



    function _sign($data ,$keys){

        ksort($data);
        $signs =   urldecode( http_build_query( $data ) .'&key='. $keys );

        return md5($signs);

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


    function https_request($url, $method = 'GET', $data = array())
    {
        if ($method == 'POST') {
            //使用crul模拟
            $ch = curl_init();
            //禁用https
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        } else {
            if (ini_get('allow_fopen_url') == '1') {
                $result = file_get_contents($url);
            } else {
                //使用crul模拟
                $ch = curl_init();
                //允许请求以文件流的形式返回
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //禁用https
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch); //执行发送
                curl_close($ch);
            }
        }

        return json_decode($result,true);;
    }

    // 服务器点对点返回
    public function notifyurl(){


        header('Content-Type:text/html;charset=utf-8');
        $data = $_GET;

         echo '<pre>';
        print_r($data);

        $orderid   = trim($_REQUEST['out_trade_no']);
        $status    = trim($_REQUEST['callbacks']);

        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents('ckurl.txt',"order_id=".$_REQUEST["orderid"].'----'.$orderid.'---'.$status."\r\n\r\n",FILE_APPEND);

        if(empty($orderid)){
            die('fail');
        }
        if($status=='CODE_SUCCESS'){
            $this->EditMoney($orderid, $this->types, 0);
            die('success');
        }else{
            die('fail');
        }
    }
    function  callbackurl(){
        echo '支付成功';die;
        /*$orderid       = trim($_REQUEST['orderid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }*/

    }

}