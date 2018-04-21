<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 14:51:29
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:16:00
 */
namespace common\authority;
use think\Model;
use think\Cache;
class AdminRoleMenuAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role_menu_access';


}
