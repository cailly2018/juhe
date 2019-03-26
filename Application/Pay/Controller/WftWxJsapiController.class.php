<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-09-04
 * Time: 0:25
 */
namespace Pay\Controller;

/**
 * 第三方接口开发示例控制器
 * Class DemoController
 * @package Pay\Controller
 *
 * 三方通道接口开发说明：
 * 1. 管理员登录网站后台，供应商管理添加通道，通道英文代码即接口类名称
 * 2. 用户管理-》通道-》指定该通道（独立或轮询）
 * 3. 用户费率优先通道费率
 * 4. 用户通道指定优先系统默认支持产品通道指定
 * 5. 三方回调地址URL写法，如本接口 ：
 *    异步地址：http://www.yourdomain.com/Pay_Demo_notifyurl.html
 *    跳转地址：http://www.yourdomain.com/Pay_Demo_callbackurl.html
 *
 *    注：下游对接请查看商户API对接文档部分.
 */

class WftWxJsapiController extends PayController
{
    const PRIVATE_CERT_PATH = './cert/wft/private.txt';
    const PUBLIC_CERT_PATH  = './cert/wft/public.txt';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  发起支付
     */
    public function Pay($array)
    {
        $return = $this->getParameter('Aipay', $array, __CLASS__, 100);
        $params = [
            'service'       => 'pay.weixin.jspay',
            'version'       => '2.0',

            'sign_type'     => 'RSA_1_256',
            'mch_id'        => $return['mch_id'],
            'is_raw'        => '1',
            'is_minipg'     => '0',
            'out_trade_no'  => $return['orderid'],
            'body'          => 'pay',
            // 'sub_openid'    => '',
            // 'sub_appid'     => '',
            'total_fee'     => $return['amount'],
            'mch_create_ip' => getIP(),
            'notify_url'    => $return['notifyurl'],
            'nonce_str'     => nonceStr(),
        ];
        $signStr         = md5Sign($params, '', '', false);
        $params['sign']  = rsaEncryptVerify($signStr, self::PRIVATE_CERT_PATH, '', OPENSSL_ALGO_SHA256);
        $xml             = arrayToXml($params);
        $result          = curlPost($return['gateway'], $xml);
        $result          = xmlToArray($result);
        $jsApiParameters = trim($result['pay_info']);
        $this->assign('jsApiParameters', $jsApiParameters);
        $this->assign('callback', $return['callbackurl']);
        $this->display('WeiXin/jsapi');

        // http://<?php echo C("DOMAIN" Pay_WxGzh_success.html?orderid=<?php echo $orderid;
    }

}
