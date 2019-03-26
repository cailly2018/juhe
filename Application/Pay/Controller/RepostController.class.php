<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-09-12
 * Time: 14:20
 */
namespace Pay\Controller;

class RepostController extends PayController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo json_encode(['code'=>500,'msg'=>'走错道了哦.']);
    }

    function test(){

        file_put_contents('8888888888888888.txt', date("Y-m-d H:i:s",time())."\r\n\r\n",FILE_APPEND);
        echo "ok";
    }
    /**
     *  补单机制
     */
    public function postUrl()
    {
       file_put_contents('pay_status0917999.txt', date("Y-m-d H:i:s",time())."\r\n\r\n",FILE_APPEND);
        echo "ok";
        //缓存
        $configs = C('PLANNING');
        $nums = $configs['postnum'] ? $configs['postnum'] : 5;
        $maps['pay_status'] = array('eq',1);
        $maps['num'] = array('lt',$nums);
        $list = M('Order')->where($maps)->field('id,pay_orderid,pay_ytongdao')->order('id asc')->limit(50)->select();
        if($list){
            foreach ($list as $item){
                $this->EditMoney($item['pay_orderid'],$item['pay_ytongdao'],0);
                M('Order')->where(['id'=>$item['id']])->save(['num'=>array('exp','num+1')]);
            }
        }
    }
    //解除保证金
    public function  relieveBond(){

        $complaints    = M("complaints_deposit");
        $where['c.status'] = array('eq', 0);
        $list = $complaints->alias('as c')
            ->where($where)
            ->order('id desc')
            ->select();
        if(empty($list)){
            exit;
        }
        foreach ($list as $key=>$v){
            //检查用户是否存在

            $member_m    = M("member");
            $member = $member_m->where(['id'=>$v['user_id']])->find();
            if($member && $v['unfreeze_time'] < time()){
                M()->startTrans();
                //可以解
                $updata['real_unfreeze_time'] = time();
                $updata['is_pause'] = 0;
                $updata['status'] = 1;
                $updata['update_at'] = 1;
                $complas =  $complaints->where(['id' => $v['id']])->save($updata);
                //修改改用户可提现资金
                $member_data['balance'] = $member['balance']+$v['freeze_money'];
                $member_result = $member_m->where(['id' => $v['user_id']])->save($member_data);

                // 商户充值金额变动
                $moneychange_data = array(
                    'userid'     => $v['user_id'],
                    'ymoney'     => $member['balance'], //原金额或原冻结资金
                    'money'      => $v['freeze_money'],
                    'gmoney'     =>  $member_data['balance'], //改动后的金额或冻结资金
                    'datetime'   => date('Y-m-d H:i:s'),
                    'tongdao'    => 0,
                    'transid'    => $v['pay_orderid'],
                    'orderid'    => $v['out_trade_id'],
                    'contentstr' => $v['pay_orderid']. '订单充值,保证金解除',
                    'lx'         => 10,
                    't'          => 0
                );
                $Moneychange = M('moneychange');
                $result = $Moneychange->add($moneychange_data);
                if($complas &&$member_result && $result){
                    //提交
                    file_put_contents('relieveBond.txt',"orderid=".$v['pay_orderid']."\r\n\r\n",FILE_APPEND);
                    M()->commit();
                }
                else{
                    //回滚
                    M()->rollback();
                }
            }
        }
    }

    //自动审核代付
    function postDf(){

        file_put_contents('postDf.log', date("Y-m-d H:i:s",time())."\r\n\r\n",FILE_APPEND);

        //检查是否开启自动审核
        $res = M('Tikuanconfig')->where(['id' => 28, 'issystem' => 1,'auto_df_switch'=>1])->find();

        $auto_df_stime = date('Y-m-d ',time()).$res['auto_df_stime'].':00';
        $auto_df_stime =strtotime($auto_df_stime);

        $auto_df_etime = date('Y-m-d ',time()).$res['auto_df_etime'].':00';
        $auto_df_etime =strtotime($auto_df_etime);

        if($auto_df_etime != $auto_df_stime){

            if($auto_df_etime < time() ){

                echo '今天的已经结算啦';exit;
            }
            if($auto_df_stime > time()){

                echo '还没有到开始时间';exit;
            }
        }

        //查询自动代付的用户
        $for_user = M('for_user')->where(['is_zd'=>1])->select();
        if(empty($for_user)){
            echo '暂时没有数据可操作';exit;
        }

        //  轮询
        $useid = array();
        foreach ($for_user as $k=>$v){
            $useid[] = $v['userid'];
            if($v['polling']==1){
                $weights    = explode('|', $v['weight']);
                if (is_array($weights)) {
                    $_tmpWeight =array();
                    foreach ($weights as $value) {
                        list($pid, $weight) = explode(':', $value);
                        if ($pid) {
                            $_tmpWeight[] = $pid;
                        }
                    }

                }else {
                    list($pid) = explode(':', $v['weight']);
                    if ($pid) {
                        $_tmpWeight[] = $pid;
                    }
                }
                $for_user[$k]['weight'] = $_tmpWeight;
            }else{
                $for_user[$k]['weight'] = array($v['channel']);
            }
        }

        //获取要代付的订单

        $maps['sum'] = array('gt',0);
        $maps['status'] = array('eq',0);

        $order = M('Wttklist')->where($maps)->select();

        $datas =array();
        foreach ($order as $key =>$value){

            if (!in_array($value['userid'], $useid)) {
                continue;
            }
            $weight = array();
            foreach ($for_user as $k=>$v){
                if($v['userid'] == $value['userid']){
                    $weight = $v['weight'];
                }
            }

            //通道
            $pfa_lists = M('pay_for_another')->where(['status' => 1,  'id' => array('in',$weight )])->select();

            if(empty($res) || empty($order) || empty($pfa_lists)){
                exit;
            }

            if($res['auto_df_maxmoney']>0){
                 if($value['tkmoney']> $res['auto_df_maxmoney']){
                     $datas[] =array('order'=>$value['id'],'msg'=>'金额不符和规则','date' =>date('Y-m-d H:i:s' ,time()));
                     continue;
                 }
            }
            //随机获取代付通道的下标
            $ke=array_rand($pfa_lists);

            if(!$pfa_lists[$ke]['id']){
                $datas[] =array('order'=>$value['id'],'msg'=>'通道不存在','date' =>date('Y-m-d H:i:s' ,time()));
                continue;
            }
            $_REQUEST = [
                'code'=>$pfa_lists[$ke]['id'],
                'id'=>$value['id'],
                'opt' => 'exec1',
            ];

            file_put_contents('code.txt',"code=".json_encode($pfa_lists[$ke]['id'])."\r\n\r\n",FILE_APPEND);


            $r = R('Payment/Index/index');
            $datas[] =array('order'=>$value['id'],'msg'=>$r,'date' =>date('Y-m-d H:i:s' ,time()));
        }
        file_put_contents('Q_postDf.txt',"code=".json_encode($datas)."\r\n\r\n",FILE_APPEND);
        echo '成功';

    }

}