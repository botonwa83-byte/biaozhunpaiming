<?php
namespace app\home\model;
use think\Model;
use think\Db;

class TermRelationships extends Model{
	protected $table = 'bzpm_term_relationships';
    protected $encrypt = null;

    public function __construct($encrypt=null){
        $this->encrypt = $encrypt;
    }

    //获取指定文章类型的文章列表
	public function getArticles($term_id,$page=1,$limit=9,$group='',$options=[]){
		$field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting,post_hits';
        $where['status'] = 1;
    	$where['post_status'] = 1;
    	$where['back'] = 0;
        if($term_id){
    	   $where['term_id'] = $term_id;
        }
        if($options){
            foreach($options as $k => $v){
                $where[$k] = $v;
            }
        }
		$posts = $this->alias('a')->field($field)
    	->join(' bzpm_posts b','a.object_id = b.id','LEFT')
    	->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->page($page)
    	->order('post_modified desc,listorder asc')
    	->limit($limit)
        // ->group($group)
        ->select();
    	foreach($posts as $k => &$v){
            if($this->encrypt){
                $v['tid'] = $this->encrypt->encrypt($v['tid']);
            }
    		$v['ad_flag'] = 0;
    	}
        unset($v);
    	return $posts;
	}

    //获取指定文章类型的文章列表
    public function getSearchArticles($term_id,$page=1,$limit=10,$group='',$options=[]){
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting,post_hits';
        $where['status'] = 1;
        $where['post_status'] = 1;
        $where['back'] = 0;
        if($term_id){
           $where['term_id'] = $term_id;
        }
        if($options){
            foreach($options as $k => $v){
                $where[$k] = $v;
            }
        }
        $posts = $this->alias('a')->field($field)
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->page($page)
        ->order('post_modified desc,listorder asc')
        ->limit($limit)
        ->group($group)
        ->select();
        foreach($posts as $k => &$v){
            if($this->encrypt){
                $v['tid'] = $this->encrypt->encrypt($v['tid']);
            }
            $v['ad_flag'] = 0;
        }
        unset($v);
        return $posts;
    }


    public function articleHits($object_id){
        Db::name('posts')->where(['id'=>$object_id])->setInc('post_hits_sure');
        return Db::name('posts')->where(['id'=>$object_id])->setInc('post_hits');
    }

    //获取指定文章类型相关文章
    public function getTermArticles($term_id,$page=1,$limit=3){
        $field = 'term_id,name,description,smeta';
        $where['parent'] = $term_id;
        $terms = Db::name('terms')->field($field)->where($where)->page($page)->order('listorder')->limit($limit)->select();
        foreach($terms as $k => $v){
            $terms[$k]['child'] = $this->getArticles($v['term_id'],1,2);
        }
        return $terms;
    }

    //获取指定字符串的相关文章
    public function getStringArticles($ids,$order='post_modified desc,listorder asc',$limit=3,$page=1,$falg=0){
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta';
        $where['status'] = 1;
        // $where['post_status'] = 1;
        $where['tid'] = ['IN',$ids];
        $posts = $this->alias('a')->field($field)
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->page($page)
        ->order($order)
        ->limit($limit)
        ->select();
        foreach($posts as $k => &$v){
            if($this->encrypt){
                $v['tid'] = $this->encrypt->encrypt($v['tid']);
            }
            $v['ad_flag'] = 0;
            $smeta = json_decode($v['smeta'],true);
            $v['smeta'] = 'http://47.94.199.232/data/upload/'. $smeta['thumb'];
        }
        unset($v);
        return $posts;
    }

    //获取文章类型
    public static function getTerms($where=[]){
        $where['status'] = 1;
        $temrs = Db::name('terms')->where($where)->order('listorder')->select();
        $new_terms = [];
        foreach($temrs as $k => $v){
            $new_terms[$v['term_id']] = $v;
        }
        return  $new_terms;
    }

    //获取指定文章类型
    public function getTerm($term_id){
        $where['status'] = 1;
        $where['term_id'] = $term_id;
        $temr = Db::name('terms')->where($where)->order('listorder')->find();
        return  $temr;
    }

    //获取指定TID文章
    public function getArticle($tid,$field='*',$status=1){
        $where['status'] = $status;
        $where['tid'] = $tid;
        $article = $this->field($field)->alias('a')
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->find();
        return $article;
    }

