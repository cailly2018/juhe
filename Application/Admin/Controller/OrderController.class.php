<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-04-02
 * Time: 23:01
 */

namespace Admin\Controller;

use Think\Page;

/**
 * 订单管理控制器
 * Class OrderController
 * @package Admin\Controller
 */
class OrderController extends BaseController
{
    const TMT = 7776000; //三个月的总秒数
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单列表
     */
    public function index()
    {
        //银行
        if(UID==7){
            $wheres['id'] = array('eq', 281);
            $tongdaolist = M("Channel")->field('id,code,title')->where($wheres)->select();
       }else{
            $tongdaolist = M("Channel")->field('id,code,title')->select();
        }
        $this->assign("tongdaolist", $tongdaolist);

        //通道
        $banklist = M("Product")->field('id,name,code')->select();

        $this->assign("banklist", $banklist);

        $where    = array();
        $memberid = I("request.memberid");
        if ($memberid) {
            $where['O.pay_memberid'] = array('eq', $memberid);
        }
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['O.out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx", "");
        if ($ddlx != "") {
            $where['O.ddlx'] = array('eq', $ddlx);
        }
		
		$is_bu = I("request.is_bu", "");
        if ($is_bu != "") {
            $where['O.is_bu'] = array('eq', $is_bu);
        }

        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['O.channel_id'] = array('eq', $tongdao);
        }
        if(UID==7){
            $where['O.channel_id'] = array('eq', 281);
        }
        $bank = I("request.bank", '', 'strip_tags');
        if ($bank) {
            $where['O.pay_bankcode'] = array('eq', $bank);
        }
        $payOrderid = I('get.pay_orderid', '');

        // exit;
        if ($payOrderid) {
            $where['O.pay_orderid'] = array('eq', $payOrderid);
        }
        $body = I("request.body", '', 'strip_tags');
        if ($body) {
            $where['O.pay_productname'] = array('eq', $body);
        }
        $status = I("request.status");
        if ($status != "") {
            if ($status == '1or2') {
                $where['O.pay_status'] = array('between', array('1', '2'));
            } else {
                $where['O.pay_status'] = array('eq', $status);
            }
        }

        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime)  = explode('|', $createtime);
            $where['O.pay_applydate'] = ['between', [strtotime($cstime), strtotime($cetime) ? strtotime($cetime) : time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime)    = explode('|', $successtime);
            $where['O.pay_successdate'] = ['between', [strtotime($sstime), strtotime($setime) ? strtotime($setime) : time()]];
        }
        $count = M('Order')->alias('as O')->where($where)->count();

        $size = 15;
        $rows = I('get.rows', $size);
        if (!$rows) {
            $rows = $size;
        }
        $fields = ['id','pay_memberid','pay_amount','pay_poundage','pay_actualamount','pay_applydate','pay_successdate','pay_status','pay_zh_tongdao','ddlx','pay_orderid','out_trade_id','memberid','pay_bankname'];
        $page = new Page($count, $rows);
        $list = M('Order')->alias('as O')
            ->field($fields)
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();

        //查询支付成功的订单的手续费，入金费，总额总和
        $countWhere               = $where;
        $countWhere['pay_status'] = ['between', [1, 2]];
        $field                    = ['sum(`pay_amount`) pay_amount', 'sum(`pay_poundage`) pay_poundage', 'sum(`pay_actualamount`) pay_actualamount', 'count(`id`) success_count'];
        $sum                      = M('Order')->alias('as O')->field($field)->where($countWhere)->find();
        foreach ($sum as $k => $v) {
            $sum[$k] += 0;
        }
        $countWhere['pay_status'] = 0;
        //失败笔数
        $sum['fail_count'] =  M('Order')->alias('as O')->where($countWhere)->count();
        //投诉保证金冻结金额
        $map = $where;
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
        //统计订单信息
        $is_month = true;
        //下单时间
        if ($createtime) {
            $cstartTime = strtotime($cstime);
            $cendTime   = strtotime($cetime) ? strtotime($cetime) : time();
            $is_month   = $cendTime - $cstartTime > self::TMT ? true : false;
        }
        //支付时间
        if ($successtime) {
            $pstartTime = strtotime($sstime);
            $pendTime   = strtotime($setime) ? strtotime($setime) : time();
            $is_month   = $pendTime - $pstartTime > self::TMT ? true : false;
        }

