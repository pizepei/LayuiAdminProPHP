<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-19 11:45:29
 */
namespace app\menu\controller;
use menu\AdminMenu;
use menu\AppMenu;
class Menu extends \VerifiController\AdminLoginVerifi
{
    /**
     * [getList 获取菜单列表]
     * @Effect
     * @return [type] [description]
     */
    public function getList()
    {
        $this->success('','',AdminMenu::getMenu());
    }
    /**
     * [setList 管理员获取设置]
     */
    public function setList()
    {
        $this->success('','',AdminMenu::setMenu());
    }
    /**
     * [setList 用户获取设置]
     */
    public function setAppList()
    {
        $this->success('','',AppMenu::setMenu());
    }
}

