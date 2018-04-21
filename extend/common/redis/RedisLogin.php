<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:32:25
 */
namespace common\redis;
use app\login\model\LoginLog as Log;

class RedisLogin  extends RedisModel{

    protected $name = null;
    protected $name_list = null;


    public function __construct()
    {
        parent::__construct();

        $this->name = config('JWT_'.$this->config['type'].'.HashToken');
        $this->name_list = config('JWT_'.$this->config['type'].'.LoginToken');
        $this->exp = config('JWT_'.$this->config['type'].'.exp');
    }


    /**
     * [set_hash_token redis hash 存储 token（mysql）]
     * @Effect
     * @param  [type] $tokenData [description]
     * @param  string $name      [description]
     * @param  string $name_list [description]
     * @return [type]            [description]
     */
    public function  set_hash_token($tokenData)
    {

        //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $this->redis->multi(\Redis::MULTI);
            $this->redis->rpush($this->name_list,$tokenData['signature']);//创建登录token链表]队列处理
            //hash数据处理
            $this->redis->hsetnx($this->name,$tokenData['signature'].'_jti',$tokenData['jti']);
            $this->redis->hsetnx($this->name,$tokenData['signature'].'_iat',$tokenData['iat']);
            $this->redis->hsetnx($this->name,$tokenData['signature'].'_sub',$tokenData['sub']);
            $this->redis->hsetnx($this->name,$tokenData['signature'].'_disp',$tokenData['disp']);
            //创建上次操作时间（没有选择旅客模式时 为字符串off）
            //当登录是选择谨慎模式（旅客模式）时每次请求鉴权时都写入当前时间戳  以便在一定时间无操作（鉴权）时吊销token 

            $this->redis->hsetnx($this->name,$tokenData['signature'].'_lastTime',$tokenData['lastTime']);
            $this->redis->hsetnx($this->name,$tokenData['signature'].'_rememberTime',$tokenData['rememberTime']);

        //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $Error = $this->redis->exec();

        if($this->get_hash_token_error($Error,$tokenData['sub'])){//判断事务操作的结果
            return true;
        }
        return false;
    }

    /**
     * [set_hash_token redis存储 token]
     * @Effect
     * @param  [type] $tokenData [description]
     * @param  string $name      [description]
     * @param  string $name_list [description]
     * @return [type]            [description]
     */
    public function set_key_token($tokenData)
    {
        //首先存入数据

        //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $this->redis->multi(\Redis::MULTI);

             $this->redis->set($this->name.$tokenData['signature'].'_jti',$tokenData['jti']);//设置key=aa value=1 [true]
             $this->redis->expire($this->name.$tokenData['signature'].'_jti',$tokenData['rememberTime']);//设置失效时间[true | false]

             $this->redis->set($this->name.$tokenData['signature'].'_iat',$tokenData['iat']);
             $this->redis->expire($this->name.$tokenData['signature'].'_iat',$tokenData['rememberTime']);

             $this->redis->set($this->name.$tokenData['signature'].'_sub',$tokenData['sub']);
             $this->redis->expire($this->name.$tokenData['signature'].'_sub',$tokenData['rememberTime']);

             $this->redis->set($this->name.$tokenData['signature'].'_disp',$tokenData['disp']);
             $this->redis->expire($this->name.$tokenData['signature'].'_disp',$tokenData['rememberTime']);

            //设置登录条件数据
             $this->redis->set($this->name.$tokenData['signature'].'_lastTime',$tokenData['lastTime']);
             $this->redis->set($this->name.$tokenData['signature'].'_rememberTime',$tokenData['rememberTime']);

             $this->redis->expire($this->name.$tokenData['signature'].'_lastTime',86400);
             $this->redis->expire($this->name.$tokenData['signature'].'_rememberTime',86400);

             $this->redis->hsetnx($this->name.'_uid'.$tokenData['sub'],$tokenData['signature'],time());


        //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $Error = $this->redis->exec();

        if($this->get_hash_token_error($this->name,$tokenData['sub'])){//判断事务操作的结果
            return true;
        }
        return false;
    }

