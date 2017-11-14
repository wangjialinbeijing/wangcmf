<?php
namespace app\admin\controller;

use Auth\Auth;

class Index extends Admin
{
    public function index()
    {
    	$auth = new Auth();
        return $this->fetch();
    }

	public function testerror()
	{
		$this->error(1);
	}
}
