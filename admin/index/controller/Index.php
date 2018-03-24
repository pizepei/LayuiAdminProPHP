<?php
namespace app\index\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;
use Redis\RedisModel;
use app\index\model\Login;
use SendMail\Mail;
// class Index extends \VerifiController\AdminLoginVerifi

class Index extends Controller
{
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


    /**
     * [menu 菜单]
     * @Effect
     * @return [type] [description]
     */
    public function menu()
    {
        //头模板
        $Menu = [  "code"=> 0,"msg"=>"获取数据成功"];

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
