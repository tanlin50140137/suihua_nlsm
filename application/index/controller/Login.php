<?php
namespace app\index\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Login
{
    //会员登陆
    public function index()
    {
        // 验证用户是否登录
        if(session('?user')){
            // 进来表示已登录
            echo "<script>window.history.back();</script>";exit;
        }
        //赋值数据集View模板输出  
        $data = array();
        $data['url'] = session('Callback') ? session('Callback') : url('User/index');
        return view('index',$data);
    }  

    //会员注册
    public function register()
    {
        $pid = input('pid');
        // header("Location:".url('Index/register').'?pid='.$pid);exit;
        // 验证用户是否登录
        if(session('?user')){
            // 进来表示已登录
            header('Location:'.url('User/index'));exit;
        }
        $name = \think\Db::name('member')->where(array('id'=>$pid))->value('username');
        return view('register',array('name'=>$name,'pid'=>$pid));
    } 

    //忘记密码
    public function forget()
    {
        // 验证用户是否登录
        if(session('?user')){
            // 进来表示已登录
            header('Location:'.url('User/index'));exit;
        }
        return view();
    } 

    // 用户退出操作
    public function logout(){
        //清空$_SESSION
        session('user',null);
        header('Location:'.url('Login/index'));exit;
    }
}
