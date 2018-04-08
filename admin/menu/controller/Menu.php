<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-29 17:23:04
 */
namespace app\menu\controller;
use menu\AdminMenu;
use menu\AppMenu;
class Menu extends \VerifiController\AdminLoginVerifi
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
        Result(AdminMenu::getMenu());


        // $this->success('','',AdminMenu::getMenu());
    }
    /**
     * [setList 管理员获取设置]
     */
    public function setList()
    {
        Result(AdminMenu::setMenu());
    }
    /**
     * [setList 用户获取设置]
     */
    public function setAppList()
    {
        Result(AppMenu::setMenu());
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

        Result(AdminMenu::addMenu($Data));

    }

    /**
     * [updataMenu 更新菜单]
     * @Effect
     * @return [type]       [description]
     */
    public function updataMenu()
    {

        $Data=[
          'id'=>(int)input('id'),
          'name'=>input('name'),
          'status'=>input('status')=='on'?1:0,
          'sort'=>(int)input('sort'),
          'icon'=>input('icon'),
          'title'=>input('title'),
        ];

        Result(AdminMenu::updataMenu($Data));

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
        
        Result(AdminMenu::deleteMenu($id));

        // if($E['code'] == 1){
        //     $this->success($E['msg']);
        // }else{
        //     $this->error($E['msg']);
        // }
    }

}

