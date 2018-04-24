<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-24 10:06:01
 */
namespace common\custom;
use think\Request;
/**
 * 访问客户端信息
 */
class TerminalInfo{

    //浏览器类型
    public static  $AgentInfoBrower = array(  
                'MSIE' => 1,  
                'MicroMessenger' => 6,  
                'Firefox' => 2,  
                'QQBrowser' => 3,  
                'QQ/' => 4,  
                'UCBrowser' => 5,  
                'Edge' => 7,  
                'Chrome' => 8,  
                'Opera' => 9,  
                'OPR' => 10,  
                'Safari' => 11,  
                'Trident/' => 12,
            );
    //浏览器类型
    public static   $AgentInfoBroweInfo = array(  
                'IE(MSIE)' => 1,  
                '微信(MicroMessenger)' => 6,  
                '火狐(Firefox)' => 2,  
                '腾讯(QQBrowser)' => 3,  
                '腾讯(QQ/)' => 4,  
                'UC/支付宝(UCBrowser)' => 5,  
                'Edge' => 7,  
                '谷歌(Chrome)' => 8,  
                '欧朋(Opera)' => 9,  
                '欧朋(OPR)' => 10,  
                '苹果(Safari)' => 11,  
                'IE(Trident/)' => 12,
        );
    //操作系统
    public static  $OsInfo =[
            '其它系统'=> 0 ,//未知
            'Windows_95'=> 1,
            'Windows_ME'=> 2,
            'Windows_98'=> 3, 
            'Windows_Vista'=> 4, 
            'Windows_7'=> 5, 
            'Windows_8'=> 6, 
            'Windows_10'=> 7,
            'Windows_XP'=> 8,  
            'Windows_2000'=> 9,  
            'Windows_NT'=> 10,  
            'Windows_32'=> 11,  
            'Linux'=> 12,  
            'Unix'=> 13,  
            'SunOS'=> 14,  
            'IBM_OS_2'=> 15,  
            'Macintosh'=> 16,  
            'PowerPC'=> 17,  
            'AIX'=> 18,  
            'HPUX'=> 19,  
            'NetBSD'=> 20,  
            'BSD'=> 21,  
            'OSF1'=> 22,  
            'IRIX'=> 23,  
            'FreeBSD'=> 24,  
            'teleport'=> 25,  
            'flashget'=> 26,  
            'webzip'=> 27,  
            'offline'=> 28,  
            'Android' => 29, 
            'iPhone' => 30,

        ];

    //操作系统
    public static  $IpInfo =['192.168.1.1','127.0.0.1','0.0.0.0'];


    const unknown_os   = 0 ;//未知
    const Windows_95 = 1;
    const Windows_ME = 2;
    const Windows_98 = 3; 
    const Windows_Vista = 4; 
    const Windows_7 = 5; 
    const Windows_8 = 6; 
    const Windows_10 = 7;
    const Windows_XP = 8;  
    const Windows_2000 = 9;  
    const Windows_NT = 10;  
    const Windows_32 = 11;  
    const Linux = 12;  
    const Unix = 13;  
    const SunOS = 14;  
    const IBM_OS_2 = 15;  
    const Macintosh = 16;  
    const PowerPC = 17;  
    const AIX = 18;  
    const HPUX = 19;  
    const NetBSD = 20;  
    const BSD = 21;  
    const OSF1 = 22;  
    const IRIX = 23;  
    const FreeBSD = 24;  
    const teleport = 25;  
    const flashget = 26;  
    const webzip = 27;  
    const offline = 28; 
    const Android = 29;  
    const iPhone = 30;  
    /**
     * [getArowserInfo 获取浏览数据]
     * @Effect
     * @return [type] [description]
     */
    public static function  getArowserInfo($type = 'arr'){

        $arr['Ipanel'] =self::getAgentInfo();//获取浏览器内核

        $arr['language'] = self::get_lang();//获取浏览器语言

        $arr['Os'] = self::get_os();//获取操作系统


        $arr['IpInfo'] = self::getIp();//时时ip信息

        if($arr['Os'] == 29){
            $arr['Build'] = self::getBuild();//获取安卓手机型号
            $arr['NetType'] = self::getBuildNetType();
        }else if($arr['Os'] == 30){
            $arr['Build'] = self::getBuildIPhone();
            $arr['NetType'] = self::getBuildNetType();
        }else{

        }
        //判断返回格式
        return $arr =  $type == 'arr'?$arr:json_encode($arr);

    }
    public static function  getArowserPro($type = 'arr'){

        $arr['Ipanel'] =self::getAgentInfo(self::getAgentInfo());//获取浏览器内核

        $arr['language'] = self::get_lang();//获取浏览器语言

        $arr['Os'] =  self::get_os() ==29?self::get_os():array_search(self::get_os(),self::$OsInfo);//获取操作系统

        $arr['IpInfo'] = self::getIp();//时时ip信息

        if(self::get_os() ==29){
            $Build = self::getBuild();

            $count = count($Build);
            if($count >1){
                $count = $count-1;
                $count = $count == 0 ?'':$Build[$count];
                $arr['Os'] = $Build[1].' | '.$count;
            }else{
                $arr['Os'] = $Build[0];
            }

            $arr['Build'] = $Build;//获取安卓手机型号
            $arr['NetType'] = self::getBuildNetType();
        }else if(self::get_os() == 30){

            $Build = self::getBuildIPhone();


            $count = count($Build);
            if($count >1){
                $count = $count-1;
                $count = $count == 0 ?'':$Build[$count];
                $arr['Os'] = $Build[1].' | '.$count;
            }else{
                $arr['Os'] = $Build[0];
            }

            // $arr['Os'] = $Build[0].' '.$Build[1];
            
            $arr['Build'] = $Build;
            $arr['NetType'] = self::getBuildNetType();
        }else{
            $arr['Os'] = array_search(self::get_os(),self::$OsInfo);//获取操作系统
            $arr['NetType'] = 'Ethernet';
        }
        //判断返回格式
        return $arr =  $type == 'arr'?$arr:json_encode($arr);

    }

