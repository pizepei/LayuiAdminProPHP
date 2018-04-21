<?php
/**
 * @Author: anchen
 * @Date:   2018-02-07 21:47:44
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:37:45
 */
namespace common\Safety;
use think\Request;
use common\redis\RedisLogin;

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
     * [addJWT JWT 签名创建]
     * @Effect
     * @param  [type] $user         [user  id]
     * @param  [type] $lastTime     [快捷登录开关 开启就是时间戳  off是关闭]
     * @param  [type] $rememberTime [快捷登录参数]
     * @param  string $iat          [统一时间戳]
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
    
    /**
     * [decodeJWT JWT签名解密]
     * @Effect
     * @param  [type] $Data [需要解密的数据]
     * @param  [type] $type [解密数据的获取方法 有mysql  redis]
     * @return [type]       [description]
     */
    public function decodeJWT($Data,$type='mysql')
    {
        //读取当前的签名配置
        $config = $this->config;
        $salt = $config['salt'];//salt
        // $exp = $config['exp'];//超时配置
        //切割获取数据
        $Data = explode('.',$Data);
        if(count($Data) != 3){
            return ['error'=>1,'msg'=>'非法请求','data'=>'非法的JWT数据'];
        }
        $header = $Data[0];
        $playload = $Data[1];
        $signature = $Data[2];


        $redis = new RedisLogin(config($this->type.'_login_redis'));

        if($type == 'mysql'){
            //mysql  验证签名是否一致
            //读取Redis 只是否有这样的数据
            if(!$redis->get_token($signature,'jti')){
                return ['error'=>011,'msg'=>'用户数据验证错误','data'=>'redis中没有数据'];
            }
            //验证签名是否一致
            if(md5($header.$playload.$salt.$redis->get_token($signature,'jti')) != $signature){
                return ['error'=>2,'msg'=>'用户数据验证错误','data'=>'错误的JWT数据'];
            }
            $RedisData = array();
            //签名一致 在redis 中进行数据获取
            $RedisData['iat'] = $redis->get_token($signature,'iat');
            $RedisData['sub'] = $redis->get_token($signature,'sub');
            $RedisData['disp'] = $redis->get_token($signature,'disp');
            //快捷登录 数据获取
            $RedisData['lastTime'] = $redis->get_token($signature,'lastTime');
            $RedisData['rememberTime'] = $redis->get_token($signature,'rememberTime');

            foreach ($RedisData as $key => $value) {
                if(empty($value)){
                    return ['error'=>2,'msg'=>'数据错误','data'=>'redis中'.$key.'数据缺失','id'=>null];
                }
            }
        }else{
            //批量获取
            $RedisData = $redis->get_key_token($signature);

            if(!$RedisData){
                return ['error'=>1,'msg'=>'操作超时','data'=>0];
            }
            //验证签名是否一致
            if(md5($header.$playload.$salt.$RedisData['jti']) != $signature){
                return ['error'=>2,'msg'=>'用户数据验证错误','data'=>'错误的JWT数据'];
            }
        }

        //快捷临时登录模式判断
        if($RedisData['lastTime'] != 'off' && $type =='mysql'){

            if((time()-$RedisData['lastTime'])>$RedisData['rememberTime']){
                //执行redis 抛弃tken  （由于在每次请求api时都需要进行登录判断）在这里判断如果超过了快捷登录的设置设计就调用退出登录方法
                $redis->logout($signature);
                //执行
                $LoginM = new \app\login\model\Login;
                if($LoginM->logout($signature,['id'=>$RedisData['sub']],'操作超时')['code'] != 0)
                {
                    return ['error'=>1,'msg'=>'意外错误','data'=>$RedisData['sub']];
                }
                return ['error'=>1,'msg'=>'操作超时','data'=>$RedisData['sub']];
            }
            //更新本次鉴权时间
            //由于快捷登录的判断标准是用户如果在单位时间内没有操作就判断为 操作超时 所以在每次完成快捷登录判断完成后写入本次判断时间
            $redis->set_lastTime($signature);

        }else if($RedisData['lastTime'] == 'off' && $type =='mysql'){


            //判断是否登录超时 这个是用户自己设置的
            if($RedisData['iat']+$RedisData['rememberTime']+2 < time()){
                return ['error'=>1,'msg'=>'登录超时','data'=>$RedisData['sub']];
            }
        }

        //设备识别码
        //进行简单的安全防御
        $AGENT = json_decode(base64_decode($playload),true)['AGENT'];
        if($AGENT != md5($_SERVER['HTTP_USER_AGENT'])){
            return ['error'=>3,'msg'=>'非法切换访问设备','data'=>'AGENT验证失败','uid'=>$RedisData['sub']];
        }

        return ['error'=>0,'msg'=>'验证通过','data'=>$RedisData['sub']];
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

