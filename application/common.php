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
		// 获取会话中的用户信息
		$user = session('user_auth');
		// 若不存在，则返回0
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
	// 系统密码加密方法
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

if(!function_exists('list_to_tree'))
{
	/*
	 * 数组转换为Tree
	 */
	function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
	{
		// 创建Tree
		$tree = [];
		if (is_array($list)) {
			// 创建基于主键的数组引用
			$refer = [];
			foreach ($list as $key => $data) {
				$refer[ $data[ $pk ] ] =& $list[ $key ];
			}
			foreach ($list as $key => $data) {
				// 判断是否存在parent
				$parentId = $data[ $pid ];
				if ($root == $parentId) {
					$tree[] =& $list[ $key ];
				} else {
					if (isset($refer[ $parentId ])) {
						$parent =& $refer[ $parentId ];
						$parent[ $child ][] =& $list[ $key ];
					}
				}
			}
		}
		return $tree;
	}
}

if(!function_exists('show_menu_active'))
{
	/**
	 * 菜单选中效果
	 * @param $sub_menu 所有菜单项
	 * @param $active_url 当前选中的url
	 * @return bool
	 */
	function show_menu_active($sub_menu, $active_url)
	{
		$active = false; // 是否选中
		if (!empty($sub_menu) && $active_url) {
			foreach ($sub_menu as $key => $val) {
				if ($active_url == $val['url']) {
					$active = true;
					break;
				}
			}
		}
		return $active;
	}
}

if(!function_exists('is_admin'))
{
	/**
	 * 判断当前用户是否是超级管理员
	 * @param null $uid
	 * @return bool
	 */
	function is_admin($uid = null)
	{
		$uid = is_null($uid) ? is_login() : $uid;
		return $uid && (intval($uid) === intval(config('USER_ADMIN')));
	}
}

/**
 * 返回格式化日期
 * @param $unix_time
 * @return false|string
 */
function time_format($unix_time , $format = 'Y-m-d H:i')
{
	if($unix_time == 0)
	{
		return '--';
	}
	return date($format , $unix_time);
}

/**
 * 获取状态信息
 * @param $status
 * @return string
 */
function get_status_info($status)
{
	$str = '';
	switch(intval($status))
	{
		case -1 :
			$str = '<span class="label label-danger">已删除</span>';
			break;
		case 1 :
			$str = '<span class="label label-success">正常</span>';
			break;
		case 0 :
			$str = '<span class="label label-danger">禁用</span>';
			break;
	}
	return $str;
}


/**
 * 获取cron状态信息
 * @param $status
 * @return string
 */
function get_cron_status_info($status)
{
	$str = '';
	switch(intval($status))
	{
		case -1 :
			$str = '<span class="label label-danger">已删除</span>';
			break;
		case 1 :
			$str = '<span class="label label-success">正常</span>';
			break;
		case 0 :
			$str = '<span class="label label-danger">禁用</span>';
			break;
		case 2 :
			$str = '<span class="label label-info">完成</span>';
			break;
		case 3 :
			$str = '<span class="label label-danger">已过期</span>';
			break;
	}
	return $str;
}

/**
 * 获取cron（log）状态信息
 * @param $status
 * @return string
 */
function get_cronlog_status_info($status)
{
	$str = '';
	switch(intval($status))
	{
		case 1 :
			$str = '<span class="label label-success">成功</span>';
			break;
		case 0 :
			$str = '<span class="label label-danger">失败</span>';
			break;
	}
	return $str;
}

/**
 * 获取菜单的名称
 * @param $pid
 * @return mixed|string
 */
function getParentMenuTitle($pid)
{
	return Db::name('menu')->where(['id'=>$pid])->value('title') ?? '--';
}
