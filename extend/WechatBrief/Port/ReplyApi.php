<?php
/**
* @Author: pizepei
* @Date:   2017-06-12 21:24:25
* @Last Modified by:   pizepei
* @Last Modified time: 2018-05-13 10:59:19
*/
/**
 * 信息处理类
 * 微信消息接口
 * 包含微信扫描二维码登录
 * 扫描二维码绑定账号
 * 包括微信关键字回复
 * 语音回复等
 */
namespace WechatBrief\Port;
use WechatBrief\func;
use WechatBrief\Module\WechatKeyword;
use WechatBrief\Module\WechatQrcodeLog as QrLog;
use WechatBrief\Module\AdminWechatOpenid as OpenidUser;
use WechatBrief\Port\AccessToken;
use GatewayClient\Gateway;
use app\login\model\Login as AddLogin;
class ReplyApi{

    private $postObj;//接受管理的xml对象

    //得到的是来源用户，是哪个用户跟我们发的消息$fromUsername$mediald
    private $fromUsername;

     //发给谁的。ToUserName   原始ID  开发者微信号
    private$toUsername;

    //被发送过来的内容
    private $keyword;

    //休息类型
    private $msgtype;

    //unix时间戳
    private $time;

    //视频消息缩略图的媒体id
    private $thumbmediaid = '';

    //媒体id
    private $mediald = '';

    //语音识别结果
    private $recognition = '';

    //图片网址
    private $picurl = '';

    //事件KEY值，与自定义菜单接口中KEY值对应 
    private $eventkey = '';

    //事件类型，subscribe(订阅)、unsubscribe(取消订阅)等
    private $event = '';

    //二维码的ticket，可用来换取二维码图片
    private $Ticket = '';

    //地理位置纬度 
    private $Latitude = '';

    //地理位置经度         
    private $Longitude = '';

    //地理位置精度
    private $Precision = '';
    

//----------------回复信息需要的 成员属性---------------------------------------

    //text 文字  image 图片  news 图文模板
    //信息  模板  array
    private $template_xml = array(
            'text'=>'<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>',

            'image'=>'<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Image>
        <MediaId><![CDATA[%s]]></MediaId>
        </Image>
        </xml>',

            'news'=>'<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <ArticleCount>%s</ArticleCount>
            <Articles>
                {$item}
            </Articles>
        </xml>'

        );

    //提取的关键字
    private $content_keyword;

    //回复信息 使用的xml 模板 字符串
    private $template_Type;
//--------------------------数据库存储方式-----------------------------        
    public $config = '';

    //没有关注的的扫描二维码事件
    public $qrscene_='';
   function __construct($config = '')
   {
        //获取post变量
        // $this->postObj = $GLOBALS["HTTP_RAW_POST_DATA"];
        $this->postObj = file_get_contents("php://input");


        if($config == ''){

            //没有  定义自定义配置使用系统定义配置
            $this->config = config('wechat_config');
            // $this->config
            // file_put_contents('./log/kk.txt',$this->config);
        }else{
            //有自定义配置
            $this->config = $config;
            // file_put_contents('./log/yyy.txt',$this->config);

        }
        //xml_todj()获取 xml并且 初始化 接收的成员属性
        //
        //template_xml() 初始化 信息面板 成员属性
        $this->xml_todj();
        // $this->template_xml();
        $this->content_type();//提取关键字


   }

