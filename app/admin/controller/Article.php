<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;
use app\admin\model\TermRelationships;
use app\admin\model\Articles;

class Article extends Auth {

	public function lists(){
		$request = request();
		$term_model = new TermRelationships();
    	$list = $term_model->getArticles($request);
    	$terms = TermRelationships::getTerms();
		return view('Article/lists',[
			'posts'=>$list['posts'],
			'page_min'=>$list['page_min'],
			'page'=>$list['page'],
			'keytype'=>$request->param('keytype'),
			'opentype_check'=>$request->param('opentype_check'),
			'sldate'=>$request->param('sldate'),
			'keyy'=>$request->param('key'),
			'terms'=>$terms
		]);
	}

	public function detail()
    {
        $request = request();
        $id = $request->param('id');
        //增加文章点击量
        $article_model = new TermRelationships();
        //缓存
        // $request->cache('articles/:id',300);
        $vote_model = new \app\home\model\Votes();
        //获取文章数据
        $article_data = $article_model->getArticle($id);
        //文章数据处理
        $article_data['smeta'] = json_decode($article_data['smeta'],true);
        $article_data['post_content'] = str_replace('47.94.199.232','www.biaozhunpaiming.com',$article_data['post_content']);
        $article_data['post_content'] = htmlspecialchars_decode($article_data['post_content']);
        //相关文章
        //收藏和点赞
        $vote_check = false;
        $choices = [];
        $vote = [];
        //投票
        if(!empty($article_data['covoting'])){
            $ip = $request->ip(true);
            $vote = $vote_model->getVote($article_data['covoting'],true);
            $choices = $vote_model->checkRecord($article_data['covoting'],$ip);
            $vote_check = $choices || $vote['endtime']< time();
        }

        $assign = [
            'article_data'=>$article_data,
            'vote' => $vote,
            'is_wechat' => false,
            'navs' => [],
            'user' => [],
            'coarticles' => [],
            'vote_check' => $vote_check,
            'choices' => $choices,
        ];
        if(!$request->isMobile()){
        	return view('Article/article',$assign);
        }else{
        	return view('Article/article_mobile',$assign);
        }
    }

	public function add(){
		$terms = TermRelationships::getTerms();
		$hotwordlist = db('hotword')->select();
		return view('Article/add',['terms'=>$terms,'hotwordlist'=>$hotwordlist]);
	}

	public function runadd(){
		$sl_data = request()->param();
		$file = request()->file('smeta');
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$smeta['thumb'] = $info->getSaveName();
			$sl_data['smeta'] = json_encode($smeta);
		}
		$term_ids = $sl_data['term_ids'];
		unset($sl_data['term_ids']);
		$status = isset($sl_data['status'])?$sl_data['status']:0;
		unset($sl_data['status']);
		$sl_data['post_modified'] = $sl_data['post_modified']?strtotime($sl_data['post_modified']):time();
		$sl_data['post_date'] = time();
		$sl_data['post_hits'] = rand(200,500);
		$sl_data['post_author'] = session('aid');
		$sl_data['post_status'] = 1;
		$article_model = new Articles();
		$id = $article_model->add($sl_data);

