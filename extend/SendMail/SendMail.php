<?php  
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:45:19
 */
    use think\Loader;
    use SendMail\EmailLog;
    use Dm\Request\V20151123 as Dm; 

    class  SendMail{
        public $sender;//发信人昵称
        public $recipient;//目标地址
        public $title;//邮件主题
        public $content;//邮件正文
        public $LogData;//日志数据

        //构造函数
        public function __construct($sender,$recipient,$title,$content,$type) {
            //初始化成员属性
            $this->sender = $sender;
            $this->recipient = $recipient;            
            $this->title = $title;
            $this->content = $content;
            $this->type = $type;
            $this->LogData = $Data = [
                'sender'=>$this->sender,
                'title' => $this->title,
                'receive_email'=>$this->recipient,
                'send_email'=>'ssr@googlevps.top',
                'info'=>$this->content,
                'sender'=>$this->sender,
                'type'=>$this->type,
                ];
            Loader::import('SendMail.aliyun-php-sdk-core.Config');
            Loader::import('SendMail.aliyun-php-sdk-core.Profile.DefaultProfile');
            // $this->Mail();
        }

        public function Mail(){

            $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", config('SendMail.accessKey'), config('SendMail.accessSecret'));        
            $client = new DefaultAcsClient($iClientProfile);    
            $request = new Dm\SingleSendMailRequest();     
            $request->setAccountName("ssr@googlevps.top");
            $request->setFromAlias($this->sender);
            $request->setAddressType(1);
            $request->setTagName("ssr");
            $request->setReplyToAddress("true");
            $request->setToAddress($this->recipient);        
            $request->setSubject($this->title);
            $request->setHtmlBody($this->content);

            // $request->setAccountName("控制台创建的发信地址");
            // $request->setFromAlias("发信人昵称");
            // $request->setAddressType(1);
            // $request->setTagName("控制台创建的标签");
            // $request->setReplyToAddress("true");
            // $request->setToAddress("目标地址");        
            // $request->setSubject("邮件主题");
            // $request->setHtmlBody("邮件正文");       
            try {
                $response = $client->getAcsResponse($request);
                EmailLog::saveLog($this->LogData);//日志
                return true;
                // print_r($response);
            }
            catch (ClientException  $e) {
                $this->LogData['error'] = $e->getErrorCode().$e->getErrorMessage();
                EmailLog::saveLog($this->LogData);//日志
                // print_r($e->getErrorCode());   
                // print_r($e->getErrorCode());   
            }
            catch (ServerException  $e) { 
                $this->LogData['error'] = $e->getErrorCode().$e->getErrorMessage();
                EmailLog::saveLog($this->LogData);//日志
                // print_r($e->getErrorCode());   
                // print_r($e->getErrorMessage());
            }

        }

    }

?>