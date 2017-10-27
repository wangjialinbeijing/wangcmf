<?php
namespace app\api\exception;

use think\exception\Handle;
use think\exception\HttpException;

class Http extends Handle
{

	/**
	 * 自定义抛出异常返回结构值
	 * @param \Exception $e
	 * @return \think\response\Json
	 */
	public function render(\Exception $e)
	{
		// 获取异常标识
		if ($e instanceof HttpException) {
			$statusCode = $e->getStatusCode();
		}
		// 是否设置状态码，默认500
		if (!isset($statusCode)) {
			$statusCode = 500;
		}
		// 返回内容定义
		$result = [
			'status'         => 0,
			'info'           => $e->getMessage()
		];
		// 返回格式定义（json)
		return json($result, $statusCode);
	}

}