<?php
namespace Pay\Controller;
use Org\Util\Wft\ClientResponseHandler;
use Org\Util\Wft\PayHttpClient;
use Org\Util\Wft\RequestHandler;
use Org\Util\Wft\Utils;

class WftWxJspayController extends PayController{

    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;

	public function __construct(){
		parent::__construct();
        $this->resHandler = new ClientResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new PayHttpClient();
	}

	
	public function Pay($array){

		$orderid = I("request.pay_orderid");
		$body = I('request.pay_productname');
		$parameter = array(
			'code' => 'WftWxJspay',
			'title' => '威富通支付（微信公众号支付）',
			'exchange' => 100, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
		);
        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        $redirect_uri = $this->_site. "Pay_WftWxJspay_jsapi.html";
        $state = $return["orderid"];
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $return["appid"] . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_base&state=" . $state . "#wechat_redirect";
        redirect($url);
        exit();
    }

    public function jsapi()
    {
        $code = I('get.code');
        $orderid = I('get.state');
        $notifyurl = $this->_site . 'Pay_WftWxJspay_notifyurl.html';
        $callbackurl = $this->_site . 'Pay_WftWxJspay_callbackurl.html';
        $Order = M("Order");
        $return = $Order->where("pay_orderid='" . $orderid . "'")->find();
        $channel = M('channel')->where(['id'=>$return['channel_id']])->find();
        $account = M('channel_account')->where(['id'=>$return['account_id']])->find();
        $urlObj["appid"] = $account['appid'];
        $urlObj["secret"] = $account['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $client = new \Org\Util\HttpClient();
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token";
        $res = $client->get($url,$urlObj);
        $data = json_decode($res, true);
        $openid = $data['openid'];
        //获取请求的url地址
        $this->reqHandler->setGateUrl($channel["gateway"]);
        $public_rsa_key_path = './cert/Wft/'.$return['memberid'].'/public_rsa_key.txt';
        $private_rsa_key_path = './cert/Wft/'.$return['memberid'].'/private_rsa_key.txt';
        if(!file_exists($public_rsa_key_path) || !file_exists($private_rsa_key_path)) {
            $this->showmessage('证书文件不存在');
        }
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $private_rsa_key = file_get_contents($private_rsa_key_path);
        $public_rsa_key =  file_get_contents($public_rsa_key_path);
        $this->reqHandler->setRSAKey("-----BEGIN RSA PRIVATE KEY-----\n"
            . wordwrap($private_rsa_key, 64, "\n", true).
            "\n-----END RSA PRIVATE KEY-----");
        $this->resHandler->setRSAKey("-----BEGIN PUBLIC KEY-----\n"
            .wordwrap($public_rsa_key, 64, "\n", true).
            "\n-----END PUBLIC KEY-----");
        $this->reqHandler->setSignType('RSA_1_256');
        $this->reqHandler->setParameter('sign_type','RSA_1_256');
        $this->reqHandler->setParameter('service','pay.weixin.jspay');
        $this->reqHandler->setParameter('version','1.0');
        $this->reqHandler->setParameter('mch_id',$return['memberid']);
        $this->reqHandler->setParameter('is_raw','1');
        $this->reqHandler->setParameter('body','普通支付');
        $this->reqHandler->setParameter('out_trade_no',$return['pay_orderid']);
        $this->reqHandler->setParameter('sub_openid',$openid);
        $this->reqHandler->setParameter('sub_appid',$account['appid']);
        $this->reqHandler->setParameter('total_fee',$return['pay_amount']*100);
        $this->reqHandler->setParameter('mch_create_ip',$ip);
        $this->reqHandler->setParameter('notify_url',$notifyurl);
        $this->reqHandler->setParameter('callback_url',$callbackurl);
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));
        $this->reqHandler->createSign();
        $data = Utils::toXml($this->reqHandler->getAllParameters());
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
        if($this->pay->call()){
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if($this->resHandler->isTenpaySign()){
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                    ?>
                    <html>
                    <head>
                        <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
                        <meta name="viewport" content="width=device-width, initial-scale=1"/>
                        <title>微信支付</title>
                        <script type="text/javascript">
                            //调用微信JS api 支付
                            function jsApiCall() {
                                WeixinJSBridge.invoke(
                                    'getBrandWCPayRequest',
                                    <?php echo $this->resHandler->getParameter('pay_info'); ?>,
                                    function (res) {
                                        astr = res.err_msg;
                                        if (astr.indexOf("ok") > 0) {
                                            window.location.href = "http://<?php echo C("DOMAIN")?>/Pay_WftWxJspay_success.html?orderid=<?php echo $orderid; ?>";
                                        }

                                    }
                                );
                            }
                            function callpay() {
                                if (typeof WeixinJSBridge == "undefined") {
                                    if (document.addEventListener) {
                                        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                                    } else if (document.attachEvent) {
                                        document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                                        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                                    }
                                } else {
                                    jsApiCall();
                                }
                            }
                            callpay();
                        </script>
                    </head>
                    <body>
                    </body>
                    </html>
                    <?php
                }else{
                    $this->showmessage($this->resHandler->getParameter('err_msg'));
                }
            } else {
                $this->showmessage($this->resHandler->getParameter('message'));
            }
        } else {
            $this->showmessage($this->resHandler->getParameter($this->pay->getErrInfo()));
        }
    }

    public function createRandomStr( $length = 32 ) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ ){
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

   
    protected function _createSign($data, $key){
        $sign = '';
        ksort($data);
        foreach( $data as $k => $vo ){
            $sign .= $k . '=' . $vo . '&';
        }
        return md5($sign . 'key=' . $key);
    }




	public function callbackurl(){
		
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["orderid"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["orderid"], 'WftWxJspay', 1);
        }else{
            exit("error");
        }
	}

	 // 服务器点对点返回
    public function notifyurl(){

        //file_put_contents('./Data/notify.txt',"【".date('Y-m-d H:i:s')."】\r\n".file_get_contents("php://input")."\r\n\r\n",FILE_APPEND);
        $xml = file_get_contents('php://input');
        $this->resHandler->setContent($xml);
        $out_trade_no = $this->resHandler->getParameter('out_trade_no');
        if(!$out_trade_no) {
            echo ('failure1');
            exit;
        }
        $order = M("Order")->where(["pay_orderid"=>$out_trade_no])->find();
        if(empty($order)) {
            echo ('failure2');
            exit;
        }

        $public_rsa_key_path = './cert/Wft/'.$order['memberid'].'/public_rsa_key.txt';
        if(!file_exists($public_rsa_key_path)) {
            echo ('failure3');
            exit;
        }
        $public_rsa_key =  file_get_contents($public_rsa_key_path);
        $this->resHandler->setRSAKey("-----BEGIN PUBLIC KEY-----\n"
            .wordwrap($public_rsa_key, 64, "\n", true).
            "\n-----END PUBLIC KEY-----");
        if($this->resHandler->isTenpaySign()){
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                $this->EditMoney($out_trade_no, 'WftWxJspay', 0);
                echo 'success';
                exit();
            } else {
                echo 'failure4';
                exit();
            }
        } else {
            echo 'failure5';
            exit();
        }
    }

    public function success()
    {
        $orderid = I("request.orderid", "");
        $Order = M("Order");
        $xx = $Order->where("pay_orderid = '" . $orderid . "'")->getField("xx");
        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>
            <title>微信支付</title>
        </head>
        <body>
        <br/>
        <font color="#9ACD32"><br/><br/><br/><br/>
            <div align="center">
                <?php
                if ($xx == 0) {
                    ?>
                    <button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;"
                            type="button"
                            onclick="javascript:window.location.href='/Pay_WftWxJspay_callbackurl.html?orderid=<?php echo $orderid; ?>'">
                        支付成功！
                    </button>
                    <script>
                        setTimeout("tz();", 100);
                        function tz() {
                            window.location.href = "Pay_WftWxJspay_callbackurl.html?orderid=<?php echo $orderid; ?>";
                        }
                    </script>
                <?php
                }else{
                ?>
                    <span style="color:#ff6c14; font-size:50px;font-weight:bold;">支付成功！</span>
                    <?php
                }
                ?>

            </div>
        </body>
        </html>
        <?php
    }
}