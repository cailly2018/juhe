<?php
/**
 * Created by PhpStorm.
 * User: cailly
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Pay\Controller;
use Org\Util\Ysenc;


class BA2payController extends PayController{

    protected $types;
    protected $merchantPrivateKey;
    public function __construct(){
        parent::__construct();
        $this->types = 'BA2pay';

    }

    public function Pay($array){

        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $parameter = array(
            'code' => $this->types,
            'title' => '收银台支付（bnzfbFix银行）',
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
        $notifyurl = $this->_site . 'Pay_'.$this->types.'_notifyurl.html';
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

      /*  $callbackurl = $this->_site . 'Pay_'.$this->types.'_callbackurl.html';
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;*/

        date_default_timezone_set("PRC");
        $arraystr = [
            'mchid' => $return["mch_id"],//'00010001',//
            'submchid' =>  $return['appid'],
            'orderno' =>$return['orderid'],//交易流水号
            'waytype' => "bnzfbFix",
            'amount' =>  $return["amount"],
            'notifyurl' => $notifyurl
        ];

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->encrypt($arraystr);
        $result =  $this->request($return['gateway'], $arraystr, 'post' );
        $result = json_decode($result, true);

        if($result['code'] == 1){

            //请求上游成功了才把数据写到表里
            $this->orderadd1($re['data']);

            //支付完成的逻辑, 反悔了一个html文本，直接在界面上将这个html文本渲染即可
            $notifyurl = $result['data']['url'];
            if($return['pay_memberid'] ==10112 || $return['pay_memberid'] == '10112'){
                //生成二维码
                import("Vendor.phpqrcode.phpqrcode", '', ".php");

                $QR  = "Uploads/charges/" . time() . ".png"; //已经生成的原始二维码图
                \QRcode::png($notifyurl, $QR, "L", 20, 1);

                $this->assign("msg", '订单以支付金额为准');
                $this->assign("msg1", ' 保存到手机 扫一扫付款');
                $this->assign("msg1", ' 保存到手机 扫一扫付款');
                $this->assign("title", '笔记本');
                $this->assign("imgurl", $this->_site .$QR);
                $this->assign("ddh", $orderid);
                $this->display("Charges/pay");
            }else{
                echo json_encode(array('code'=>0, 'msg'=>'调起成功', 'payurl'=>$notifyurl));die;
            }

        }else{
            echo json_encode(array('code'=>1, 'msg'=>'调起失败','payurl'=>''));die;

        }

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
     * 加密方法
     * @param $data
     * @return string
     */
    protected function encrypt($data){
        $private_content = file_get_contents(BASE_PATH.'/private.pem');
        $sign = '';
        openssl_sign($this->SortToString($data), $sign, $private_content, OPENSSL_ALGO_SHA1);
        $signData = base64_encode($sign);//最终的签名
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


    /**
     * @desc 默认post请求
     * @access public
     * @return array
     */
    public function request( $url, $params=array(), $method='post' )
    {
        return $this->httpRequest( $url, $params, $method );
    }

    /**
     * @desc 模拟请求
     * @access public
     */
    private function httpRequest( $url, $params=array(), $method = 'post', $connectTimeout=5, $readTimeout=10 )
    {
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
        return $result;
    }
    // 服务器点对点返回
    public function notifyurl(){

        header('Content-Type:text/html;charset=GB2312');
        $data   = $_POST['data'];
        $code    =(int)$_POST['code'];
        $orderid= $data['agentorderno'];
        //订单号为必须接收的参数，若没有该参数，则返回错误
        file_put_contents($this->types.'callbackurl.txt',"order_id=".$orderid.'---'.$code."\r\n\r\n",FILE_APPEND);

        if($code== 1){
            if(empty($orderid)){
                die('FAIL');
            }
            if($data['status']== "1"){
                $PayName = $this->types;
                $pay_amount = $data['totalamount'];
                $this->clgm($orderid,$pay_amount,$PayName);
                $this->EditMoney($orderid, $this->types, 0);
                die('SUCCESS');
            }else{
                die('FAIL');
            }
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
            'mchid'	=>  $channel_account["mch_id"],//商户编号
            'submchid'	=> $channel_account["mch_id"],//时间戳格式
            'orderno'	=> $order['pay_orderid'],//商户订单号
            'orderdate'	=>  date('Y-m-d',$order['pay_applydate']),//签名

        );
        #进行签名处理，一定按照文档中标明的签名顺序进行
        $arraystr['sign'] =$this->SortToString($arraystr);
        $url= $this->_createForm($Channel["gateway"] ,$arraystr);
        if($url['code']=='GP_00'){
            //成功/关闭/已退款/失败
            if($url['data']['status'] == '成功' &&$order['pay_orderid'] == $url['data']['businessnumber'] ){
                $this->EditMoney($url['data']['businessnumber'], $this->types, 0);
                return 1;
            }
        }
        return 2;
    }
    function  callbackurl(){
        echo '12';die;
        $orderid       = trim($_REQUEST['orderid']);
        $m_Order    = M("Order");
        $order_info = $m_Order->where(['pay_orderid' => $orderid])->find(); //获取订单信息
        if($order_info){
            header("Location: ".$order_info['pay_callbackurl']);
        }

    }
}