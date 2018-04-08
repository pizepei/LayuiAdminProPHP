<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-08 10:57:10
 */
namespace app\authority\controller;
use app\authority\model\Role as RoleName;
/**
 * 
 */
class Role extends \VerifiController\AdminLoginVerifi
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {
        return[
            'getList'=>'获取菜单列表',
        ];
    }
    /**
     * [getList 获取权限组列表]
     * @Effect
     * @return [type] [description]
     */
    public function getList()
    {
        (int)$page = input('page');
        (int)$limit = input('limit');
        if(empty(input('whe'))){
            $whe = '';
        }else{
            $whe = input('whe');
        }
        Result(RoleName::getList($page,$limit,$whe));
    }
    /**
     * [updataStatus 修改用户组状态]
     * @Effect
     * @return [type] [description]
     */
    public function updataStatus()
    {
        (int)$Id = input('id');
        $Type = input('type');
        $Type = $Type=='false'?1:0;
        Result(RoleName::updataStatus($Id,$Type));
    }

    /**
     * [addRole 添加权限组]
     * @Effect
     */
    public function addRole()
    {
        Result(RoleName::addRole(input()));
    }


}

