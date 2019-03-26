<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class AApayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    protected $types;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->types = 'AApayDf';
    }


    /**
     * 创建代付申请
     * @param $parameter
     * @return array
     */
    public function Payment($dataInfo)
    {


        if(empty($dataInfo['pay'])){
            return  array('msg'=>'缺少数据','status'=>5);
        }
        $payInfo = json_decode($dataInfo['pay'],true);
        $data['userOrderId'] =  $dataInfo['orderid'] ;//商户代付单号
        $data['syncUrl'] = $this->_site . 'Payment_'.$this->types.'_notifyurl.html';//异步回调地址;
        $data['cardHolder'] =$dataInfo['bankfullname'];//收款人账户名
        $data['cardNo'] =$dataInfo['banknumber'];//收款人账户名
        $data['money'] = $dataInfo['money'];//交易金额
/*
        $data1['companyId'] = $payInfo['mch_id'];
        $data = json_encode($data);
        $data1['data'] = $this->encrypt($data, $payInfo['signkey']);*/
        $jsonArray['status'] = 0;
        if($jsonArray['status'] == 0 ){
            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>'异常','status'=>5);
        }
    }
    function  notifyurl(){
        echo 'success';die;
    }


}