        $time       = $successtime ? 'pay_successdate' : 'pay_applydate';
        $dateFormat = $is_month ? '%Y年-%m月' : '%Y年-%m月-%d日';
        $field      = "FROM_UNIXTIME(" . $time . ",'" . $dateFormat . "') AS date,SUM(pay_amount) AS amount,SUM(pay_poundage) AS rate,SUM(pay_actualamount) AS total";
        $_mdata     = M('Order')->alias('as O')->field($field)->where($where)->group('date')->select();
        $mdata      = [];
        foreach ($_mdata as $item) {
            $mdata['amount'][] = $item['amount'] ? $item['amount'] : 0;
            $mdata['mdate'][]  = "'" . $item['date'] . "'";
            $mdata['total'][]  = $item['total'] ? $item['total'] : 0;
            $mdata['rate'][]   = $item['rate'] ? $item['rate'] : 0;
        }
        //统计今日交易数据
        if ($status == '1or2') {
            //今日成功交易总额
            $todayBegin = date('Y-m-d').' 00:00:00';
            $todyEnd = date('Y-m-d').' 23:59:59';
            $stat['todaysum'] = M('Order')->where(['pay_successdate'=>['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status'=>['in', '1,2']])->sum('pay_amount');
            //平台收入
            $orderSum = M('Order')->where(['pay_successdate' => ['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status' => ['in', '1,2']])->sum('pay_amount');
            $profitSum = M('moneychange')->where(['datetime'=>['between', [$todayBegin, $todyEnd]],'lx'=>9])->sum('money');
            $cost1 = M('Order')->where(['pay_successdate' => ['between', [strtotime($todayBegin), strtotime($todyEnd)]], 'pay_status' => ['in', '1,2']])->sum('cost');
            $cost2 = M('wttklist')->where(['cldatetime' => ['between', [$todayBegin, $todyEnd]], 'status' => 2])->sum('cost');
            $stat['platform'] = $orderSum - $profitSum - $cost1 - $cost2;

            //代理收入
            $stat['agentIncome'] = $profitSum;
            foreach($stat as $k => $v) {
                $stat[$k] = $v+0;
            }
            $this->assign('stat', $stat);
        }
        $ptsr   = M('websiteconfig')->where(['id' => '1'])->field('ptsr')->find();
        $parentP    = M('Member')->where(['id' => $ptsr['ptsr']])->field('id,balance')->find();


        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign("mdata", $mdata);
        $this->assign('stamount', $sum['pay_amount']);
        $this->assign('page', $page->show());
        $this->assign('strate', $parentP['balance']);
        $this->assign('strealmoney', $sum['pay_actualamount']);
        $this->assign('success_count', $sum['success_count']);
        $this->assign('fail_count', $sum['fail_count']);
        $this->assign('complaints_deposit_freezed', $sum['complaints_deposit_freezed']);
        $this->assign('complaints_deposit_unfreezed', $sum['complaints_deposit_unfreezed']);
        $this->assign("isrootadmin", is_rootAdministrator());
        C('TOKEN_ON', false);
        $this->display();
    }

    /**
     * 导出交易订单
     * */
    public function exportorder()
    {
        $memberid = I("request.memberid");
        if ($memberid) {
            $where['pay_memberid'] = array('eq', $memberid);
        }
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['out_trade_id'] = $orderid;
        }
        $ddlx = I("request.ddlx", "");
        if ($ddlx != "") {
            $where['ddlx'] = array('eq', $ddlx);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['pay_bankcode'] = array('eq', $tongdao);
        }
        $bank = I("request.bank", '', 'strip_tags');
        if ($bank) {
            $where['pay_bankname'] = array('eq', $bank);
        }
        $status = I("request.status", 0, 'intval');
        if ($status != "") {
            if ($status == '1or2') {
                $where['pay_status'] = array('between', array('1', '2'));
            } else {
                $where['pay_status'] = array('eq', $status);
            }
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime)  = explode('|', $createtime);
            $where['pay_applydate'] = ['between', [strtotime($cstime), strtotime($cetime) ? strtotime($cetime) : time()]];
        }
        $successtime = urldecode(I("request.successtime"));
        if ($successtime) {
            list($sstime, $setime)    = explode('|', $successtime);
            $where['pay_successdate'] = ['between', [strtotime($sstime), strtotime($setime) ? strtotime($setime) : time()]];
        }

