<?php
namespace app\admin\controller;
use Think\Db;
use app\admin\controller\Auth;

class Menu extends Auth {

	public function lists(){
		return view('Menu/lists',['admin_rule'=>$this->getAdminrule()]);
	}

	public function admin_rule_edit(){
		$id = request()->param('id');
		$rule = Db::name('menu')->where(['id'=>$id])->find();
		return view('Menu/admin_rule_edit',['rule'=>$rule,'admin_rule'=>$this->getAdminrule()]);
	}

	public function getAdminrule(){
		$lists = new \Util\Leftnav();
		$admin_rule = Db::name('menu')->order('listorder')->select();
		return $lists::rule($admin_rule);
	}

	public function admin_rule_runedit(){
		$param = request()->param();
		$param['status'] = isset($param['status'])?$param['status']:0;
		if(Db::name('menu')->update($param)){
			return ['status'=>1,'info'=>'菜单信息设置成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'菜单信息设置失败','url'=>url('lists')];
		}
	}

	public function admin_rule_runadd(){
		$param = request()->param();
		$result = Db::name('menu')->insert($param);
		if($result){
			return ['status'=>1,'info'=>'菜单信息设置成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'菜单信息设置失败','url'=>url('lists')];
		}
	}

	public function admin_rule_del(){
		$param = request()->param();
		$param['status'] = isset($param['status'])?$param['status']:0;
		if(Db::name('menu')->delete($param)){
			return ['status'=>1,'info'=>'菜单信息设置成功','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'菜单信息设置失败','url'=>url('lists')];
		}
	}

	public function admin_rule_state(){
		$param = request()->param();
		$meun = Db::name('menu')->where(['id'=>$param['x']])->find();
		$param['status'] = $meun['status']==1?0:1;
		$param['id'] = $param['x'];
		unset($param['x']);
		Db::name('menu')->update($param);
		if($param['status']){
			return ['status'=>1,'info'=>'显示','url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>'隐藏','url'=>url('lists')];
		}
	}

}