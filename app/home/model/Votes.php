<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Votes extends Model{
	protected $table = 'bzpm_vote';

	public function _initialize(){
		parent::_initialize();
	}

	public function getVote($id,$order=false){
		$vote =	$this->where(['id'=>$id])->find();
		$question = Db::name('vote_question')->where(['vid'=>$id,'status'=>1])->select();
		foreach($question as $k => $v){
			$count = 0;
			$answers = Db::name('vote_answer')->where(['q_id'=>$v['id'],'status'=>1])->order('a_order asc')->select();
			if($order === true){
				foreach($answers as $kk => $vv){
					$count += $vv['size_modified'];
				}
				usort($answers,function($a,$b){return $b['size_modified']-$a['size_modified'];});
			}
			$question[$k]['count'] = $count;
			$question[$k]['answers'] = $answers;
		}
		$vote['smeta'] = json_decode($vote['smeta'],true);
		$vote['question'] = $question;
		return $vote;
	}

	public function votePost($param,$ip=0){
		$new_choices = [];
		$sl_data = [];
		$time = time();
		foreach($param['choices'] as $k => $v){
			if(is_array($v)){
				foreach($v as $vv){
					$sl_data[] = [
						'vote_user' => session('uid'),
						'vote_ip' => $ip,
						'vote_id' => $param['id'],
						'answer_id' => $vv,
						'record_status' => 1,
						'createtime' => $time
					];
					$new_choices[] = $vv;
				}
			}else{
				$sl_data[] = [
					'vote_user' => session('uid'),
					'vote_ip' => $ip,
					'vote_id' => $param['id'],
					'answer_id' => $v,
					'record_status' => 1,
					'createtime' => $time
				];
				$new_choices[] = $v;
			}
		}
		$vote_answer_model = Db::name('vote_answer');
		$vote_answer_model->where(['id'=>['IN',implode(',',$new_choices)]])->setInc('size');
		$result = $vote_answer_model->where(['id'=>['IN',implode(',',$new_choices)]])->setInc('size_modified');
		if($result){
			if(count($sl_data)>1){
				Db::name('vote_record')->insertAll($sl_data);
			}else{
				Db::name('vote_record')->insert($sl_data[0]);
			}
			return $new_choices;
		}else{
			return;
		}
	}

	public function checkRecord($id,$ip){
		$record_status = [];
		$createtime = 0;
		if($uid = session('uid')){
			$records = Db::name('vote_record')
			->field('answer_id,createtime')
			->where(['vote_user'=>$uid,'vote_id'=>$id])
			->order('id desc')
			->select();
			if($records){
				foreach($records as $k => $v){
					$record_status[] = $v['answer_id'];
				}
				$createtime = $records[0]['createtime'];
			}
		}else{
			$records = Db::name('vote_record')
			->field('answer_id,createtime')
			->where(['vote_ip'=>$ip,'vote_id'=>$id])
			->order('id desc')
			->select();
			if($records){
				foreach($records as $k => $v){
					$record_status[] = $v['answer_id'];
				}
				$createtime = $records[0]['createtime'];
			}
		}
		$time_status = strtotime(date("Y-m-d",$createtime))<strtotime(date("Y-m-d",time()));
		if(empty($record_status) || $time_status){
	        return false;
	    }else{
	    	return $record_status;
	    }
	}

	public function addVote($request){
		$sl_data = $request->post();
		$question = $sl_data['question'];
		$smeta['thumb'] = '/static/mobile/images/randvote-'.rand(1,9).'.jpg';
		//投票主体数据
		$vote_data = [
			'endtime' => strtotime($sl_data['endtime']),
			'title' => $sl_data['title'],
			'description' => $sl_data['description'],
			'vote_author_name' => session('user.user_nicename')?session('user.user_nicename'):session('user_login_encrypt'),
			'keywords' => $sl_data['keywords'],
			'createtime' => time(),
			'vote_status' => isset($sl_data['vote_status'])?$sl_data['vote_status']:0,
			'vote_top' => 0,
			'show' => 1,
			'smeta' => json_encode($smeta),
			'vote_author' => session('uid'),
			'ismain' => $sl_data['ismain']
		];
		if($vid = $this->insertGetId($vote_data)){
			foreach($question as $k => $v){
				$qt_data = [
					'vid'=>$vid,
					'question'=>isset($v['title'])?$v['title']:$sl_data['title'],
					'ismanyasr'=> isset($v['ismanyasr'])?$v['ismanyasr']:0,
				];
				if($qid = Db::name('vote_question')->insertGetId($qt_data)){
					foreach($v['answer'] as $kk => $vv){
						$as_data[] = [
							'q_id' => $qid,
							'content' => $vv,
						];
					}
				}
			}
			if(Db::name('vote_answer')->insertAll($as_data)){
				return $vid;
			}
		}else{
			return;
		}
	}
}