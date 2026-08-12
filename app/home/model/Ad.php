<?php

namespace app\home\model;
use think\Model;
class Ad extends Model
{
	protected $table = 'bzpm_ad';

	protected function _initialize()
	{
		parent::_initialize();
		
	}

	public function getAds($term_id,$posts,$offset1,$offset2){
	    $ads = $this->where(" ad_order between $offset1+1 and $offset2 AND ad_status = 1 AND ad_term_id = $term_id ")->order('ad_order asc')->select();
	    if($ads){
	        foreach($ads as $k => $v){
	            $order = $v['ad_order']%config('MORE_PAGESIZE');
	            $order = $order?$order:config('MORE_PAGESIZE');
	            $fore = array_splice($posts,0,$order+$k);
	            $fore[] = $v;
	            $posts = array_merge($fore,$posts);
	        }
	    }
	    return $posts;
	}

}
