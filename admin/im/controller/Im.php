<?php
namespace app\im\controller;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;
use common\Redis\RedisModel;
use app\index\model\Login;
use SendMail\Mail;
use common\Safety\Safetylogin;
use think\Loader;
use GatewayClient\Gateway;
use common\Safety\DemandSensitiveWord as Sensitive;

/**
 * WEB IM 
 */
class Im extends \common\VerifiController\AdminLoginVerifi
{

    /**
     * [title 标题]
     * @Effect
     * @return [type] [description]
     */
    static function title()
    {

        return[
        'initializeIm'=>'初始化绑定用户',
        'view'=>'测试二维码',
        'GetUserInfo'=>'获取IM初始化数据',
        'sendMessage'=>'转发IM信息',
        'getMembers'=>'获取群成员列表',
        ];

    }
    /**
     * [initializeIm description]
     * @Effect
     * @return [type] [description]
     */
    public function initializeIm()
    {
        $client_id = input('client_id');
        // $cccccc
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
        // 假设用户已经登录，用户uid和群组id在session中
        $uid      = md5($this->access_token);
        $group_id = 11;
        // // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        // // 加入某个群组（可调用多次加入多个群组）
        Gateway::joinGroup($client_id, $group_id);
    }
    /**
     * [GetUserInfo description]
     * @Effect
     */
    public function GetUserInfo()
    {
        $namearr=['皮皮虾','小马哥','马哥','大马哥','小气鬼','绝地求生','刺激战场','买买提','阿里巴巴','泽','皮卡丘','丘比特'];


        //设置id
        $id = md5($this->access_token);

        //cache('IM_mine_members',null);
        //获取缓存
        $IM_mine_members = cache('IM_mine_members');
        if(!$IM_mine_members){
            $IM_mine_members = array();
        }
        $array_intersect_key = array_intersect_key($IM_mine_members,[$id=>null]);
        // dump($array_intersect_key);
        if($array_intersect_key){
            //已经到了过
            $mine = $array_intersect_key[$id];
        }else{
            $ipinfo = \common\custom\TerminalInfo::getArowserPro('arr');
            // dump($ipinfo);
            if(is_array($ipinfo['IpInfo'])){
                $province = $ipinfo['IpInfo']['province'];
                $city = $ipinfo['IpInfo']['city'];
                $username = $province.$city.$namearr[mt_rand(0,11)];
            }else{
                $username = '地头蛇'.$namearr[mt_rand(0,11)];
            }

            //第一次触发
            $mine = [
                'username'=>$username,
                'id'=>$id,
                'status'=>'status', //在线状态 online：在线、hide：隐身
                'sign'=>$ipinfo["Ipanel"].$ipinfo["Os"],
                'avatar'=>'../static/pic/'.mt_rand(0,14).'.jpg',
            ];

            $IM_mine_members[$id] = $mine;
            cache('IM_mine_members',$IM_mine_members);
        }


        //群数据
        $group = [
        'groupname'=>'临时交流',
        'id'=>11,
        'avatar'=>'../static/pic/5.jpg'
        ];

        foreach (cache('IM_mine_members') as $key => $value) {
            $friendData[] = $value;
        }

        //好友列表
        $friend = [
            'groupname'=>'好友列表',
            'id'=>1,
            'list'=>$friendData,

            
        ];
        return Result([
            'mine'=>$mine,
            'group'=>[$group],
            'friend'=>[$friend],
            ]);
    }

    /**
     * [sendMessage sendMessage]
     * @Effect
     * @return [type] [description]
     */
    public function sendMessage()
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';

        $mine = json_decode(input('mine'),true);
        $to = json_decode(input('to'),true);
        $mine['mine'] = $mine['mine']=='true'?false:true;
        //获取敏感数据
        $badword = Sensitive::getSelect();
        //过滤
        $badword1 = array_combine($badword,array_fill(0,count($badword),'**')); 
        array_shift($badword1);
        $mine['content'] = strtr($mine['content'], $badword1); 





        //"friend"   "group" 
        if($to['type'] == 'friend'){
            $data = json_encode(['emit'=>'sendMessage','data'=>[
                'username'=>$mine['username'],//消息来源用户名
                'avatar'=>$mine['avatar'],//消息来源用户头像
                'id'=>$mine['id'],//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
                'type'=>$to['type'],
                'content'=>$mine['content'],
                'mine'=>$mine['mine'],
                'fromid'=>$mine['id'],
                'timestamp'=>time(),
            ]]);

            // 向任意uid的网站页面发送数据
            Gateway::sendToUid($to['id'], $data);

        }else if($to['type'] == 'group'){
            $data = json_encode(['emit'=>'sendMessage','data'=>[
            'username'=>$mine['username'],//消息来源用户名
            'avatar'=>$mine['avatar'],//消息来源用户头像
            'id'=>$to['id'],//消息的来源ID（如果是私聊，则是用户id，如果是群聊，则是群组id）
            'type'=>$to['type'],//聊天窗口来源类型，从发送消息传递的to里面获取
            'content'=>$mine['content'],//消息内容
            'mine'=>$mine['mine'],//是否我发送的消息，如果为true，则会显示在右方
            'fromid'=>$mine['id'],//消息的发送者id（比如群组中的某个消息发送者），可用于自动解决浏览器多窗口时的一些问题
            'timestamp'=>time(),//服务端时间戳毫秒数。注意：如果你返回的是标准的 unix 时间戳，记得要 *1000
            ]]);

            // 向任意群组的网站页面发送数据
            Gateway::sendToGroup($to['id'], $data);

        }
        return Result(true);


    }
    /**
     * [getMembers 获取群成员列表]
     * @Effect
     * @return [type] [description]
     */
    public function getMembers()
    {
        foreach (cache('IM_mine_members') as $key => $value) {
            $Data[] = $value;
        }

        return Result(['list'=>$Data]);
    }



}
