<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-06 15:59:00
 */
namespace app\menu\controller;
use common\menu\AdminMenu;
use common\menu\AppMenu;
/**
 * 系统菜单管理
 */
class Menu extends \common\VerifiController\AdminLoginVerifi
{

    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    public static function title()
    {
        return[
            'getList'=>'获取菜单列表',
            'setList'=>'设置系统后台菜单的列表',
            'setAppList'=>'用户获取设置',
            'addMenu'=>'添加系统后台菜单',
            'updataMenu'=>'更新系统后台菜单',
            'deleteMenu'=>'删除系统后台菜单',
        ];
    }

    /**
     * [getList 获取菜单列表]
     * @Effect
     * @return [type] [description]
     */
    public function getList()
    {
        
        // echo $this->UserData['login_name'];
        return Result(AdminMenu::getMenu());
        // $this->success('','',AdminMenu::getMenu());
    }
    /**
     * [setList 管理员获取设置]
     */
    public function setList()
    {
        return Result(AdminMenu::setMenu(1));
    }
    /**
     * [setList 用户获取设置]
     */
    public function setAppList()
    {
        return Result(AppMenu::setMenu());
    }

    /**
     * [addMenu 添加系统后台菜单]
     * @Effect
     */
    public function addMenu()
    {
        $Data=[
          'id'=>(int)input('id'),
          'name'=>input('name'),
          // 'status'=>(int)input('status'),
          'sort'=>(int)input('sort'),
          'icon'=>input('icon'),
          'title'=>input('title'),
        ];

        return Result(AdminMenu::addMenu($Data));

    }

    /**
     * [updataMenu 更新菜单]
     * @Effect
     * @return [type]       [description]
     */
    public function updataMenu()
    {

       $arr=[67,92,94,95,68,69];
       
       if(in_array((int)input('id'),$arr)){
            Result(['code'=>1,'msg'=>'测试环境禁止这个操作']);
       }
        $Data=[
          'id'=>(int)input('id'),
          'name'=>input('name'),
          'status'=>input('status')=='on'?1:0,
          'sort'=>(int)input('sort'),
          'icon'=>input('icon'),
          'title'=>input('title'),
        ];

        return Result(AdminMenu::updataMenu($Data));
    }

    /**
     * [delete 删除菜单]
     * @Effect
     * @return [type] [description]
     */
    public function deleteMenu()
    {

        $id = (int)input('id');

        if(!$id){
            Result('','请选择需要操作的菜单');
        }
        return Result(AdminMenu::deleteMenu($id));

    }

}

