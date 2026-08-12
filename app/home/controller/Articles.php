<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\TermRelationships;
use app\home\model\SlideCat;

class Articles extends Base
{
    public function detail()
    {
        $request = request();
        $id = $request->param('id');
        if(is_numeric($id)){
            $decrypt_id = $id;
        }else{
            $decrypt_id = $this->encrypt->decrypt($id);//解密
        }
        //增加文章点击量
        $article_model = new TermRelationships();
        //缓存
        // $request->cache('articles/:id',300);
        $vote_model = new \app\home\model\Votes();
        //获取文章数据
        $article_data = $article_model->getArticle($decrypt_id);
        $article_model->articleHits($article_data['object_id']);
        //文章数据处理
        $article_data['smeta'] = json_decode($article_data['smeta'],true);
        $article_data['post_content'] = str_replace('47.94.199.232','www.biaozhunpaiming.com',$article_data['post_content']);
        $article_data['post_content'] = htmlspecialchars_decode($article_data['post_content']);
        //相关文章
        $coarticles = $article_model->getCoarticles($decrypt_id,$article_data['term_id'],$article_data['correlation'],$article_data['post_keywords']);
        //加密
        foreach($coarticles as $k => $v){
            $coarticles[$k]['tid'] = $this->encrypt->encrypt($v['tid']);
        }
        //收藏和点赞
        $dolike = $this->getDolike($article_data['object_id'],$request->ip(true),session('user'));
        $favorite = $this->getFavorite(session('uid'),$article_data['object_id']);
        
        $vote_check = false;
        $choices = [];
        $vote = [];
        //投票
        // if(!empty($article_data['covoting'])){
        //     $ip = $request->ip(true);
        //     $vote = $vote_model->getVote($article_data['covoting'],true);
        //     $choices = $vote_model->checkRecord($article_data['covoting'],$ip);
        //     $vote_check = $choices || $vote['endtime']< time();
        // }

        $assign = [
            'article_data'=>$article_data,
            'vote' => $vote,
            'coarticles'=>$coarticles,
            'dolike' => $dolike,
            'favorite' => $favorite,
            'is_wechat' => $this->is_wechat,
            'vote_check' => $vote_check,
            'choices' => is_array($choices)?$choices:[],
        ];
        if(!$request->isMobile()){
            // $assign['navs'] = $this->navs;
            $assign['nav_id'] = $article_data['term_id'];
        }
        //微信js接口
        if($this->is_wechat){
            $wechat_jsapi_data = $this->wechatJsapi($request);
            $assign['time'] = $wechat_jsapi_data['time'];
            $assign['nonceStr'] = $wechat_jsapi_data['nonceStr'];
            $assign['signature'] = $wechat_jsapi_data['signature'];
            $assign['url'] = $wechat_jsapi_data['url'];
            $assign['is_wechat'] = $this->is_wechat;
        }
        return view('Articles/articles',$assign);
    }

    public function doLike(){
        $request = request();
        $user = session('user')?session('user'):'';
        $pid = $request->param('id');
        $ip = $request->ip(true);
        if($this->getDolike($pid,$ip,$user)){
            return ['status'=>0,'info'=>'点赞失败！'];
        }
        if(db('posts')->where(['id'=>$request->param('id')])->setInc('post_like')){
            $dolike_data = [
                'uid'=>isset($user['id'])?$user['id']:0,
                'pid'=>$pid,
                'time'=>time(),
                'ip'=>$ip,
                'type' => 1,
            ];
            if(db('dolike')->insert($dolike_data)){
                return ['status'=>1,'info'=>'点赞成功！']; 
            }else{
                return ['status'=>1,'info'=>'点赞失败！'];
            }
        }else{
            return ['status'=>1,'info'=>'点赞失败！'];
        }
    }

    public function getDolike($pid=0,$ip=0,$user=''){
        $pid?$where['pid']=$pid:'';
        $where['type'] = 1;
        if($uid = isset($user['id'])?$user['id']:0){
            $where['uid'] = $uid;
        }else{
            $where['ip'] = $ip;
        }
        return db('dolike')->field('pid')->where($where)->find();
    }

    public function favorite(){
        $request = request();
        if(!$uid = session('uid')){
            return ['status'=>0,'info'=>'收藏失败！'];
        }
        $object_id = $request->param('id');
        if($result = $this->getFavorite($uid,$object_id,true)){
            if($result['favo_status'] == 1){
                return ['status'=>0,'info'=>'取消收藏成功！'];
            }else{
                return ['status'=>1,'info'=>'收藏成功！'];
            }
        }else{
           $favorite_data = [
                'uid'=>isset($uid)?$uid:0,
                'object_id'=>$object_id,
                'createtime'=>time(),
                'favo_status' => 1
            ];
            if(db('user_favorites')->insert($favorite_data)){
                return ['status'=>1,'info'=>'收藏成功！']; 
            }else{
                return ['status'=>0,'info'=>'收藏失败！'];
            } 
        }
    }

    public function getFavorite($uid,$object_id,$status=false){
        if($favs = db('user_favorites')->where(['uid'=>$uid,'object_id'=>$object_id])->find()){
            if($status){
                if($favs['favo_status'] == 0){
                    $favo_status = 1;
                }else{
                    $favo_status = 0; 
                }
                db('user_favorites')->where(['uid'=>$uid,'object_id'=>$object_id])->update(['favo_status'=>$favo_status]); 
            }
            return $favs;
        }else{
            return false;
        }
    }

}
