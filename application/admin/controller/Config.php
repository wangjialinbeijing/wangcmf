<?php
namespace app\admin\controller;

use think\Db;

class Config extends Admin
{
	public function group()
	{
		return $this->fetch('config/index');
	}

	// 配置列表
	public function index()
	{

		return $this->fetch();
	}

	public function del()
	{
		return $this->setStatus(Db::name('config'));
	}
}
