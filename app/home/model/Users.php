<?php
namespace app\home\model;
use think\Model;
class Users extends Model
{
	protected $table = 'bzpm_users';

	protected function _initialize()
	{
		parent::_initialize();
		
	}

	public function checkPassword($request){

        $where['user_login'] = $request->param('username');
        $where['user_type'] = 2;
		$user = $this->where($where)->find();
		if (!$user){
			return ;
		}else{
			$password="###".md5(md5(config("AUTHCODE").$request->param('password')));
			if($password === $user['user_pass']){
				$data = array(
					'last_login_time' => time(),
					'last_login_ip' => $request->ip(),
				);
				$this->where(array('user_login'=> $user["user_login"]))->update($data);
				$user['user_login_encrypt'] = str_replace(substr($user['user_login'],3,6),'**',$user['user_login']);
                $user['uid'] = $user['id'];
				session('uid',$user['id']);
				session('user',$user);
				return $user;
			}
		}
	}

	public function checkMobile($mobile){
		$reg = '/[^0-9+]*(?P<tel>(\+86[1][3578][0-9]{9})|([1][3578][0-9]{9}))[^0-9+]*/';
		if(!preg_match($reg,$mobile)){
			return false;
		}else{
			return $this->where(['user_login'=>$mobile,'user_type'=>2])->find();
		}
	}

	public function register($request){
		$username = $request->param('username');
        $verify = $request->param('verify');
        $password = $_SESSION['register'][$username]['password'];
        if($this->checkMobile($username)){
        	return ['status'=>0,'info'=>'账号已存在！','url'=>''];
        }
        if(empty($username) || empty($verify) || empty($password)){
        	return ['status'=>0,'info'=>'参数丢失！','url'=>''];
        }
        $ip = $request->ip();
        $time = time();
        $last_time = isset($_SESSION['register'][$username]['timestutas'])?$_SESSION['register'][$username]['timestutas']:0;
        if($time - $last_time > 1800){
            return ['status'=>0,'info'=>'验证码错误！','url'=>''];
        }else{
            $verifys = $_SESSION['register'][$username]['verify'];
            if(in_array($verify,$verifys)){
                $sl_data = [
                    'user_login' => $username,
                    'user_pass' => "###".md5(md5(config("AUTHCODE").$password)),
                    'last_login_ip' => $ip,
                    'last_login_time' => $time,
                    'create_time' => $time,
                    'user_status' => 1,
                    'user_type' => 2,
                    'mobile' => $username,
                    'avatar' => '/static/mobile/images/portrait8.jpg'
                ];
                $result = db('users')->insertGetId($sl_data);
                if($result){
	                $user_login_encrypt = str_replace(substr($username,3,6),'**',$username);
	                $session = [
	                    'uid' => $result,
	                    'user_nicename' => '',
	                    'user_login_encrypt' => $user_login_encrypt,
	                    'mobile' => $username,
	                    'avatar' => '/static/mobile/images/portrait8.jpg'
	                ];
	                unset($_SESSION['register'][$username]);
	                session('uid',$result);
	                session('user',$session);
	                return ['status'=>1,'info'=>'注册成功！','url'=>url('user/personBuild')];	
                }else{
                	return ['status'=>0,'info'=>'系统出错！','url'=>''];
                }
            }else{
                return ['status'=>0,'info'=>'验证码错误！','url'=>''];
            }
        }
	}

	public function changePassword($username,$password,$type=2){
		$password="###".md5(md5(config("AUTHCODE").$password));
		$user = $this->where(['user_login'=>$username])->find();
		if($user['user_pass'] === $password){
			return false;
		}else{
			return $this->where(['user_login'=>$username])->update(['user_pass'=>$password]);
		}
	}

	public function loadMorePersonBuildData($request){
		$page = request()->param('page');
        $page = $page?$page:1;
        $votes = db('vote')
        ->where(['vote_author'=>session('uid')])
        ->page($page,config('MORE_PAGESIZE'))
        ->order('createtime')
        ->select();
        if($votes){
            if($request->isMobile()){
                $html = '';
                foreach($votes as $k => $v){
                    $smeta=json_decode($v['smeta'],true);
                    $url = url('vote/voteshow',array('id'=>$v['id']));
                    $html .= '<div class="card"><a href="'.$url.'"><div class="media-left" href="'.$url.'"><img class="media-object img-responsive" src="'.$smeta['thumb'].'" ></div><div class="media-body"><h6 class="media-heading">'.$v['title'].'</h6><p><small><span class="pull-left" style="padding-top: .2em">'.date("Y-m-d",$v['createtime']).'</span><span class="m-r text-';
                    $html .= $v['vote_status']==1?'green':'red';
                    $html .= '">';
                    $html .= $v['vote_status']==1?'审核':'未审核';
                    $html .= '</span><span>'.$v['comment_count'].' 评论</span></small></p></div></a></div>';
                }
                if($html){
                    return ['code'=>200,'html'=>$html];
                }else{
                    return ['code'=>400,'html'=>''];
                }
            }else{
                foreach($votes as $k => $v){
                    $smeta = json_decode($v['smeta'],true);
                    $votes[$k]['smeta'] = $smeta['thumb'];
                    $votes[$k]['createtime'] = date('Y-m-d',$v['createtime']);
                }
                return ['code'=>200,'data'=>$votes,'type'=>'user_votes'];
            }
        }else{
            return ['code'=>400,'info'=>'没有更多'];
        }
	}

    public function checkBandWord($word){
        $words = db('bandwords')->field('content')->select();
        $new_words = array();
        foreach($words as $v){
            $new_words[] = $v['content'];
        }
        $count = 0;
        preg_replace($new_words,'***',$word,-1,$count);
        return $count;
    }

}
