<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Comments extends Model{
	protected $table = 'bzpm_comments';

	public function _initialize(){
		parent::_initialize();
	}

	public function getCommentlist($request,$type=1,$limit=10,$children_limit=3){
		$commentlist = $this->where(['post_id'=>$request->param('id'),'status'=>1,'parentid'=>0,'type'=>$type])->order('top desc,createtime desc')->limit($limit)->select();
		foreach($commentlist as $k => $v){
			$childrens = [];
			$new_childrens = [];
			$childrens = $this->where(['parentid'=>$v['id'],'type'=>$type])->limit($children_limit)->select();
			foreach($childrens as $kk => $vv){
				$new_childrens[$vv['id']] = $vv;
				if($vv['status'] == 0){
					$vv['content'] = '此内容已删除！';
				}
			}
			$commentlist[$k]['children'] = $new_childrens;
		}
		return $commentlist;
	}

	public function getCommentReply($request){
		$comment = $this->where(['id'=>$request->param('cid'),'status'=>1])->find();
		$childrens = $this->where(['parentid'=>$comment['id']])->select();
		$new_childrens = [];
		foreach($childrens as $k => $v){
			$new_childrens[$v['id']] = $v;
			if($v['status'] == 0){
				$v['content'] = '此内容已删除！';
			}
		}
		$comment['children'] = $new_childrens;
		return $comment;
	}

	public function addCommentContent($data=[],$ip=0){
		$user = session('user');
		$nickname = empty($user['user_nicename'])?$user['user_login_encrypt']:$user['user_nicename'];
		$sl_data = [
			'post_id' => $data['ob_id'],
			'uid' => $user['id'],
			'full_name' => $nickname,
			'createtime' => time(),
			'content' => $data['content'],
			'status' => 1,
			'avatar' => $user['avatar'],
			'ip' => $ip,
			'type' => $data['type']
		];
		if(isset($data['cid']) && $cid = $data['cid']){
			$sl_data['parentid'] = $cid;
			if($reply_id = $data['reply_id']){
				$sl_data['path'] = '0-'.$cid.'-'.$reply_id;
				$sl_data['to_cid'] = $reply_id;
			}else{
				$sl_data['path'] = '0-'.$cid;
				$sl_data['to_cid'] = $cid;
			}
			Db::name('comments')->where(['id'=>$cid])->setInc('replay_num');
		}
		if($status = $this->insertGetId($sl_data)){
			if($data['type'] == 1){
				return Db::name('posts')->where(['id'=>$data['ob_id']])->setInc('comment_count');
			}else{
				return Db::name('vote')->where(['id'=>$data['ob_id']])->setInc('comment_count');
			}
		}else{
			return ;
		}
	}

	public function checkBandWord($word){
	    $words = Db::name('bandwords')->field('content')->select();
	    $new_words = array();
	    foreach($words as $v){
	        $new_words[] = $v['content'];
	    }
	    $count = 0;
	    preg_replace($new_words,'***',$word,-1,$count);
	    return $count;
	}

	public function getDolike($pid=0,$cid=0,$ip=0,$user='',$type=1){
		$pid?$where['pid']=$pid:'';
		$cid?$where['cid']=$cid:'';
		if($uid = isset($user['id'])?$user['id']:0){
			$where['uid'] = $uid;
		}else{
			$where['ip'] = $ip;
		}
		$where['type'] = $type;
        return Db::name('comments_dolike')->field('cid')->where($where)->select();
    }

}