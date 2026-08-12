<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;
use app\admin\model\TermRelationships;

class Ad extends Auth {
	public function index(){
		$param = request()->param();
		$lists = Db::name('ad')->order('ad_createtime desc')->paginate(config('PAGESIZE'),false,['query'=>$param]);
		$terms = TermRelationships::getTerms();
		return view('Ad/index',['lists'=>$lists,'page'=>$lists->render(),'page_min'=>$lists->render(),'terms'=>$terms]);
	}

	public function adState(){
		$request = request()->param();
		$status = Db::name('ad')->where(['ad_id'=>$request['x']])->find();
		$status = $status['ad_status'];
        if($status == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        Db::name('ad')->where(['ad_id'=>$request['x']])->update(['ad_status'=>$status]);
		if($status){
			return ['status'=>1,'info'=>'网页显示','url'=>$status];
		}else{
			return ['status'=>1,'info'=>'网页不显示','url'=>$status];
		}
	}

	public function adRunadd(){
		$posts = request()->post();
		$file = request()->file('ad_image');
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$posts['ad_image'] = '/data/upload/'.$info->getSaveName();
		}
		$posts['ad_createtime'] = time();
		$posts['ad_hit'] = rand(1,100);
		if(Db::name('ad')->insert($posts)){
			return ['status'=>1,'info'=>'添加成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'添加失败！','url'=>''];
		}
	}

	public function adEdit(){
		$request = request()->param();
		$ad = Db::name('ad')->where(['ad_id'=>$request['ad_id']])->find();
		$terms = TermRelationships::getTerms();
		return view('Ad/adEdit',['ad'=>$ad,'terms'=>$terms]);
	}

	public function runEdit(){
		$posts = request()->post();
		$file = request()->file('ad_image');
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$posts['ad_image'] = '/data/upload/'.$info->getSaveName();
		}
		if(Db::name('ad')->update($posts)){
			return ['status'=>1,'info'=>'修改成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'修改失败！','url'=>''];
		}
	}
}