<?php
/**
 * @Author: pizepei
 * @Date:   2018-03-29 15:15:33
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-20 17:42:50
 */
    /**
     * [Mt_str 生成随机字符串]
     * @Effect
     * @param  [type]  $vel  [类型] 1、全部大写字母 2、混合 大小写字母+数字 3、混合 大小写字母+数字+特殊字符
     * @param  integer $type [数量]
     */
    function  Mt_str($vel,$type=1)
    {

        //生成随机字符串
        if($type == 2){
            $str = 'Q2Ww3E4Rr5aTd6Yef7U8gI9Oxczcghh0hP3Aj4S5D6kF7Gtsdj8Hy9J2u3K4L5kZ6Xl72CsV3BNM';
        }else if($type == 1){
            $str = 'QWERTYUIOPASDFGHJKLZXCVBNMQWERTYUIOPASDFGHJKLZXCVBNMQWERTYUIOPASDFGHJKLZXCVB';
        }else if($type == 3){
            $str = 'QWERT~YUaTd#6Yef7U8@gI9czc*ghh0N$MQRTYUI@OPADFGH%JKdj8^Hy9J2u3K&4LkZ6DF*GHJK';

        }else if($type == 4){
            $str = 'QWE1RTY4UIOP2ASDFG3H2JKL3Z3XCVBN67MQWERT1YU5IO4P7A0SDFG1HJKL7ZX7CVB8NMQW0ERTYUIOPA4SDFGHJ56KLZXC71VB';

        }
        $mt_str = '';
        $strlen = strlen($str)-1;

        for ($i=0; $i <$vel ; $i++) { 
            $mt_str .= $str{mt_rand(0,$strlen)};
        }
        return $mt_str;
    } 


    //判断是否是移动客户端 移动设备
    function isMobile() { 
      // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
      if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
      } 
      // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
      if (isset($_SERVER['HTTP_VIA'])) { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
      } 
      // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
      if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
          return true;
        } 
      } 
      // 协议法，因为有可能不准确，放到最后判断
      if (isset ($_SERVER['HTTP_ACCEPT'])) { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
          return true;
        } 
      } 
      return false;
    }

    // 判断是否是微信内置浏览器
    function isWeixin() { 
      if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) { 
        return true; 
      } else {
        return false; 
      }
    }

    //微信相关的  curl 函数
    function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    
    /**
     * [Mdate 快捷获取格式化时间]
     * @Effect
     * @param  string $time [description]
     */
    function Mdate($time=''){

      if($time==''){
        $time = time();
      }
      return date('Y-m-d H:i:s',$time);
    }

    /**
     * [isTimeFormat 判断是否是标准的时间格式]
     * @Effect
     * @param  [type]  $str [description]
     * @return boolean      [description]
     */
    function isTimeFormat($str){
      
        if(strtotime($str)){
            return date('Y-m-d',strtotime($str));
        }
        return strtotime($str) !== false;
    }
    /**
     * [Result 判断并且返回结果给前端]
     * @Effect
     * @param  [type]  $Data     [数据 开bool，arr，nulll  ]
     * @param  string  $Msg      [业务提示信息]
     * @param  integer $HttpCode [http状态码]
     * @param  boolean $over     [是否直接结束程序]
     */
    function Result($Data,$Msg='',$HttpCode=200,$Over=false){

        //判断是否是数组
        if(is_array($Data)){
            $D = true;

            if(!empty($Data['count'])){
                $Result['count'] = $Data['count'];
                unset($Data['count']);
                $D = false;
            }
            //没有 data 
            if(!empty($Data['msg'])){
                $Result['msg'] = $Data['msg'];
                unset($Data['msg']);
                $D = false;
            }
            if(isset($Data['code'])){
                $Result['code'] = $Data['code'];
                unset($Data['code']);
                $D = false;
            }
            // access_token
            if(!empty($Data['access_token'])){
                $Result['data']['access_token'] = $Data['access_token'];
                unset($Data['access_token']);
                $D = false;
            }

            //如果存入了data
            if(empty($Data['data'])){

                if($D){
                    //如果没有   code   access_token  count 数据 但是还有其他数据（默认成功状态）
                    //这里注意：如果 没有$Data['data'] 默认为直接返回数据不带 其他数据
                    //要要返回的数据，同时还要定义状态的  就必须吧数据放在$Data['data']中
                    //
                    if(!empty($Data)){
                        $Result['code'] = 0;
                        $Result['msg'] = '成功';
                        $Result['data'] = $Data;
                    }
                }
            }else{
                $Result['code'] = 0;
                $Result['msg'] = '成功';
                //有$Data['data']
                $Result['data'] = $Data['data'];
            }
        }else{

            if($Data){
                $Result['code'] = 0;
                $Result['msg'] = '操作成功';
            }else{
                $Result['code'] = 1;
                $Result['msg'] = '操作失败';
            }

        }

        if($Msg != ''){
            $Result['msg'] = $Msg;
        }
        if(!isset($Result)){
            $Result = '';
        }
        (int)$HttpCode;
        
        if($Over == true){
          die(json_encode($Result));
        }
        return json($Result,$HttpCode);
    }


