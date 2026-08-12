<?php
namespace app\admin\controller;
use think\Db;
use app\admin\controller\Auth;
use app\home\model\Nav;

class Navcat extends Auth {

	public function index(){
		$data = db('nav')->where("parentid = 0")->select();
		return view("Nav/index",['data'=>$data]);
	}

	public function edit(){
		$id =request()->param('id');
		$data = db('nav')->where(['id'=>$id])->find();
		return view("Nav/edit",['data'=>$data]);
	}

	public function runedit(){
		$sl_data = request()->post();
		$sl_data['status'] = isset($sl_data['status'])?$sl_data['status']:0;
		// $file = request()->file('smeta');
		// if($file){
		// 	$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
		// 	$sl_data['smeta'] = '/data/upload/'.$info->getSaveName();
		// }
		$result = db('nav')->update($sl_data);
		if($result){
			db('terms')->where(['term_id'=>$sl_data['id']])->update(['name'=>$sl_data['label']]);
			if(empty($navs)){
	            $nav_model = new Nav();
	            $old_navs = $nav_model->getNav(1);
	            foreach($old_navs as $k => $v){
	                $navs[$v->id] = $v;
	            }
	            cache('navs',$navs,1800);
	        }
			return ['info'=>'修改成功！','status'=>1,'url'=>''];
		}else{
			return ['info'=>'修改失败！','status'=>0,'url'=>''];
		}
	}

	public function listorder(){
		$sl_data = request()->param();
		foreach($sl_data['id'] as $k => $v){
			$result = db('nav')->where(['id'=>$k])->update(['listorder'=>$v]);
		}
		if(empty($navs)){
            $nav_model = new Nav();
            $old_navs = $nav_model->getNav(1);
            foreach($old_navs as $k => $v){
                $navs[$v->id] = $v;
            }
            cache('navs',$navs,1800);
        }
		if($result){
			return ['info'=>'修改成功！','status'=>1,'url'=>''];
		}else{
			return ['info'=>'修改失败！','status'=>0,'url'=>''];
		}

	}

	public function state(){
		$param = request()->param();
		$meun = Db::name('nav')->where(['id'=>$param['x']])->find();
		$param['status'] = $meun['status']==1?0:1;
		$param['id'] = $param['x'];
		unset($param['x']);
		Db::name('nav')->update($param);
		if(empty($navs)){
            $nav_model = new Nav();
            $old_navs = $nav_model->getNav(1);
            foreach($old_navs as $k => $v){
                $navs[$v->id] = $v;
            }
            cache('navs',$navs,1800);
        }
		if($param['status']){
			return ['status'=>1,'info'=>'已审','url'=>''];
		}else{
			return ['status'=>1,'info'=>'未审','url'=>''];
		}
	}

}