<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 11:40:06
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-16 14:29:18
 */
namespace app\authority\controller;
use authority\AdminMenuAccess as Access;
use menu\AdminMenu;
use menu\AppMenu;
/**
 * 系统用户组管理
 */
class Menuaccess extends \VerifiController\AdminLoginVerifi
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {
        return[
            'updateList'=>'更新系统菜单权限',
            'setList'=>'设置用户组菜单权限',
        ];
    }
    /**
     * [getList 更新菜单权限]
     * @Effect
     * @return [type] [description]
     */
    public function updateList()
    {
        (int)$Uid = input('uid');
        $Type = input('type');
        $Status = input('status');
        (int)$Aid = input('aid');
        Result(Access::updateList($Uid,$Type,$Aid,$Status));
    }
    /**
     * [setList 管理员获取设置]
     */
    public function setList()
    {
        (int)$Uid = input('name');
        
        Result(AdminMenu::setMenu($Uid));
    }


}