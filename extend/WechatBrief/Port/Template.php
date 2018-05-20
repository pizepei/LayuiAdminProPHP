<?php
/**
 * @Author: pizepei
 * @Date:   2017-06-03 14:39:36
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-12 23:43:13
 */
namespace WechatBrief\Port;
use WechatBrief\func;
/**
 * 微信  模板通知
 */
class Template{

     protected $openid = '';//接受者id

     protected $template_id = '';//模板id

     protected $url = '';//url

     protected $access_token = '';//access_token

     protected $data ='';//数据

     protected $template_data = '';//模板-模型-数据

     protected $template_model = '';//最后的数据
 
     protected $Add_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';//请求地址
     /**
      * [__construct 构造函数，获取Access Token]
      * @Effect
      * @param  [type] $openid      [接受者id]
      * @param  [type] $template_id [模板id]
      * @param  string $url         [url]
      */
     public function __construct( $openid,$template_id,$url = '')

     {      //获取AccessToken
            $AccessToken  = new \WechatBrief\Port\AccessToken();
            $this->access_token = $AccessToken->access_token();
            //初始化  参数
            $this->url = $url;
            $this->template_id = $template_id;
            $this->openid = $openid;
     }

    /**
     * [send 发送模板]
     * @Effect
     * @param  [type] $data [需要发送的模板数据]
     * @return [type]       [description]
     */
    public function send($data){

        $this->data = $data;
        //通过模板id  获取模板模型
        $this->ModelData();
        //向模板模型中插入需要发送的数据
        $this->model();
        //准备  url
        $Add_url = $this->Add_url.$this->access_token;
        //curl  请求
        $res = func::http_request($Add_url, $this->template_model);
        //返回结果
        return json_decode($res, true);
     }

    public function Model(){

        //判断  是否有模板
        if(!$this->template_data){
            return false;
        }

        $i = 0;
        //初始化模板
        foreach ($this->template_data as $key => $value) {
            $this->template_data[$key] = $this->data[$i];
            ++$i;
        }

        //处理数据
        $template_model = array(
                            'touser'=>$this->openid,
                            'template_id'=>$this->template_id,
                            'url'=>$this->url,
                            'data'=>$this->template_data
                            );
        $this->template_model = json_encode($template_model);
        return true;
    } 
    //模板数据模型
    public function ModelData(){

        $DataArr = array(
            //有新用户 下单通知
            'YuVBNwseLM4avDTJQR-XPEm1iIU9e3ckzX5M_OIRyZQ'=>array(
                    'first'        => array(
                                            'value' => "注意了——有用户支付成功\n", 
                                            'color' => "#000000"
                                     ),

                    'keyword1'     => array(
                                                'value' => 'data',
                                                'color' => '#000093'
                                                ),

                    'keyword2'     => array(
                                                'value' => 'data', 
                                                'color' => '#000093'
                                            ),

                    'keyword3'     => array(
                                                'value' => 'data',
                                                'color' => '#000093',
                                               ),
                    'keyword4'     => array(
                                                'value' => 'data',
                                                'color' => "#000093",
                                               ),   

                    'keyword5'     => array(
                                                'value' => 'data',
                                                'color' => '#000093',
                                               ),   

                    'remark'     => array(
                                                'value' =>'data',
                                                'color' => '#000093',
                                               ),  

            ),
            //购买成功通知
            'Hb-TauRMVVjmocaCuxffy3IMUwK6YhoVqKgiYxqtQo8'=>array(
                    'productType'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),
                    'name'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),
                    'number'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),

                    'expDate'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),
                    'remark'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),                    
                ),

            //示例
            'id'=>array(
                    'first'        => array(
                                            'value' => "", 
                                            'color' => "#000000"
                                     ),                
                ),

            );
        
        $this->template_data = $DataArr[$this->template_id];//获取模板

    }


 }

