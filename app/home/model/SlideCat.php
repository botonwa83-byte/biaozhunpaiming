<?php

namespace app\home\model;
use think\Model;
class SlideCat extends Model
{
	protected $table = 'bzpm_slide_cat';

	protected function _initialize()
	{
		parent::_initialize();
	}

	public function getHomeSlide($slide_name){
		$home_slides = $this->alias('a')->join(' bzpm_slide b','a.cid = b.slide_cid','LEFT')
        ->where(" cat_idname = '$slide_name' and slide_status = 1")
        ->limit(5)
        ->order('listorder ASC')
        ->select();
        return $home_slides;
	}
	
}
