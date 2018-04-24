<?php
/**
 * @Author: pizepei
 * @Date:   2018-03-15 15:29:15
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:27:44
 */
namespace common\heillog;
use common\custom\TerminalInfo;
use common\Model;
use think\Request;
/**
 * 系统日志
 */
class ErrorLog extends Model {
    protected $resultSetType = 'collection';
    protected $table = 'error_log';
    // const type_WEB = 0;
    /**
     * [addLog description]
     * @Effect
     * @param  [type]  $Data  [数据]
     * @param  [type]  $type  [错误联系0 系统错误 1 管理后台错误  2用户后台 3首页 4用户注册]
     * @param  integer $state [默认错误  0为 成功  1错误]
     */
    public static function  addLog($title,$Data,$type,$state=1)
    {
        //获取终端 信息
        $TerminalInfo =  new TerminalInfo();
        //验证类
        $request = Request::instance();

        $ip = $request->ip();//ip
        $Self = new static();
        $Self->error_type    = $type;//错误类型
        $Self->state     = $state;//状态 0成功 1失败
        $Self->title     = $title;//
        $Self->info    = $Data;//详细信息
        $Self->ip     = $ip;//登录ip
        $Self->fun     = $request->module() . '/' . $request->controller() . '/' . $request->action();
        $Self->machine    = $TerminalInfo -> getArowserInfo('json');//登录的设备
        $Self->status     = 0;//状态，0为正常，1为锁定
        $Self->isdel    = 0; //软删除
        $Self->create_time    = date('Y-m-d H:i:s'); //登录时间
        $Self->save();
    }

}
