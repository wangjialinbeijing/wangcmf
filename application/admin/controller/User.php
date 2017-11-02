<?php
/**
 * Created by PhpStorm.
 * User: qiQAQqi
 * Date: 2017/11/2 0002
 * Time: 14:49
 */

namespace app\admin\controller;


use think\Controller;

class User extends Admin
{
	public function index()
	{
		echo time();
	}
}