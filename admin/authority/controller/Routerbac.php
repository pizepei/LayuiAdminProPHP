<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-16 15:05:28
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-17 22:19:03
 */
namespace app\authority\controller;

use authority\AdminRouteAccess as Access;
/**
 * 系统后台管理员
 */
class Routerbac extends \VerifiController\AdminLoginVerifi
{
    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {
        return[
            'setList'=>'获取权限列表',
            'updateList'=>'更新功能权限',
        ];
    }
    /**
     * [getList 获取权限组列表]
     * @Effect
     * @return [type] [description]
     */
    public function setList()
    {
        (int)$Uid = input('name');
        
        Result(Access::getAccessList($Uid,$this->getRbac()));
    }

    /**
     * [getList 更新功能权限]
     * @Effect
     * @return [type] [description]
     */
    public function updateList()
    {
        (int)$Uid = input('uid');
        $Type = input('type');
        $Status = input('status');
        $Aid = input('aid');
        if(empty($Uid)){ Result(['code'=>1,'msg'=>'请选择用户组']);}

        // $arr = ['authority/Role/updataStatus','menu/Menu/deleteMenu','authority/Mainuser/updataStatus','authority/Routerbac/updateList'];

        // if(in_array($Aid,$arr)){
        //         Result(['code'=>1,'msg'=>'测试账号断开这个修改权限']);
        // }

        Result(Access::updateList($Uid,$Type,$Aid,$Status));
    }
}