<?php

namespace app\home\controller;

use think\Controller;
use think\Db;
use think\Loader;
use think\Request;
use app\home\model\Nav;
/**
 * 系统基础控制器：不需登录
 */
class Base extends Controller
{
	protected $users = [];
    protected $encrypt = null;
    protected $access_token = '';
    protected $jsapi_ticket = '';
    protected $type = '';
    protected $code = '';
    protected $is_wechat = false;
	/**
     * 初始化方法
     * @author tangtanglove
     */
    protected function _initialize()
    {

    	#用户登陆id
    	$this->users = session('user');
        $navs = cache('navs');
        if(empty($navs)){
            $nav_model = new Nav();
            $old_navs = $nav_model->getNav(1);
            foreach($old_navs as $k => $v){
                $navs[$v->id] = $v;
            }
            cache('navs',$navs,1800);
        }
        $this->assign('navs',$navs);

        //是否微信端访问
        if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')!==false){
            $this->is_wechat = true;
            //$this->typeFrom();
        }
        if(!request()->isMobile()){
           $slide_model =  new \app\home\model\SlideCat();
           $this->assign('nav_id',0);
           $this->assign('home_slides',$slide_model->getHomeSlide('portal_index'));
        }
        $this->encrypt = new \Mcrypt\Mcrypt();
        $this->assign('user',$this->users);
        $this->assign('is_wechat',$this->is_wechat);
    }

    protected function typeFrom(){
        $code = request()->param('code');
        $type = request()->param('type')?request()->param('type'):'';
        if($code && !$this->users && !$type){
            $this->htmlAccesstoken($code);
        }else{
            $this->access_token = $this->get_access_token(config('WX_APP_ID'),config('WX_APP_SECRET'));
            $this->jsapi_ticket = $this->get_jsapi_ticket($this->access_token);
            $this->type = 'weixin';
        }
    }
    //微信jsapi接口
    protected function wechatJsapi($request=null){
        $url = $request->url(true);
        $sl_data = [];
        $sl_data['time'] = time();
        $sl_data['url'] = $url;
        $sl_data['nonceStr'] = config('nonceStr');
        $sl_data['signature'] = sha1('jsapi_ticket='.$this->jsapi_ticket.'&noncestr='.$sl_data['nonceStr'].'&timestamp='.$sl_data['time'].'&url='.$url);
        return $sl_data;
    }

    /**
     * 进入微信登录模式
     */
    protected function wechatLogin(){
        $refer_url = request()->server('HTTP_REFERER');
        $current_url = request()->url(true);
        $refer = !empty($refer_url)?$refer_url:$current_url;
        $redirect_uri = urlencode($refer);
        $appid = config('WX_APP_ID');
        // $appid = 'wxbcea9dff94dbdbca';
        $scope = 'snsapi_userinfo';
        $state = '';
        $wechat_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
        return $this->redirect($wechat_url);
    }
    /**
     * 获取微信网页access_token
     */
    protected function htmlAccesstoken($code){
        $wechat_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.config('WX_APP_ID').'&secret='.config('WX_APP_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $html_accesstoken = json_decode($this->curlGet($wechat_url),true);
        return $this->getWechatUserinfo($html_accesstoken);
    }

    /**
     * 获取微信用户信息
     */
    protected function getWechatUserinfo($html_accesstoken){
        $wechat_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$html_accesstoken['access_token'].'&openid='.$html_accesstoken['openid'].'&lang=zh_CN';
        $current_userinfo = json_decode($this->curlGet($wechat_url),true);
        $where['openid'] = $html_accesstoken['openid'];
        $userinfo = Db::name('users')->where($where)->find();
        $ip = request()->ip();
        $time = time();
        if(!empty($userinfo)){
            Db::name('users')->where($where)->update([
                'avatar' => $current_userinfo['headimgurl'],
                'user_nicename' => $current_userinfo['nickname'],
                'last_login_ip' => $ip,
                'last_login_time' => $time
            ]);
            session('uid',$userinfo['id']);
            $userinfo['uid'] = $userinfo['id'];
            session('user',$userinfo);
            $this->users = $userinfo;
        }else{
            $sl_data = [
                'user_nicename' => $current_userinfo['nickname'],
                'avatar' => $current_userinfo['headimgurl'],
                'last_login_ip' => $ip,
                'last_login_time' => $time,
                'create_time' => $time,
                'openid' => $current_userinfo['openid'],
                'user_status' => 1,
                'user_type' => 2,
            ];
            $result = Db::name('users')->insertGetId($sl_data);
            if($result){
                $session['id']=$result;
                $session['user_nicename']=$current_userinfo['nickname'];
                $session['avatar']=$current_userinfo['headimgurl'];
                session('uid',$result);
                $session['uid'] = $result;
                session('user',$session);
                $this->users = $session;
            }
        }
    }

    /**
     * 获取access_token
     */
    protected function get_access_token($appid, $secret) {
        if (empty ( $appid ) || empty ( $secret )) {
            return ;
        }
        $access_token = cache('access_token');
        if($access_token = cache('access_token')){
            return $access_token['access_token'];
        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
            $tempArr = json_decode ($this->curlGet( $url ), true );
            if (@array_key_exists ( 'access_token', $tempArr )) {
                cache('access_token', $tempArr, 3600);
                return $tempArr ['access_token'];
            } else {
                return ;
            }
        }
    }
    /**
     * 获取jsapi权限
     */
    protected function get_jsapi_ticket($access_token){
        if (empty ($access_token)) {
            return ;
        }
        // $js_tikict = cache('ticket');
        if($js_tikict = cache('ticket')){
            return $js_tikict['ticket'];
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
            $tempArr = json_decode ( $this->curlGet( $url ), true );
            if (@array_key_exists ( 'ticket', $tempArr )) {
                cache('ticket',$tempArr,3600);
                return $tempArr ['ticket'];
            } else {
                return ;
            }
        }
    }
    /**
     * 获取get请求方式的数据
     */
    protected function curlGet($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURL_SSLVERSION_SSL,2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    /**
     * 获取post请求方式的数据
     */
    protected function curlPost($url,$post_data){
        // $post_encode_data = json_encode($post_data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
