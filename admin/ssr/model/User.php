<?php
/**
 * @Author: pizepei
 * @Date:   2018-03-06 21:56:17
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-17 14:20:53
 */
namespace app\ssr\model;
use think\Model;

class User extends Model {

    protected $resultSetType = 'collection';

    const flow_kb = 1024;  //kb
    const flow_mb = 1048576;  //mb
    const flow_gb = 1073741824;  //gb

    // 设置当前模型的数据库连接
    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '',
        // 数据库名
        'database'    => '',
        // 数据库用户名
        'username'    => '',
        // 数据库密码
        'password'    => '',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
        // 数据库调试模式
        'debug'       => false,
    ];

    /**
     * [getEnableAttr 账号状态获取器]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    // public function getEnableAttr($value)
    // {
    //     $status = [0=>'禁用',1=>'正常'];
    //     return $status[$value];
    // }

    /**
     * [getTAttr 最后使用的时间]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getTAttr($value)
    {
        return Mdate($value);
    }
    /**
     * [getUAttr 已上传流量]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getUAttr($value)
    {
        return floor($value/self::flow_gb);

    }
    /**
     * [getDAttr 已下载流量]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getDAttr($value)
    {
        return floor($value/self::flow_gb);
        // return (float)number_format($value/self::flow_gb, 2);
    }
    /**
     * [getTransferEnableAttr 可用流量（总量）]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getTransferEnableAttr($value)
    {
        return floor($value/self::flow_gb);
    }

    /**
     * [getUserData 获取ssr用户数据]
     * @return [type] [description]
     */
    public static function getUserData($page,$limit,$whe='')
    {
        $where='';
        $where=array();
        if(!empty($whe)){

            $where['email'] = $whe;

        }
        //实例化对象
        $UserData = new self;
        $Data =$UserData->where($where)->page("{$page},{$limit}")->select()->toArray();
        return ['count'=>$UserData->where($where)->count(),'data'=>$Data];
    }
    /**
     * [updataEnable 更新账户状态]
     * @Effect
     * @param  [int] $id   [uid]
     * @param  [int] $type [状态]
     * @return [type]       [description]
     */
    public static  function updataEnable($uid,$type)
    {   (int)$uid;
        (int)$type;
        $E = self::where('uid', $uid)
        ->update(['enable'=>$type]);
        return $E;
    }

    /**
     * [updataReservation 更新账户有效期]
     * @Effect
     * @param  [type] $id   [uid]
     * @param  [type] $Data [数据]
     * @return [type]       [description]
     */
    public static function updataReservation($uid,$Data)
    {
        (int)$uid;
        //验证时间格式
        $TimeData = isTimeFormat($Data);
        if(!$TimeData){
            return false;
        }
        $E = self::where('uid', $uid)
        ->update(['reservation_indent_finish'=>$TimeData]);
        return $E;
    }

    /**
     * [updataTransferEnable 更新总流量]
     * @Effect
     * @param  [type] $uid  [description]
     * @param  [type] $Data [description]
     * @return [type]       [description]
     */
    public static function updataTransferEnable($uid,$Data)
    {
        (int)$uid;
        (int)$Data;
        $Data = self::flow_gb*$Data;
        $E = self::where('uid', $uid)
        ->update(['transfer_enable'=>$Data]);
        return $E;
    }
    /**
     * [setPlan 更新套餐]
     * @Effect
     * @param  [type] $uid  [description]
     * @param  [type] $Data [description]
     */
    public static function setPlan($uid,$Data)
    {
        (int)$uid;
        $Data =  strtoupper($Data);
        $E = self::where('uid', $uid)
        ->update(['plan'=>$Data]);
        return $E;
    }




}
