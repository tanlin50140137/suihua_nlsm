<?php
namespace app\nlsm\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Index extends Common
{
	//后台首页
    public function index()
    {
        //获取用户拥有的权限
        $admin = session('admin');
        $admin['power'] = get_admin_power();
    	//赋值数据集View模板输出  
        $data = array();
        $data['admin'] = $admin;
    	return view('index',$data);
    }

    //后台欢迎界面
    public function welcome(){
        
        return view();
    }

    // 清空删除缓存
    public function clean(){
        \think\Cache::clear();
        $this->redirect('Index/index');
    }
}
