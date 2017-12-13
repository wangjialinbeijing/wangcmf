<?php
namespace app\admin\controller;

use think\Db;

class AuthManager extends Admin
{
	public function index()
	{
		return $this->fetch();
	}
}
