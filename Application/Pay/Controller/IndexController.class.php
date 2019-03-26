<?php
namespace Pay\Controller;

class IndexController extends PayController
{
    protected $channel; //

    protected $memberid; //商户ID

    protected $pay_amount; //交易金额

    protected $bankcode; //银行码

    protected $orderid; //订单号

    public function __construct()
    {


        parent::__construct();
        if (empty($_POST)) {
            $this->showmessage('no data!');
        }

        $this->firstCheckParams(); //初步验证参数 ，设置memberid，pay_amount，bankcode属性

        $this->judgeRepeatOrder(); //验证是否可以提交重复订单

        $this->userRiskcontrol(); //用户风控检测

        $this->productIsOpen(); //判断通道是否开启

        $this->setChannelApiControl(); //判断是否开启支付渠道 ，获取并设置支付通api的id和通道风控


        $this->open_charge();//检查以后是否开启了充值功能
    }

    public function index()
    {

        //进入支付
        if ($this->channel['api']) {
            $info = M('Channel')->where(['id' => $this->channel['api'], 'status' => 1])->find();

            if (!is_file(APP_PATH . MODULE_NAME . '/Controller/' . $info['code'] . 'Controller.class.php')) {
                $this->showmessage('支付通道不存在', ['pay_bankcode' => $this->channel['api']]);
            }


            if (R($info['code'] . '/Pay', [$this->channel]) === false) {
                $this->showmessage('服务器开小差了...');
            }
        } else {
            $this->showmessage("抱歉......服务器飞去月球了");
        }
    }

    //======================================辅助方法===================================

    function open_charge(){
        $this->memberid = I("request.pay_memberid", 0, 'intval') - 10000;
        $res      = M('Member')->where(['id' => $this->memberid,'open_charge'=>1])->find();
        if(empty($res)){
            $this->showmessage('充值功能未开启');
        }
    }

    /**
     * [初步判断提交的参数是否合法并设置为属性]
     */
    protected function firstCheckParams()
    {
        $this->memberid = I("request.pay_memberid", 0, 'intval') - 10000;

        // 商户编号不能为空
        if (empty($this->memberid) || $this->memberid <= 0) {
            $this->showmessage("不存在的商户编号!");
        }

        $this->pay_amount = I('post.pay_amount', 0);
        if ($this->pay_amount == 0) {
            $this->showmessage('金额不能为空');
        }

        //银行编码
        $this->bankcode = I('request.pay_bankcode', 0, 'intval');
        if ($this->bankcode == 0) {
            $this->showmessage('不存在的银行编码!', ['pay_banckcode' => $this->bankcode]);
        }

        $this->orderid = I('post.pay_orderid', '');
        if (!$this->orderid) {
            $this->showmessage('订单号不合法！');
        }

    }

    /**
     * [用户风控]
     */
    protected function userRiskcontrol()
    {
        $l_UserRiskcontrol = new \Pay\Logic\UserRiskcontrolLogic($this->pay_amount, $this->memberid); //用户风控类
        $error_msg         = $l_UserRiskcontrol->monitoringData();
        if ($error_msg !== true) {
            $this->showmessage('商户：' . $error_msg);
        }
    }

    /**
     * [productIsOpen 判断通道是否开启，并分配]
     * @return [type] [description]
     */
    protected function productIsOpen()
    {
        $count = M('Product')->where(['id' => $this->bankcode, 'status' => 1])->count();
        //通道关闭
        if (!$count) {
            // $this->showmessage('暂时无法连接支付服务器!');
        }
        $this->channel = M('ProductUser')->where(['pid' => $this->bankcode, 'userid' => $this->memberid, 'status' => 1])->find();
        //用户未分配
        if (!$this->channel) {
            //$this->showmessage('暂时无法连接支付服务器!');
        }
    }

    /**
     * [判断是否开启支付渠道 ，获取并设置支付通api的id---->轮询+风控]
     */
    protected function setChannelApiControl()
    {
        $l_ChannelRiskcontrol = new \Pay\Logic\ChannelRiskcontrolLogic($this->pay_amount); //支付渠道风控类
        $m_Channel            = M('Channel');
        $where['id'] =$this->channel['id'];
        if ($this->channel['polling'] == 1 && $this->channel['weight']) {

            /***********************多渠道,轮询，权重随机*********************/
            $weight_item  = [];
            $error_msg    = '已经下线';
            $temp_weights = explode('|', $this->channel['weight']);
            $use_pids =array();
            if(!empty($this->channel['use_pid'])){
                $use_pids = json_decode($this->channel['use_pid'],true);
            }
            $n =$temp_weights;

            foreach ($temp_weights as $k => $v) {
                list($pid, $weight) = explode(':', $v);
                if(!empty($use_pids)){
                    if (in_array($pid, $use_pids)) {
                        unset($temp_weights[$k]);
                    }
                }
            }
            if(empty($temp_weights)){
                $ndata['use_pid'] = '';
                M('ProductUser')->where($where)->save($ndata);
                $temp_weights =$n;
                $use_pids =array();
            }
            foreach ($temp_weights as $k => $v) {

                list($pid, $weight) = explode(':', $v);

                //检查是否开通
                $temp_info = $m_Channel->where(['id' => $pid, 'status' => 1])->find();

                //判断通道是否开启风控并上线
                if ($temp_info['offline_status'] == 1 && $temp_info['control_status'] == 1) {

                    //-------------------------进行风控-----------------
                    $l_ChannelRiskcontrol->setConfigInfo($temp_info); //设置配置属性
                    $error_msg = $l_ChannelRiskcontrol->monitoringData();
                    if ($error_msg === true) {
                        $weight_item[] = ['pid' => $pid, 'weight' => $weight];

                    }

                } else if ($temp_info['control_status'] == 0) {
                    $weight_item[] = ['pid' => $pid, 'weight' => $weight];
                }

            }

            //如果所有通道风控，提示最后一个消息
            if ($weight_item == []) {
                $this->showmessage('通道:' . $error_msg);
            }
            $weight_item          = getWeight($weight_item);

            if(empty($use_pids)){
                $use_pid =  array($weight_item['pid']);
            }else{
                $sum = count($use_pids);
                $use_pids[$sum] = $weight_item['pid'];

                $use_pid = $use_pids;
            }

            //更新数据
            $ndata['use_pid'] = json_encode($use_pid);
            M('ProductUser')->where($where)->save($ndata);

            $this->channel['api'] = $weight_item['pid'];

        } else {
            /***********************单渠道,没有轮询*********************/

            //查询通道信息
            $pid          = $this->channel['channel'];
            $channel_info = $m_Channel->where(['id' => $pid])->find();

            //通道风控
            $l_ChannelRiskcontrol->setConfigInfo($channel_info); //设置配置属性
            $error_msg = $l_ChannelRiskcontrol->monitoringData();

            if ($error_msg !== true) {
                $this->showmessage('通道:' . $error_msg);
            }
            $this->channel['api'] = $pid;
        }
    }

    /**
     * 判断是否可以重复提交订单
     * @return [type] [description]
     */
    public function judgeRepeatOrder()
    {
        $is_repeat_order = M('Websiteconfig')->getField('is_repeat_order');
        if (!$is_repeat_order) {
            //不允许同一个用户提交重复订单
            $pay_memberid = $this->memberid + 10000;
            $count = M('Order')->where(['pay_memberid' => $pay_memberid, 'out_trade_id' => $this->orderid])->count();
            if($count){
                $this->showmessage('重复订单！');
            }
        }
    }

}
