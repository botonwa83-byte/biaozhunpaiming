<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
// use app\admin\controller\AuthTp;
//权限认证
class Auth extends Controller{
	protected $request;
	protected $options;
	//初始化
	protected function _initialize(){
        parent::_initialize();
        $this->request = request();

        // if(!$this->request->param('pwd')){	//后台密码访问
        // 	$this->redirect(url('/'));
        // }else{

        // }
        $this->options = cache('admin_options');
        if(!$this->options){
        	$this->options = Db::name('options')->select();
        	cache('admin_options',$this->options);
        }

		//未登陆，不允许直接访问
		if(!session('aid')){
			$this->redirect(url('Login/login'));
		}
		
		//已登录，不需要验证的权限
		$not_check = ['Sys/clear','Index/index'];//不需要检测的控制器/方法

		//不在不需要检测的控制器/方法时,检测
		// if(!in_array($this->request->controller().'/'.$this->request->action(), $not_check)){
		// 	$auth = new AuthTp();
		// 	if(!$auth->check($this->request->controller().'/'.$this->request->action(),session('aid')) && session('aid')!= 1){
		// 		$this->error('没有权限',0,0);
		// 	}
		// }
		
		//获取有权限的菜单tree
		$menus = cache('menus_admin_'.session('aid'));
		if(empty($menus)){
			$m = Db::name('menu');
			$data = $m->where(array('status'=>1))->order('listorder')->select();
			// foreach ($data as $k=>$v){
			// 	if(!$auth->check($v['name'], session('aid')) && session('aid') != 1){
			// 		unset($data[$k]);
			// 	}
			// }
			$menus = $this->node_merge($data);
			// cache('menus_admin_'.session('aid'),$menus);
		}

		$menus_curr = array();
		//当前方法倒推到顶级菜单数组
		if($this->request->controller() != 'Index'){
			$menus_curr = $this->get_menus_admin();
			//如果$menus_curr为空,则根据'控制器/方法'取status=0的menu
			if(empty($menus_curr)){
				$rst = Db::name('menu')->where(array('model'=>$this->request->controller(),'action'=>$this->request->action()))->order('listorder')->limit(1)->select();
				if($rst){
					$parentid = $rst[0]['parentid'];
					$parent = Db::name('menu')->where(array('id'=>$parentid))->find();
					//再取父级
					$rst = Db::name('menu')->where(array('id'=>$parent['parentid']))->find();
					$menus_curr = $this->get_menus_admin($rst['model'],$rst['action']);
				}
			}
		}

		//取当前操作菜单父ID
		if(count($menus_curr)>=4){
			$pid=$menus_curr[1];
			$id_curr=$menus_curr[2];
		}elseif(count($menus_curr)>=2){
			$pid=$menus_curr[count($menus_curr)-2];
			$id_curr=end($menus_curr);
		}else{
			$pid='0';
			$id_curr=(count($menus_curr)>0)?end($menus_curr):'';
		}
		//取$pid下子菜单
		$menus_child = Db::name('menu')->where(array('status'=>1,'parentid'=>$pid))->order('listorder')->select();
		// dump($menus);
		// dump($menus_child);
		// dump($id_curr);
		$this->assign('menus_curr',$menus_curr);
		$this->assign('menus',$menus);
		$this->assign('menus_child',$menus_child);
		$this->assign('id_curr',$id_curr);
	}

	/**
	 * 倒推后台菜单数组
	 * $str String '方法名'或'控制器名/方法名'，为空则为'当前控制器/当前方法'
	 * $status int 获取的menu是否含全部状态，还是仅status=1。不为0和1时,不限制
	 * $arr boolean 是否返回全部数据数组，默认假，仅返回ids
	 * @author rainfer <81818832@qq.com>
	 */
	public function get_menus_admin($model='',$action='',$status=1,$arr=false)
	{
		// $str = empty($str)?$this->request->controller().'/'.$this->request->action():$str;
		// if(strpos($str,'/')===false){
		// 	$str .= $this->request->controller();
		// }
		$status = empty($status)?1:$status;
		$arr = empty($arr)?false:true;

		$where['model'] = !empty($model)?$model:$this->request->controller();
		$where['action'] = !empty($action)?$action:$this->request->action();
		if($status==0 || $status==1){
			$where['status']=$status;
		}
		$arr_rst = array();
		$rst = Db::name('menu')->where($where)->order('listorder')->limit(1)->select();
		if($rst){
			$rst = $rst[0];
			if($arr){
				$arr_rst[]=$rst;
			}else{
				$arr_rst[]=$rst['id'];
			}
			$pid=$rst['parentid'];
			while(intval($pid)!=0) {
				//非顶级
				$rst=Db::name('menu')->where(array('id'=>$pid))->find();
				if($arr){
					$arr_rst[]=$rst;
				}else{
					$arr_rst[]=$rst['id'];
				}
				$pid=$rst['parentid'];	
			} 
		}
		return array_reverse($arr_rst);
	}

	/**
	 * 递归重组节点信息为多维数组
	 *
	 * @param array $node
	 * @param number $pid
	 * @author rainfer <81818832@qq.com>
	 */
	public function node_merge(&$node, $pid = 0, $id_name = 'id', $pid_name = 'parentid', $child_name = '_child')
	{
	    $arr = array();

	    foreach ($node as $v) {
	        if ($v [$pid_name] == $pid) {
	            $v [$child_name] = $this->node_merge($node, $v [$id_name], $id_name, $pid_name, $child_name);
	            $arr [] = $v;
	        }
	    }

	    return $arr;
	}
}