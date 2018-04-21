<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:38:33
 */
namespace common\redis;

class RedisModel 
{
    /** @var \Redis */
    protected $redis = null;

    protected $config  = [
        'host'         => '127.0.0.1', // redis主机
        'port'         => 6379, // redis端口
        'password'     => '', // 密码
        'select'       => 0, // 操作库
        'expire'       => 3600, // 有效期(秒)
        'timeout'      => 0, // 超时时间(秒)
        'persistent'   => true, // 是否长连接
        'session_name' => '', // sessionkey前缀
        'type' => 'user', // 链接类型
    ];

    public function __construct($config = [])
    {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('not support:redis');
        }

        try{
            $this->config = array_merge($this->config, $config);
            $redis = new \Redis();
            $redis->connect($this->config['host'], $this->config['port'],1);
            if(!empty($this->config['password'])){
                $redis->auth($this->config['password']);//登录验证密码，返回【true | false】
            }
            $redis->select($this->config['select']);
            $this->redis = $redis;
            $this->type = $redis;
            

        }catch(\Exception $e){
            echo json_encode(['code'=>1001]);
            exit;
        }

    }

}
