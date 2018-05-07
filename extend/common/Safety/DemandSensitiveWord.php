<?php
/**
 * @Author: pizepei
 * @Date:   2018-05-06 22:14:49
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-06 22:46:45
 */
namespace common\Safety;
use common\Model;
use think\Cache;
/**
 * 敏感词库
 */
class DemandSensitiveWord extends Model {
    protected $resultSetType = 'collection';
    protected $table = 'demand_sensitive_word';


    /**
     * [getSelect 获取数据]
     * @Effect
     * @return [type] [description]
     */
    public static function getSelect()
    {
        Cache::clear('DemandSensitiveWord');
        $static = static::cache('DemandSensitiveWord_getSelect',0,'DemandSensitiveWord')->field('badword')->group('badword')->select()->toArray();
        return static::setarr($static);
    }
    /**
     * [setarr 处理数据]
     * @Effect
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function setarr($data)
    {

        foreach ($data as $key => $value) {
            $arr[] = $value['badword'];
        }
        return $arr;
    }

}