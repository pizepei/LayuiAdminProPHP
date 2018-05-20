<?php
/**
 * @Author: pizepei
 * @Date:   2018-05-14 22:29:25
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-05-14 23:47:55
 */
namespace WechatBrief\Module;
use common\Model as M;
use think\Cache;
/**
 * 微信关键字模型
 */
class WechatKeyword extends M {
    protected $resultSetType = 'collection';
    protected $table = 'wechat_keyword';


}