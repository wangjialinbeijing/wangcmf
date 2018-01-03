<?php
namespace  app\admin\validate;

use think\Validate;

class User extends Validate
{

	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		'name'  => 'require|max:100',
		'mobile'   => 'unique:user|require|regex:\d{11}',
		'password'=>'require|confirm|regex:[a-zA-Z0-9]{6,18}'
	];

	/**
	 * 提示信息
	 * @var array
	 */
	protected $message = [
		'name.require' => '用户名名不能为空',
		'type.require'   => '用户类型不能为空',
		'mobile.require'   => '手机号码不能为空',
		'mobile.unique'   => '手机号码已经存在',
		'mobile.regex'   => '手机号码格式错误',
		'password.regex'  => '密码为6-18位数字英文大小写',
		'password.require'  => '密码不能为空',
		'password.confirm'  => '两次密码输入不一致',
	];
}