        $title = [
            '下游订单号',
            '系统订单号',
            '商户编号',
            '交易金额',
            '手续费',
            '实际金额',
            '提交时间',
            '成功时间',
            '通道',
            '通道商户号',
            '状态',
        ];
        $count = M('Order')->where($where)->count();
        $rows  = 10000;
        for ($i = 0; $i <= $count; $i += $rows) {
            $data = M('Order')->where($where)->limit($i, $rows)->select();
            foreach ($data as $item) {

                switch ($item['pay_status']) {
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
                if ($item['pay_successdate']) {
                    $pay_successdate = date('Y-m-d H:i:s', $item['pay_successdate']);
                } else {
                    $pay_successdate = 0;
                }

                $list[] = [
                    'out_trade_id'     => $item['out_trade_id'],
                    'pay_orderid'      => $item['pay_orderid'],
                    'pay_memberid'     => $item['pay_memberid'],
                    'pay_amount'       => $item['pay_amount'],
                    'pay_poundage'     => $item['pay_poundage'],
                    'pay_actualamount' => $item['pay_actualamount'],
                    'pay_applydate'    => date('Y-m-d H:i:s', $item['pay_applydate']),
                    'pay_successdate'  => $pay_successdate,
                    'pay_zh_tongdao'   => $item['pay_zh_tongdao'],
                    'memberid'         => $item['memberid'],
                    'pay_status'       => $status,
                ];
            }
            exportCsv($list, $title);
            // 将已经写到csv中的数据存储变量销毁，释放内存占用
            unset($list);
            //刷新缓冲区
            ob_flush();
            flush();
        }
    }

    /**
     * 查看订单
     */
    public function show()
    {
        $id = I("get.oid", 0, 'intval');
        if ($id) {
            $order = M('Order')
                ->join('LEFT JOIN __MEMBER__ ON (__MEMBER__.id + 10000) = __ORDER__.pay_memberid')
                ->field('pay_member.id as userid,pay_member.username,pay_member.realname,pay_order.*')
                ->where(['pay_order.id' => $id])
                ->find();
        }
		$order['bu_admin']=session('admin_auth')['username'];
        $this->assign('order', $order);
        $this->display();
    }

    /**
     * 资金变动记录
     */
    public function changeRecord()
    {
        //通道
        $banklist = M("Product")->field('id,name,code')->select();
        $this->assign("banklist", $banklist);

        $where    = array();
        $memberid = I("get.memberid");
        if ($memberid) {
            $where['userid'] = array('eq', ($memberid - 10000) > 0 ? ($memberid - 10000) : 0);
        }
        $orderid = I("get.orderid");
        if ($orderid) {
            $where['transid'] = array('eq', $orderid);
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['tongdao'] = array('eq', $tongdao);
        }
        $bank = I("request.bank", '', 'strip_tags');
        if ($bank) {
            $where['lx'] = array('eq', $bank);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);
            $where['datetime']     = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }
        $count = M('Moneychange')->where($where)->count();
  
