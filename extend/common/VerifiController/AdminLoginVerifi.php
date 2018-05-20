<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-07 10:32:51
 */
namespace common\VerifiController;
use think\Controller;
use common\Safety\Safetylogin;
use common\redis\RedisLogin;
use app\login\model\MainUser;
use app\login\model\LoginLog as Log;
use common\heillog\ErrorLog;
use think\Request;
use think\Cache;
use common\authority\AdminRouteAccess;
/**
 * Controller基类
 */
class AdminLoginVerifi extends Controller
{

    protected $UserData = '';
    protected $access_token = '';

    public function __construct()
    {
        // exit;
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('not support:redis');
        }

        $this->access_token = input('access_token');
        //JWT登录验证
        $this->VerifiDecodeJWT();
        //rbac权限验证
        $this->verifiRbac();

        parent::__construct();

    }
    /**
     * [VerifiDecodeJWT JWT登录验证]
     */
    public function VerifiDecodeJWT()
    {

        $Safetylogin = new Safetylogin('admin');

        $Data = explode('.',$this->access_token);

        if(count($Data) != 3){
           $this->error('非法请求');
            echo json_encode(['code'=>1001]);
            exit;
        }
        $access_data = $Safetylogin->decodeJWT($this->access_token,'admin');

        if($access_data['error'] != 0){

            //错误日志
            if($access_data['error'] == 2){ ErrorLog::addLog('管理后台登录验证',$access_data['data'],2); }//系统错误日志 2
            if($access_data['error'] == 1){ Log::addLog(['id'=>$access_data['data'],'info'=>$access_data['msg']],1); }//会员错误日志
            echo json_encode(['code'=>1001]);
            exit;
        }

        $user_id  = $access_data['data'];
        //获取redis中用户数据
        $redis = new RedisLogin();
        $UserData = $redis->get_user_data($user_id);

        if(!$UserData){
            //从数据库获取 用户数据
            $UserData = MainUser::get($user_id);
            $UserIfonData = $UserData->hidden(['pwd_salt','pwd_hash','phone'])->toArray();
            if(!$UserIfonData){
                ErrorLog::addLog('登录验证通过后','验证通过但是数据库没有用户数据',2);
            echo json_encode(['code'=>1001]);
            exit;
            }
            //复制个$this->UaerData
            $this->UserData = $UserIfonData;
            //缓存数据到redis
            $error = $redis->set_user_data($user_id,$UserIfonData);
            if(!$error){
                ErrorLog::addLog('登录验证通过后','缓存用户数据到redis中',2);
                echo json_encode(['code'=>1001]);
                exit;
            }
        }else{
            $this->UserData = $UserData;
            $this->access_token = $Data[2];
        }
    }

    //获取 权限信息
    public function getRbac()
    {
        $bangs = CONF_PATH;

        //判断是否有缓存
        $rbac = Cache::get('ADMIN_RBAC');
        if ($rbac) {
            return $rbac;
        }
        //打开总目录
        $bangs = CONF_PATH;
        //打开资源
        $dir = opendir($bangs);
        $dirs = []; // 根
        $muduel = []; //模块
        $controllers = []; //控制器
        while (($file = readdir($dir)) !== false) {
            if($file != 'route'){
                $mfile = $bangs . $file;
                if (is_dir($mfile) && $file != '.' && $file != '..' && $file != 'common') {
                    $dirs[] = $mfile . '/controller'; //拼接目录
                }

            }

        }

        //列出所有的目录数据
        foreach ($dirs as $v) {
            $dir = opendir($v);
            // dump($dir);
            while (($controller = readdir($dir)) !== false) {
                if (!is_dir($controller)) {
                    $str = 'app/' . str_replace($bangs, '', $v) . '/' . str_replace('.php', '', $controller);
                    $class = str_replace('/', '\\', $str);
                    //处理方法
                    if (method_exists($class, 'title')) {
                        $title = new \ReflectionClass($class);
                        $str = str_replace(['app\\', 'controller\\'], '', $class);
                        $herf = ['title' => str_replace(["*", "/", "\\", "\t", "\n", "\r", " "], '', $title->getDocComment()), 'herf' => str_replace('\\', '/', $str), 'funs' => $class::title()];
                        $funlist = explode("\\", $str);
                        $controllers[$funlist[0]][$funlist[1]] = $herf;
                    }
                } 
            }
        }

        Cache::set('ADMIN_RBAC',$controllers,3);
        return $controllers;
        // return Result(['code'=>0,'msg'=>'更新系统RBAC数据完成^_^']);
    }
    /**
     * [getRoute 获取路由]
     * @Effect
     * @return [type] [description]
     */
    public function getRoute()
    {

        $Request = Request::instance();

        return strtolower($Request->module().'/'.$Request->controller().'/'.$Request->action());

    }
    /**
     * [verifiRbac 路由权限]
     * @Effect
     * @return [type] [description]
     */
    public function verifiRbac()
    {
        //获取数据
        $this->getRbac();
        //获取当前路由
        $Route = $this->getRoute();

        // 获取权限
        $Access = AdminRouteAccess::getAccess($this->UserData['user_group']['Role']);

        if(!in_array($Route,$Access)){

            return Result(false,'您没有权限',301,true);
        }

    }

}
