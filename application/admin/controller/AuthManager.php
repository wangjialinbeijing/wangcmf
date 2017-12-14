<?php
namespace app\admin\controller;

use app\admin\validate\AuthGroup;
use think\Db;

class AuthManager extends Admin
{
	/**
	 * 权限分组列表
	 * @return mixed
	 * @throws \think\exception\DbException
	 */
	public function index()
	{
		$map['status'] = ['egt' ,0];
		$group_list = Db::name('AuthGroup')->where( $map )->paginate(10);
		$this->assign('_list' , $group_list);
		return $this->fetch();
	}

	/**
	 * 新增分组页面
	 */
	public function add()
	{
		if($this->request->isPost())
		{
			// 数据获取
			$data = input('post.');
			// 数据验证
			$validate = new AuthGroup();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			// 数据写入
			$data['status'] = 1;
			$data['module'] = 'admin';
			$data['type'] = 1;
			$insertId = Db::name('auth_group')->insert($data);
			if($insertId !== false)
			{
				$this->success('保存成功',url('index'));
			}
			$this->error('数据库操作失败');
		}
		$this->assign('active_url' , 'AuthManager/index');
		return $this->fetch();
	}

	/**
	 * 编辑分组页面
	 */
	public function edit()
	{
		$id = input('id');
		if(!$id)
		{
			$this->error('参数错误！');
		}
		$this->assign('info' , Db::name('auth_group')->where(['id'=>$id])->find());
		if($this->request->isPost())
		{
			// 数据获取
			$data = input('post.');
			// 数据验证
			$validate = new AuthGroup();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			$map['id'] = $data['id'];
			// 数据写入
			$insertId = Db::name('auth_group')->where( $map )->update($data);
			if($insertId !== false)
			{
				$this->success('保存成功',url('index'));
			}
			$this->error('数据库操作失败');
		}
		$this->assign('active_url' , 'AuthManager/index');
		return $this->fetch();
	}

	// 用户授权
	public function user()
	{
		if($this->request->isPost())
		{
			$user_id = input('user_id');
			$group_id = input('group_id');
			if( empty($user_id) ){
				$this->error('参数有误');
			}
			if(is_numeric($user_id)){
				if (is_admin($user_id)) {
					$this->error('该用户为超级管理员');
				}
				if( !Db::name('user')->where(['id'=>$user_id])->find() ){
					$this->error('用户不存在');
				}
			}

			if($group_id)
			{
				$group_info = Db::name('auth_group')->where(['id'=>$group_id])->find();
				if(!$group_info)
				{
					$this->error('分组信息查询错误');
				}
				$data = [];
				$data['user_id'] = $user_id;
				$data['group_id'] = $group_id;
				$access_info = Db::name('auth_group_access')->where($data)->find();
				if($access_info)
				{
					$this->error('请勿重复添加');
				}
				$insertId = Db::name('auth_group_access')->insert($data);
				if($insertId !== false)
				{
					$this->success('添加成功');
				}
			}
		}
		$this->assign('active_url' , 'AuthManager/index');
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
		// 获取分组ID
		$group_id = input('id');
		if(!$group_id)
		{
			$this->error('未查询到权限分组');
		}
		// 查询分组信息
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
		// 节点列表
		$this->assign('node_list' ,$this->returnMenuNodes());
		// 当前分组权限节点列表
		$this->assign('auth_group_nodes' , explode(',' , $auth_group['rules']));
		// 菜单高亮
		$this->assign('active_url' , 'AuthManager/index');
		return $this->fetch();
	}

	/**
	 * 表记录状态迁移
	 */
	public function setGroupStatus()
	{
		return $this->setStatus(Db::name('auth_group'));
	}
}
