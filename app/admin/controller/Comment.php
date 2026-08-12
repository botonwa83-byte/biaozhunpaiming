<?php
namespace app\admin\Controller;

use Think\Db;
use app\admin\controller\Auth;
use app\admin\model\CommentArticles;
use app\admin\model\TermRelationships;
class Comment extends Auth {

	public function commentAlllist()
	{
		$comment_model = new CommentArticles();
		$comments = $comment_model->getCommentAlllist();
		return view('Comment/comment_articles',['comments'=>$comments['comments'],'page'=>$comments['page']]);
	}
	public function commentDel()
	{
		$request = request();
		$id = $request->param('id');
		$p = $request->param('p');
		$action = $request->param('action');
		if($action != 'voteList'){
			$action = 'commentAlllist';
		}
		if(Db::name('comments')->where(array('id'=>$id))->update(['back'=>0,'status'=>0]))
		{
			return ['status'=>1,'info'=>'删除成功','url'=>url('comment/'.$action,['p'=>$p])];
		}
	}
	public function commentAlldel()
	{
		$request = request();
		$param = $request->param();
		$ids = implode(',',$param['id']);
		if(Db::name('comments')->where(" id in($ids) ")->update(['back'=>0,'status'=>0]))
		{
			return ['status'=>1,'info'=>'删除成功','url'=>url('comment/commentAlllist',['p'=>$param['p']])];
		}
	}

	public function commentState()
	{
		$id = request()->param('x');
		$comment_model = Db::name('comments');
		$status = $comment_model->where(array('id'=>$id))->find();//判断当前状态情况
		$status = $status['status'];
		if($status == 1){
			$statedata = array('status'=>0);
			$auth_group = $comment_model->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('status'=>1);
			$auth_group = $comment_model->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function sheildWords()
	{	
		if(request()->isPost()){
			$content = request()->param('content');
			$data['content'] = '/'.$content.'/';
			$data['createtime'] = time();
			$data['status'] = 1;
			if(db('bandwords')->insert($data)){
				return ['status'=>1,'info'=>'添加成功','url'=>''];
			}
		}
		$data = db('bandwords')->order('id desc')->paginate(config('PAGESIZE'));
		return view('Comment/sheildWords',['data'=>$data,'page' => $data->render(),'page_min' => $data->render()]);
	}

	public function addComment()
	{	
		$usernames = Db::name('usernames')->select();
		$count = count($usernames);
		if($request = request()->post()){
			$post_ob = db('term_relationships')->where(['tid'=>$request['post_id']])->find();
			$sl_data = [];
			foreach($request['content'] as $k => $v){
				$rand = rand(1,$count);
				if($v){
					$sl_data[] = [
						'post_id' => $post_ob['object_id'],
						'content' => $v,
						'uid' => -1,
						'createtime'=>time(),
						'full_name' => $usernames[$rand-1]['username'],
						'avatar' => 'http://47.94.199.232/images/'.$usernames[$rand-1]['id'].'.jpg',
						'type' => $request['type'],
					];
				}
			}
			// $table = $request['type'] == 1?'comments':'comment';
			if($result = Db::name('comments')->insertAll($sl_data)){
				db('posts')->where(array('id'=>$post_ob['object_id']))->setInc('comment_count',count($sl_data));
				return ['status'=>1,'info'=>'添加成功','url'=>url('addcomment')];
			}
		}
		return view('Comment/addcomment');	
	}

	public function userList()
	{
		$comment_model = Db::name('usernames');
		$userlists = $comment_model->order('id desc')->paginate(15);
		return view('Comment/comment_userlist',['userlists'=>$userlists,'page'=>$userlists->render()]);
	}

	public function addUser()
	{
		if($param = request()->post()){
			$comment_model = Db::name('usernames');
			foreach($param['username'] as $k => $v){
				$sl_data[$k]['username'] = $v;
			}
			$result = $comment_model->insertAll($sl_data);
			if($result){
				return ['status'=>1,'info'=>'添加用户名成功','url'=>url('addUser')];
			}else{
				return ['status'=>0,'info'=>'添加用户名失败','url'=>url('addUser')];
			}
		}
		return view('Comment/adduser');
	}

	public function voteList(){
		$comment_model = new CommentArticles();
		$comments = $comment_model->getVoteAlllist();
		return view('Comment/comment_votes',['comments'=>$comments['comments'],'page'=>$comments['page']]);
	}
}