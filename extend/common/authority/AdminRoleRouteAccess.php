<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-16 16:27:35
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:16:09
 */
namespace common\authority;
use think\Model;
use think\Cache;
/**
 * 操作权限角色表与权限表的关联表
 */
class AdminRoleRouteAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role_route_access';


}
