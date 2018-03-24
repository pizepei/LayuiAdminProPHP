<?php
namespace app\index\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;

use Redis\RedisModel;
use app\index\model\Login;

// class Index extends \VerifiController\AdminLoginVerifi

class Index extends Controller
{
    public function index()
    {

        return $this->fetch();
        exit;
        
        // $DeviceDetector = new Get();
        // dump($DeviceDetector->get($_SERVER['HTTP_USER_AGENT']));
    // $redis = new RedisModel(config('login_redis'));
    // // $redis->auth('11111');
    // $redis->set('key', 'hello wor速度速度ld1111');

    // dump($redis->get('key'));

        // session('name', 'thinkphp');

        // $llq =  new \custom\TerminalInfo() ;

        // dump($llq -> getArowserInfo());

        // $name = 'pizepei';
        // $Password = 'PzP386356321';
        // //登录
        // $Login = new Login;
        // $LoginData = $Login->loginAction($name,$Password);


        // dump($LoginData);

        // $Safety = new \Safety\Safetylogin('admin');
        // dump($Safety->decodeJWT($LoginData['data']));
        // dump($Safety->decodeJWT('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJvdXRzdGFuZGluZyIsInN1YiI6MSwiZXhwIjo3MjAwLCJpYXQiOjE1MTg0NDAwOTMsImRpc3AiOiJUOVlnVCM4WTg5YWZmNjkifQ==.af1c7768e8f34a565bd93547224238dd'));
        
        // dump($ccc->toArray());
        // dump(AdminLoginMainToken::get(['uid'=>1]));
        // $data = $ccc->AdminLoginMainToken;
        // dump($data->toArray());
        // $data = $ccc->AdminLoginMainConfig;
        // echo count($data->toArray());
        // foreach ($data as $key => $value) {

        //         echo $value->toArray();
        //         echo '<br>';
        //     # code...
        // }

        // dump($data->data);
        // $AdminMainAdmin->loginAction($name,$Password);

        // echo $cccc->combo; // 例如输出“正常”
        // exit();
        // $Safety = new Safety();
        // $Safety = passwordVerify($passWord,$salt,$hash);
        //实例化 密码安全类
        $Safety = new \Safety\Safetylogin();
        //创建密码
        $Password = $Safety->addPassword('p123456',14);
        dump($Password);
        // 查询用户
        // $Where = ['login_name'=>'pizepei','status'=>0,'isdel'=>0];
        // $user = db('admin_main_admin')->where(['id'=>1])->find();
        // //输入密码
        // $Password = 'PzP386356321';
        // //验证密码
        // if($Safety->passwordVerify($Password,$user['pwd_salt'],$user['pwd_hash'])){
        //     echo '密码正确';
        // }else{
        //     echo '密码错误';
        // }

        // return $this->fetch();
    }

    public function view()
    {

        $qrCode=new QrCode();
        $url = 'https://www.baidu.com';//加http://这样扫码可以直接跳转url
        $qrCode->setText($url)
            ->setSize(300)//大小
            ->setLabelFontPath(VENDOR_PATH.'endroid\qrcode\assets\24.ttf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('百万富翁推广码')
            ->setLabelFontSize(16);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }


    /**
     * [menu 菜单]
     * @Effect
     * @return [type] [description]
     */
    public function menu()
    {
        //头模板
        $Menu = [  "code"=> 0,"msg"=>"获取数据成功"];


        // 采用分类方法

        // id    name title   icon 



        //一级菜单模板
        $MenuDataTl = ["name"=>"SSR_manage","title"=>"SSR管理","icon"=> "layui-icon-component",'list'=>$data];//有二级菜单 的一级菜单

        $StairDataTl =  ["name"=>"get","title"=> "授权","icon"=>"layui-icon-auz","jump"=>"system/get"];//一级

        $StairDataTl = ["name"=>"security","title"=> "安全设置"];//二级无三级菜单


        $Menu['data'] = '';



















    }


    public function cscs(){
        // echo $_SERVER['HTTP_USER_AGENT'];
        dump(\custom\TerminalInfo::getArowserPro('arr'));
        // dump($this->UserData);

    }

}
