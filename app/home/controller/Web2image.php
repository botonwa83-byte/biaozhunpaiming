<?php
namespace app\home\controller;
use think\Db;

class Web2image{
	public function index(){
        return view('Web2image/index');
    }

    public function runadd(){
    	if($data = request()->post()){
            $file = request()->file('img');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
                $img = $info->getSaveName();
            }
    		$content = htmlspecialchars_decode($data['post_content']);
    		$sl_data = [
                'content' => $content,
                'title' => $data['title'],
                'message_one' => $data['message_one'],
                'message_two' => $data['message_two'],
                'message_three' => $data['message_three'],
    			'img' => '/data/upload/'.$img,
    			'addtime' => time()
    		];
    		if($id = Db::name('web2image')->insertGetId($sl_data)){
    			return ['status'=>1,'info'=>'添加成功','url'=>url('showHtml',['id'=>$id])];
    		}
    	}
    }

    public function showHtml(){
    	$id = request()->param('id');
    	$data = Db::name('web2image')->where(['id'=>$id])->find();
    	return view('Web2image/showhtml',['data'=>$data]);
    }

    public function edit(){
    	$id = request()->param('id');
    	$data = Db::name('web2image')->where(['id'=>$id])->find();
    	return view('Web2image/edit',['data'=>$data]);
    }

    public function runedit(){
    	if($data = request()->post()){
    		$content = htmlspecialchars_decode($data['post_content']);
            if($file = request()->file('img')){
                $info = $file->move(ROOT_PATH . 'public' . DS .'data'. DS . 'upload');
                $img = '/data/upload/'.$info->getSaveName();
            }else{
                $img = $data['nochg_img'];
            }
    		$sl_data = [
                'id' => $data['id'],
                'content' => $content,
                'title' => $data['title'],
                'message_one' => $data['message_one'],
                'message_two' => $data['message_two'],
                'message_three' => $data['message_three'],
                'img' => $img,
            ];
    		Db::name('web2image')->update($sl_data);
    		return ['status'=>1,'info'=>'修改成功','url'=>url('showHtml',['id'=>$data['id']])];
    	}
    }

}