<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-20 17:11:54
 */
namespace app\login\controller;
use think\Controller;
use Redis\RedisModel;
use app\login\model\Login as AddLogin;
/**
 * 登录模块
 */
class Login extends Controller
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {

        return[
        'login'=>'登录接口',
        ];

    }
    /**
     * [login 登录接口]
     * @Effect
     * @return [type] [description]
     */
    public function login()
    {
        $username = input('username');
        $password = input('password');
        $vercode = input('vercode');
        (int)$remember = input('remember');
        $rememberTime = 'off';
        if($remember!=0){
            $rememberTime = $remember;
        }
        //登录
        $Login = new AddLogin;
        return Result($Login->loginActionRedis($username,$password,$remember,$rememberTime));//redis 版本
        return Result($Login->loginAction($username,$password,$remember,$rememberTime));// redis +mysql
    }


}

