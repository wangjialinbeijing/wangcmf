<?php
namespace app\admin\controller;

use think\Db;
use think\Validate;

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

	// 新增配置
	public function add()
	{
		// 判断是否是POST
		if(request()->isPost())
		{
			// 验证开始
			$rule = [
				'name'  => 'unique:config|require|max:100',
				'value'   => 'require|max:1000',
			];

			$msg = [
				'name.require' => '配置名不能为空',
				'name.unique'     => '配置名已存在',
				'value.require'   => '配置值不能为空',
				'value.max'  => '值字符串长度过长',
			];
			$validate = new Validate($rule , $msg);
			$result   = $validate->check(input('post.'));
			if(!$result)
			{
				$this->error($validate->getError());
			}
			// 构造数据
			$data = input('post.');
			$data['status'] = 1;
			$data['create_time'] = time();
			// 数据入库
			$insertId = Db::name('config')->insert($data);
			if($insertId)
			{
				$this->success('操作成功',url('config/index'));
			}
			$this->error('数据库操作失败');
		}
		$this->assign('active_url' , 'Config/index');
		return $this->fetch();
	}

	public function setConfigStatus()
	{
		return $this->setStatus(Db::name('config'));
	}
}
