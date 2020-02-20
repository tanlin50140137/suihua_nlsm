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

class Index
{
    public function __construct(){
        // echo '网站升级维护中...';exit;
    }

	/**
     * @param $name
     * 如果在本控制器中找不到该操作那就运行我
     */
    public function _empty($name)
    {
        return view($name);
    }

    public function index()
    {
        $url = $_SERVER['REQUEST_URI'];
        if($url == '/'){
            header("Location:".url('Index/index'));exit;
        }
        return view();
    }

    //会员注册
    public function register()
    {
        $pid = input('pid');
        $name = \think\Db::name('member')->where(array('id'=>$pid))->value('username');
        return view('register',array('name'=>$name));
    } 
}
