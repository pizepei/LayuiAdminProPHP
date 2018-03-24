<?php

namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
/**
 * 登录用户模型
 */
class AdminMainAdmin extends Model {

    protected $resultSetType = 'collection';
    const StatusPaid = 0;  
    /**
     * [getComboAttr 套餐获取器]
     * @Effect
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getComboAttr($value)
    {
        $status = [0=>'普通',1=>'包日',2=>'包月',3=>'包年',4=>'流量'];
        return $status[$value];
    }
    /**
     * [getGradeAttr 会员等级获取器]
     * @Effect
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getGradeAttr($value)
    {
        $status = [0=>'平淡',1=>'出众',2=>'优秀',3=>'超神'];
        return $status[$value];
    }
    //登陆方法
    /**
     * [loginAction 登录数据获取方法]
     * @Effect
     * @param  [type] $name     [description]
     * @param  [type] $Password [description]
     * @return [type]           [description]
     */
    public function  loginAction($name)
    {
        $where['login_name'] = $name;
        $where['status'] = 0;
        $where['isdel'] = 0;
        //获取数据
        $Data = $this->where($where)
            ->field('id,pwd_salt,pwd_hash,login_error_count,combo,grade,user_group,wc_openid,apy_openid,login_error_count_time')
            ->find();
        return $Data;
    }
    /**
     * [AdminLoginMainToken 获取登录控制记录]
     * @Effect
     */
    public function AdminLoginMainToken()
    {
        return $this->hasMany('AdminLoginMainToken','uid','id');
    }
    /**
     * [AdminLoginMainConfig 获取登录控制配置]
     * @Effect
     */
    public function AdminLoginMainConfig()
    {
        return $this->hasOne('AdminLoginMainConfig','uid','id');
    }

}
