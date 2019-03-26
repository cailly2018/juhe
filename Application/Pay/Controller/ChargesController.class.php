<?php
namespace Pay\Controller;

use Think\Controller;
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2017/10/24
 * Time: 11:18
 */
class ChargesController extends PayController{


    public function index(){
        $mchid= I("mid",0,"intval");

        if(!$mchid){
            redirect(U('Pay/Charges/msgshow'));
        }
        $where['id'] = intval($mchid) - 10000;
        $member=M("member")->where($where)->find();
        if(!$member){
            redirect(U('Pay/Charges/msgshow'));
        }
        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1])->select();
        $finalProducts = [];
        if(isMobile() && !is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'SCAN')){
                    continue;
                }

                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product; 
            }
        }else if(isMobile() && is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    $product['icon'] = $this->getIcon($product['code']);
                    $finalProducts[] = $product; 
                    break;
                }
            }
        }else{
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product; 
            }
        }
        
        $pay_orderid = date("YmdHis").rand(100000,999999);    //订单号

        $this->assign('pay_orderid', $pay_orderid);
        $this->assign('products', $finalProducts);
        $this->assign("cache",$member);
        $this->assign("mchid",$mchid);
        $this->assign("posturl",$this->_site."Pay_Charges_checkout.html");
        $this->display();
    }


    public function test()
    {

        $payid =  I('request.payid', 0, 'intval');
        $pay_memberid = "10057";   //商户ID
        if($payid){
            $pay_memberid = $payid;
        }
        $mid =  I('request.mid', 0, 'intval');

        if($mid){
            $pay_memberid = $mid;
        }
        $pay_orderid = 'E' . date("YmdHis") .$this->get_random(4);    //订单号
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间$this->_site .
        $pay_notifyurl = $this->_site . 'Pay_pay_getNotifyurl.html';
        $pay_callbackurl = $this->_site . 'Pay_pay_info.html';

        $banknumber ='6226623005303338';
        $bankname ='中国光大银行';
        //扫码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_applydate" => $pay_applydate,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
            "banknumber" => $banknumber,
            //"bankname" => $bankname,
        );

        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1])->select();
        $finalProducts = [];
        if(isMobile() && !is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'SCAN')){
                    continue;
                }

                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product;
            }
        }else if(isMobile() && is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    $product['icon'] = $this->getIcon($product['code']);
                    $finalProducts[] = $product;
                    break;
                }
            }
        }else{
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product;
            }
        }

        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1])->select();
        $bank = M('systembank')->where([ 'is_off'=>1])->select();

        $this->assign('banklist', $bank);
        $this->assign('products', $products);
        $this->assign('native', $native);
        $this->display();
    }

    public function testqianyue()
    {

        $payid =  I('request.payid', 0, 'intval');
        $pay_memberid = "10057";   //商户ID
        if($payid){
            $pay_memberid = $payid;
        }
        $mid =  I('request.mid', 0, 'intval');

        if($mid){
            $pay_memberid = $mid;
        }
        $pay_orderid = 'E' . date("YmdHis") .$this->get_random(4);    //订单号
        //$pay_amount = "0.01";    //交易金额
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间$this->_site .
        $pay_notifyurl = "http://www.dlaravel.com/";   //服务端返回地址
        $pay_callbackurl = "http://www.dlaravel.com/admin/login";  //页面跳转返回地址
        $banknumber ='6226623005303338';
        //扫码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_applydate" => $pay_applydate,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
            "banknumber" => $banknumber,
        );

        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1])->select();
        $finalProducts = [];
        if(isMobile() && !is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'SCAN')){
                    continue;
                }

                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product;
            }
        }else if(isMobile() && is_weixin()){
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    $product['icon'] = $this->getIcon($product['code']);
                    $finalProducts[] = $product;
                    break;
                }
            }
        }else{
            foreach ($products as $key => $product) {
                if(strstr($product['code'], 'WXJSAPI')){
                    continue;
                }
                $product['icon'] = $this->getIcon($product['code']);
                $finalProducts[] = $product;
            }
        }

        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1,'id'=>913])->select();
        $bank = M('systembank')->where([ 'is_off'=>1])->select();

        $this->assign('banklist', $bank);
        $this->assign('products', $products);
        $this->assign('native', $native);
        $this->display();
    }
    protected function get_random($len = 3)
    {
        //range 是将10到99列成一个数组
        $numbers = range(10, 99);
        //shuffle 将数组顺序随即打乱
        shuffle($numbers);
        //取值起始位置随机
        $start = mt_rand(1, 10);
        //取从指定定位置开始的若干数
        $result = array_slice($numbers, $start, $len);
        $random = "";
        for ($i = 0; $i < $len; $i++) {
            $random = $random . $result[$i];
        }
        return $random;
    }
    public function  checkmd5str()
    {
        //POST参数
        $native = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'pay_amount'      => I('request.pay_amount', ''),
            'pay_applydate'   => I('request.pay_applydate', ''),
            'pay_bankcode'    => I('request.pay_bankcode', ''),
            'pay_notifyurl'   => I('request.pay_notifyurl', ''),
            'pay_callbackurl' => I('request.pay_callbackurl', ''),
        );

        $Md5key = M('Member')->where(['id'=>$native['pay_memberid']-10000])->getField('apikey');

        ksort($native);

        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        file_put_contents('md1.txt',$md5str . "key=" . $Md5key."\r\n\r\n",FILE_APPEND);

        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        echo json_encode($sign);

    }

    function testOrder(){

        ignore_user_abort();//关闭浏览器仍然执行

        set_time_limit(60);//让程序一直执行下去

        $interval=1;//每隔一定时间运行

        do{

            $pay_notifyurl = "http://www.dlaravel.com/";   //服务端返回地址
            $pay_callbackurl = "http://www.dlaravel.com/admin/login";  //页面跳转返回地址
            $native = array(
                'pay_memberid'    => 10090,
                'pay_orderid'     => 'E' . date("YmdHis") .$this->get_random(4),
                'pay_amount'      => 10,
                'pay_applydate'   => date("Y-m-d H:i:s"),
                'pay_bankcode'    => 904,
                'pay_notifyurl'   =>$pay_notifyurl,
                'pay_callbackurl' => $pay_callbackurl,
            );

            file_put_contents('log.log',"order_id=".$native['pay_orderid']."---".$native['pay_applydate']."\r\n\r\n",FILE_APPEND);

            $Md5key = M('Member')->where(['id'=>$native['pay_memberid']-10000])->getField('apikey');

            ksort($native);

            $md5str = "";
            foreach ($native as $key => $val) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }

            $native['pay_md5sign'] = strtoupper(md5($md5str . "key=" . $Md5key));
            $url ="http://juhe.nutbe.cn/Pay_index.html";
            $this->_createForm($url, $native);

            sleep($interval);//等待时间，进行下一次操作。

        }while(true);



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

        echo $str;die;


    }

    function  pay(){

        //POST参数
        $native = array(
            'pay_memberid'    => I('request.pay_memberid', 0, 'intval'),
            'pay_orderid'     => I('request.pay_orderid', ''),
            'pay_amount'      => I('request.pay_amount', ''),
            'pay_applydate'   => I('request.pay_applydate', ''),
            'pay_bankcode'    => I('request.pay_bankcode', ''),
            'pay_notifyurl'   => I('request.pay_notifyurl', ''),
            'pay_callbackurl' => I('request.pay_callbackurl', ''),
            'owner' => I('request.owner', ''),
            'cert_no' => I('request.cert_no', ''),
            'cvv2' => I('request.cvv2', ''),
            'cert_no' => I('request.cert_no', ''),
            'phone' => I('request.phone', ''),
            'check_code' => I('request.check_code', ''),
            'pay_mac' => I('request.pay_mac', ''),
        );

        $Md5key = M('Member')->where(['id'=>$native['pay_memberid']-10000])->getField('apikey');

        ksort($native);

        $md5str = "";
        foreach ($native as $key => $val) {
            if (!empty($val)) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        file_put_contents('md51.txt',$md5str."\r\n\r\n",FILE_APPEND);
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        echo json_encode(array('code'=>1,'sign'=>$sign));
    }

    function testqianyue2(){
        $order_no =  I('request.mid');
        //支付产品
        $products = M('Product')->where(['isdisplay'=>1, 'status'=>1,'id'=>913])->select();
        $this->assign('products', $products);
        $this->assign('order_no', $order_no);
        $this->assign('pay_memberid', 10002);
        $this->display();
    }
    function payMoney(){
        $order_no =  I('request.mid');
        $code =  I('request.code');
        //支付产品
        $this->assign('order_no', $order_no);
        $this->assign('pay_memberid', 10002);
        $this->assign('pay_mac', '18:de:d7:a4:99:6b');
        $this->assign('check_code', $code);
        $this->display();
    }
    function ConfirmPayment(){
        $order_no =  I('request.mid');
        $code =  I('request.code');
        //支付产品
        $this->assign('order_no', $order_no);
        $this->assign('pay_memberid', 10002);
        $this->assign('check_code', $code);
        $this->assign('pay_amount', 10002);
        $this->display();
    }
    function success(){
        echo '支付成功';
    }



    protected function getIcon($product_code)
    {
        $weixin = ['WXSCAN', 'WXJSAPI','XAIPLIYH5'];
        $alipay = ['ALIWAP', 'ALISCAN','XAIPLIY '];
        $qq     = ['QQWAP'];
        if(in_array($product_code, $weixin)){
            $icon = 'wechat-icon';
        }
        if(in_array($product_code, $alipay)){
            $icon = 'alipay-icon';
        }
        if(in_array($product_code, $qq)){
            $icon = 'qq-icon';
        }
        return $icon;
    }
    public function checkout(){
        if(IS_POST){
            $pay_amount =  I("amount");    //交易金额
            if($pay_amount<=0){
                exit("交易金额不正确");
            }
            $mchid= I("mchid",0,"intval");

            if(!$mchid){
                exit("缺少商户号");
            }
            $where['id'] = intval($mchid) - 10000;
            $member=M("member")->where($where)->find();
            if(!$member){
                exit("缺少商户号");
            }

            $pay_memberid = ($member["id"]+10000);   //商户ID
            $pay_orderid = 'C'.date("YmdHis").rand(100000,999999);    //订单号


            $pay_bankcode =I("bankcode");   //银行编码
            if(empty($pay_memberid)||empty($pay_amount)||empty($pay_bankcode)){
                die("信息不完整！");
            }

            $pay_applydate = date("Y-m-d H:i:s");  //订单时间
            $pay_notifyurl = $this->_site."Pay_Charges_notify.php";   //服务端返回地址
            $pay_callbackurl = $this->_site."Pay_Charges_callback.php";  //页面跳转返回地址
            $Md5key = $member["apikey"];   //密钥
            $tjurl = $this->_site."Pay_Index.html";   //提交地址



            $native = array(
                "pay_memberid" => $pay_memberid,
                "pay_orderid" => $pay_orderid,
                "pay_amount" => $pay_amount,
                "pay_applydate" => $pay_applydate,
                "pay_bankcode" => $pay_bankcode,
                "pay_notifyurl" => $pay_notifyurl,
                "pay_callbackurl" => $pay_callbackurl,
            );
            ksort($native);
            $md5str = "";
            foreach ($native as $key => $val) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }

            $sign = strtoupper(md5($md5str . "key=" . $Md5key));
            $native["pay_md5sign"] = $sign;
            $native['pay_attach'] = $_POST["remarks"];
            $native['pay_productname'] ='收款';


            $this->setHtml($tjurl,$native);
        }
    }

    public function notify()
    {
        $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );
        if(!$ReturnArray["memberid"]){
            die;
        }

        $where['id'] = intval($ReturnArray["memberid"]) - 10000;
        $member=M("member")->where($where)->find();
        if(!$member){
            die;
        }
        $Md5key =$member["apikey"];

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if ($sign == $_REQUEST["sign"]) {

            if ($_REQUEST["returncode"] == "00") {
                exit("ok");
            }
        }
    }

    public function callback()
    {
        $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 流水号
            "returncode" => $_REQUEST["returncode"]
        );

        if(!$ReturnArray["memberid"]){
            die;
        }

        $where['id'] = intval($ReturnArray["memberid"]) - 10000;
        $member=M("member")->where($where)->find();
        if(!$member){
            die;
        }
        $Md5key =$member["apikey"];

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));

        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
                $this->assign("cache",$ReturnArray);
                $this->assign("goback",U('Pay/Charges/index',array('mchid'=>$ReturnArray["memberid"])));
               $this->display("success");

            }
        }
    }
    public function msgshow(){
        $msg=I("msg")?I("msg"):"非法操作";
        $this->assign("msg",$msg);
        $this->display();
    }
}
