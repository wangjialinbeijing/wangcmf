<?php
namespace app\admin\controller;

use app\common\controller\Config;
use think\Controller;
use think\Db;

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

	    // 定义常量获取模块控制器和方法名
	    define('MODULE_NAME' , request()->module());
		define('CONTROLLER_NAME' , request()->controller());
		define('ACTION_NAME' , request()->action());

		// 菜单变量置换到模板中
	    $this->assign('menu_list' , $this->getMenus());
	    // 当前选中的控制器/方法
	    $this->assign('active_url' , CONTROLLER_NAME.'/'.ACTION_NAME);
    }

	/**
	 * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
	 */
	final public function getMenus($controller=CONTROLLER_NAME){
		$menus  =   session('ADMIN_MENU_LIST.'.$controller);
		if(empty($menus)){
			// 获取主菜单
			$where['pid']   =   0;
			$where['hide']  =   0;
			$menus['main']  =   Db::name('menu')->where($where)->order('sort asc')->field('id,title,url')->select();
			$menus['child'] =   array(); //设置子节点
			foreach ($menus['main'] as $key => $item) {
				// 判断主菜单权限
//				if ( !IS_ROOT && !$this->checkRule(strtolower(MODULE_NAME.'/'.$item['url']),AuthRuleModel::RULE_MAIN,null) ) {
//					unset($menus['main'][$key]);
//					continue;//继续循环
//				}
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

//						if(!IS_ROOT){
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
//								if($this->checkRule($rule, AuthRuleModel::RULE_URL,null))
									$to_check_urls[] = $to_check_url;
								}
							}

//						}
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
			session('ADMIN_MENU_LIST.'.$controller,$menus);
		}
		return $menus;
	}
}
