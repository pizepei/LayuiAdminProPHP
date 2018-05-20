<?php
/**
 * @Author: anchen
 * @Date:   2017-04-18 22:22:41
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-15 22:33:07
 */
    // namespace Think\Wechat\Module;
    // \Module\拓展目录\
    
    namespace WechatBrief\Module\Keyword;
    
    class KeywordModule{
        //参数1、模板、2、来源用户，是哪个用户跟我们发的消息 3、开发者微信id  4、时间戳   5、回复信息的格式    6、需要被回复的内容
        //7、图文信息内容  类型array
        //8、关键字 
        function index($template_Type,$fromUsername,$toUsername,$time,$type,$content,$news_content,$content_keyword,$recognition)
        {

            
            switch($type)
            {
                case 'text'://文字回复

                 $content_text = sprintf($template_Type, $fromUsername, $toUsername, $time, $type, $content);
                // file_put_contents('content_text.txt',$content_text);
                return $content_text;
                    break;

                    
                case 'news'://图文回复
                    //于对 JSON 格式的字符串进行解码，并转换为 PHP 变量。  true 当该参数为 TRUE 时，将返回数组，FALSE 时返回对象。
                    $content = json_decode($content, true);
                    // file_put_contents('json.txt',$content);
                // $content = array(

                //     array('Title'=>'会员中心','Description'=>'速度是多少','PicUrl'=>'http://abcabc1314.net/sc/wxbt.jpg','Url'=>'http://www.pizepei.com')

                //     );
                    //获取图文数量
                    $count = count($content);
                    
                    foreach ($content as $k => $v) {
                        $value .='<item>
                                    <Title><![CDATA['.$v['Title'].']]></Title>
                                    <Description><![CDATA['.$v['Description'].']]></Description>
                                    <PicUrl><![CDATA['.$v['PicUrl'].']]></PicUrl>
                                    <Url><![CDATA['.$v['Url'].']]></Url>
                                  </item>';
                    }

                    $news = str_replace('{$item}',$value,$template_Type);
                    //file_put_contents('news.txt',$news);
                    //file_put_contents('news22.txt',$news.'|'.$fromUsername.'|'.$toUsername.'|'.$time.'|'.$type.'|'.$count);
                    $content_text = sprintf($news,$fromUsername,$toUsername,$time,$type,$count);
                    // file_put_contents('content_textnews.txt',$content_text);
                    return $content_text;
/*
json 格式
        $content = array(

            array('Title'=>'会员中心','Description'=>'云海翻腾','PicUrl'=>'http://wx1.sinaimg.cn/mw690/006D2KSdly1fghgdujwsnj31jk111n1y.jpg','Url'=>'http://fuliba.net'),
            array('Title'=>'会员中心','Description'=>'云海翻腾','PicUrl'=>'http://wx1.sinaimg.cn/mw690/006D2KSdly1fghgdujwsnj31jk111n1y.jpg','Url'=>'http://fuliba.net'),
            array('Title'=>'会员中心','Description'=>'云海翻腾','PicUrl'=>'http://wx1.sinaimg.cn/mw690/006D2KSdly1fghgdujwsnj31jk111n1y.jpg','Url'=>'http://fuliba.net')
            );


        echo $content = json_encode($content);
*/

                    break;
                
                case 'image'://图片

                    //$this->msg_Type = "text";

                    break;
                case 'voice'://语音
                
                    $content_text = sprintf($template_Type, $fromUsername, $toUsername, $time, $type, $content);
                    //file_put_contents('content_text.txt',$content_text);
                    return $content_text;
                    
                    break;

                case 'video'://视频



                    break;


                case 'event' ://事件


                    break;

                default:
            }
            

        }

    }
