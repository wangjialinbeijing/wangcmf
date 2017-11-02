<?php
use think\Db;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('is_login'))
{
	// 检测用户是否已经登录
	function is_login(){
		$user = session('user_auth');
		if (empty($user)) {
			return 0;
		} else {
			// 检测当前登录用户
			return session('user_auth_sign') == data_auth_sign($user) ? $user['user_id'] : 0;
		}
		return $user;
	}
}

if(!function_exists('sys_md5'))
{
	// 系统加密
	function sys_md5($string)
	{
		return md5(md5($string));
	}
}

if(!function_exists('data_auth_sign'))
{
	// 生成签名
	function data_auth_sign($data)
	{
		//数据类型检测
		if (!is_array($data)) {
			$data = (array)$data;
		}
		ksort($data); //排序
		$code = http_build_query($data); //url编码并生成query字符串
		$sign = sha1($code); //生成签名
		return $sign;
	}
}


