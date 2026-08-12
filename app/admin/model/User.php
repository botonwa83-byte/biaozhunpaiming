<?php

namespace app\admin\model;
use think\Model;
class User extends Model
{
	protected $table = 'bzpm_users';

	protected function initialize()
	{
		parent::initialize();
	}

	public function loginRun($username,$password,$forget=0){
		$user = $this->where(array('user_login'=>$username))->find();
		if(!empty($user) && $user['user_type']==1){
			if(self::checkPassword($password,$user['user_login'])){

				// $role_user_model = M("RoleUser");
				// $role_user_join = 'bzpm_role as b on a.role_id = b.id';
				// $groups = $role_user_model->alias("a")->join($role_user_join)->where(array("user_id"=>$result["id"],"status"=>1))->getField("role_id",true);

				// if( $result["id"] !=1 && ( empty($groups) || empty($result['user_status']) ) ){
				// 	$this->error(L('USE_DISABLED'));
				// }

				//登入成功页面跳转
				$result['last_login_ip'] = request()->ip();
				$result['last_login_time'] = time();
				if($forget){
					setcookie("admin_username",$user["user_login"],time()+30*24*3600,"/");
				}
				session('aid',$user['id']);
				session('admin_username',$user['user_login']);
				return $this->where(['id'=>$user['id']])->update($result);
				// redirect(url("/admin/index/index"));
			}
		}else{
			return false;
		}
	}

	private static function checkPassword($password,$password_in_db)
	{
		if(strpos($password_in_db, "###")===0){
	        $authcode = config("AUTHCODE");
			$result = "###".md5(md5($authcode.$password));
			return $result;
	    }else{
	        $decor = md5(config('DB_PREFIX'));
		    $mi = md5($password);
		    return substr($decor,0,12).$mi.substr($decor,-4,4);
	    }
	}
}
