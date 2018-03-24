<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   anchen
 * @Last Modified time: 2018-03-17 15:58:52
 */
namespace app\menu\controller;
use menu\AppMenu;

class Menu extends \VerifiController\UserLoginVerifi
{
    /**
     * [getList 获取菜单列表]
     * @Effect
     * @return [type] [description]
     */
    public function getList()
    {
        AppMenu::getMenu();
            $this->success('','',AppMenu::getMenu());
    }

}

