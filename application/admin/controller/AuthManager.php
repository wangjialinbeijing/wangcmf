<?php
namespace app\admin\controller;

use think\Db;

class AuthManager extends Admin
{
	public function index()
	{
		return $this->fetch();
	}

	/**
	 * 分组节点管理
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function groupmanage()
	{
		$group_id = input('group_id');
		if(!$group_id)
		{
			$this->error('未查询到权限分组');
		}
		$map['status'] = ['egt' ,0];
		$map['module'] = 'admin';
		$map['type'] = 1;
		if($group_id)
		{
			$map['id'] = $group_id;
		}
		$auth_group = Db::name('AuthGroup')
			->where( $map )
			->field('id,id,title,rules')
			->find();


		$this->assign('node_list' ,$this->returnMenuNodes());
		$this->assign('auth_group_nodes' , explode(',' , $auth_group['rules']));
		return $this->fetch();
	}
}
