<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;

class Portal extends Auth {

	public function lists(){
		$lists = new \Util\Leftnav();
		$admin_rule = Db::name('menu')->order('listorder')->select();
		$arr = $lists::rule($admin_rule);
		// dump($arr);
		return view('Menu/lists',['admin_rule'=>$arr]);
	}

}