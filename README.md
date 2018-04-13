# LayuiAdminProPHP
## LayuiAdminProPHP是针对LayuiAdminPro后台模板（以下简称LayAdmin）使用ThinkPHP5.0+开发的基础版本
## 此项目期望在基于ThinkPHP5.0+版本框架下编写一套（包含以下通用方案）的功能框架，以可以在大部分新目开始之初可拿来就用无需重复编写下方列出的功能模块提升开发效率。</br>
 + 1、账户登录、注册、密码找
 + 2、遵循 JWT（JSON Web Token）标准的鉴权方案（使用Redis），这里主要是针对单页面应用、APP后台
 + 3、LayAdmin菜单方案
 + 4、权限验证方案（完善中）
 + 5、数据缓存方案</br>
 + 6、WEB IM方案
 + 7、日志系统
 + 8、微信开发基础方案（包括、微信公众接入、自动回复、自定义菜单、微信登录、微信支付完善中  完善中）
 + 9、支付方案（包括、支付宝、微信  完善中）
 + 10、验证码方案（邮箱验证、手机验证  完善中）
 + 11、基础的系统监控、异步任务处理方案（完善中）等
 + 12、简单的shadowsocksr数据库版本的系统后台管理（用户、结点、套餐、账号监控）</br>
 + 目前就这些后续再进行完善</br>
 
等一个项目开始之初送需要基础功能方案。
## 如果你对LayAdmin、ThinkPHP5还不太了解请阅读下面的简单介绍：
 + 1、layuiAdmin后台模板介绍：
     + layuiAdmin后台模板为基于layui开发的单页面应用，所有操作无需跳转、采用前后端分离开发模式、更友好的交互体验，减轻浏览器负载、始终基于全新的 layui 版本、面向全屏幕尺寸的响应式适配能力、灵活的主题色配置。这里的基础版本目前只对单页面应用版本（layuiAdmin）进行适配<a href="http://www.layui.com/admin/">layuiAdmin官网</a>
 + 2、ThinkPHP5介绍：
     + ThinkPHP V5.0是一个为API开发而设计的高性能框架——是一个颠覆和重构版本，采用全新的架构思想，引入了很多的PHP新特性，优化了核心，减少了依赖，实现了真正的惰性加载，支持composer，并针对API开发做了大量的优化。 ThinkPHP5是一个全新的里程碑版本，包括路由、日志、异常、模型、数据库、模板引擎和验证等模块都已经重构，但绝对是新项目的首选（无论是WEB还是API开发）。<a href="http://www.thinkphp.cn/">ThinkPHP官网</a>
 ## 运行环境的简单介绍：
 + 1、推荐PHP7+版本+MySQL+Nginx
 + 2、功能模块中大量运用到Redis
 ## 目录结构简介：
 ~~~
