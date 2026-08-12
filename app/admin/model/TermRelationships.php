<?php

namespace app\admin\model;
use think\Model;
use think\Db;
class TermRelationships extends Model
{
	protected $table = 'bzpm_term_relationships';

	protected function initialize()
	{
		parent::initialize();
		
	}

	public function getArticles($request='',$map = []){
        if($param = request()->param()){
            $keytype = isset($param['keytype'])?$param['keytype']:'';
            $key = isset($param['key'])?$param['key']:'';
            $opentype_check = isset($param['opentype_check'])?$param['opentype_check']:'';
            $diyflag = isset($param['diyflag'])?$param['diyflag']:'';
            $sldate = isset($param['reservation'])?$param['reservation']:'';
            $page = isset($param['p'])?$param['p']:1;
            $arr = explode(" - ",$sldate);
            if(count($arr)==2){
                $arrdateone = strtotime($arr[0]);
                $arrdatetwo = strtotime($arr[1].' 23:55:55');
                $map['post_modified'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
            }
            if($keytype == 'post_title'){
                $map[$keytype]= array('like',"%".$key."%");
            }elseif($keytype == 'post_author_name'){
                $map['post_author_name']= array('like',"%".$key."%");
            }elseif($keytype){
                $map[$keytype] = $key;
            }
            if ($opentype_check!=''){
                $map['post_status']= array('eq',$opentype_check);
            }
        }
        $map['back'] = 0;
		$field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,status,post_hits,term_id,post_author_name,listorder,post_hits_sure';
		$posts = $this->alias('a')->field($field)
    	->join(' bzpm_posts b','a.object_id = b.id','LEFT')
    	->join(' bzpm_users c','b.post_author = c.id','LEFT')
    	->where($map)
    	->order('post_date desc,listorder asc')
        ->paginate(config('PAGESIZE'),false,['query'=>$param]);
    	return ['posts'=>$posts,'page'=>$posts->render(),'page_min'=>$posts->render()];
	}

    public function getArticle($tid){
        $where['tid'] = $tid;
        $article = $this->alias('a')
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->find();
        return $article;

    }

    public static function getTerms(){
        $temrs = Db::name('terms')->where(['status'=>1])->order('listorder')->select();
        $new_terms = [];
        foreach($temrs as $k => $v){
            $new_terms[$v['term_id']] = $v;
        }
        return  $new_terms;
    }

    public function getTermids($object_id){
       return $this->field('term_id')->where(['object_id'=>$object_id,'back'=>0])->select();
    }
    
    public function state($tid){
        $status = $this->where(['tid'=>$tid])->find();
        $status = $status['status'];
        if($status == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        $this->where(['tid'=>$tid])->update(['status'=>$status]);
        return $status;
    }

    public function add($sldate){
       return $this->insertAll($sldate);
    }

    public function recycle($request,$map = [])
    {

        $keytype = $request->param('keytype');
        $key = $request->param('key');
        $opentype_check = $request->param('opentype_check');
        $diyflag = $request->param('diyflag','');
        $sldate = $request->param('reservation');
        $arr = explode(" - ",$sldate);
        if(count($arr)==2){
            $arrdateone = strtotime($arr[0]);
            $arrdatetwo = strtotime($arr[1].' 23:55:55');
            $map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
        if($keytype == 'post_title'){
            $map[$keytype]= array('like',"%".$key."%");
        }elseif($keytype == 'post_author_name'){
            $map['post_author_name']= array('like',"%".$key."%");
        }elseif($key){
            $map[$keytype]= $key;
        }
        if ($opentype_check!=''){
            $map['post_status']= array('eq',$opentype_check);
        }
        $field = 'tid,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,status,post_hits,term_id,post_author_name';
        $posts = $this->alias('a')->field($field)
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($map)
        ->order('post_date desc,listorder asc')
        ->paginate(config('PAGESIZE'),false,['query'=>$request->param()]);
        return ['posts'=>$posts,'page'=>$posts->render(),'page_min'=>$posts->render()];
    }

    public function backOpen($tid){
        return $this->where(['tid'=>$tid])->update(['status'=>1,'back'=>0]);
    }

    //获取指定字符串的相关文章
    public function getStringArticles($ids,$page=1,$falg=0){
        $field = 'tid,post_title,specialorder';
        $where['tid'] = ['IN',$ids];
        $posts = $this->alias('a')->field($field)
        ->join(' bzpm_posts b','a.object_id = b.id','LEFT')
        ->join(' bzpm_users c','b.post_author = c.id','LEFT')
        ->where($where)
        ->order('listorder asc,post_modified desc')
        ->select();
        return $posts;
    }

}
