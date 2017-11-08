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
		$map = [];
		$map['status'] = ['egt' , 0];
		$list = Db::name('config')->where($map)->order('create_time desc')->paginate(1);
		if($list)
		{
			$this->assign('_list' , $list);
		}
		return $this->fetch();
	}

	public function setConfigStatus()
	{
		return $this->setStatus(Db::name('config'));
	}
}