    /**
     * [get_key_count 获取同时在线的设备数量同时删除超过在的数据（redis版本）]
     * @Effect
     * @param  [type] $uid  [用户id]
     * @param  [type] $time [过期的限制秒s]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function get_key_count($uid)
    {
        /**
         * 逻辑介绍
         * 在 $name.'_uid'.$uid 表中 保存了之前的登录数据  key 为 $tokenData['signature']  token
         *
         * 使用token查询是否有数据（由于数据被设置了有效期 过期自动就删除了） 所以就可以判断是否在线了
         * 如果使用token查询不到值 就删除这个记录
         *
         * 快捷登陆了的有效期更新由调用API时鉴权时鉴权通过后更新
         */
        $Data = $this->redis->hgetall($this->name.'_uid'.$uid);//查，返回哈希表key中的所有域和值。[当key不存在时，返回一个空表]
        $i = 0;
        //判断
        foreach ($Data as $key => $value) {

            if($this->redis->get($this->name.$key.'_sub')){
                $i = $i+1;
            }else{
                //删除没用在线的数据
                $this->redis->hdel($this->name.'_uid'.$uid,$key);//删，删除指定下标的field,不存在的域将被忽略,[num | false]  
            }

        }
        return $i;
    }

    /**
     * [get_key_token 批量获取redis中的数据（redis版本登录）同时更新快捷登录的时间]
     * @Effect
     * @param  [type] $token [token]
     * @return [type]        [description]
     */
    public function get_key_token($token)
    {
        $Data = array();
        $Data['lastTime'] = $this->redis->get($this->name.$token.'_lastTime');
        $Data['jti'] = $this->redis->get($this->name.$token.'_jti');
        $Data['iat'] = $this->redis->get($this->name.$token.'_iat');
        $Data['sub'] = $this->redis->get($this->name.$token.'_sub');
        $Data['disp'] = $this->redis->get($this->name.$token.'_disp');

        $Data['rememberTime'] = $this->redis->get($this->name.$token.'_rememberTime');

        foreach ($Data as $key => $value) {
            if(empty($value) || $value == null || $value == false){
                return false;
            }
        }
        if($Data['lastTime'] != 'off'){
            $this->redis->expire($this->name.$token.'_jti',$Data['rememberTime']);
            $this->redis->expire($this->name.$token.'_iat',$Data['rememberTime']);
            $this->redis->expire($this->name.$token.'_sub',$Data['rememberTime']);
            $this->redis->expire($this->name.$token.'_disp',$Data['rememberTime']);
        }
        return $Data;
    }
    /**
     * [get_key redis中的数据（redis版本登录）]
     * @Effect
     * @param  [type] $token [description]
     * @param  [type] $name  [description]
     * @return [type]        [description]
     */
    public function get_key($token,$name){
        return $this->redis->get($this->name.$token.'_'.$name);
    }


    // /**
    //  * [get_key_count_sh 获取快捷登录数据]
    //  * @Effect
    //  * @param  [type] $uid  [description]
    //  * @param  [type] $time [description]
    //  * @param  string $type [description]
    //  * @return [type]       [description]
    //  */
    // public function get_key_count_sh($uid,$time)
    // {

    //     $Data = $this->redis->hgetall($this->name.'_uid'.$uid);//查，返回哈希表key中的所有域和值。[当key不存在时，返回一个空表]
    //     $i = 0;
    //     //判断
    //     foreach ($Data as $key => $value) {

    //         // 当前- 创建  < 过期限制 72001
    //         if(time()-$value <$time){
    //             $i = $i+1;
    //         }else{
    //             //删除没用在线的数据
    //             $this->redis->hdel($this->name.'_uid'.$uid,$key);//删，删除指定下标的field,不存在的域将被忽略,[num | false]
    //         }
    //     }
    //     return $i;
    // }


    /**
     * [get_token 获取token]
     * @Effect
     * @param  [type] $Name     [description]
     * @param  string $TokeName [description]
     * @return [type]           [description]
     */
    public function get_token($Token,$Name)
    {

        return $this->redis->hget($this->name,$Token.'_'.$Name);
    }

    /**
     * [set_lastTime 更新上次操作时间]
     * @param [type] $Token    [description]
     * @param string $type     [description]
     * @param string $TokeName [description]
     */
    public function set_lastTime($Token)
    {
        $this->redis->hset($this->name,$Token.'_lastTime',time());
    }

    /**
     * [get_hash_token_error 判断事务操作的结果]
     * @Effect
     * @param  [type] $Data [务操作的结果]
     * @return [type]       [description]
     */
    public function   get_hash_token_error($Data,$uid){

        if(is_array($Data)){
            foreach ($Data as $key => $value) {
                if($value == false){
                    Log::addLog(['id'=>$uid,'info'=>'登录时redis存储Token失败，详细信息：'.json_encode($Data)],2);
                    return false;
                }
            }
            return true;
        }
    }
    /**
     * [get_user_data 获取用户数据]
     * @param  [type] $id [用户id]
     * @return [type]     [description]
     */
    public function get_user_data($id)
    {
        $Data = $this->redis->hget('UserData','User'.'_'.$id);
        if(!$Data){ return false;}
        return json_decode($Data,true);
    }
    /**
     * [set_user_data 写入用户数据 或者修改]
     * @param [type] $id   [用户id]
     * @param [type] $Data [用户数据]
     */
    public function set_user_data($id,$Data)
    {
        $Data = json_encode($Data);
        $error = $this->redis->hset('UserData','User'.'_'.$id,$Data);
        if($error == false ){ 
            return false;
        }
        return true;
    }

    /**
     * [logout 退出登录方法]
     * @Effect
     * @param  [type] $token [description]
     * @param  string $type  [description]
     * @return [type]        [description]
     */
    public function logout($token,$type='mysql')
    {
        if($type != 'mysql'){
            $Count = $this->redis->del([$this->name.$token.'_lastTime',$this->name.$token.'_jti',$this->name.$token.'_iat',$this->name.$token.'_sub',$this->name.$token.'_disp',$this->name.$token.'_rememberTime']);
            if($Count >1){
                return true;
            }
            return false;
        }
        //查询 redis 中是否有登录数据
        $value = $this->redis->hget($this->name,$token.'_iat');//查，取值【value|false】
        if(!$value){
            return false;
        }
        //重置 redis 的签发状态
        $this->redis->hset($this->name,$token.'_iat',0);//增，改，将哈希表key中的域field的值设为value,不存在创建,存在就覆盖【1 | 0】
        return true;
    }
}
