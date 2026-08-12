<?php
namespace app\admin\controller;
use think\Db;
use app\admin\controller\Auth;
use app\admin\model\TermRelationships;

class Index extends Auth {
	public function index(){
		//未登录
		if (!session('aid')){
			$this->redirect('Login/login');
		}
		//系统信息
		$info = array(
			'PCTYPE'=>PHP_OS,
			'RUNTYPE'=>$_SERVER["SERVER_SOFTWARE"],
			'ONLOAD'=>ini_get('upload_max_filesize'),
			'ThinkPHPTYE'=>THINK_VERSION,
		);
        $this->assign('info',$info);

		//热门文章排行
		$term_model = new TermRelationships();
		$request = request();
    	$list = $term_model->getArticles($request);
    	foreach($list['posts'] as $k => $v){
    	    if($k % 2 == 1){
    	        $new_list[] = $v;
    	    }
    	}
		$this->assign('news_list',$new_list);
		//总文章数
		$news_count = Db::name('posts')->count();
		$this->assign('news_count',$news_count);
        //总会员数
        $members_count = Db::name('users')->count();
        $this->assign('members_count',$members_count);

        //总评论数
        $coms_count=Db::name('comments')->count();
        $this->assign('coms_count',$coms_count);

    		//今日发表文章数
        $today = strtotime(date('Y-m-d 00:00:00'));//今天开始日期
        $todata['post_modified'] = array('egt',$today);
    		$tonews_count = Db::name('posts')->where($todata)->count();
    		$this->assign('tonews_count',$tonews_count);

    		//昨日文章数
        $ztday=strtotime(date('Y-m-d 00:00:00'))-60*60*24;//昨天开始日期
        $ztdata['post_modified'] = array('between',array($ztday,$today));
    		$ztnews_count = Db::name('posts')->where($ztdata)->count();
    		$this->assign('ztnews_count',$ztnews_count);
    		
    		//今日提升比
        $difday=($ztnews_count>0)?($tonews_count-$ztnews_count)/$ztnews_count*100:0;
    		$this->assign('difday',$difday);

    		//今日增加会员
        $tomembers_count=Db::name('users')->where(array('last_login_time'=>array('egt',$today)))->count();
        $this->assign('tomembers_count',$tomembers_count);

        // 昨日会员数
        $ztmembers_count=Db::name('users')->where(array('last_login_time'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztmembers_count',$ztmembers_count);
        $difday_m=($ztmembers_count>0)?($tomembers_count-$ztmembers_count)/$ztmembers_count*100:0;
        $this->assign('difday_m',$difday_m);

        // 今日评论
        $tocoms_count=Db::name('comments')->where(array('createtime'=>array('egt',$today)))->count();
        $this->assign('tocoms_count',$tocoms_count);
        $ztcoms_count=Db::name('comments')->where(array('createtime'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztcoms_count',$ztcoms_count);
        $difday_c=($ztcoms_count>0)?($tocoms_count-$ztcoms_count)/$ztcoms_count*100:0;
        $this->assign('difday_c',$difday_c);
		//安全检测
        /*
    		$this->system_safe = true;
        $this->danger_mode_debug = APP_DEBUG;
        if ($this->danger_mode_debug) {
            $this->system_safe = false;
        }
        $this->weak_setting_db_password = false;
        $weak_pwd_reg = array(
            '/^[0-9]{0,6}$/',
            '/^[a-z]{0,6}$/',
            '/^[A-Z]{0,6}$/'
        );
        foreach ($weak_pwd_reg as $reg) {
            if (preg_match($reg, config('DB_PWD'))) {
                $this->weak_setting_db_password = true;
                break;
            }
        }
        if ($this->weak_setting_db_password) {
            $this->system_safe = false;
        }
        $this->weak_setting_admin_password = session('admin_weak_pwd');
        if ($this->weak_setting_admin_password) {
            $this->system_safe = false;
        }
        $this->weak_setting_admin_last_change_password = (session('admin_last_change_pwd_time') < time() - 3600 * 24 * 30);
        if ($this->weak_setting_admin_last_change_password) {
            $this->system_safe = false;
        }
    		$this->assign('system_pageshow',C('SHOW_PAGE_TRACE'));
    		$debug=APP_DEBUG;
    		$this->assign('debug',$debug);
    		$log_size = 0;
            $log_file_cnt = 0;
            foreach (list_file(LOG_PATH) as $f) {
                if ($f ['isDir']) {
                    foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
                        if ($ff ['isFile']) {
                            $log_size += $ff ['size'];
                            $log_file_cnt++;
                        }
                    }
                }
            }
    	$this->assign('log_size',$log_size);
    	$this->assign('log_file_cnt',$log_file_cnt);
		//版本检查
 		$version=F('ver_last');
		if(empty($version)){
			$version = checkVersion();
			F('ver_last',$version);
		}
		$ver_curr=substr(C('YFCMF_VERSION'),1);
		$ver_last=substr($version,1);
		if(version_compare($ver_curr,$ver_last)===-1){
			$ver_str='最新版本V'.$ver_last;
		}else{
			$ver_str='已经是最新版本';
			$ver_last='';
		}
		$this->assign('ver_str',$ver_str);
		$this->assign('ver_last',$ver_last);*/
		//渲染模板
		return view('Index/index');
	}
}