<?php
/**
 * @Author: pizepei
 * @Date:   2018-04-10 11:01:36
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:22:18
 */
namespace common\console;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use common\menu\AdminMenu;
use think\Db;

/**
 * 测试示例
 * 进入tp项目根目录（think文件所在目录）
 * 执行
 * php think test --name [参数数据]
 *
 * 在这里可以使用tp的模型来、助手函数等
 */
class Test extends Command
{
    protected $name = null;

    /**
     * [configure 指令配置]
     * @Effect
     * @return [type] [description]
     */
    protected function configure()
    {
        // 具体的请在  think\console\Command 类中了解
        $this
            ->setName('test')//设置指令名称
            //        选项名称  别名    类型                 描述                默认值
            ->addOption('name', 'var', Option::VALUE_OPTIONAL, 'test parameter ', null)
            // 指令 设置描述
            ->setDescription('Clear runtime file');
    }
    /**
     * [initialize 初始化]
     * @Effect
     * @param  Input  $input  [依赖注入  think\console\Input]
     * @param  Output $output [依赖注入 think\console\Output]
     * @return [type]         [description]
     */
    protected function initialize(Input $input, Output $output)
    {
        //这里写业务逻辑
        $this->name = $input->getOption('name');//获取参数
        $this->funcTest();//业务方法
    }
    /**
     * [execute 执行方法]
     * @Effect
     * @param  Input  $input  [依赖注入  think\console\Input]
     * @param  Output $output [依赖注入 think\console\Output]
     * @return [type]         [description]
     */
    protected function execute(Input $input, Output $output)
    {
        //这里也可以写业务逻辑
        //目前 不知与initialize方法下有什么本质差异性
        $output->writeln('write data  ok');
    }
    /**
     * [funcTest 一个测试方法]
     * @Effect
     * @return [type] [description]
     */
    protected function funcTest()
    {
        echo 'I am a test method.';
        echo 'parameter   name='.$this->name;
    }

}