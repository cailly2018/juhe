<?php
namespace Admin\Controller;

class IncomingPartsController extends BaseController
{
    protected $baseUrl = "http://platform.shanglianchuangfu.com/";
    public function __construct()
    {
        parent::__construct();
    }

    function channel(){
        $channel =  'http://platform.shanglianchuangfu.com/api-v1-user/channel';

        $data['mchid'] = '00020009';
        $data['submchid'] = '00020009000000000003';
        $data['channelinfo'] = json_encode([
            "bnwxQR" => [
                'rate' => 30,
                'fee' => 0
            ],
            "bnzfbQR" => [
                'rate' => 35,
                'fee' =>0
            ]
        ]);
        $data['sign'] = $this->encrypt($data);
        $regionlist = $this->request($channel,$data,'POST');

        echo '<pre>';
        print_r($regionlist);die;
        echo json_encode(array('data'=> $regionlist['data']));


    }


    public function userInfo()
    {

        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getProvince',array(),'POST');

        $url ='http://platform.shanglianchuangfu.com/api-v1-bank/getBankList';
        $re = $this->request( $url, array(), 'post' );

        $this->assign("shenglist", $regionlist['data']);
        $this->assign("getBankList", $re['data']);

        $this->display();
    }
    function cargetCity(){

        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getCity',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function cargetArea(){

        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getArea',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function getCity(){
        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getCity',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }
    function getArea(){

        $name   = I('post.areaId');
        $regionlist = $this->request('http://platform.shanglianchuangfu.com/api-v1-zone/getArea',array('parent'=>$name),'POST');
        echo json_encode(array('data'=> $regionlist['data']));
    }

    public function register($memberData, $bnZfbRate, $bnWxRate){
      $validateRule = [
            'name' => "尚未提交商户姓名",
            'province' => "尚未提交商户一类卡所在省份",
            'city' => "尚未提交商户一类卡所在城市",
            'area' => "尚未提交商户一类卡所在区县",
            'address' => "尚未提交商户所在地址",
            'payname' => "尚未提交商户持卡人姓名",
            'cardno' => "尚未提交商户一类卡卡号",
            'certno' => "尚未提交商户持卡人身份证号",
            'payphone' => "尚未提交商户持卡人对应手机号",
            'bankno' => "尚未提交商户持卡人对应总行编号",
            'branchno' => "尚未提交商户持卡人对应支行联行号",
            'buslicpic' => "尚未提交商户营业执照照片",
            'legfrontpic' => "尚未提交商户身份证正面照片",
            'legbackpic' => "尚未提交商户身份证反面照片",
            'handpic' => "尚未提交商户手持身份证照片",
            'doorpic' => "尚未提交商户店铺门头照照片"
        ];
        //判断其他提交规则等参数
        foreach($validateRule as $i => $v){
            if(empty($memberData[$i])){
                return ['code' => 0, 'msg' => $v, 'data' => []];
            }
        }
        $sendData = [
            'mchid' => $memberData['mchid'],
            'name' => $memberData['name'],
            'province' => $memberData['province'],
            'city' => $memberData['city'],
            'area' => $memberData['area'],
            'address' => $memberData['address'],
            'legelname' => $memberData['payname'],
            'legelcertno' => openssl_encrypt($memberData['legelcertno'], 'des-ede3-cbc', "f9b08f4246f4981a7964eb74", false, '01234567'),
            'email' => $memberData['email'],
            'phone' => $memberData['phone'],
            'bankno' => $memberData['bankno'],
            'branchno' =>$memberData['branchno'],
            'cardno' => openssl_encrypt($memberData['cardno'], 'des-ede3-cbc', "f9b08f4246f4981a7964eb74", false, '01234567'),
            'payname' => openssl_encrypt($memberData['payname'], 'des-ede3-cbc', "f9b08f4246f4981a7964eb74", false, '01234567'),
            'payphone' => $memberData['payphone'],
            'cardprovince' => $memberData['cardprovince'],
            'cardcity' => $memberData['cardcity'],
            'cardarea' => $memberData['cardarea'],
            'type' => $memberData['type'],
            'certtype' => $memberData['certtype'],
            'certno' => openssl_encrypt($memberData['certno'], 'des-ede3-cbc', "f9b08f4246f4981a7964eb74", false, '01234567'),
            'buslicpic' => $memberData['area'],
            'legfrontpic' => $memberData['legfrontpic'],
            'legbackpic' => $memberData['legbackpic'],
            'handpic' => $memberData['handpic'],
            'doorpic' => $memberData['doorpic'],
            'channelinfo' => json_encode([
                "bnwxQR" => [
                    'rate' => $bnWxRate[0],
                    'fee' => $bnWxRate[1]
                ],
                "bnzfbQR" => [
                    'rate' => $bnZfbRate[0],
                    'fee' => $bnZfbRate[1]
                ]
            ])
        ];

        //进行参数的加密
        $sendData['sign'] = $this->encrypt($sendData);

        //开始发送数据
        $result = $this->request($this->baseUrl . "api-v1-user/register", $sendData,'post');

        return $result;

    }
    /**
     * 加密方法
     * @param $data
     * @return string
     */
    protected function encrypt($data){
        $private_content = file_get_contents(BASE_PATH.'/private.pem');
        $sign = '';
        openssl_sign($this->SortToString($data), $sign, $private_content, OPENSSL_ALGO_SHA1);
        $signData = base64_encode($sign);//最终的签名
        return $signData;
    }


    function registerCha(){
        $memberData =   $_POST['b'];

        $buslicpic =  explode('--',$memberData['buslicpic']);
        $memberData['buslicpic'] = $buslicpic[0];
        $legfrontpic =  explode('--',$memberData['legfrontpic']);
        $memberData['legfrontpic'] = $legfrontpic[0];
        $legbackpic =  explode('--',$memberData['legbackpic']);
        $memberData['legbackpic'] = $legbackpic[0];
        $handpic =  explode('--',$memberData['handpic']);
        $memberData['handpic'] = $handpic[0];
        $adoorpic=  explode('--',$memberData['doorpic']);
        $memberData['doorpic'] = $adoorpic[0];
        $cashierpic =  explode('--',$memberData['cashierpic']);
        $memberData['cashierpic'] = $cashierpic[0];
        $accopenpic =  explode('--',$memberData['accopenpic']);
        $memberData['accopenpic'] = $accopenpic[0];
        $bnWxRate = array(0,0);
        $bnZfbRate = array(0,0);

        if($memberData['channelinfo']){
            $channelinfo=  explode(';',$memberData['channelinfo']);
            $ww =explode(':',$channelinfo[0]);
            $bnwxQR=  explode('--',$ww[1]);
            $ww2 =explode(':',$channelinfo[1]);
            $otherFul=  explode('--',$ww2[1]);
            $bnWxRate = $bnwxQR;
            $bnZfbRate = $otherFul;
        }


        $result = $this->register([
           // 银行信息 建设银行成都茶店子支行 6217003810026070147 孙成龙 预留电话 15655553500

            'mchid' =>  $memberData['mchid'],
            'name' => $memberData['name'],
            'legelcertno' =>  $memberData['legelcertno'],
            'province' => $memberData['province'],
            'email' => $memberData['email'],
            'city' => $memberData['city'],
            'certtype' => $memberData['certtype'],
            'type' => $memberData['type'],
            'area' => $memberData['area'],
            'address' =>  $memberData['address'],
            'bankno' => $memberData['bankno'],
            'branchno' => $memberData['branchno'],
            'cardno' => $memberData['cardno'],
            'payname' => $memberData['payname'],
            'payphone' => $memberData['payphone'],
            'phone' => $memberData['phone'],
            'cardprovince' => $memberData['cardprovince'],
            'cardcity' => $memberData['cardcity'],
            'cardarea' => $memberData['cardarea'],
            'certno' => $memberData['certno'],
            'buslicpic' =>  $memberData['buslicpic'],
            'legfrontpic' => $memberData['legfrontpic'],
            'legbackpic' => $memberData['legbackpic'] ,
            'handpic' => $memberData['handpic'],
            'doorpic' => $memberData['doorpic'],
            'cashierpic' => $memberData['cashierpic'],

        ], $bnWxRate, $bnZfbRate);


        if(!empty($result)){

            if( $result['code'] == 1){

                $memberData['buslicpic'] = $buslicpic[1];
                $memberData['legfrontpic'] = $legfrontpic[1];
                $memberData['legbackpic'] = $legbackpic[1];
                $memberData['handpic'] = $handpic[1];
                $memberData['doorpic'] = $adoorpic[1];
                $memberData['cashierpic'] = $cashierpic[1];
                $memberData['accopenpic'] = $accopenpic[1];
                $memberData['submchid'] = $result['data']['submchid'];
                $memberData['phone'] = $result['data']['phone'];
                $d['text'] =json_encode($memberData);
                $d['payphone'] = $memberData['payphone'];
                $d['submchid'] = $result['data']['submchid'];
                $d['mchid'] = $result['data']['mchid'];
                $d['phone'] = $result['data']['phone'];
                 M('jinjian')->add($d);
                $this->ajaxReturn( ['code' => 1, 'msg' => "注册完成", 'data' => $result['data']]);
            }else{
                $this->ajaxReturn( ['code' => 0, 'msg' => $result['msg'], 'data' => []]);
            }
        }else{
            $this->ajaxReturn( ['code' => 0, 'msg' => "与服务器连接失败，未知错误", 'data' => []]);
        }
    }
    function SortToString($data){
        ksort($data);
        $temp = [];
        foreach($data as $i => $v){
            if(isset($v)){
                if(is_array($v)){
                    $temp[] = $i . "=" . $this->SortToString($v);
                }else{
                    $temp[] = $i . "=" . $v;
                }
            }
        }
        return join("&", $temp);
    }

    public function uploadImg()
    {
            if (IS_POST) {

            $upload           = new \Think\Upload();
            $upload->maxSize  = 5097152;
            $upload->exts     = array('jpg', 'gif', 'png');
            $upload->savePath = '/jinjian/';
            $info             = $upload->uploadOne($_FILES['file']);

            if (!$info) {
                // 上传错误提示错误信息
                $this->error($upload->getError());
            } else {
                $logo = BASE_PATH.'Uploads' . $info['savepath'] . $info['savename'];
                $logoS = 'Uploads' . $info['savepath'] . $info['savename'];

               if($_SERVER['SERVER_ADDR']=='127.0.0.1'){
                   $logo = str_replace( '/','\\', $logo);
               }

                $this->ajaxReturn(['code' => 1, 'msg' => "上传成功",'url'=> $this-> upload($logo).'--'.$logoS]);

            }
        }
    }

    function upload($file){

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, "http://platform.shanglianchuangfu.com/api-v1-user/upload.html");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, [
            'file' => new \CurlFile($file)
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $result =  json_decode($result,true);
        $url ='';
        if($result['code'] ==1){
            $url = $result['data']['file'];
        }

        return $url;
    }

    function getBankList(){
        $name   = I('post.areaId');
        $url ='http://platform.shanglianchuangfu.com/api-v1-bank/getBranchList';
        $re = $this->request( $url, array('parent'=>$name), 'post' );

        echo json_encode(array('data'=> $re['data']));
    }

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
        $result =  json_decode($result,true);
        return $result;
    }

}
