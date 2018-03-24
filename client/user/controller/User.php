<?php
/**
 * @Author: anchen
 * @Date:   2018-02-14 15:21:06
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-09 11:25:38
 */


namespace app\user\controller;
use think\Controller;
use custom\TerminalInfo;
class User extends \VerifiController\UserLoginVerifi
{

    /**
     * [userSeesion 获取用户基础随机api]
     * @return [type] [description]
     */
    public function userSeesion()
    {

        //获取用户数据
        // dump($this->UserData);
        $this->success('获取成功','',$this->UserData);
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