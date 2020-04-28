<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;

use think\Db;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthRule;
use app\admin\service\UserService;
use app\admin\service\AuthGroupService;
use app\admin\model\User as UserModel;

class Bill extends Common
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function userBill()
    {
         if ($this->request->isAjax()) {
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $map = [];
            empty ($data['key']) || $map[] = ['m.account', 'like', '%' . $data['key'] . '%'];
            $list = Db::name('member_bill')
                ->alias('b')
                ->join('member m', 'b.uid = m.id')
                ->where('type < 8')
                ->where($map)
                ->field('b.*,m.account,m.nickname')
                ->paginate($data['limit'], false, ['query' => $data]);

            $user_date = [];
            foreach ($list as $key => $val) {
                $val['pm'] = $val['pm'] == 0?"支付":"获取";
                $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
                switch ($val['type']) {
                    case '1':
                        $val['type'] = "佣金";
                        break;
                    case '2':
                        $val['type'] = "抽成";
                        break;
                    default:
                        $val['type'] = "余额互转";
                        break;
                }
                switch ($val['status']) {
                    case '0':
                        $val['status'] = '待确定';
                        break;
                    case '1':
                        $val['status'] = '有效';
                        break;
                    default:
                        $val['status'] = '无效';
                        break;
                }
                $user_date[$key] = $val;
            }
            return show($user_date, 0, '', ['count' => $list->total()]);
        }
        return $this->fetch();
    }
    public function recharge()
    {
        if ($this->request->isAjax()) {
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $map = [];
            empty ($data['key']) || $map[] = ['m.account', 'like', '%' . $data['key'] . '%'];
            $list = Db::name('member_bill')
                ->alias('b')
                ->join('member m', 'b.uid = m.id')
                ->where('type in(8,9)')
                ->where($map)
                ->field('b.*,m.account,m.nickname')
                ->paginate($data['limit'], false, ['query' => $data]);

            $user_date = [];
            foreach ($list as $key => $val) {
                $val['pm'] = $val['pm'] == 0?"支付":"获取";
                $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
                switch ($val['type']) {
                    case '8':
                        $val['type'] = "提现";
                        break;
                    case '9':
                        $val['type'] = "充值";
                        break;
                    default:
                        $val['type'] = "其他";
                        break;
                }
                switch ($val['status']) {
                    case '0':
                        $val['status'] = '待确定';
                        break;
                    case '1':
                        $val['status'] = '有效';
                        break;
                    default:
                        $val['status'] = '无效';
                        break;
                }
                $user_date[$key] = $val;
            }
            return show($user_date, 0, '', ['count' => $list->total()]);
        }
        return $this->fetch();
    }
    public function cash_withdrawal()
    {
         if($this->request->isPost()){
            $data = $this->request->post();
            $moneys = Db::name('member_bill')->where('id',$data['id'])->field('number,type')->find();
            if ($moneys['type'] == 8) {
                if ($data['status'] == 1) {
                    $res = Db::name('member')->where('id',$data['id'])->setDec('moneys',$moneys['number']);
                }else{
                    $res = Db::name('member')->where('id',$data['id'])->setInc('moneys',$moneys['number']);
                }
            }else{
                if ($data['status'] == 1) {
                    $res = Db::name('member')->where('id',$data['id'])->setDec('moneys',$moneys['number']);
                }else{
                    $res = Db::name('member')->where('id',$data['id'])->setInc('moneys',$moneys['number']);
                }
            }
        }
        if ($res) {
            $code = 1;
            $msg = '审核成功';
        }else{
            $code = 0;
            $msg = '审核失败';
        }
        return show([], $code, $msg);
    }
   
}