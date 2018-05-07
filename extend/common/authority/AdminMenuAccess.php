<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 15:28:02
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-07 08:42:10
 */
namespace common\authority;
use common\Model;
use think\Cache;
use common\authority\AdminRole;
use common\authority\AdminRoleMenuAccess as RoleMenuAccess;

/**
 * 权限
 */
class AdminMenuAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_menu_access';
    /**
     * [getAccess 获取用户组对应权限]
     * @Effect
     * @return [type] [description]
     */
    public static function  getAccess($gid = 1)
    {
                //清除 权限缓存
        // Cache::clear('AdminAccess');
        $arr = array();
        $RoleArr = array();

        $Menu = new static();
        //获取当前用户组的所有权限id
        $AdminRole = AdminRole::where('id',$gid)->cache('authority_AdminMenuAccess_getAccess_AdminRole',7200,'AdminAccess')->find();

        $AdminRoleMenuAccess = $AdminRole->AdminRoleMenuAccess;

        foreach ($AdminRoleMenuAccess as $key => $value) {
            if($value->status==0){
                $RoleArr[] = $value->access_id;
            }
        }

        //获取权限数据
        $Access = $Menu->cache('authority_AdminMenuAccess_getAccess_Access',7200,'AdminAccess')->where(['status'=>0])->where('id','in',$RoleArr)->select();

        //获取
        foreach ($Access as $key => $value) {
            $arr[] = $value->menu_id;
        }
        // dump($arr);
        return  $arr;
    }
    /**
     * [updateList 更新系统后台管理员菜单权限]
     * @Effect
     * @param  [type] $Uid    [description]
     * @param  [type] $Type   [description]
     * @param  [type] $Aid    [description]
     * @param  [type] $Status [description]
     * @return [type]         [description]
     */
    public static function  updateList($Uid,$Type,$Aid,$Status)
    {
        if($Status=='true'){
            $Status = 0;
        }else{
            $Status = 1;
        }
        //查询是否有权限表记录
        //  由于每一个菜单的id 在权限表中只存在一个
        $MenuAccess = new static();
        $Access = $MenuAccess::get(['menu_id'=>$Aid]);
        //开启事务
        $MenuAccess->startTrans();

        try{
            $E = false;
            //判断是增加还是修改
            if(!$Access){
                //增加
                //权限
                $MenuAccess->title = '';//权限标题
                $MenuAccess->menu_id =$Aid;//菜单id
                $MenuAccess->status = $Status;//该记录是否有效0：有效、1：无效
                $MenuAccess->create_time = Mdate();//
                $MenuAccess->update_time = Mdate();//
                $MenuAccess->save();

                // 获取自增ID
                if($MenuAccess->id){
                    //关联
                    $RoleMenuAccess = new RoleMenuAccess;
                    $RoleMenuAccess->role_id = $Uid;//角色id
                    $RoleMenuAccess->status = $Status;//状态0：有效、1：无效
                    $RoleMenuAccess->access_id = $MenuAccess->id;//权限id
                    $RoleMenuAccess->create_time = Mdate();//创建时间
                    $RoleMenuAccess->update_time = Mdate();//更新时间

                    if(!$RoleMenuAccess->save()){
                        $E = false;
                        $MenuAccess->rollback();
                    }
                    $E = true;
                    $MenuAccess->commit();
                };
            }else{
                //已经有 权限记录  
                $RoleMenuAccess = RoleMenuAccess::get(['access_id'=>$Access->id,'role_id'=>$Uid]);

                if($RoleMenuAccess){
                    //当前用户组 有关联记录
                    $RoleMenuAccess->status = $Status;//状态0：有效、1：无效
                    $RoleMenuAccess->update_time = Mdate();//更新时间
                    if(!$RoleMenuAccess->save()){
                        $E = false;
                        $MenuAccess->rollback();
                    }
                    $E = true;
                    $MenuAccess->commit();
                }else{
                    //没有关联  创建
                    $RoleMenuAccess = new RoleMenuAccess;
                    $RoleMenuAccess->role_id = $Uid;//角色id
                    $RoleMenuAccess->status = $Status;//状态0：有效、1：无效
                    $RoleMenuAccess->access_id = $Access->id;//权限id
                    $RoleMenuAccess->create_time = Mdate();//创建时间
                    $RoleMenuAccess->update_time = Mdate();//更新时间
                    if(!$RoleMenuAccess->save()){
                        $E = false;
                        $MenuAccess->rollback();
                    }
                    $E = true;
                    $MenuAccess->commit();
                }

            }

           } catch (\Exception $e) {
            $MenuAccess->rollback();
            throw $e;
        }

        //清除 权限缓存
        Cache::clear('AdminAccess');
        return $E;


    }


}
