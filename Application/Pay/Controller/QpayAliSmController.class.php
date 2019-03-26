<?php
namespace Pay\Controller;

class QpayAliSmController extends PayController
{

    public function Pay($array)
    {

        $orderid = I("request.pay_orderid", '');

        $body = I('request.pay_productname', '');

        $format = I('request.format', '');

        $parameter = [
            'code'         => 'QpayAliSm',
            'title'        => 'LeGuo（支付宝扫码）',
            'exchange'     => 1, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel'      => $array,
            'body'         => $body,
        ];

        //支付金额
        $pay_amount = I("request.pay_amount", 0);

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $this->_site . 'Pay_QpayAliSm_notifyurl.html';

        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_QpayAliSm_callbackurl.html';

        $arraystr = [
            'uid'        => $return['mch_id'],
            'price'      => sprintf('%.2f', $return['amount']),
            'istype'     => '1',
            'notify_url' => $return['notifyurl'],
            'return_url' => $return['callbackurl'],
            'orderid'    => $return['orderid'],
        ];
        
        $arraystr['key'] = $this->_createSign($arraystr, $return['signkey']);
        if($format == 'json') {
            $res = curlPost($return['gateway'].'?format=json', $arraystr);
            echo $res;
            exit;
        } else {
            echo $this->_createForm($return['gateway'], $arraystr);
        }
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

    protected function _createSign($data, $key)
    {
        $sign          = '';
        $data['token'] = $key;
        ksort($data);
        foreach ($data as $k => $vo) {
            $sign .= $vo;
        }
        return md5($sign);
    }

    public function callbackurl()
    {
        $orderid    = I('request.orderid', '');
        $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $data      = I('post.', '');
        $sign      = $data['key'];
        $orderList = M('Order')->where(['pay_orderid' => $data['orderid']])->find();
        unset($data['key']);
        $md5Sign = md5($data['orderid'] . $data['orderuid'] . $data['platform_trade_no'] . $data['price'] . $data['realprice'] . $orderList['key']);
        $diff    = $orderList['pay_amount'] * 100 - $data['price'] * 100;
        if ($md5Sign == $sign && ($diff == 0 || abs($diff) <= 5)) {
            $this->EditMoney($data['orderid'], '', 0);
			exit('OK');
        }
    }

}
