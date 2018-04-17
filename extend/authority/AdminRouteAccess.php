<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 15:23:17
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-17 13:57:30
 */
namespace authority;
use think\Model;
use think\Cache;
use authority\AdminRole;
use authority\AdminRoleRouteAccess as RoleRouteAccess;
/**
 * 功能权限
 */
class AdminRouteAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_route_access';

    /**
     * [Modulename 模块名称]
     * @Effect
     * @param  [type] $k [description]
     */
    public static function Modulename($k)
    {
        $arr = [
            'authority' => '权限系统',
            'index' => '首页入口',
            'login' => '登录验证',
            'menu' => '菜单系统',
            'ssr'=>'SSR系统',
            'user' => '用户信息',
        ];
        if (isset($arr[$k])) {
            return $arr[$k];
        }
        return $k;
    }
    /**
     * [getAccess 获取用户组对应权限]
     * @Effect
     * @return [type] [description]
     */
    public static function  getAccess($gid = 1)
    {
        $arr = array();
        $RoleArr = array();

        $Menu = new static();
        //获取当前用户组的所有权限id
        $AdminRole = AdminRole::get($gid);
        $AdminRoleRouteAccess = $AdminRole->AdminRoleRouteAccess;
        foreach ($AdminRoleRouteAccess as $key => $value) {
            if($value->status==0){
                $RoleArr[] = $value->access_id;
            }
        }

        //获取权限数据
        // $Access = $Menu->where(['status'=>0])->cache('MenuAccess_getAccess_Access',0,'nameMenu')->select();
        $Access = $Menu->where(['status'=>0])->where('id','in',$RoleArr)->select();
        // $Access = $Menu->where(['status'=>0])->select();
        //获取
        foreach ($Access as $key => $value) {
            $arr[] = $value->menu_id;
        }
        return  $arr;
    }
    /**
     * [getAccessList 获取权限状态列表]
     * @Effect
     * @param  integer $Gid   [用户组id]
     * @param  [type]  $Route [缓存的路由]
     * @return [type]         [description]
     */
    public static function getAccessList($Gid = 1,$Route)
    {
        $Access = self::getAccess($Gid);
        $RouteArr = array();
        foreach ($Route as $y => $e) {
            //替换控制器名称
            $RouteArr[self::Modulename($y)] = $e;
        }

        //模块
        foreach ($RouteArr as $key => $value) {
            
            // 控制器
            foreach ($value as $k => $v) {
                //方法
                foreach ($v['funs'] as $ke => $f) {
                    if( in_array(strtolower($v['herf'].'/'.$ke),$Access)){
                        $RouteArr[$key][$k]['status'][$ke] = 0;
                    }else{
                        $RouteArr[$key][$k]['status'][$ke] = 1;


                    }
                }

            }
        }
        return $RouteArr;


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
        $Aid = strtolower($Aid);
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
                    $RoleRouteAccess = new RoleRouteAccess;
                    $RoleRouteAccess->role_id = $Uid;//角色id
                    $RoleRouteAccess->status = $Status;//状态0：有效、1：无效
                    $RoleRouteAccess->access_id = $MenuAccess->id;//权限id
                    $RoleRouteAccess->create_time = Mdate();//创建时间
                    $RoleRouteAccess->update_time = Mdate();//更新时间
                    if(!$RoleRouteAccess->save()){
                        $E = false;
                        $MenuAccess->rollback();
                    }
                    $E = true;
                    $MenuAccess->commit();
                };
            }else{
                //已经有 权限记录  
                $RoleRouteAccess = RoleRouteAccess::get(['access_id'=>$Access->id,'role_id'=>$Uid]);

                if($RoleRouteAccess){
                    //当前用户组 有关联记录
                    $RoleRouteAccess->status = $Status;//状态0：有效、1：无效
                    $RoleRouteAccess->update_time = Mdate();//更新时间
                    if(!$RoleRouteAccess->save()){
                        $E = false;
                        $MenuAccess->rollback();
                    }
                    $E = true;
                    $MenuAccess->commit();
                }else{
                    //没有关联  创建
                    $RoleRouteAccess = new RoleRouteAccess;
                    $RoleRouteAccess->role_id = $Uid;//角色id
                    $RoleRouteAccess->status = $Status;//状态0：有效、1：无效
                    $RoleRouteAccess->access_id = $Access->id;//权限id
                    $RoleRouteAccess->create_time = Mdate();//创建时间
                    $RoleRouteAccess->update_time = Mdate();//更新时间
                    if(!$RoleRouteAccess->save()){
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
        return $E;


    }









}
