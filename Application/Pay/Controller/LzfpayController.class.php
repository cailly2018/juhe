<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;

class LzfpayController extends PayController{

    protected $types;
    protected $merchantPrivateKey;
    public function __construct(){
        parent::__construct();
        $this->types = 'Lzfpay';
    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';

        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（龙支付银联扫码支付）',
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

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        date_default_timezone_set("PRC");

        $arraystr = [

            'merchantid' => $return["mch_id"],
            'merc_ord_no' =>$return['orderid'],//交易流水号
            'version' => '1.0',
            'reqtime' =>  date('YmdHms',time()),
            'paytype' =>  'UNIONPAY',
            'tranamt' =>  $return["amount"],
            'notifyurl' => $notifyurl,
            'callbackurl' => $return["callbackurl"],
        ];
        $arraystr['sign'] =  $this->encrypt($arraystr);

        $result =  $this-> doPost($return['gateway'].'api/single/scan', $arraystr);

        if($result['returncode'] == '0000'){
            //请求上游成功了才把数据写到表里
            $this->orderadd1($re['data']);
            //支付完成的逻辑, 反悔了一个html文本，直接在界面上将这个html文本渲染即可
            if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){
                $data['orderid'] = $arraystr['out_trade_no'];
                $data['amount'] = $return["amount"];
                $data['out_trade_id'] =$return['orderid'];
                $data['datetime'] = date('Y-n-d H:i:s',time());
                $data['subject'] =  $arraystr['title'];
                $this->showQRcode( $result['codeurl'], $data, 'alipay',1);

            }else{
                import("Vendor.phpqrcode.phpqrcode", '', ".php");
                $QR = "Uploads/codepay/" . $return["orderid"] . ".png"; //已经生成的原始二维码图
                \QRcode::png($result['codeurl'], $QR, "L", 20);

                echo json_encode(array('code'=>0, 'msg'=>'调起成功', 'payurl'=> $this->_site . $QR));die;
            }

        }else{
            echo json_encode(array('code'=>1, 'msg'=>'调起失败','payurl'=>''));die;

        }

    }
    /**
     * 加密方法
     * @param $data
     * @return string
     */
    protected function encrypt($data){

        $private_key = file_get_contents(BASE_PATH.'/rsa_private_key.pem');
        if (empty($private_key))
        {
            $this->showmessage('密钥不存在误');
        }

        $pkeyid = openssl_get_privatekey($private_key);
        if (empty($pkeyid))
        {
            $this->showmessage('密钥有误');
        }

        $sign = '';
        openssl_sign($this->SortToString($data), $sign, $private_key, OPENSSL_ALGO_MD5);
        $signData = base64_encode($sign);//最终的签名
        openssl_free_key($pkeyid);
        return $signData;
    }
    //直接运行代码如下
    function SortToString($data){
        ksort($data);
        $temp = [];
        foreach($data as $i => $v){
            if(isset($v)){
                if(is_array($v)){
                    $temp[] = $i . "=" . $this->SortToString($v);
                }else{
                    $temp[] = $i . "=" . $v;
                }
            }
        }
        return join("&", $temp);
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
    /**
     * @desc 默认post请求
     * @access public
     * @return array
     */
    public function doPost( $url, $params=array(), $method='post' )
    {
        return $this->httpRequest( $url, $params, $method );
    }
    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array(), $method = 'post', $connectTimeout=5, $readTimeout=10 )
    {
        header("Content-Type:text/html; charset=GBK");
        if( !function_exists('curl_init') )
            return 901 ;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $connectTimeout + $readTimeout );
        curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'API PHP5 Client (curl) ' . phpversion() );

        if( is_array($params) || is_object($params) )
            $query_string = http_build_query($params);
        else
            $query_string = $params;

        $method = strtolower($method);
        switch( $method )
        {
            case 'get':
                if( false === strstr( $url, '?' ) )
                    $url = $url.'?'.$query_string;
                else
                    $url = $url.'&'.$query_string;
                curl_setopt($ch, CURLOPT_URL, $url );
                break;
            case 'post':
                curl_setopt( $ch, CURLOPT_URL, $url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
                break;
            default:
                return  900 ;
                break;
        }
        $starttime = microtime(true);
        $result = curl_exec($ch);

        $endtime = microtime(true);
        $this->exectime = $endtime-$starttime;
        $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $curl_error = curl_error($ch);
        curl_close($ch);
        if( $curl_error )
        {
            return $curl_error;
        }
        if( $httpcode != 200 )
        {
            return $httpcode;
        }
        $result = iconv("UTF-8", "GBK//IGNORE",$result);

        return json_decode($result,true);

    }


    // 服务器点对点返回
    public function notifyurl(){
        header('Content-Type:text/html;charset=GB2312');
        $orderid= $_POST['merc_ord_no'];
        $status= $_POST['status'];
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$orderid.'---'.$status."\r\n\r\n",FILE_APPEND);
        if($status== '00' ){
            if(empty($orderid)){
                die('FAIL');
            }
            $PayName = $this->types;
            $this->EditMoney($orderid,$PayName, 0);
            die('SUCCESS');
        }
        die('FAIL');
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
            'merchantid'	=> $channel_account["mch_id"],
            'merc_ord_no'	=>  $order["pay_orderid"],//商户编号
            'version' => '1.0',
            'reqtime' =>  date('YmdHms',time()),
        );

        $dats['sign'] =  $this->encrypt($arraystr);

        $result =  $this-> doPost($Channel['gateway'].'api/single/query', $dats);

        $result = json_decode($result, true);

        // payResult -1未支付, 0已经支付, 1支付失败
        if($result['status']=='00'){
            //成功/关闭/已退款/失败
            $this->EditMoney($result['userOrderId'], $this->types, 0);
            return 1;
        }
        return 2;
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