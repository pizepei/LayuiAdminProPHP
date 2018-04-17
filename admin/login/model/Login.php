<?php
namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
use app\login\model\MainUser as Main;
use app\login\model\LoginLog as Log;
use app\login\model\LoginMainToken as Token;
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
     * [loginActionRedis 登录数据获取方法]
     * @Effect
     * @param  [type] $name     [description]
     * @param  [type] $Password [description]
     * @return [type]           [description]
     */
    public function  loginActionRedis($name,$Password,$remember,$rememberTime)
    {
        // 初始化时间（登录相关全局都使用这个）
        $this->Time =time(); 
        $lastTime = 'off';
        if($remember != 0){
            if($rememberTime <=1 || $rememberTime >30){
                return ['code'=>1,'msg'=>'非法的临时登录时间'];
            }
            $lastTime = $this->Time;
        }

        $Main = new Main;
        //获取数据
        $User = $Main->loginAction($name);

        //判断获取数据是否有问题  （有没有这个用户）
        if(!$User)return ['code'=>1,'msg'=>'没有用户'];


        //判断是否超过登录限制
        //       密码错误次数    >= 系统设置       同时                  
        if(($User->login_error_count >=self::login_error_count) ){
            //当前时间戳 - 上次错误时间戳    <= 系统设置7200
            if(($this->Time-strtotime($User->login_error_count_time)) <= self::login_error_count_time){

                if($User->login_error_count >self::login_error_count){

                    $infoData = '24小时内密码错误过多'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                    Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                    return ['code'=>1,'msg'=>'限制登陆','data'=>$infoData];
                }

                $infoData = '密码错误超过'.self::login_error_count.'次'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                return ['code'=>1,'msg'=>$infoData];
            }
        }

        //实例化 密码安全类
        $Safety = new \Safety\Safetylogin('admin');

        //判断 密码是否正确
        if(!$Safety->passwordVerify($Password,$User->pwd_salt,$User->pwd_hash)){

            $User->login_error_count = $User->login_error_count+1;
            $User->login_error_count_time = date('Y-m-d H:i:s');
            $User->save();
            //写入日志
            Log::addLog(['id'=>$User->id,'info'=>'账号或者密码第'.$User->login_error_count.'次错误'],1);
            return ['code'=>1,'msg'=>'账号或者密码错误'];

        };

        //获取登录配置数据
        $ConfigLogin = $User->LoginMainConfig;
        // 获取有效期配置
        if($lastTime == 'off'){
            $rememberTime = $ConfigLogin->overdue;
        }else{
            $rememberTime = $rememberTime*60;
        }

        //获取同时登录设备数量
        $login_count =$ConfigLogin->login_count;

        // 获取现在同时登录设备数量
        $RedisLogin = new \redis\RedisLogin(config('admin_login_redis'));

        $Rlogin_count =$RedisLogin->get_key_count($User->id);

        //判断是否超过登录限制
        if($Rlogin_count >$login_count){

            $infoData = '超过同时在线上限'.'限制在线'.$login_count.'|在线'.$Rlogin_count;
            Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
            return ['code'=>1,'msg'=>'超过同时在线上限'];
        }

        //生成 JWT
        //signature 唯一签名   
        //sub所面向的用户（用户id） 
        //iat 的签发时间 
        //jti 唯一身份标识（一次性token）主要用来作为一次性token,从而回避重放攻击。
        $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);

        //判断 redis 存储结果
        if(!$RedisLogin ->set_key_token($Signature)){
            // 错误 尝试
            $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
            //再次判断
            if($RedisLogin ->set_key_token($Signature)){
                Log::addLog(['id'=>$User->id,'info'=>'redis 存储JWT失败'],1);

                return ['code'=>1,'msg'=>'请稍后再尝试[loo1]'];
            }
        }

        //更新redis 中的用户数据
        Main::setUserData($User->id,true);

        //写入日志
        $request = Request::instance();
        Log::addLog(['id'=>$User->id,'info'=>'登录成功ip:'.$request->ip()],0);
        return ['code'=>0,'msg'=>'登录成功','access_token'=>$Signature['header'].'.'.$Signature['playload'].'.'.$Signature['signature']];

    }

    /**
     * [loginAction 登录数据获取方法]
     * @Effect
     * @param  [type] $name     [description]
     * @param  [type] $Password [description]
     * @return [type]           [description]
     */
    public function  loginAction($name,$Password,$remember,$rememberTime)
    {

        /**
         * 登录逻辑简单说明
         *流程如下
         *1、获取自定义的快捷登录控制 默认 off  不开启 如在1-30之间为正常数据（实际上就是1-30分钟，这里的时间是1-30分钟内如果没有进行操作就定义为操作超时）
         *  
         *2、获取用户数据 判断是否有这个用户
         *  对获取到的用户数据解析分销判断。
         *      （1）、判断密码错误次数与上次密码错误时间（规则：总错误次数超过 login_error_count定义 就判断上次错误的时间是否在 login_error_count_time 设置的限制内）
         *      
         *3、获取对应用户的登录限制配置数据
         *     要单一登录的有效期、可同时在线的设备数量
         *4、使用安全类判断密码是否正确
         *  如果错误就写入密码错误记录方便2的判断
         *
         *5、安全类生成 JWT 数据
         *      按照规范保存都Redis中
         *6、准备登录记录（mysql中的）用以控制判断同时登录的设备数量
         *  由于买一个用户在admin_login_main_token表中的数据是固定的（比如当设置3个设备同时在线 那么就只有3个记录、后来设置5个设备同时在线当同时在线的设备依然只有3台时是不会自己记录而是修改之前已经过期的记录，当第四台设备登录时会写入新的记录）
         *  这里好判断如果没有超过登录限制就获取最近的一条登录记录的id进行重新写入记录（修改）
         */

        $Main = new Main;
        //获取数据
        $User = $Main->loginAction($name);
        //判断获取数据是否有问题  （有没有这个用户）
        if(!$User)return ['code'=>1,'msg'=>'没有用户'];


        //判断是否超过登录限制
        //       密码错误次数    >= 系统设置       同时                  
        if(($User->login_error_count >=self::login_error_count) ){
            //当前时间戳 - 上次错误时间戳    <= 系统设置7200
            if(($this->Time-strtotime($User->login_error_count_time)) <= self::login_error_count_time){

                if($User->login_error_count >self::login_error_count){

                    $infoData = '24小时内密码错误过多'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                    Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                    return ['code'=>1,'msg'=>'限制登陆','data'=>$infoData];
                }

                $infoData = '密码错误超过'.self::login_error_count.'次'.'限制登陆'.(self::login_error_count_time/(60*60)).'小时';
                Log::addLog(['id'=>$User->id,'info'=>$infoData],1);
                return ['code'=>1,'msg'=>$infoData];
            }
        }

        //实例化 密码安全类
        $Safety = new \Safety\Safetylogin('admin');

        //判断 密码是否正确
        if(!$Safety->passwordVerify($Password,$User->pwd_salt,$User->pwd_hash)){

            $User->login_error_count = $User->login_error_count+1;
            $User->login_error_count_time = date('Y-m-d H:i:s');
            $User->save();
            //写入日志
            Log::addLog(['id'=>$User->id,'info'=>'账号或者密码第'.$User->login_error_count.'次错误'],1);
            return ['code'=>1,'msg'=>'账号或者密码错误'];
        };
        //获取登录配置数据
        $ConfigLogin = $User->LoginMainConfig;
        // 获取有效期配置
        $Overdue = $ConfigLogin->overdue;
        //获取同时登录设备数量
        $login_count =$ConfigLogin->login_count;

        // 初始化时间（登录相关全局都使用这个）
        $this->Time =time(); 
        $lastTime = 'off';

        if($remember != 0){
            if($rememberTime <=1 || $rememberTime >30){
                return ['code'=>1,'msg'=>'非法的临时登录时间'];
            }
            $lastTime = $this->Time;
        }else{
            $rememberTime = $Overdue;
        }
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

                return ['code'=>1,'msg'=>'超过同时在线上限'];
            }
            //生成 JWT
            //signature 唯一签名   
            //sub所面向的用户（用户id） 
            //iat 的签发时间 
            //jti 唯一身份标识（一次性token）主要用来作为一次性token,从而回避重放攻击。
            $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
                                 
            $RedisLogin = new \redis\RedisLogin(config('admin_login_redis'));
            //判断 redis 存储结果
            if(!$RedisLogin ->set_hash_token($Signature)){
                // 错误 尝试
                $Signature = $Safety->addJWT($User->id,$lastTime,$rememberTime,$this->Time);
                //再次判断
                if($RedisLogin ->set_hash_token($Signature)){
                    //放弃
                    $Token->rollback();
                    return ['code'=>1,'msg'=>'请稍后再尝试[loo1]'];
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
                    return ['code'=>1,'msg'=>'请稍后再尝试'];
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
                return ['code'=>1,'msg'=>'请稍后再尝试'];
            }
            $Token->commit();

            //写入日志
            Log::addLog(['id'=>$User->id,'info'=>'登录成功ip:'.$request->ip()],0);


            return ['code'=>0,'msg'=>'登录成功','access_token'=>$Signature['header'].'.'.$Signature['playload'].'.'.$Signature['signature']];

           } catch (\Exception $e) {
            $Token->rollback();
            Log::addLog(['id'=>$User->id,'info'=>'登录失败ip:'.$request->ip().'原因：事务错误'],1);
            // dump($e);
            return ['code'=>1,'msg'=>'请稍后再尝试[loo1]'];
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
        $pasData = new \Safety\Safetylogin('admin');
        $password = $pasData->addPassword($post['password']);
        //并且开启事务
        $Main = new Main;
        $Main->startTrans();
        try{
            //查询邮箱是否存在
            $MainEmail = $Main->lock(true)->where('email',$post['cellemail'])->find();
            if($MainEmail){
                $Main->rollback();
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱已经注册',4);

                return ['error'=>0,'msg'=>'注册失败','data'=>'email已经注册'];
            }
            //生成登录名称
            $login_name = Mt_str(2,4).time().Mt_str(7,4);
            //判断登录名称
            $Mainlogin_name = $Main->lock(true)->where('login_name',$login_name)->find();
            if($Mainlogin_name){

                $login_name = time().Mt_str(6,4);
                $Mainlogin_name = $Main->lock(true)->where('login_name',$login_name)->find();

                if($Mainlogin_name){
                    $Main->rollback();
                    ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因登录名称字段写入失败（重复）',4);

                    return ['error'=>0,'msg'=>'系统繁忙','data'=>'请稍后再尝试[loo1]'];     
                }

            }

            //准备数据
            $Main->nickname =   $post['nickname']  ;//昵称字符串
            $Main->login_name =  $login_name    ;//登录名称
            $Main->phone =  null;//手机号码
            $Main->email =  $post['cellemail']   ;//电子邮件
            $Main->inviter_id =  0;//邀请人
            $Main->pwd_salt = $password['salt'];//密码盐
            $Main->pwd_hash =  $password['hash'];//密码盐+密码的hash
            $Main->login_error_count =   0  ;//登录错误 5次  禁止2小时  发送邮件通知
            $Main->combo =   1   ;//套餐
            $Main->grade =   1;//会员等级 1、2、3、4、5、6、7、8、9
            $Main->user_group =  1;//用户组
            $Main->balance =  0   ;//用户余额
            $Main->integral =   0  ;//用户积分余额
            $Main->autonym =   1 ;//认证状态，0为正常，1为锁定
            $Main->status =   0 ;//状态，0为正常，1为锁定
            $Main->isdel =   0   ;//软删除  0正常  1删除
            $Main->register_way =    '';//注册方式
            $Main->create_time =  Mdate();//注册时间

            if(!$Main->save()){
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因：用户核心数据表写入失败：',4);
                $Main->rollback();
            }
            // 登录创建配置数据

            $Config = new Config;
            $Config->uid = $Main->id;//用户id
            $Config->type = 0;//登录类型
            $Config->login_count = 3;//同时登录数量
            $Config->overdue = 7200;//过期时间 0 不过期 秒
            $Config->status = 0;//状态，0为正常，1为锁定
            $Config->isdel = 0;//软删除  0正常  1删除

            $Config->create_time = Mdate();//软删除  0正常  1删除
            if(!$Config->save()){
                ErrorLog::addLog('注册失败',$post['cellemail'].'：邮箱'.'错误原因：用户配置表写入失败：',4);
              $Main->rollback();
                return ['error'=>1,'msg'=>'系统繁忙','data'=>'请稍后再尝试[zcoo1]'];
            }
            $Main->commit();
            return ['error'=>1,'msg'=>'注册成功','data'=>'注册成功'];
           } catch (\Exception $e) {
            $Main->rollback();
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
    public function logout($access_token,$UserData,$type='mysql')
    {
        //查询 redis 中是否有登录数据
       $RedisLogin = new  \redis\RedisLogin(config('admin_login_redis'));

       if(!$RedisLogin->logout($access_token,$type)){
            //写入日志
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：redis中没用数据'],1);
            return ['code'=>1,'msg'=>'请稍后再尝试[utoo1]'];
       }
       if($type == 'redis'){
            return ['code'=>0,'msg'=>'退出登录成功'];
       }

        $Token = Token::get(['login_access_token'=>$access_token]);
        //判断是否是正常 token 
        if(!$Token){
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：mysql中没用数据'],1);
            return ['code'=>1,'msg'=>'请稍后再尝试[utoo2]'];
        }
        $Token->login_access_token_time = date('Y-m-d H:i:s',strtotime($Token->login_access_token_time)-config('JWT_admin.exp'));
        if(!$Token->save()){
            Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录失败：更新登录数据错误'],1);
            return ['code'=>1,'msg'=>'请稍后再尝试[utoo3]'];
        }

        Log::addLog(['id'=>$UserData['id'],'info'=>$type.'退出登录成功'],0);
        return ['code'=>0,'msg'=>'退出登录成功'];
        //重置数据库  redis 只的状态
    }


}
