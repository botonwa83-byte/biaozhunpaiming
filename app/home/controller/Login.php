<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Users;

class Login extends Base
{
    protected $user_model = '';

	public function _initialize()
    {
        parent::_initialize();
        $this->user_model = new Users();
    }

    public function login(){
	    if(session('uid')){
	        $this->redirect('/home/user/account');
        }
    	$request = request();
    	if($param = $request->post()){
    		$user = $this->user_model->checkPassword($request);
    		if($request->isAjax()){
                if($user){
                    $refer_url = session('login_refer_url');
                    session('login_refer_url',null);
                    $refer_url = !empty($refer_url)?$refer_url:'/';
                    return ['status'=>1,'info'=>'','url'=>$refer_url];
                }else{
                    return ['status'=>0,'info'=>'账号密码错误！','url'=>''];
                }
    		}else{
    			$this->redirect('/');
    		}
    	}else{
            $refer_url = $request->server('HTTP_REFERER');
            session('login_refer_url',$refer_url);
            return view('User/login');
        }
    }

    public function getVerifyCode(){
        $request = request();
        $username = $request->param('username');
        if(empty($_SESSION['register'][$username])){
            return ['status'=>0,'info'=>'参数丢失！','url'=>''];
        }
        $last_time = isset($_SESSION['register'][$username]['timestutas'])?$_SESSION['register'][$username]['timestutas']:0;
        if($last_time){
            if(time() - $last_time < 90){
                return ['status'=>0,'info'=>'操作过于频繁！','url'=>''];
            }
        }
        $msg = '';
        for($i=0;$i<6;$i++){
            $msg .= rand(0,9);
        }
        $_SESSION['register'][$username]['verify'][] = $msg;
        $_SESSION['register'][$username]['timestutas'] = time();

        // $line_num = substr($username,-4);
        $sms_model = new \Util\AliyunSms("YOUR_ACCESS_KEY_ID","YOUR_ACCESS_KEY_SECRET");
        // $msage = "【标准排名】: 您的手机绑定验证码是：".$msg."，手机尾号（".$line_num."），嘘，可别让他人看到哦。";
        // $sms_model->sendSMS($username,$msage);
        $response = $sms_model->sendSms(
            "标准排名网", // 短信签名
            "SMS_105390016", // 短信模板编号
            "$username", // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>$msg,
                "product"=>"dsd"
            ),
            "123"
        );
        return true;
    }

    public function register(){
        $request = request();
        if($request->post()){
            $username = $request->param('username');
            $password = $request->param('password');
            $result = $this->user_model->checkMobile($username);
            if($result === false){
                return ['status'=>0,'info'=>'请填写正确的手机号！','url'=>''];
            }elseif(!empty($result)){
                return ['status'=>0,'info'=>'账号已经被注册！','url'=>''];
            }else{
                $_SESSION['register'][$username]['mobile'] = $username;
                $_SESSION['register'][$username]['password'] = $password;
                if($request->isMobile()){
                    return ['status'=>1,'info'=>'','url'=>url('verifyCode',['username'=>$username])];
                }else{
                    return ['status'=>1,'info'=>'','url'=>''];
                }
            }
        }else{
           return view('User/register'); 
        }
    }

    public function verifyCode(){
        $request = request();
        if($request->post()){
            return $this->user_model->register($request);
        }else{
            return view('User/verifycode',['username'=>$request->param('username')]);
        }
    }

    /**
     * 填写密码
     * @var void
     * @access public
     */
    public function forgetPassword(){
        $request = request();
        if($request->post()){
            $username = $request->param('username');
            if(!empty($username)){
                $result = $this->user_model->checkMobile($username);
                if($result === false){
                    return ['status'=>0,'info'=>'请填写正确的手机号！','url'=>''];
                }elseif(empty($result)){
                    return ['status'=>0,'info'=>'账号不存在！','url'=>''];
                }else{
                    $_SESSION['forget'][$username]['mobile'] = $username;
                    return ['status'=>1,'info'=>'','url'=>url('forgetVerifyCode',['username'=>$username])];
                }
            }else{
                return ['status'=>0,'info'=>'手机号不能为空！','url'=>''];
            }
        }else{
            return view('User/forgetPassword');
        }
    }

    /**
     * 发送验证码页面
     * @var void
     * @access public
     */
    public function forgetVerifyCode(){
        if($post = request()->post()){
            $username = $post['username'];
            $verify = $post['verify'];
            $verifys = $_SESSION['forget'][$username]['verify'];
            session('login_refer_url','/');
            if(in_array($verify,$verifys)){
                return ['status'=>1,'info'=>'','url'=>url('changePassword',['username'=>$username,'verify'=>$verify])];
            }else{
                return ['status'=>0,'info'=>'验证码错误','url'=>''];
            }
        }
        return view('User/forgetpwdverify',['username'=>request()->param('username')]);
    }

    /**
     * 重置密码
     * @var void
     * @access public
     */
    public function changePassword(){
        $request = request();
        if($request->post()){
            $param = $request->param();
            if(!empty($param['username']) && !empty($param['verify'])){
                $verifys = $_SESSION['forget'][$param['username']]['verify'];
                if(in_array($param['verify'],$verifys)){
                    $result = $this->user_model->changePassword($param['username'],$param['password']);
                    if($result){
                        unset($_SESSION['forget'][$param['username']]);
                        session('login_refer_url','/');
                        return ['status'=>1,'info'=>'密码重置成功！','url'=>url('home/login/login')];
                    }elseif($result === false){
                        return ['status'=>0,'info'=>'密码不能与原密码相同！','url'=>''];
                    }else{
                        return ['status'=>0,'info'=>'系统出错！','url'=>''];
                    }
                }else{
                    return ['status'=>0,'info'=>'系统出错！','url'=>''];
                }
            }else{
               return ['status'=>0,'info'=>'参数丢失！','url'=>'']; 
            }
        }else{
            return view('User/changepassword');
        }
    }

    /**
     * 发送重置短信
     * @var void
     * @access public
     */
    public function getForgetpwdVerify(){
        $request = request();
        $username = $request->param('username');
        if(empty($_SESSION['forget'][$username])){
            return ['status'=>0,'info'=>'参数丢失！','url'=>''];
        }
        $last_time = isset($_SESSION['forget'][$username]['timestutas'])?$_SESSION['forget'][$username]['timestutas']:0;
        if($last_time){
            if(time() - $last_time < 90){
                return ['status'=>0,'info'=>'操作过于频繁！','url'=>''];
            }
        }
        $msg = '';
        for($i=0;$i<6;$i++){
            $msg .= rand(0,9);
        }
        $_SESSION['forget'][$username]['verify'][] = $msg;
        $_SESSION['forget'][$username]['timestutas'] = time();

        // $line_num = substr($username,-4);
        $sms_model = new \Util\AliyunSms("YOUR_ACCESS_KEY_ID","YOUR_ACCESS_KEY_SECRET");
        // $msage = "【标准排名】: 您的手机绑定验证码是：".$msg."，手机尾号（".$line_num."），嘘，可别让他人看到哦。";
        // $sms_model->sendSMS($username,$msage);
        $response = $sms_model->sendSms(
            "标准排名网", // 短信签名
            "SMS_105390016", // 短信模板编号
            "$username", // 短信接收者
            ["code"=>$msg]
        );
        session('login_refer_url','/');
        return true;

    }

    public function wechatLoginUrl(){
        $this->wechatLogin();
    }
    
}