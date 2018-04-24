<?php
namespace common\menu;
use common\Model;
/**
 * 登录用户模型
 */
class AppMenu extends Model {

    protected $resultSetType = 'collection';
    protected $table = 'app_menu';


    /**
     * [getMenu 获取菜单]
     * @Effect
     * @return [type] [description]
     */
    public static function  getMenu()
    {

        $AppMenu = new static();
        //获取总数据
        $Menu = $AppMenu->where(['isdel'=>0,'status'=>0])->select();
        // 获取一级菜单并进行排序
        $fatherMenu = $AppMenu->where(['isdel'=>0,'status'=>0,'father_id'=>0])->order('sort desc')->select();
        $Menu =$Menu ->toArray();
        $fatherMenu =$fatherMenu ->toArray();

        $MenuData = array();
        foreach ($fatherMenu as $key => $value) {
                //以一级菜单为基本 继续子菜单数据获取
                $data = ['name'=>$value['name'],'title'=>$value['title'],'icon'=>$value['icon'],'list'=>self::plist($Menu,$value['id']),'sort'=>$value['sort']];
                $MenuData[]= $data;
        }
        return $MenuData;
    }



    /**
     * [setMenu 后台修改获取菜单]
     * @Effect
     * @return [type] [description]
     */
    public static function  setMenu()
    {

        $AppMenu = new static();
        //获取总数据
        $Menu = $AppMenu->select();
        // 获取一级菜单并进行排序
        $fatherMenu = $AppMenu->where(['father_id'=>0])->order('sort desc')->select();
        $Menu =$Menu ->toArray();
        $fatherMenu =$fatherMenu ->toArray();

        $MenuData = array();
        foreach ($fatherMenu as $key => $value) {
                //以一级菜单为基本 继续子菜单数据获取
                $data = ['name'=>$value['name'],'title'=>$value['title'],'icon'=>$value['icon'],'list'=>self::plist($Menu,$value['id']),'sort'=>$value['sort'],'id'=>$value['id']];
                $MenuData[]= $data;
        }
        //清除 缓存
        Cache::clear('AdminNameMenu');


        return $MenuData;
    } 


    
    /**
     * [plist 获取子菜单]
     * @Effect
     * @param  [type] $Menu [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public static function plist($Menu,$id){
        //获取下级k
        $seeklist = self::seeklist($Menu,$id);
        $MenuData = array();
        if($seeklist){
            foreach ($seeklist as $key => $value) {
                $plist = self::plist($Menu,$Menu[$value]['id']);
                if($plist){
                    $Data = ['name'=>$Menu[$value]['name'],'title'=>$Menu[$value]['title'],'list'=>$plist,'sort'=>$Menu[$value]['sort']];
                }else{
                    $Data = ['name'=>$Menu[$value]['name'],'title'=>$Menu[$value]['title'],'sort'=>$Menu[$value]['sort']];
                }
                $MenuData[] = $Data;
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




}
