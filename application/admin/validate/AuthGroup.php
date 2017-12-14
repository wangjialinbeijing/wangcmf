<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 权限分组验证类
 * Class AuthGroup
 * @package app\admin\validate
 */
class AuthGroup extends Validate
{
	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		'title' => 'require|unique:auth_group|max:100',
		'description' => 'max:500'
	];

	protected $message = [
		'title.require' => '分组名不能为空',
		'title.unique' => '分组名不能重复',
		'description.max'   => '描述内容过多'
	];
}