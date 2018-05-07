<?php
namespace common\menu;
use common\Model;

use think\Cache;
use common\authority\AdminMenuAccess;
/**
 * 登录用户模型
 */
class AdminMenu extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'admin_menu';

    /**
     * [getMenu 获取菜单]
     * @Effect
     * @return [type] [description]
     */
    public static function  getMenu()
    {

        //获取权限
        $Access = AdminMenuAccess::getAccess();

        $AppMenu = new static();
        // for ($i=0; $i <100 ; $i++) { 
        //         $Access[]=$i;
        //     # code...
        // }
        //获取总数据
        $Menu = $AppMenu->where(['isdel'=>0,'status'=>0])->cache('AdminMenu_getMenu_Menu',0,'AdminNameMenu')->select();
        // 获取一级菜单并进行排序
        $fatherMenu = $AppMenu->where(['father_id'=>0,'isdel'=>0,'status'=>0])->order('sort desc')->cache('AdminMenu_getMenu_fatherMenu',0,'AdminNameMenu')->select();
        $Menu =$Menu ->toArray();
        $fatherMenu =$fatherMenu ->toArray();
        $MenuData = array();
        foreach ($fatherMenu as $key => $value) {
                //以一级菜单为基本 继续子菜单数据获取
                if(in_array($value['id'],$Access)){
                    $data = ['name'=>$value['name'],'title'=>$value['title'],'icon'=>$value['icon'],'list'=>self::plist($Menu,$value['id'],$Access),'sort'=>$value['sort']];
                    $MenuData[]= $data;
                }
        }
        return $MenuData;
    }
    /**
     * [setMenu 后台修改获取菜单]
     * @Effect
     * @return [type] [description]
     */
    public static function  setMenu($guid)
    {
        //获取权限
        $Access = AdminMenuAccess::getAccess($guid);

        $AppMenu = new static();
        //获取总数据
        $Menu = $AppMenu->where(['isdel'=>0])->select();
        // 获取一级菜单并进行排序
        $fatherMenu = $AppMenu->where(['father_id'=>0,'isdel'=>0])->order('sort desc')->select();
        $Menu =$Menu ->toArray();
        $fatherMenu =$fatherMenu ->toArray();

        $MenuData = array();
        foreach ($fatherMenu as $key => $value) {
                //以一级菜单为基本 继续子菜单数据获取
                $data = ['name'=>$value['name'],'title'=>$value['title'],'icon'=>$value['icon'],'list'=>self::plist($Menu,$value['id'],$Access,true),'sort'=>$value['sort'],'id'=>$value['id'],'status'=>$value['status'],'access'=>in_array($value['id'],$Access)?0:1];

                $MenuData[]= $data;
        }
        return $MenuData;
    } 
    /**
     * [plist 获取子菜单]
     * @Effect
     * @param  [type] $Menu [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public static function plist($Menu,$id,$Access=array(),$type=false){
        //获取下级k
        $seeklist = self::seeklist($Menu,$id);
        $MenuData = array();
        if($seeklist){
            foreach ($seeklist as $key => $value) {
                //获取三级
                if(in_array($Menu[$value]['id'],$Access) || $type){

                    $plist = self::plist($Menu,$Menu[$value]['id'],$Access,$type);
                    if($plist){
                        //有
                        $Data = ['name'=>$Menu[$value]['name'],'title'=>$Menu[$value]['title'],'list'=>$plist,'sort'=>$Menu[$value]['sort'],'id'=>$Menu[$value]['id'],'status'=>$Menu[$value]['status'],'access'=>in_array($Menu[$value]['id'],$Access)?0:1];
                    }else{
                        //没有
                        $Data = ['name'=>$Menu[$value]['name'],'title'=>$Menu[$value]['title'],'sort'=>$Menu[$value]['sort'],'id'=>$Menu[$value]['id'],'status'=>$Menu[$value]['status'],'access'=>in_array($Menu[$value]['id'],$Access)?0:1];
                    }
                    $MenuData[] = $Data;
                }
            }
            //进行排序
            $sort = array_column($MenuData, 'sort');//获取对应的排序值 内置函数
            array_multisort($sort,SORT_DESC,$MenuData );//多维数组的排序
        }
        return $MenuData;
    }


    /**
     * [seeklist 获取当前菜单id下的只菜单相对$Menu的下标]
     * @Effect
     * @param  [arr] $Menu [arr总数据]
     * @param  [int] $id   [当前id]
     * @return [arr]       [id数据]
     */
    public static function  seeklist($Menu,$id)
    {
        $arr =array();
        foreach ($Menu as $key => $value) {
            if($value['father_id'] == $id){
                $arr[]=$key;
                $sort[]=$value['sort'];
            }
        }
        return $arr;
    }

    /**
     * [setFather 获取一级菜单并进行排序]
     * @Effect
     * @param  [type] $Menu [description]
     */
    public static function setFather($Menu)
    {
        $arr =array();
        foreach ($Menu as $key => $value) {
            if($value['father_id'] == 0){
                $arr[] =$Menu[$key];
                $sort[] = $value['sort'];
            }
        }
        //排序
        $len=count($arr);
        //该层循环控制 需要冒泡的轮数
        for($i=1;$i<$len;$i++)
        { 
            //该层循环用来控制每轮 冒出一个数 需要比较的次数
            for($k=0;$k<$len-$i;$k++)
            {
               if($arr[$k]<$arr[$k+1])
                {
                    $tmp=$arr[$k+1];
                    $arr[$k+1]=$arr[$k];
                    $arr[$k]=$tmp;
                }
            }
        }

    }

    /**
     * [addMenu 添加菜单]
     * @Effect
     */
    public static function addMenu($Data)
    {

        if(!empty($Data['id'])){
            // 添加子菜单
            $Data['father_id'] = $Data['id'];
            unset($Data['id']);
        }
        $Data['create_time'] = Mdate();
        //添加一级
        $Menu = new static($Data);
        // 过滤post数组中的非数据表字段数据
        // dump($Menu->allowField(true)->save());
        if($Menu->allowField(true)->save()){
            self::saveCache();
            //清除 缓存
            Cache::clear('AdminNameMenu');
            return true;
        }

        return false;

    }

    /**
     * [updataMenu 更新菜单]
     * @Effect
     * @param  [type] $Data [description]
     * @return [type]       [description]
     */
    public static function updataMenu($Data)
    {

        $Id = $Data['id'];
        unset($Data['id']);

        $Menu = new static();

        // 视图名称  显示标题   图标  排序  状态，0为正常，1为锁定
        if($Menu->allowField(['name','title','icon','sort','status'])->save($Data, ['id' =>$Id])){
            self::saveCache();

            return true;
        }

        return false;


    }
    /**
     * [deleteMenu 删除菜单]
     * @Effect
     * @param  [type] $Id [description]
     * @return [type]     [description]
     */
    public static function deleteMenu($Id)
    {

        $Data['isdel'] =1;
        $Menu = new static();

        $count = $Menu->where(['father_id'=>$Id,'isdel'=>0])->count();
        if($count){
            return ['code'=>0,'msg'=>'该菜单下还有子菜单无法删除'];
        }

        if($Menu->allowField(['isdel'])->save($Data, ['id' =>$Id])){
            self::saveCache();
            return ['code'=>0,'msg'=>'删除成功'];
        }
        return ['code'=>1,'msg'=>'删除失败'];
    }
    /**
     * [saveCache 更新缓存]
     * @Effect
     * @return [type] [description]
     */
    public static function saveCache()
    {
        Cache::rm('AdminMenu_getMenu_Menu'); 
        Cache::rm('AdminMenu_getMenu_fatherMenu'); 
    }

}
