<?php
/**
 * @Author: anchen
 * @Date:   2017-06-04 12:15:29
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-12 16:55:38
 */


    namespace Org\Wechat\Module\Qqdzz;
    use Org\Wechat\Module\Qqdzz;
    
    class QqdzzModule{

        //参数1、模板、2、来源用户，是哪个用户跟我们发的消息 3、开发者微信id  4、时间戳   5、回复信息的格式    6、需要被回复的内容
        //7、图文信息内容  类型array
        //8、关键字 
        
        function index($template_Type,$fromUsername,$toUsername,$time,$type,$content,$news_content,$content_keyword)
        {

            switch($type)
            {
                case text://文字回复

                //$content = bbtjr($fromUsername,$content_keyword);//领取棒棒糖


                $url='http://bbt.pizepei.com/php/do_add_bbt.php?url='.$content_keyword;
                
                @$list = file_get_contents($url);//获取
                $list = json_decode($list,TRUE);
                $list_content = '尊敬的：'.$list['account'].'你好！
                
';

                //$list_content = '尊敬的：'.$list['account'].'['.$list['account'].']'.'';
                

                //判断 是否重复 操作
                if($list['code'] == 1)
                {
                    $list_content .= '请不要重复领取[每人明天1次]';
                }else if($list['code']==0){
                    $list_content .= '领取棒棒糖成功';
                }else if(empty($list['code']) || !isset($list['code']))
                     //判断是否接口失败
                {
                     $list_content .='意外错误，请留下管理员微信【ZE2016WX】';
                    
                }else{

                    $list_content .='意外错误，请留下管理员微信【ZE2016WX】';
                }
                
                $content = $list_content;

                 $content_text = sprintf($template_Type, $fromUsername, $toUsername, $time, $type, $content);
                //file_put_contents('content_text.txt',$content_text);
                return $content_text;
                    break;
                case news://图文回复
                     $title1 = '会员中心';

                     $desc1 = '想您所想看你所看';

                     $picUrl1 = 'http://abcabc1314.net/sc/wxbt.jpg';

                     $url1 = 'http://www.abc314.net';

                     $title2 = '皮泽培的个人博客';

                     $desc2 = '阿泽的互联网ID';

                     $picUrl2 = 'http://mmbiz.qpic.cn/mmbiz/xsUzAUEquy4leUSM4yLYSsGiaUIZLJ5ImnibCdftYd3KzJWz99iaR19Dp6FIz03XN4icxkmL9Je3cc4uXTGCo9Ynaw/640?wx_fmt=jpeg&tp=webp&wxfrom=5';

                     $url2 = 'http://www.pizepei.com';

                $content_text = sprintf($template_Type, $fromUsername, $toUsername, $time, $type, $title1, $desc1, $picUrl1, $url1, $title2, $desc2, $picUrl2, $url2);


                    break;
                
                case image://图片

                    //$this->msg_Type = "text";

                    break;
                case voice://语音

                    
                    break;

                case video://视频



                    break;


                case event ://事件


                    break;

                default:
            }
            

        }



        function bbtjr($fromUsername,$keyword)//领取棒棒糖
        {
            $pd = './module/qqdzz/bbtjr/'.$fromUsername.'.txt';//记录目录
            $pd_url = './module/qqdzz/bbt_url_tx/url_ts.txt';//URL提示记录目录
            @$sj= filemtime($pd) ;//记录时间

            if((time()-$sj)>=8000)
            {
                $url='http://bbt.pizepei.com/php/do_add_bbt.php?url='.$keyword;
                //$url='http://www.qiuqiuwu.com/php/do.php?url='.$keyword;//url
                @$list = file_get_contents($url);//获取
                    
                    if (strlen($list)>=40 )
                    {
                    $list1= substr_replace("$list",'尊敬的',0,30);
                    $list2= substr_replace("$list1",'',-13,13);
                    $list3= substr_replace("$list",'[',0,10);
                    $list4= substr_replace("$list3",']"
        恭喜您本次刷糖成功！
        ',10,65);

                    @file_put_contents($pd,$list2.$list4);//入住记录
                    $_xx=file_get_contents($pd).file_get_contents($pd_url);
                    return $_xx;
                
                    }else
                    {
                        echo '抱歉！
                        网络繁忙请10分钟后再试
                        也可直接进入网页版刷棒棒糖！！
                        地址：<a href="http://wx.admin.pizepei.com/bbs/shop.html">点我进入</a>';
                    }
            }else
                { 
                $zzz1=file_get_contents($pd);
                $bbtsc=substr_replace("$zzz1",'
        每人每天一次,请不要重复刷 !

        上次刷糖 ：
        '.date("m月d号H点i分s秒",$sj),-32,32).'
        如需365天自动刷棒棒糖
        <a href="http://wx.admin.pizepei.com/bbs/shop.html">请点我进入</a>';
                return $bbtsc;
            }
        }



        function inject_check($Sql_Str)//自动过滤Sql的注入语句。
        {   

            if (!get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否打开     
            {     
            $Sql_Str = addslashes($Sql_Str); // 进行过滤     
            }     
            $Sql_Str = str_replace("_", "_", $Sql_Str); // 把 '_'过滤掉     
            $Sql_Str = str_replace("%", "%", $Sql_Str); // 把' % '过滤掉  
            
            $check=preg_match("/select|insert|update|;|delete|'|\*|*|../|./|union|into|load_file|outfile/i",$Sql_Str);
            if ($check) {

                    echo '<script language="JavaScript">alert("系统警告：nn请不要尝试在参数中包含非法字符尝试注入！");</script>';
                    exit();
            }else{
                    return $Sql_Str;
            }
        }
    }