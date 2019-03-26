<?php
/**
 * Created by PhpStorm.
 * User: mapeijian
 * Date: 2018-04-11
 * Time: 17:37
 */
namespace Pay\Controller;

class YeeBankController extends PayController
{
    private $gateway = 'https://open.yeepay.com/yop-center';
    //商户私钥文件路径
    private $_private_key = '';
    //易宝公钥文件路径
    private $_yop_public_key = '';
    //直连参数
    protected $_bank_code = array(
        "ICBC_B2C"=>'工商银行',
        "CMBCHINA_B2C"=>'招商银行',
        "CCB_B2C" =>'建设银行',
        "BOCO_B2C"=>'交通银行',
        "CIB_B2C"=>'兴业银行',
        "CMBC_B2C"=>'中国民生银行',
        "CEB_B2C"=>'光大银行',
        "BOC_B2C"=>'中国银行',
        "PINGANBANK_B2C"=>'平安银行',
        "ECITIC_B2C"=>'中信银行',
        "SDB_B2C"=>'深圳发展银行',
        "GDB_B2C"=>'广发银行',
        "SHB_B2C"=>'上海银行',
        "SPDB_B2C"=>'上海浦东发展银行',
        "HXB_B2C"=>'华夏银行',
        "BCCB_B2C"=>'北京银行',
        "ABC_B2C"=>'中国农业银行',
        "PSBC_B2C"=>'中国邮政储蓄银行',
        "BJRCB_B2C"=>'北京农商银行',
        "SRCB_B2C"=>'上海农村商业银行',
        "HZBANK_B2C"=>'杭州银行',
        "NBCB_B2C" =>'宁波银行',
    );

    public function __construct(){
        parent::__construct();
        require_once (LIB_PATH."Org/Util/Yeepay/YopClient3.php");
        require_once (LIB_PATH."Org/Util/Yeepay/Util/YopSignUtils.php");
    }

    /**
     *  发起支付
     */
    public function Pay($array)
    {
        $orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site ."Pay_YeeBank_notifyurl.html"; //异步通知
        $callbackurl = $this->_site . 'Pay_YeeBank_callbackurl.html'; //跳转通知
        $bank_code = I("request.bank_code",'');
        if($bank_code) {
            if (!array_key_exists($bank_code, $this->_bank_code)) {
                $bank_code = '';
            }
        }
        $parameter = array(
            'code' => 'YeeBank',
            'title' => '易宝网银支付',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        );
        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        if($return['unlockdomain'] != '') {
            $url = $return['unlockdomain'].'/Pay_YeeBank_topay_orderid_'.$return["orderid"].'_bank_code_'.$bank_code.'_body_'.urlencode($body).'.html';
            echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
            exit;
        } else {
            $appid = substr($return['appid'],4);
            $this->_private_key = file_get_contents('./cert/Yee/'.$appid.'/certificate_pri.pem');
            $this->_yop_public_key = file_get_contents('./cert/Yee/'.$appid.'/yee_pub.pem');
            $request = new \Org\Util\Yeepay\YopRequest($return['appid'], $this->_private_key,$this->gateway,$this->_yop_public_key);
            $request->addParam("parentMerchantNo", $return['mch_id']);
            $request->addParam("merchantNo", $return['mch_id']);
            $request->addParam("orderId", $return['orderid']);
            $request->addParam("orderAmount", $return['amount']);
            $request->addParam("redirectUrl", $callbackurl);
            $request->addParam("notifyUrl", $notifyurl);
            $goodsParamExt = ['goodsName'=>$body, 'goodsDesc'=>$body];
            $request->addParam("goodsParamExt", json_encode($goodsParamExt,JSON_UNESCAPED_UNICODE));

            $response = \Org\Util\Yeepay\YopClient3::post("/rest/v1.0/std/trade/order", $request);;
            if($response->validSign == 1){
                //取得返回结果
                $data = $this->object_array($response);
                $token = $data['result']['token'];
                $cashter = array(
                    "merchantNo" => $return['mch_id'] ,
                    "token" => $token,
                    "timestamp" => time(),
                    "directPayType" => $bank_code,
                    "cardType" => '',
                    "userNo" => '',
                    "userType" => '',
                    "ext" => '',
                );
                $getUrl = $this->getUrl($cashter, $this->_private_key);
                //$getUrl = str_replace("&timestamp","&amp;timestamp",$getUrl);
                $url = "https://cash.yeepay.com/cashier/std?" . $getUrl;
                header('Location: '.$url);
            } else {
                $this->showmessage('签名验证失败');
            }
        }
    }

