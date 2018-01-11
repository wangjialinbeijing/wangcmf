<?php
namespace app\admin\controller;

use Michelf\Markdown;

class Index extends Admin
{
	// 显示README.md
    public function index()
    {
	    $html = Markdown::defaultTransform(file_get_contents(ROOT_PATH . 'README.md'));
	    $this->assign('content' , $html);
        return $this->fetch();
    }
}
