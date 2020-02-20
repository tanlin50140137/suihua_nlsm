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

class Login extends \think\Controller
{
	// 显示登陆页面
    public function index(){
        // 验证用户是否登录
        if(session('?admin')){
            $this->redirect('Index/index');exit;
        }
        return view('index');
    }

    // 用户登陆验证
    public function login(){
        //验证验证码
        $code = input('post.code','');
        if($code != session('login_code')){return json(array('success'=>false,'info'=>'验证码错误！'));exit;}
        //拼接条件
        $where = array();
        //用户账号
        $where['username'] = input('post.username','');
        //用户密码
        $where['password'] = password(input('post.password',''));
        $where['is_state'] = 1;
        //验证账号密码输入错误次数
        $lognum = \think\Cache::get($where['username']);
        if($lognum > 5){return json(array('success'=>false,'info'=>'密码输入错误次数较多，请三个小时之后再试！'));exit;}
        $list = \think\Db::name('admin')->field(true)->where($where)->find();
        //判断用户是否登陆成功
        if($list){
            //登陆成功 把数据存放到$_SESSION
            session('admin',$list);
            //获取登录地址
            require_once("./extend/location.php");
            // 实例化类 参数表示IP地址库文件
            $location = new \Location('UTFWry.dat');
            $result = $location->getlocation();
            //记录系统日志
            admin_log('登陆成功，登陆地点：'.$result['country'].'；'.$result['area']);
            //显示后台首页
            return json(array('success'=>true,'info'=>'登录成功！'));exit;
        }else{
            //记录账号密码输入错误次数
            \think\Cache::set($where['username'],$lognum + 1,10800);
            // 登录失败
            return json(array('success'=>false,'info'=>'登录失败，账号密码错误！'));exit;
        }
    }

    // 生成验证码
    public function code(){
        $arr = array('+','-','x','÷');
        $mark = $arr[rand(0,1)];
        $num1 = rand(0,30);
        $num2 = rand(0,30);
        switch ($mark) {
            case '+':
                $sum = $num1 + $num2;
                $code = $num1.' + '.$num2.' = ?';
                break;
            case '-':
                $sum = $num1 - $num2;
                $code = $num1.' - '.$num2.' = ?';
                if($num1 < $num2){
                    $sum = $num2 - $num1;
                    $code = $num2.' - '.$num1.' = ?';
                }
                break;
            case 'x':
                $sum = $num1 * $num2;
                $code = $num1.' x '.$num2.' = ?';
                break;
            case '÷':
                $sum = $num1 / $num2;
                $code = $num1.' ÷ '.$num2.' = ?';
                break;
        }
        session('login_code',$sum);
        return json(array('success'=>true,'info'=>'','list'=>$code));exit;
    }

    // 用户退出操作
    public function logout(){
        //清空$_SESSION
        session('admin',null);
        $this->redirect("Login/index");
    }
}
