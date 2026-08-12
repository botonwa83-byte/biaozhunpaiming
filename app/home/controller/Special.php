<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\TermRelationships;
use app\home\model\SlideCat;
use think\Db;
class Special extends Base{

	public function detail(){
		$request = request();
		$id = $request->param('id');
        $order='specialorder asc,listorder asc,post_modified desc';
        $page = $request->param('page')?$request->param('page'):1;
		$article_model = new TermRelationships($this->encrypt);
        $data = $guest = $video_links = [];
		$data = Db::name('special')->where(['id'=>$id,'status'=>1])->find();
        if($page < 2){
            $data['content'] = htmlspecialchars_decode($data['content']);
            if(isset($data['guest']) && ($ids = $data['guest'])){
                $guest = Db::name('personal')->where(" id in($ids)")->select();
            }
            $assign['correlations'] = $assign['reviews']  = '';
            if(isset($data['correlation']) && ($correlation = $data['correlation'])){
                $data['corr_ids'] = $correlation;
                $assign['correlations'] = $article_model->getStringArticles($correlation,$order);
            }
            if(isset($data['review']) && ($review = $data['review'])){
                $data['review_ids'] = $review;
                $assign['reviews'] = $article_model->getStringArticles($review,$order);
            }
            if(isset($data['video_link']) && ($video_links = explode(',',$data['video_link']))){
                $video_link = Db::name('special_video')->where(array('id'=>$video_links[0]))->find();
                $data['video_link'] = $video_link['video_link'];
            }
            if(isset($data['other_name']) && $data['other_name']){
                $data['other_name'] = unserialize($data['other_name']);
                $data['morename'] = array_pop($data['other_name']);
            }
        }
        if(isset($data['other_ids']) && $data['other_ids']){
            $data['other_ids'] = unserialize($data['other_ids']);
            $last  = array_pop($data['other_ids']);
            $data['last_ids'] = $last;
            $order='specialorder asc,listorder asc,post_modified desc';
            if($last){
                if($page <2){
                   foreach($data['other_ids'] as $k => $v){
                        $data['posts'][$k] = $article_model->getStringArticles($v,$order,config('SPECIAL_PAGE'));
                    } 
                }
                $data['moreposts'] = $article_model->getStringArticles($last,$order,config('MORE_PAGESIZE'),$page);
            }
        }
        if($data['content']){
            $data['content'] = explode("\r\n",$data['content']);
        }
        $assign['data'] = $data;
        $assign['guest'] = $guest;
        $assign['video_links'] = count($video_links);
        if(!$request->isMobile()){
            $slide_model = new SlideCat();
            $assign['home_slides'] = $slide_model->getHomeSlide('portal_index');
        }
        $assign['app'] = 0;
        if($app = $request->param('app')){
            $assign['app'] = $app;
        }
		return view('Special/detail',$assign);
	}

    public function moreSpecialArticles(){
        $request = request();
        $ids = $request->param('id');
        $page = $request->param('page')?$request->param('page'):1;
        $name = $request->param('name');
        $article_model = new TermRelationships($this->encrypt);
        $order='specialorder asc,listorder asc,post_modified desc';
        $posts = $article_model->getStringArticles($ids,$order,config('MORE_PAGESIZE'),$page);
        $home_slides = [];
        if(!$request->isMobile()){
            $slide_model = new SlideCat();
            $home_slides = $slide_model->getHomeSlide('portal_index');
        }
        return view('Special/morespecialarticle',['posts'=>$posts,'ids'=>$ids,'name'=>$name,'home_slides'=>$home_slides]);
    }

    public function loadMoreSpecialArticles(){
        $request = request();
        $ids = $request->param('id');
        $page = $request->param('page');
        $article_model = new TermRelationships($this->encrypt);
        $order='specialorder asc,listorder asc,post_modified desc';
        $posts = $article_model->getStringArticles($ids,$order,config('MORE_PAGESIZE'),$page);
        if($posts){
            if($request->isMobile()){
                $html = '';
                foreach($posts as $k => $v){
                    $html .= '<div class="card"><a href="';
                    $html .= url('articles/detail',array('id'=>$v['tid']),true,true);
                    $html .= '"><div class="media-left" href="';
                    $html .= url('articles/detail',array('id'=>$v['tid']),true,true);
                    $html .= '"><img class="lazy_'.$page.' media-object img-responsive" data-original="';
                    $html .= $v['smeta'];
                    $html .= '"></div><div class="media-body"><h6 class="media-heading">';
                    $html .= $v['post_title'];
                    $html .= '</h6><p><small><span class="pull-left" style="padding-top: .2em">';
                    $html .= date("Y-m-d",$v['post_modified']);
                    $html .= '</span><span> ';
                    $html .= $v['comment_count'];
                    $html .= '评论</span></small></p></div></a></div>';
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                foreach($posts as $k => $v){
                    $posts[$k]['post_modified'] = date('Y-m-d',$v['post_modified']);
                }
                return ['code'=>200,'data'=>$posts,'type'=>'articles'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
    }

    public function detailShow(){
        $request = request();
        $id = $request->param('id');
        $page = $request->param('page')?$request->param('page'):1;
        $article_model = new TermRelationships($this->encrypt);
        $data = $guest = $video_links = [];
        $data = Db::name('special')->where(['id'=>$id])->find();
        if($page < 2){
            $data['content'] = htmlspecialchars_decode($data['content']);
            if(isset($data['guest']) && ($ids = $data['guest'])){
                $guest = Db::name('personal')->where(" id in($ids)")->select();
            }
            $assign['correlations'] = $assign['reviews']  = '';
            if(isset($data['correlation']) && ($correlation = $data['correlation'])){
                $data['corr_ids'] = $correlation;
                $assign['correlations'] = $article_model->getStringArticles($correlation);
            }
            if(isset($data['review']) && ($review = $data['review'])){
                $data['review_ids'] = $review;
                $assign['reviews'] = $article_model->getStringArticles($review);
            }
            if(isset($data['video_link']) && ($video_links = explode(',',$data['video_link']))){
                $video_link = Db::name('special_video')->where(array('id'=>$video_links[0]))->find();
                $data['video_link'] = $video_link['video_link'];
            }
            if(isset($data['other_name']) && $data['other_name']){
                $data['other_name'] = unserialize($data['other_name']);
                $data['morename'] = array_pop($data['other_name']);
            }
        }
        if(isset($data['other_ids']) && $data['other_ids']){
            $data['other_ids'] = unserialize($data['other_ids']);
            $last  = array_pop($data['other_ids']);
            $data['last_ids'] = $last;
            if($page <2){
               foreach($data['other_ids'] as $k => $v){
                    $data['posts'][$k] = $article_model->getStringArticles($v,config('SPECIAL_PAGE'));
                } 
            }
            $data['moreposts'] = $article_model->getStringArticles($last,config('MORE_PAGESIZE'),$page);
        }
        if($data['content']){
            $data['content'] = explode("\r\n",$data['content']);
        }
        $assign['data'] = $data;
        $assign['guest'] = $guest;
        $assign['video_links'] = count($video_links);
        if(!$request->isMobile()){
            $slide_model = new SlideCat();
            $assign['home_slides'] = $slide_model->getHomeSlide('portal_index');
        }
        $assign['app'] = 0;
        if($app = $request->param('app')){
            $assign['app'] = $app;
        }
        return view('Special/detail',$assign);
    }

    public function themeName(){
        $data = db('personal')->where(['id'=>request()->param('id')])->find();
        return view('Special/theme',['data'=>$data]);
    }

}