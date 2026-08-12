<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\TermRelationships AS Article;
use app\home\model\SlideCat;
use think\Request;
class Rank extends Base{

    public function detail(){
        $request = request();
        $id = $request->param('id');
        //获取主排行榜数据
        $rank = db('rank_list')->where(['id'=>$id])->find();
        //获取品牌数据
        $brands = db('rank_brands')->where(['rank_list_id'=>$id,'status'=>1,'back'=>1])->order('listorder asc')->select();
        //搜索品牌
        if($post_data = $request->post()){
            if(!empty($post_data['keyword'])){
                $keyword = $post_data['keyword'];
                $brand_ids = db('rank_brands')->field('id')->where(['rank_list_id'=>$id,'brand_name'=>['like',"%$keyword%"]])->select();
                $brand_ids_old_ids = array_column($brands,'brand_id');
                foreach($brand_ids as $v){
                    if(in_array($v,$brand_ids_old_ids)){
                        $brand_ids_arr[] = $v['id'];
                    }
                }
            }
        }
        //计算月份
        $curr_month = date("m",time());
        $curr_month = $curr_month>$rank['month']?$rank['month']:$curr_month;
        $month = $request->param('month') ? $request->param('month') : $curr_month;

        $brands_list = [];
        array_map(function($data) use (&$brands_list){
            $brands_list[$data['id']] = $data;
        },$brands);

        //获取当前月份分数数据
        $curr_scores = db('rank_score')->field('score,brand_id')->where(['status'=>1,'month'=>$month,'brand_id'=>["IN",implode(',',array_keys($brands_list))]])->order('score desc')->limit($rank['number'])->select();
        // dump($curr_scores);die;

        // $curr_scores_others = db('rank_score')->field('score,brand_id')->where(['status'=>1,'month'=>$month,'brand_id'=>["IN",implode(',',array_keys($brands_list))]])->order('score desc')->limit(50,50)->select();

        //获取上月数据
        $last_scores_data = [];
        if($month - 1 >= 0){
            $last_month = $month-1;
            $last_scores = db('rank_score')->field('score,brand_id')->where(['status'=>1,'month'=>$last_month,'brand_id'=>["IN",implode(',',array_keys($brands_list))]])->order('score desc')->select();
            if($last_scores){
                array_map(function($data) use (&$last_scores_data){
                    $last_scores_data[$data['brand_id']] = $data;
                },$last_scores);
            }
        }

        $assign = [
            'rank' => $rank,
            'curr_month' => $curr_month,
            'month' => $month,
            'brands' => $brands_list,
            'curr_scores' => $curr_scores,
            // 'curr_scores_others' => $curr_scores_others,
            'last_scores_data' => $last_scores_data,
            'brand_ids_arr' => isset($brand_ids_arr)?$brand_ids_arr:[],
            'keyword' => isset($keyword)?$keyword:'',
        ];
        unset($brands_list);
        unset($last_scores_data);
        return view('Rank/detail',$assign);
    }

    public function brandList()
    {
        return view('Rank/index');
    }

    public function getSearchBrandList(Request $request)
    {
        if(strlen($request->param('keyword')) > 0){
            $keyword = $request->param('keyword');
            $brand = db('rank_brands')
                ->alias('a')
                ->field('a.*,b.status,b.rank_type')
                ->join('bzpm_rank_list b','a.rank_list_id=b.id','left')
                ->where('brand_name','like',"%$keyword%")
                ->where('b.status',1)
                ->where('back',1)
                ->find();
            if($brand){
                $brand['details'] = db('rank_brands_detail')
                    ->where('rank_list_id',$brand['id'])
                    ->order('listorder desc','id desc')
                    ->select();
                $this->result($brand,200,'获取成功','json');
            }
        }
        $this->result(null,200,'获取成功','json');
    }

    public function getBrandList(Request $request)
    {
        $brands_list = db('rank_brands')
            ->where('status',1)
            ->where('back',1)
            ->where('rank_list_id',$request->rank_list_id ?: 51)
            ->order('exponent desc','listorder desc')
            ->limit(50)
            ->select();
        
        if($brands_list){
            $this->result($brands_list,200,'获取成功1','json');
        }
        $this->result(null,200,'获取成功','json');
    }