   /**
    * [xml_todj 获取 xml 对象]
    * @Effect
    * @return [type] [description]
    */
   public function xml_todj()
   {

        // file_put_contents('./log/sql.txt','1122211');
        if(!empty($this->postObj))
        {
            // file_put_contents('./log/1sql.txt','1122211');
            //这个语句直接百度的时候，查到的信息是做安全防御用的：对于PHP，由于simplexml_load_string 函数的XML解析问题出现在libxml库上，所以加载实体前可以调用这样一个函数，所以这一句也应该是考虑到了安全问题。 
            libxml_disable_entity_loader(true);

            // simplexml_load_string() 函数把 XML 字符串载入对象中。
            // 如果失败，则返回 false。
            $postObj = simplexml_load_string($this->postObj, 'SimpleXMLElement', LIBXML_NOCDATA);
             
            //判断是否成功获取 xml对象
           if($postObj)
            {   
                // 赋值postObj成员属性
                $this->postObj = $postObj;

                //初始化成员属性
                $this->fromUsername = $postObj->FromUserName;

                $this->toUsername = $postObj->ToUserName;

                $this->keyword = trim($postObj->Content);

                $this->msgtype = $postObj->MsgType;

                $this->time = time();

                $this->thumbmediaid = $postObj->ThumbMediaId;

                $this->mediald = $postObj->posMediaId;

                $this->recognition = trim($postObj->Recognition,"！");

                $this->picurl = $postObj->PicUrl;

                $this->eventkey = $postObj->EventKey;

                $this->event = $postObj->Event;

                $this->Ticket  = $postObj->Ticket;

                /*测试使用
                    file_put_contents('sssss.txt',$this->fromUsername);
                */
            }else{

                //写入错误日志 mt_rand(0,500)
                file_put_contents('./log/LOG'.date('y_m_d H').'非法请求.txt',$this->postObj);
                exit('非法请求');
            }
            //返回 
            return true;

        }
   }

   /**
    * [content_type 提取关键字 判断信息类型 处理内容]
    * @Effect
    * @return [type] [description]
    */
   function content_type()
   {
    
        switch($this->msgtype)
        {
            case 'text'://文字回复

                //$this->msg_Type = "text";
                $this->content_keyword = $this->keyword;
                //数据库关键字
                $this->keyword_trigger();

                break;

            
            case 'image'://图片

                //$this->msg_Type = "text";

                break;
            case 'voice'://语音

                
                $this->voice();

                break;

            case 'video'://视频


                break;


            case 'event' ://事件

                switch($this->event)
                {

                    //subscribe(订阅)、unsubscribe(取消订阅)
                    // case subscribe:
                    // if(empty()){

                    //     $this->content_keyword = 'subscribe';
                    //     //数据库关键字
                    //     $this->keyword_trigger();


                    // }

                    // break;


                    case 'unsubscribe':

                        $this->content_keyword = 'unsubscribe';
                        //数据库关键字
                        $this->keyword_trigger();
                    break;


                    case 'CLICK':
                    //
                    //点击菜单拉取消息时的事件推送
                    //用户点击自定义菜单后，微信会把点击事件推送给开发者，请注意，点击菜单弹出子菜单，不会产生上报。
                        $this->content_keyword = 'unsubscribe';
                        //EventKey    事件KEY值，与自定义菜单接口中KEY值对应
                    break;


                    //点击菜单跳转链接时的事件推送
                    case 'VIEW':

                    //EventKey    事件KEY值，设置的跳转URL
                        $this->content_keyword = 'unsubscribe';

                    break;

                    // 扫描带参数二维码事件------------------------------------------------//
                    case 'subscribe': //1. 用户未关注时，进行关注后的事件推送

                        if(empty($this->Ticket)){
                            //没有  扫描事件  的关注事件
                            $this->content_keyword = 'subscribe';
                            //数据库关键字
                            $this->keyword_trigger();

                        }else{
                            //扫描二维码  并且没有关注公众号
                                $this->Ticket = ltrim($this->Ticket,'qrscene_');
                                // $this->qrscene_ = 'qrscene_';
                                $this->subscribe();                  
                        }
                        // $this->content_keyword = '关注事件';

                    // EventKey    事件KEY值，qrscene_为前缀，后面为二维码的参数值
                    // Ticket  二维码的ticket，可用来换取二维码图片
                    break;

                    case 'SCAN': //2. 用户已关注时的事件推送 (包括二维码)
                        file_put_contents('Ticket.txt',$this->Ticket);
                        file_put_contents('eventkey.txt',$this->eventkey);

                        $this->subscribe();
                        // file_put_contents('./eventkey2.txt', $this->eventkey);

                        // $this->content_keyword = '绑定成功';

                    // EventKey    事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id
                    // Ticket  二维码的ticket，可用来换取二维码图片
                    
                    break;

                    //上报地理位置事件
                    case 'LOCATION':
                    // Latitude    地理位置纬度
                    // Longitude   地理位置经度
                    // Precision   地理位置精度
                        $this->content_keyword = 'unsubscribe';

                    break;

                };


                break;

            default:
        }
   }

