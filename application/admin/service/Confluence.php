<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/7
 * Time: 10:00
 */

namespace app\admin\service;

use app\admin\model\User;
use think\Db;
use app\admin\traits\Result;
use app\admin\model\Config;

class Confluence
{
    use Result;

    /**
     * 推荐人获取佣金
     * @param  [int] $uid [推荐人id]
     * @return [type]      [description]
     * @author 叶子
     */
    public static function subCommission($uid,$moneys)
    {
        $data = Config::where('name', 'commission_config')->find()->toArray();
        $moneys = $moneys * $data['value']['commission_rate'] / 100;
        Db::name('member')->startTrans();
        if (Db::name('member')->where('id',$uid)->setInc('commission',$moneys)) {
            //写入日志
            Db::name('member')->commit();
            return $moneys;
        }else{
            Db::name('member')->rollback();
            return false;
        }
    }

    /**
     * 写入日志
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function income($title,$uid,$type,$number,$balance = 0,$mark = '',$status = 1,$pm,$orderid)
    {
        $add_time = time();
        return  Db::name('member_bill')->insert(compact('title','uid','type','number','balance','mark','status','pm','add_time','orderid'));
    }
    /**
     * 获取用户的级别佣金
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function banker($uid)
    {
        $moneys = Db::name('member')->where('id',$uid)->value('moneys');
        if ($moneys >= 2000) {
            $res = Db::name('member')->where('id',$uid)->update(['level_id'=>12]);
            return true;
        }else{
            $user = Db::name('member')->where('moneys >= 2000')->select();
            if ($user) {
                return $user;
            }else{
                return false;
            }
        }
    }
    /**
     * 抢红包
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function participants($uid,$moneys,$number,$type,$orderid)
    {
        $add_time = time();
        return  Db::name('edpacket_log')->insert(compact('uid','moneys','number','type','orderid'));
    }

   

}