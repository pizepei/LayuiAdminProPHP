<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-24 10:20:43
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 13:47:07
 */

namespace common;
use think\Model as M;
use think\Cache;
class Model extends M {
    protected $resultSetType = 'collection';

    /**
     * [getPageList 基础列表分页方法]
     * @Effect
     * @param  [type] $page  [当前页]
     * @param  [type] $limit [每页数量]
     * @param  [arr] $whe   [查询条件]
     * @param  [arr] $hidden   [限制输出 数据]
     * @return [type]        [description]
     */
    public static function  getPageList($page,$limit,$whe,$hidden=[])
    {
        $where = '';
        $where=array();
        if(!empty($whe)){
            $where = $whe;
        }
        //实例化对象
        $New = new static;
        $Data =$New->hidden(['create_time','update_time'])->where($where)->page("{$page},{$limit}")->select()->toArray();
        return ['count'=>$New->where($where)->count(),'data'=>$Data];
    }
}
