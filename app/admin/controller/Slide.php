<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;

class Slide extends Auth {
	public function index(){
		$param = request()->param();
		$slides = Db::name('slide')->select();
		$cat = Db::name('slide_cat')->where(['cat_status'=>1])->select();
		foreach($cat as $k => $v){
			$cats[$v['cid']] = $v['cat_name'];
		}
		return view('Slide/index',['slides'=>$slides,'cats'=>$cats]);
	}

	public function edit(){
		$id = request()->param('slide_id');
		$slide = Db::name('slide')->where(['slide_status'=>1,'slide_id'=>$id])->find();
		$cats = Db::name('slide_cat')->where(['cat_status'=>1])->select();
		return view('Slide/edit',['slide'=>$slide,'cats'=>$cats]);
	}

	public function runedit(){
		$param = request()->param();
		$file = request()->file('file0');
		unset($param['file0']);
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$param['slide_pic'] = '/data/upload/'. $info->getSaveName();
		}
		// $vote_model = Db::name('vote');
		Db::name('vote')->where(['id'=>$param['slide_url']])->update(['vote_top'=>0]);
		if(Db::name('slide')->update($param)){
			Db::name('vote')->where(['id'=>$param['slide_url']])->update(['vote_top'=>1]);
			return ['status'=>1,'info'=>'修改成功','url'=>url('index')];
		}else{
			return ['status'=>0,'info'=>'修改失败','url'=>''];
		}
	}

	public function add(){
		$cats = Db::name('slide_cat')->where(['cat_status'=>1])->select();
		return view('Slide/add',['cats'=>$cats]);
	}

	public function runadd(){
		$param = request()->param();
		$file = request()->file('file0');
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$param['slide_pic'] = '/data/upload/'. $info->getSaveName();
		}
		unset($param['file0']);
		if(Db::name('slide')->insert($param)){
			return ['status'=>1,'info'=>'添加成功','url'=>url('index')];
		}else{
			return ['status'=>0,'info'=>'添加失败','url'=>''];
		}
	}

	public function state(){
		$param = request()->param();
		$slide_status = Db::name('slide')->field('slide_status')->where(['slide_id'=>$param['x']])->find();
		if($slide_status['slide_status'] == 1){
			$status = 0;
		}else{
			$status = 1;
		}
		Db::name('slide')->where(['slide_id'=>$param['x']])->update(['slide_status'=>$status]);
		if($status){
			return ['status'=>1,'info'=>'已审','url'=>1];
		}else{
			return ['status'=>1,'info'=>'未审','url'=>1];
		}
	}

	public function listorder(){
		$param = request()->param();
		foreach($param['listorder'] as $k => $v){
			db('slide')->where(['slide_id'=>$k])->update(['listorder'=>$v]);
		}
		$this->redirect('index');
	}

	public function del($slide_id){
		if(Db::name('slide')->where(['slide_id'=>$slide_id])->delete()){
			return ['status'=>1,'info'=>'删除成功','url'=>url('index')];
		}else{
			return ['status'=>0,'info'=>'删除失败','url'=>''];
		}
	}
}