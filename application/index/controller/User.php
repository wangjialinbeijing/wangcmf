<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

/*
 * 用户模块
 */
class User extends Controller
{
	/**
	 * 用户登录
	 * @return mixed
	 */
	public function login()
	{
		if($this->request->isPost())
		{
			$mobile = input('mobile');
			$password = input('password');
			$userinfo = Db::name('user')->where(['mobile'=>$mobile,'password'=>sys_md5($password)])->find();
			if(!$mobile || !$password)
			{
				$this->assign('error_tips' , '请输入手机号或者密码');
			}
			elseif(!$userinfo)
			{
				$this->assign('error_tips' , '用户名或者密码错误');
			}
			else
			{
				session('USER_ID' , $userinfo['id']);
				session('USER_NAME' , $userinfo['name']);
				$this->redirect( 'Index/index');
			}
		}
		return $this->fetch();
	}

	/**
	 * 注销登录
	 */
	public function logout()
	{
		session('USER_ID' ,null);
		session('USER_NAME' ,null);
		$this->redirect('index/index');
	}

	/**
	 * 用户注册
	 */
	public function reg()
	{
		if($this->request->isPost())
		{
			$mobile = input('mobile');
			$username = input('name');
			$password = input('password');
			$repassword = input('repassword');
			$data = $this->request->param();
			if(!$username || !$mobile || !$password)
			{
				$this->assign('error_tips' , '请输入用户名、手机号或密码');
			}
			elseif($password != $repassword)
			{
				$this->assign('error_tips' , '两次密码不一致');
			}
			elseif(Db::name('user')->where(['mobile'=>$mobile])->find())
			{
				$this->assign('error_tips' , '手机号已经存在');
			}
			else
			{

				$data['status'] = 1;
				$data['create_time'] = time();
				$data['password'] = md5(md5($data['password']));
				unset($data['repassword']);
				$insertId = Db::name('user')->insert($data);
				if($insertId)
				{
					$this->redirect( 'User/login');
				}
			}

			$this->assign('data' , $data);
		}
		return $this->fetch();
	}
}