        $size  = 15;
        $rows  = I('get.rows', $size);
        if (!$rows) {
            $rows = $size;
        }
        $page = new Page($count, $rows);
        $list = M('Moneychange')
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign("page", $page->show());
        C('TOKEN_ON', false);
        $this->display();
    }

    /**
     * 资金变动记录导出
     */
    public function exceldownload()
    {
        $where    = array();
        $memberid = I("request.memberid");
        if ($memberid) {
            $where['userid'] = array('eq', ($memberid - 10000) > 0 ? ($memberid - 10000) : 0);
        }
        $orderid = I("request.orderid");
        if ($orderid) {
            $where['orderid'] = $orderid;
        }
        $tongdao = I("request.tongdao");
        if ($tongdao) {
            $where['tongdao'] = array('eq', $tongdao);
        }
        $bank = I("request.bank", '', 'strip_tags');
        if ($bank) {
            $where['lx'] = array('eq', $bank);
        }
        $createtime = urldecode(I("request.createtime"));
        if ($createtime) {
            list($cstime, $cetime) = explode('|', $createtime);
            $where['datetime']     = ['between', [$cstime, $cetime ? $cetime : date('Y-m-d')]];
        }

        $title = array('订单号', '用户名', '类型', '提成用户名', '提成级别', '原金额', '变动金额', '变动后金额', '变动时间', '通道', '备注');

        $count = M("Moneychange")->where($where)->count();
        $rows  = 10000;
        for ($i = 0; $i <= $count; $i += $rows) {
            $list = M("Moneychange")->where($where)->limit($i, $rows)->select();
            foreach ($list as $key => $value) {
                $data[$key]['transid']    = "\t" . $value["transid"];
                $data[$key]['parentname'] = getParentName($value["userid"], 1);
                switch ($value["lx"]) {
                    case 1:
                        $data[$key]['lxstr'] = "付款";
                        break;
                    case 3:
                        $data[$key]['lxstr'] = "手动增加";
                        break;
                    case 4:
                        $data[$key]['lxstr'] = "手动减少";
                        break;
                    case 6:
                        $data[$key]['lxstr'] = "结算";
                        break;
                    case 7:
                        $data[$key]['lxstr'] = "冻结";
                        break;
                    case 8:
                        $data[$key]['lxstr'] = "解冻";
                        break;
                    case 9:
                        $data[$key]['lxstr'] = "提成";
                        break;
                    default:
                        $data[$key]['lxstr'] = "未知";
                }
                $data[$key]['tcuserid']   = getParentName($value["tcuserid"], 1);
                $data[$key]['tcdengji']   = $value["tcdengji"];
                $data[$key]['ymoney']     = $value["ymoney"];
                $data[$key]['money']      = $value["money"];
                $data[$key]['gmoney']     = $value["gmoney"];
                $data[$key]['datetime']   = "\t" . $value["datetime"];
                $data[$key]['tongdao']    = getProduct($value["tongdao"]);
                $data[$key]['contentstr'] = $value["contentstr"];
            }
            exportCsv($data, $title);
            // 将已经写到csv中的数据存储变量销毁，释放内存占用
            unset($data);
            //刷新缓冲区
            ob_flush();
            flush();
        }
    }

    public function delOrder()
    {
        $createtime          = urldecode(I("request.createtime"));
        $where['pay_status'] = array('eq', 0);
        if ($createtime) {
            list($cstime, $cetime)  = explode('|', $createtime);
            $where['pay_applydate'] = ['between', [strtotime($cstime), strtotime($cetime) ? strtotime($cetime) : time()]];
        } else {
            $this->ajaxReturn(array('status' => 0, "请选择删除无效订单时间段"));
        }
        $status = M('Order')->where($where)->delete();
        if ($status) {
            $this->ajaxReturn(array('status' => 1, "删除成功"));
        } else {
            $this->ajaxReturn(array('status' => 0, "删除失败"));
        }

    }

    /**
     *   代付订单Api
     */
    public function dfApiOrderList()
    {

        $where        = [];
        $out_trade_no = I('request.out_trade_no');
        if ($out_trade_no) {
            $where['out_trade_no'] = $out_trade_no;
        }
        $accountname = I("request.accountname", "");
        if ($accountname != "") {
            $where['accountname'] = array('like', "%$accountname%");
        }
        $check_status = I("request.check_status");
        if ($check_status) {
            $where['check_status'] = array('eq', $check_status);
        }
        $status = I("request.status", 0, 'intval');
        if ($status) {
            $where['status'] = array('eq', $status);
        }
        $create_time = urldecode(I("request.create_time"));
        if ($create_time) {
            list($cstime, $cetime) = explode('|', $create_time);
            $where['create_time']  = ['between', [strtotime($cstime), strtotime($cetime) ? strtotime($cetime) : time()]];
        }
        $check_time = urldecode(I("request.check_time"));
        if ($check_time) {
            list($sstime, $setime) = explode('|', $check_time);
            $where['check_time']   = ['between', [strtotime($sstime), strtotime($setime) ? strtotime($setime) : time()]];
        }
    /*    print_r($this->fans);die;
        $where['O.userid'] = $this->fans['uid'];*/
        $count             = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `' . C('DB_PREFIX') . 'wttklist` AS W ON W.df_api_id = O.id')
            ->where($where)->count();
        $size = 15;
        $rows = I('get.rows', $size);
        if (!$rows) {
            $rows = $size;
        }
        $page = new Page($count, $rows);
        $list = M('df_api_order')
            ->alias('as O')
            ->join('LEFT JOIN `' . C('DB_PREFIX') . 'wttklist` AS W ON W.df_api_id = O.id')
            ->where($where)
            ->field('O.*,W.status')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
       // echo  M()->getLastSql(); die;

        $this->assign('rows', $rows);
        $this->assign("list", $list);
        $this->assign("page", $page->show());
        $this->display();
    }

    //代付审核
    public function check()
    {

    }

    //批量删除订单
    public function delAll() {

        if(IS_POST) {
            $code   = I('request.code');
            $createtime          = urldecode(I("request.createtime"));
            if ($createtime) {
                list($cstime, $cetime)  = explode('|', $createtime);
                $startTime = strtotime($cstime);
                $endTime = strtotime($cetime);
                if(!$startTime || !$endTime || ($startTime >= $endTime)) {
                    $this->ajaxReturn(array('status' => 0, "时间范围错误"));
                }
                $where['pay_applydate'] = ['between', [$startTime, $endTime]];
            } else {
                $this->ajaxReturn(array('status' => 0, "请选择删除订单时间段"));
            }
            if (session('send.delOrderSend') == $code && $this->checkSessionTime('delOrderSend', $code)) {
                $status = M('Order')->where($where)->delete();
                if ($status) {
                    $this->ajaxReturn(array('status' => 1, "删除成功".$status.'个订单！'));
                } else {
                    $this->ajaxReturn(array('status' => 0, "删除失败"));
                }
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
            }
        } else {
            $uid = session('admin_auth')['uid'];
            $mobile = M('Admin')->where(['id'=>$uid])->getField('mobile');
            $this->assign('mobile', $mobile);
            $this->display();
        }
    }

    /**
     * 批量删除订单验证码信息
     */
    public function delOrderSend()
    {
        $mobile = I('request.mobile');
        $res = $this->send('delOrderSend', $mobile ,'批量删除订单');
        $this->ajaxReturn(['status' => $res['code']]);
    }

    //设置订单为已支付
    public function setOrderPaid() {

        if(IS_POST) {
            $orderid = I('request.orderid');
            $auth_type = I('request.auth_type',0,'intval');
            if(!$orderid) {
                $this->ajaxReturn(['status' => 0, 'msg' => "缺少订单ID！"]);
            }
            $order = M('Order')->where(['id'=>$orderid])->find();
            if($order['status'] != 0) {
                $this->ajaxReturn(['status' => 0, 'msg' => "该订单状态为已支付！"]);
            }
            $payModel = D('Pay');
            $res = $payModel->completeOrder($order['pay_orderid'], '', 0);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => "设置成功！"]);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => "设置失败"]);
            }
            exit;
          
            if($auth_type == 1) {//谷歌安全码验证
                $google_code   = I('request.google_code');
                if(!$google_code) {
                    $this->ajaxReturn(['status' => 0, 'msg' => "谷歌安全码不能为空！"]);
                } else {
                    $ga = new \Org\Util\GoogleAuthenticator();
                    $uid = session('admin_auth')['uid'];
                    $google_secret_key = M('Admin')->where(['id'=>$uid])->getField('google_secret_key');
                    if(!$google_secret_key) {
                        $this->ajaxReturn(['status' => 0, 'msg' => "您未绑定谷歌身份验证器！"]);
                    }
                    $oneCode = $ga->getCode($google_secret_key);
                    if($google_code !== $oneCode) {
                        $this->ajaxReturn(['status' => 0, 'msg' => "谷歌安全码错误！"]);
                    } else {
                        $res = $payModel->completeOrder($order['pay_orderid'], '', 0);
                        if ($res) {
                            $this->ajaxReturn(['status' => 1, 'msg' => "设置成功！"]);
                        } else {
                            $this->ajaxReturn(['status' => 0, 'msg' => "设置失败"]);
                        }
                    }
                }
            } else {//短信验证码
                $code   = I('request.code');
                if(!$code) {
                    $this->ajaxReturn(['status' => 0, 'msg'=>"短信验证码不能为空！"]);
                } else {
                    if (session('send.setOrderPaidSend') == $code && $this->checkSessionTime('setOrderPaidSend', $code)) {
                        $res = $payModel->completeOrder($order['pay_orderid'], '', 0);
                        if ($res) {
                            $this->ajaxReturn(['status' => 1, 'msg' => "设置成功！"]);
                        } else {
                            $this->ajaxReturn(['status' => 0, 'msg' => "设置失败"]);
                        }
                    } else {
                        $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
                    }
                }
            }
        } else {
            $orderid = I('request.orderid');
            if(!$orderid) {
                $this->error('缺少参数');
            }
            $order = M('Order')->where(['id'=>$orderid])->find();
            if(empty($order)) {
                $this->error('订单不存在');
            }
            if($order['status'] != 0) {
                $this->error("该订单状态为已支付！");
            }
            $uid = session('admin_auth')['uid'];
            $user = M('Admin')->where(['id'=>$uid])->find();
            $this->assign('mobile', $user['mobile']);
            $this->assign('order', $order);
            $this->display();
        }
    }

    /**
     * 设置订单为已支付验证码信息
     */
    public function setOrderPaidSend()
    {
        $mobile = I('request.mobile');
        $res = $this->send('setOrderPaidSend', $mobile ,'设置订单为已支付');
        $this->ajaxReturn(['status' => $res['code']]);
    }
}