    public function topay()
    {
        $orderid = I("get.orderid");
        if(!$orderid) {
            $this->showmessage("参数错误");
        }
        $order = M('order')->where(array('pay_orderid'=>$orderid))->find();
        if(empty($order)) {
            $this->showmessage("订单不存在");
        }
        if($order['pay_status'] != 0) {
            $this->showmessage("订单已支付");
        }
        $bank_code = I("get.bank_code",'');
        if($bank_code) {
            if (!array_key_exists($bank_code, $this->_bank_code)) {
                $bank_code = '';
            }
        }
        $body = urldecode(I("get.body",''));
        if($bank_code == '') {
            //$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            //$url = $http_type . $_SERVER['HTTP_HOST'].'/Pay_YeeBank_Rpay_orderid_'.$orderid.'_body_'.urlencode($body).'.html';
            ///header('Location: '.$url);
            //exit;
        }
        $notifyurl = $this->_site ."Pay_YeeBank_notifyurl.html"; //异步通知
        $callbackurl = $this->_site . 'Pay_YeeBank_callbackurl.html'; //跳转通知
        $appid = substr($order['account'],4);
        $this->_private_key = file_get_contents('./cert/Yee/'.$appid.'/certificate_pri.pem');
        $this->_yop_public_key = file_get_contents('./cert/Yee/'.$appid.'/yee_pub.pem');
        $request = new \Org\Util\Yeepay\YopRequest($order['account'], $this->_private_key, $this->gateway, $this->_yop_public_key);
        $request->addParam("parentMerchantNo", $order['memberid']);
        $request->addParam("merchantNo", $order['memberid']);
        $request->addParam("orderId", $orderid);
        $request->addParam("orderAmount", $order['pay_amount']);
        $request->addParam("redirectUrl", $callbackurl);
        $request->addParam("notifyUrl", $notifyurl);
        $goodsParamExt = ['goodsName'=>$body, 'goodsDesc'=>$body];
        $request->addParam("goodsParamExt", json_encode($goodsParamExt,JSON_UNESCAPED_UNICODE));

        $response = \Org\Util\Yeepay\YopClient3::post("/rest/v1.0/std/trade/order", $request);;
        if($response->validSign == 1) {
            //取得返回结果
            $data = $this->object_array($response);
            $token = $data['result']['token'];
            $cashter = array(
                "merchantNo" => $order['memberid'],
                "token" => $token,
                "timestamp" => time(),
                "directPayType" => $bank_code,
                "cardType" => '',
                "userNo" => '',
                "userType" => '',
                "ext" => '',
            );
            $getUrl = $this->getUrl($cashter, $this->_private_key);
            //$getUrl = str_replace("&timestamp","&amp;timestamp",$getUrl);
            $url = "https://cash.yeepay.com/cashier/std?" . $getUrl;
            echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
            exit;
        } else {
            $this->showmessage('签名验证失败');
        }
    }

