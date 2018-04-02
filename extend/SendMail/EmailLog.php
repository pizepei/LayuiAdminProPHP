<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-23 15:45:02
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-28 17:38:08
 */
namespace SendMail;

use think\Model;
/**
 * 邮件系统日志模型
 */
class EmailLog extends Model {

    protected $resultSetType = 'collection';
    const login_error_count = 10;  //密码错误次数

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
        // dump(date('Y-m-d H:i:s'));
        $request = \think\Request::instance();
        $ip = $request->ip();
        $log           = new static();
        $log->sender   =$Data['sender'];//发信人昵称
        $log->Type     = $Data['type'];//发送类型 1、注册验证2、找回密码3、修改密码
        $log->title    = $Data['title'];//主题
        $log->receive_email     = $Data['receive_email'];//接收地址
        $log->send_email    = $Data['send_email'];//发送地址
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
     * @param  [type] $email [description]
     * @param  [type] $Time  [description]
     * @param  [type] $type  [description]
     * @return [type]        [description]
     */
    public static function getAddTime($email,$Time,$type)
    {

        $Time = time()-$Time;
        $Time = date('Y-m-d H:i:s',$Time);
        // dump($Time );
        $log           = new self;
        $data = $log->where(['receive_email'=>$email])
        ->where(['Type'=>$type])
        ->where('send_create_time','> time',$Time)
        ->find();
        if($data){
            return true;
        }
        return false;

    }
}