    /**
     * 触发 关键字 函数
     * @param string $type 关键字名称
     * @param string $contents 内容
     */
   function keyword_trigger($content='',$type='text')
   {
        //获取关键字
        //inject_check($Sql_Str)//自动过滤Sql的注入语句。
        //$content_keyword = func::inject_check($this->content_keyword);
       $content_keyword = $this->content_keyword;
        if(empty($content)){
            file_put_contents('fromUsername.txt','ssss1s'.$content);
//            获取关键字
            $sql_keyword = $this->content_keyword;
            // 判断数据库存储类型
            // file_put_contents('./log/yy2.txt',$this->config);
            if($this->config['cache_keyword_type'] == 'mysql'){
                //查询关键字
                //链接数据库
                $mysqi =new WechatKeyword;
                //$result = $mysqi->where("name LIKE '%{$sql_keyword}'")->find();
                $result = $mysqi->where("name = '{$sql_keyword}'")->select()->toArray();
                if($result){
                    //获取关键字 参数
                    $result = $result[0];
                    $name = $result['name'];
                    $model = $result['model'];
                    $method = $result['method'];
                    $type = $result['type'];
                    $content = $result['content'];

                    //部分模块不需要现在定义回复内容，数据库在无内容
                    if(empty($content))
                    {
                        $content='';
                    }
                }else{
                    $name = 'name';
                    $model = 'keyword';
                    $method = 'index';
                    $content='自动回复';
                }

            }else if($this->config == 'redis'){


            }
            //自定义关键字
        }else{
            $name = 'name';
            $model = 'keyword';
            $method = 'index';
        }

       $this->content_keyword = $content;
       //匹配回复   信息模板
       $this->template_Type = $this->template_xml[$type];

        /*
            这里设置检查   模块类是否存在
            不存在  写入日志
         */
          // ./module/模块名称/首字母大写的模块名字.Module.class.php
        if(!file_exists('../extend/WechatBrief/Module/'.ucfirst($model).'/'.ucfirst($model).'Module.php'))
        {

            file_put_contents('./LOG_module'.date('ymd_h').'.txt','[类名称]./module/'.$model.'/'.ucfirst($model).'Module.class.php'.'[关键字]'.$name,FILE_APPEND);
            exit();

        }

        
        //包含 模块 类default
        //
        
        $new1 = '\WechatBrief\Module\\'.ucfirst($model).'\\'.ucfirst($model).'Module';
        // file_put_contents('./log/Rapi.txt',$new1);
        // $uploadMgr = new $aaa();
        // Think\Wechat\Module
        // include './module/'.$model.'/'.ucfirst($model).'Module.class.php';
        // //实例化
        // $new1 = ucfirst($model).'Module';
        // file_put_contents('new.txt',$new1);
        
        $new = new $new1;
        // file_put_contents('./ThinkPHP/Library/Org/Wechat/Module/log/Rapi.txt',$new1);
        //参数1、模板、2、来源用户，是哪个用户跟我们发的消息 3、开发者微信id  4、时间戳   5、回复信息的格式    6、需要被回复的内容
        //7、图文信息内容  类型array
        //8、关键字 

        $template_Type = $this->template_Type;
        $fromUsername = $this->fromUsername;
        $toUsername = $this->toUsername;
        $time = $this->time;
        //$ontent_keyword = $this->content_keyword;
        $recognition = $this->recognition;
        //file_put_contents('./content_keyword2.txt',$template_Type.$fromUsername.$toUsername.$time.$type.$content.$news_content.$content_keyword.$recognition);
        //回复信息      =   调用$method（）模块中的方法 处理学院返回的 完整xml内容  标签echo 到微信
        echo $content_text = $new->$method($template_Type,$fromUsername,$toUsername,$time,$type,$content,$news_content='',$this->content_keyword,$recognition);
        //return ture;
   }