    public function Rpay()
    {
        if(IS_POST) {
            $orderid = I("post.orderid");
            if(!$orderid) {
                $this->showmessage("参数错误");
            }
            $order = M('order')->where(array('pay_orderid'=>$orderid))->find();
            if(empty($order)) {
                $this->showmessage("订单不存在");
            }
            if($order['pay_status'] != 0) {
                $this->showmessage("订单已支付");
            }
            $bank_code = I("post.bank_code");
            if(!$bank_code) {
                $this->showmessage("银行代码不能为空");
            }
            $body = I("post.body");
            $notifyurl = $this->_site ."Pay_YeeBank_notifyurl.html"; //异步通知
            $callbackurl = $this->_site . 'Pay_YeeBank_callbackurl.html'; //跳转通知
            $appid = substr($order['account'],4);
            $this->_private_key = file_get_contents('./cert/Yee/'.$appid.'/certificate_pri.pem');
            $this->_yop_public_key = file_get_contents('./cert/Yee/'.$appid.'/yee_pub.pem');
            $request = new \Org\Util\Yeepay\YopRequest($order['account'], $this->_private_key, $this->gateway, $this->_yop_public_key);
            $request->addParam("parentMerchantNo", $order['memberid']);
            $request->addParam("merchantNo", $order['memberid']);
            $request->addParam("orderId", $orderid);
            $request->addParam("orderAmount", $order['pay_amount']);
            $request->addParam("redirectUrl", $callbackurl);
            $request->addParam("notifyUrl", $notifyurl);
            $goodsParamExt = ['goodsName'=>$body, 'goodsDesc'=>$body];
            $request->addParam("goodsParamExt", json_encode($goodsParamExt,JSON_UNESCAPED_UNICODE));

            $response = \Org\Util\Yeepay\YopClient3::post("/rest/v1.0/std/trade/order", $request);;
            if($response->validSign == 1) {
                //取得返回结果
                $data = $this->object_array($response);
                $token = $data['result']['token'];
                $cashter = array(
                    "merchantNo" => $order['memberid'],
                    "token" => $token,
                    "timestamp" => time(),
                    "directPayType" => $bank_code,
                    "cardType" => '',
                    "userNo" => '',
                    "userType" => '',
                    "ext" => '',
                );
                $getUrl = $this->getUrl($cashter, $this->_private_key);
                //$getUrl = str_replace("&timestamp","&amp;timestamp",$getUrl);
                $url = "https://cash.yeepay.com/cashier/std?" . $getUrl;
                echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
                exit;
            } else {
                $this->showmessage('签名验证失败');
            }

        } else {
            $orderid = I("orderid");
            if(!$orderid) {
                $this->showmessage("参数错误");
            }
            $body = I("body");
            $order = M('order')->where(array('pay_orderid'=>$orderid))->find();
            if(empty($order)) {
                $this->showmessage("订单不存在");
            }
            if($order['pay_status'] != 0) {
                $this->showmessage("订单已支付");
            }
            $channel = M('channel')->where(['id'=>$order['channel_id']])->find();
            if($channel['unlockdomain']) {
                $rpay_url = $channel['unlockdomain']. '/Pay_YeeBank_topay.html';
            } else {
                $rpay_url = $this->_site . 'Pay_YeeBank_topay.html';
            }
            $bank_array = [
                "ICBC_B2C"=>'102',
                "CMBCHINA_B2C"=>'3001',
                "CCB_B2C" =>'105',
                "BOCO_B2C"=>'301',
                "CIB_B2C"=>'309',
                "CMBC_B2C"=>'305',
                "CEB_B2C"=>'303',
                "BOC_B2C"=>'104',
                "PINGANBANK_B2C"=>'SPAB',
                "ECITIC_B2C"=>'302',
                "SDB_B2C"=>'SDB',
                "GDB_B2C"=>'306',
                "SHB_B2C"=>'SHB',
                "SPDB_B2C"=>'310',
                "HXB_B2C"=>'304',
                "BCCB_B2C"=>'370',
                "ABC_B2C"=>'103',
                "PSBC_B2C"=>'403',
                "BJRCB_B2C"=>'北京农商银行',
                "SRCB_B2C"=>'上海农村商业银行',
                "HZBANK_B2C"=>'杭州银行',
                "NBCB_B2C" =>'NBCB',
            ];
            $this->assign('rpay_url', $rpay_url);
            $this->assign('bank_array', $bank_array);
            $this->assign('order', $order);
            $this->assign('orderid', $orderid);
            $this->assign('body', $body);
            $this->display('Yee/pc');
        }

    }
    /**
     * 页面通知
     */
    public function callbackurl()
    {
        $Order      = M("Order");
        $orderid    = I('request.orderId', '');
        $pay_status = $Order->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    /**
     *  服务器通知
     */
    public function notifyurl()
    {
        //file_put_contents('./Data/notify.txt',"【".date('Y-m-d H:i:s')."】\r\n".file_get_contents("php://input")."\r\n\r\n",FILE_APPEND);
        $data = $_REQUEST["response"];
        $appid = substr($_REQUEST['customerIdentification'],4);
        $this->_private_key = file_get_contents('./cert/Yee/'.$appid.'/certificate_pri.pem');
        $this->_yop_public_key = file_get_contents('./cert/Yee/'.$appid.'/yee_pub.pem');
        $response = \Org\Util\Yeepay\Util\YopSignUtils::decrypt($data, $this->_private_key, $this->_yop_public_key);
		$response = json_decode($response, true);
        if($response['status'] == 'SUCCESS') {
            $this->EditMoney($response['orderId'], 'YeeBank', 0);
            exit('SUCCESS');
        }
    }

    public function getString($response){

        $str="";

        foreach ($response as $key => $value) {
            $str .= $key . "=" . $value . "&";
        }
        $getSign = substr($str, 0, strlen($str) - 1);
        return $getSign;
    }

    public function getUrl($response,$private_key)
    {
        $content=$this->getString($response);
        $sign=\Org\Util\Yeepay\Util\YopSignUtils::signRsa($content,$private_key);
        $url=$content."&sign=".$sign;
        return  $url;
    }

    public function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }
}
