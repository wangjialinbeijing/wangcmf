<?php
namespace app\admin\controller;

class Index extends Admin
{
    public function index()
    {
    	dump($_SESSION);
        return $this->fetch();
    }

	public function testerror()
	{
		$this->error(1);
	}
}
