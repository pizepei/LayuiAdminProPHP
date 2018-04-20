<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-08 17:31:19
 * @Last Modified by:   pizepei
 */
namespace app\ssr\controller;
use think\Controller;
use app\ssr\model\User;
use heillog\SsrUserLog as Log;
use app\ssr\model\Indent;
/**
 *  用户管理[SSR]
 */
class Suser extends \VerifiController\AdminLoginVerifi
{

    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    public static function title()
    {
        return[
            'getList'=>'获取SSR用户列表',
            'updataEnable'=>'更新SSR账户状态',
            'updataReservation'=>'更新SSR账户有效期',
            'updataTransferEnable'=>'更新总流量',
            'getUserData'=>'获取一个用户的详情',
            'getIndent'=>'获取用户订单数据',
            'setPlan'=>'更新套餐',
        ];
    }

    /**
     * [getList 获取用户列表]
     * @Effect
     * @return [type] [description]
     */
    public function getList()
    {
        (int)$page = input('page');
        (int)$limit = input('limit');
        if(empty(input('whe'))){
            $whe = '';
        }else{
            $whe = input('whe');
        }
        return Result(User::getUserData($page,$limit,$whe));

    }
    /**
     * [updataEnable 更新账户状态]
     * @Effect
     * @return [type] [description]
     */
    public function updataEnable()
    {
        (int)$uid = input('uid');
        $type = input('type');
        if($type =='true'){
            $type = 1;
        }else{
            $type = 0;
        }

        if(User::updataEnable($uid,$type) >0){
            //日志
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新账户状态:'.$type],4,0);
            return Result(true);

        }else{
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新账户状态:'.$type],4,1);
            return Result(false);
        }

    }

    /**
     * [updataReservation 更新账户有效期]
     * @Effect
     * @return [type] [description]
     */
    public function updataReservation()
    {
        (int)$uid = input('uid');
        $data = input('data');
        if(!isTimeFormat($data)){
            $this->error('时间格式必须是YY-mm-dd');
        }
        $E = User::updataReservation($uid,$data);
        if($E >0){
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新账户有效期:'.$data],2,0);
            return Result(true,'更改成功：'.$data);

        }else if($E == 0){
            return Result(false,'更改失败:重复更新');

        }else{
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新账户有效期:'.$data],2,1);
            return Result(false,'更改失败');
        }

    }

    /**
     * [updataTransferEnable 更新总流量]
     * @Effect
     * @return [type] [description]
     */
    public function updataTransferEnable()
    {
        (int)$uid = input('uid');
        (int)$data = input('data');
        $E = User::updataTransferEnable($uid,$data);
        if($E >0){
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新总流量:'.$data],2,0);
            return Result(true,'更改成功：'.$data);

        }else if($E == 0){
            return Result(false,'更改失败:重复更新');

        }else{

            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新总流量:'.$data],2,1);
            return Result(false,'更改失败');

        }
    }

    /**
     * [getUserData 获取一个用户的详情]
     * @Effect
     * @return [type] [description]
     */
    public function getUserData()
    {
        (int)$uid = input('uid');
        $E = User::get($uid)->toArray();
        if($E){
            return Result($E);
        }else{
            return Result(false,'获取失败');
        }
    }


    /**
     * [getIndent 获取用户订单数据]
     * @Effect
     * @return [type] [description]
     */
    public function getIndent()
    {
        (int)$uid = input('uid');
        (int)$page = input('page');
        (int)$limit = input('limit');
        if(empty(input('whe'))){
            $whe = '';
        }else{
            $whe = input('whe');
        }
        $Data = Indent::getIndent($uid,$page,$limit,$whe);
        return Result($Data);
    }

    /**
     * [setPlan 更新套餐]
     * @Effect
     */
    public function setPlan()
    {
        (int)$uid = input('uid');
        $data = input('data');

        $E = User::setPlan($uid,$data);

        if($E >0){
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新套餐:'.$data],2,0);
            return Result(true,'更改成功：'.$data);

        }else if($E == 0){
            return Result(false,'更改失败:重复更新');
        }else{
            Log::addLog(['aid'=>$this->UserData['id'],'uid'=>$uid,'info'=>'更新套餐:'.$data],2,1);
            return Result(false,'更改失败');
        }

    }

}

