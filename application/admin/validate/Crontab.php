<?php
namespace  app\admin\validate;

use think\Validate;

class Crontab extends Validate
{

	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		'title'  => 'unique:crontab|require|max:500',
		'type'  => 'require',
		'content'   => 'require|max:1000',
		'maximums'   => 'number',
		'sleep'   => 'number',
		'begin_time'   => 'require|date',
		'end_time'   => 'require|date',
	];

	/**
	 * 提示信息
	 * @var array
	 */
	protected $message = [
		'title.require' => '任务名不能为空',
		'title.unique'     => '任务名已存在',
		'type.require'   => '任务类型不能为空',
		'content.require'  => '任务执行内容不能为空',
		'content.max'  => '任务执行内容过长',
		'maximums.number'  => '最大执行次数格式错误',
		'sleep.number'  => '延迟秒数次数格式错误',
		'begin_time.require' => '开始时间不能为空',
		'end_time.require' => '结束时间不能为空',
		'begin_time.date' => '开始时间格式错误',
		'end_time.date' => '结束时间格式错误',
	];
}