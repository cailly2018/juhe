<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace User\Controller;

use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package User\Controller
 */
class OrderController extends UserController
{

    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
    }

    public function index()
    {
        //通道
        $products = M('ProductUser')
            ->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id = __PRODUCT_USER__.pid')
            ->where(['pay_product_user.status'=>1,'pay_product_user.userid'=>$this->fans['uid']])
            ->field('pay_product.name,pay_product.id,pay_product.code')
            ->select();
        $this->assign("banklist", $products);

        $where = array();
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['channel_id'] = array('eq', $tongdao);
        }
		$body = I("request.body", '', 'strip_tags');
        if ($body) {
            $where['pay_productname'] = array('eq', $body);
        }
        $status = I("request.status",0,'intval');
      
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        if($status==4){
            $where['pay_status'] = 0;
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $where['isdel'] = 0;
        $where['pay_memberid'] = $this->fans['memberid'];
        $count = M('Order')->where($where)->count();
        $size = 15;
        $rows  = I('get.rows', $size);
        if(!$rows){
            $rows = $size;
        }
        //统计今日交易数据
        if ($status == '2') {
            //今日成功交易总额
            $todayBegin = date('Y-m-d').' 00:00:00';
            $todyEnd = date('Y-m-d').' 23:59:59';
            $stat['todaysum'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->sum('pay_amount');
            //今日实际到账总额
            $stat['taodayactualamount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->sum('pay_actualamount');
            //今日成功笔数
            $stat['todaysuccesscount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->count();
            //今日失败笔数
            $stat['todayfailcount'] = M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>0])->count();
            foreach($stat as $k => $v) {
                $stat[$k] = $v+0;
            }
            $this->assign('stat', $stat);
        }
        //查询支付成功的订单的手续费，入金费，总额总和
        $field                    = ['sum(`pay_amount`) pay_amount', 'sum(pay_poundage) pay_poundage',  'sum(`pay_actualamount`) pay_actualamount', 'count(`id`) success_count'];
        $sum                      = M('Order')->field($field)->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_status' => ['in', '1, 2']])->find();

        foreach ($sum as $k => $v) {
            $sum[$k] += 0;
        }
        //失败笔数
        $sum['fail_count'] =  M('Order')->where(['pay_memberid'=>10000+$this->fans['uid'],'pay_status' => 0])->count();
        //投诉保证金冻结金额
        $map['C.user_id'] = $this->fans['uid'];
        $map['C.status'] = 0;
        $sum['complaints_deposit_freezed'] = M('complaints_deposit')->alias('as C')->join('LEFT JOIN __ORDER__ AS O ON C.pay_orderid=O.pay_orderid')
            ->where($map)
            ->sum('freeze_money');
        $sum['complaints_deposit_freezed'] += 0;
        $map['C.status'] = 1;
        $sum['complaints_deposit_unfreezed'] = M('complaints_deposit')->alias('as C')->join('LEFT JOIN __ORDER__ AS O ON C.pay_orderid=O.pay_orderid')
            ->where($map)
            ->sum('freeze_money');
        $sum['complaints_deposit_unfreezed'] += 0;


        $sum['f_amount'] =  $sum['pay_amount']-$sum['pay_poundage'];



       // print_r($sum);die;
        $this->assign('sum', $sum);
        $page            = new Page($count, $rows);
        $list = M('Order')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('id desc')
            ->select();
   //   echo M()->getLastSql(); die;
        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign('page',$page->show());
        C('TOKEN_ON',false);
        $this->display();
    }

    /**
     * 导出交易订单
     * */
    public function exportorder()
    {

        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx","");
        if($ddlx != ""){
            $where['ddlx'] = array('eq',$ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['pay_tongdao'] = array('eq',$tongdao);
        }
        $bank = I("request.bank",'','strip_tags');
        if ($bank) {
            $where['pay_bankname'] = array('eq',$bank);
        }

        $status = I("request.status",0,'intval');
        if ($status) {
            $where['pay_status'] = array('eq',$status);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime,$cetime) = explode('|',$createtime);
            $where['pay_applydate'] = ['between',[strtotime($cstime),strtotime($cetime)?strtotime($cetime):time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime,$setime) = explode('|',$successtime);
            $where['pay_successdate'] = ['between',[strtotime($sstime),strtotime($setime)?strtotime($setime):time()]];
        }
        $where['isdel'] = 0;
        $where['pay_memberid'] = $this->fans['memberid'];

        $title = array('订单号','商户编号','交易金额','手续费','实际金额','提交时间','成功时间','通道','状态');
        $data = M('Order')->where($where)->select();
        foreach ($data as $item){

            switch ($item['pay_status']){
                case 0:
                    $status = '未处理';
                    break;
                case 1:
                    $status = '成功，未返回';
                    break;
                case 2:
                    $status = '成功，已返回';
                    break;
            }
            $list[] = array(
                'pay_orderid'=>$item['out_trade_id'] ? $item['out_trade_id']:$item['pay_orderid'],
                'pay_memberid'=>$item['pay_memberid'],
                'pay_amount'=>$item['pay_amount'],
                'pay_poundage'=>$item['pay_poundage'],
                'pay_actualamount'=>$item['pay_actualamount'],
                'pay_applydate'=>date('Y-m-d H:i:s',$item['pay_applydate']),
                'pay_successdate'=>date('Y-m-d H:i:s',$item['pay_successdate']),
                'pay_zh_tongdao'=>$item['pay_zh_tongdao'],
                'pay_status'=>$status,
            );
        }
        exportCsv($list,$title);
    }

    /**
     * 查看订单
     */
    public function show()
    {
        $id = I("get.oid",0,'intval');
        if($id){
            $order = M('Order')
                ->where(['id'=>$id])
                ->find();
        }
        if($order['pay_memberid'] != $this->fans['memberid']) {
            $parentId = M('Member')->where(['id'=>$order['pay_memberid']-10000])->getField('parentid');
            if($parentId != $this->fans['uid']) {
                $this->error('没有权限查看该订单');
            }
        } else {
            //$this->error('没有权限查看该订单');
        }

        $this->assign('order',$order);
        $this->display();
    }

    /**
     *  伪删除订单
     */
    public function delOrder()
    {
        if(IS_POST){
            $id = I('post.id',0,'intval');
            if($id){
                $res = M('Order')->where(['id'=>$id,'pay_memberid'=>$this->fans['memberid']])->setField('isdel',1);
            }
            $this->ajaxReturn(['status'=>$res]);
        }
    }
}
?>
