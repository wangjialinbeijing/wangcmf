<?php
// 防止监听过程中断开与redis的连接
ini_set('default_socket_timeout', -1);
//大于100M内存退出程序，防止内存泄漏被系统杀死导致任务终端
if(memory_get_usage() > 100*1024*1024){
	exit(0);
}
// 实例化Redis类，监听订阅信息
$redis = new \Redis();
$redis->connect('127.0.0.1',6379);
$redis->subscribe(['task_queue'], function($redis,$chan,$msg){
	switch ($chan) {
		case 'task_queue':
			// 反序列化消息数据
			$task = unserialize($msg);
			// 参数判断
			if(!$task['user_id'] || !$task['goods_id'])
			{
				echo "参数错误\r\n";
				return false;
			}
			// 创建PDO数据库连接
			try {
				$pdo = new PDO('mysql:host=127.0.0.1;dbname=wangcmf', 'root', '');
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			} catch (PDOException $e) {
				echo "Connect Failed: " . $e->getMessage();
				return false;
			}
			// 关闭自动提交
			$pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
			try {
				$pdo->beginTransaction(); // 开启一个事务

				// 已经处理完毕
				$new_redis = new \Redis();
				$new_redis->connect('127.0.0.1',6379);
				$new_redis->hSet('goods_'.$task['goods_id'] , $task['goods_id'].'_'.$task['user_id'] , 1);

				// 查询商品是否还有库存
				$sql = "select * from db_goods where id = {$task['goods_id']} and stock > 0 and status = 1";
				$goods_info = [];
				foreach ($pdo->query($sql) as $item)
				{
					$goods_info = $item;
				}
				if(empty($goods_info))
				{
					echo "商品没有库存，或者已经下架\r\n";
					$pdo->rollback();
					return false;
				}

				// 查询是否已经下过单
				$sql = "select * from db_orders where user_id={$task['user_id']} and goods_id = {$task['goods_id']} and status = 1 and is_pay = 1";
				$result = [];
				foreach ($pdo->query($sql) as $item)
				{
					$result[] = $item;
				}
				if(!empty($result))
				{
					echo "请勿重新下单\r\n";
					$pdo->rollback();
					return false;
				}

				// 创建订单（模拟支付成功）
				$order_no = date('YmdHis').mt_rand(10000,99999);
				$pay_price = $goods_info['sell_price'];
				$is_pay = 1;
				$pay_time = time();
				$status = 1;
				$create_time = time();
				$update_time = 0;
				$sql = "insert into db_orders values(null,'{$task['goods_id']}' , '{$task['user_id']}' , '{$order_no}',{$pay_price},{$is_pay},{$pay_time},{$status},{$create_time},{$update_time})";
				$result = $pdo->exec($sql);
				if(!$result)
				{
					echo "下单失败\r\n";
					$pdo->rollback();
					return false;
				}

				// 减少库存
				$stock = ( $goods_info['stock'] - 1 ) >= 0 ? $goods_info['stock'] - 1 : 0;
				$sql = "update db_goods set stock = {$stock} where id = {$goods_info['id']}";
				$result = $pdo->exec($sql);
				if(!$result)
				{
					echo "更新库存失败\r\n";
					$pdo->rollback();
					return false;
				}
				echo "下单成功";
				$pdo->commit();
			} catch (PDOException $e) {
				$pdo->rollback(); // 执行失败，事务回滚
				exit($e->getMessage());
			}
			break;
		default:
			break;
	}
});