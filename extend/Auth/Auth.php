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
		'AUTH_TYPE'         => 1,                         // 认证方式，1为实时认证；2为登录认证。
		'AUTH_GROUP'        => 'auth_group',                // 用户组数据表名
		'AUTH_GROUP_ACCESS' => 'auth_group_access',         // 用户-用户组关系表
		'AUTH_RULE'         => 'auth_rule',                 // 权限规则表
		'AUTH_USER'         => 'member'                     // 用户信息表
	);

	/**
	 * 权限验证类构造方法
	 * Auth constructor.
	 */
	public function __construct() {
		// 获取数据库表前缀
		$prefix = config('database.prefix');
		$this->_config['AUTH_GROUP'] = $prefix.$this->_config['AUTH_GROUP'];
		$this->_config['AUTH_RULE'] = $prefix.$this->_config['AUTH_RULE'];
		$this->_config['AUTH_USER'] = $prefix.$this->_config['AUTH_USER'];
		$this->_config['AUTH_GROUP_ACCESS'] = $prefix.$this->_config['AUTH_GROUP_ACCESS'];
		if (config('AUTH_CONFIG')) {
			//可设置配置项 AUTH_CONFIG, 此配置项为数组。
			$this->_config = array_merge($this->_config, config('AUTH_CONFIG'));
		}
	}

	protected function getAuthList($uid,$type) {
		static $_authList = []; //保存用户验证通过的权限列表
		$t = implode(',',(array)$type);
		if (isset($_authList[$uid.$t])) {
			return $_authList[$uid.$t];
		}
		if( $this->_config['AUTH_TYPE']==2 && isset($_SESSION['_AUTH_LIST_'.$uid.$t])){
			return $_SESSION['_AUTH_LIST_'.$uid.$t];
		}

		//读取用户所属用户组
		$groups = $this->getGroups($uid);
		$ids = array();//保存用户所属用户组设置的所有权限规则id
		foreach ($groups as $g) {
			$ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
		}
		$ids = array_unique($ids);
		if (empty($ids)) {
			$_authList[$uid.$t] = array();
			return array();
		}

		$map=array(
			'id'=>array('in',$ids),
			'type'=>$type,
			'status'=>1,
		);
		//读取用户组所有权限规则
		$rules = Db::name($this->_config['AUTH_RULE'])->where($map)->field('condition,name')->select();

		//循环规则，判断结果。
		$authList = array();   //
		foreach ($rules as $rule) {
			if (!empty($rule['condition'])) { //根据condition进行验证
				$user = $this->getUserInfo($uid);//获取用户信息,一维数组

				$command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
				//dump($command);//debug
				@(eval('$condition=(' . $command . ');'));
				if ($condition) {
					$authList[] = strtolower($rule['name']);
				}
			} else {
				//只要存在就记录
				$authList[] = strtolower($rule['name']);
			}
		}
		$_authList[$uid.$t] = $authList;
		if($this->_config['AUTH_TYPE']==2){
			//规则列表结果保存到session
			$_SESSION['_AUTH_LIST_'.$uid.$t]=$authList;
		}
		return array_unique($authList);
	}

	public function check($name, $uid, $type=1, $mode='url', $relation='or') {
		if (!$this->_config['AUTH_ON'])
			return true;
		$authList = $this->getAuthList($uid,$type); //获取用户需要验证的所有有效规则列表

		if (is_string($name)) {
			$name = strtolower($name);
			if (strpos($name, ',') !== false) {
				$name = explode(',', $name);
			} else {
				$name = array($name);
			}
		}
		$list = array(); //保存验证通过的规则名
		if ($mode=='url') {
			$REQUEST = unserialize( strtolower(serialize($_REQUEST)) );
		}
		foreach ( $authList as $auth ) {
			$query = preg_replace('/^.+\?/U','',$auth);
			if ($mode=='url' && $query!=$auth ) {
				parse_str($query,$param); //解析规则中的param
				$intersect = array_intersect_assoc($REQUEST,$param);
				$auth = preg_replace('/\?.*$/U','',$auth);
				if ( in_array($auth,$name) && $intersect==$param ) {  //如果节点相符且url参数满足
					$list[] = $auth ;
				}
			}else if (in_array($auth , $name)){
				$list[] = $auth ;
			}
		}

		if ($relation == 'or' and !empty($list)) {
			return true;
		}
		$diff = array_diff($name, $list);
		if ($relation == 'and' and empty($diff)) {
			return true;
		}
		return false;
	}

	/**
	 * 获取用户角色群组及节点
	 * @param $user_id
	 * @return mixed
	 */
	public function getGroups($user_id) {
		static $groups = array();
		if (isset($groups[$user_id]))
			return $groups[$user_id];
		$where_map = [
			'a.user_id'      =>  $user_id,
			'g.status'  =>  1
		];
		$user_groups = Db::table($this->_config['AUTH_GROUP_ACCESS'].' a')
			->field(true)
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
		static $user_info=array();
		if(!isset($user_info[$user_id])){
			$user_info[$user_id]=Db::name('user')->where(array('id'=>$user_id))->find();
		}
		return $user_info[$user_id];
	}

}