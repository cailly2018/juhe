<?php
namespace Pay\Controller;

use Org\Util\HttpClient;
use Org\Util\Ysenc;

class LeGuoAliController extends PayController{
	
	public function __construct(){
		parent::__construct();
	}

	public function Pay($array){

		$orderid = I("request.pay_orderid");
		
		$body = I('request.pay_productname');
		$notifyurl = $this->_site . 'Pay_LeGuoAli_notifyurl.html';


		file_put_contents('20180709.txt',$_SERVER["HTTP_REFERER"]."\r\n\r\n",FILE_APPEND);

		$parameter = array(
			'code' => 'LeGuoAli',
			'title' => '收银台支付（支付宝H5）',
			'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
		);

		//支付金额
		$pay_amount = I("request.pay_amount", 0);
		

		 // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        $callbackurl = $this->_site . 'Pay_LeGuoAli_callbackurl.html?order_id='.$return['orderid'];
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $notifyurl;

        //$return["callbackurl"] || $return["callbackurl"] = $callbackurl;
        $return["callbackurl"] ||  $return["callbackurl"] = $callbackurl;
        //file_put_contents('20180611.txt',"callbackurl=".$return["callbackurl"]."\r\n\r\n",FILE_APPEND);
        //获取请求的url地址
        $url=$return["gateway"];


	   $arraystr = array(
	        'type'=>2,
            'uid' => $return['mch_id'],
            'order_id' => $return['orderid'],
            'order_uid' => $return['pay_orderuid'],
            'order_name' => $body,
            'price' => $return['amount'],
            'notify_url' => $return['notifyurl'],
            'return_url'=>$return['callbackurl'],

        );


        $token=$return['signkey'];
        $arraystr["key"] = md5($arraystr["notify_url"] .$arraystr["order_id"] .$arraystr["order_name"]. $arraystr["price"] .$arraystr["return_url"] .$token .$arraystr["type"] . $arraystr["uid"]);
        //file_put_contents('20180611.txt',json_encode($arraystr)."\r\n\r\n",FILE_APPEND);
        $res=$this->_createForm($url,$arraystr);
        echo $res;
        //list($return_code, $return_content) = $this->httpPostData($url, $arraystr);

	    //$respJson = json_decode($return_content,true);
        //file_put_contents('20180611.txt',$return_content."\r\n\r\n",FILE_APPEND);
        /*if($respJson['code'] == '0000'){
            $sign_array = $respJson;
            unset($sign_array['sign']);
            $respSign = $this->_createSign($sign_array,$return['signkey']);
            if(strtoupper($respSign) !=  $respJson['sign']){
                $this->showmessage('验签失败！');
            }else{
               
                redirect($respJson['code_url']);
       
            }
        }else{
            $this->showmessage($respJson['err_msg']);
        }*/
           
        return;
    }

    public function httpPostData($url, $data_string){

        $cacert = ''; //CA根证书  (目前暂不提供)
        $CA = false ;   //HTTPS时是否进行严格认证
        $TIMEOUT = 30;  //超时时间(秒)
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        
        $ch = curl_init ();
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   //  只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);      //  CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    //  检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //  信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);    //  检查证书中是否设置域名
        }


        curl_setopt ( $ch, CURLOPT_TIMEOUT, $TIMEOUT);
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $TIMEOUT-2);
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded') );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
       
        curl_close($ch);
        return array (
            $return_code,
            $return_content
        );
    }
	

	public function callbackurl(){
        file_put_contents('LeGuoAlicallbackurl.txt',"order_id=".$_REQUEST["order_id"]."\r\n\r\n",FILE_APPEND);
        $Order = M("Order");
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["order_id"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["order_id"], 'LeGuoAli', 1);
        }else{
            exit("error");
        }
	}

	 // 服务器点对点返回
    public function notifyurl(){
        //file_put_contents('20180611.txt',"毁掉中"."\r\n\r\n",FILE_APPEND);
        // $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        

        //$data = $GLOBALS['HTTP_RAW_POST_DATA'];

        //$data = xmlToArray($data);
 
        //$sign = $data['sign'];
        //unset($data['sign']);
		
		//$Order = M("Order");
        //$signkey = $Order->where("pay_orderid = '".$data['out_trade_no']."'")->getField("key");

        //$respSign = strtoupper($this->_createSign($data,$signkey));


        $code = $_POST['code'];				// 0000为支付成功
        $order_id = $_POST['order_id'];		//传入的订单号
        $order_uid = $_POST['order_uid'];	//传入的order_uid
        $price = $_POST['price'];			//支付金额
        $transaction_id = $_POST['transaction_id'];			//渠道流水号

        file_put_contents('LeGuoAlinotifyurl.txt',"code=".$code."\r\n\r\n",FILE_APPEND);
        file_put_contents('LeGuoAlinotifyurl.txt',"order_id=".$order_id."\r\n\r\n",FILE_APPEND);

        if($code=='0000'){
            $this->EditMoney($order_id, 'LeGuoAli', 0);
            exit('success');
        }
        
        exit('fail');
    }

    protected function _createForm($url, $data)
    {
        $str = '<!doctype html>
                <html>
                    <head>
                        <meta charset="utf8">
                        <title>正在跳转付款页</title>
                    </head>
                    <body onLoad="document.pay.submit()">
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