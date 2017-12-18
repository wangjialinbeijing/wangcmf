<?php
namespace app\admin\controller;

use WBuilder\WBuilder;

class Index extends Admin
{
	// 测试基础布局文件
    public function index()
    {
        return $this->fetch();
    }

	public function testerror()
	{
		$this->error(1);
	}

	// 测试构建器
	public function tb()
	{
		WBuilder::make('table');
	}
}
