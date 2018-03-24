<?php

namespace app\login\model;
use think\Model;
use \Safety\Safetylogin as Safety;
/**
 * 登录用户模型
 */
class AppMainUser extends Model {

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
        $status = [-1=>'无福消受',0=>'焉知祸福',1=>'入我福门',2=>'享我福精',3=>'一饱眼福',4=>'有福同享',5=>'五福临门',6=>'福甲天下',7=>'福光普照',8=>'金刚福禄娃',9=>'浮生若梦'];
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
        $checkmail="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";//定义正则表达式
        $checkphone="/^1[345789]\d{9}$/";//定义正则表达式 
        
        if(isset($name) && $name!=""){            //判断文本框中是否有值  
            if(preg_match($checkmail,$name)){      //用正则表达式函数进行判断  
                $where['email'] = $name;
                // echo "电子邮箱格式正确";  
            }else if(preg_match($checkphone,$name)){ 
                $where['phone'] = $name;
                // echo "电话号码格式正确";
            }else{
                return null;
                // echo "不是邮箱也不是手机号码";  
            } 
        }  

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
    public function AppLoginMainToken()
    {
        return $this->hasMany('AppLoginMainToken','uid','id');
    }
    /**
     * [AdminLoginMainConfig 获取登录控制配置]
     * @Effect
     */
    public function AppLoginMainConfig()
    {
        return $this->hasOne('AppLoginMainConfig','uid','id');
    }
    /**
     * [addUserData 添加用户]
     * @Effect
     * @param  [type] $Data [description]
     */
    public function addUserData($Data)
    {

        $this->name =   $Data['name']  ;//昵称字符串
        $this->login_name =  $Data['login_name']    ;//登录名称
        $this->phone =  $Data['phone']    ;//手机号码
        $this->email =  $Data['email']    ;//电子邮件
        $this->inviter_id =  $Data['inviter_id']    ;//邀请人
        $this->pwd_salt =  $Data['pwd_salt']    ;//密码盐
        $this->pwd_hash =  $Data['pwd_hash']    ;//密码盐+密码的hash
        $this->login_error_count =   0  ;//登录错误 5次  禁止2小时  发送邮件通知
        // $this->login_error_count_time =     ;//上次密码错误时间
        $this->combo =   $Data['combo']   ;//套餐
        $this->grade =    $Data['grade']  ;//会员等级 1、2、3、4、5、6、7、8、9
        $this->user_group =   $Data['user_group']   ;//用户组
        $this->balance =   $Data['balance']   ;//用户余额
        $this->integral =   $Data['integral']   ;//用户积分余额
        $this->wc_openid =    $Data['wc_openid']  ;//微信openid
        $this->apy_openid =    $Data['apy_openid']  ;//支付宝openid
        $this->autonym =   $Data['autonym']   ;//认证状态，0为正常，1为锁定
        $this->status =   $Data['status']   ;//状态，0为正常，1为锁定
        $this->isdel =   0   ;//软删除  0正常  1删除
        $this->register_way =    $Data['register_way']  ;//注册方式
        $this->create_time =   $Data['create_time']   ;//注册时间

        $this->save();

    }





}
