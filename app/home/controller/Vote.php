<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Votes;

class Vote extends Base{
	protected $vote_model;

	public function _initialize(){
		parent::_initialize();
		$this->vote_model = new Votes();
	}

	public function add(){
		return view('Vote/add');
	}

    public function addMore(){
        return view('Vote/addMore');
    }

    public function runadd(){
        $request = request();
        if($this->vote_model->addVote($request)){
            return ['status'=>1,'info'=>'','url'=>url('home/user/personBuild')];
        }else{
            return ['status'=>0,'info'=>'','url'=>''];
        }
    }

	public function voteShow(){
		$request = request();
		$param = $request->param();
		$vote = $this->vote_model->getVote($param['id']);
		$time = time();
        $ip = $request->ip(true);
		if($this->vote_model->checkRecord($param['id'],$ip) || $vote['endtime']<$time){
			$this->redirect(url('vote/voteResult',['id'=>$request->param('id')]));
		}
        $dolike = $favorite = [];
		$dolike = $this->getDolike($vote['id'],$request->ip(true),$this->users);
        if($this->users){
            $favorite = $this->getFavorite($this->users['uid'],$vote['id']);
        }
		$assign = [
			'vote' => $vote,
			'dolike' => $dolike,
            'favorite' => $favorite,
            'is_wechat' => $this->is_wechat,

            'covotes' => $this->keywordCorrelation($param['id'],$vote['keywords'])
		];
		if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')!==false){
            $wechat_jsapi_data = $this->wechatJsapi($request);
            $assign['time'] = $wechat_jsapi_data['time'];
            $assign['nonceStr'] = $wechat_jsapi_data['nonceStr'];
            $assign['signature'] = $wechat_jsapi_data['signature'];
            $assign['url'] = $wechat_jsapi_data['url'];
            $assign['is_wechat'] = $this->is_wechat;
        }
		return view('Vote/voteshow',$assign);
	}

	public function votePost(){
		$request = request();
		$param = $request->param();
        $ip = $request->ip(true);
		if(!$result = $this->vote_model->checkRecord($param['id'],$ip)){
			$result = $this->vote_model->votePost($param,$ip);
		}
        if($request->param('type') == 'article'){
            $refer_url = $request->server('HTTP_REFERER');
            return ['status'=>1,'info'=>'','url'=>$refer_url];
        }else{
		  $this->redirect(url('Vote/voteresult',array('id'=>$param['id'])));
        }
	}

	public function voteResult(){
		$request = request();
		$param = $request->param();
        $ip = $request->ip(true);
		$time = time();
		$vote = $this->vote_model->getVote($param['id'],true);
		$result = $this->vote_model->checkRecord($param['id'],$ip);
		if(!$result && $vote['endtime']>$time){
			$this->redirect(url('vote/voteShow',['id'=>$request->param('id')]));
		}
		$dolike = $this->getDolike($param['id'],$ip,$this->users);
        $favorite = $this->getFavorite($this->users['uid'],$param['id']);
		$assign = [
			'vote' => $vote,
			'choices' => $result === false?[]:$result,
			'dolike' => $dolike,
            'favorite' => $favorite,
			'is_wechat' => $this->is_wechat,

            'covotes' => $this->keywordCorrelation($param['id'],$vote['keywords'])
		];
		if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')!==false){
            $wechat_jsapi_data = $this->wechatJsapi($request);
            $assign['time'] = $wechat_jsapi_data['time'];
            $assign['nonceStr'] = $wechat_jsapi_data['nonceStr'];
            $assign['signature'] = $wechat_jsapi_data['signature'];
            $assign['url'] = $wechat_jsapi_data['url'];
            $assign['is_wechat'] = $this->is_wechat;
        }
		return view('Vote/voteresult',$assign);
	}

	public function doLike(){
        $request = request();
        $user = session('user')?session('user'):'';
        $pid = $request->param('id');
        $ip = $request->ip(true);
        if($this->getDolike($pid,$ip,$user)){
            return ['status'=>0,'info'=>'点赞失败！'];
        }
        if(db('vote')->where(['id'=>$request->param('id')])->setInc('praise')){
            $dolike_data = [
                'uid' => isset($user['id'])?$user['id']:0,
                'pid' => $pid,
                'time' => time(),
                'ip' => $ip,
                'type' => 2,
            ];
            if(db('dolike')->insert($dolike_data)){
                return ['status'=>1,'info'=>'点赞成功！']; 
            }else{
                return ['status'=>1,'info'=>'点赞失败！'];
            }
        }else{
            return ['status'=>1,'info'=>'点赞失败！'];
        }
    }

    public function getDolike($pid=0,$ip=0,$user=''){
        !empty($pid)?$where['pid']=$pid:'';
        $where['type'] = 2;
        if($uid = isset($user['id'])?$user['id']:0){
            $where['uid'] = $uid;
        }else{
            $where['ip'] = $ip;
        }
        return db('dolike')->field('pid')->where($where)->find();
    }

    public function favorite(){
        $request = request();
        if(!$uid = session('uid')){
            return ['status'=>0,'info'=>'收藏失败！'];
        }
        $vote_id = $request->param('id');
        if($result = $this->getFavorite($uid,$vote_id,true)){
            if($result['favo_status'] == 1){
                return ['status'=>0,'info'=>'取消收藏成功！'];
            }else{
                return ['status'=>1,'info'=>'收藏成功！'];
            }
        }else{
           $favorite_data = [
                'uid' => $uid,
                'vote_id' => $vote_id,
                'createtime' => time(),
                'favo_status' => 1,
                'type' => 2
            ];
            if(db('user_favorites')->insert($favorite_data)){
                return ['status'=>1,'info'=>'收藏成功！']; 
            }else{
                return ['status'=>0,'info'=>'收藏失败！'];
            } 
        }
    }

    public function getFavorite($uid,$vote_id,$status=false){
        if($favs = db('user_favorites')->where(['uid'=>$uid,'vote_id'=>$vote_id,'type'=>2])->find()){
            if($status){
                if($favs['favo_status'] == 0){
                    $favo_status = 1;
                }else{
                    $favo_status = 0; 
                }
                db('user_favorites')->where(['uid'=>$uid,'vote_id'=>$vote_id,'type'=>2])->update(['favo_status'=>$favo_status]); 
            }
            return $favs;
        }else{
            return false;
        }
    }

    private function keywordCorrelation($id,$keyword){
        $covotes = array();
        $vote_model = db('vote');
        if($keyword){
            $keyword = explode('，',$keyword);
            foreach($keyword as $v){
                $covotes = $vote_model->field('id,title,keywords,description,createtime,hits,smeta,comment_count')->where(" id !=$id AND vote_status = 1 AND CONCAT(title,keywords) like '%$v%' ")->order('q_order asc,createtime desc')->limit(3)->select();
            }
        }
        if(count($covotes)>=3){
            return $covotes;
        }else{
            $covotes = $vote_model->field('id,title,keywords,description,createtime,hits,smeta,comment_count')->where(" id != $id AND vote_status = 1 ")->order('q_order asc,createtime desc')->limit(3)->select();
            return $covotes;
        }
    }

    public function votes()
    {
        $request = request();
        $page = $request->get('page') ?: 1;
        $votes = [];
        $votes = db('vote')
        ->where(['vote_status'=>1])
        ->order('q_order asc,createtime desc')
        ->page($page,10)
        ->select();
        if($votes){
            foreach($votes as $k => &$v){
                $v['image_url'] = 'http://www.biaozhunpaiming.com/'.json_decode($v['smeta'],true)['thumb'];
            }
            $this->result($votes,200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
    }
}