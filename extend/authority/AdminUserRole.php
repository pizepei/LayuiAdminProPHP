<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-13 13:54:21
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-13 14:30:39
 */
namespace authority;
use think\Model;
use think\Cache;
use think\AdminRole;
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

}
