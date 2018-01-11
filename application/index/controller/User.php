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
			// 接收参数
			$mobile = input('mobile');
			$password = input('password');
			// 查询用户手机号和密码是否正确
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
				// 用户信息写入会话
				session('USER_ID' , $userinfo['id']);
				session('USER_NAME' , $userinfo['name']);
				// 页面重定向到首页
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
		// 销毁会话登录信息
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
			// 获取用户提交的注册信息
			$mobile = input('mobile');
			$username = input('name');
			$password = input('password');
			$repassword = input('repassword');
			$data = $this->request->param();
			// 数据效验
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
				// 注册新用户
				$data['status'] = 1;
				$data['create_time'] = time();
				$data['password'] = sys_md5($data['password']);
				unset($data['repassword']);
				$insertId = Db::name('user')->insert($data);
				if($insertId)
				{
					// 注册成功后，自动跳转到登录界面
					$this->redirect( 'User/login');
				}
			}
			// 用户注册输入的信息，替换到注册表单中
			$this->assign('data' , $data);
		}
		return $this->fetch();
	}
}