    //获取指定OBJECT_ID文章
    public function getObjectArticle($field,$id,$status=1){
        $where['status'] = $status;
        $where['object_id'] = $id;
        $article = $this->field($field)->alias('a')
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->find();
        return $article;
    }

    //获取相关文章
    public function getCoarticles($id,$term_id='',$ids='',$keyword=''){
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting';
        $where['status'] = 1;
        if(!empty($ids)){
            $where['tid'] = array('in',$ids);
            $article = $this->field($field)->alias('a')
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
            $coarticles = $this->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where(" status=1 AND post_status=1 AND term_id = $term_id AND tid != $id AND CONCAT(post_keywords,post_title,post_content) like '%$v%' ")
            ->order('post_modified desc,listorder asc')
            ->limit(6)
            ->select();
        }
        if(($limit_2 = $limit-count($coarticles))>0){
            $coarticles_2 = $this->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where(" status=1 AND post_status=1 AND term_id = $term_id AND tid != $id ")
            ->order('post_modified desc,listorder asc')
            ->limit(6)
            ->select();
        }
        $article = array_merge($coarticles,$coarticles_2);
        if(empty($article)){
            $article = $this->field($field)->alias('a')
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where(" status=1 AND post_status=1 AND status=1 AND tid != $id ")
            ->order('post_modified desc,listorder asc')
            ->limit(6)
            ->select();
        }
        foreach(array_rand($article,$limit) as $k => $v){
            $new_article[] = $article[$v];
        }
        return $new_article;
    }

