<?php
namespace app\admin\controller;

use Auth\Auth;
use WBuilder\WBuilder;

class Index extends Admin
{
    public function index()
    {
    	$auth = new Auth();
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
