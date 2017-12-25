<?php
/**
 * Created by PhpStorm.
 * User: wangjialin
 * Date: 2017/11/2 0002
 * Time: 13:24
 */
namespace  app\common\controller;

use think\Controller;
use think\Db;

class Config extends Controller
{
	/**
	 * 获取数据库中的配置列表
	 * @return array 配置数组
	 */
	public static function lists(){
		$map    = array('status' => 1);
		$data   = Db::name('Config')->where($map)->field('type,name,value')->select();

		$config = [];
		if($data && is_array($data)){
			foreach ($data as $value) {
				$config[$value['name']] = self::parse($value['type'], $value['value']);
			}
		}
		return $config;
	}

	/**
	 * @param $type 配置项类型，0：字符串，1：数组类型
	 * @param $value 配置项值
	 * @return array|false|string[]
	 */
	private static function parse($type, $value){
		switch ($type) {
			case 1: //解析数组
				$array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
				if(strpos($value,':')){
					$value  = array();
					foreach ($array as $val) {
						list($k, $v) = explode(':', $val);
						$value[$k]   = $v;
					}
				}else{
					$value = $array;
				}
				break;
		}
		return $value;
	}
}