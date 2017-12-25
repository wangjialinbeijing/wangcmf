<?php
namespace app\admin\controller;

use think\Db;
use app\admin\validate\Config as ConfigValidate;

/**
 * 配置管理
 * Class Config
 * @package app\admin\controller
 */
class Config extends Admin
{
	public function test()
	{
		dump(config('ORDER_STATUS'));
	}

	/**
	 * 配置列表展示
	 * @return mixed
	 * @throws \think\exception\DbException
	 */
	public function index()
	{
		// 构造查询条件
		$map = [];
		$map['status'] = ['egt' , 0];
		// 查询分页数据
		$list = Db::name('config')->where($map)->order('create_time desc')->paginate(10);
		if($list)
		{
			// 变量置换
			$this->assign('_list' , $list);
		}
		// 模板输出渲染
		return $this->fetch();
	}

	/**
	 * 新增配置
	 * @return mixed
	 */
	public function add()
	{
		// 判断请求类型是否是POST
		if(request()->isPost())
		{
			// 初始化验证类
			$validate = new ConfigValidate();
			$result   = $validate->check(input('post.'));
			if(!$result)
			{
				// 错误信息提示
				$this->error($validate->getError());
			}
			// 构造数据
			$data = input('post.');
			$data['status'] = 1;
			$data['create_time'] = time();
			// 数据入库
			$insertId = Db::name('config')->insert($data);
			if($insertId)
			{
				// 更新缓存
				cache('DB_CONFIG_DATA' , null);
				$this->success('操作成功',url('config/index'));
			}
			$this->error('数据库操作失败');
		}
		// 置换当前菜单项显示地址
		$this->assign('active_url' , 'Config/index');
		return $this->fetch();
	}

	/**
	 * 编辑配置
	 * @return mixed
	 */
    public function edit()
    {
        $id = input('id' );                 // 获取配置id
        $map['id'] = $id;                   //　构建查询条件
        $map['status'] = ['egt' , 0];
        $info = Db::name('config')->where($map)->find();
        if($info === null)
        {
            $this->error('参数错误或查询为空');
        }
        $this->assign('info' ,$info);

        // 判断是否是POST
        if(request()->isPost())
        {
            $validate = new ConfigValidate();
            $result   = $validate->check(input('post.'));
            if(!$result)
            {
                $this->error($validate->getError());
            }
            // 构造数据
            $data = input('post.');
            $data['update_time'] = time();
            $map['id'] = $data['id'];
            // 数据入库
            $updateReturn = Db::name('config')->where($map)->update($data);
            if($updateReturn)
            {
	            // 更新缓存
	            cache('DB_CONFIG_DATA' , null);
                $this->success('操作成功',url('config/index'));
            }
            $this->error('数据库操作失败');
        }
        $this->assign('active_url' , 'Config/index');
        return $this->fetch();
    }

	/**
	 * 表记录状态迁移
	 */
	public function setConfigStatus()
	{
		// 更新缓存
		cache('DB_CONFIG_DATA' , null);
		return $this->setStatus(Db::name('config'));
	}
}
