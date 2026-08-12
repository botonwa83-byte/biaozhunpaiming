<?php
namespace app\admin\controller;
use think\Db;
use app\admin\controller\Auth;

class Setting extends Auth {

	public function site(){
		$options_value = json_decode($this->options[1]['option_value'],true);
		return view('Setting/site',['sys'=>$options_value,'templates'=>null]);
	}

	public function clearcache(){
		$rootdirs = array_map('basename',glob(RUNTIME_PATH."*"));
		$dirs = array ();
		$noneed_clear=array(".","..");
		$rootdirs=array_diff($rootdirs, $noneed_clear);
		foreach ( $rootdirs as $dir ) {
			if ($dir != "." && $dir != "..") {
				$dir = RUNTIME_PATH . $dir;
				if (is_dir ( $dir )) {
					$tmprootdirs = array_map('basename',glob($dir."/*"));
					foreach ( $tmprootdirs as $tdir ) {
						if ($tdir != "." && $tdir != "..") {
							$tdir = $dir . '/' . $tdir;
							if (is_dir ( $tdir )) {
								array_push ( $dirs, $tdir );
							}else{
								@unlink($tdir);
							}
						}
					}
				}else{
					@unlink($dir);
				}
			}
		}
		if(request()->isAjax()){
			return ['status'=>1,'info'=>'清理缓存成功'];
		}else{
			$this->redirect('Index/index');
		}
	}

	// 网站信息设置提交
	public function site_post(){
		if (request()->ispost()) {
			if(isset($_POST['option_id'])){
				$data['option_id']=I('post.option_id',0,'intval');
			}
			$options=I('post.options/a');
			
			$configs["SP_SITE_ADMIN_URL_PASSWORD"]=empty($options['site_admin_url_password'])?"":md5(md5(C("AUTHCODE").$options['site_admin_url_password']));
			$configs["SP_DEFAULT_THEME"]=$options['site_tpl'];
			$configs["DEFAULT_THEME"]=$options['site_tpl'];
			$configs["SP_ADMIN_STYLE"]=$options['site_adminstyle'];
			$configs["URL_MODEL"]=$options['urlmode'];
			$configs["URL_HTML_SUFFIX"]=$options['html_suffix'];
			$configs["COMMENT_NEED_CHECK"]=empty($options['comment_need_check'])?0:1;
			$comment_time_interval=intval($options['comment_time_interval']);
			$configs["COMMENT_TIME_INTERVAL"]=$comment_time_interval;
			$_POST['options']['comment_time_interval']=$comment_time_interval;
			$configs["MOBILE_TPL_ENABLED"]=empty($options['mobile_tpl_enabled'])?0:1;
			$configs["HTML_CACHE_ON"]=empty($options['html_cache_on'])?false:true;
				
			sp_set_dynamic_config($configs);//sae use same function
				
			$data['option_name']="site_options";
			$data['option_value']=json_encode($options);
			if($this->options_model->where("option_name='site_options'")->find()){
				$result=$this->options_model->where("option_name='site_options'")->save($data);
			}else{
				$result=$this->options_model->add($data);
			}
			
			$cmf_settings=I('post.cmf_settings/a');
			$banned_usernames=preg_replace("/[^0-9A-Za-z_\x{4e00}-\x{9fa5}-]/u", ",", $cmf_settings['banned_usernames']);
			$cmf_settings['banned_usernames']=$banned_usernames;

			sp_set_cmf_setting($cmf_settings);
			
			$cdn_settings=I('post.cdn_settings/a');
			sp_set_option('cdn_settings', $cdn_settings);
			
			if ($result!==false) {
				$this->success("保存成功！");
			} else {
				$this->error("保存失败！");
			}
			
		}
	}
	
	// 密码修改
	public function password(){
		return view('Setting/password');
	}
	
	// 密码修改提交
	public function password_post(){
		$request = request();
		if ($request->ispost()) {

			if(empty($request->param('old_password'))){
				return ['status'=>0,'info'=>'原始密码不能为空！','url'=>''];
			}
			if(empty($request->param('password'))){
				return ['status'=>0,'info'=>'新密码不能为空！','url'=>''];
			}

			$user_obj = db('users');
			$uid=session('aid');

			$admin=$user_obj->where(array("id"=>$uid))->find();
			$old_password = $request->param('old_password');
			$password = $request->param('password');

			$old_password_md5="###".md5(md5(config("AUTHCODE").$request->param('old_password')));
			$password_md5="###".md5(md5(config("AUTHCODE").$request->param('password')));

			if($old_password_md5 === $admin['user_pass']){

				if($password == $request->param('repassword')){
					if($password_md5 === $admin['user_pass']){
						return ['status'=>0,'info'=>'新密码不能和原始密码相同！','url'=>''];
					}else{
						$data['user_pass'] = $password_md5;
						$data['id'] = $uid;
						$r=$user_obj->update($data);
						if ($r!==false) {
							return ['status'=>1,'info'=>'修改成功！','url'=>''];
						} else {
							return ['status'=>0,'info'=>'修改失败！','url'=>''];
						}
					}
				}else{
					return ['status'=>0,'info'=>'密码输入不一致！','url'=>''];
				}
			}else{
				return ['status'=>0,'info'=>'原始密码不正确！','url'=>''];
			}
		}
	}

}