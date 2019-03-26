<?php
/**
 * 提现接口
 */

namespace Pay\Controller;
header("Content-type: text/html; charset=utf-8");
use Think\Controller;

class TxController extends Controller
{
  

    /**
     * 提现申请
     */
    public function draw()
    {

        $username = $_REQUEST['username'];
       // $paypassword = $_REQUEST['paypassword'];    //支付密码
        $money = $_REQUEST['money'];
        $apikey = $_REQUEST['apikey'];
        $bankname = $_REQUEST['bankname'];
        $bankzhiname = $_REQUEST['bankzhiname'];
        $banknumber = $_REQUEST['banknumber'];
        $sheng = $_REQUEST['sheng'];
        $shi = $_REQUEST['shi'];
        $bankfullname = $_REQUEST['bankfullname'];
        $notifyurl = $_REQUEST['notifyurl'];
        $trade_order_id = $_REQUEST['trade_order_id'];
        $key = $_REQUEST['key'];

        if (!$trade_order_id || !$notifyurl || !$username || !$money || !$apikey || !$bankname || !$bankzhiname || !$banknumber || !$sheng || !$shi || !$bankfullname|| !$key) {
            exit(json_encode(['status' => 0, 'msg' => '参数不全'], JSON_UNESCAPED_UNICODE));
        }

        //判断是否设置了节假日不能提现
        $tkHolidayList = M('Tikuanholiday')->limit(366)->getField('datetime', true);
        if ($tkHolidayList) {
            $today = date('Ymd');
            foreach ($tkHolidayList as $k => $v) {
                if ($today == date('Ymd', $v)) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '节假日暂时无法提款！']);
                }

            }
        }


        //个人信息
        $Member = M('Member');
        $info = $Member->where(['username' => $username, 'apikey' => $apikey])->find();
        if (!$info) exit(json_encode(['status' => 0, 'msg' => '商户不存在'], JSON_UNESCAPED_UNICODE));
      
        $my_key = md5($info['paypassword'].$money.$apikey);
        //dump('your_key:'.$key.',my_key:'.$my_key);
        if($key != $my_key){
            exit(json_encode(['status' => 0, 'msg' => '验签key失败'], JSON_UNESCAPED_UNICODE));
        }
      

        //用户的ID
        $userid = $info['id'];
        $u = I('post.u');

        //结算方式：
        $Tikuanconfig = M('Tikuanconfig');
        $tkConfig = $Tikuanconfig->where(['userid' => $userid, 'tkzt' => 1])->find();

        $defaultConfig = $Tikuanconfig->where(['issystem' => 1, 'tkzt' => 1])->find();

        //判断是否开启提款设置
        if (!$defaultConfig) {
            exit(json_encode(['status' => 0, 'msg' => '提款已关闭！'], JSON_UNESCAPED_UNICODE));
        }

        //判断是否用户是否开启个人规则
        if (!$tkConfig || $tkConfig['tkzt'] != 1) {
            //没有个人规则，默认系统提现规则
            $tkConfig = $defaultConfig;

        } else {
            //个人规则，但是提现时间规则要按照系统规则
            $tkConfig['allowstart'] = $defaultConfig['allowstart'];
            $tkConfig['allowend'] = $defaultConfig['allowend'];
        }

        //判断结算方式
        $t = $tkConfig['t1zt'] > 0 ? $tkConfig['t1zt'] : 0;
        //判断是否T+7,T+30
        if ($t == 7) {//T+7每周一结算
            if (date('w') != 1) {
                exit(json_encode(['status' => 0, 'msg' => '请在周一申请结算!'], JSON_UNESCAPED_UNICODE));
            }
        } elseif ($t == 30) { //月结
            if (date('j') != 1) {
                exit(json_encode(['status' => 0, 'msg' => '请在每月1日申请结算!'], JSON_UNESCAPED_UNICODE));
            }
        }

        //支付密码
        //md5($paypassword) != $info['paypassword'] && exit(json_encode(['status' => 0, 'msg' => '支付密码有误!'], JSON_UNESCAPED_UNICODE));

        //是否在许可的提现时间
        $hour = date('H');

        //判断提现时间是否合法
        if ($tkConfig['allowend'] != 0) {
            if ($tkConfig['allowstart'] > $hour || $tkConfig['allowend'] <= $hour) {
                exit(json_encode(['status' => 0, 'msg' => '不在结算时间内，算时间段为' . $tkConfig['allowstart'] . ':00 - ' . $tkConfig['allowend'] . ':00'], JSON_UNESCAPED_UNICODE));
            }
        }

        //将金额转为绝对值，防止sql注入
        $tkmoney = abs(floatval($money));
        if ($tkmoney <= 0) {
            exit(json_encode(['status' => 0, 'msg' => '金额不正确'], JSON_UNESCAPED_UNICODE));
        }

        if ($tkmoney > $info["balance"]) {
            exit(json_encode(['status' => 0, 'msg' => '余额不足！'], 256));
        }

        //单笔最小提款金额
        if ($tkConfig['tkzxmoney'] > $tkmoney) {
            exit(json_encode(['status' => 0, 'msg' => '单笔最低提款额度：' . $tkConfig['tkzxmoney']], 256));
        }

        //单笔最大提款金额
        if ($tkConfig['tkzdmoney'] < $tkmoney) {
            exit(json_encode(['status' => 0, 'msg' => '单笔最大提款额度：' . $tkConfig['tkzdmoney']], 256));
        }

        //今日总金额，总次数
        $today = date('Y-m-d');

        //查询代付表跟提现表的条件
        $map['userid'] = $userid;
        $map['sqdatetime'] = ['egt', date('Y-m-d')];

        //查询提现表的数据
        $Tklist = M('Tklist');
        $tkNum = $Tklist->where($map)->count();
        $tkSum = $Tklist->where($map)->sum('tkmoney');

        //查询代付表的数据
        $Wttklist = M('Wttklist');
        $wttkNum = $Wttklist->where($map)->count();
        $wttkSum = $Wttklist->where($map)->sum('tkmoney');

        //总次数
        $dayzdnum = $tkConfig['dayzdnum'];
        //判断代付表跟提现表的提现次数是否大于规定的次数
        if (bcadd($tkNum, $wttkNum, 2) >= $dayzdnum) {
            exit(json_encode(['status' => 0, 'msg' => "超出当日提款次数！"], 256));
        }

        //当日最大总金额
        $dayzdmoney = $tkConfig['dayzdmoney'];
        //判断代付表跟提现表的提现金额是否大于规定的金额数
        $todaySum = bcadd($wttkSum, $tkSum, 2);
        if ($todaySum >= $dayzdmoney) {
            exit(json_encode(['status' => 0, 'msg' => "超出当日提款额度！"], 256));
        }
        if (($todaySum + $tkmoney) > $dayzdmoney) {
            exit(json_encode(['status' => 0, 'msg' => "提现额度不足！您今日剩余提现额度：" . ($dayzdmoney - $todaySum) . "元"], 256));
        }
        //单人单卡最高提现额度检查
        if ($tkConfig['daycardzdmoney'] > 0) {
            $map['banknumber'] = $banknumber;
            $tkCardSum = $Tklist->where($map)->sum('tkmoney');
            $wttkCardSum = $Wttklist->where($map)->sum('tkmoney');
            $todayCardSum = bcadd($tkCardSum, $wttkCardSum, 2);
            if ($todayCardSum >= $tkConfig['daycardzdmoney']) {
                exit(json_encode(['status' => 0, 'msg' => "该银行卡今日提现已超额！"], 256));
            }
            if (($todayCardSum + $tkmoney) > $tkConfig['daycardzdmoney']) {
                exit(json_encode(['status' => 0, 'msg' => "银行卡提现额度不足！该银行卡今日剩余提现额度：" . ($tkConfig['daycardzdmoney'] - $todayCardSum) . "元"], 256));
            }
        }

        $is_exist = $Tklist->where(['userid'=>$userid,'trade_order_id'=>$trade_order_id])->find();
        if($is_exist) exit(json_encode(['status' => 0, 'msg' => "订单号已存在，请更换"], 256));


        //开启事物
        M()->startTrans();
        //减用户余额
        $balance = bcsub($info['balance'], $tkmoney, 2);
        $res = $Member->where(['id' => $userid])->save(['balance' => $balance]);
        if ($res) {

            //获取订单号
            $orderid = $this->getOrderId();

            //提现时间
            $time = date("Y-m-d H:i:s");

            //计算手续费
            $sxfmoney = $tkConfig['tktype'] ? $tkConfig['sxffixed'] : bcdiv(bcmul($tkmoney, $tkConfig['sxfrate']), 100, 2);

            //实际提现的金额
            $money = bcsub($tkmoney, $sxfmoney, 2);

            //提交提现记录
            $data = [
                'orderid' => $orderid,
                'bankname' => $bankname,
                'bankzhiname' => $bankzhiname,
                'banknumber' => $banknumber,
                'bankfullname' => $bankfullname,
                'sheng' => $sheng,
                'shi' => $shi,
                'userid' => $userid,
                'sqdatetime' => $time,
                'status' => 0,
                'tkmoney' => $tkmoney,
                'sxfmoney' => $sxfmoney,
                'notifyurl' => $notifyurl,
                'trade_order_id' => $trade_order_id,
                'money' => $money,
                't' => $t,
            ];
            $result = $Tklist->add($data);

            if ($result) {
                //提交流水记录
                $rows = [
                    'userid' => $userid,
                    'ymoney' => $info['balance'],
                    'money' => $tkmoney,
                    'gmoney' => $balance,
                    'datetime' => $time,
                    'transid' => $orderid,
                    'orderid' => $orderid,
                    'lx' => '6',
                    'contentstr' => $time . '提现操作',
                ];
                $result = M('Moneychange')->add($rows);
                if ($result) {
                    M()->commit();
                    exit(json_encode(['status' => 1, 'msg' => '提现成功'], 256));
                }
            }

        }

        M()->rollback();
        $this->ajaxReturn(['status' => 0]);
    }


    public function balance(){
        $username = $_REQUEST['username'];
        $apikey = $_REQUEST['apikey'];
        $paypassword = $_REQUEST['paypassword'];

        if(!$username || !$apikey || !$paypassword) exit(json_encode(['status' => 0, 'msg' => '参数不全'], JSON_UNESCAPED_UNICODE));

        //个人信息
        $Member = M('Member');
        $info = $Member->where(['username' => $username, 'apikey' => $apikey])->find();
        if (!$info) exit(json_encode(['status' => 0, 'msg' => '商户不存在'], JSON_UNESCAPED_UNICODE));

        //支付密码
        md5($paypassword) != $info['paypassword'] && exit(json_encode(['status' => 0, 'msg' => '支付密码有误!'], JSON_UNESCAPED_UNICODE));

        exit(json_encode(['status' => 1, 'msg' => '可提现余额：'.$info['balance']], JSON_UNESCAPED_UNICODE));
    }
  
  
     public function qxt(){
        $username = $_REQUEST['username'];
        $apikey = $_REQUEST['apikey'];
        $trade_order_id= $_REQUEST['trade_order_id'];

        if(!$username || !$apikey || !$trade_order_id) exit(json_encode(['status' => 0, 'msg' => '参数不全'], JSON_UNESCAPED_UNICODE));

        //个人信息
        $Member = M('Member');
        $info = $Member->where(['username' => $username, 'apikey' => $apikey])->find();
        if (!$info) exit(json_encode(['status' => 0, 'msg' => '商户不存在'], JSON_UNESCAPED_UNICODE));

        $order = M('tklist')->where(array('userid'=>$info['id'],'trade_order_id'=>$trade_order_id))->find();
        //dump(M('tklist')->getLastSql());exit;
        if(!$order) exit(json_encode(['status' => 0, 'msg' => '订单不存在'], JSON_UNESCAPED_UNICODE));

        exit(json_encode(['status' => 1, 'msg' => '查询成功','code'=>$order['status']], JSON_UNESCAPED_UNICODE));
    }
  

    /**
     * 获得订单号
     *
     * @return string
     */
    public function getOrderId()
    {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $i = intval(date('Y')) - 2010 - 1;

        return $year_code[$i] . date('md') .
            substr(time(), -5) . substr(microtime(), 2, 5) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }



}

