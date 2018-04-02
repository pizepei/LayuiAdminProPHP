<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-02 11:53:02
 */
namespace VerifiController;
use think\Controller;
use Safety\Safetylogin;
use redis\RedisLogin;
use app\login\model\MainUser;
use app\login\model\LoginLog as Log;

class AdminLoginVerifi extends Controller
{

    protected $UserData = '';
    protected $access_token = '';


    public function __construct()
    {

        // $this->atups();
        // exit;

        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('not support:redis');
        }
        $this->access_token = input('access_token');
        
        //JWT登录验证
        $this->VerifiDecodeJWT();
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
            echo json_encode(['code'=>'L1001']);
            exit;
        }
        $access_data = $Safetylogin->decodeJWT($this->access_token,'admin');

        if($access_data['error'] != 0){

            //错误日志
            if($access_data['error'] == 2){ \heillog\ErrorLog::addLog('管理后台登录验证',$access_data['data'],2); }//系统错误日志 2
            if($access_data['error'] == 1){ Log::addLog(['id'=>$access_data['data'],'info'=>$access_data['msg']],1); }//会员错误日志
            echo json_encode(['code'=>'L1001']);
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
                \heillog\ErrorLog::addLog('登录验证通过后','验证通过但是数据库没有用户数据',2);
            echo json_encode(['code'=>'L1001']);
            exit;
            }
            //复制个$this->UaerData
            $this->UserData = $UserIfonData;
            //缓存数据到redis
            $error = $redis->set_user_data($user_id,$UserIfonData);
            if(!$error){
                \heillog\ErrorLog::addLog('登录验证通过后','缓存用户数据到redis中',2);
                echo json_encode(['code'=>'L1001']);
                exit;
            }
        }else{
            $this->UserData = $UserData;
            $this->access_token = $Data[2];
        }
    }

    public function atups()
    {
        $bangs = CONF_PATH;


        // $rbac = Cache::get('rbac');

        // if ($rbac) {
        //     return $rbac;
        // }
        //打开总目录
        $bangs = CONF_PATH;
        //打开资源
        $dir = opendir($bangs);
        $dirs = []; // 根
        $muduel = []; //模块
        $controllers = []; //控制器
        while (($file = readdir($dir)) !== false) {
            $mfile = $bangs . $file;
            if (is_dir($mfile) && $file != '.' && $file != '..' && $file != 'common') {
                $dirs[] = $mfile . '/controller'; //拼接目录
            }
        }
        dump($dirs);
        //列出所有的目录数据
        foreach ($dirs as $v) {
            $dir = opendir($v);
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
        dump( $controllers);
        // $re = SysRule::updatafirm($controllers);

        // Cache::set('rbac', $controllers, 3600); //OK
        // $authbase = '/' . strtolower($this->request->module() . '/' . $this->request->controller() . "/" . $this->request->action());
        // if ($this->request->action() == 'clearcace') {
        //     $authbase = '/index/index/index';
        // }
        // return $this->success('更新系统RBAC数据完成^_^', $authbase); //请稍等
    }

}
