<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-23 17:46:08
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-23 18:00:05
 */
namespace aLms;
use think\Model;
/**
 * 邮件系统日志模型
 */
class SmsLog extends Model {

    protected $resultSetType = 'collection';

    public static function saveLog($Data)
    {
        //如果存在错误信息error 自动设置state 1 
        if(isset($Data['error'])){
            $Data['state'] = 1;
        }else{
            $Data['state'] = 0;
            $Data['error'] = 0;
        }
        date_default_timezone_set("PRC");


        if(isset($Data['ip'])){
            $ip = $Data['ip'];
        }else{
            $request = \think\Request::instance();
            $ip = $request->ip();
        }

        $log           = new static();
        $log->app   =$Data['app'];//应用类型 1、系统后台  2、用户后台
        $log->Type     = $Data['type'];//流水号
        $log->phone    = $Data['phone'];//接收验证码手机号码
        $log->code    = $Data['code'];//验证码
        $log->tplid    = $Data['tplid'];//模板CODE
        $log->state     = $Data['state'];//状态 0成功 1失败 
        $log->ip    = $ip;//ip
        $log->info    = $Data['info'];//详细信息
        $log->er    = $Data['error'];//错误信息 没有错误0
        $log->isdel     = 0;//软删除  0正常  1删除
        $log->send_create_time    = date('Y-m-d H:i:s');//发送创建时间
        $log->create_time    = date('Y-m-d H:i:s');//创建时间
        $log->save();
    }


    /**
     * [getAddTime 邮箱]
     * @Effect
     * @param  [type] $phone [description]
     * @param  [type] $Time  [description]
     * @param  [type] $type  [description]
     * @param  [type] $app  [description]
     * @return [type]        [description]
     */
    public static function getAddTime($phone,$Time,$type,$app)
    {

        $Time = time()-$Time;
        $Time = date('Y-m-d H:i:s',$Time);
        // dump($Time );
        $log           = new self;
        $data = $log->where(['phone'=>$phone])
        ->where(['Type'=>$type])
        ->where(['app'=>$app])
        ->where('send_create_time','> time',$Time)
        ->find();
        if($data){
            return true;
        }
        return false;

    }
}