<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:47:58
 */
namespace app\login\controller;
use think\Controller;
use common\Redis\RedisModel;
use app\login\model\Login as AddLogin;
use WechatBrief\Port\Ticket;
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
            'login_qr'=>'登录二维码获取',
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
    /**
     * [login 登录二维码获取]
     * @Effect
     * @return [type] [description]
     */
    public function login_qr()
    {

        $remember = input('remember');
        $rememberTime = 'off';
        if($remember!=0){
            $rememberTime = $remember;
        }
        $client_id = input('client_id');
        $http_agent = md5($_SERVER['HTTP_USER_AGENT']);
        $Ticket = Ticket::get_ticket(Mt_str(5,6),2,700,0,$http_agent,$client_id,$rememberTime);
        return Result($Ticket);
        //echo '<img alt="" src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.Ticket::get_ticket(Mt_str(5,6),2,700,2)['ticket'].'" width="200" id="pay_img" style="overflow: hidden; display:;">';
    }
    /**
     * [initializeIm 绑定id]
     * @Effect
     * @return [type] [description]
     */
    public function initializeIm()
    {
        $client_id = input('client_id');
        // $cccccc
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
        // 假设用户已经登录，用户uid和群组id在session中
        $uid      = md5($this->access_token);
        $group_id = 11;
        // // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        // // 加入某个群组（可调用多次加入多个群组）
        Gateway::joinGroup($client_id, $group_id);
    }
}

