<?php
/**
 * @Author: pizepei
 * @Date:   2018-02-24 17:07:31
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-03-01 10:39:17
 */
namespace app\login\validate;
use think\Validate;
class User extends Validate
{
    // 验证规则
    protected $rule = [
        ['nickname|昵称', 'require|min:3|chsAlphaNum', '昵称必须|昵称不能短于5个字符|不能是特殊字符'],
        ['cellemail|邮箱', 'require|email', '邮箱格式错误'],
        ['repass|密码', 'require|confirm:repass', '两次密码输入不一致'],
        ['password|密码', 'require|min:8', '两次密码输入不一致|密码长度不足8位'],
    ];
    
}