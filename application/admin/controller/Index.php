<?php
namespace app\admin\controller;

class Index extends Admin
{
    public function index()
    {
        return $this->fetch();
    }

	public function testerror()
	{
		$this->error(1);
	}
}