   //语音识别 选择处理
   function voice()
   {


        if(C('WECHAT_GLOBAL')){
            
            //使用redis 存储

            $redis = new \Redis();
            $redis->connect( C('WECHAT_REDIS_HOST'),C('WECHAT_REDIS_PORT'));
            $redis->auth(C('WECHAT_REDIS_AUTH')); 
            $redis->set('Wechat_voice','4');
            $voice = $redis->get("Wechat_voice");
            // file_put_contents('./log/content_keyword.txt',$voice);

        }else{
            //使用文件存储
            //判断文件是否存在
            if(file_exists('./ThinkPHP/Library/Org/Wechat/Module/Cache/voice.txt')){

                $voice = file_get_contents('./ThinkPHP/Library/Org/Wechat/Module/Cache/voice.txt');

            }else{

                //文件不存在默认 提示无法处理
                //1、text关键字：
                // 把语音识别结果过滤掉[。！，？]->然后使用text关键字功能回复
                $voice = 1;

            }
        }


        // 存在根据条件（内容）处理
        // 1、数据库关键字
        // 2、机器人
        // 3、有道翻译
        // 4、原样回复（文字）

        if($voice == 1)
        {
            //过滤英文标点符号 过滤中文标点符号 标签 赋值给 $this->content_keyword 
            $this->content_keyword = func::filter_mark($this->recognition);
            $this->keyword_trigger();
        }else if($voice == 2){


        }else if($voice == 3){

             //包含 模块 类wx api 类
            // include './api/wx/PublicApi.class.php';
            $api = new \Org\Wechat\Port\PublicApi;

            $content = $api->ydfy_voice_hs($this->recognition);// 语音识别结果  有道翻译 函数
            

            //实例化
            // file_put_contents('./log/cw2.txt','sssss');
            $new = new \Org\Wechat\Module\Keyword\KeywordModule;  
            //参数1、模板、2、来源用户，是哪个用户跟我们发的消息 3、开发者微信id  4、时间戳   5、回复信息的格式    6、需要被回复的内容
            //7、图文信息内容  类型array
            //8、关键字 
            $template_Type = $this->template_xml['text'];//匹配回复   信息模板
            $fromUsername = $this->fromUsername;
            $toUsername = $this->toUsername;
            $time = $this->time;
            //$ontent_keyword = $this->content_keyword;
            $recognition = $this->recognition;
            $type = 'text';
            //file_put_contents('./log/yys.txt',$template_Type.$fromUsername.$toUsername.$time.$type.$content.$news_content);

            echo $content_text = $new->index($template_Type,$fromUsername,$toUsername,$time,$type,$content,$news_content,$content_keyword,$recognition);

        }else if($voice == 4){

            //包含 模块 类default
            // DefaultModule.class.php
            //实例化
            $new = new \Org\Wechat\Module\Keyword\KeywordModule;  
            //参数1、模板、2、来源用户，是哪个用户跟我们发的消息 3、开发者微信id  4、时间戳   5、回复信息的格式    6、需要被回复的内容
            //7、图文信息内容  类型array
            //8、关键字 
            $content = $this->recognition;// 语音识别结果  有道翻译 函数
            $template_Type = $this->template_xml['text'];//匹配回复   信息模板
            $fromUsername = $this->fromUsername;
            $toUsername = $this->toUsername;
            $time = $this->time;
            //$ontent_keyword = $this->content_keyword;
            $recognition = $this->recognition;
            $type = 'text';
            // file_put_contents('./log/yys.txt',$template_Type.$fromUsername.$toUsername.$time.$type.$content.$news_content);
            
            echo $content_text = $new->index($template_Type,$fromUsername,$toUsername,$time,$type,$content,$news_content,$content_keyword,$recognition);

        }else if($voice == 5){
            $this->content_keyword = '抱歉无法处理语音识';
            $this->keyword_trigger();
        }


   }

