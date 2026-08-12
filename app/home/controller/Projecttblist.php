<?php
namespace app\home\controller;
use app\home\controller\Base;
use app\home\model\TermRelationships AS Article;
use app\home\model\SlideCat;
use think\Request;
class Projecttblist extends Base{

    public function tbinfo(){
        return view('Projecttb/index');
    }

    public function insertTbs(){
        $request = request();
        // $project_yyimg = request()->file('project_yyimg');
        // if($project_yyimg){
        //     $project_yyimg_path = '/data/upload/'.$project_yyimg->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload')->getSaveName();
        // }
        // $project_img = request()->file('project_img');
        // if($project_img){
        //     $project_img_path = '/data/upload/'.$project_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload')->getSaveName();
        // }
        // if($tb_img){
        //     $tb_img_path = '/data/upload/'.$tb_img->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload')->getSaveName();
        // }
        $_img_path = [];
        foreach (request()->file() as $key=>$file){
            $file_cls = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload',true,false);
            if($file_cls){
                $_img_path[$key] = '/data/upload/'.$file_cls->getSaveName();    
            }
        }
        $tbinfos = [
            'group_name' => $request->param('group_id')?:'这个就是集团名称1',
            'project_gcname' => $request->param('project_gcname')?:'这个就是项目名称这个就是项目名称11',
            'project_yyimg' => isset($_img_path['project_yyimg'])?$_img_path['project_yyimg']:'',
            'project_img' => isset($_img_path['project_img'])?$_img_path['project_img']:'',
            'tb_img' => isset($_img_path['tb_img'])?$_img_path['tb_img']:'',
            'tb_name' => $request->param('tb_name')?:'这个就是填报人',
            'tb_bgphone' => $request->param('tb_bgphone')?:0,
            'tb_czphone' => $request->param('tb_czphone')?:0,
            'tb_mobile' => $request->param('tb_mobile')?:0,
            'email' => $request->param('email')?:0,
            'address' => $request->param('address')?:0,
            'jb_project_name' => $request->param('jb_project_name')?:0,
            'jb_project_address' => $request->param('jb_project_address')?:0,
            'jb_project_type' => $request->param('jb_project_type')?:0,
            'jb_project_mj' => $request->param('jb_project_mj')?:0,
            'jb_project_zmj' => $request->param('jb_project_zmj')?:0,
            'jb_project_jgtype' => $request->param('jb_project_jgtype')?:0,
            'jb_project_rjl' => $request->param('jb_project_rjl')?:0,
            'jb_project_ldl' => $request->param('jb_project_ldl')?:0,
            'jb_project_ghzz' => $request->param('jb_project_ghzz')?:0,
            'jb_project_tcsl' => $request->param('jb_project_tcsl')?:0,
            'jb_project_kg' => $request->param('jb_project_kg'),
            'jb_project_jg' => $request->param('jb_project_jg'),
            'zj_project_zjmj' => $request->param('zj_project_zjmj')?:0,
            'zj_project_level' => $request->param('zj_project_level')?:0,
            'zj_project_ypmj' => $request->param('zj_project_ypmj')?:0,
            'zj_project_nhlevel' => $request->param('zj_project_nhlevel')?:0,
            'zj_project_nhmj' => $request->param('zj_project_nhmj')?:0,
            'zj_project_dtjzlevel' => $request->param('zj_project_dtjzlevel')?:0,
            'zj_project_dtjz' => $request->param('zj_project_dtjz')?:0,
            'zj_project_zpsmj' => $request->param('zj_project_zpsmj')?:0,
            'zj_project_zxcpmj' => $request->param('zj_project_zxcpmj')?:0,
            'zj_project_jkjzmj' => $request->param('zj_project_jkjzmj')?:0,
            'zj_project_lstype' => $request->param('zj_project_lstype')?:0,
            'zj_project_lsjzmj' => $request->param('zj_project_lsjzmj')?:0,
            'jg_project_jgmj' => $request->param('jg_project_jgmj')?:0,
            'jg_project_lsjztype' => $request->param('jg_project_lsjztype')?:0,
            'jg_project_ypmj' => $request->param('jg_project_ypmj')?:0,
            'jg_project_nhlevel' => $request->param('jg_project_nhlevel')?:0,
            'jg_project_nhmj' => $request->param('jg_project_nhmj')?:0,
            'jg_project_dtjzlevel' => $request->param('jg_project_dtjzlevel')?:0,
            'jg_project_dtjz' => $request->param('jg_project_dtjz')?:0,
            'jg_project_zpsmj' => $request->param('jg_project_zpsmj')?:0,
            'jg_project_zxcpmj' => $request->param('jg_project_zxcpmj')?:0,
            'jg_project_jkjzmj' => $request->param('jg_project_jkjzmj')?:0,
            'jg_project_lstype' => $request->param('jg_project_lstype')?:0,
            'jg_project_lsjzmj' => $request->param('jg_project_lsjzmj')?:0,
            'project_dkfmj' => $request->param('project_dkfmj')?:0,
            'hj_qy' => $request->param('hj_qy')?:0,
            'hj_cy' => $request->param('hj_cy')?:0,
            'hj_trq' => $request->param('hj_trq')?:0,
            'hj_dl' => $request->param('hj_dl')?:0,
            'hj_rl' => $request->param('hj_rl')?:0,
            'hj_wsqt' => $request->param('hj_wsqt')?:0,
            'hj_dyhhw' => $request->param('hj_dyhhw')?:0,
            'hj_eyhl' => $request->param('hj_eyhl')?:0,
            'hj_xflz' => $request->param('hj_xflz')?:0,
            'hj_gtfqw' => $request->param('hj_gtfqw')?:0,
            'hj_yhfqw' => $request->param('hj_yhfqw')?:0,
            'hj_whfqw' => $request->param('hj_whfqw')?:0,
            'hj_jzlj' => $request->param('hj_jzlj')?:0,
            'hj_fsws' => $request->param('hj_fsws')?:0, 
            'hj_yzl' => $request->param('hj_yzl')?:0,
            'hj_ysl' => $request->param('hj_ysl')?:0,
            'hj_slhlt' => $request->param('hj_slhlt')?:0,
            'hj_gc' => $request->param('hj_gc')?:0,
            'hj_lc' => $request->param('hj_lc')?:0,
            'hj_bl' => $request->param('hj_bl')?:0,
            'hj_tc' => $request->param('hj_tc')?:0,
            'hj_yffy' => $request->param('hj_yffy')?:0,
            'hj_hbzj' => $request->param('hj_hbzj')?:0,
            'hj_shgy' => $request->param('hj_shgy')?:0,
            'hj_zscq' => $request->param('hj_zscq')?:0,
            'hj_qywg' => $request->param('hj_qywg')?:0,
            'hj_ygwg' => $request->param('hj_ygwg')?:0,
            'hj_ssaj' => $request->param('hj_ssaj')?:0,
            'hj_ssje' => $request->param('hj_ssje')?:0,
            'hj_aqsg' => $request->param('hj_aqsg')?:0,
            'hj_aqsgsw' => $request->param('hj_aqsgsw')?:0,
            'hj_gyszdsj' => $request->param('hj_gyszdsj')?:0,
            'hj_gyszlwt' => $request->param('hj_gyszlwt')?:0,
            'create_time' => date('Y-m-d H:i:s',$request->time())
        ];
        $insert_info = db('project_tblist')->insertGetId($tbinfos);
        if($insert_info){
            $this->result(null,200,'成功','json');
        }
        $this->result(null,400,'失败','json');
    }
}