    public function getArticleListbak(Request $request)
    {
        $article_model = new Article($this->encrypt);
        $posts = $article_model->getArticleList(1,1,3);
        $slide_model = new SlideCat();
        $home_slides = $slide_model->getHomeSlide('portal_index');
        if($posts && $home_slides){
            foreach($posts as &$post){
                $post['post_content'] = mb_convert_encoding(mb_substr(strip_tags(htmlspecialchars_decode($post['post_content'])),0,304),'UTF-8','UTF-8,GBK,GB2312,BIG5');
                rtrim('?',$post['post_content']);
            }
            foreach($home_slides as $home_slide){
                $home_slide['image_url'] = 'http://www.biaozhunpaiming.com'.$home_slide['slide_pic'];
            }
            $this->result(['article_list'=>$posts,'banner_list'=>$home_slides],200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
    }

    public function getArticleList(Request $request)
    {
		$posts1 =  $this->getArticleTypeList(6);
        $ids[] = $posts1['id'];
        $posts[] = $posts1;
		$posts2 =  $this->getArticleTypeList(5,$ids);
        $ids[] = $posts2['id'];
        $posts[] = $posts2;
		$posts[] =  $this->getArticleTypeList(3,$ids);
		$this->result(['article_list'=>$posts],200,'获取成功','json');
    }

    public function getArticleTypeList($type = 1,$tids = [])
    {
        $article_model = new Article($this->encrypt);
        $posts = $article_model->getArticleList($type,1,1,$tids);
        $post = $posts[0];
        $post['post_content'] = mb_convert_encoding(mb_substr(strip_tags(htmlspecialchars_decode($post['post_content'])),0,304),'UTF-8','UTF-8,GBK,GB2312,BIG5');
        rtrim('?',$post['post_content']);
        return $post;
    }

    public function getRandList(Request $request)
    {
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
        // $new_brand_list = [];
        // $rank = 1;
        // foreach($new_brands as $key => $list){
        //     if($rank > 50){
        //         break;
        //     }
        //     if($key > 0){
        //         if(
        //             $list['exponent'] == $new_brands[$key-1]['exponent']
        //             &&
        //             $key != 26
        //             &&
        //             $key != 49
        //         ){
        //             $list['rank'] = $rank;
        //             $list['rank_key_num'] = $key;
        //             $new_brand_list[] = $list;
        //         }else{
        //             $rank++;
        //             $list['rank'] = $rank;
        //             $list['rank_key_num'] = $key;
        //             $new_brand_list[] = $list;
        //         }
        //     }else{
        //         $list['rank'] = $rank;
        //         $list['rank_key_num'] = $key;
        //         $new_brand_list[] = $list;
        //     }
        // }
        if($new_brands){
            $this->result($new_brands,200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
    }

    public function getTermArticle($term_id,$page)
    {
        $article = new Article();
        $terms = $article->getArticles($term_id,$page);
        if($terms){
            foreach($terms as $k => &$v){
                $v['image_url'] = 'http://www.biaozhunpaiming.com/data/upload/'.json_decode($v['smeta'],true)['thumb'];
            }
            $this->result($terms,200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
    }

    public function getJdarticleList()
    {
        return $this->getTermArticle(5,$this->request->get('page'));
    }

    public function getZxarticleList()
    {
        return $this->getTermArticle(3,$this->request->get('page'));
    }

    public function getJzarticleList()
    {
        return $this->getTermArticle(7,$this->request->get('page'));
    }

    public function getYyarticleList()
    {
        return $this->getTermArticle(8,$this->request->get('page'));
    }

    public function getSparticleList()
    {
        return $this->getTermArticle(9,$this->request->get('page'));
    }

    public function getQcarticleList()
    {
        return $this->getTermArticle(10,$this->request->get('page'));
    }

    public function getCsarticleList()
    {
        return $this->getTermArticle(11,$this->request->get('page'));
    }
    
    public function getBannerlist()
    {
        $banners = db('slide')->field('slide_name,slide_pic,slide_url')->where('slide_status',1)->where('slide_type',1)->order('listorder','asc')->select();
        if($banners){
            foreach($banners as &$banner){
                $banner['slide_url'] ='http://www.biaozhunpaiming.com/articles/'.$banner['slide_url'];
                $banner['slide_pic'] = 'http://www.biaozhunpaiming.com'.$banner['slide_pic'];
            }
            $this->result($banners,200,'获取成功','json');
        }
        $this->result(null,200,'获取成功','json');
    }
}
