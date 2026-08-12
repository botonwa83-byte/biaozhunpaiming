<?php
namespace app\home\controller;

use app\home\controller\Base;
use app\home\model\Users;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
        if(!request()->isMobile() && empty($this->users)){
            // $this->assign('navs',$this->navs);
            $this->redirect(url('/'));
        }
        if(empty($this->users)){
            $this->redirect(url('home/login/login'));
        }
    }

    public function logout(){
        session('uid',null);
        session('user',null);
        $this->redirect('/');
    }

    public function person(){
        $assign = [];
        return view('User/person');
    }

    public function personBuild(){
        $page = request()->param('page');
        $page = $page?$page:1;
        $vote_model = db('vote');
        $votes = $vote_model
        ->where(['vote_author'=>$this->users['uid']])
        ->page($page,config('MORE_PAGESIZE'))
        ->order('createtime')
        ->select();
        $assign = [
            'votes' => $votes,
        ];
        return view('User/personbuild',$assign);
    }

    public function myAttendVote(){
        $field = 'b.id,title,keywords,hits,comment_count,smeta,b.createtime';
        $myattend = db('vote_record')->alias('a')
        ->field($field)
        ->join(' bzpm_vote b','a.vote_id = b.id','LEFT')
        ->where(['vote_user'=>$this->users['uid'],'vote_status'=>1])
        ->group('vote_id')
        ->select();
        foreach($myattend as $k => $v){
            $smeta = json_decode($v['smeta'],true);
            $myattend[$k]['smeta'] = $smeta;
        }
        return view('User/myattend',['myattend'=>$myattend]);
    }

    public function myArticleFavlists(){
        $uid = session('uid');
        $page = request()->param('page');
        $articles = db('user_favorites')->alias('a')
        ->field('tid,b.object_id,post_title,post_mime_type,comment_count,post_like,post_modified,smeta,covoting')
        ->join(' bzpm_term_relationships b','a.object_id = b.object_id','LEFT')
        ->join(' bzpm_posts c','a.object_id = c.id','LEFT')
        ->where(['uid'=>$uid,'favo_status'=>1,'type'=>1])
        ->page($page,config('MORE_PAGESIZE'))
        ->group('a.object_id')
        ->select();
        if($articles){
            foreach($articles as $k => $v){
                $smeta = json_decode($v['smeta'],true);
                $articles[$k]['smeta'] = $smeta['thumb'];
                $articles[$k]['tid'] = $this->encrypt->encrypt($v['tid']);
            }
        }
        return view('User/articlefavlists',['articles'=>$articles]);
    }

    public function myVoteFavlists(){
        $uid = session('uid');
        $page = request()->param('page');
        $votes = db('user_favorites')->alias('a')
        ->field('b.id,title,keywords,hits,comment_count,smeta,b.createtime,vote_status')
        ->join(' bzpm_vote b','a.vote_id = b.id','LEFT')
        ->where(['uid'=>$uid,'favo_status'=>1,'type'=>2])
        ->page($page,config('MORE_PAGESIZE'))
        ->select();
        if($votes){
            foreach($votes as $k => $v){
                $smeta = json_decode($v['smeta'],true);
                $votes[$k]['smeta'] = $smeta['thumb'];
            }
        }
        return view('User/votefavlists',['votes'=>$votes]);   
    }

    public function account(){
        return view('User/account',['user'=>$this->users]);
    }

    public function mydownload(){
        $uid = cookie('uid');
        $reports = [];
        if($downloads = db('user_download')->field('rid')
            ->where(['uid'=>$uid,'loadstatus'=>1])
            ->select()){
            foreach($downloads as $k => $v){
                $rids[] = $v['rid'];
            }
            $reports = db('report')->field('id,report_hits,report_name,createtime,smeta,report_file')->where(['id'=>['IN',implode(',',$rids)]])->select();
        }
        return view('User/mydownload',['reports'=>$reports]);
    }

    public function changeUsername(){
        $request = request();
        $uid = session('uid');
        if($username = $request->post('username')){
            $user_model = new Users();
            if($user_model->checkBandWord($username)>0){
                return ['status'=>0,'info'=>'昵称中含有敏感词汇！'];
            }
            if(db('users')->where(['id'=>$uid])->update(['user_nicename'=>$username])){
                session('user.user_nicename',$username);
                return ['status'=>1,'info'=>'','url'=>url('account')];
            }else{
                return ['status'=>0,'info'=>'修改失败！','url'=>''];
            }
        }
        $user = db('users')->where(['id'=>$uid])->find();
        return view('User/changeusername',['user'=>$user]);
    }

    public function aboutUs(){
        return view('User/aboutus');
    }

    public function bindMobile(){
        $refer_url = request()->server('HTTP_REFERER');
        session('refer_url_bind',$refer_url);
        return view('User/bindmobile');
    }

    /**
     * 发送绑定手机号验证码
     * @var void
     * @access public
     */
    public function getBindVerifyCode(){
        $request = request();
        $username = $request->param('username');
        if(empty($username)){
            return ['status'=>0,'info'=>'参数丢失！','url'=>''];
        }
        $last_time = isset($_SESSION['bind'][$username]['timestutas'])?$_SESSION['bind'][$username]['timestutas']:0;
        if($last_time){
            if(time() - $last_time < 90){
                return ['status'=>0,'info'=>'操作过于频繁！','url'=>''];
            }
        }
        $msg = '';
        for($i=0;$i<6;$i++){
            $msg .= rand(0,9);
        }
        $_SESSION['bind'][$username]['verify'][] = $msg;
        $_SESSION['bind'][$username]['timestutas'] = time();

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
    }

    public function bindMobileData(){
        $param = request()->param();
        if(!empty($param['username']) && !empty($param['verify']) && isset($_SESSION['bind'][$param['username']])){
            $verifys = $_SESSION['bind'][$param['username']]['verify'];
            if(in_array($param['verify'],$verifys)){
                if(db('users')->where(['id'=>session('uid')])->update(['mobile'=>$param['username']])){
                    unset($_SESSION['bind'][$param['username']]);
                    $user = session('user');
                    $user['user_login_encrypt'] = str_replace(substr($param['username'],3,6),'**',$param['username']);
                    $user['mobile'] = $param['username'];
                    session('user',$user);
                    $refer_url = session('refer_url_bind');
                    session('refer_url_bind',null);
                    return ['status'=>1,'info'=>'绑定成功！','url'=>$refer_url];
                }else{
                    return ['status'=>0,'info'=>'手机号不能重复绑定！','url'=>''];
                }
            }else{
                return ['status'=>0,'info'=>'验证码错误！','url'=>''];
            }
        }else{
           return ['status'=>0,'info'=>'请填写手机号和验证码！','url'=>'']; 
        }
    }

    public function loadMorePersonBuild(){
        $request = request();
        $user_model = new Users();
        return $user_model->loadMorePersonBuildData($request);
    }

    public function loadMoreAttendVote(){
        $request = request();
        $user_model = new Users();
        return $user_model->loadMoreAttendVoteData($request);
    }

}