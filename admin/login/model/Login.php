<?php
namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
use app\login\model\AdminMainAdmin as MainAdmin;
use app\login\model\AdminLoginLog as Log;
use app\login\model\AdminLoginMainToken as Token;
use think\Request;

/**
 * 登录用户模型
 */
class Login extends Model {

    protected $resultSetType = 'collection';
    const login_error_count = 10;  //密码错误次数
    const login_error_count_time = 7200;  //密码错误限制登录时间
    const type_WEB = 0;  //登录类型
    protected $Time = '';

    protected $verdictLoginCount = '';

    /**
     * [loginAction 登录数据获取方法]
     * @Effect
     * @param  [type] $name     [description]
     * @param  [type] $Password [description]
     * @return [type]           [description]
     */
    public function  loginAction($name,$Password,$remember,$rememberTime)
    {
        // 初始化时间

        $this->Time =time(); 
        $lastTime = 'off';
        if($remember != 0){
            if($rememberTime <=1 || $rememberTime >30){
                return ['error'=>0,'msg'=>'非法的临时登录时间','data'=>'非法的临时登录时间'];
            }
            $lastTime = $this->Time;

        }

        // dump($lastTime);

        $MainAdmin = new MainAdmin;
        //获取数据
        $User = $MainAdmin->loginAction($name);
        //判断获取数据是否有问题  （有没有这个用户）
        if(!$User)return ['error'=>0,'msg'=>'没有用户','data'=>'数据库中无数据'];
        //判断是否超过登录限制
        //       密码错误次数    >= 系统设置       同时                  
        if(($User->login_error_count >=self::login_error_count) ){
            //当前时间戳 - 上次错误时间戳    <= 系统设置7200
            if(($this->Time-strtotime($User->login_error_count_time)) <= self::login_error_count_time){

                if($User->login_error_count >self::login_error_count){

                    $infoData = '24小时内密码错误过多'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                    Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                    return ['error'=>0,'msg'=>'限制登陆','data'=>$infoData];
                }

                $infoData = '密码错误超过'.self::login_error_count.'次'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                return ['error'=>0,'msg'=>$infoData];
            }
        }

        //获取登录配置数据
        $ConfigLogin = $User->AdminLoginMainConfig;
        // 获取有效期配置
        $Overdue = $ConfigLogin->overdue;
        //获取同时登录设备数量
        $login_count =$ConfigLogin->login_count;
        //并且开启事务
        $Token = new Token;
        $Token->startTrans();

        try{

            //获取登录设备记
            // $MainTokenLogin = $User->AdminLoginMainToken;
            $MainTokenLogin = $Token->lock(true)->where('uid', $User->id)->select();

            //判断是否超过同时在线上限制
            if(!$this->verdictLoginCount($MainTokenLogin,$Overdue,$login_count)){
                $Token->rollback();
                $infoData = '超过同时在线上限'.'限制在线'.$login_count.'|在线'.$this->verdictLoginCount;
                Log::addLog(['id'=>$User->id,'info'=>$infoData],1);

                return ['error'=>0,'msg'=>'超过同时在线上限','data'=>'超过同时在线上限'];
            }

            //实例化 密码安全类
            $Safety = new \Safety\Safetylogin('admin');

            //判断 密码是否正确
            if(!$Safety->passwordVerify($Password,$User->pwd_salt,$User->pwd_hash)){

                $Token->rollback();
                $User->login_error_count = $User->login_error_count+1;
                $User->login_error_count_time = date('Y-m-d H:i:s');
                $User->save();
                //写入日志
                Log::addLog(['id'=>$User->id,'info'=>'账号或者密码第'.$User->login_error_count.'次错误'],1);
                return ['error'=>0,'msg'=>'账号或者密码错误','data'=>'请稍后再尝试'];

            };

            //生成 JWT
            //signature 唯一签名   
            //sub所面向的用户（用户id） 
            //iat 的签发时间 
            //jti 唯一身份标识（一次性token）主要用来作为一次性token,从而回避重放攻击。
            $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
                                 
            $RedisLogin = new \redis\RedisLogin(config('admin_login_redis'));
            //判断 redis 存储结果
            if(!$RedisLogin ->set_hash_token($Signature,'admin')){
                // 错误 尝试
                $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
                //再次判断
                if($RedisLogin ->set_hash_token($Signature,'admin')){
                    //放弃
                    $Token->rollback();
                    return ['error'=>0,'msg'=>'登录失败','data'=>'请稍后再尝试[loo1]'];
                }
            }

            $request = Request::instance();

            //准备数据
            $arr = [
                'uid'=>$User->id,  //用户id
                'type'=>self::type_WEB, //登录类型
                'login_access_token'=>$Signature['signature'], //登录token
                'login_access_token_salt'=>$Signature['jti'],  //登录token的salt
                'login_access_token_time'=>date('Y-m-d H:i:s',$Signature['iat']),  //登录token创建时间
                'login_info'=>$request->ip(), //登录的设备或者ip
                'status'=> 0 , //状态，0为正常，1为锁定
                'isdel'=> 0 , //软删除  0正常  1删除
                'create_time'=>date('Y-m-d H:i:s',$Signature['iat']),  //登录时间
            ];
            // 判断是增加登录设备记录还是修改登录设备记录6
            if($login_count >count($MainTokenLogin)  || count($MainTokenLogin) == 0){
                //增加
                // $Token->data($arr);
                $Error = $Token->save($arr);
                // dump($Error);
                if(!$Error){
                    $Token->rollback();
                    //错误信息
                    $ErrorF = '增加';
                }
            }else{
                //修改 获取需要修改的id
                $Update = $this->verdictLoginUpdate($MainTokenLogin,$Overdue);
                // dump($Update);
                if(!$Update){
                    $Token->rollback();

                    Log::addLog(['id'=>$User->id,'info'=>'登录失败ip:'.$request->ip().'原因：更新Token时间错误verdictLoginUpdate方法'],1);
                    return ['error'=>0,'msg'=>'登录失败','data'=>'请稍后再尝试'];
                }

                //设置修改的id  并且修改
                $Error = $Token->save($arr,['id'=>$Update]);
                if(!$Error){
                    $Token->rollback();
                    //错误信息
                    $ErrorF = '更新';
                }

            }
            //判断 增加 或者更新操作是否成功
            if(!$Error){
                $Token->rollback();
                //写入日志
                Log::addLog(['id'=>$User->id,'info'=>'登录失败ip:'.$request->ip().'原因：'.$ErrorF.'Token失败'],1);
                return ['error'=>0,'msg'=>'登录失败','data'=>'请稍后再尝试'];
            }

            //写入日志
            Log::addLog(['id'=>$User->id,'info'=>'登录成功ip:'.$request->ip()],0);
            $Token->commit();


            return ['error'=>1,'msg'=>'登录成功','data'=>$Signature['header'].'.'.$Signature['playload'].'.'.$Signature['signature']];

           } catch (\Exception $e) {
            $Token->rollback();
            Log::addLog(['id'=>$User->id,'info'=>'登录失败ip:'.$request->ip().'原因：事务错误'],1);
            // dump($e);
            return ['error'=>0,'msg'=>'登录失败','data'=>'请稍后再尝试[loo1]'];
            throw $e;
        }

        //获取
    }
    /**
     * [verdictLoginCount 判断是否超过同时在线上限制]
     * @Effect
     * @param  [type] $ConfigLogin [数据]
     * @param  [type] $Overdue     [有效期配置time]
     * @param  [type] $login_count     [同时在线数量配置]
     * @return [type]              [description]
     */
    public function verdictLoginCount($MainTokenLogin,$Overdue,$login_count)
    {
        $i = 0;
        //修改
        foreach ($MainTokenLogin as $key => $value) {
            //创建时间   +   7200   >  当前时间
            if( (strtotime($value->login_access_token_time)+$Overdue) > $this->Time){
                //有设备在线
                $i = $i+1;
            }
        }

        $this->verdictLoginCount = $i;
        // echo '有'.$i.'设备在线';
        // echo '限制'.$login_count.'台设备';
        //判断结果
        if($i >= $login_count)
        {
            return false;
        }
        return true;
    }
    //修改
    /**
     * [verdictLoginUpdate 判断登录记录中最近的一条登录超时记录的id 返回给更新]
     * @param  [type] $MainTokenLogin [登录记录]
     * @param  [type] $Overdue        [超时时间配置]
     * @return [type]                 [description]
     */
    public function verdictLoginUpdate($MainTokenLogin,$Overdue)
    {
        $i = 0;
        // echo $Overdue;
        //修改
        foreach ($MainTokenLogin as $key => $value) {
            //创建时间   +   7200   <  当前时间
            if( (strtotime($value->login_access_token_time)+$Overdue) < $this->Time){
                //有设备在线
                return $value['id'];
            }
        }

        return false;
    }


    /**
     * [logout 退出登录方法]
     * @Effect
     * @return [type] [description]
     */
    public function logout($access_token,$UserData,$type='')
    {
        //查询 redis 中是否有登录数据
       $RedisLogin = new  \redis\RedisLogin(config('admin_login_redis'));
       if(!$RedisLogin->logout($access_token,'admin')){
            //写入日志
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：redis只没用数据'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo1]'];
       }

        $Token = Token::get(['login_access_token'=>$access_token]);
        //判断是否是正常 token 
        if(!$Token){
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：mysql只没用数据'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo2]'];
        }
        $Token->login_access_token_time = date('Y-m-d H:i:s',strtotime($Token->login_access_token_time)-config('JWT_admin.exp'));
        if(!$Token->save()){
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：更新登录数据错误'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo3]'];
        }
        Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录成功'],0);
        return ['error'=>0,'msg'=>'退出登录成功','data'=>'退出登录成功'];
        //重置数据库  redis 只的状态
    }


}
