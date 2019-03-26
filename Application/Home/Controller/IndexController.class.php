<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Home\Controller;

/**
 * 网站入口控制器
 * Class IndexController
 * @package Home\Controller
 * @author 22691513@qq.com
 */
class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->display();
    }

    /**
     * 生成二维码
     */
    public function generateQrcode()
    {
        $str     =html_entity_decode(urldecode(I('str','')));
        if(!$str){
            exit('请输入要生成二维码的字符串！');
        }
        import("Vendor.phpqrcode.phpqrcode",'',".php");
        header('Content-type: image/png');
        \QRcode::png($str, false, "L", 10, 1);
        die;
    }
}