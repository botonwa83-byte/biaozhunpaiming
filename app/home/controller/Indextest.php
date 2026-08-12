<?php
namespace app\home\controller;
use app\home\controller\Base3;
use app\home\model\TermRelationships AS Article;
use app\home\model\SlideCat;
use app\home\model\Ad;
use think\Db;

class Indextest extends Base3
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
        $request = request();
        
         $rank = db('rank_list')->where(['status'=>1])->find();
        //获取品牌数据
        $brands = db('rank_brands')->where(['rank_list_id'=>$rank['id'],'status'=>1,'back'=>1])->order('listorder asc')->select();
        foreach($brands as &$brand){
            $brand['rank'] = $brand['listorder'];
            $brands_ids[] = $brand['id'];
        }
        $brand_scores = db('rank_score')->where('brand_id','in',implode(',',$brands_ids))->where('status',1)->order('score desc')->limit(100)->select();
        $brands = array_column($brands,null,'id');
        foreach($brand_scores as &$brand){
            if($brand['last_score'] <= 0){
                $brand['last_score'] = '-';
            }
           if(count($brand) != count($brand, 1)){
                //arsort($brand_scores[$brand['id']],'score');
                $brands[$brand['brand_id']]['score'] = $brand[0];
            }else{
                $brands[$brand['brand_id']]['score'] = $brand;
            }
            $new_brands[] = $brands[$brand['brand_id']];
        }
    
        dump($new_brands);die;
        if($new_brand_list){
            $this->result($new_brand_list,200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
        
    }

}
