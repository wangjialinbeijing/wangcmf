<?php
namespace app\admin\controller;

use think\Db;
use WBuilder\WBuilder;

/**
 * Class Builder
 * @package app\admin\controller
 */
class Builder extends Admin
{
	public function index()
	{
		// 查询数据列表对象
		$list = Db::name('user')->paginate(3);
		return WBuilder::make('table')
			->setPageHeader('用户列表')
			->setPageHeaderSmall('所有用户')
			->setPageTitle('构建器：用户管理')
			->addColumn('id' , 'ID')
			->addColumn('name' , '用户名')
			->addColumn('type' , '用户类型' , 'callback' , function($value){
				return $value == 0 ? '管理员' : '普通用户';
			})
			->addColumn('mobile' , '手机号')
			->addColumn('status' , '状态' , 'status')
			->addColumn('create_time' ,'记录时间',  'date' , '--')
			->addRightButton('edit')
			->addRightButton('status')
			->addRightButton('delete')
			->addRightButton('custom' , ['title'=> '返回首页' ,'href'=>url('index/index',['id'=>'__id__'])])
			->setRowList($list)    // 设置表格的数据
			->setPages($list->render())
			->js('a.js')
			->fetch();
	}
}