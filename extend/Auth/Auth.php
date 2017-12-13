<?php
namespace Auth;

use think\Db;

/**
 * RABC权限类
 * Class Auth
 * @package Auth
 */
class Auth
{
	//默认配置
	protected $_config = array(
		'AUTH_ON'           => true,                      // 认证开关
		'AUTH_GROUP'        => 'auth_group',                // 用户组数据表名
		'AUTH_GROUP_ACCESS' => 'auth_group_access',         // 用户-用户组关系表
		'AUTH_RULE'         => 'auth_rule',                 // 权限规则表
		'AUTH_USER'         => 'member'                     // 用户信息表
	);

	/**
	 * 权限类构造方法
	 * Auth constructor.
	 */
	public function __construct() {
		// 获取数据库表前缀
		$prefix = config('database.prefix');
		// 定义权限表名称
		$this->_config['AUTH_GROUP'] = $prefix.$this->_config['AUTH_GROUP'];
		$this->_config['AUTH_RULE'] = $prefix.$this->_config['AUTH_RULE'];
		$this->_config['AUTH_USER'] = $prefix.$this->_config['AUTH_USER'];
		$this->_config['AUTH_GROUP_ACCESS'] = $prefix.$this->_config['AUTH_GROUP_ACCESS'];
	}

	/**
	 * 获取用户权限节点列表
	 * @param $uid 用户ID
	 * @return array|mixed 节点列表
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function getAuthList($uid) {
		// 保存用户验证通过的权限列表
		static $_authList = [];
		// 查看是否已经有用户权限列表
		if (isset($_authList[$uid])) {
			return $_authList[$uid];
		}
		//读取用户所属用户组
		$groups = $this->getGroups($uid);
		// 保存用户所属用户组设置的所有权限规则id
		$ids = [];
		foreach ($groups as $group) {
			$ids = array_merge($ids, explode(',', trim($group['rules'], ',')));
		}
		// 移除重复的权限节点值
		$ids = array_unique($ids);
		// 没有权限节点值，则返回空数组
		if (empty($ids)) {
			$_authList[$uid] = [];
			return [];
		}
		// 构造查询条件
		$map=array(
			'id'=>['in',$ids],
			'status'=>1,
		);
		//读取用户组所有权限规则
		$rules = Db::name('auth_rule')->where($map)->field('condition,name')->select();
		//循环规则，判断结果。
		$authList = [];   //
		foreach ($rules as $rule) {
			//只要存在就记录
			$authList[] = strtolower($rule['name']);
		}
		$_authList[$uid] = $authList;
		return array_unique($authList);
	}

	/**
	 * @param $name 权限节点名称：模块/控制器/方法 admin/index/index
	 * @param $uid  用户ID
	 * @param string $mode
	 * @param string $relation
	 * @return bool
	 */
	public function check($name, $uid, $mode='url') {
		// 是否开启权限验证
		if (!$this->_config['AUTH_ON'])
		{
			return true;
		}
		// 获取当前用户拥有的权限节点列表
		$authList = $this->getAuthList($uid); //获取用户需要验证的所有有效规则列表
		// 判断权限节点是否为字符串
		if (is_string($name)) {
			$name = [strtolower($name)];
		}
		// 保存验证通过的规则名
		$list = [];
		// 获取请求对象（URL地址和参数都转换为小写）
		if ($mode=='url') {
			$REQUEST = unserialize( strtolower(serialize($_REQUEST)) );
		}
		// 遍历用户权限列表
		foreach ( $authList as $auth ) {
			$query = preg_replace('/^.+\?/U','',$auth);
			if ($mode=='url' && $query!=$auth ) {
				// 解析URL规则中的参数
				parse_str($query,$param);
				// 和请求参数进行比较，返回交集
				$intersect = array_intersect_assoc($REQUEST,$param);
				// 获取权限节点名称（去除URL参数）
				$auth = preg_replace('/\?.*$/U','',$auth);
				// 判断节点是否相符且URL参数满足
				if ( in_array($auth,$name) && $intersect==$param )
				{
					$list[] = $auth ;
				}
			}else if (in_array($auth , $name)){
				$list[] = $auth ;
			}
		}
		// 判断是否拥有权限
		if (!empty($list)) {
			return true;
		}
		return false;
	}

	/**
	 * 获取用户角色及其节点
	 * @param $user_id
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function getGroups($user_id) {
		static $groups = [];
		if (isset($groups[$user_id]))
			return $groups[$user_id];
		$where_map = [
			'a.user_id'      =>  $user_id,
			'g.status'  =>  1
		];
		$user_groups = Db::table($this->_config['AUTH_GROUP_ACCESS'].' a')
			->where($where_map)
			->join($this->_config['AUTH_GROUP']." g" ,'a.group_id=g.id')
			->select();
		// sql:SELECT `user_id`,`group_id`
		// FROM `db_auth_group_access` `a`
		// INNER JOIN `db_auth_group` `g`
		// ON `a`.`group_id`=`g`.`id`
		// WHERE  `a`.`user_id` = 1
		// AND `g`.`status` = '1'
		$groups[$user_id]=$user_groups?:[];
		return $groups[$user_id];
	}

	/**
	 * 获取用户信息
	 * @param $user_id
	 * @return mixed
	 */
	protected function getUserInfo($user_id) {
		static $user_info=[];
		if(!isset($user_info[$user_id])){
			$user_info[$user_id]=Db::name('user')->where(array('id'=>$user_id))->find();
		}
		return $user_info[$user_id];
	}

}