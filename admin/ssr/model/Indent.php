<?php
/**
 * @Author: pizepei
 * @Date:   2018-03-16 11:06:45
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:53:14
 */
namespace app\ssr\model;
use common\Model;

class Indent extends Model {

    protected $resultSetType = 'collection';

    // const flow_kb = 1024;  //kb
    // const flow_mb = 1048576;  //mb
    // const flow_gb = 1073741824;  //gb

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
     * [getTAttr 商品信息json]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getGoodsJsonAttr($value)
    {
        return json_decode($value,true);
    }
    /**
     * [getIndent 获取用户订单]
     * @Effect
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function getIndent($uid,$page,$limit,$whe='')
    {
        (int)$uid;
        $where=array();
        $where['user_id'] = $uid;
        if(!empty($whe)){
            $where['ordernumber'] = $whe;
        }
        //实例化对象
        $IndentData = new static();
        $Data =$IndentData->where($where)->order('id desc')->page("{$page},{$limit}")->select()->toArray();

        return ['count'=>$IndentData->where($where)->count(),'data'=>$Data];
    }
    public static function pudata()
    {

        $data = self::all()->toArray();

        foreach ($data as $key => $value) {
            
            $Self = self::get($value['id']);
                $Self->ordernumber     = '模拟_'.Mt_str(10);
                $Self->contact_name    = '联系方式_'.Mt_str(8);
                $Self->contact     = '内容';
                dump($Self->save());

        }



    }
}
