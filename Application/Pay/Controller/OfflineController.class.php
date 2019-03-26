<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-09-12
 * Time: 14:43
 */
namespace Pay\Controller;

class OfflineController extends PayController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo json_encode(['code' => 500, 'msg' => '走错道了哦.']);
    }

    /**
     * 解冻T1资金计划
     */
    public function offlinePlanning()
    {
        $hour    = intval(date('H'));
        $configs = C('PLANNING');

        $allowstart = $configs['allowstart'] ? $configs['allowstart'] : 1;
        $allowend   = $configs['allowend'] ? $configs['allowend'] : 5;
        //计划执行
        $rows = 1000;
        if ($hour >= $allowstart && $hour < $allowend) {

            // 处理通道的上线
            $Channel      = M('Channel');
            $channelCount = $Channel->count();
            for ($i = 0; $i < $channelCount; $i++) {
                $channelInfo = $Channel->where(['control_status' => 1])->limit($i, $rows)->getField('id', true);
                foreach ($channelInfo as $k => $v) {
                    $Channel->where(['id' => $v])->save(['pay_money' => 0, 'offline_status' => 1]);
                }
            }

            //处理通道子账号
            $ChannelAccount = M('ChannelAccount');
            $accountCount   = $ChannelAccount->count();
            for ($i = 0; $i < $accountCount; $i++) {
                $accountInfo = $ChannelAccount->where(['control_status' => 1])->limit($i, $rows)->getField('id', true);
                foreach ($accountInfo as $k => $v) {
                    $ChannelAccount->where(['id' => $v])->save(['pay_money' => 0, 'offline_status' => 1]);
                }
            }

            //处理用户
            $Member      = M('Member');
            $memberCount = $Member->count();
            for ($i = 0; $i < $memberCount; $i++) {
                $memberInfo = $Member->limit($i, $rows)->getField('id', true);
                foreach ($memberInfo as $k => $v) {
                    $memberData = [
                        'pay_money'           => 0,
                        'unit_pay_money'      => 0,
                        'unit_pay_number'     => 0,
                        'unit_frist_pay_time' => 0,
                    ];
                    $Member->where(['id' => $v])->save($memberData);
                }
            }

            sleep(1);
        } else {

        }
    }

}
