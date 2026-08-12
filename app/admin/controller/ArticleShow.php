<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\model\TermRelationships;
use app\admin\model\Articles;

class ArticleShow{
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
        $article_data['post_content'] = htmlspecialchars_decode($article_data['post_content']);
        //相关文章
        $coarticles = $this->getCoarticles($id,$article_data['term_id'],$article_data['correlation'],$article_data['post_keywords']);
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
            'coarticles' => $coarticles,
            'vote_check' => $vote_check,
            'choices' => $choices,
        ];

        $wechat_jsapi_data = $this->wechatJsapi($request);
        $assign['time'] = $wechat_jsapi_data['time'];
        $assign['nonceStr'] = $wechat_jsapi_data['nonceStr'];
        $assign['signature'] = $wechat_jsapi_data['signature'];
        $assign['url'] = $wechat_jsapi_data['url'];

        if(!$request->isMobile()){
        	return view('Article/article',$assign);
        }else{
        	return view('Article/article_mobile',$assign);
        }
    }

    public function get_jsapi_ticket(){
        $js_tikict = cache('ticket');
        return $js_tikict['ticket'];
    }

    //微信jsapi接口
    protected function wechatJsapi($request=null){
        $url = $request->url(true);
        $jsapi_ticket = $this->get_jsapi_ticket();
        $sl_data = [];
        $sl_data['time'] = time();
        $sl_data['url'] = $url;
        $sl_data['nonceStr'] = 'biaozhun';
        $sl_data['signature'] = sha1('jsapi_ticket='.$jsapi_ticket.'&noncestr='.$sl_data['nonceStr'].'&timestamp='.$sl_data['time'].'&url='.$url);
        return $sl_data;
    }

    //获取相关文章
    public function getCoarticles($id,$term_id='',$ids='',$keyword=''){
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting';
        $where['status'] = 1;
        if(!empty($ids)){
            $where['tid'] = array('in',$ids);
            $article = db('term_relationships')->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where($where)
            ->select();
            if(($limit = 3-count($article))>0){
                $keyword_articles = $this->keywordCorrelation($id,$term_id,$keyword,$limit);
                $article = array_merge($article,$keyword_articles);
            }
        }else{
            $article = $this->keywordCorrelation($id,$term_id,$keyword);
        }
        return $article;
    }

    //获取相关文章
    private function keywordCorrelation($id,$term_id=1,$keyword='',$limit=3){
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting';
        $keyword = explode('，',$keyword);
        $coarticles = array();
        $coarticles_2 = array();
        $new_article = array();
        foreach($keyword as $v){
            $coarticles = db('term_relationships')->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where(" status=1 AND post_status=1 AND term_id = $term_id AND tid != $id AND CONCAT(post_keywords,post_title,post_content) like '%$v%' ")
            ->order('listorder asc,post_date desc')
            ->limit(6)
            ->select();
        }
        if(($limit_2 = $limit-count($coarticles))>0){
            $coarticles_2 = db('term_relationships')->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where(" status=1 AND post_status=1 AND term_id = $term_id AND tid != $id ")
            ->order('listorder asc,post_date desc')
            ->limit(6)
            ->select();
        }
        $article = array_merge($coarticles,$coarticles_2);
        foreach(array_rand($article,$limit) as $k => $v){
            $new_article[] = $article[$v];
        }
        return $new_article;
    }


}