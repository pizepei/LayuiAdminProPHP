<?php
namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
use app\login\model\AppMainUser as MainApp;
use app\login\model\AppLoginLog as Log;
use app\login\model\AppLoginMainToken as Token;
use app\login\model\AppLoginMainConfig as Config;
use heillog\ErrorLog;
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
     * @param  [type] $name     [登录名称]
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

        
        $MainApp = new MainApp;
        //获取数据
        $User = $MainApp->loginAction($name);
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
        $ConfigLogin = $User->AppLoginMainConfig;
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
            $Safety = new \Safety\Safetylogin('user');

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

            $RedisLogin = new \redis\RedisLogin(config('user_login_redis'));
            //判断 redis 存储结果
            if(!$RedisLogin ->set_hash_token($Signature,'user')){
                // 错误 尝试
                $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
                //再次判断
                if($RedisLogin ->set_hash_token($Signature,'user')){
                    //放弃
                    $Token->rollback();
                    return ['error'=>0,'msg'=>'请稍后再尝试[loo2]','data'=>'请稍后再尝试生成 JWT并且存储'];
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
                    return ['error'=>0,'msg'=>'请稍后再尝试[loo3]','data'=>'数据库操作异常'];
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
                return ['error'=>0,'msg'=>'请稍后再尝试[loo4]','data'=>'Token失败'];
            }

            //写入日志
            Log::addLog(['id'=>$User->id,'info'=>'登录成功ip:'.$request->ip()],0);
            $Token->commit();


            return ['error'=>1,'msg'=>'登录成功','data'=>$Signature['header'].'.'.$Signature['playload'].'.'.$Signature['signature']];

           } catch (\Exception $e) {
            $Token->rollback();
            throw $e;

            return ['error'=>0,'msg'=>'请稍后再尝试[loo5]','data'=>'请稍后再尝试[loo3]'];
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
     * [register 用户注册]
     * @Effect
     * @return [type] [description]
     */
    public function register($post)
    {

        //准备用户数据
        // 产生密码
        $pasData = new \Safety\Safetylogin('user');
        $password = $pasData->addPassword($post['password']);

        //并且开启事务
        $MainApp = new MainApp;
        $MainApp->startTrans();

        try{

            //查询邮箱是否存在
            $MainEmail = $MainApp->lock(true)->where('email',$post['cellemail'])->find();
            if($MainEmail){
                $MainApp->rollback();
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱已经注册',4);

                return ['error'=>0,'msg'=>'注册失败','data'=>'email已经注册'];
            }
            //生成登录名称
            $login_name = Mt_str(2,4).time().Mt_str(7,4);
            //判断登录名称
            $Mainlogin_name = $MainApp->lock(true)->where('login_name',$login_name)->find();
            if($Mainlogin_name){

                $login_name = time().Mt_str(6,4);
                $Mainlogin_name = $MainApp->lock(true)->where('login_name',$login_name)->find();

                if($Mainlogin_name){
                    $MainApp->rollback();
                    ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因登录名称字段写入失败（重复）',4);

                    return ['error'=>0,'msg'=>'系统繁忙','data'=>'请稍后再尝试[loo1]'];     
                }

            }

            //准备数据
            $MainApp->nickname =   $post['nickname']  ;//昵称字符串
            $MainApp->login_name =  $login_name    ;//登录名称
            $MainApp->phone =  null;//手机号码
            $MainApp->email =  $post['cellemail']   ;//电子邮件
            $MainApp->inviter_id =  0;//邀请人
            $MainApp->pwd_salt = $password['salt'];//密码盐
            $MainApp->pwd_hash =  $password['hash'];//密码盐+密码的hash
            $MainApp->login_error_count =   0  ;//登录错误 5次  禁止2小时  发送邮件通知
            // $this->login_error_count_time =     ;//上次密码错误时间
            $MainApp->combo =   1   ;//套餐
            $MainApp->grade =   1;//会员等级 1、2、3、4、5、6、7、8、9
            $MainApp->user_group =  1;//用户组
            $MainApp->balance =  0   ;//用户余额
            $MainApp->integral =   0  ;//用户积分余额
            $MainApp->wc_openid =    null  ;//微信openid
            $MainApp->apy_openid =    null  ;//支付宝openid
            $MainApp->autonym =   1 ;//认证状态，0为正常，1为锁定
            $MainApp->status =   0 ;//状态，0为正常，1为锁定
            $MainApp->isdel =   0   ;//软删除  0正常  1删除
            $MainApp->register_way =    '';//注册方式
            $MainApp->create_time =  Mdate();//注册时间

            if(!$MainApp->save()){
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因：用户核心数据表写入失败：',4);

                $MainApp->rollback();

            }
            // 登录创建配置数据

            $Config = new Config;
            $Config->uid = $MainApp->id;//用户id
            $Config->type = 0;//登录类型
            $Config->login_count = 3;//同时登录数量
            $Config->overdue = 7200;//过期时间 0 不过期 秒
            $Config->status = 0;//状态，0为正常，1为锁定
            $Config->isdel = 0;//软删除  0正常  1删除

            $Config->create_time = Mdate();//软删除  0正常  1删除
            if(!$Config->save()){
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因：用户配置表写入失败：',4);
                $MainApp->rollback();
                return ['error'=>1,'msg'=>'系统繁忙','data'=>'请稍后再尝试[zcoo1]'];
            }
            $MainApp->commit();
            return ['error'=>1,'msg'=>'注册成功','data'=>'注册成功'];
           } catch (\Exception $e) {
            $MainApp->rollback();
            ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因：写入数据流程事务失败'.json_encode($e, JSON_FORCE_OBJECT),4);
            throw $e;
            return ['error'=>0,'msg'=>'系统繁忙','data'=>'请稍后再尝试[zoo1]'];
        }


    }


    /**
     * [logout 退出登录方法]
     * @Effect
     * @return [type] [description]
     */
    public function logout($access_token,$UserData)
    {
        //查询 redis 中是否有登录数据
       $RedisLogin = new  \redis\RedisLogin(config('user_login_redis'));
       if(!$RedisLogin->logout($access_token,'user')){
            //写入日志
            Log::addLog(['id'=>$UserData['id'],'info'=>'退出登录失败：redis只没用数据'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo1]'];
       }

        $Token = Token::get(['login_access_token'=>$access_token]);
        //判断是否是正常 token 
        if(!$Token){
            Log::addLog(['id'=>$UserData['id'],'info'=>'退出登录失败：mysql只没用数据'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo2]'];
        }
        $Token->login_access_token_time = date('Y-m-d H:i:s',strtotime($Token->login_access_token_time)-config('JWT_user.exp'));
        if(!$Token->save()){
            Log::addLog(['id'=>$UserData['id'],'info'=>'退出登录失败：更新登录数据错误'],1);
            return ['error'=>1,'msg'=>'退出登录失败','data'=>'请稍后再尝试[utoo3]'];
        }
        Log::addLog(['id'=>$UserData['id'],'info'=>'退出登录成功'],0);
        return ['error'=>0,'msg'=>'退出登录成功','data'=>'退出登录成功'];
        //重置数据库  redis 只的状态
    }


}
