<?php
namespace app\admin\controller;
use app\admin\controller\Auth;
use think\Db;
class Rank extends Auth{

	public function lists(){
		$data = db('rank_list')->paginate(config('PAGESIZE'));
		return view('Rank/lists',['data'=>$data,'page'=>$data->render(),'page_min'=>$data->render()]);
	}

	public function edit(){
		$id = request()->param('id');
		if($data = request()->post()){
			$data['createtime'] = time();
			if(db('rank_list')->where(['id'=>$id])->update($data)){
				return ['status'=>1,'info'=>"数据更新成功",'url'=>url('lists')];
			}else{
				return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
			}
		}else{
			$rank = db('rank_list')->where(['id'=>$id])->find();
		}
		return view('Rank/edit',['rank'=>$rank]);
	}

	public function randEdit(){
		$id = request()->param('id');
		if($data = request()->post()){
			if(db('rank_brands')->update($data)){
				return ['status'=>1,'info'=>"数据更新成功",'url'=>''];
			}else{
				return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
			}
		}else{
			$brand = db('rank_brands')->where(['id'=>$id])->find();
		}
		return view('Rank/randEdit',['brand'=>$brand]);
	}

	public function delBrand(){
		$id = request()->param('id');
		if(db('rank_brands')->where(['id'=>$id])->update(['back'=>0])){
			return ['status'=>1,'info'=>"数据更新成功",'url'=>''];
		}else{
			return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
		}
	}

	public function brandState(){
		$id = request()->param('x');
		$status = db('rank_brands')->field('status')->where(array('id'=>$id))->find();//判断当前状态情况
		if($status['status']==1){
			$statedata = array('status'=>0);
			$auth_group = db('rank_brands')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'未审','url'=>1];
		}else{
			$statedata = array('status'=>1);
			$auth_group = db('rank_brands')->where(array('id'=>$id))->update($statedata);
			return ['status'=>1,'info'=>'已审','url'=>1];
		}
	}

	public function scoreEdit(){
		$id = request()->param('id');
		if($data = request()->post()){
			if(db('rank_score')->update($data)){
				return ['status'=>1,'info'=>"数据更新成功",'url'=>''];
			}else{
				return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
			}
		}else{
			$score = db('rank_score')->where(['id'=>$id])->find();
			$brand = db('rank_brands')->where(['id'=>$score['brand_id']])->find();
		}
		return view('Rank/scoreEdit',['score'=>$score,'brand'=>$brand]);
	}

	public function delScore(){
		$id = request()->param('id');
		if(db('rank_score')->where(['id'=>$id])->update(['status'=>0])){
			return ['status'=>1,'info'=>"数据更新成功",'url'=>''];
		}else{
			return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
		}
	}

	public function runAdd(){
		if($data = request()->post()){
			$data['createtime'] = time();
			if(db('rank_list')->insert($data)){
				return ['status'=>1,'info'=>"数据添加成功",'url'=>url('lists')];
			}else{
				return ['status'=>0,'info'=>"数据添加失败",'url'=>''];
			}
		}else{
			return ['status'=>0,'info'=>"数据添加失败",'url'=>''];
		}
	}

	public function del(){
		$id = request()->param('id');
		if(db('rank_list')->where(['id'=>$id])->delete()){
			return ['status'=>1,'info'=>"数据删除成功",'url'=>url('lists')];
		}else{
			return ['status'=>0,'info'=>"数据删除失败",'url'=>''];
		}
	}

	public function state(){
		$request = request();
		$id = $request->param('x');
        $status = db('rank_list')->where(['id'=>$id])->find();
        $status = $status['status'];
        if($status == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        db('rank_list')->where(['id'=>$id])->update(['status'=>$status]);
		if($status){
			return ['status'=>1,'info'=>'已审','url'=>1];
		}else{
			return ['status'=>1,'info'=>'未审','url'=>1];
		}
	}

	public function showRand(){
		$request = request();
		$id = $request->param('id');
		$data = db('rank_brands')->where(['rank_list_id'=>$id,'back'=>1])->order('listorder desc')->paginate(config('PAGESIZE'));
		return view('Rank/showRand',['data'=>$data,'page'=>$data->render(),'page_min'=>$data->render()]);
	}

	public function showScore(){
		$request = request();
		$id = $request->param('id');
		$data = db('rank_score')->where(['brand_id'=>$id,'status'=>1])->paginate(config('PAGESIZE'));
		$brand = db('rank_brands')->where(['id'=>$id])->find();
		return view('Rank/showScore',['data'=>$data,'page'=>$data->render(),'page_min'=>$data->render(),'brand'=>$brand]);
	}

	public function runScoreAdd(){
		$request = request();
		$sl_data = $request->post();
		if(db('rank_score')->where(['month'=>$sl_data['month'],'brand_id'=>$sl_data['brand_id'],'status'=>1])->find()){
			return ['status'=>0,'info'=>'该月份数据已存在！','url'=>''];
		}
		if(db('rank_score')->insert($sl_data)){
			return ['status'=>1,'info'=>'添加成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'添加失败！','url'=>''];
		}
	}

	public function runRandAdd(){
		$request = request();
		$sl_data = $request->post();
		$sl_data['createtime'] = time();
		if(db('rank_brands')->insert($sl_data)){
			return ['status'=>1,'info'=>'添加成功！','url'=>''];
		}else{
			return ['status'=>0,'info'=>'添加失败！','url'=>''];
		}
	}

	public function listorder(){
		$request = request();
		$param = $request->param();
		foreach($param['listorder'] as $k => $v){
			db('rank_brands')->where(['id'=>$k])->update(['listorder'=>$v]);
		}
		return ['status'=>1,'info'=>'排序成功！','url'=>''];
	}

    public function showDetail(){
        $request = request();
        $id = $request->param('id');
        $data = db('rank_brands_detail')->where(['rank_list_id'=>$id,'back'=>1])->order('listorder desc')->paginate(config('PAGESIZE'));
        return view('Rank/showDetail',['data'=>$data,'page'=>$data->render(),'page_min'=>$data->render()]);
    }

    public function detailEdit(){
        $id = request()->param('id');
        if($data = request()->post()){
            if(db('rank_brands_detail')->update($data)){
                return ['status'=>1,'info'=>"数据更新成功",'url'=>''];
            }else{
                return ['status'=>0,'info'=>"数据更新失败",'url'=>''];
            }
        }else{
            $data = db('rank_brands_detail')->where(['id'=>$id])->find();
        }
        return view('Rank/detailEdit',['data'=>$data]);
    }

    public function rundetailadd(){
        $request = request();
        $sl_data = $request->post();
        $sl_data['createtime'] = time();
        if(db('rank_brands_detail')->insert($sl_data)){
            return ['status'=>1,'info'=>'添加成功！','url'=>''];
        }else{
            return ['status'=>0,'info'=>'添加失败！','url'=>''];
        }
    }

    public function deldetail(){
        $id = request()->param('id');
        if(db('rank_brands_detail')->where(['id'=>$id])->delete()){
            return ['status'=>1,'info'=>"数据删除成功",'url'=>url('lists')];
        }else{
            return ['status'=>0,'info'=>"数据删除失败",'url'=>''];
        }
    }

    public function rankZsscore()
    {
        return view('Rank/rank_zsscore');
    }

    public function aboutMe()
    {
        return view('Rank/about_me');
    }

    public function rankZxdetail()
    {
        return view('Rank/rank_zxdetail');
    }

}
