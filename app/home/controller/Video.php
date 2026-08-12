<?php
namespace app\home\controller;
use app\home\controller\Base;
use \think\Db;
class Video extends Base{

	public function detail(){
		$request = request();
		$id = $request->param('id');
		$video = Db::name('ad')->where(['ad_id'=>$id])->find();
		$corrles = Db::name('ad')->where(['ad_id'=>['IN',$video['ad_correlation']]])->select();
		$assgin = ['video'=>$video,'corrles'=>$corrles];
		return view('Video/detail',$assgin);
	}

}