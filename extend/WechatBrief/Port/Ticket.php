<?php
/**
 * @Author: pizepei
 * @Date:   2017-06-03 14:39:36
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-17 23:06:21
 */
namespace WechatBrief\Port;
use WechatBrief\func;
use WechatBrief\Module\WechatQrcodeLog as log;
use WechatBrief\Port\AccessToken;
/**
 * 微信  带参数的二维码
 * <img alt="" src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQGm7zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyVlB2cFZkeGtibl8xVXhNVE5xMXoAAgQZqfdaAwQIBwAA" width="200" id="pay_img" style="overflow: hidden; display:;">
 */
class Ticket{
     private static $expire_seconds = '';//过期时间
     private static $scene_id = '';//参数
     private static $action_name = '';
     private static $access_token = '';
     private static $ticket ='';

    /**
     * [Ticket 获取]
     * @Effect
     */
    public static function get_ticket($scene_id,$type=0,$expire_seconds = 60,$uid=0,$http_agent,$client_id=0,$remember=0){
        $AccessToken  = new AccessToken();
        self::$access_token = $AccessToken->access_token();
        // 判断永久还是临时
        // 默认临时
        if($expire_seconds){
            //临时
            $qrcode = '{"expire_seconds": '.$expire_seconds.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}'; 
        }else{
            //永久
            $qrcode = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        }
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".self::$access_token;
        $result = func::http_request($url,$qrcode);
        $jsoninfo = json_decode($result, true);
        if(empty($jsoninfo['errcode'])){

            self::$ticket = $jsoninfo["ticket"];
            log::addLog(static::$ticket,$jsoninfo["url"],$scene_id,$type,$expire_seconds,$uid,$http_agent,$client_id,$remember);
            return $jsoninfo;
        }
        return false;
        // ["ticket"] => string(96) "gQH17jwAAAAAAAAAAS5odHRwOi8vd2V"
        // ["expire_seconds"] => int(1800)
        // ["url"] => string(45) "http://weixin.qq.com/q/02uyTuUPxkbn_1R9mYxq15"
     }

 }
