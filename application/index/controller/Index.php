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
	 * 商品列表
	 * @return mixed
	 */
    public function index()
    {
    	$map['status'] = 1;
    	$this->assign('_list' , Db::name('goods')->where($map)->paginate(6));
		return $this->fetch();
    }

	/**
	 * 商品详情
	 * @return mixed
	 */
    public function detail()
    {
    	$map['id'] = input('id');
	    $info = Db::name('goods')->where($map)->find();
	    if(!$info)
	    {
	    	$this->error('参数错误！');
	    }
	    $this->assign('info' , $info);
	    return $this->fetch();
    }

	/**
	 * 发布商品
	 * @return mixed
	 */
	public function add()
	{
		if(session('USER_ID') != intval(config('USER_ADMIN')))
		{
			$this->error('只有管理员才可以发布商品');
		}

		if($this->request->isPost())
		{
			$data = $this->request->param();
			$image = $this->upload();
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
