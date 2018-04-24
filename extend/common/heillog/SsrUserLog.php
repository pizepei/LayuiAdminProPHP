<?php
/**
 * @Author: pizepei
 * @Date:   2018-03-15 15:29:15
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:27:49
 */

namespace common\heillog;
use common\Model;
use think\Request;
/**
 * SSR用户日志
 */
class SsrUserLog extends Model {
    protected $resultSetType = 'collection';
    protected $table = 'ssr_user_log';

    //类型
    public static  $Type = array(  
                            '流量',//1
                            '有效期',//2
                            '套餐',//3
                            '账户状态',//4
                            '订阅地址',//5
                            '发送邮件',//6
                        );
    /**
     * [addLog description]
     * @Effect
     * @param  [type]  $type  [操作类型]
     * @param  integer $state [默认错误  0为 成功  1错误]
     */
    public static function  addLog($Data,$Type,$state=2)
    {
        //验证类
        $request = Request::instance();

        $ip = $request->ip();//ip
        $Self = new static();
        $Self->Type    = $Type;//操作类型
        $Self->admin_id = $Data['aid'];//管理员id
        $Self->uid = $Data['uid'];//ssr 用户id
        $Self->state     = $state;//登录状态 0成功 1失败 2系统错误级别
        $Self->info    = $Data['info'];//详细信息
        $Self->ip     = $ip;//登录ip
        $Self->status     = 0;//状态，0为正常，1为锁定
        $Self->isdel    = 0; //软删除
        $Self->create_time    = date('Y-m-d H:i:s'); //登录时间
        $Self->save();
    }

}
