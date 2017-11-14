<?php
namespace app\admin\controller;

use think\Db;
use app\admin\validate\Config as ConfigValidate;

class Config extends Admin
{
	public function group()
	{
		echo date('Y-m-d H:i:s');
//		return $this->fetch('config/index');
	}

	// 配置列表
	public function index()
	{
		$map = [];
		$map['status'] = ['egt' , 0];
		$list = Db::name('config')->where($map)->order('create_time desc')->paginate(10);
		if($list)
		{
			$this->assign('_list' , $list);
		}
		return $this->fetch();
	}

	// 新增配置
	public function add()
	{
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
			$data['status'] = 1;
			$data['create_time'] = time();
			// 数据入库
			$insertId = Db::name('config')->insert($data);
			if($insertId)
			{
				$this->success('操作成功',url('config/index'));
			}
			$this->error('数据库操作失败');
		}
		$this->assign('active_url' , 'Config/index');
		return $this->fetch();
	}

    // 新增配置
    public function edit()
    {
        $id = input('id' );
        $map['id'] = $id;
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
		return $this->setStatus(Db::name('config'));
	}
}
