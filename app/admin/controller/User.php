<?php
namespace app\admin\Controller;
use app\admin\controller\Auth;
use think\Db;

class User extends Auth{

	public function lists(){
		$data = db('users')->where(['user_type'=>2,'openid'=>''])->order(array("id" => "desc"))->paginate(config('PAGESIZE'));
        return view('User/index',['data'=>$data,'page' => $data->render(),'page_min' => $data->render(),]);
	}

	public function oauth(){
		$data = db('users')->where("user_type=2 AND openid != ''")->order(array("id" => "desc"))->paginate(config('PAGESIZE'));
        return view('User/index',['data'=>$data,'page' => $data->render(),'page_min' => $data->render(),]);
	}

	public function index()
    {
        $where = ["user_type" => 1];
        /**搜索条件**/
        $user_login = $this->request->param('user_login');
        $user_email = trim($this->request->param('user_email'));

        if ($user_login) {
            $where['user_login'] = ['like', "%$user_login%"];
        }

        if ($user_email) {
            $where['user_email'] = ['like', "%$user_email%"];;
        }
        $users = Db::name('users')
            ->where($where)
            ->order("id DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $users->render();

        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }

        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $users);
        return view('User/indexs');
    }

    public function add()
    {
        if(session('aid') != 1){
            $this->redirect('/');
        }
        $roles = db('role')->where('status',1)->select();
        $this->assign("roles", $roles);
        return view('User/add');
    }

    public function addPost()
    {
        $post = $this->request->post();
        if(db('users')->where('user_login',$post['username'])->find()){
            return ['status'=>0,'info'=>'用户名已存在','url'=>''];
        }
        $password="###".md5(md5(config("AUTHCODE").$post['password']));
        $data = [
            'user_login' => $post['username'],
            'user_pass'   => $password,
            'user_type'   => 1
        ];
        $user_id = db('users')->insertGetId($data);
        db('role_user')->insert([
            'user_id' => $user_id,
            'role_id' => $post['role_id']
        ]);
        return ['status'=>1,'info'=>'添加成功','url'=>url('index')];
    }

    public function del()
    {
        $id = $this->request->param('id');
        if(session('aid') != 1){
            return ['status'=>0,'info'=>'没有权限','url'=>url('index')];
        }
        if(db('users')->where(['id' => $id])->delete()){
            return ['status'=>1,'info'=>'删除成功','url'=>url('index')];
        }else{
            return ['status'=>0,'info'=>'删除失败','url'=>url('index')];
        }
    }

}
