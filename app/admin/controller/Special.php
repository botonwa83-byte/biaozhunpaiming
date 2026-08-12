<?php
namespace app\admin\controller;
use app\admin\controller\Auth;
use app\admin\model\TermRelationships;
class Special extends Auth{

	public function index(){
		$request = request();
		$map = [];
		if($param = $request->param()){
            $keytype = isset($param['keytype'])?$param['keytype']:'';
            $key = isset($param['key'])?$param['key']:'';
            $opentype_check = isset($param['opentype_check'])?$param['opentype_check']:'';
            $diyflag = isset($param['diyflag'])?$param['diyflag']:'';
            $sldate = isset($param['reservation'])?$param['reservation']:'';
            $page = isset($param['p'])?$param['p']:1;
            $arr = explode(" - ",$sldate);
            if(count($arr)==2){
                $arrdateone = strtotime($arr[0]);
                $arrdatetwo = strtotime($arr[1].' 23:55:55');
                $map['createtime'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
            }
            if($keytype == 'title'){
                $map[$keytype]= array('like',"%".$key."%");
            }elseif($keytype){
                $map[$keytype] = $key;
            }
            if ($opentype_check!=''){
                $map['vote_status']= array('eq',$opentype_check);
            }
        }

		$specials = db('special')->where($map)
		->order('createtime desc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	$assign = [
			'specials' => $specials,
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'page' => $specials->render(),
			'page_min' => $specials->render(),
		];
		return view('Special/lists',$assign);
	}

	public function add(){
		return view('Special/add');
	}

	public function person(){
		$request = request();
		// $map['status'] = 1;
		$map = [];
		if($param = $request->param()){
            $keytype = isset($param['keytype'])?$param['keytype']:'';
            $key = isset($param['key'])?$param['key']:'';
            $opentype_check = isset($param['opentype_check'])?$param['opentype_check']:'';
            $diyflag = isset($param['diyflag'])?$param['diyflag']:'';
            $sldate = isset($param['reservation'])?$param['reservation']:'';
            $page = isset($param['p'])?$param['p']:1;
            $arr = explode(" - ",$sldate);
            if(count($arr)==2){
                $arrdateone = strtotime($arr[0]);
                $arrdatetwo = strtotime($arr[1].' 23:55:55');
                $map['createtime'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
            }
            if($keytype == 'title'){
                $map[$keytype]= array('like',"%".$key."%");
            }elseif($keytype){
                $map[$keytype] = $key;
            }
            if ($opentype_check!=''){
                $map['vote_status']= array('eq',$opentype_check);
            }
        }
        $map['status'] =1;
		$personals = db('personal')->where($map)
		->order('id desc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	$assign = [
			'personals' => $personals,
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'page' => $personals->render(),
			'page_min' => $personals->render(),
		];
		return view('Special/person',$assign);
	}

	public function personAdd(){
		$request = request();
		if($request->isPost()){
			$sl_data['name'] = $request->param('name');
			$sl_data['description'] = $request->param('description');
			$sl_data['createtime'] = strtotime($request->param('createtime'));
			$sl_data['pstatus'] = $request->param('pstatus')?$request->param('pstatus'):0;
			$sl_data['status'] = $request->param('status')?$request->param('status'):0;
			$file = $request->file('smeta');
			if($file){
				$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
				$sl_data['smeta'] = '/data/upload/'.$info->getSaveName();
			}
			if(db('personal')->insert($sl_data)){
				return ['status'=>1,'info'=>'添加人物成功！','url'=>url('person')];
			}else{
				return ['status'=>0,'info'=>'添加人物失败！','url'=>''];
			}
		}
		return view('Special/personAdd');
	}

	public function personEdit(){
		$request = request();
		if($request->isPost()){
			$sl_data['id'] = $request->param('id');
			$sl_data['name'] = $request->param('name');
			$sl_data['description'] = $request->param('description');
			$sl_data['createtime'] = strtotime($request->param('createtime'))?strtotime($request->param('createtime')):time();
			$sl_data['pstatus'] = $request->param('pstatus')?$request->param('pstatus'):0;
			$sl_data['status'] = $request->param('status')?$request->param('status'):0;
			$file = $request->file('smeta');
			if($file){
				$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
				$sl_data['smeta'] = '/data/upload/'.$info->getSaveName();
			}
			if(db('personal')->update($sl_data)){
				return ['status'=>1,'info'=>'修改人物成功！','url'=>url('person')];
			}else{
				return ['status'=>0,'info'=>'修改人物失败！','url'=>''];
			}
		}else{
			$id = $request->param('id');
			$data = db('personal')->where(['id'=>$id])->find();
			return view('Special/personEdit',['data'=>$data]);
		}
	}

	public function delPerson($id){
		if(db('personal')->where(['id'=>$id])->update(['status'=>0])){
			return ['status'=>1,'info'=>'删除成功！','url'=>url('person')];
		}else{
			return ['status'=>0,'info'=>'删除失败！','url'=>''];
		}
	}

	public function runadd(){
		$request = request();
		$sl_data = $request->param();
		$files = $request->file();
		if($head_img = $files['img']){
			$info_head = $head_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$sl_data['smeta'] = '/data/upload/'.$info_head->getSaveName();
		}
		unset($sl_data['img']);
		if($head_list_pic = $files['list_pic']){
			$info_head = $head_list_pic->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$sl_data['list_pic'] = '/data/upload/'.$info_head->getSaveName();
		}
		$sl_data['createtime'] = strtotime($sl_data['createtime']);
		$sl_data['status'] = isset($sl_data['status'])?$sl_data['status']:0;
		$sl_data['other_name'] = isset($sl_data['other_name'])?serialize($sl_data['other_name']):null;
		$sl_data['other_ids'] = isset($sl_data['other_ids'])?serialize($sl_data['other_ids']):null;
		if(db('special')->insert($sl_data)){
			return ['status'=>1,'info'=>'添加专题成功！','url'=>url('index')];
		}else{
			return ['status'=>0,'info'=>'添加专题失败！','url'=>''];
		}
	}

	public function state(){
		$id = request()->param('x');
		$status = db('special')->field('status')->where(array('id'=>$id))->find();//判断当前状态情况
		if($status['status']==1){
			$statedata = array('status'=>0);
			$auth_group = db('special')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('status'=>1);
			$auth_group = db('special')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function edit(){
		$id = request()->param('id');
		$special = db('special')->where(['id'=>$id])->find();
		$term_model = new TermRelationships(); 
		$special['corr_aritlces'] = $term_model->getStringArticles($special['correlation']);
		if(isset($special['other_name'])){
			$special['other_name'] = unserialize($special['other_name']);
		}
		if(isset($special['other_ids'])){
			$special['other_ids'] = unserialize($special['other_ids']);
		}
		return view('Special/edit',['special'=>$special]);
	}

	public function runedit(){
		$request = request();
		$sl_data = $request->param();
		$files = $request->file();
		if(isset($files['smeta']) && ($head_img = $files['smeta'])){
			$info_head = $head_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$sl_data['smeta'] = '/data/upload/'.$info_head->getSaveName();
		}
		if(isset($files['list_pic']) && ($head_list_pic = $files['list_pic'])){
			$info_head = $head_list_pic->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$sl_data['list_pic'] = '/data/upload/'.$info_head->getSaveName();
		}
		$sl_data['createtime'] = strtotime($sl_data['createtime']);
		$sl_data['status'] = isset($sl_data['status'])?$sl_data['status']:0;
		if(isset($sl_data['other_name'])){
			$sl_data['other_name'] = serialize($sl_data['other_name']);
		}
		if(isset($sl_data['other_ids'])){
			$sl_data['other_ids'] = serialize($sl_data['other_ids']);
		}
		if(isset($sl_data['specialorder']) && !empty($sl_data['specialorder'])){
			$term_db_model = db('term_relationships');
			foreach($sl_data['specialorder'] as $k => $v){
				$term_db_model->where(['tid'=>$k])->update(['specialorder'=>$v]);
			}
			unset($sl_data['specialorder']);
		}
		db('special')->update($sl_data);
		return ['status'=>1,'info'=>'修改专题成功！','url'=>url('index')];
	}

	public function videolists(){
		$request = request();
		$map = [];
		if($param = $request->param()){
            $keytype = isset($param['keytype'])?$param['keytype']:'';
            $key = isset($param['key'])?$param['key']:'';
            $opentype_check = isset($param['opentype_check'])?$param['opentype_check']:'';
            $diyflag = isset($param['diyflag'])?$param['diyflag']:'';
            $sldate = isset($param['reservation'])?$param['reservation']:'';
            $page = isset($param['p'])?$param['p']:1;
            $arr = explode(" - ",$sldate);
            if(count($arr)==2){
                $arrdateone = strtotime($arr[0]);
                $arrdatetwo = strtotime($arr[1].' 23:55:55');
                $map['createtime'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
            }
            if($keytype == 'title'){
                $map[$keytype]= array('like',"%".$key."%");
            }elseif($keytype){
                $map[$keytype] = $key;
            }
            if ($opentype_check!=''){
                $map['vote_status']= array('eq',$opentype_check);
            }
        }
		$specials = db('special_video')->where($map)
		->order('id desc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	$assign = [
			'specials' => $specials,
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'page' => $specials->render(),
			'page_min' => $specials->render(),
		];
		return view('Special/vlists',$assign);
	}

	public function videoAdd(){
		$request = request();
		if($request->isPost()){
			$sl_data = $request->param();
			if(db('special_video')->insert($sl_data)){
				return ['status'=>1,'info'=>'添加视频成功！','url'=>url('videolists')];
			}else{
				return ['status'=>0,'info'=>'添加视频失败！','url'=>''];
			}
		}
		return view('Special/vadd');
	}

	public function delVideo($id){
		if(db('special_video')->where(['id'=>$id])->delete()){
			return ['status'=>1,'info'=>'删除成功！','url'=>url('videolists')];
		}else{
			return ['status'=>0,'info'=>'删除失败！','url'=>''];
		}
	}

	public function del($id){
		if(db('special')->where(['id'=>$id])->delete()){
			return ['status'=>1,'info'=>'删除成功！','url'=>url('index')];
		}else{
			return ['status'=>0,'info'=>'删除失败！','url'=>''];
		}
	}

}