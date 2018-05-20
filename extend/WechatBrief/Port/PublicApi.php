<?php
/**
 * @Author: pizepei
 * @Date:   2017-06-13 15:19:27
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-12 17:20:56
 */

    namespace WechatBrief\Port;
    use WechatBrief\func;
    
    class PublicApi
    {

        function ydfy_voice_hs($recognition)// 语音识别结果  有道翻译 函数
        {
            $_xx = '语音识别结果: '.$recognition;
            $keywor= urlencode($recognition);
            $url= "http://fanyi.youdao.com/openapi.do?keyfrom=abc314&key=1424434700&type=data&doctype=json&version=1.1&q=" .$keywor;
            //$list = $this->curl($url);
            $list = file_get_contents($url);
            $js_de = json_decode($list,true);
            $_xx1=$js_de['translation'] ['0'];
            $_xx2=$js_de["basic"]['phonetic'];
            $_xx3=$js_de["basic"]["explains"][0];
            $_xx4=$js_de["basic"]["explains"][1];
            $_xx5 = $js_de['query'];
            $_xx6='
                 '.$js_de["web"][0]["value"][0].'
                  '.$js_de["web"][1]["value"][0].'
                  '.$js_de["web"][2]["value"][0];
/*                  
         $_xx='          《 中英互译 》           
'.'语音识别结果: '.$recognition.'
原文：
    '.$_xx5.'
翻译：
    '.$_xx1.'
原文发音：'.$_xx2.'
解释：'.$_xx3.''.$_xx4.'
网络释义:'.$_xx6.'
注： 翻译结果支持英日韩法俄西到中文的翻译以及中英互译';
*/

         $_xx='          《 中英互译 》           
'.'语音识别结果: '.$recognition.'
原文：
    '.$_xx5.'
翻译：
    '.$_xx1.'
注： 翻译结果支持英日韩法俄西到中文的翻译以及中英互译';


            return $_xx;
        }


        function ydfy_wz_hs($keyword)//有道文字翻译 函数
        {
            $keywor= urlencode($keyword);
            $url= "http://fanyi.youdao.com/openapi.do?keyfrom=abc314&key=1424434700&type=data&doctype=json&version=1.1&q=" .$keywor;
            $list = file_get_contents($url);
            $js_de = json_decode($list,true);
            $_xx5 = $js_de['query'];
            $_xx1=$js_de['translation'] ['0'];
            $_xx2=$js_de["basic"]['phonetic'];
            $_xx3=$js_de["basic"]["explains"][0];
            $_xx4=@$js_de["basic"]["explains"][1];
            $_xx6='
                 '.$js_de["web"][0]["value"][0].'
                  '.$js_de["web"][1]["value"][0].'
                  '.$js_de["web"][2]["value"][0];
                 
                 $_xx='            《 中英互译 》           
'.'原文：
         '.$_xx5.'
翻译：
         '.$_xx1.'
原文发音：
         '.$_xx2.'
解释：
         '.$_xx3.'

         '.$_xx4.'
网络释义:'.$_xx6.'
注： 翻译结果支持英日韩法俄西到中文的翻译以及中英互译';
            return $_xx;
                  
        }        

        function curl($url)
        {


            header("Content-type:text/html;charset=utf-8");
            //curl 模拟get请求1 截取部分数据 以数据流的形式显示出 而不是整个页面

            //1.初始化
            $ch=curl_init();
            
            //$url="http://www.kuitao8.com/api/joke";
            //2.设置变量
            curl_setopt($ch,CURLOPT_URL,$url);
            //把数据以数据流的形式显示出
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            //3.执行
            $output=curl_exec($ch);
            //4.关闭curl
            curl_close($ch);

            // echo $output;
            //json_decode()
            $s=json_decode($output,true);
            // file_put_contents('./curl.txt',$s);
            return $s;



        }


        function access_token()
        {

            //定义第三方平台凭证
            $appID="wx82b4d710c62c0490";
            //定义凭证秘钥
            $appsecret="7963856ba3adabe2fbb3ec91141f2d21";
            //接口地址
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appsecret}";
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            $output=curl_exec($ch);
            curl_close($ch);
            // echo $output;
            $access_token=json_decode($output,true);
            //var_dump($access_token);
            echo $access_token['access_token'];


        }

    }