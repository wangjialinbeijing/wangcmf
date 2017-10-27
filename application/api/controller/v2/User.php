<?php
namespace app\api\controller\v2;

use app\api\controller\Api;

/**
 * 用户接口类v2
 * Class User
 * @package app\api\controller\v2
 */
class User extends Api
{
	public function info($user_id = 0 )
	{
		if(is_numeric($user_id))
		{
			$user_info = [
				'username'      => 'wangjialin',
				'mobile'        => '13011112222',
				'age'           => 12,
				'update_time'   => '2012-12-12 12:12',
				'create_time'   => '2011-11-11 12:12'
			];
			return json($user_info);
		}
		abort(500 ,'参数错误');
	}
}
