<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 14:51:29
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:27:16
 */
namespace common\authority;
use common\Model;
use think\Cache;
class AdminRoleMenuAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role_menu_access';


}
