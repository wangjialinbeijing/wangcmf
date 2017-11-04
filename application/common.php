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
	 * @param $sub_menu
	 * @param $active_url
	 * @return bool
	 */
	function show_menu_active($sub_menu, $active_url)
	{
		$active = false;
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

function is_admin($uid = null){
	$uid = is_null($uid) ? is_login() : $uid;
	return $uid && (intval($uid) === config('USER_ADMIN'));
}