    //加载更多文章数据
    public function loadArticlesData($request,$ad=true,$options=[],$group=''){
        $term_id = $request->param('term_id');
        $page = $request->param('page');
        $posts = $this->getArticles($term_id,$page,config('MORE_PAGESIZE'),$group,$options);
        if($request->isMobile() && $ad){
            $ad_model = new Ad();
            $offset_1 = ($page - 1)*config('MORE_PAGESIZE');
            $offset_2 = $page*config('MORE_PAGESIZE');
            $posts = $ad_model->getAds($term_id,$posts,$offset_1+1,$offset_2);
        }
        if($posts){
            if($request->isMobile()){
                $html = '';
                foreach($posts as $k => $v){
                    if($v['ad_flag'] == 0){
                        $smeta=json_decode($v['smeta'],true);
                        $html .= '<div class="card"><a href="';
                        $html .= url('articles/detail',array('id'=>$v['tid']),true,true);
                        $html .= '"><div class="media-left" href="';
                        $html .= url('articles/detail',array('id'=>$v['tid']),true,true);
                        $html .= '"><img class="lazy_'.$page.' media-object img-responsive" data-original="';
                        $html .= 'http://47.94.199.232/data/upload/'.$smeta['thumb'];
                        $html .= '"></div><div class="media-body"><h6 class="media-heading">';
                        $html .= $v['post_title'];
                        $html .= '</h6><p><small><span class="pull-left" style="padding-top: .2em">';
                        $html .= date("Y-m-d",$v['post_modified']);
                        $html .= '</span><span> ';
                        $html .= $v['post_hits'];
                        $html .= '阅读量</span></small></p></div></a></div>';
                    }else{
                        if($v['ad_type'] == 3){
                            $html .= '<div class="content-order"><div class="card" style="padding: 0"><div style="text-align: center;" class="video-hit" video-id="';
                            $html .= $v['ad_id'];
                            $html .= '"><div id="mod_player_'.$k.'" class="movie"></div>';
                            $html .= '<script type="text/javascript">var width = $(".card").width();var height = width*0.56+"px";var video = new tvp.VideoInfo();video.setVid("'.$v['ad_url'].'");var player =new tvp.Player();player.create({width:width,height:height,video:video,modId:"mod_player_'.$k.'",autoplay:false,pic:"'.$v['ad_image'].'"});</script></div><a href="';
                            $html .= url('video/detail',array('id'=>$v['ad_id']));
                            $html .= '"><div class="card-block"><h6 class="card-title" style="margin-bottom: 5px;">'.$v['ad_name'].'</h6><p class="card-text text-right"><small><span class="pull-left" style="padding-top: .2em">';
                            $html .= date('Y-m-d',$v['ad_createtime']);
                            $html .= '</span><span> <i class="icon-eye"></i> ';
                            $html .= $v['ad_hit'];
                            $html .= '</span></small></p></div></a></div></div>';
                        }else{
                            $html .= '<div class="position-rel"><a href="';
                            $html .= $v['ad_url'].'">';
                            if($v['ad_type'] == 2){
                                $html .= '<span class="icon-theme"></span>';
                            }
                            $html .= '<img class="img-responsive" src="http://47.94.199.232/'.$v['ad_image'].'" alt=""></a></div>';
                        }
                    }
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                foreach($posts as $k => $v){
                    $smeta = json_decode($v['smeta'],true);
                    $posts[$k]['smeta'] = 'http://47.94.199.232/data/upload/'.$smeta['thumb'];
                    $posts[$k]['post_modified'] = date('Y-m-d',$v['post_modified']);
                }
                return ['code'=>200,'data'=>$posts,'type'=>'articles'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
    }

    //加载更多推荐数据
    public function loadOrderData($request){
        $page = $request->param('page');
        $posts = $this->getArticles($request->param('term_id'),$page,config('MORE_PAGESIZE'));
        $ad_model = new Ad();
        $offset_1 = ($page - 1)*config('MORE_PAGESIZE');
        $offset_2 = $page*config('MORE_PAGESIZE');
        $posts = $ad_model->getAds($request->param('term_id'),$posts,$offset_1+1,$offset_2);
        if($posts){
            if($request->isMobile()){
                $html = '';
                foreach($posts as $k => $v){
                    if($v['ad_flag'] == 0){
                        $smeta = json_decode($v['smeta'],true);
                        $html .= '<div class="card"><a href="';
                        $html .= url('articles/detail',array('id'=>$v['tid']));
                        $html .= '"><img class="card-img-top lazy_'.$page.'" src="';
                        $html .= 'http://47.94.199.232/data/upload/'.$smeta['thumb'];
                        $html .= '"><div class="card-block"><h6 class="card-title">';
                        $html .= $v['post_title'];
                        $html .= '</h6><p class="card-text text-right"><small><span class="pull-left" style="padding-top: .2em">';
                        $html .= date("Y-m-d",$v['post_modified']);
                        $html .= '</span><span> ';
                        $html .= $v['comment_count'];
                        $html .= '评论</span></small></p></div></a></div>';
                    }else{
                        if($v['ad_type'] == 3){
                            $html .= '<div class="content-order"><div class="card" style="padding: 0"><div style="text-align: center;" class="video-hit" video-id="';
                            $html .= $v['ad_id'];
                            $html .= '"><div id="mod_player_'.$k.'" class="movie"></div>';
                            $html .= '<script type="text/javascript">var width = $(".card").width();var height = width*0.56+"px";var video = new tvp.VideoInfo();video.setVid("'.$v['ad_url'].'");var player =new tvp.Player();player.create({width:width,height:height,video:video,modId:"mod_player_'.$k.'",autoplay:false,pic:"'.$v['ad_image'].'"});</script></div><a href="';
                            $html .= url('video/detail',array('id'=>$v['ad_id']));
                            $html .= '"><div class="card-block"><h6 class="card-title" style="margin-bottom: 5px;">'.$v['ad_name'].'</h6><p class="card-text text-right"><small><span class="pull-left" style="padding-top: .2em">';
                            $html .= date('Y-m-d',$v['ad_createtime']);
                            $html .= '</span><span> <i class="icon-eye"></i> ';
                            $html .= $v['ad_hit'];
                            $html .= '</span></small></p></div></a></div></div>';
                        }else{
                            $html .= '<div class="position-rel"><a href="';
                            $html .= $v['ad_url'];
                            if($v['ad_type'] == 2){
                                $html .= '<span class="icon-theme"></span>';
                            }
                            $html .= '<img class="img-responsive" src="'.$v['ad_image'].'" alt=""></a></div>';
                        }
                    }
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                foreach($posts as $k => $v){
                    $smeta = json_decode($v['smeta'],true);
                    $posts[$k]['smeta'] = 'http://47.94.199.232/data/upload/'.$smeta['thumb'];
                    $posts[$k]['post_modified'] = date('Y-m-d',$v['post_modified']);
                }
                return ['code'=>200,'data'=>$posts,'type'=>'articles'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
    }

    //加载更多投票数据
    public function loadVotesData($request,$options=[]){
        $page = $request->param('page');
        $slide_model = new \app\home\model\SlideCat();
        $vote_slides = $slide_model->getHomeSlide('vote_index');
        foreach($vote_slides as $k => $v){
            $slide_ids[] = $v->slide_url;
        }
        $where = ['vote_status'=>1,'vote_top'=>0];
        if($vote_slides){
            $where[]=['NOT IN',implode(',',$slide_ids)];
        }
        if($options){
            foreach($options as $k => $v){
                $where[$k] = $v;
            }
        }
        $votes = Db::name('vote')->where($where)->page($page)->order('q_order asc,createtime desc')->limit(config('MORE_PAGESIZE'))->select();
        if($votes){
            if($request->isMobile()){
                $html = '';
                foreach($votes as $k => $v){
                    $smeta=json_decode($v['smeta'],true);
                    $url = url('vote/voteshow',array('id'=>$v['id']));
                    $html .= '<div class="card"><a href="'.$url.'"><div class="media-left" href="'.$url.'"><img class="media-object img-responsive" src="'.$smeta['thumb'].'" ></div><div class="media-body"><h6 class="media-heading">'.$v['title'].'</h6><p><small><span class="pull-left" style="padding-top: .2em">'.date("Y-m-d",$v['createtime']).'</span><span>'.$v['comment_count'].' 评论</span></small></p></div></a></div>';
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                foreach($votes as $k => $v){
                    $smeta = json_decode($v['smeta'],true);
                    $votes[$k]['smeta'] = $smeta['thumb'];
                    $votes[$k]['createtime'] = date('Y-m-d',$v['createtime']);
                }
                return ['code'=>200,'data'=>$votes,'type'=>'votes'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
    }

    //加载更多热榜数据
    public function loadHotlistData($request){
        $term_id = $request->param('term_id');
        $page = $request->param('page');
        $terms = $this->getTermArticles($term_id,$page);
        if($terms){
            if($request->isMobile()){
                $html = '';
                foreach($terms as $k => $v){
                    $smeta = json_decode($v['smeta'],true);
                    $html .= '<div class="card"><div class="hotlist-title"><img class="card-img-top" src="'.$v['smeta'].'"><div class="hotlist-mark text-center"><h6>'.$v['name'].'</h6><p>'.$v['description'].'</p></div></div>';
                    foreach($v['child'] as $key => $val){
                        $smeta_2 = json_decode($val['smeta'],true);
                        $html .= '<a href="'.url('articles/detail',array('id'=>$val['tid'])).'" class="cell"><div class="media-left" href="#"><img class="media-object img-responsive" src="http://47.94.199.232/data/upload/'.$smeta_2['thumb'].'"></div><div class="media-body"><h6 class="media-heading">'.$val['post_title'].'</h6><p><small><span class="pull-left" style="padding-top: .2em">'.date("Y-m-d",$val['post_modified']).'</span><span> '.$val['comment_count'].' 评论</span></small></p></div></a>';
                    }
                    $html .= '<div class="p-a-sm text-center"><a href="'.url('hotlistUnite',['id'=>$v['term_id']]).'">查看更多 ></a></div></div>';
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                return ['code'=>200,'data'=>$terms,'type'=>'terms'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
    }

    public function getArticleList($term_id,$page=1,$limit=5,$ids = [])
    {
        $field = 'tid,b.id,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting,post_hits,post_keywords,post_content';
        $where['status'] = 1;
        $where['post_status'] = 1;
        $where['back'] = 0;
        if($term_id){
            $where['term_id'] = $term_id;
        }
        $posts_model = $this->alias('a')->field($field)
            ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
            ->join(' bzpm_users c','b.post_author = c.id','LEFT')
            ->where($where)
            ->page($page)
            ->order('post_modified desc,listorder asc')
            ->limit($limit);
        if(count($ids) > 0){
            $posts_model->where('b.id','not in',$ids);
        }
        $posts = $posts_model->select();
        foreach($posts as $k => &$v){
            $v['ad_flag'] = 0;
            $v['image_url'] = 'http://www.biaozhunpaiming.com/data/upload/'.json_decode($v['smeta'],true)['thumb'];
        }
        unset($v);
        return $posts;
    }

}
