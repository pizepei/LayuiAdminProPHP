<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-11 15:07:14
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:27:59
 */
namespace common\menu;
use common\Model;
use think\Cache;
class AdminMenuAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_menu_access';
    /**
     * [getAccess 获取用户组对应权限]
     * @Effect
     * @return [type] [description]
     */
    public static function  getAccess()
    {
        $arr = array();
        $Menu = new static();
        //获取权限数据
        // $Access = $Menu->where(['status'=>0])->cache('MenuAccess_getAccess_Access',0,'nameMenu')->select();
        $Access = $Menu->where(['status'=>0])->select();

        $Access = $Access->toArray();
        //获取
        foreach ($Access as $key => $value) {
            $arr[] = $value['menu_id'];
        }
        return  $arr;
    }

    public static function  setSatus($Uid,$Type,$Aid,$Status)
    {
        $arr = array();
        $Menu = new static();
        // 首先通过uid（用户组）权限id 查找是否有这个权限记录
        $Menu->where([''])->find();


        //如果有  直接判断更改 这个权限记录的  状态




        // 如果没有创建
        $E = self::where('uid', $uid)
        ->update(['enable'=>$type]);
        return $E;











        //获取权限数据
        // $Access = $Menu->where(['status'=>0])->cache('MenuAccess_getAccess_Access',0,'nameMenu')->select();
        $Access = $Menu->where(['status'=>0])->select();

        $Access = $Access->toArray();
        //获取
        foreach ($Access as $key => $value) {
            $arr[] = $value['menu_id'];
        }
        return  $arr;
    }


}
