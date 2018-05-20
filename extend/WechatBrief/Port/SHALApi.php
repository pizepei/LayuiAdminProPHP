<?php
/**
 * @Author: pizepei
 * @Date:   2017-06-03 14:39:36
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-12 23:43:23
 */
namespace WechatBrief\Port;
/**
 * 验证 微信公众号 接口
 */
class SHALApi{
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     */
    private $token; //票据
    private $timestamp; // 时间戳
    private $echostr; //随机字符串
    private $nonce; //随机数
    private $signature;//微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。

    
    //构造函数 为 基本参数 赋 值
   function __construct($token='12345')
   {

        $this->token = $token;
        $this->timestamp = $_GET['timestamp'];
        $this->nonce = $_GET['nonce'];
        $this->signature = $_GET['signature'];
        $this->echostr = $_GET['echostr'];
   }

   //权限控制器
   function control()
   {

        if($this->token())
        {
            return $this->echostr;
        }else{

            exit('非法请求');
        }

   }


   function token()
   {
        //将token、timestamp、nonce三个参数进行字典序排序
        $array = array($this->token,$this->timestamp,$this->nonce);
        //   sort()  SORT_STRING - 把每一项作为字符串来处理
        sort($array,SORT_STRING);  

        //implode 对array 的值使用空‘默认’进行拼接=字符串
        $sign = implode($array);

        //sha1() 函数计算字符串的 SHA-1 散列。  是一种加密方式
        $sign = sha1($sign);

        //对微信传送过来的signature 与 本地拼接的$sign 进行比较
        if($sign == $this->signature)
        {
            //验证通过
            //删除SHALApi.txt
            // Unlink('./api/SHALApi.txt');
            return true;
        }else{
            return false;
        }
   }

}
