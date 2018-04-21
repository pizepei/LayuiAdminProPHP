<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 15:23:17
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:15:50
 */
namespace common\authority;
use think\Model;
use think\Cache;
class AdminRoleAccess extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_role_access';
}
