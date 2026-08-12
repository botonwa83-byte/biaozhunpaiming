<?php
namespace app\home\controller;
use app\home\controller\Base;
use \think\Db;
class Apply extends Base{

	public function detail(){
		$request = request();
		$id = $request->param('id');
		$apply = Db::name('apply')->where(['id'=>$id])->find();
		$apply['options'] = unserialize($apply['options']);
		$apply['apply_case'] = unserialize($apply['apply_case']);
		$assgin = ['apply'=>$apply];
		return view('Apply/detail',$assgin);
	}

}