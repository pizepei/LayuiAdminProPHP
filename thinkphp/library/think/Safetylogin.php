<?php
/**
 * @Author: anchen
 * @Date:   2018-02-07 21:47:44
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-01 15:12:23
 */
namespace think;
use think\Request;
use redis\RedisLogin;
/**
 * 加密安全类
 */
class Safetylogin{

    public $type = 'user';
    public $config = '';
    public $Passwordconfig = '';

    public function __construct($type='')
    {

        if($type != ''){
            $this->type = $type;
        }
        $this->Passwordconfig = config('Password_'.$this->type);
        $this->config = config('JWT_'.$this->type);
    }
    /**
     * [addPassword 密码生成方法]
     * @Effect
     * @param  [type] $password [description]
     * @param  [type] $cost     [description]
     */
    public function addPassword($password,$cost=0){

        $config = $this->Passwordconfig;

        if($cost==0){

            $cost = $config['cost'];
        }

        //原始密码 + md5(config 中的salt  + 随机字符串)
        
        $salt = md5($config['salt'].Mt_str(10,3));
        $password = $password.$salt;

        $options = [
            'cost' => $cost,
        ];

        $hash = password_hash($password, PASSWORD_DEFAULT, $options);

        return ['hash'=>$hash,'salt'=>$salt];
    }

