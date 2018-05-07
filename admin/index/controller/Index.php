<?php
namespace app\index\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;
use common\Redis\RedisModel;
use app\index\model\Login;
use SendMail\Mail;
use common\Safety\Safetylogin;
use think\Loader;
use GatewayClient\Gateway;

// class Index extends \VerifiController\AdminLoginVerifi
/**
 * 首页
 */
class Index extends Controller
{

    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {

        return[
        'index'=>'首页显示',
        'view'=>'测试二维码',
        ];

    }
    
    public function index()
    {
        //生成密码
        // $password = 'p123456';
        // $Safetylogin = new Safetylogin('admin');
        // $Safety = $Safetylogin->addPassword($password);

        // dump($Safety);
        // Loader::import('AlSms.api_demo.SmsDemo');

        return $this->fetch();

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


    public function cscs(){




        // echo $_SERVER['HTTP_USER_AGENT'];
        dump(\custom\TerminalInfo::getArowserPro('arr'));
        // dump($this->UserData);

    }

    /**
     * [im description]
     * @Effect
     * @return [type] [description]
     */
    public function im1()
    {
        echo $client_id = input('client_id');
        // $cccccc
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';

        // 假设用户已经登录，用户uid和群组id在session中
        $uid      = 1;
        $group_id = 11;
        // // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        // // 加入某个群组（可调用多次加入多个群组）
        Gateway::joinGroup($client_id, $group_id);

    }
    public function im2()
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
        $uid      = 1;
        $group_id = 11;
        // 向任意uid的网站页面发送数据
        Gateway::sendToUid($uid, json_encode(['type'=>'im2']));
        // 向任意群组的网站页面发送数据
        Gateway::sendToGroup($group_id, json_encode(['type'=>'im3g']));

    }
}
