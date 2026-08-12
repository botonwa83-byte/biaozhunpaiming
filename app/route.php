<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
$route = [];
$route = [
	'articles/:id' => 'articles/detail',
	'hotlistunite/:id' => 'hotlistunite',
	'voterlt/:id' => 'vote/voteresult',
	'votesw/:id' => 'vote/voteshow',
	'comments/:id' => 'comment/index',
	'commentreply/:id/:cid' => 'comment/commentreply',
	'hotwordlist/:id' => 'hotwordlist',
	'login' => 'home/login/login',
	'video/:id' => 'video/detail',
	'special/:id' => 'Special/detail',
	'apply/:id' => 'Apply/detail',
	'report/:id' => 'report/detail',
	'vcomment/:id/:types' => 'comment/vote',
	'vcommentreply/:id/:cid' => 'comment/votecommentreply',
	'rank/:id' => 'Rank/detail',
];
$navs = cache('navs');
if($navs){
	foreach($navs as $k => $v){
		$route_key =  substr($v->href,strpos($v->href,'/')+1,strpos($v->href,'.html')-1);
		if($route_key){
			$route_value =  "home/index/{$route_key}?id={$v->id}";
			$route[$route_key] = $route_value;
		}
	}
}
return $route;
