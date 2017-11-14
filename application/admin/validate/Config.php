<?php
namespace  app\admin\validate;

use think\Validate;

class Config extends Validate
{

    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'name'  => 'unique:config|require|max:100',
        'value'   => 'require|max:1000',
    ];

    protected $message = [
        'name.require' => '配置名不能为空',
        'name.unique'     => '配置名已存在',
        'value.require'   => '配置值不能为空',
        'value.max'  => '值字符串长度过长',
    ];
}