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

class Common extends \think\Controller
{
    public function __construct(){
		// 验证用户是否登录
		if(!session('?admin')){
			// 进来表示未登录
			$this->redirect('Login/index');exit;
		}
    }

    // 检测用户是否有权限修改查看内容
    public function checkpower($id){
    	//超级管理员拥有所有权限
    	if(session('admin.id') != 1){
			//获取管理员信息
    		$admin = get_admin(session('admin.id'));
    		$power = explode(',', $admin['power']);
			if(!in_array($id,$power)){
				if(request()->isAjax()){
					echo json_encode(array('success'=>false,'info'=>'你的权限不足！'));exit;
				}else{
					return error('你的权限不足！');exit;
				}
			}
    	}
    }
}
