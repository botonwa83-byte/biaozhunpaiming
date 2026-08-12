<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;

class Report extends Auth {

	public function lists(){
		$param = request()->param();
		$lists = Db::name('report')->paginate(config('PAGESIZE'),false,['query'=>$param]);
		return view('Report/lists',['lists'=>$lists,'page'=>$lists->render(),'page_min'=>$lists->render()]);
	}

	public function add(){
		return view('Report/add');
	}

	public function runadd(){
		$request = request();
		$file = $request->file();
		$sl_data = $request->param();
		$sl_data['createtime'] = time();
		if($file){
			if(isset($file['report_file']) && ($report_file = $file['report_file'])){
				$info = $report_file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'backup','');
				$sl_data['report_file'] = $info->getSaveName();
			}
			if(isset($file['smeta']) && ($smeta = $file['smeta'])){
				$info = $smeta->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
				$sl_data['smeta'] = '/data/upload/'.$info->getSaveName();
			}
		}
		if(Db::name('report')->insert($sl_data)){
			return ['status'=>1,'info'=>'添加成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'添加失败','url'=>''];
		}
	}

	public function edit(){
		$id = request()->param('id');
		$report = Db::name('report')->where(['id'=>$id])->find();
		return view('Report/edit',['report'=>$report]);
	}

	public function runedit(){
		$request = request();
		$file = $request->file();
		$sl_data = $request->param();
		if($file){
			if(isset($file['report_file']) && ($report_file = $file['report_file'])){
				$info = $report_file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'backup','');
				$sl_data['report_file'] = $info->getSaveName();
			}
			if(isset($file['smeta']) && ($smeta = $file['smeta'])){
				$info = $smeta->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
				$sl_data['smeta'] = '/data/upload/'. $info->getSaveName();
			}
		}
		if(Db::name('report')->update($sl_data)){
			return ['status'=>1,'info'=>'修改成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'修改失败','url'=>''];
		}
	}

	public function adstate(){
		$id = request()->param('x');
		$status = db('report')->field('status')->where(array('id'=>$id))->find();//判断当前状态情况
		if($status['status']==1){
			$statedata = array('status'=>0);
			$auth_group = db('report')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('status'=>1);
			$auth_group = db('report')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function ad_del($id=null){
		Db::name('report')->where(['id'=>$id])->delete();
		return ['status'=>1,'info'=>'成功','url'=>''];
	}

}