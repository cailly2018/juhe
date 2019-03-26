<?php
namespace Pay\Controller;
use Org\Util\Wft\ClientResponseHandler;
use Org\Util\Wft\PayHttpClient;
use Org\Util\Wft\RequestHandler;
use Org\Util\Wft\Utils;

class WftAliJspayController extends PayController{

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
        $notifyurl = $this->_site . 'Pay_WftAliJspay_notifyurl.html';

        $callbackurl = $this->_site . 'Pay_WftAliJspay_callbackurl.html';

        $parameter = array(
            'code' => 'WftAliJspay',
            'title' => '威富通支付（支付宝服务窗支付）',
            'exchange' => 100, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        $redirect_uri = $this->_site. "Pay_WftAliJspay_jsapi.html";
        $state = $return["orderid"];
        $url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=" . $return["appid"] . "&scope=auth_base&redirect_uri=". $redirect_uri."&state=".$state;
        redirect($url);
        exit();

    }

    public function jsapi()
    {
        $auth_code = I('get.auth_code');
        if(!$auth_code) {
            $this->showmessage('未获取auth_code');
        }
        $orderid = I('get.state');
        $Order = M("Order");
        $return = $Order->where(["pay_orderid"=>$orderid])->find();
        $rsaPrivateKey_path = './cert/Wft/'.$return['memberid'].'/rsaPrivateKey.txt';
        $alipayrsaPublicKey_path = './cert/Wft/'.$return['memberid'].'/alipayrsaPublicKey.txt';
        $rsaPrivateKey = file_get_contents($rsaPrivateKey_path);
        $alipayrsaPublicKey =  file_get_contents($alipayrsaPublicKey_path);
        vendor('Alipay.aop.AopClient');
        vendor('Alipay.aop.SignData');
        vendor('Alipay.aop.request.AlipaySystemOauthTokenRequest');
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $return['account'];
        $aop->rsaPrivateKey = $rsaPrivateKey;
        $aop->alipayrsaPublicKey = $alipayrsaPublicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($auth_code);
        $result = $aop->execute ( $request);
        $user_id = $result->alipay_system_oauth_token_response->user_id;
        if(!$user_id){
            $this->showmessage('获取支付宝用户信息失败');
        }
        $notifyurl = $this->_site . 'Pay_WftAliJspay_notifyurl.html';
        $callbackurl = $this->_site . 'Pay_WftAliJspay_callbackurl.html';
        $channel = M('channel')->where(['id'=>$return['channel_id']])->find();
        $account = M('channel_account')->where(['id'=>$return['account_id']])->find();
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
        $this->reqHandler->setParameter('service','pay.alipay.jspay');
        $this->reqHandler->setParameter('version','1.0');
        $this->reqHandler->setParameter('mch_id',$return['memberid']);
        $this->reqHandler->setParameter('out_trade_no',$return['pay_orderid']);
        $this->reqHandler->setParameter('body','普通支付');
        $this->reqHandler->setParameter('total_fee',$return['pay_amount']*100);
        $this->reqHandler->setParameter('mch_create_ip',$ip);
        $this->reqHandler->setParameter('notify_url',$notifyurl);
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));
        $this->reqHandler->setParameter('buyer_id',$user_id);
        $this->reqHandler->createSign();
        $data = Utils::toXml($this->reqHandler->getAllParameters());
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
        if($this->pay->call()){
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if($this->resHandler->isTenpaySign()){
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                    $pay_info = $this->resHandler->getParameter('pay_info');
                    $pay_info = json_decode($pay_info, true);
                    ?>
                    <script src="/Public/Front/js/jquery.min.js"></script>
                    <script type="application/javascript">
                        $(document).ready(function(){

                            tradePay("<?php echo $pay_info['tradeNO'];?>");
                        });
                        function ready(callback) {
                            if (window.AlipayJSBridge) {
                                callback && callback();
                            } else {
                                document.addEventListener('AlipayJSBridgeReady', callback, false);
                            }
                        }

                        function tradePay(tradeNO) {
                            ready(function(){
                                // 通过传入交易号唤起快捷调用方式(注意tradeNO大小写严格)
                                AlipayJSBridge.call("tradePay", {
                                    tradeNO: tradeNO
                                }, function (data) {
                                    if ("9000" == data.resultCode) {
                                        window.location.href = "http://<?php echo C("DOMAIN")?>/Pay_WftAliJspay_success.html?orderid=<?php echo $orderid; ?>";
                                    }
                                });
                            });
                        }
                    </script>
                    <?php
                }else{
                    $this->showmessage($this->resHandler->getParameter('err_msg'));
                }
            } else {
                $this->showmessage($this->resHandler->getParameter('message'));
            }
        }
        else {
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
            $this->EditMoney($_REQUEST["orderid"], 'WftAliJspay', 1);
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl()
    {

        //file_put_contents('./Data/notify.txt',"【".date('Y-m-d H:i:s')."】\r\n".file_get_contents("php://input")."\r\n\r\n",FILE_APPEND);
        $xml = file_get_contents('php://input');
        $this->resHandler->setContent($xml);
        $out_trade_no = $this->resHandler->getParameter('out_trade_no');
        if (!$out_trade_no) {
            echo('failure1');
            exit;
        }
        $order = M("Order")->where(["pay_orderid" => $out_trade_no])->find();
        if (empty($order)) {
            echo('failure2');
            exit;
        }

        $public_rsa_key_path = './cert/Wft/' . $order['memberid'] . '/public_rsa_key.txt';
        if (!file_exists($public_rsa_key_path)) {
            echo('failure3');
            exit;
        }
        $public_rsa_key = file_get_contents($public_rsa_key_path);
        $this->resHandler->setRSAKey("-----BEGIN PUBLIC KEY-----\n"
            . wordwrap($public_rsa_key, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----");
        if ($this->resHandler->isTenpaySign()) {
            if ($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                $this->EditMoney($out_trade_no, 'WftAliJspay', 0);
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
            <title>支付宝</title>
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
                            onclick="javascript:window.location.href='/Pay_WftAliJspay_callbackurl.html?orderid=<?php echo $orderid; ?>'">
                        支付成功！
                    </button>
                    <script>
                        setTimeout("tz();", 100);
                        function tz() {
                            window.location.href = "Pay_WftAliJspay_callbackurl.html?orderid=<?php echo $orderid; ?>";
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