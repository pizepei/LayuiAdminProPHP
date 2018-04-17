<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-16 16:27:35
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-16 16:28:46
 */
namespace authority;
use think\Model;
use think\Cache;
/**
 * 操作权限角色表与权限表的关联表
 */
class AdminRoleRouteAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role_route_access';


}
