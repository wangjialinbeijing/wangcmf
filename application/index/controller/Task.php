<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

/*
 * 发布订阅消息模块
 */
class Task extends Controller
{

	private $redis_host = '127.0.0.1';
	private $redis_port = 6379;
	private $task_name = 'task_queue';

	/**
	 * 发布Redis订阅
	 */
	public function publish()
	{
		if(true)
		{
			// 获取基本参数
			$user_id = session('USER_ID');
			$goods_id = $this->request->param('goods_id');
			if(!$user_id || !$goods_id)
			{
				$this->error('用户id或者商品id参数错误');
			}

			// 或者直接在数据库中查询是否已经下单支付
			$redis = new \Redis();
			$redis->connect($this->redis_host , $this->redis_port);
			$is_buy = $redis->hGet('goods_'.$goods_id , $goods_id.'_'.$user_id);
			if($is_buy)
			{
//				$this->error('商品已经购买过');
			}
			//Todo:: 再进行一次数据库order表的查询,潜在降低性能
			// 把：goods_id_user_id写入redis哈希列表中
			$insert = $redis->hSet('goods_'.$goods_id , $goods_id.'_'.$user_id , true);
			if(!$insert)
			{
//				$this->error('系统错误，请稍后再试！');
			}
			// 发布订阅消息
			$task = [
				'user_id' => $user_id,
				'goods_id' => $goods_id
			];

			// 发布定义消息（序列化）
			$publish = $redis->publish($this->task_name , serialize($task));
			if(!$publish)
			{
				$this->error('抢购失败，请稍后再试！');
			}
			$this->success('正在抢购中，请耐心等待！');
		}
		$this->error('参数错误！');
	}
}
