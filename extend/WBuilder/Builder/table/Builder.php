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
	 * @var string 当前模型名称
	 */
	private $_module = '';

	/**
	 * @var string 当前控制器名称
	 */
	private $_controller = '';

	/**
	 * @var string 当前操作名称
	 */
	private $_action = '';

	/**
	 * @var string 数据表名
	 */

	private $_table_name = '';
	/**
	 * @var string 模板路径
	 */

	private $_template = '';

	/**
	 * @var array 模板变量
	 */
	private $_vars = [
		'page_title'         => '',       // 页面标题
		'page_tips'          => '',       // 页面提示
		'extra_html'         => '',       // 额外HTML代码
		'extra_js'           => '',       // 额外JS代码
		'extra_css'          => '',       // 额外CSS代码
		'right_buttons'      => [],       // 表格右侧按钮
		'columns'            => [],       // 表格列集合
		'pages'              => '',       // 分页数据
		'data_list'           => [],       // 表格数据列表
		'_page_info'         => '',       // 分页信息
		'primary_key'        => 'id',     // 表格主键名称
		'_table'             => '',       // 表名
		'js_list'            => [],       // js文件名
		'css_list'           => [],       // css文件名
		'_js_files'          => [],       // js文件
		'_css_files'         => [],       // css文件
		'_table_class_name'  => '',       // 表格样式
		'search_form_info'   => [],       // 搜索表单数据
		'search_form_text'   => [],       // input输入框
		'search_form_date'   => [],       // date日期输入框
		'search_form_select'   => [],     // select下拉框
		'page_header'       => '',        // 页面标题
		'page_header_small' => '',        // 页面副标题
		'm_js_path' => '',
		'm_css_path' => '',
		'bottom_btns' => [],              // 表格底部按钮
	];

	/**
	 * 初始化方法
	 */
	public function _initialize()
	{
		parent::_initialize();
		// 获取当前的模块控制器信息
		$this->_module     = $this->request->module(); // 模块名
		$this->_controller = $this->request->controller(); // 控制器名
		$this->_action     = $this->request->action(); // 方法
		$this->_table_name = strtolower($this->_module.'_'.trim(preg_replace("/[A-Z]/", "_\\0", $this->_controller), "_"));
		// 表格构建器的模板位置
		$this->_template   = ROOT_PATH. 'extend/WBuilder/builder/table/layout.html';

		// 定义静态文件夹位置
		define('__STATIC__' ,'/static');
		$this->_vars['m_js_path'] = __STATIC__ . '/' .$this->_module . '/js';
		$this->_vars['m_css_path'] = __STATIC__ . '/' .$this->_module . '/css';
	}

	/**
	 * 添加一列
	 * @param string $name 字段名称
	 * @param string $title 列标题
	 * @param string $type 单元格类型
	 * @param string $default 默认值
	 * @param string $param 额外参数
	 * @param string $class css类名
	 * @return $this
	 */
	public function addColumn($name = '', $title = '', $type = '', $default = '', $param = '', $class = '')
	{
		$column = [
			'name'    => $name,
			'title'   => $title,
			'type'    => $type,
			'default' => $default,
			'param'   => $param,
			'class'   => $class
		];

		$this->_vars['columns'][] = $column;
		return $this;
	}

	/**
	 * 增加右侧按钮
	 * @param string $type
	 * @param array $attribute
	 * @return $this
	 */
	public function addRightButton( $type = '' , $attribute = [])
	{
		// 定义按钮的属性
		$btn_attribute = [];
		switch ($type) {
			// 编辑按钮
			case 'edit':
				// 默认属性
				$btn_attribute = [
					'type'  => 'edit',
					'title' => '编辑',
					'class' => '',
					'href'  => url(
						$this->_module.'/'.$this->_controller.'/edit',
						[
							'id'  => '__id__'
						]
					)
				];
				break;
			// 启用/禁用按钮
			case 'status':
				// 默认属性
				$btn_attribute = [
					'type'  => 'status',
					'title' => '状态',
					'class' => 'ajax-get',
					'href'  => url($this->_module.'/'.$this->_controller.'/setStatus',
						[
							'id'   => '__id__'
						]
					),
				];
				break;

			// 删除按钮
			case 'delete':
				$btn_attribute = [
					'type'  => 'delete',
					'title' => '删除',
					'class' => 'ajax-get confirm',
					'href'  => url(
						$this->_module.'/'.$this->_controller.'/setStatus',
						[
							'id'   => '__id__',
							'status' => -1
						]
					)
				];
				break;

			// 自定义按钮
			case 'custom':
				// 默认属性
				$btn_attribute = [
					'type'  => 'custom',
					'title' => isset($attribute['title']) ? $attribute['title'] : '未定义',
					'class' => isset($attribute['class']) ? $attribute['class'] : '',
					'href'  => isset($attribute['href']) ? $attribute['href']:'javasript:;',
				];
				break;
			default:
				$this->error('未知类型');
		}
		$this->_vars['right_buttons'][] = $btn_attribute;
		return $this;
	}

	/**
	 * 编辑表格列
	 */
	private function compileRows()
	{
		foreach ($this->_vars['data_list'] as $key => &$row) {
			// 编译右侧按钮
			if ($this->_vars['right_buttons']) {
				// 设置默认值
				if (!isset($row['right_button'])) {
					$row['right_button'] = '';
				}
				foreach ($this->_vars['right_buttons'] as $button) {
					// 处理主键变量值
					$button['href'] = preg_replace(
						'/__id__/i',
						$row[$this->_vars['primary_key']],
						$button['href']
					);

					// 替换其他字段值
					if (preg_match_all('/__(.*?)__/', $button['href'], $matches)) {
						// 要替换的字段名
						$replace_to = [];
						$pattern    = [];
						foreach ($matches[1] as $match) {
							if (isset($row[$match])) {
								$pattern[]    = '/__'. $match .'__/i';
								$replace_to[] = $row[$match];
							}
						}
						$button['href'] = preg_replace(
							$pattern,
							$replace_to,
							$button['href']
						);
					}
					// 判断右侧按钮是否是启用/禁用状态
					if($button['type'] == 'status')
					{
						if($row['status'] == 0)
						{
							$button['title'] = '启用';
							$button['href'] = $button['href'] .'?status=1';
						}
						if($row['status'] == 1)
						{
							$button['title'] = '禁用';
							$button['href'] = $button['href'] .'?status=0';
						}
					}
					$row['right_button'] .= '<a class="'.$button['class'].'" href="'.$button['href'].'">'.$button['title'].'</a>&nbsp;';
				}

			}
			// 编译单元格数据类型
			if ($this->_vars['columns'])
			{
				foreach ($this->_vars['columns'] as $column) {
					// 备份原数据
					if (isset($row[$column['name']])) {
						$row['__'.$column['name'].'__'] = $row[$column['name']];
					}

					switch ($column['type']) {
						case 'status': // 状态
							switch ($row[$column['name']]) {
								case '0': // 禁用
									$info = isset($column['param'][0]) ? $column['param'][0] : '禁用';
									$row[$column['name']] = '<span class="label label-warning">'.$this->replaceFields($info , $row).'</span>';
									break;
								case '1': // 启用
									$info = isset($column['param'][1]) ? $column['param'][1] : '启用';
									$row[$column['name']] = '<span class="label label-success">'.$this->replaceFields($info , $row).'</span>';
									break;
							}
							break;
						case 'time':
						case 'datetime':
						case 'date': // 时间
							// 默认格式
							$format = 'Y-m-d H:i';
							switch($column['type'])
							{
								case 'date':
									$format = 'Y-m-d';
									break;
								case 'datetime':
									$format = 'Y-m-d H:i';
									break;
								case 'time':
									$format = 'H:i';
									break;
							}
							// 格式
							$format = $column['param'] == '' ? $format : $column['param'];
							if ($row[$column['name']] == '') {
								$row[$column['name']] = $column['default'];
							} else {
								$row[$column['name']] = date($format , $row[$column['name']]);
							}
							break;
						case 'picture': // 单张图片
							$row[$column['name']] = '<a href="'.($row[$column['name']]).'" target="_blank" title="'.($row[$column['name']]).'"><img style="width:100px;" class="" src="'.($row[$column['name']]).'"></a>';
							break;

						case 'callback': // 调用回调方法
							if ($column['param'] == '') {
								$params = [$row[$column['name']]];
							} else if ($column['param'] === '__data__') {
								$params = [$row[$column['name']], $row];
							} else {
								$params = [$row[$column['name']], $column['param']];
							}
							// 调用回调方法
							$row[$column['name']] = call_user_func_array($column['default'], $params);
							break;
						default: // 默认文本
							if (!isset($row[$column['name']]) && !empty($column['default'])) {
								$row[$column['name']] = $column['default'];
							}
							if (!empty($column['param'])) {
								if (isset($column['param'][$row[$column['name']]])) {
									$row[$column['name']] = $column['param'][$row[$column['name']]];
								}
							}
					}
				}
			}
		}
	}


	/**
	 * 设置表格标题
	 * @param string $page_title 表格标题
	 * @return $this
	 */
	public function setPageTitle($page_title = '')
	{
		if ($page_title != '') {
			$this->_vars['page_title'] = $page_title;
		}
		return $this;
	}

	/**
	 * 设置页面全局标题
	 * @param string $page_header 全局标题
	 * @return $this
	 */
	public function setPageHeader($page_header = '')
	{
		if ($page_header != '') {
			$this->_vars['page_header'] = $page_header;
		}
		return $this;
	}

	/**
	 * 设置页面全局标题
	 * @param string $page_header 全局标题
	 * @return $this
	 */
	public function setPageHeaderSmall($page_header_small = '')
	{
		if ($page_header_small != '') {
			$this->_vars['page_header_small'] = $page_header_small;
		}
		return $this;
	}

	/**
	 * 引入模块js文件
	 * @param string $files_name js文件名，多个文件用逗号隔开
	 * @author caiweiming <314013107@qq.com>
	 * @return $this
	 */
	public function js($files_name = '')
	{
		if ($files_name != '') {
			$this->loadJsCssFile('js', $files_name);
		}
		return $this;
	}

	/**
	 * 引入模块css文件
	 * @param string $files_name css文件名，多个用逗号隔开
	 * @return $this
	 */
	public function css($files_name = '')
	{
		if ($files_name != '') {
			$this->loadJsCssFile('css', $files_name);
		}
		return $this;
	}

	/**
	 * 引入css或js文件
	 * @param string $type 类型：css/js
	 * @param string $files_name 文件名，多个用逗号隔开
	 */
	private function loadJsCssFile($type = '', $files_name = '')
	{
		if ($files_name) {
			if (!is_array($files_name)) {
				$files_name = explode(',', $files_name);
			}
			foreach ($files_name as $item) {
				$this->_vars[$type.'_list'][] = $item;
			}
		}
	}

	/**
	 * 设置表格数据列表
	 * @param array $data_list 表格数据
	 * @return $this
	 */
	public function setRowList($data_list = [])
	{
		// 判断是否有列表数据
		if (!empty($data_list)) {
			if (is_array($data_list) && !empty($data_list)) {
				// 如果是数组类型，直接赋值即可
				$this->_vars['data_list'] = $data_list;
			} elseif (is_object($data_list) && !$data_list->isEmpty()) {
				// 带分页的对象类型，进行处理
				$this->_vars['data_list']   = is_object(current($data_list->getIterator())) ? $data_list : $data_list->all();
				$this->_vars['_page_info'] = $data_list;
				// 分页设置
				$this->_vars['pages'] = $data_list->render();
			}
		}
		return $this;
	}
	/**
	 * 设置分页
	 * @param string $pages 分页数据
	 * @return $this
	 */
	public function setPages($pages = '')
	{
		if ($pages != '') {
			$this->_vars['pages'] = $pages;
		}
		return $this;
	}
	/**
	 * 加载模板输出
	 * @param string $template 模板文件名
	 * @param array  $vars     模板输出变量
	 * @param array  $replace  模板替换
	 * @param array  $config   模板参数
	 * @return mixed
	 */
	public function fetch($template = '', $vars = [], $replace = [], $config = [])
	{
		// 编译表格列表数据
		$this->compileRows();
		if ($template != '') {
			$this->_template = $template;
		}
		// 实例化视图并渲染
		return parent::fetch($this->_template, $this->_vars, $replace, $config);
	}

	/**
	 * 字段替换，如__id__替换为最新的内容
	 * @param $str
	 * @param $row
	 * @return mixed
	 */
	private function replaceFields($str , $row)
	{
		// 查找是否有满足需求的字符串__字段名__
		if(preg_match_all('/__(.*?)__/', $str, $matches))
		{
			$replace_to = [];
			$pattern    = [];
			foreach ($matches[1] as $match) {
				if (isset($row[$match])) {
					$pattern[]    = '/__'. $match .'__/i';
					// 替换值赋值
					$replace_to[] = $row[$match];
				}
			}
			// 字段替换
			return preg_replace(
				$pattern,
				$replace_to,
				$str
			);
		}
		return $str;
	}
}