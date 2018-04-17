<?php
/**
 * @Author: anchen
 * @Date:   2018-02-14 15:21:06
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-16 14:37:07
 */


namespace app\user\controller;
use think\Controller;
use custom\TerminalInfo;
/**
 * 当前用户信息
 */
class User extends \VerifiController\AdminLoginVerifi
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {

        return[
            'userSeesion'=>'当前用户基础数据',
            'userTerminalInfo'=>'用户登录日志',

        ];
    }
    /**
     * [userSeesion 获取用户基础api]
     * @return [type] [description]
     */
    public function userSeesion()
    {
        //获取用户数据
        Result($this->UserData);
    }

    /**
     * [userTerminalInfo 获取用户访问设备部分数据]
     * @return [type] [description]
     */
    public function userTerminalInfo()
    {
        // return json(TerminalInfo::getArowserPro('arr'));
        $this->error('','',[TerminalInfo::getArowserPro('arr')]);
        
    }
    /**
     * [userTerminalIp ip]
     * @return [type] [description]
     */
    public function userTerminalIp()
    {
        $this->error('','',[TerminalInfo::getIp()]);
        
    }
    /**
     * [userTerminalIp ip]
     * @return [type] [description]
     */
    public function userTerminalData()
    {
        $this->error('','',[$this->UserData]);
    }


}