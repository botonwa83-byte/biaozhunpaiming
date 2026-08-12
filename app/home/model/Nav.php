<?php

namespace app\home\model;
use think\Model;
class Nav extends Model
{
	protected $table = 'bzpm_nav';

	protected function _initialize()
	{
		parent::_initialize();
		//TODO:自定义的初始化
	}

	public function getNav($nav_cat_id){
		$navs = $this->where("cid = $nav_cat_id and status = 1")->order(array("listorder" => "ASC"))->select();
        return $navs;
	}
	
}
