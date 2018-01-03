<?php
namespace app\admin\controller;
use think\Db;
use app\admin\validate\User as UserValidate;

/**
 * 用户控制器类
 * Class User
 * @package app\admin\controller
 */
class User extends Admin
{
	/**
	 * 用户列表
	 * @return mixed
	 */
	public function index()
	{
		$this->assign('_list' , Db::name('user')->where(['status'=>['egt' ,0 ]])->order('create_time desc')->paginate($this->page));
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
			$validate = new UserValidate();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			unset($data['password_confirm']);
			// 数据写入
			$data['status'] = 1;
			$data['password'] = sys_md5($data['password']);
			$data['create_time'] = time();
			$insertId = Db::name('user')->insert($data);
			if($insertId !== false)
			{
				$this->success('保存成功' , url('user/index'));
			}
			$this->error('数据库操作失败');
		}

		$this->assign('active_url' , 'User/index');
		return $this->fetch();
	}

}