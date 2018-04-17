<?php

namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
use \authority\AdminRole as Role; 
use \authority\AdminUserRole as UserRole; 

/**
 * 登录用户模型
 */
class MainUser extends Model {

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
    public function getUserGroupAttr($value,$data)
    {
        //获取用户组
        $UserRole = UserRole::get(['uid'=>$data['id']]);
        $UserRole = $UserRole->AdminRole->toArray();
        return ['Group'=>$UserRole[0]['name'],'Role'=>$UserRole[0]['id']];
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
            ->field('id,pwd_salt,pwd_hash,login_error_count,combo,grade,user_group,login_error_count_time')
            ->find();


        return $Data;
    }
    /**
     * [getList 获取用户组列表]
     * @Effect
     * @param  [type] $page  [description]
     * @param  [type] $limit [description]
     * @param  [type] $whe   [description]
     * @return [type]        [description]
     */
    public static function  getList($page,$limit,$whe)
    {
        $where = '';
        $where=array();
        if(!empty($whe)){
            $where['email'] = $whe;
        }
        //实例化对象
        $New = new static;
        $Data =$New->where($where)->page("{$page},{$limit}")->select()->toArray();
        return ['count'=>$New->where($where)->count(),'data'=>$Data];

    }

    /**
     * [updataStatus 修改状态]
     * @Effect
     * @param  [type] $Id   [description]
     * @param  [type] $Type [description]
     * @return [type]       [description]
     */
    public static function updataStatus($Id,$Type)
    {
        (int)$Id;
        (int)$Type;
        $E = static::where('id', $Id)
        ->update(['status'=>$Type]);
        return $E;
    }
    /**
     * [updataUserData 更新用户数据数据]
     * @Effect
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function updataUserData($name)
    {
        $Data = self::loginAction($name);
        $RedisLogin = new \redis\RedisLogin(config('admin_login_redis'));
        $RedisLogin->set_user_data($Data->id,$Data);
    }
    /**
     * [getUserData 获取用户数据数据]
     * @Effect
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function setUserData($id,$type=false)
    {
        $Data = self::get($id);
        $Data = $Data->hidden(['pwd_hash','pwd_salt'])->toArray();
        if($type){
            $RedisLogin = new \redis\RedisLogin(config('admin_login_redis'));
            $RedisLogin->set_user_data($Data['id'],$Data);
        }else{
            return $Data;
        }
    }
    /**
     * [LoginMainToken 获取登录控制记录]
     * @Effect
     */
    public function LoginMainToken()
    {
        return $this->hasMany('LoginMainToken','uid','id');
    }
    /**
     * [AdminLoginMainConfig 获取登录控制配置]
     * @Effect
     */
    public function LoginMainConfig()
    {
        return $this->hasOne('LoginMainConfig','uid','id');
    }









}
