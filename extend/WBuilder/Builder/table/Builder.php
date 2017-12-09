<?php
namespace WBuilder\builder\table;

use think\Request;
use WBuilder\WBuilder;

/**
 * 表格构建器
 * Class Builder
 * @package WBuilder\builder\table
 */
class Builder extends WBuilder
{

	/**
	 * @var string 模板路径
	 */
	private $_template = '';

	public function __construct(Request $request = null)
	{
		parent::__construct($request);

		return $this->fetch();
	}
}