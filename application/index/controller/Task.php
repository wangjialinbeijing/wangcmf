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
		if($this->request->isAjax())
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
				$this->error('商品已经购买过');
			}
			//Todo:: 再进行一次数据库order表的查询,潜在降低性能
			// 把：goods_id_user_id写入redis哈希列表中
			$insert = $redis->hSet('goods_'.$goods_id , $goods_id.'_'.$user_id , 0);
			if(!$insert)
			{
				$this->error('系统错误，请稍后再试！');
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

	/**
	 * 获取订单状态
	 */
	public function getOrderStatus()
	{
		if($this->request->isAjax())
		{
			$user_id =  $this->request->param('user_id');
			$goods_id = $this->request->param('goods_id');
			if(!$user_id || !$goods_id)
			{
				$this->error('用户id或商品id错误！');
			}
			$map = [
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'status' => 1
			];
			// 查看队列是否已经处理完成
			$key = $goods_id . '_' . $user_id;
			$redis = new \Redis();
			$redis->connect($this->redis_host , $this->redis_port);
			$is_finish = $redis->hGet('goods_'.$goods_id , $key);
			// 队列正在处理，提示耐心等待
			if($is_finish == 0)
			{
				$this->error('正在排队处理中，请继续等待！');
			}
			// 查询订单是否已经创建
			$order_info = Db::name('orders')->where($map)->find();
			// 订单信息为空，并且队列已经处理完毕，说明没有抢到订单
			if($order_info === null && $is_finish == 1)
			{
				$this->error('很遗憾，商品已经售完！');
			}
			$this->success('抢购成功，订单已经创建，请点击确认按钮查看订单！');
		}
		$this->error('参数错误！');
	}
}
