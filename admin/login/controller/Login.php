<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-02-27 17:00:26
 */
namespace app\login\controller;
use think\Controller;
use Redis\RedisModel;
use app\login\model\Login as AddLogin;
class Login extends Controller
{
    /**
     * [login 登录类]
     * @Effect
     * @return [type] [description]
     */
    public function login()
    {
        // $name = 'pizepei';
        // $Password = 'PzP386356321';
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
        $LoginData = $Login->loginAction($username,$password,$remember,$rememberTime);
        if($LoginData['error'] == 1){
            $this->success($LoginData['msg'],'',['access_token'=>$LoginData['data']]);

        }else{

            $this->error($LoginData['msg']);

        }
    }



}

