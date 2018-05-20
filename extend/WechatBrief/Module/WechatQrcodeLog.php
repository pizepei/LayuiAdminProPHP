<?php
/**
 * @Author: pizepei
 * @Date:   2018-05-16 22:36:38
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-17 23:05:51
 */
namespace WechatBrief\Module;
use common\Model as M;
use think\Cache;
/**
 * 带参数的二维码 模型
 */
class WechatQrcodeLog extends M {
    protected $resultSetType = 'collection';
    protected $table = 'wechat_qrcode_log';
    /**
     * [addLog 添加日志]
     * @Effect
     * @param  [type] $uid       [用户id]
     * @param  [type] $ticketid  [二维码url参数]
     * @param  string $content   [自定义内容]
     * @param  [type] $openid    [openid]
     * @param  [type] $term_time [过期时间]
     * @param  [type] $type      [类型]
     */
    public static function addLog($ticketid,$url,$content='',$type,$term_time,$uid,$http_agent,$client_id,$remember)
    {

        if($term_time !=0){
            $term_time = $term_time+time();
        }

        $request = \think\Request::instance();
        $ip = $request->ip();
        $log           = new static();
        $log->uid   =$uid;//uid
        $log->ip   =$ip;//二维码参数
        $log->socketid = $client_id;//socketid
        $log->http_agent = $http_agent;//md5 设备信息
        $log->login_remember = $remember;//登录模式
        $log->ticketid   =$ticketid;//二维码参数
        $log->Type     = $type;//二维码类型0未知  1 绑定 2 登录 
        $log->content    = $content;//主题
        $log->term_time     = $term_time;//有效期0 永久  时间戳+$term_time
        $log->url    = $url;//url
        $log->create_time    = date('Y-m-d H:i:s');//创建时间
        $log->save();

    }
}