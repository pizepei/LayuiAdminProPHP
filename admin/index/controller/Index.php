<?php
namespace app\index\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;
use Redis\RedisModel;
use app\index\model\Login;
use SendMail\Mail;
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

}
