<?php
/**
 * @Author: pizepei
 * @Date:   2018-05-17 22:41:25
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-17 23:36:51
 */
namespace WechatBrief\Module;
use common\Model as M;
use think\Cache;
/**
 * 带参数的二维码 模型
 */
class AdminWechatOpenid extends M {
    protected $resultSetType = 'collection';
    protected $table = 'admin_wechat_openid';
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
    public static function addUser($data,$uid)
    {
        //获取是否已经绑定
        $log     = new static();

        $userdata = $log->where('openid',$data['openid'])
            ->whereOr('uid',$uid)->select()->toArray();
            //file_put_contents('./id1c.txt',json_encode($userdata));
        //存在
        if(count($userdata)>0){
            file_put_contents('./id3c.txt',count($userdata));
            return false;
        }
        //http://thirdwx.qlogo.cn/

        //https://irdwx.qlogo.cn/mmopen/SKFYA8FKM7ULuMrBvbjIaILDt0oPVXYIhACPU9Ccj9uUpo7iciaHrSJHibj0w2sCRn3kNmnf7D818s3bhfC4GPmUVRR7D3ADhaS/132

        $data['headimgurl'] = 'https://thirdwx.qlogo.cn/'.ltrim($data['headimgurl'],"http://irdwx.qlogo.cn/");
        $data['uid'] = $uid;
        $data['create_time'] = date('Y-m-d H:i:s');

        if($log->data($data)->allowField(true)->save()){
         return true;
        }



    }
}