    /**
     * [getAgentInfo 获取浏览器内核]
     * @Effect
     * @param  boolean $Data [浏览器内核 值]
     * @return [type]        [description]
     */
    public static function getAgentInfo($Data = false){
        //如果没有存入 浏览器内核 值 就是获取浏览器内核 值
        if(!$Data){
            $agent = $_SERVER['HTTP_USER_AGENT'];  
            $browser_num = 0;//未知  
            foreach(self::$AgentInfoBrower as $bro => $val){  
                if(stripos($agent, $bro) !== false){  
                    $browser_num = $val;  
                    break;  
                }
            }
            return  $browser_num;  
        }
        //存入就是获取 文字浏览器内核名称
        return array_search($Data,self::$AgentInfoBroweInfo);
    }

    /**
     * 获得访问者浏览器语言
     */
    public static function get_lang() {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。  
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match("/zh-c/i", $lang))  
            $lang = "简体中文";  
            else if (preg_match("/zh/i", $lang))  
            $lang = "繁體中文";  
            else if (preg_match("/en/i", $lang))  
            $lang = "English";  
            else if (preg_match("/fr/i", $lang))  
            $lang = "French";  
            else if (preg_match("/de/i", $lang))  
            $lang = "German";  
            else if (preg_match("/jp/i", $lang))  
            $lang = "Japanese";  
            else if (preg_match("/ko/i", $lang))  
            $lang = "Korean";  
            else if (preg_match("/es/i", $lang))  
            $lang = "Spanish";  
            else if (preg_match("/sv/i", $lang))  
            $lang = "Swedish";  
            else
            $lang = "else";  

