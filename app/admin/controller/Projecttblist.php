<?php
namespace app\admin\controller;
use app\admin\controller\Auth;
class Projecttblist extends Auth{

     public function lists(){
        $data = db('project_tblist')->paginate(10);
        return view('Projecttblist/lists',['data'=>$data,'page'=>$data->render(),'page_min'=>$data->render()]);
    }

    public function edit(){
        $id = request()->param('id');
        $tainfo = db('project_tblist')->where('id',$id)->find();
        return view('Projecttblist/edit',['tainfo'=>$tainfo]);
    }

    public function editsub(){
        if(db('project_tblist')->update(request()->param())){
            return ['status'=>1,'info'=>"数据更新成功",'url'=>url('lists')];
        }else{
            return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
        }   
    }

    public function tbexcel(){
        vendor('phpexcel.PHPExcel');
        $data = db('project_tblist')->where('status',1)->field('group_id,create_time,status',true)->select();
        $excel_config = [
            "file_name"   => "项目数据"
        ];
        $excel_apply = new \Util\ PhpExcelApply();
        $excel_head = config('title_dat');
        $excel_apply->setExportConfig($excel_config)->setExportHead($excel_head)->exportExport($data);
    }
}
