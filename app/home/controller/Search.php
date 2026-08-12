<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\TermRelationships;
use think\Db;
class Search extends Base
{
	public function detail(){
		$param = request()->param();
        $page = isset($param['page'])?$param['page']:1;
        $article_model = new TermRelationships($this->encrypt);
        if(empty($param['keyword'])){
            $param['keyword'] = '';
            $posts = [];
            // $votes = [];
        }else{
            $where = ['post_title|post_author_name'=>['like',"%".$param['keyword']."%"]];
            $posts = $article_model->getSearchArticles(false,$page,config('MORE_PAGESIZE'),'object_id',$where);
            array_map(function($data){$data['tid'] = $this->encrypt->encrypt($data['tid']);},$posts);
            // $votes = Db::name('vote')
            // ->where(['title'=>['like',"%".$param['keyword']."%"],'vote_status'=>1])
            // ->page($page)
            // ->select(); 
        }
        $assign = [
            'posts'=>$posts,
            'keyword'=>$param['keyword'],
        	// 'votes'=>$votes,
        ];
        return view('Search/detail',$assign);
	}

    public function detail2(){
        $param = request()->param();
        $page = isset($param['page'])?$param['page']:1;
        $article_model = new TermRelationships($this->encrypt);
        if(empty($param['keyword'])){
            $param['keyword'] = '';
            $posts = [];
            // $votes = [];
        }else{
            $where = ['post_title|post_author_name'=>['like',"%".$param['keyword']."%"]];
            // $whereOr = ['post_author_name'=>['like',"%".$param['keyword']."%"]];
            $posts = $article_model->getSearchArticles2(false,$page,config('MORE_PAGESIZE'),'object_id',$where);
            array_map(function($data){$data['tid'] = $this->encrypt->encrypt($data['tid']);},$posts);
            // $votes = Db::name('vote')
            // ->where(['title'=>['like',"%".$param['keyword']."%"],'vote_status'=>1])
            // ->page($page)
            // ->select(); 
        }
        $assign = [
            'posts'=>$posts,
            'keyword'=>$param['keyword'],
            // 'votes'=>$votes,
        ];
        return view('Search/detail',$assign);
    }

	public function loadSearchDetail(){
        $request = request();
		$param = $request->param();
		$page = isset($param['page'])?$param['page']:1;
        $type = isset($param['type'])?$param['type']:'';
        $article_model = new TermRelationships($this->encrypt);
        if($type == 'article'){
            $where = ['post_title'=>['like',"%".$param['keyword']."%"]];
            return $article_model->loadArticlesData($request,false,$where,'object_id');
        }elseif($type == 'vote'){
            $where = ['title'=>['like',"%".$param['keyword']."%"]];
            return $article_model->loadVotesData($request,$where);
        }else{
            return ['status'=>0,'info'=>'没有更多'];
        }
	}

}