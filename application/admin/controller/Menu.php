<?php
namespace app\admin\controller;

use Menu\Tree;
use think\Db;
use think\Validate;

class Menu extends Admin
{

	// 菜单列表
	public function index()
	{
		// 查询条件
		$map = [];
		$map['status'] = ['egt' , 0];
		$map['pid'] = 0;
		$pid = input('pid' , 0);
		if($pid)
		{
			$map['pid'] = $pid;
		}
		$list = Db::name('menu')->where($map)->order('sort asc')->paginate(10);
		if($list)
		{
			$this->assign('_list' , $list);
		}
		$this->assign('pid' , $pid);
		return $this->fetch();
	}

	// 新增菜单
	public function add()
	{
		// 判断是否是POST
		if(request()->isPost())
		{
			//Todo::数据验证/数据保存/信息提示
		}

		$menus = Db::name('Menu')->select();
		$treeObj = new Tree();
		$menus = $treeObj->toFormatTree($menus);
		$menus = array_merge([['id'=>0,'title_show'=>'顶级菜单']], $menus);
		$this->assign('menus', $menus);

		$this->assign('active_url' , 'Menu/index');
		return $this->fetch();
	}

	/**
	 * 表记录状态迁移
	 */
	public function setConfigStatus()
	{
		return $this->setStatus(Db::name('config'));
	}
}
