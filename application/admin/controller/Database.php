<?php
namespace app\admin\controller;

class Database extends Admin
{
	public function index()
	{
		return $this->fetch();
	}
}
