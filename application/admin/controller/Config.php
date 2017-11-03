<?php
namespace app\admin\controller;

class Config extends Admin
{
	public function group()
	{
		return $this->fetch('config/index');
	}
}
