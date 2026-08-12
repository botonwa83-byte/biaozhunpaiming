<?php
namespace app\admin\model;
use think\Model;
use think\Db;

class Votes extends Model{
	protected $table = 'bzpm_vote';

	public function _initialize(){
		parent::_initialize();
	}

	public function getVotes($request='',$map = []){
        if($param = request()->param()){
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
		$votes = $this->where($map)
    	->order('id desc,q_order asc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	return ['votes'=>$votes,'page'=>$votes->render(),'page_min'=>$votes->render()];
	}

	public function addVote($request){
		$sl_data = $request->post();
		$question = $sl_data['question'];
		//投票主体数据
		$vote_data = [
			'endtime' => strtotime($sl_data['endtime']),
			'title' => $sl_data['title'],
			'description' => $sl_data['description'],
			'vote_author_name' => $sl_data['vote_author_name'],
			'keywords' => $sl_data['keywords'],
			'createtime' => time(),
			'vote_status' => isset($sl_data['vote_status'])?$sl_data['vote_status']:0,
			'vote_top' => 0,
			'show' => 1,
			'vote_author' => session('uid')
		];
		//投票的图片
		$files = $request->file();
		if($head_img = $files['img']){
			$info_head = $head_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$smeta['thumb'] = '/data/upload/'.$info_head->getSaveName();
			$vote_data['smeta'] = json_encode($smeta);
		}

		if($vid = $this->insertGetId($vote_data)){
			foreach($question as $k => $v){
				$qt_data = [
					'vid'=>$vid,
					'question'=>$v['title'],
					'ismanyasr'=> $v['ismanyasr'],
				];
				if($qid = Db::name('vote_question')->insertGetId($qt_data)){
					foreach($v['answer'] as $kk => $vv){
						$img = '';
						if(isset($files['pic'.$k.'_'.$kk]) && ($file = $files['pic'.$k.'_'.$kk])){
							$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
							$img = '/data/upload/'. $info->getSaveName();
						}
						$as_data[] = [
							'q_id' => $qid,
							'content' => $vv,
							'a_pic' => $img,
							'createtime' => time()
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

	public function editVote($request){
		$sl_data_post = $request->post();
		$sl_data = [
			'id' => $sl_data_post['id'],
			'endtime' => strtotime($sl_data_post['endtime']),
			'smeta' => json_encode($sl_data_post['smeta']),
			'title' => $sl_data_post['title'],
			'description' => $sl_data_post['description'],
			'vote_author_name' => $sl_data_post['vote_author_name'],
			'keywords' => $sl_data_post['keywords'],
			'hits' => $sl_data_post['hits'],
			'praise' => $sl_data_post['praise'],
            'vote_status' => $sl_data_post['vote_status'],
		];
		$question = $sl_data_post['question'];
		$files = $request->file();
		if(isset($files['img'])){
			$info_head = $files['img']->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$smeta['thumb'] = '/data/upload/'.$info_head->getSaveName();
			$sl_data['smeta'] = json_encode($smeta);
		}
		$this->update($sl_data);
		$vote_answer_model = Db::name('vote_answer');
		foreach($question as $k => $v){
			$qt_data = [];
			$qt_data = [
				'vid'=>$sl_data_post['id'],
				'question' => $v['title'],
				'ismanyasr'=> $v['ismanyasr'],
			];
			if($question_result = Db::name('vote_question')->where(['id'=>$k,'vid'=>$sl_data_post['id']])->where('status',1)->find()){
				Db::name('vote_question')->where(['id'=>$k])->update($qt_data);
			}else{
				$qid = Db::name('vote_question')->insertGetId($qt_data);
			}
			foreach($v['answer'] as $kk => $vv){
				if(isset($files['pics'.$k.'_'.$kk])){
					$info = $files['pics'.$k.'_'.$kk]->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
					$vv['a_pic'] = '/data/upload/'. $info->getSaveName();
				}
				if(Db::name('vote_answer')->where(['id'=>$kk,'q_id'=>$k])->find()){
					Db::name('vote_answer')->where(['id'=>$kk])->update($vv);
				}else{
					if(isset($question_result)){
						$as_data[] = [
							'q_id' => $question_result['id'],
							'content' => $vv['content'],
							'a_pic' => isset($vv['a_pic'])?$vv['a_pic']:'',
						];
					}elseif(isset($qid)){
						$as_data[] = [
							'q_id' => $qid,
							'content' => $vv['content'],
							'a_pic' => isset($vv['a_pic'])?$vv['a_pic']:'',
						];
					}
				}
			}
		}
		if(isset($as_data) && $as_data){
			$vote_answer_model->insertAll($as_data);
		}
	}

	public function getVote($id,$order=true){

		$vote =	$this->where(['id'=>$id])->find();
		$question = Db::name('vote_question')->where(['vid'=>$id,'status'=>1])->select();
		foreach($question as $k => $v){
			$count = 0;
			$answers = Db::name('vote_answer')->where(['q_id'=>$v['id']])->select();
			foreach($answers as $kk => $vv){
				$count += $vv['size_modified'];
			}
			if($order){
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
		foreach($param['choices'] as $k => $v){
			if(is_array($v)){
				foreach($v as $vv){
					$sl_data[] = [
						'vote_user' => session('uid'),
						'vote_ip' => $ip,
						'vote_id' => $param['id'],
						'answer_id' => $vv,
						'record_status' => 1
					];
					$new_choices[] = $vv;
				}
			}else{
				$sl_data[] = [
					'vote_user' => session('uid'),
					'vote_ip' => $ip,
					'vote_id' => $param['id'],
					'answer_id' => $v,
					'record_status' => 1
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

	public function checkRecord($request){
		$id = $request->param('id');
		$ip = $request->ip(true);
		$record_status = [];
		if($uid = session('uid')){
			$records = Db::name('vote_record')->field('answer_id')->where(['vote_user'=>$uid,'vote_id'=>$id])->select();
			foreach($records as $k => $v){
				$record_status[] = $v['answer_id'];
			}
			return $record_status;
		}else{
			$records = Db::name('vote_record')->field('answer_id')->where(['vote_ip'=>$ip,'vote_id'=>$id])->select();
			foreach($records as $k => $v){
				$record_status[] = $v['answer_id'];
			}
			return $record_status;
		}
	}

	public function delOptions($id){
		if(Db::name('vote_answer')->where(['id'=>$id])->update(['q_id'=>0])){
			return true;
		}else{
			return ;
		}
	}

}
