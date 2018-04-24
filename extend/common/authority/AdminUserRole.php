<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-13 13:54:21
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:27:25
 */
namespace common\authority;
use common\Model;
use think\Cache;
use common\authority\AdminRole;
/**
 * 系统 管理员 与 角色 关系模型
 */
class AdminUserRole extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_user_role';
    /**
     * [AdminRole 获取 用户组]
     * @Effect
     */
    public function AdminRole()
    {
        return $this->hasMany('AdminRole','id','role_id');
    }
    /**
     * [updataRole 更新管理员用户组]
     * @Effect
     * @param  [type] $Rid [description]
     * @param  [type] $Uid [description]
     * @return [type]      [description]
     */
    public static function updataRole($Rid,$Uid){
        (int)$Rid;
        (int)$Uid;
        $E = self::where('uid', $Uid)
        ->update(['role_id'=>$Rid]);
        return $E;
    }

}