		foreach($term_ids as $k => $v){
			$term_arr[$k]['term_id'] = $v;
			$term_arr[$k]['object_id'] = $id;
			$term_arr[$k]['listorder'] = 0;
			$term_arr[$k]['status'] = $status;
		}
		$term_model = new TermRelationships();
		$term_result = $term_model->add($term_arr);
		if($term_result){
			return ['status'=>1,'info'=>'添加文章成功','url'=>''];
		}else{
			return ['status'=>0,'info'=>'添加文章失败','url'=>''];
		}
	}

	public function edit(){
		$param = request()->param();
		$article_model = new TermRelationships();
		$article = $article_model->getArticle($param['tid']);
		$article['post_content'] = str_replace('47.94.199.232','www.biaozhunpaiming.com',$article['post_content']);
		$article['smeta'] = json_decode($article['smeta'],true);
		$terms = TermRelationships::getTerms();
		$term_id_objects = $article_model->getTermids($article['object_id'],true);
		foreach($term_id_objects as $k => $v){
			$term_ids[] = $v->term_id;
		}
		$hotwordlist = db('hotword')->select();
		return view('Article/edit',['article'=>$article,'terms'=>$terms,'term_ids'=>$term_ids,'hotwordlist'=>$hotwordlist]);
	}

	public function runedit(){
		$sl_data = request()->post();
		$file = request()->file('smeta');
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
			$smeta['thumb'] = $info->getSaveName();
			$sl_data['smeta'] = json_encode($smeta);
		}else{
			$sl_data['smeta'] = json_encode($sl_data['smeta']);
		}
		$term_ids = $sl_data['term_ids'];
		unset($sl_data['term_ids']);

		$status = isset($sl_data['status'])?$sl_data['status']:0;
		if(isset($sl_data['status'])){unset($sl_data['status']);}

		$sl_data['comment_status'] = isset($sl_data['comment_status'])?$sl_data['comment_status']:0;
		$sl_data['isshow'] = isset($sl_data['isshow'])?$sl_data['isshow']:1;
		$sl_data['post_recommend_status'] = isset($sl_data['post_recommend_status'])?$sl_data['post_recommend_status']:0;
		$sl_data['post_modified'] = strtotime($sl_data['post_modified']);
		$sl_data['post_author'] = session('aid');

		$article_model = new Articles();
		$result = $article_model->update($sl_data);

		$term_model = new TermRelationships();
		$term_result = $term_model->where(['object_id'=>$sl_data['id']])->update(['status'=>$status]);
		$term_del_result = $term_model->where(['object_id'=>$sl_data['id'],'term_id'=>['NOT IN',implode(',',$term_ids)]])->update(['back'=>1]);
		foreach($term_ids as $k => $v){
			$term_isset_result = $term_model->where(['object_id'=>$sl_data['id'],'term_id'=>$v])->find();
			if(!$term_isset_result && !isset($term_isset_result['back'])){
				$term_new_result = $term_model->insert([
					'object_id'=>$sl_data['id'],
					'term_id' => $v,
					'listorder' => 0,
					'status' => $status
				]);
			}else{
				$term_model->where(['object_id'=>$sl_data['id'],'term_id' => $v])->update(['back' => 0]);
			}
		}
		if($result){
			return ['info'=>'修改成功！','status'=>1,'url'=>''];
		}else{
			return ['info'=>'修改失败！','status'=>0,'url'=>''];
		}
		
	}

	public function del(){
		$request = request();
		$id = $request->param('tid');
		if(db('term_relationships')->where(['tid'=>$id])->update(['status'=>0,'back'=>1])){
			return ['status'=>1,'info'=>'文章移除到回收站','url'=>''];
		}else{
			return ['status'=>0,'info'=>'文章移除失败','url'=>''];
		}
	}

	public function state(){
		$request = request();
		$article_model = new TermRelationships();
		if($article_model->state($request->param('x'))){
			return ['status'=>1,'info'=>'已审','url'=>1];
		}else{
			return ['status'=>1,'info'=>'未审','url'=>1];
		}
	}

	public function recyclebin(){
		$request = request();
		$terms = TermRelationships::getTerms();
		$article_model = new TermRelationships();
    	$list = $article_model->recycle($request,['status'=>0,'back'=>1]);
		return view('Article/recyclebin',['posts'=>$list['posts'],'page'=>$list['page'],'page_min'=>$list['page_min'],'keytype'=>$request->param('keytype'),'opentype_check'=>$request->param('opentype_check'),'sldate'=>$request->param('sldate'),'keyy'=>$request->param('key'),'terms'=>$terms]);
	}

	public function backOpen(){
		$request = request();
		$article_model = new TermRelationships();
		if($article_model->backOpen($request->param('id'))){
			return ['status'=>1,'info'=>'还原成功','url'=>''];
		}else{
			return ['status'=>1,'info'=>'还原失败','url'=>''];
		}
	}

	public function listorder(){
		$request = request();
		$param = $request->param();
		foreach($param['tid'] as $k => $v){
			db('term_relationships')->where(['tid'=>$k])->update(['listorder'=>$v]);
		}
		return ['status'=>1,'info'=>'排序成功！','url'=>''];
	}

	public function news_back_del($id){
		if(db('term_relationships')->delete(['id'=>$id])){
			return ['status'=>1,'info'=>'成功','url'=>''];
		}else{
			return ['status'=>0,'info'=>'失败','url'=>''];
		}
	}

}