<?php
namespace common\authority;
use think\Model;
use think\Request;
/**
 * 用户组
 */
class AdminRole extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role';

    /**
     * [getList 获取用户组列表]
     * @Effect
     * @param  [type] $page  [description]
     * @param  [type] $limit [description]
     * @param  [type] $whe   [description]
     * @return [type]        [description]
     */
    public static function  getList($page,$limit,$whe)
    {
        $where = '';
        $where=array();
        if(!empty($whe)){
            $where['email'] = $whe;
        }
        //实例化对象
        $New = new static;
        $Data =$New->where($where)->page("{$page},{$limit}")->select()->toArray();
        return ['count'=>$New->where($where)->count(),'data'=>$Data];
    }
    /**
     * [updataStatus 修改状态]
     * @Effect
     * @param  [type] $Id   [description]
     * @param  [type] $Type [description]
     * @return [type]       [description]
     */
    public static function updataStatus($Id,$Type)
    {
        (int)$Id;
        (int)$Type;
        $E = static::where('id', $Id)
        ->update(['status'=>$Type,'update_time'=>Mdate()]);
        return $E;
    }
    /**
     * [addRole 添加]
     * @Effect
     * @param  [type] $Data [description]
     */
    public static function addRole($Data)
    {
        $Data['update_time'] = Mdate();
        $Data['create_time'] = Mdate();
        if(static::get(['name'=>$Data['name']])){
            return ['code'=>1,'msg'=>'重复的权限组'];
        }
        //添加一级
        $Menu = new static($Data);
        // 过滤post数组中的非数据表字段数据
        // dump($Menu->allowField(true)->save());
        if($Menu->allowField(true)->save()){
            return true;
        }
        return false;

    }
    /**
     * [LoginMainToken 获取菜单权限与 用户组 关系]
     * @Effect
     */
    public function AdminRoleMenuAccess()
    {
        return $this->hasMany('AdminRoleMenuAccess','role_id','id');
    }

    /**
     * [LoginMainToken 获取功能权限与 用户组 关系]
     * @Effect
     */
    public function AdminRoleRouteAccess()
    {
        return $this->hasMany('AdminRoleRouteAccess','role_id','id');
    }


}
