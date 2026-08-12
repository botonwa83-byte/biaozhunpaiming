<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\TermRelationships AS Article;
use app\home\model\SlideCat;
use app\home\model\Ad;
use think\Db;

class Index extends Base
{
    protected $slide_model;
    protected $article_model;

    public function _initialize()
    {
        parent::_initialize();
        $this->slide_model = new SlideCat();
        $this->article_model = new Article($this->encrypt);
    }

    //首页
    public function index()
    {
        $this->redirect('http://www.biaozhunpaiming.com/home/rank/brandList#/home');
        $request = request();
    	$posts = $this->article_model->getArticles(1);
        array_map(function($data){$data['tid'] = $this->encrypt->encrypt($data['tid']);},$posts);
        $ad_model = new Ad();
        $posts = $ad_model->getAds(1,$posts,0,config('MORE_PAGESIZE'));
        $home_slides = $this->slide_model->getHomeSlide('portal_index');
        array_map(function($data){
            if($data['slide_type'] == 1 || $data['slide_type'] == 4){
                $data['slide_url'] = $this->encrypt->encrypt($data['slide_url']);
            }
        },$home_slides);

        $special = [];
        if(!$request->isMobile()){
            $special = db('special')->field('id,speacial_name,description,smeta')->where(['status'=>1])->order('createtime desc')->select();
        }

        $assign = [
            'posts' => $posts,
            'special' => $special,
            'home_slides' => $home_slides,
            'nav_id' => 1
        ];
        return view('Index/index',$assign);
    }

    //加载文章
    public function loadArticles()
    {
        $request = request();
        return $this->article_model->loadArticlesData($request);
    }

    public function exponential(){
        $nav_id = request()->param('id');
        $posts = $this->article_model->getArticles($nav_id,1,10);
        $assign = [
            'posts'=> $posts,
            'nav_id'=>$nav_id,
        ];
        return view('Index/exponential',$assign);
    }

    public function finance()
    {
        $request = request();
        $nav_id = $request->param('id');
        $posts = $this->article_model->getArticles($nav_id,1,10);
        $ad_model = new Ad();
        $posts = $ad_model->getAds($nav_id,$posts,0,config('MORE_PAGESIZE'));
        $assign = [
            'posts'=> $posts,
            'nav_id'=>$nav_id,
        ];
        return view('Index/finance',$assign);
    }

    public function loadFinance(){
        $request = request();
        return $this->article_model->loadOrderData($request);
    }

    //投票
    public function votes()
    {
        $request = request();
        $nav_id = $request->param('id');
        $votes = [];
        $vote_slides = $this->slide_model->getHomeSlide('vote_index');
    	$votes = db('vote')
        ->where(['vote_status'=>1])
        ->order('q_order asc,createtime desc')
        ->limit(config('MORE_PAGESIZE'))
        ->select();
        $assign = [
            'votes' => $votes,
            'nav_id' => $nav_id,
        ];
        $assign['vote_slides'] = $vote_slides;
        return view('Index/votes',$assign);
    }

    public function loadVotes()
    {
        $request = request();
        return $this->article_model->loadVotesData($request);
    }


    public function ceshisms(){
        phpinfo();
    }

}
