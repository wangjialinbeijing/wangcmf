<?php
namespace app\admin\controller;

use Menu\Tree;
use think\Db;
use think\Validate;
use app\admin\validate\Menu as MenuValidate;

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
			// 数据获取
			$data = input('post.');
			// 数据验证
			$validate = new MenuValidate();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			// 数据写入
			$data['status'] = 1;
			$insertId = Db::name('menu')->insert($data);
			if($insertId !== false)
			{
				// 更新会话中的菜单信息
				session('ADMIN_MENU_LIST' , NULL);
				$this->success('保存成功');
			}
			$this->error('数据库操作失败');
		}

		// 显示已有菜单树形结构
		$this->assign('menus', $this->getMenuTreeList());
		$this->assign('active_url' , 'Menu/index');
		return $this->fetch();
	}

	// 编辑菜单
	public function edit()
	{
		$id = input('id' ,0);
		if(!$id)
		{
			$this->error('参数错误');
		}
		$menu_data = Db::name('menu')->where(['id'=>$id])->find();
		if($menu_data === null)
		{
			$this->error('数据查询为空');
		}
		$this->assign('info' , $menu_data);

		// 判断是否是POST
		if(request()->isPost())
		{
			// 数据获取
			$data = input('post.');
			// 数据验证
			$validate = new MenuValidate();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			// 数据更新
			$map['id'] = $data['id'];
			unset($data['id']);
			$return = Db::name('menu')->where($map)->update($data);
			if($return !== false)
			{
				// 更新会话中的菜单信息
				session('ADMIN_MENU_LIST' , NULL);
				$this->success('保存成功');
			}
			$this->error('数据库操作失败');
		}

		// 显示已有菜单树形结构
		$this->assign('menus', $this->getMenuTreeList());
		$this->assign('active_url' , 'Menu/index');
		return $this->fetch();
	}

	/**
	 * 获取菜单树形结构
	 */
	private function getMenuTreeList()
	{
		$menus = Db::name('Menu')->select();
		$treeObj = new Tree();
		$menus = $treeObj->toFormatTree($menus);
		$menus = array_merge([['id'=>0,'title_show'=>'顶级菜单']], $menus);
		return $menus;
	}

	/**
	 * 禁用和启用状态迁移
	 */
	public function setMenuStatus()
	{
		// 会话菜单数据初始化
		session('ADMIN_MENU_LIST' , NULL);
		return $this->setStatus(Db::name('menu'));
	}

	/**
	 * 删除菜单
	 */
	public function del()
	{
		if(request()->isAjax())
		{
			$id = input('id' , 0);
			if(!$id)
			{
				$this->error('删除失败，参数错误');
			}
			$map['id'] = $id;
			$return = Db::name('menu')->where($map)->delete();
			if(!$return)
			{
				$this->error('数据库操作失败！');
			}
			// 会话菜单信息初始化
			session('ADMIN_MENU_LIST' , NULL);
			$this->success('删除成功！');
		}
		$this->error('数据异常');
	}
}
