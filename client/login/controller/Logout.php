<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-22 14:32:06
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-01 15:13:35
 */
namespace app\login\controller;
use think\Controller;
use Redis\RedisModel;
use app\login\model\Login as Addlogout;
/**
 * 退出登录
 */
class Logout extends \VerifiController\UserLoginVerifi
{
    /**
     * [logout 退出登录 清空]
     * @Effect
     * @return [type] [description]
     */
    public function logout()
    {
        $Addlogout = new Addlogout;
        if($Addlogout->logout($this->access_token,$this->UserData)['error'] != 0)
        {
            $this->error('退出登录失败');
        }
        $this->success('退出成功');
    }

}