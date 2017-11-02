<?php
namespace app\admin\controller;

use app\api\controller\News;
use app\common\controller\Config;
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
        // 读取缓存中的配置
	    $config =   cache('DB_CONFIG_DATA');
	    if(!$config)
	    {
	    	// 在数据库中读取所有的配置信息
	    	$config = Config::lists();
	    	cache('DB_CONFIG_DATA' , $config);
	    }
		// 数据库配置合并到系统配置文件
	    config($config);


    }
}
