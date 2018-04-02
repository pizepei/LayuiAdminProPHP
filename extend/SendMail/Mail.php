<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-23 17:23:02
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-01 14:20:58
 */
namespace SendMail;
use think\Loader;
use SendMail\EmailLog;
use SendMail\SendMail;
/**
 * 邮件系统入口
 */
class Mail{

    //浏览器类型
    public static  $Domain = '';
    public static  $config = '';
    //模板
    /**
     * [sendMailCode 发送邮件]
     * @Effect
     * @param  [type] $recipient [目标地址]
     * @param  [type] $title     [邮件主题]
     * @param  [type] $type      [类型]
     * @param  [type] $time      [类型]
     * @return [type]            [description]
     */
    public static function sendMailCode($recipient,$type,$Time=120)
    {
        $config = config('CodeMail');
        $sender = $config['sender'];
        $title = $config['codeName'][$type]['title'];


        if(\SendMail\EmailLog::getAddTime($recipient,$Time,$type)){

            return ['error'=>1,'msg'=>'你已经发邮件请稍后再发','data'=>'你已经发邮件请稍后再发'];
        }

        //获取模板
        $Template = self::getTemplate($type);
        //获取数据
        $conData = self::getDataCode($sender,$type);
        //工厂
        $content = self::plantcCntent($Template,$conData);

        Loader::import('SendMail.SendMail');
        //初始化邮件类并且设置参数
        $sss = new \SendMail($sender,$recipient,$title,$content,$type);
        //发送邮件
        
        $sessionName = $config['codeName'][$type]['sessionName'];
        if(!$sss-> Mail()){
            return ['error'=>1,'msg'=>'邮件失败请稍后再试','data'=>'邮件失败请稍后再试'];
        }
        $sessData = ['code'=>$conData['code'],'time'=>time(),'email'=>$recipient];
        //保存code
        session($sessionName, $sessData);
        return ['error'=>0,'msg'=>'邮件发送成功','data'=>$conData['code']];
    }
    /**
     * [verifyCode 验证验证码]
     * @Effect
     * @return [type] [description]
     */
    public static function verifyCode($Code,$email,$type=1)
    {

        $config = config('CodeMail');
        $sessionName = $config['codeName'][$type]['sessionName'];
        $expire = $config['expire'];

        //判断是不是对应的邮箱
        if(!session($sessionName.'.email')){
            return ['error'=>0,'msg'=>'验证码不存在或者过期','data'=>'验证码不存在或者过期'];
        }
        if($email != session($sessionName.'.email')){
            return ['error'=>0,'msg'=>'验证码错误（邮箱与验证码不符）','data'=>'验证码错误（邮箱与验证码不符）'];
        }
        // 验证有效期
        $Time = session($sessionName.'.time');
        
        if(time() >  $Time+$expire){
            return ['error'=>0,'msg'=>'验证码错误过期','data'=>'验证码错误过期'];
        }

        //判断
        if(session($sessionName.'.code') == $Code){
            // 判断验证码类型进行验证通过后的处理
            if($config['codeName'][$type]['safety'] == 2){
                session($sessionName.'.code',null);
            }
            return ['error'=>1,'msg'=>'验证成功','data'=>'验证成功'];

        }

        if($config['codeName'][$type]['safety'] == 3){
            session($sessionName.'.code',null);
        }
        return ['error'=>0,'msg'=>'验证码错误','data'=>'验证码错误'];

    }
    /**
     * [contentTemplate 通过类型获取模板]
     * @Effect
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public static function getTemplate($type)
    {
        //注册验证码
        $Template['1'] = <<<SLIDE
        <h4>感谢你支持[{&%terrace%&}]!</h4>
        <p>您的注册验证码为: {&%code%&}</p>
SLIDE;
        //找回密码验证码
        $Template['2'] = <<<SLIDE
        <h4>感谢你支持[{&%terrace%&}]!</h4>
        <p>您正在进行回密码操作请确保为您本人操作</p>
        <p>您的安全验证码为: {&%code%&}</p>
SLIDE;
        //安全验证密码验证码
        $Template['3'] = <<<SLIDE
        <h4>感谢你支持[{&%terrace%&}]!</h4>
        <p>您的安全证码为: {&%code%&}</p>
SLIDE;
        if(empty($Template[$type])){
            return null;
        }
        return $Template[$type];

    }
    /**
     * [plantcCntent 邮件中文工厂 对已经获取的模板与已经获取的data str_replace()数据进行替换]
     * @Effect
     * @param  [type] $Tpl  [模板]
     * @param  [type] $Data [数据]
     * @return [type]       [description]
     */
    public static function plantcCntent($Tpl,$Data)
    {
        //循环
        foreach ($Data as $key => $value) {
                //替换第一个数据后 把结果 复制给$Tpl 继续替换
                $Tpl = str_replace('{&%'.$key.'%&}',$value,$Tpl);
        }
        $DataStr = $Tpl;
        return  $DataStr;
    }
    /**
     * [getDataCode 验证码邮件的数据拼接]
     * @Effect
     * @param  [type] $Tpl [description]
     * @return [type]      [description]
     */
    public static function getDataCode($sender,$type=1){
        $Data['terrace'] = $sender;
        $Data['code'] = Mt_str(9,4);
        return $Data;
    }
}