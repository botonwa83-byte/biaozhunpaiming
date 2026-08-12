<?php

namespace app\admin\model;
use think\Model;
use think\Db;
class Articles extends Model
{
	protected $table = 'bzpm_posts';

	protected function initialize()
	{
		parent::initialize();
		
	}

    public function add($sldate)
    {
       return $this->insertGetId($sldate);
    }

}