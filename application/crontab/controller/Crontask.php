<?php
namespace app\crontab\controller;

use Cron\CronExpression;
use think\Controller;
use think\Db;
use think\Exception;

/**
 * 计划任务（cli执行）
 * Class Crontask
 * @package app\crontab\controller
 */
class Crontask extends Controller
{
	private $_config = [];
	/**
	 * 初始化方法
	 */
	public function _initialize()
	{
		parent::_initialize();
		config('app_trace', false); // 关闭 app_trace
		// 只可以以cli方式执行
		if (!$this->request->isCli()){
			$this->error('计划任务必须在命令行中执行');
		}
		// 初始化状态
		$this->_config = [
			'DELETE'    => -1, //已经删除
			'DISABLED'  => 0, //禁用
			'NORMAL'    => 1, //正常
			'COMPLETED' => 2, // 完成
			'EXPIRED' => 3 // 过期
		];
	}

	/**
	 * 定时任务执行方法
	 * @return bool
	 */
	public function index()
	{
		// 查询所有的任务列表
		$map['status'] = ['gt' , 0];
		$crontab_list = Db::name('crontab')->where($map)->select();
		if(!$crontab_list)
		{
			return false;
		}
		$now_time = time();
		// 遍历所有的任务记录
		foreach($crontab_list as $key=>$crontab)
		{
			$is_execute = false;   // 是否执行
			$update = []; // 需要更新的数据
			if ($now_time < $crontab['begin_time']) {   //任务未开始
				continue;
			}
			if ($crontab['maximums'] && $crontab['executes'] >= $crontab['maximums']) { // 任务超过最大执行次数，任务完成
				$update['status'] = $this->_config['COMPLETED'];
			} else if ($crontab['end_time'] > 0 && $now_time > $crontab['end_time']) { // 任务已过期
				$update['status'] = $this->_config['EXPIRED'];
			} else {
				// 创建计划任务对象，并传入时间表达式
				$cron = CronExpression::factory($crontab['schedule']);
				/*
				 * 根据当前时间判断是否该应该执行
				 * 这个判断和秒数无关，其最小单位为分
				 * 也就是说，如果处于该执行的这个分钟内如果多次调用都会判定为真
				 * 所以我们在服务器上设置的定时任务最小单位应该是分
				 */
				if ($cron->isDue()) {
					// 允许执行
					$is_execute = true;
					// 允许执行的时候更新状态
					$update['execute_time'] = $now_time;
					$update['update_time'] = $now_time;
					$update['executes'] = $crontab['executes'] + 1;
					$update['status'] =
						($crontab['maximums'] > 0 && $update['executes'] >= $crontab['maximums']) ?
							$this->_config['COMPLETED'] : $this->_config['NORMAL'];
				} else {    //如果未到执行时间则跳过本任务去判断下一个任务
					continue;
				}
			}

			$map = [];
			$map['id'] = $crontab['id'];
			// 更新状态
			Db::name('crontab')->where($map)->update($update);

			// 如果不允许执行，只是从当前开始已过期或者已超过最大执行次数的任务，只是更新状态就行了，不执行
			if (!$is_execute) {
				continue;
			}

			// 执行计划任务操作
			try{
				// 判断任务类型
				switch ($crontab['type']) {
					// 请求URL
					case 'url':
						if (substr($crontab['content'], 0, 1) == "/") {// 本地项目URL
							$request = shell_exec('php ' . ROOT_PATH . 'index.php ' . $crontab['content'] . ' 2>&1');
							$this->saveLog('url', $crontab['id'], $crontab['title'], 1, $request);
						} else {
							// 远程URL
							try {
								// 使用Requests对象请求URL
								$request = \Requests::get($crontab['content']);
								if ($request->success) {
									$this->saveLog('url', $crontab['id'], $crontab['title'], 1, $crontab['content'] . ' 请求成功，HTTP状态码: ' . $request->status_code);
								} else {
									$this->saveLog('url', $crontab['id'], $crontab['title'], 0, $crontab['content'] . ' 请求失败，HTTP状态码: ' . $request->status_code);
								}
							} catch (\Requests_Exception $e) {
								$this->saveLog('url', $crontab['id'], $crontab['title'], 0, $crontab['content'] . ' 任务异常: ' . $e->getMessage());
							}
						}
						break;
					case 'shell':
						// 执行命令
						$request = shell_exec($crontab['content'] . ' 2>&1');
						$this->saveLog('shell', $crontab['id'], $crontab['title'], 1, $request);
						break;
				}
			}
			catch (\Exception $e)
			{
				// 记录异常
				$this->saveLog($crontab['type'], $crontab['id'], $crontab['title'], 0, "执行的内容发生异常:\r\n" . $e->getMessage());
			}
		}
	}

	// 保存运行日志
	private function saveLog($type, $crontab_id, $title, $status, $remark = '')
	{
		$data['type'] = $type;
		$data['crontab_id'] = $crontab_id;
		$data['title'] = $title;
		$data['status'] = $status;
		$data['remark'] = $remark;
		$data['create_time'] = time();
		Db::name('crontab_log')->insert($data);
	}
}