   //绑定Wxlogin
   public function subscribe(){

        $Ticket = $this->Ticket;
        $openid = $this->fromUsername;
        settype($openid,'string');//设置数据类型
       file_put_contents('fromUsername.txt',$openid);
        //获取
        $QrData = QrLog::get(['ticketid'=>$this->Ticket,'content'=>$this->eventkey]);

        if(!$QrData){
            //异常信息
            
        }
        if(time()<$QrData['create_time']){
            $this->content_keyword = 'qr码过期'; 
            $this->keyword_trigger(); 
            exit();
        }
        if($QrData['status'] == 1){
            $this->content_keyword = 'qr码已经被使用'; 
            $this->keyword_trigger(); 
            exit();
        }
        //二维码类型0未知  1 绑定 2 登录
        switch ($QrData['Type']) {
            case 1:  //绑定账号

                //获取微信信息
                $AccessToken  = new AccessToken();
                $access_token = $AccessToken->access_token();
                $get_user_info = func::get_user_info($this->fromUsername,$access_token);
                //判断数据
                if(empty($QrData['uid'])){
                    $this->content_keyword = '未知错误';
                    $Clientdata = ['emit'=>'login_init','data'=>['code'=>1,'msg'=>'未知错误']];
                }

                if(OpenidUser::addUser($get_user_info,$QrData['uid'])){

                    QrLog::get(['ticketid'=>$this->Ticket,'content'=>$this->eventkey]);
                    //设置二维码使用状态
                    $QrData->status = 1;
                    $QrData->save();
                    $Clientdata = ['emit'=>'login_init','data'=>['code'=>0,'msg'=>'绑定成功']];
                    $this->content_keyword = '绑定事件';
                }else{
                    $Clientdata = ['emit'=>'login_init','data'=>['code'=>1,'msg'=>'重复绑定事件']];
                    //设置二维码使用状态
                    $QrData->status = 1;
                    $QrData->save();
                    $this->content_keyword = '重复绑定事件';
                }
                break;
            case 2:  //登录

                //获取用户信息
                $UserData = OpenidUser::where(['openid'=>$openid])->select()->toArray();
                file_put_contents('$UserData.txt',count($UserData));
                if(count($UserData)>0 && count($UserData)< 2){
                    
                    $Login = new AddLogin;
                    $loginData = $Login->loginActionRedisQr($UserData[0]['uid'],$QrData['login_remember'],$QrData['login_remember'],$QrData['http_agent']);
                    if($loginData['code'] == 0){
                        $this->content_keyword = '登录成功事件';
                    }else{
                        $this->content_keyword = $loginData['msg'];
                    }
                    $Clientdata = ['emit'=>'login_init','data'=>$loginData];

                    //设置二维码使用状态
                    $QrData->status = 1;
                    $QrData->save();

                    //$this->content_keyword = '登录成功事件';
                    //$uid = $UserData[0]['uid'];
                    file_put_contents('fromUsernamecc.txt',json_encode($UserData));
                }else if(count($UserData) == 0 ){
                    //loginActionRedis
                    $Clientdata = ['emit'=>'login_init','data'=>['code'=>1,'msg'=>'登录未绑定事件']];
                    $this->content_keyword = '您的微信没有未绑定账号';
                }else{
                    $Clientdata = ['emit'=>'login_init','data'=>['code'=>1,'msg'=>'绑定数据错误事件']];
                    $this->content_keyword = '绑定数据错误事件';
                }

                break;

            default:
                $Clientdata = ['emit'=>'login_init','data'=>['code'=>1,'msg'=>'微信二维码未知事件']];
                $this->content_keyword = '微信二维码未知事件'; 
                break;


        }
       $this->sendToClient($QrData['socketid'], json_encode($Clientdata));
//
        //$this->content_keyword = $QrData['content'];
        $this->keyword_trigger();
        exit();
/*********************************************************************************************/

        //是否有 二维码 信息
        $wx_qr = M('Wx_qr');
        // $qr_data = $wx_qr->getByEmail('liu21st@gmail.com');
        $qr_data = $wx_qr->where("ticketid = '{$Ticket}' AND state = 2")->find();

        if(!$qr_data){
            //没有有效的二维码
            $this->content_keyword = '二维码已经被使用'; 
            $this->keyword_trigger();
            exit();
        }

        //二维码正常
        
        //判断二维码是否在有效期
        if((time()-$qr_data['time']) > 600){
            //没有有效的二维码
            $this->content_keyword = '二维码过期'; 
            $this->keyword_trigger(); 
            exit();

        }
        // 在有效期
        // 链接用户表
        


        $user = M('User');
        //判断  事件类型
        ////判断  是否已经绑定账号
        $user_data = $user->where("openid = '{$openid}'")->find();
        switch ($qr_data['type']) {
            case 1:  //绑定账号

                //判断  是否已经绑定账号
                if($user_data['uid']){
                    //没有绑定账号
                    $this->content_keyword = '重复绑定事件';

                }else{
                    $uid = $qr_data['uid'];

                    $data['state'] = 1; //确定 已经使用  
                    // settype($openid,'string');
                    $data['openid'] = $openid;
                    
                    $btuser = $wx_qr->where("ticketid = '{$Ticket}'")->save($data); //更新二维码状态

                    //判断  修改状态 
                    if($btuser){
                        //获取  微信用户信息
                        //获取AccessToken
                        $AccessToken  = new \Org\Wechat\Port\AccessToken();
                        $access_token = $AccessToken->access_token();

                        $odata  = $this->get_user_info($access_token,$openid);
                        //准备数据
                        $udata['openid'] =  $odata['openid'];
                        $udata['scope'] =  $odata['wq'];
                        $udata['add_wx_time'] =  $odata['subscribe_time'];
                        $udata['nickname'] =  $odata['nickname'];
                        $udata['sex'] =  $odata['sex'];
                        $udata['province'] =  $odata['province'];
                        $udata['country'] =  $odata['country'];
                        $udata['headimgurl'] = 'https://'.ltrim($odata['headimgurl'],"http://");
                        // $udata['headimgurl'] =  $odata['headimgurl'];

                        $userbt = $user->where("uid = '{$uid}'")->save($udata);//绑定参数
                        // file_put_contents('./id.txt',$uid);
                        if($userbt){

                            $this->content_keyword = '绑定事件';
                        }
                        


                    }else{

                        $this->content_keyword = '绑定事件失败'; 
                    }


                }                    



                break;

            case 2:  //登录类型

                //查询 扫描二维码的微信是否有绑定账号
                $user_data = $user->where("openid = '{$openid}'")->find();
                // $user_data = $user->getByOpenid($openid);
                 // $this->content_keyword = '请绑定账号'; 
                if(!$user_data){
                    //没有绑定账号
                    $this->content_keyword = '请绑定账号'; 
                }else{
                    

                    $data['state'] = 1; 
                    // settype($openid,'string');
                    $data['openid'] = $openid; 
                    
                    $btuser = $wx_qr->where("ticketid = '{$Ticket}'")->save($data); 
                    // $this->content_keyword = '请绑定账号';
                    //判断  修改状态 
                    if($btuser){
                        $this->content_keyword = '登录事件';

                    }else{

                        $this->content_keyword = '登录事件失败'; 
                    }


                }

                break;

            
            default:
                $this->content_keyword = '微信二维码未知事件'; 
                break;
        }

        $this->keyword_trigger();//使用关键字
    


   }

