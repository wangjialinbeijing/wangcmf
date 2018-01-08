<?php
namespace app\admin\controller;
use app\admin\validate\Crontab;
use think\Db;

/**
 * 计划任务管理
 * Class Cron
 * @package app\admin\controller
 */
class Cron extends Admin
{
	/**
	 * 计划任务列表
	 * @return mixed
	 * @throws \think\exception\DbException
	 */
	public function index()
	{
		// 查询所有的任务记录
		$list = Db::name('crontab')->order('id desc')->paginate(10);
		$this->assign('_list' , $list);
		return $this->fetch();
	}

	/**
	 * 新增计划任务
	 * @return mixed
	 */
	public function add()
	{
		if($this->request->isPost())
		{
			// 获取参数
			$data = $this->request->param();
			// 参数验证
			$validate = new Crontab();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}
			// 时间参数格式转换
			$data['begin_time'] = strtotime($data['begin_time']);
			$data['end_time'] = strtotime($data['end_time']);
			// 时间区间范围判断
			if($data['begin_time'] < time())
			{
				$this->error('开始时间不能小于当前时间');
			}
			if($data['begin_time'] >= $data['end_time'])
			{
				$this->error('开始时间不能大于等于结束时间');
			}
			// 构建数据并写入数据表
			$data['status'] = 1;
			$data['create_time'] = time();
			if(!Db::name('crontab')->insert($data))
			{
				$this->error('数据写入失败！');
			}
			$this->success('新增计划任务成功！');
		}
		$this->assign('active_url' , 'Cron/index');
		return $this->fetch();
	}

	/**
	 * 禁用和启用状态迁移
	 */
	public function setCronStatus()
	{
		return $this->setStatus(Db::name('crontab'));
	}

	/**
	 * 日志列表
	 * @return mixed
	 */
	public function log_list()
	{
		$crontab_id = $this->request->param('id');
		if(!$crontab_id)
		{
			$this->error('参数错误！');
		}
		$list = Db::name('crontab_log')->where(['crontab_id'=>$crontab_id])->paginate(20);
		$this->assign('_list' , $list);
		$this->assign('active_url' , 'Cron/index');
		return $this->fetch();
	}
}
