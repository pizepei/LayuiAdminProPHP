<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-12 11:40:06
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 17:59:49
 */
namespace app\authority\controller;
use common\authority\AdminMenuAccess as Access;
use common\menu\AdminMenu;
use common\menu\AppMenu;


/**
 * 系统用户组管理
 */
class Menuaccess extends \common\VerifiController\AdminLoginVerifi
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
       if(empty($Uid)){ Result(['code'=>1,'msg'=>'请选择用户组']);}


        $Type = input('type');

        $Status = input('status');
        $Aid = input('aid');

       $arr=[67,92,94,95,68,69];

       if(in_array($Aid,$arr)){
            return Result(['code'=>1,'msg'=>'测试环境禁止这个操作']);
       }

       return  Result(Access::updateList($Uid,$Type,$Aid,$Status));
    }
    /**
     * [setList 管理员获取设置]
     */
    public function setList()
    {
        
        (int)$Uid = input('name');
        if(empty($Uid)){ Result(['code'=>1,'msg'=>'请选择用户组']);}
        
       return  Result(AdminMenu::setMenu($Uid));
    }


}