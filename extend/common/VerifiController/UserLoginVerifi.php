<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:42:21
 */
namespace VerifiController;
use think\Controller;
use common\Safety\Safetylogin;
use common\redis\RedisLogin;
use app\login\model\AppMainUser;
use app\login\model\AppLoginLog as Log;

class UserLoginVerifi extends Controller
{

    protected $UserData = '';
    protected $access_token = '';


    public function __construct()
    {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('not support:redis');
        }

        $this->access_token = input('access_token');
        //JWT登录验证
        $this->VerifiDecodeJWT();
    }
    /**
     * [VerifiDecodeJWT JWT登录验证]
     */
    public function VerifiDecodeJWT()
    {

        $Safetylogin = new Safetylogin('user');

        $Data = explode('.',$this->access_token);
        if(count($Data) != 3){
           $this->error('非法请求');
            echo json_encode(['code'=>1001]);
            exit;
        }
        $access_data = $Safetylogin->decodeJWT($this->access_token);

        if($access_data['error'] != 0){
            // dump($access_data);
            //错误日志
            if($access_data['error'] == 2){ \heillog\ErrorLog::addLog('管理后台登录验证',$access_data['data'],2); }//系统错误日志 2
            if($access_data['error'] == 3){//非法切换访问设备
                 Log::addLog(['id'=>$access_data['uid'],'info'=>$access_data['msg']],1); 
                    // dump($Data[2]);

                 $logout = new \app\login\model\Login; 
                 $logout->logout($Data[2],['id'=>$access_data['uid']]);
             }
            if($access_data['error'] == 1){ Log::addLog(['id'=>$access_data['data'],'info'=>$access_data['msg']],1); }//会员错误日志
            echo json_encode(['code'=>1001]);
            exit;

            // $this->error($access_data['msg']);
        }

        $user_id  = $access_data['data'];
        //获取redis中用户数据
        $redis = new RedisLogin();
        $UserData = $redis->get_user_data($user_id);

        if(!$UserData){
            //从数据库获取 用户数据
            $UserData = AppMainUser::get($user_id);
            $UserIfonData = $UserData->hidden(['pwd_salt','pwd_hash'])->toArray();
            if(!$UserIfonData){
                \heillog\ErrorLog::addLog('登录验证通过后','验证通过但是数据库没有用户数据',2);
            echo json_encode(['code'=>1001]);
            exit;

                // $this->error('意外的错误[v001]');//用户数据不存在
            }
            //复制个$this->UaerData
            $this->UserData = $UserIfonData;
            //缓存数据到redis
            $error = $redis->set_user_data($user_id,$UserIfonData);
            if(!$error){

                \heillog\ErrorLog::addLog('登录验证通过后','缓存用户数据到redis中',2);
                echo json_encode(['code'=>1001]);
                exit;

                // $this->error('意外的错误[v002]');return;
            }
        }else{
            $this->UserData = $UserData;
            $this->access_token = $Data[2];
        }
        // $this->success('新增成功', 'User/list');
    }


}
