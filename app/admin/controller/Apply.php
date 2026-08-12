<?php
namespace app\admin\controller;
use app\admin\controller\Auth;

class Apply extends Auth{

	public function lists(){
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

		$applys = db('apply')->where($map)
		->order('createtime desc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	$assign = [
			'applys' => $applys,
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'page' => $applys->render(),
			'page_min' => $applys->render(),
		];
		return view('Apply/lists',$assign);
	}

	public function add(){
		return view('Apply/add');
	}

	public function runadd(){
		$request = request();
		$apply = $request->param();
		$files = $request->file();
		if($head_img = $files['smeta']){
			$info_head = $head_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$apply['smeta'] = '/data/upload/'.$info_head->getSaveName();
		}
		$apply['createtime'] = strtotime($apply['createtime']);
		$apply['content'] = htmlspecialchars_decode($apply['content']);
		$apply['options'] = serialize($apply['options']);
		$apply['apply_case'] = serialize($apply['apply_case']);
		$result = db('apply')->insert($apply);
		if($result){
			return ['status'=>1,'info'=>'添加成功！','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'添加失败！','url'=>''];
		} 
	}

	public function edit(){
		$request = request();
		$id = $request->param('id');
		$apply = db('apply')->where(['id'=>$id])->find();
		$apply['options'] = unserialize($apply['options']);
		$apply['apply_case'] = unserialize($apply['apply_case']);
		return view('Apply/edit',['apply'=>$apply]);
	}

	public function runedit(){
		$request = request();
		$apply = $request->param();
		$files = $request->file();
		if($head_img = $files['smeta']){
			$info_head = $head_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$apply['smeta'] = '/data/upload/'.$info_head->getSaveName();
		}
		$apply['createtime'] = strtotime($apply['createtime']);
		$apply['content'] = htmlspecialchars_decode($apply['content']);
		$apply['options'] = serialize($apply['options']);
		$apply['apply_case'] = serialize($apply['apply_case']);
		$result = db('apply')->update($apply);
		if($result){
			return ['status'=>1,'info'=>'修改成功！','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'修改失败！','url'=>''];
		} 
	}

}