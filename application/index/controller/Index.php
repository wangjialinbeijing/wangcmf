<?php
namespace app\index\controller;

use app\common\controller\Config;
use think\Controller;
use think\Db;

/*
 * 首页控制器
 */
class Index extends Controller
{
	/**
	 * 初始化方法
	 */
	protected function _initialize()
	{
		parent::_initialize();
		// 读取缓存中的配置
		$config =   cache('DB_CONFIG_DATA');
		if(!$config)
		{
			// 在数据库中读取所有的配置信息
			$config = Config::lists();
			cache('DB_CONFIG_DATA' , $config);
		}
		// 数据库配置合并到系统配置
		config($config);
	}

	/**
	 * 订单列表
	 * @return mixed
	 */
	public function order_list()
	{
		if(!session('USER_ID'))
		{
			$this->redirect(url('index/index'));
		}
		$map['user_id'] = session('USER_ID');
		$this->assign('_list' , Db::name('orders')->where($map)->order('create_time desc')->select());
		return $this->fetch();
	}

	/**
	 * 商品列表
	 * @return mixed
	 */
    public function index()
    {
    	// 查询条件
    	$map['status'] = 1;
    	// 商品列表（分页）
    	$this->assign('_list' , Db::name('goods')->where($map)->paginate(6));
		return $this->fetch();
    }

	/**
	 * 商品详情
	 * @return mixed
	 */
    public function detail()
    {
    	// 根据商品id查询详情
    	$map['id'] = input('id');
	    $info = Db::name('goods')->where($map)->find();
	    if(!$info)
	    {
	    	$this->error('参数错误！');
	    }
	    // 商品信息变量置换
	    $this->assign('info' , $info);
	    return $this->fetch();
    }

	/**
	 * 发布商品
	 * @return mixed
	 */
	public function add()
	{
		// 权限限制，只有超管才可以发布商品
		if(session('USER_ID') != intval(config('USER_ADMIN')))
		{
			$this->error('只有管理员才可以发布商品');
		}
		// 判断请求类型
		if($this->request->isPost())
		{
			// 获取请求的商品参数
			$data = $this->request->param();
			// 文件上传
			$image = $this->upload();
			// 基本的非空验证
			if(!$data['name'] ||!$data['sell_price']|| !$data['stock'])
			{
				$this->assign('error_tips' , '字段不能为空');
			}
			elseif(!$image)
			{
				$this->assign('error_tips' , '请上传缩略图');
			}
			else
			{
				// 写入数据到数据库
				$data['image'] = $image;
				$data['status'] = 1;
				$data['create_time'] = time();
				$data['user_id'] = session('USER_ID');
				$insertId = Db::name('goods')->insert($data);
				if($insertId)
				{
					$this->redirect('index/index');
				}
			}
		}
		return $this->fetch();
	}

	/**
	 * 文件上传
	 * @return string
	 */
	public function upload(){
		// 获取表单上传文件 例如上传了001.jpg
		$file = request()->file('image');

		// 移动到框架应用根目录/public/uploads/ 目录下
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
			if($info){
				return '/uploads/' . $info->getSaveName();
			}else{
				// 上传失败获取错误信息
				return $file->getError();
			}
		}
	}
}
