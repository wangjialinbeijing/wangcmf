<?php
namespace app\admin\controller;

use think\Controller;

class Admin extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();

        // 在会话中检测USER_ID
	    if(!defined('USER_ID'))
	    {
		    define('USER_ID',is_login());
	    }
	    // 还没登录 跳转到登录页面
	    if( !USER_ID ){
		    $this->redirect('public_user/login');
	    }


    }
}
