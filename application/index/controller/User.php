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

class User extends \think\Controller
{
    public function __construct(){
        echo '请下载APP登陆！';exit;
        //重载父类方法
        parent::__construct();
        // 验证用户是否登录
        if(!session('?user')){
            //保存上一页访问地址
            session('Callback',$_SERVER['REQUEST_URI']);
            // 进来表示未登录
            header('Location:'.url('Login/index'));exit;
        }
        $user = get_member(session('user.id'));
        $user['appkey'] = \think\Db::name('member')->where(array('id'=>session('user.id')))->value('appkey');
        $this->assign('user',$user);
        $this->assign('cur','user');
    }

    //个人中心
    public function index()
    {
        return view();
    }

    //修改密码
    public function personal_modify()
    {
        return view();
    }

    //修改支付密码
    public function paymentcode()
    {
        return view();
    }

    //绑定支付宝
    public function alipays()
    {
        return view();
    }

    //个人资料
    public function personal()
    {
        return view();
    }

    //话费充值
    public function topup()
    {
        return view();
    } 

    //话费订单
    public function orderset()
    {
        return view();
    } 

    //会员升级套餐
    public function destoonupgrade()
    {
        return view();
    } 

    //投资项目
    public function partake()
    {
        $investId = input('get.investId','');
        //赋值数据集View模板输出  
        $data = array();
        $data['investId'] = $investId;
        return view('partake',$data);
    }

    //我的投资
    public function myinvest()
    {
        return view();
    }

    //购物车
    public function cart()
    {
        $data = array();
        $data['cur'] = 'cart';
        return view('cart',$data);
    }

    //确定订单
    public function confirmorder(){
        return view();
    }

    //添加地址
    public function addsite(){
        return view();
    }

    //收货地址
    public function mysite(){
        return view();
    }

    //投资项目
    public function editsite()
    {
        $siteId = input('get.siteId','');
        //赋值数据集View模板输出  
        $data = array();
        $data['siteId'] = $siteId;
        return view('editsite',$data);
    }
}
