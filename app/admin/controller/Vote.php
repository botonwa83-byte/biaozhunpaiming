<?php
namespace app\admin\controller;

use app\admin\controller\Auth;
use app\admin\model\Votes;

class Vote extends Auth{
	protected $vote_model;

	public function _initialize(){
		parent::_initialize();
		$this->vote_model = new Votes();
	}

	public function lists(){
		$request = request();
		$list = $this->vote_model->getVotes($request);
		$assign = [
			'votes' => $list['votes'],
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'page' => $list['page'],
			'page_min' => $list['page_min'],
		];
		return view('Vote/lists',$assign);
	}

	public function add(){
		return view('Vote/add');
	}

	public function runadd(){
		$request = request();
		if($this->vote_model->addVote($request)){
			return ['status'=>1,'info'=>'添加投票成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'添加投票失败','url'=>url('add')];
		}
	
	}

	public function edit(){
		$id = request()->param('id');
		$assign = [
			'vote' => $this->vote_model->getVote($id,false),
		];
		return view('Vote/edit',$assign);
	}

	public function runEdit(){
		$request = request();
		if($this->vote_model->editVote($request)){
			return ['status'=>1,'info'=>'修改成功！','url'=>''];
		}else{
			return ['status'=>1,'info'=>'修改成功！','url'=>''];
		}
	}

	public function delOption(){
		$id = request()->param('id');
		if($this->vote_model->delOptions($id)){
			return ['status'=>1,'info'=>'删除成功！'];
		}else{
			return ['status'=>0,'info'=>'删除失败！'];
		}
	}

	public function optionState(){
		$id = request()->param('x');
		$status = db('vote_answer')->field('status')->where(array('id'=>$id))->find();
		//判断当前状态情况
		if($status['status']==1){
			$statedata = array('status'=>0);
			$auth_group = db('vote_answer')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('status'=>1);
			$auth_group = db('vote_answer')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function delQeustion(){
		$id = request()->param('id');
		if(db('vote_question')->where(['id'=>$id])->update(['status'=>0])){
			return ['status'=>1,'info'=>'删除成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'删除失败！'];
		}
	}

	public function del(){
		$id = request()->param('id');
		$p = request()->param('p');
		if(db('vote')->where(['id'=>$id])->delete()){
			return ['status'=>1,'info'=>'删除成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'删除失败！','url'=>''];
		}
	}

	public function alldel(){
		$param = request()->param();
		$ids = implode(',',$param['id']);
		if(db('vote')->where(['id'=>['IN',$ids]])->delete()){
			return ['status'=>1,'info'=>'删除成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'删除失败！','url'=>''];
		}
	}

	public function state(){
		$id = request()->param('x');
		$status = db('vote')->field('vote_status')->where(array('id'=>$id))->find();//判断当前状态情况
		if($status['vote_status']==1){
			$statedata = array('vote_status'=>0);
			$auth_group = db('vote')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('vote_status'=>1);
			$auth_group = db('vote')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function votes_back(){
		$keytype = I('keytype','votes_title');
		$key = I('key');
		$opentype_check = I('opentype_check','');
		//查询：时间格式过滤
		$sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
        if(count($arr)==2){
            $arrdateone=strtotime($arr[0]);
            $arrdatetwo=strtotime($arr[1].' 23:55:55');
            $map['votes_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
		//map架构查询条件数组
		$map['votes_back']= 0;
        if($keytype=='votes_title'){
            $map[$keytype]= array('like',"%".$key."%");
        }else{
            $map[$keytype]= $key;
        }
		if ($opentype_check!=''){
			$map['votes_open']= array('eq',$opentype_check);
		}
        $rs =  M('Votes');
		$count= $rs->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,C('DB_PAGENUM'));// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
		$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$votes=$rs->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('votes_modified desc')->select();
		$this->assign('opentype_check',$opentype_check);
		$this->assign('keytype',$keytype);
		$this->assign('keyy',$key);
		$this->assign('sldate',$sldate);
		$this->assign('votes',$votes);
		$this->display();
	}

	public function listorder(){
		$request = request();
		$param = $request->param();
		foreach($param['id'] as $k => $v){
			db('vote')->where(['id'=>$k])->update(['q_order'=>$v]);
		}
		return ['status'=>1,'info'=>'排序成功！','url'=>''];
	}

	public function voteDataShow(){
		$request = request();
		$id = $request->param('id');
		$vote = $this->vote_model->getVote($id,false);
		$html = '<table><thead><th>序号</th><th>品牌名</th><th>得票数</th><th>得票数</th></thead>';
		foreach($vote['question'] as $k => $v){
			foreach($v['answers'] as $kk => $vv){
				$html .= '<tr>';
				$html .= '<td>'.$vv['id'].'</td>';
				$html .= '<td>'.$vv['content'].'</td>';
				$html .= '<td>'.$vv['size'].'</td>';
				$html .= '<td>'.$vv['size_modified'].'</td>';
				$html .='</tr>';
			}
		}
		$html .= '</table>';
		echo $html;
	}

}