    /**
     * [passwordVerify 密码验证]
     * @param  [type] $passWord [需要验证的密码字符串]
     * @param  [type] $salt     [盐]
     * @param  [type] $hash     [验证哈希值]
     * @return [type]           [结果]
     */
    public function passwordVerify($passWord,$salt,$hash)
    {
        // $config = config('Password');
        $passWord = $passWord.$salt;

        if(password_verify($passWord,$hash)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * [addJWT 创建]
     * @Effect
     */
    public function addJWT($user,$lastTime,$rememberTime,$iat='')
    {

        $config = $this->config;
        
        $header = base64_encode(json_encode(['typ'=>$config['typ'],'alg'=>$config['alg']]));
        $salt = $config['salt'];
        $jti = $this->Mt_str(32,3);//生成随机jti
        $disp = $this->Mt_str(6,3);//生成随机jti
        $exp = $config['exp'];
        $iss = $config['iss'];
        if($iat == ''){
            $iat = time();
        }

        $playload = base64_encode(
            json_encode([
                'iss'=>$iss,//jwt签发者
                'sub'=>$user,//jwt所面向的用户
                'exp'=>$exp,//jwt的过期时间，这个过期时间必须要大于签发时间
                'iat'=>$iat,//jwt的签发时间
                'disp'=>$disp,//jwt的唯一身份标识，主要用来作为一次性token,从而回避重放攻击。
                'AGENT'=>md5($_SERVER['HTTP_USER_AGENT']),//保存登录设备的基本标识符 简单的防止复制链接登录
            ])
        );
        //拼接签名
        $signature = $header.$playload.$salt.$jti;
        //md5
        $signature = md5($signature);
        return [
            'signature'=>$signature,
            'jti'=>$jti,
            'iat'=>$iat,
            'sub'=>$user,
            'header'=>$header,
            'playload'=>$playload,
            'disp'=>$disp,
            'lastTime'=>$lastTime,
            'rememberTime'=>$rememberTime
        ];
    }
    

    public function decodeJWT($Data)
    {

        $config = $this->config;
        $salt = $config['salt'];//salt
        $exp = $config['exp'];//超时配置
        //切割获取数据
        $Data = explode('.',$Data);
        if(count($Data) != 3){
            return ['error'=>1,'msg'=>'非法请求','data'=>'非法的JWT数据'];
        }
        $header = $Data[0];
        $playload = $Data[1];
        $signature = $Data[2];

        //设备识别码
        $AGENT = json_decode(base64_decode($playload),true)['AGENT'];
        if($AGENT != md5($_SERVER['HTTP_USER_AGENT'])){
            return ['error'=>2,'msg'=>'用户数据验证错误','data'=>'AGENT验证失败'];
        }
        //读取Redis
        $redis = new RedisLogin(config($this->type.'_login_redis'));
        
        if(!$redis->get_token($signature,'jti','admin')){
            return ['error'=>01,'msg'=>'用户数据验证错误','data'=>'redis中没有数据'];
        }

        if(md5($header.$playload.$salt.$redis->get_token($signature,'jti','admin')) != $signature){
            return ['error'=>2,'msg'=>'用户数据验证错误','data'=>'错误的JWT数据'];
        }

        $iat = $redis->get_token($signature,'iat','admin');
        $sub = $redis->get_token($signature,'sub','admin');
        $disp = $redis->get_token($signature,'disp','admin');

        $lastTime = $redis->get_token($signature,'lastTime','admin');
        $rememberTime = $redis->get_token($signature,'rememberTime','admin');


        if(!$disp){
            return ['error'=>2,'msg'=>'数据错误','data'=>'redis中disp数据缺失'];
        }  
        if(!$sub){
            return ['error'=>2,'msg'=>'数据错误','data'=>'redis中sub数据缺失'];
        }        
        if(!$iat){
            return ['error'=>2,'msg'=>'数据错误','data'=>'redis中iat数据缺失'];
        }

        if(!$lastTime){
            return ['error'=>2,'msg'=>'数据错误','data'=>'redis中lastTime数据缺失'];
        }


        if(!$rememberTime){
            return ['error'=>2,'msg'=>'数据错误','data'=>'redis中rememberTime数据缺失'];
        }

        //临时登录模式判断
        if($lastTime != 'off'){

            $rememberTime = $rememberTime*60;
            if((time()-$lastTime)>$rememberTime){
                //执行redis 抛弃tken
                $redis->logout($signature,'admin');
                //执行
                $LoginM = new \app\login\model\Login;
                if($LoginM->logout($signature,$usid =['id'=>$sub],'操作超时')['error'] != 0)
                {
                    return ['error'=>1,'msg'=>'意外错误','data'=>$sub];
                }
                return ['error'=>1,'msg'=>'操作超时','data'=>$sub];
            }
            //更新本次鉴权时间
            $redis->set_lastTime($signature,'admin');
        }
        //判断是否登录超时
        if($iat+$exp+2 < time()){
            return ['error'=>1,'msg'=>'登录超时','data'=>$sub];
        }


        return ['error'=>0,'msg'=>'验证通过','data'=>$sub];
    }
    /**
     * [meshSafety 通讯安全验证]
     * @return [type] [description]
     */
    public function meshSafety($domain='')
    {

        // 获取来源IP
        $request = Request::instance();
        // 获取当前域名
        echo $request->domain();
        echo '<hr>';
        //请求方法：get  post ajax
        echo $request->method();
        echo '<hr>';
        echo $request->ip();
        echo '<hr>';
        echo $this->get_client_ip();
        //获取来源域名

    }

    /**
     * [get_client_ip 获取客户端ip]
     * @return [type] [description]
     */
    public function get_client_ip()
    {
        $arr_ip_header = array(
            'HTTP_CDN_SRC_IP',
            'HTTP_PROXY_CLIENT_IP',
            'HTTP_WL_PROXY_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );
        $client_ip = 'unknown';
        foreach ($arr_ip_header as $key)
        {
            if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
            {
                $client_ip = $_SERVER[$key];
                break;      
            }
        }
        return $client_ip;
    }
    /**
     * [Mt_str 生成随机字符串]
     * @Effect
     * @param  [type]  $vel  [类型] 1、全部大写字母 2、混合 大小写字母+数字 3、混合 大小写字母+数字+特殊字符
     * @param  integer $type [数量]
     */
    protected function  Mt_str($vel,$type=1)
    {
        //生成随机字符串
        if($type == 2){
            $str = 'Q2Ww3E4Rr5aTd6Yef7U8gI9Oxczcghh0hP3Aj4S5D6kF7Gtsdj8Hy9J2u3K4L5kZ6Xl72CsV3BNM';
        }else if($type == 1){
            $str = 'QWERTYUIOPASDFGHJKLZXCVBNMQWERTYUIOPASDFGHJKLZXCVBNMQWERTYUIOPASDFGHJKLZXCVB';
        }else if($type == 3){
            $str = 'QWERT~YUaTd#6Yef7U8@gI9czc*ghh0N$MQRTYUI@OPADFGH%JKdj8^Hy9J2u3K&4LkZ6DF*GHJK';
        }
        $mt_str = '';
        for ($i=0; $i <$vel ; $i++) { 
            $mt_str .= $str{mt_rand(0,25)};
        }
        return $mt_str;
    }


}

