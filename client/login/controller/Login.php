<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-01 15:17:04
 */
namespace app\login\controller;
use think\Controller;
use Redis\RedisModel;
use app\login\model\Login as AddLogin;
use app\login\model\AppMainUser as MainUser;

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
    /**
     * [register description]
     * @Effect
     * @return [type] [description]
     */
    public function register()
    {


        if(empty(input('vercode'))){$this->error('验证码必须填写');}

        

        if(input('agreement') != 'on'){$this->error('注册必须阅读并且同意协议');}

        //验证昵称合法性   nickname  验证密码合法性  password    repass
        $validate = validate('\app\login\validate\User');

        if(!$validate->check(input())){
            $this->error($validate->getError());
        }
        $SendMailerror = \SendMail\Mail::verifyCode(input('vercode'),input('cellemail'),1);
        if($SendMailerror['error'] != 1){$this->error($SendMailerror['data']);};

        //进行注册操作
        
        $AddLogin = new AddLogin;
        $eData = $AddLogin->register(input());
        if($eData['error'] !=1){
            return $this->error($eData['data']);
        }

        $this->success('注册成功');
    }
    /**
     * [registerCodeMail 邮箱验证码]
     * @Effect
     * @return [type] [description]
     */
    public function registerCodeMail()
    {


        $email = input('mail');
        if(empty($email)) $this->error('请输入邮箱');

        //判断是否有对应的邮箱注册

        if(MainUser::get(['email'=>$email])){
            $this->error('您好该邮箱已注册');
        }

        //发送验证码   1 注册验证码  2 找回密码验证码  3 安全验证码
        $code = \SendMail\Mail::sendMailCode($email,1);
        if($code['error'] == 0){
            $this->success('获取成功');
        }else{
            $this->error($code['msg']);

        }
    }
    /**
     * [registerCodeImg 图像验证码]
     * @Effect
     * @return [type] [description]
     */
    public function registerCodeImg()
    {



    }


}

