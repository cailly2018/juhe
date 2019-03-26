<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-04-03
 * Time: 1:56
 */

namespace User\Controller;

use Think\Controller;

class BaseController extends Controller
{
    public $_site;
    public $siteconfig;
    const LENGTH = 6; //验证码的长度
    const EXPIRE = 300; //过期时间
    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->assign('siteurl', $this->_site);
        $this->assign('sitename', C('WEB_TITLE'));
        //获取系统配置
        $this->siteconfig = M("Websiteconfig")->find();
        $this->assign('siteconfig', $this->siteconfig);
    }
    function generate_password( $length =6 ) {

     // 密码字符集，可任意添加你需要的字符

        $chars ='/*abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*/0123456789';
        $password = '';
            for ( $i = 0; $i < $length; $i++ )

            {

            // 这里提供两种字符获取方式

            // 第一种是使用 substr 截取$chars中的任意一位字符；

            // 第二种是取字符数组 $chars 的任意元素

            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);

                $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];

            }

            return $password;

        }

    protected function sendNew($callIndex,$userid,$timestamp,$sign,$mobile,$content){

        //验证码的长度
        $length = self::LENGTH;
        //生成随机验证码
        $num = range(0, 9);
        shuffle($num);
        $randNum      = substr(implode('', $num), 0, $length);

        $templeData   = getSmsTemplateCode($callIndex);
        $templateCode = $templeData['template_code'];
        //记录验证码
        $sessionCode = 'send.' . $callIndex;
        $timeSession = 'send.' . $callIndex . '|' . $randNum;
        session($timeSession, time()); //存入当前生成验证码的时间
        session($sessionCode, $randNum);
        $re = send_sms($userid,$timestamp,$sign,$mobile,$content.$randNum);
        $re = json_decode($re,true);
        if($re['ReturnStatus'] == 'Success'){
            return array('code'=>1);
        }
        return array('code'=>0);
     //   {"ReturnStatus":"Success","Message":"ok","RemainPoint":17,"TaskID":20327701,"SuccessCounts":1}
    }

    /**
     * 发送验证码
     * @param  [type] $callInde 要调用的模板代码
     * @param  [type] $mobile 手机号码
     * @param  [type] $product 模板的$product参数
     * @return [type]          [description]
     */
    protected function send($callIndex, $mobile, $product,$i=1)
    {

        //验证码的长度
        $length = self::LENGTH;
        //生成随机验证码
        $num = range(0, 9);
        shuffle($num);
        $randNum      = substr(implode('', $num), 0, $length);

        $templeData   = getSmsTemplateCode($callIndex);
        $templateCode = $templeData['template_code'];
        //记录验证码
        $sessionCode = 'send.' . $callIndex;
        $timeSession = 'send.' . $callIndex . '|' . $randNum;
        session($timeSession, time()); //存入当前生成验证码的时间
        session($sessionCode, $randNum);

        if ($callIndex == 'loginWarning') {
            $templeContent = ['time' => time()];
        } else {
            //查看模板变量的个数，如果是1个是新模板，2个是旧模板
            $count = substr_count($templeData['template_content'], '$');
            //模板参数
            $templeContent = $count >= 2 ? ['code' => $randNum] : ['code' => $randNum];
        }

        $res = sendSMS($mobile, $templateCode, $templeContent,$i);
        if ($res === true) {
            return ['code' => 1, 'message' => '发送成功'];
        } else {
            return ['code' => 0, 'message' => $res];
        }
    }

    /**
     * 发送文本信息
     * @param  [type] $callIndex [description]
     * @param  [type] $mobile    [description]
     * @param  [type] $product   [description]
     * @return [type]            [description]
     */
    protected function sendStr($callIndex, $mobile, $product)
    {
        $templeData    = getSmsTemplateCode($callIndex);
        $templateCode  = $templeData['template_code'];
        $templeContent = ['time' => $product['time'], 'address' => $product['address']];
        //发送
        $res = sendSMS($mobile, $templateCode, $templeContent);
        if ($res === true) {
            return ['code' => 1, 'message' => '发送成功'];
        } else {
            return ['code' => 0, 'message' => $res];
        }
    }

    protected function checkSessionTime($callIndex, $randNum)
    {
        $timeSession = 'send.' . $callIndex . '|' . $randNum;
        $time        = session($timeSession);
        return time() - $time < self::EXPIRE;
    }
}
