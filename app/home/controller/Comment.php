<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\Comments;
use app\home\model\TermRelationships;
use think\Db;

class Comment extends Base
{
    protected $comment_model;
	public function _initialize()
    {
        parent::_initialize();
        $this->comment_model = new Comments();
    }

    public function index()
    {
        $request = request();
        $ob_id = $request->param('id');
        $ip = $request->ip(true);
        $field = 'tid,object_id,post_keywords,post_title,post_author_name,post_modified,post_mime_type,post_hits,comment_count,smeta';
        $article_model = new TermRelationships();
        $post = $article_model->getObjectArticle($field,$ob_id);
        $post['semta'] = json_decode($post['smeta'],true);
        $commentlist = $this->comment_model->getCommentlist($request);
        //微信js接口
        $wechat_jsapi_data = $this->wechatJsapi($request);
        $assign = [
            'post' => $post,
            'commentlist' => $commentlist,
            'ob_id' => $ob_id, 
            'time' => $wechat_jsapi_data['time'],
            'nonceStr' =>$wechat_jsapi_data['nonceStr'],
            'signature' =>$wechat_jsapi_data['signature'],
            'url' =>$wechat_jsapi_data['url'],
            'encrypt_id' => $this->encrypt->encrypt($post['tid'])
        ];
        $new_dolikes = [];
        $user = session('user')?session('user'):[];
        if($dolikes = $this->comment_model->getDolike($ob_id,0,$ip,$user)){
            foreach($dolikes as $k => $v){
                $new_dolikes[] = $v['cid'];
            }
        }
        $assign['dolikes'] = $new_dolikes?$new_dolikes:[];
        return view('Comment/comment',$assign);
    }

    public function vote(){
        $request = request();
        $id = $request->param('id');
        $ip = $request->ip(true);
        $field = 'id,title,description,keywords,hits,createtime,endtime,vote_author_name,smeta';
        $vote = db('vote')->field($field)->where(['id'=>$id])->find();
        $vote['semta'] = json_decode($vote['smeta'],true);
        $commentlist = $this->comment_model->getCommentlist($request,2);
        //微信js接口
        $wechat_jsapi_data = $this->wechatJsapi($request);
        $assign = [
            'vote' => $vote,
            'commentlist' => $commentlist,
            'navs' => $this->navs,
            'vote_id' => $id, 
            'time' => $wechat_jsapi_data['time'],
            'nonceStr' =>$wechat_jsapi_data['nonceStr'],
            'signature' =>$wechat_jsapi_data['signature'],
            'url' =>$wechat_jsapi_data['url'],
        ];
        $new_dolikes = [];
        $user = session('user')?session('user'):[];
        if($dolikes = $this->comment_model->getDolike($id,0,$ip,$user,2)){
            foreach($dolikes as $k => $v){
                $new_dolikes[] = $v['cid'];
            }
        }
        $assign['dolikes'] = $new_dolikes?$new_dolikes:[];
        return view('Vote/comment',$assign);
    }

    public function present(){
        $request = request();
        $data = $request->post();
        if($this->comment_model->checkBandWord($data['content'])>0){
            return ['status'=>0,'info'=>'评论中含有敏感词汇！'];
        }
        if(isset($data['ob_id']) && !empty($data['content'])){
            if($this->comment_model->addCommentContent($data,$request->ip(true))){
                if($request->isMobile()){
                    $url = $request->server('HTTP_REFERER');
                }else{
                    if(isset($data['flag']) && $data['flag'] == 1){
                        $url = url('comment/index',['id'=>$data['ob_id']]);
                    }elseif(isset($data['flag']) && $data['flag'] == 2){
                        $url = url('comment/vote',['id'=>$data['ob_id'],'type'=>2]);
                    }else{
                        $url = $request->server('HTTP_REFERER');
                    }
                }
                return ['status'=>1,'info'=>'评论成功！','url'=>$url];
            }else{
                return ['status'=>0,'info'=>'评论失败，稍后再试！'];
            }
        }else{
            return ['status'=>0,'info'=>'评论失败，稍后再试！'];
        }
    }

    public function doLike(){
        $request = request();
        $user = session('user')?session('user'):'';
        $pid = $request->param('ob_id');
        $cid = $request->param('id');
        $type = $request->param('type');
        $ip = $request->ip(true);
        if($this->comment_model->getDolike($pid,$cid,$ip,$user,$type)){
            return ['status'=>0,'info'=>'点赞失败！'];
        }
        if(Db::name('comments')->where(['id'=>$request->param('id')])->setInc('com_like')){
            $dolike_data = [
                'uid'=>isset($user['id'])?$user['id']:0,
                'pid'=>$pid,
                'time'=>time(),
                'ip'=>$ip,
                'cid'=>$cid,
                'type'=>$type
            ];
            if(Db::name('comments_dolike')->insert($dolike_data)){
                return ['status'=>1,'info'=>'点赞成功！']; 
            }else{
                return ['status'=>1,'info'=>'点赞失败！'];
            }
        }else{
            return ['status'=>1,'info'=>'点赞失败！'];
        }
    }

    public function commentReply(){
        $request = request();
        $user = session('user')?session('user'):[];
        $new_dolikes = [];
        $ob_id = $request->param('id');
        $ip = $request->ip(true);
        if($dolikes = $this->comment_model->getDolike($ob_id,0,$ip,$user,1)){
            foreach($dolikes as $k => $v){
                $new_dolikes[] = $v['cid'];
            }
        }
        $comment = $this->comment_model->getCommentReply($request);
        return view('Comment/comreply',['comment' => $comment,'dolikes'=>$new_dolikes]);
    }

    public function voteCommentReply(){
        $request = request();
        $user = session('user')?session('user'):[];
        $new_dolikes = [];
        $ob_id = $request->param('id');
        $ip = $request->ip(true);
        if($dolikes = $this->comment_model->getDolike($ob_id,0,$ip,$user,2)){
            foreach($dolikes as $k => $v){
                $new_dolikes[] = $v['cid'];
            }
        }
        $comment = $this->comment_model->getCommentReply($request);
        return view('Vote/comreply',['comment' => $comment,'dolikes'=>$new_dolikes]);
    }

    public function commentMove(){
        $result = Db::name('comment')->where(['status'=>1])->select();
        foreach($result as $k => $v){
            unset($v['id']);
            $v['type'] = 2;
            Db::name('comments')->insert($v);
        }
    }

    public function del($id){
        if(db('comments')->where(['id'=>$id])->update(['status'=>0])){
            return ['status'=>1,'info'=>'删除成功！','url'=>''];
        }else{
            return ['status'=>0,'info'=>'删除失败！','url'=>''];
        }
    }

}
