<?php
/*
 * 代付API
 */
namespace Payment\Controller;

use Think\Controller;

class YYpayDfController extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
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
        $bank = array('中国工商银行','工商银行','中国农业银行','中国银行','建设银行','中国建设银行','交通银行','中信银行','中国光大银行',
            '华夏银行','中国民生银行','广发银行','平安银行（原深圳发展银行）','招商银行','兴业银行','上海浦东发展银行',
            '北京银行','天津银行','河北银行','邯郸市商业银行','邢台银行','张家口市商业银行','承德银行','沧州银行','廊坊银行',
            '衡水银行股份有限公司','晋商银行','阳泉市商业银行股份有限公司','晋城市商业银行','内蒙古银行','包商银行','鄂尔多斯银行',
            '大连银行','鞍山市商业银行','锦州银行','葫芦岛银行','营口银行','阜新银行','吉林银行','哈尔滨银行','龙江银行','上海银行'
        ,'南京银行','江苏银行','苏州银行','江苏长江商业银行','杭州银行','宁波银行','温州银行','嘉兴银行','湖州银行','绍兴银行',
            '浙江稠州商业银行','台州银行','浙江泰隆商业银行','浙江民泰商业银行','福建海峡银行','厦门银行','南昌银行','赣州银行',
            '上饶银行','齐鲁银行','青岛银行','齐商银行','东营市商业银行','烟台银行','潍坊银行','济宁银行','泰安市商业银行',
            '莱商银行','威海市商业银行','德州银行','临商银行','日照银行','郑州银行','开封市商业银行','洛阳银行',
            '安阳银行股份有限公司','许昌银行股份有限公司','漯河市商业银行','商丘市商业银行','驻马店银行股份有限公司（不对外办理业务）'
        ,'南阳银行','汉口银行','湖北银行股份有限公司','华融湘江银行股份有限公司','长沙银行','广州银行','珠海华润银行',
            '广东华兴银行股份有限公司','广东南粤银行','东莞银行','广西北部湾银行','柳州银行','桂林银行股份有限公司','成都银行',
            '重庆银行','自贡市商业银行','攀枝花市商业银行','德阳银行','绵阳市商业银行','贵阳银行','富滇银行','长安银行','兰州银行',
            '青海银行','宁夏银行','乌鲁木齐市商业银行','昆仑银行','新疆汇和银行股份有限公司（清算行）','江苏江阴农村商业银行股份有限公司',
            '昆山农村商业银行','吴江农村商业银行','常熟农村商业银行','张家港农村商业银行','广州农村商业银行','顺德农村商业银行',
            '海口联合农村商业银行股份有限公司','重庆农村商业银行','恒丰银行','浙商银行','天津农商银行','渤海银行','徽商银行',
            '上海农商银行','北京农村商业银行','吉林农村信用社','江苏省农村信用社联合社','浙江省农村信用社','鄞州银行','安徽省农村信用社联合社','福建省农村信用社','山东省农联社','湖北农信','深圳农商行','东莞农村商业银行','广西农村信用社（合作银行）','海南省农村信用社','四川省农村信用社联合社','云南省农村信用社','陕西省农村信用社联合社资金清算中心','黄河农村商业银行','中国邮政储蓄银行','东亚','外换银行（中国）有限公司','友利银行','新韩银行中国','企业银行','韩亚银行'
        );
        $bank_car =array(1001,1001,1002,1003,1004,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1017
        ,1018,1019,1020,1021,1022,1023,1024,1025,1026,1027,1028,1029,1030,1031,1032,1033,1034
        ,1035,1036,1037,1038,1039,1040,1041,1042,1043,1044,1045,1046,1047,1048,1049,1050,1051
        ,1052,1053,1054,1055,1056,1057,1058,1059,1060,1061,1062,1063,1064,1065,1066,1067,1068
        ,1069,1070,1071,1072,1073,1074,1075,1076,1077,1078,1079,1080,1081,1082,1083,1084,1085
        ,1086,1087,1088,1089,1090,1091,1092,1093,1094,1095,1096,1097,1098,1099,1100,1101,1102
        ,1103,1104,1105,1106,1107,1108,1109,1110,1111,1112,1113,1114,1115,1116,1117,1118,1119
        ,1120,1121,1122,1123,1124,1125,1126,1127,1128,1129,1130,1131,1132,1133,1134,1135,1136
        ,1137,1138,1139,1140,1141,1142,1143,1144,1145,1146,1147);

        $BankType ='';
        $bankname = $dataInfo['bankname'];
        foreach ($bank as $k=>$v) {
            if (strpos($v, $bankname) !== false || strpos( $bankname,$v) !== false) {
                $BankType = $bank_car[$k];
            }
        }

        $data['mch_id'] = $payInfo['mch_id'];//商户号
        $data['pay_amount'] = $dataInfo['money']*100;//交易金额
        $data['order_num'] = $dataInfo['orderid'] ;//商户代付单号
        $data['account_name'] = $dataInfo['bankfullname'];//收款人账户名
        $data['account_id'] = $dataInfo['banknumber'];//银行账号
        $data['bank_no'] = $BankType;//银行行号
        $data['notify_url'] = $this->_site . 'Payment_YYpayDf_notifyurl.html';//异步回调地址
        $data['pay_type'] = 'alipay';//开户行名称
        $data['sign'] = $this->_createSign($data, $payInfo['signkey'],1);

        $jsonArray = $this->request($payInfo['exec_gateway'],$data,'get');

        if($jsonArray['code'] == '0000' ){

            return  array('msg'=>'success','status'=>1);
        }else{
            return array('msg'=>$jsonArray['info'],'status'=>5);
        }
    }
    function notifyurl(){

        $orderid   = $_REQUEST['order_num'];
        $status    = trim($_REQUEST['pay_status']);
        $p = json_encode($_POST);
        file_put_contents('66666.txt',"order_id=".$p ."\r\n\r\n",FILE_APPEND);

        if($status == 'success') {
            $status = 2;
        }
        if($status == 'fail'){
            $status =3;
        }
        $where = ['orderid' => $orderid];
        $lists = M('Wttklist')->where($where)->find();

        $apikey = M("Member")->where("id=" . $lists['userid'])->getField("apikey");

        if(empty($lists)){
            die("fail");
        }
        if(!empty($lists['out_trade_no'])){
            $where = ['out_trade_no' => $lists['out_trade_no']];
            $list = M('df_api_order')->where($where)->find();

            if(!$list){
                die("fail");
            }
            $arra['status'] = $status;
            $arra['orderid'] = $lists['out_trade_no'];

            $arra['sign'] = $this->createSign($apikey, $arra) ;
            if($list['notifyurl']){
                $url= $list['notifyurl'] . "?".http_build_query($arra);
                file_put_contents('66666.txt',"order_id=".$url ."\r\n\r\n",FILE_APPEND);

                $ch = curl_init();
                $timeout = 10;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $contents = curl_exec($ch);
            }

        }

        $this->editwtStatus($lists['id'],$status,$lists['userid'],$lists['tkmoney']+$lists['sxfmoney'],$lists['orderid'],0);

        die("success");
    }
    protected function createSign($apikey, $list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $apikey));
        return $sign;
    }

    protected function _createSign($data, $key,$t=1)
    {
        $sign          = '';
        ksort($data);
        foreach ($data as $k => $vo) {
            $sign .= $k.'='.$vo.'&';

        }
        if($t){
           $str= $sign.'key='.$key. base64_encode('3269567451@qq.com');
        }else{
            $str= $sign.'key='.$key;
        }

        return strtoupper(MD5($str));
    }
    //脚本自动处理查询代付订单
    function Query(){

        $Wttklist = M("Wttklist");
        $map['code'] = 'YYpayDf';
        $map['status'] = 1;
        $withdraw = $Wttklist->where($map)->select();
        if($withdraw){
            foreach ($withdraw as $key => $v){

                $pfa_list = M("PayForAnother")->where(['id'=>$v['df_id']])->lock(true)->find();

                $this-> PaymentQuery($v, $pfa_list,2);
            }
        }
        echo '执行成功';
    }
    //代付查询
    public function PaymentQuery($v, $pfa_list ,$s=1)
    {
          $data['mch_id'] = $pfa_list['mch_id'];
          $data['order_num'] = $v['orderid'];
          $data['sign'] = $this->_createSign($data, $pfa_list['signkey'],0);


          $re = $this->request( $pfa_list['query_gateway'],$data,'get');
          if($re['code'] == '0000'){
              if($re['pay_status']=='SUCCESS' || $re['pay_status']=='success'){
                  $re['pay_status'] = 2;
              }
              if($re['pay_status']=='REMITTING' || $re['pay_status']== strtolower('REMITTING')){
                  $re['pay_status'] = 1;
              }
              if($re['pay_status']=='REMIT_FAIL' || $re['pay_status']==strtolower('REMIT_FAIL')){
                  $re['pay_status'] = 3;
              }
              $this->editwtStatus($v['id'],$re['pay_status'],$v['userid'],$v['tkmoney']+$v['sxfmoney'],$v['orderid'],$s);
          }
    }

    public function editwtStatus($id,$status,$userid,$tkmoney,$orderid,$s=1)
    {
            $data           = [];
            $data["status"] = $status;

            $Wttklist = M("Wttklist");
            $map['id'] = $id;
            $withdraw = $Wttklist->where($map)->lock(true)->find();
            //开启事物
            M()->startTrans();
            M()->rollback();
            $Member     = M('Member');
            $memberInfo = $Member->where(['id' => $userid])->lock(true)->find();

        //判断状态
        switch ($status) {
            case '2':
             /*  if( $memberInfo['parentid']>1){
                    $parentid = $Member->where(array('id'=>$memberInfo['parentid']))->find();
                    if( $parentid['parentid'] >1){
                        $parentid = $Member->where(array('id'=>$parentid['parentid']))->find();
                    }
                    $bdmoney = $parentid['balance']+$withdraw['sxfmoney'];
                    //提现时间
                    $time = date("Y-m-d H:i:s");
                    $mcDatas = [
                        "userid"     => $parentid['id'],
                        'ymoney'     => $parentid['balance'],
                        "money"      => $withdraw['sxfmoney'],
                        'gmoney'     => $bdmoney,
                        "datetime"   => $time,
                        "transid"    => $withdraw['orderid'],
                        "orderid"    => $withdraw['orderid'],
                        "lx"         => 10,
                        'contentstr' => date("Y-m-d H:i:s") . '委托提现操作手续费',
                    ];
                    //组织上级收到的手续费流水
                    $sx= $Member->where(['id' => $parentid['id']])->save(['balance' => $bdmoney]);
                    $sxls = M('Moneychange')->add($mcDatas);
                }*/
                break;
            case '3'://处理失败
                $gmoney =  $memberInfo['balance'] + $tkmoney;
                //2,记录流水订单号
                $arrayField = array(
                    "userid"     => $userid,
                    "ymoney"     => $memberInfo['balance'],
                    "money"      => $tkmoney,
                    "gmoney"     => $gmoney,
                    "datetime"   => date("Y-m-d H:i:s"),
                    "tongdao"    => 0,
                    "transid"    => $id,
                    "orderid"    => $orderid,
                    "lx"         => 12,
                    'contentstr' => '处理失败',
                );
                //组织上级收到的手续费流水
                $sx= $Member->where(['id' => $userid])->save(['balance' =>$gmoney]);
                $res = M('Moneychange')->add($arrayField);

                break;
        }
        $data["cldatetime"] = date("Y-m-d H:i:s");


        $res = $Wttklist->where($map)->save($data);
        if ($res) {
            M()->commit();
            if($s==1){

                $this->ajaxReturn(['status' => $res]);
            }
        }


        M()->rollback();
        if($s==1){
            $this->ajaxReturn(['status' => 0]);
        }
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
        return json_decode($result,true);
    }

}