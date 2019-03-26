<?php

namespace Pay\Controller;

use Think\Controller;
use Think\Db;

class Pay1Controller extends Controller
{
    //商家信息
    protected $merchants;
    //网站地址
    protected $_site;
    //通道信息
    protected $channel;
    //代理提成
    protected $tc;
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->tc = 0;
    }
    function  test(){

            //过滤所有的img
        $url = $_GET['url'];

         $r = explode('item/',$url);
         $rs = explode('.html',$r[1]);
        //取得指定位址的內容，並储存至text
        $text=file_get_contents($url);

        preg_match('/<div[^>]*class="det_p "[^>]*>(.*?) <\/div>/si',$text,$match);

        $preg = '/<img[^>]*\/>/';
        preg_match_all($preg, $match[0], $matches);

         //获取src中的链接
        $arr = [];
        foreach($matches[0] as $v){
            $preg = '/<img[^>]*?src="([^"]*?)"[^>]*?>/i';
            preg_match_all($preg, $v, $match);
            $arr[] = $match[1];
        }

       //创建文件夹
       $dirs = "C:/img/户外/".$rs[0]."/";
        $dir = iconv("UTF-8", "GBK", $dirs);
        if (!file_exists($dir)){
            mkdir ($dir,0777,true);
        }
        $myfile = fopen($dirs.$rs[0].'.txt', "a");

      $url = '';
        foreach ( $arr as $key=>$v ) {
            $url.= $v[0].',';

        }
        $this->xia($dirs,$url);

    }
function xia($dirs,$url){

    $url = explode(',',$url);
    foreach ( $url as $key=>$v ) {

        if(!empty($v)){

            $this->down_images($v,$dirs,$key);
        }
    }

}

function down_images($url,$path,$key) {


	$header = array("Connection: Keep-Alive", "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3", "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	//curl_setopt($ch, CURLOPT_HEADER, $v);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

	$content = curl_exec($ch);

	$curlinfo = curl_getinfo($ch);

	//print_r($curlinfo);

	//关闭连接

	curl_close($ch);



	if ($curlinfo['http_code'] == 200) {

	if ($curlinfo['content_type'] == 'image/jpeg') {

	$exf = '.jpg';

	} else if ($curlinfo['content_type'] == 'image/png') {

	$exf = '.png';

	} else if ($curlinfo['content_type'] == 'image/gif') {

	$exf = '.gif';

	}
	//存放图片的路径及图片名称  *****这里注意 你的文件夹是否有创建文件的权限 chomd -R 777 mywenjian
	$filename =$key. $exf;//这里默认是当前文件夹，可以加路径的 可以改为$filepath = '../'.$filename
	$filepath = $path.$filename;
	$res = file_put_contents($filepath, $content);
        echo $res;
	}
}

}
