<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
class Login extends Controller{
	//登入页面
	public function login()
	{
		//已登录,跳转到首页
		if(session('aid')){
			$this->redirect(url('Index/index'),'');
		}else{
			return view('Login/login');
		}
	}

	//登陆验证
	public function runlogin()
	{
		$request = request();
		$username = $request->param('admin_username');
		$password = $request->param('admin_pwd');
		$forget = $request->param('forget');
		$verify =new \verify\Verify();
		if(!$verify->check($request->param('verify'),'aid')){
			return ['status'=>0,'info'=>'验证码错误','url'=>''];
		}
		$userModel = new User();
		if($userModel->loginRun($username,$password,$forget)){
			return ['status'=>1,'info'=>'登录成功','url'=>url('admin/index/index')];
		}else{
			return ['status'=>0,'info'=>'账号密码错误','url'=>''];
		}
	}
	
	//验证码
	public function verify()
    {
        if (session('aid')) {
            redirect(url('Index/index'));
            return;
        }
		ob_end_clean();
        $verify = new \verify\Verify(array(
            'fontSize' => 20,
            'imageH' => 42,
            'imageW' => 250,
            'length' => 5,
            'useCurve' => false,
            'useNoise' => false,
        ));
        $verify->entry('aid');
    }

	/*
     * 退出登录
     */
	public function logout()
	{
		session('aid',null);
		session('admin_username',null);
		session('admin_realname',null);
		session('admin_avatar',null);
		session('admin_last_change_pwd_time', null);
		$this->redirect('Login/login');
	}

}