    //获取用户基本信息
    public function get_user_info($access_token,$openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = func::http_request($url);
        return json_decode($res, true);
    }

   //无法处理的未知触发
   public function abnormal_trigger()
   {





   }


   /**
    * [inject_check 自动过滤Sql的注入语句]
    * @Effect
    * @param  [type] $Sql_Str [需要过滤的数据]
    * @return [type]          [description]
    */
    public function inject_check($Sql_Str)//。
    {   

        if (!get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否打开     
        {     
        $Sql_Str = addslashes($Sql_Str); // 进行过滤     
        }     
        $Sql_Str = str_replace("_", "_", $Sql_Str); // 把 '_'过滤掉     
        $Sql_Str = str_replace("%", "%", $Sql_Str); // 把' % '过滤掉  
        
        $check=preg_match("/select|insert|update|;|delete|'|\*|*|../|./|union|into|load_file|outfile/i",$Sql_Str);
        if ($check) {
                return '非法关键字';
                //echo '<script language="JavaScript">alert("系统警告：nn请不要尝试在参数中包含非法字符尝试注入！");</script>';
                exit();
        }else{
                return $Sql_Str;
        }
    }


    /**
     * 向某个客户端连接发消息
     *
     * @param int    $client_id
     * @param string $message
     * @return bool
     */
    public function sendToClient($client_id, $message)
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
        return Gateway::sendToClient($client_id, $message);
    }


}
