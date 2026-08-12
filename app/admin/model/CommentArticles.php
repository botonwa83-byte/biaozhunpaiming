<?php
namespace app\admin\model;

use think\Model;
use think\Db;
class CommentArticles extends Model
{
	protected $table = 'bzpm_comments';

	public function getCommentAlllist()
	{
		$count = $this->where(['status'=>1,'back'=>1,'type'=>1])->count();
		$Page= new \Page\Page($count,config('PAGESIZE'));
		$show = $Page->show();
		$comments = $this->where(['status'=>1,'back'=>1,'type'=>1])->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		return ['comments'=>$comments,'page'=>$show];
	}

	public function getVoteAlllist()
	{
		$count = $this->where(['status'=>1,'back'=>1,'type'=>2])->count();
		$Page= new \Page\Page($count,config('PAGESIZE'));
		$show = $Page->show();
		$comments = $this->where(['status'=>1,'back'=>1,'type'=>2])->order('createtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		return ['comments'=>$comments,'page'=>$show];
	}

}