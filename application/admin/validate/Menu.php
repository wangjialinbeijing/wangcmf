<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 菜单操作验证类
 * Class Menu
 * @package app\admin\validate
 */
class Menu extends Validate
{
	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		'title' => 'require|max:100',
		'url'   => 'require|max:1000',
		'group' => 'max:1000',
		'sort'  => 'number'
	];

	protected $message = [
		'title.require' => '菜单名不能为空',
		'url.require'   => 'URL地址不能为空',
		'group.max'     => '分组长度过长',
		'sort.number'   => '排序字段格式为数字'
	];
}