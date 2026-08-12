<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\SlideCat;
use think\Db;
class Report extends Base{

	public function detail(){
		$request = request();
		$id = $request->param('id');
		$report = Db::name('report')->where(['id'=>$id,'status'=>1])->find();
		$report['report_content'] = htmlspecialchars_decode($report['report_content']);
		$report_colls = Db::name('report')->where(['id'=>['IN',$report['report_ids']]])->select();
        $report['report_author'] = explode("\r\n",$report['report_author']);
        $assign = [
            'report' => $report,
            'report_colls' => $report_colls
        ];
        if(!$request->isMobile()){
            $slide_model = new SlideCat();
            $assign['home_slides'] = $slide_model->getHomeSlide('portal_index');
            $assign['navs'] = $this->navs;
        }
		return view('Report/detail',$assign);
	}

	public function reportDownload()
    {
    	$request = request();
    	$filename = $request->param('backup');
        $backup_name = isset($filename)&&trim($filename)?trim($filename):'';
        $sql_file =  FILE_PATH. 'data/backup/' . $backup_name;
        if (file_exists($sql_file))
        {
            $sl_data = array(
                'uid' => session('uid'),
                'filename'=> $backup_name,
                'loadtime' => time(),
                'loadstatus' => 1,
                'ip' => $request->ip()
            );
            Db::name('user_download')->insert($sl_data);
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $backup_name . '"');
            header("Content-Length: " . filesize($sql_file) . "; ");
            readfile($sql_file);
        } else{
            header('HTTP/1.1 404 Not Found');
            header('Status:404 Not Found');
        }
    }

    public function reportForm(){
        return view('Report/reportform');
    }

}