            return $lang;
        } else {
            return 'unknow';
        }
    }

    /**  
     * 获取客户端操作系统信息包括win10  
     * @param  null  
     * @author  Jea杨  
     * @return string   
     */  
    public static function get_os($Data=false){  

        if(!$Data){
            $agent = $_SERVER['HTTP_USER_AGENT'];  
            $os = false;  
            if (preg_match('/win/i', $agent) && strpos($agent, '95'))  
            {  
              $os = self::Windows_95;  
            }  
            else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))  
            {  
              $os = self::Windows_95;    
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))  
            {  
              $os =  self::Windows_98; 
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))  
            {  
              $os = self::Windows_Vista;  
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))  
            {  
              $os = self::Windows_7;  
            }  
              else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))  
            {  
              $os = self::Windows_8;  
            }else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))  
            {  
              $os = self::Windows_10;
            }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))  
            {  
              $os = self::Windows_XP;  
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))  
            {  
              $os = self::Windows_2000;  
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))  
            {  
              $os = self::Windows_NT;  
            }  
            else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))  
            {  
              $os = self::Windows_32;  
            }  
            else if (preg_match('/Android/i', $agent)){
              $os = self::Android;  
            }
            else if (preg_match('/linux/i', $agent))  
            {  
              $os = self::Linux;  
            }  
            else if (preg_match('/unix/i', $agent))  
            {  
              $os = self::Unix;  
            }  
            else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))  
            {  
              $os = self::SunOS;  
            }  
            else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))  
            {  
              $os = self::IBM_OS_2;  
            }  
            else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))  
            {  
              $os = self::Macintosh;  
            }  
            else if (preg_match('/PowerPC/i', $agent))  
            {  
              $os = self::PowerPC;  
            }  
            else if (preg_match('/AIX/i', $agent))  
            {  
              $os = self::AIX;  
            }  
            else if (preg_match('/HPUX/i', $agent))  
            {  
              $os = self::HPUX;  
            }  
            else if (preg_match('/NetBSD/i', $agent))  
            {  
              $os = self::NetBSD;  
            }  
            else if (preg_match('/BSD/i', $agent))  
            {  
              $os = self::BSD;  
            }  
            else if (preg_match('/OSF1/i', $agent))  
            {  
              $os = self::OSF1;  
            }  
            else if (preg_match('/IRIX/i', $agent))  
            {  
              $os = self::IRIX;  
            }  
            else if (preg_match('/FreeBSD/i', $agent))  
            {  
              $os = self::FreeBSD;  
            }  
            else if (preg_match('/teleport/i', $agent))  
            {  
              $os = self::teleport;  
            }  
            else if (preg_match('/flashget/i', $agent))  
            {  
              $os = self::flashget;  
            }  
            else if (preg_match('/webzip/i', $agent))  
            {  
              $os = self::webzip;  
            }  
            else if (preg_match('/offline/i', $agent))  
            {  
              $os = self::offline;  
            }  
            else if (preg_match('/iPhone/i', $agent)){
              $os = self::iPhone;  
            }
            else  
            {  
              $os = self::unknown_os;  
            }  
            return $os;   
        }

         return array_search($Data,self::$OsInfo);
    }  

    /**
     * [get_os_show 获取文字标识系统显示]
     * @param  [type] $id [id]
     * @return [type]     [description]
     */
    public static function get_os_show($id)
    {
        return array_search($id,self::$OsInfo);
    }
    /**
     * [getBuild 获取安卓手机型号]
     * @Effect
     * @return [type] [description]
     */
    public static function getBuild(){
        $agent = $_SERVER['HTTP_USER_AGENT'];  
        if(!preg_match("/; (.*) Build\//i",$agent,$arrt)){
            return '未知型号';
        }
        if(empty($arrt[1])){
            return '未知型号数据';
        }

        // return '未知型号版本数据';
        return explode('; ',$arrt[1]);
    }


    public static function getBuildIPhone(){
        $agent = $_SERVER['HTTP_USER_AGENT'];  
        if(!preg_match("/; CPU (.*) like Mac OS X/i",$agent,$arrt)){
            return '未知型号版本';
        }
        if(empty($arrt[1])){
            return '未知型号版本数据';
        }
        return explode('; ',$arrt[1]);
    }

    public static function getBuildNetType(){
        // $agent = 'Mozilla/5.0 (Linux; Android 7.1.1; ONEPLUS A5010 Build/NMF26X; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/043906 Mobile Safari/537.36 MicroMessenger/6.6.3.1260(0x26060339) NetType/WIFI Language/zh_CN'; 
        $agent = $_SERVER['HTTP_USER_AGENT'];  

        // NetType/WIFI Language
        if(!preg_match("/ NetType\/(.*) Language/i",$agent,$arrt)){
            return '未知网络';
        }
        return $arrt[1];
    }

    public static function getIp($value = ''){

        if(empty($value)){
            $request = Request::instance();
            $value = $request->ip();
        }

        if(in_array($value,self::$IpInfo)){
            return $value;
        }
        //淘宝接口
        $Turl = 'https://ip.taobao.com/service/getIpInfo.php?ip=113.91.189.73';
        http://ip.taobao.com/service/getIpInfo.php?ip=113.91.189.73
        $url = 'https://api.map.baidu.com/location/ip?ip='.$value.'&ak='.config('lbsyun_baidu.ak').'&coor=bd09ll';
        // $IpData = http_request($url);
        $IpData = json_decode(http_request($url),true);

        if($IpData['status'] == 0){
           return  $IpData['content']['address_detail'];
        }
        return $IpData;
    }



}