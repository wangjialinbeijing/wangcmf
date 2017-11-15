<?php
namespace app\admin\controller;
use think\Db;

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
}