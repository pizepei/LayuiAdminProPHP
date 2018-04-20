<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-04 16:24:10
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-20 17:11:03
 */
namespace app\authority\controller;
use app\login\model\MainUser   as User;

use authority\AdminRole;
use authority\AdminUserRole;

/**
 * 系统后台管理员
 */
class Mainuser extends \VerifiController\AdminLoginVerifi
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {
        return[
            'getList'=>'获取管理员列表',
            'updataStatus'=>'修改用户状态',
            'addUser'=>'添加管理员',
            'updataRole'=>'修改管理员用户组',
        ];
    }
    /**
     * [getList 获取管理员列表]
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
        return Result(User::getList($page,$limit,$whe));
    }
    /**
     * [updataStatus 修改用户状态]
     * @Effect
     * @return [type] [description]
     */
    public function updataStatus()
    {
        (int)$Id = input('id');
        $Type = input('type');
        $Type = $Type=='false'?1:0;
        return Result(User::updataStatus($Id,$Type));
    }
    /**
     * [addRole 添加管理员]
     * @Effect
     */
    public function addUser()
    {
        return Result(User::addRole(input()));
    }

    /**
     * [updataRole 修改管理员用户组]
     * @Effect
     * @return [type] [description]
     */
    public function updataRole()
    {

        (int)$Uid = input('uid');
        (int)$Rid = input('rid');
        if(!AdminRole::get($Rid)){
            Result(['code'=>1,'msg'=>'没有这个用户组']);
        }
        
        return Result(AdminUserRole::updataRole($Rid,$Uid));
    }

}
