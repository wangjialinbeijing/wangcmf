<?php
namespace app\admin\controller;

use app\common\controller\Config;
use Auth\Auth;
use Cron\CronExpression;
use think\Controller;
use think\Db;

class Admin extends Controller
{

	// 每页分页显示条数
	protected $page = 10;

	// 测试用
	public function cron()
	{
		$cron = CronExpression::factory('* * * * *');
		dump($cron);
	}

	// 初始化方法
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
		// 数据库配置合并到系统配置
	    config($config);

	    // 定义常量获取模块、控制器和方法名
	    define('MODULE_NAME' , request()->module());
		define('CONTROLLER_NAME' , request()->controller());
		define('ACTION_NAME' , request()->action());
	    // 当前项目地址
	    define('__APP__',strip_tags(rtrim($_SERVER['SCRIPT_NAME'],'/')));
	    // 当前请求是否AJAX
	    define('IS_AJAX' , request()->isAjax());
	    // 是否是超级管理员
	    define('IS_ROOT',  is_admin());

		// 菜单变量置换到模板中
	    $this->assign('menu_list' , $this->getMenus());
	    // 当前选中的控制器/方法
	    $this->assign('active_url' , CONTROLLER_NAME.'/'.ACTION_NAME);

	    // 权限判断(超级管理员默认拥有全部权限)
	    if(!IS_ROOT)
	    {
	    	// 获取当前访问的控制器/方法地址
		    $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
		    // 初始化权限类
		    $auth = new Auth();
		    if(!$auth->check($rule,USER_ID))
		    {
		    	unset($_SESSION);
				$this->error('未授权访问');
		    }
	    }
    }

	/**
	 * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
	 */
	public function getMenus($controller=CONTROLLER_NAME){
		if(empty($menus)){
			// 获取主菜单
			$where['pid']   =   0;
			$where['hide']  =   0;
			$menus['main']  =   Db::name('menu')->where($where)->order('sort asc')->field('id,title,url')->select();
			$menus['child'] =   array(); //设置子节点
			$auth = new Auth();
			foreach ($menus['main'] as $key => $item) {
				// 判断主菜单权限
				if ( !IS_ROOT && !$auth->check(strtolower(MODULE_NAME.'/'.$item['url']),USER_ID) ) {
					unset($menus['main'][$key]);
					continue;//继续循环
				}
				if(strtolower(CONTROLLER_NAME.'/'.ACTION_NAME)  == strtolower($item['url'])){
					$menus['main'][$key]['class']='active';
				}
			}

			// 查找当前子菜单
			$pid = Db::name('menu')->where("pid !=0 AND url like '%{$controller}/".ACTION_NAME."%'")->value('pid');

			if($pid){
				// 查找当前主菜单
				$nav =  Db::name('menu')->find($pid);
				if($nav['pid']){
					$nav    =   Db::name('menu')->find($nav['pid']);
				}
				foreach ($menus['main'] as $key => $item) {
					// 获取当前主菜单的子菜单项
					if($item['id'] == $nav['id']){
						$menus['main'][$key]['class']='active';
						//生成child树
						$groups_list = Db::name('menu')
							->field('group')
							->where(array('group'=>array('neq',''),'pid' =>$item['id']))
							->distinct(true)
							->select();
						$groups = [];
						foreach($groups_list as $k=>$v)
						{
							$groups[] = $v['group'];
						}

						//获取二级分类的合法url
						$where          =   array();
						$where['pid']   =   $item['id'];
						$where['hide']  =   0;
						$second_urls = Db::name('menu')->where($where)->column('url' , 'id');

						if(!IS_ROOT){
							// 检测菜单权限
							$to_check_urls = array();
							if($second_urls)
							{
								foreach ($second_urls as $key=>$to_check_url) {
									if( stripos($to_check_url,MODULE_NAME)!==0 ){
										$rule = MODULE_NAME.'/'.$to_check_url;
									}else{
										$rule = $to_check_url;
									}
									if($auth->check($rule, USER_ID))
									{
										$to_check_urls[] = $to_check_url;
									}
								}
							}
						}
						// 按照分组生成子菜单树
						foreach ($groups as $g) {
							$map = array('group'=>$g);
							if(isset($to_check_urls)){
								if(empty($to_check_urls)){
									// 没有任何权限
									continue;
								}else{
									$map['url'] = array('in', $to_check_urls);
								}
							}
							$map['pid']     =   $item['id'];
							$map['hide']    =   0;
							$menuList = Db::name('menu')->where($map)->field('id,pid,title,url,tip')->order('sort asc')->select();
							$menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
						}
					}
				}
			}
			// 菜单数据缓存到会话，减少数据查询
			session('ADMIN_MENU_LIST.'.$controller,$menus);
		}
		return $menus;
	}

	// 表记录变更状态方法
	protected function setStatus($model = null)
	{
		if($model !== null)
		{
			$id = input('id');
			$status = input('status');

			if($id !== null && $status !== null)
			{
				$map = [];
				$map['id'] = $id;
				$data['status'] = $status;
				$model->where($map)->update($data);

				switch ($status)
				{
					case -1 :
						$this->success('删除成功！');
						break;
					case 1 :
						$this->success('启用成功！');
						break;
					case 0 :
						$this->success('禁用成功！');
						break;
				}
			}
		}
		$this->error('参数错误！');
	}


	/**
	 * 获取菜单权限节点
	 * @param bool $tree
	 * @return array|false|mixed|\PDOStatement|string|\think\Collection
	 */
	protected function returnMenuNodes($tree = true){
		static $tree_nodes = array();
		if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
			return $tree_nodes[$tree];
		}
		if((int)$tree){
			$list = Db::name('Menu')->field('id,pid,title,url,tip,hide')->order('sort asc')->select();
			foreach ($list as $key => $value) {
				if( stripos($value['url'],MODULE_NAME)!==0 ){
					$list[$key]['url'] = MODULE_NAME.'/'.$value['url'];
				}
			}
			$nodes = list_to_tree($list,$pk='id',$pid='pid',$child='operator',$root=0);
			foreach ($nodes as $key => $value) {
				if(!empty($value['operator'])){
					$nodes[$key]['child'] = $value['operator'];
					unset($nodes[$key]['operator']);
				}
			}
		}else{
			$nodes = Db::name('Menu')->field('title,url,tip,pid')->order('sort asc')->select();
			foreach ($nodes as $key => $value) {
				if( stripos($value['url'],MODULE_NAME)!==0 ){
					$nodes[$key]['url'] = MODULE_NAME.'/'.$value['url'];
				}
			}
		}
		$tree_nodes[(int)$tree]   = $nodes;
		return $nodes;
	}

	/**
	 * 通过菜单更新节点数据
	 * @return bool
	 */
	public function updateRules(){
		//需要新增的节点必然位于菜单节点
		$nodes    = $this->returnMenuNodes(false);
		// 查询现在的节点规则
		$map['module'] = 'admin';
		$map['type'] = ['in' ,'1,2'];
		$rules    = Db::name('auth_rule')->where($map)->order('name')->select();
		//保存需要插入和更新的新节点
		$data     = [];
		foreach ($nodes as $value)
		{
			$temp['name']   = $value['url'];
			$temp['title']  = $value['title'];
			$temp['module'] = 'admin';
			$temp['type'] = 2; // 顶级菜单
			if($value['pid'] > 0){
				$temp['type'] = 1; // 一般URL
			}
			$temp['status']   = 1;
			$data[strtolower($temp['name'].$temp['module'].$temp['type'])] = $temp;//去除重复项
		}


		$update = [];//保存需要更新的节点
		$ids    = [];//保存需要删除的节点的id
		foreach ($rules as $index=>$rule)
		{
			$key = strtolower($rule['name'].$rule['module'].$rule['type']);
			if ( isset($data[$key]) ) {//如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
				$data[$key]['id'] = $rule['id'];//为需要更新的节点补充id值
				$update[] = $data[$key];
				unset($data[$key]);
				unset($rules[$index]);
				unset($rule['condition']);
				$diff[$rule['id']]=$rule;
			}elseif($rule['status']==1){
				$ids[] = $rule['id'];
			}
		}
		// 是否有更新的节点数据
		if ( count($update) ) {
			foreach ($update as $k=>$row){
				if ( $row != $diff[$row['id']] ) {
					Db::name('auth_rule')->where(['id'=>$row['id']])->update($row);
				}
			}
		}
		// 是否有需要删除的节点数据
		if ( count($ids) ) {
			Db::name('auth_rule')->where( array( 'id'=>array('IN',implode(',',$ids)) ) )->update(array('status'=>-1));
			//删除规则是否需要从每个用户组的访问授权表中移除该规则?
		}
		// 是否有新增的节点数据
		if( count($data) ){
			foreach($data as $value)
			{
				Db::name('auth_rule')->insert($value);
			}
		}
		return true;
	}
}
