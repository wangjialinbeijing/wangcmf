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
		$list = Db::name('crontab')->paginate(10);
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
			$data = $this->request->param();
			$validate = new Crontab();
			$result = $validate->check($data);
			if(!$result)
			{
				$this->error($validate->getError());
			}

			$data['begin_time'] = strtotime($data['begin_time']);
			$data['end_time'] = strtotime($data['end_time']);
			if($data['begin_time'] < time())
			{
				$this->error('开始时间不能小于当前时间');
			}
			if($data['begin_time'] >= $data['end_time'])
			{
				$this->error('开始时间不能大于等于结束时间');
			}
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
}
