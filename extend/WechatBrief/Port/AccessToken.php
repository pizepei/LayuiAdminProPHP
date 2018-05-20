<?php
/**
 * @Author: pizepei
 * @Date:   2017-06-03 14:39:36
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-12 23:43:58
 */
namespace WechatBrief\Port;
use WechatBrief\func;
/**
 * 获取access_token
 */
class AccessToken{

     protected $config = '';//配置信息
     protected $site_file = '../extend/WechatBrief/Module/Cache/access_token.json';
     protected $redis = '';//redis
     protected $access_token = '';//access_token
     protected $expires_time = 3600;//expires_time

     //构造函数，获取Access Token
     public function __construct($config=NULL)
     {
        if($config== NULL){
            //获取配置
            $this->config = config('wechat_config');
        }else{
            $this->config = $config;
        }
     }
     /**
      * [redis redis缓存]
      * @Effect
      * @return [type] [description]
      */
     protected function redis_cache()
     {

        $redis = new \Redis();
        $redis->connect($this->config['host'], $this->config['port'],1);
        if(!empty($this->config['password'])){
            $redis->auth($this->config['password']);//登录验证密码，返回【true | false】
        }
        $redis->select($this->config['select']);
        $this->redis = $redis;
        //获取判断
        $this->access_token = $this->redis->get($this->config['type']);
        if(!$this->access_token){
            //获取
            if(!$this->get_access_token()){
                return false;
            }

            //存储
            $this->redis->set($this->config['type'],$this->access_token['access_token']);
            $this->redis->expire($this->config['type'],$this->access_token['expires_in']);
            return $this->access_token['access_token'];
        }
        return $this->access_token;

     }

     /**
      * [redis file缓存]
      * @Effect
      * @return [type] [description]
      */
     protected function file_cache()
     {
        //读取文件
        $res = file_get_contents($this->site_file);        
        $this->access_token = json_decode($res, true);
        //如果不存在  比如从redis 切换到file
        if(!isset($this->access_token['expires_time'])){
            if(!$this->get_access_token()){
                return false;
            }
            // expires_time 创建时间
            // expires_in 有效期时间
            file_put_contents(
                $this->site_file, '{"access_token": "'.$this->access_token['access_token'].'", "expires_time": '.time().',"expires_in": '.$this->access_token['expires_in'].'}'
                );

        }else if(time() > ($this->access_token['expires_time'] + $this->access_token['expires_in'])){
            if(!$this->get_access_token()){
                return false;
            }
            // expires_time 创建时间
            // expires_in 有效期时间
            file_put_contents(
                $this->site_file, '{"access_token": "'.$this->access_token['access_token'].'", "expires_time": '.time().',"expires_in": '.$this->access_token['expires_in'].'}'
                );
        }

        return $this->access_token['access_token'];
     }

     /**
      * [access_token 获取]
      * @Effect
      * @return [type] [description]
      */
     public function access_token(){

        //判断存储方式
        if($this->config['cache_type']=='redis'){
            //获取
            return $this->redis_cache();

        }else if($this->config['cache_type']=='file'){
            //获取
            return $this->file_cache();
        }
     }
    /**
     * [get_access_token 获取数据]
     * @Effect
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected function get_access_token()
    {  
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->config['appid']."&secret=".$this->config['appsecret'];
        $res = func::http_request($url);
        $this->access_token  = json_decode($res, true);

        if(isset($this->access_token['errcode'])){
            return false;
        }
        return true;

     }
 }
