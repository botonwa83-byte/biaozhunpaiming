<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;
use app\admin\model\TermRelationships;

class Hotword extends Auth {

	public function lists(){
		$hot_lists = Db::name('hotword')->paginate(config('PAGESIZE'));
		return view('Hotword/lists',['hot_lists'=>$hot_lists,'page'=>$hot_lists->render(),'page_min'=>$hot_lists->render()]);
	}

	public function addHotword(){
		if($param = request()->post()){
			$param['createtime'] = time();
			if(Db::name('hotword')->insert($param)){
				return ['status'=>1,'info'=>'添加热词成功','url'=>url('lists')];
			}else{
				return ['status'=>0,'info'=>'添加热词失败','url'=>url('addHotword')];
			}
		}
		return view('Hotword/add');
	}

	public function listorder(){
		$request = request();
		$param = $request->param();
		foreach($param['id'] as $k => $v){
			db('hotword')->where(['id'=>$k])->update(['listorder'=>$v]);
		}
		return ['status'=>1,'info'=>'排序成功！','url'=>''];
	}

}