www  WEB部署目录（或者子目录）
├─admin           系统后台应用目录
├─public          系统后台入口
│  ├─static        公共资源
│  │  ├─index      Layuiadmin 资源文件
├─extend          主要功能类目录
│  ├─console            think 命令行
│  │  ├─test.php        think 指令类示例
│  ├─custom             获取公共信息
│  │  ├─TerminalInfo.php      获取web客户端信息类（包括ip信息）
│  ├─heillog        日志系列
│  │  ├─ErrorLog.php    系统日志写入类
│  │  ├─SsrUserLog.php   SSR操作用户记录日志写入类
│  ├─helper         自定义助手函数系列
│  │  ├─helper.php         自定义助手函数
│  ├─menu        菜单系列
│  │  ├─AdminMenu.php    系统后台菜单
│  │  ├─AppMenu.php      用户前台菜单
│  ├─redis        redis系列
│  │  ├─RedisLogin.php    redis登录类
│  │  ├─RedisModel.php    redis基类
│  ├─Safety        密码、验证、JWT系列、数据加密等相关
│  │  ├─Safetylogin.php   密码、验证、JWT系列、数据加密等相关
│  ├─SendMail        阿里云邮件类
│  │  ├─EmailLog.php    邮件日志类
│  │  ├─SendMail.php    初始化邮件发送类
│  │  ├─Mail.php        邮件发送方法类
│  ├─VerifiController        用以被控制器继承的权限验证、登录验证、基本信息获取
│  │  ├─AdminLoginVerifi.php    系统后台
│  │  ├─UserLoginVerifi.php     用户前台
├─table.sql        表结构(用户表中的用户需要自己添加)
├─config.js        LayAdmin的配置
注意：这里在\thinkphp\library\think\log\driver目录下增加MysqFile.php驱动类使用MySQL记录系统错误日志（可在应用的config.php中修改）
~~~
 ## 文档：
 + [更新日志](https://github.com/pizepei/LayuiAdminProPHP/wiki/%E6%9B%B4%E6%96%B0%E6%97%A5%E5%BF%97)  
 + [演示地址](http://demo.heil.red/) ，登录用户：test 密码：p123456

 ## 配置简介：
 + 应用的配置都在应用目录下的config.php中，具体的直接打开config.php文件就可以了解。
 + LayAdmin的配置在根目录下config.js中
 ## 注意：
 + 由于LayuiAdmin需要授权故不在此上次代码[授权地址](http://www.layui.com/admin/)  
 + 由于使用了ThinkPHP5.0+框架请先了解熟悉ThinkPHP的使用[手册地址](https://www.kancloud.cn/manual/thinkphp5/118003)  
 + 使用项目代码的前提是你已经了解 ThinkPHP5与LayuiAdmin的使用。
 ### 下载使用ThinkPHP5.0+框架：
+ 1、本项目中不包含 ThinkPHP 与LayuiAdmin 的文件：
  + 使用安装 ThinkPHP
     ~~~
      使用  composer 安装 ThinkPHP
     composer create-project topthink/think=5.0.* tp5  --prefer-dist
     使用  git 安装 ThinkPHP
     https://github.com/top-think/think
     ~~~
+ 2、获取LayuiAdmin授权[授权地址](http://www.layui.com/admin/)  
 ## 思路介绍：
 #### 热数据的缓存：
+ 项目使用Redis缓存大部分的重复调要数据如：JWT（JSON Web Token）标准的鉴权方案需要的数据、登录保存的用户基本数据、菜单数据、权限数据等。
 #### JWT鉴权处理：
+ 登录控制有两种方案Redis+mysql、纯redis 前者考虑到使用纯redis方案是利用redis的kye过期自动删除 不会保存登录数据（我自己找的理由）使用mysql保存基本的登录数据，纯redis方案是把所有的JWT（JSON Web Token）数据保存在redis中然后利用redis的kye过期自动删除功能控制登录的有效性（如何使用鉴权下面详细介绍）。
 #### 应用配置config：
 + 考虑到部分项目可能会吧前（用户）后台（系统）放在一个项目中（以不同应用的形式存在）使用同一个redis数据库，就在配置文件（应用目录下config.php）中对配置进行了区分（下面会详细说明）。
 #### RBAC、菜单权限、权限的继承：
  + 菜单权限与方法权限（控制器方法）共同使用一个用户组系统。
  + 除index首页（宿主页面）的渲染、登录注册、退出登录、对外开放API（微信）等控制器外的所有控制器请继承extend\VerifiController\AdminLoginVerifi.php 类由该类进行权限控制。
 #### 编写的规范：
   + 所有业务逻辑与数据操作都放在模型（M）中，控制器（C）中不再有任何业务逻辑也不要在控制器中进行db操作，控制器只做请求的转发与权限控制。
  API数据规范：控制器中所有的API数据（针对LayuiAdmin的）统一使用Result（）助手函数（helper.php）。
 #### 日志处理：
   + 这里在\thinkphp\library\think\log\driver目录下增加MysqFile.php驱动类使用MySQL记录系统错误日志（如果不是要可在应用的config.php中修改）MysqFile.php只是对tp原有的驱动类进行了简单的修改以达到使用mysql记录系统基本的错误（使用extend\heillog\ErrorLog.php类写入）。
   + 建议尽可能的对用户、管理员的所有操作进行记录。
 #### 常用方法：
  + 控制器中所有的API数据（针对LayuiAdmin的）统一使用Result（）助手函数（helper.php）。
  + app\login\model\MainUser 类的 MainUser::setUserData($id,$type=false)，当type=false时只获取用户的数据，为true时获取并且更新Redis中的用户数据（排除了密码数据）。
 #### 日志处理：
#### think 命令行工具：
+ 1、需要在下面根目录下的think 文件 中配置
~~~
// 定义项目路径
define('APP_PATH', './admin/');
// 加载框架命令行引导文件
require './thinkphp/console.php';
~~~
+ 2、在应用根目录下的command.php文件中配置指令类的引用地址建议统一放在\extend\console目录下(不需要.php)
~~~
// 定义项目路径
return [
    '\extend\console\test',
];
~~~
+ 3、可在\extend\console目录下增加创建一个指令类test.php
+ 4、think指令可配合Linux 的crontab 做计划任务（比如再配合Redis 队列 异步处理订单、支付状态等）提高系统的高可用性。
+ 目前这里只有一个\extend\console目录一个test.php指令类做简单示例。   
   
 ## 开始构建：
 + 1、安装好ThinkPHP5.0。
 + 2、获取LayuiAdmin授权并且选择代码，复制黏贴到public\static\index目录下 
 + 3、使用本项目代码在根目录进行粘贴替换。
 + 4、根据自己项目的需求对应用目录下config.php进行配置修改。

 
