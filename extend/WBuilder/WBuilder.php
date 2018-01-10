<?php
namespace WBuilder;

use app\common\controller\Config;
use Auth\Auth;
use think\Controller;


/**
 * 表单、表格构建器基类
 * Class WBuilder
 * @package WBuilder
 */
class WBuilder extends Controller
{

	// 模板置换变量
	protected static $vars = [];

	/**
	 * 构建器创建方法
	 * @param string $type table或者form，可以创建两种类型的构建器
	 * @return mixed
	 */
	public static function make($type = '')
	{
		if(!$type)
		{
			abort(500, '参数错误：未指定构建器名称');
		}
		$class = '\\WBuilder\\Builder\\'. strtolower($type) .'\\Builder';
		if (!class_exists($class)) {
			abort(500, '构建器类不存在');
		}
		return new $class;
	}

	/**
	 * 自定义变量合并与模板输出
	 * @param string $template 模板地址
	 * @param array $vars  模板输出
	 * @param array $replace 模板替换
	 * @param array $config 模板配置
	 * @return mixed
	 */
	public function fetch($template = '', $vars = [], $replace = [], $config = [])
	{
		$vars = array_merge($vars, self::$vars);
		return parent::fetch($template, $vars, $replace, $config);
	}
}