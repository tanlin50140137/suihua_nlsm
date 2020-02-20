<?php
namespace app\home\controller;
use think\Controller;
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
        $type = get_goods_type();
       	$picture = get_picture();
       	$is_recom = \think\Db::name('goods')->where(array('is_recom'=>2))->limit(0,3)->select();
        $is_hot = \think\Db::name('goods')->where(array('is_hot'=>1))->limit(0,4)->select();
        $goods = \think\Db::name('goods')->limit(0,6)->select();

       
        // dump($goods);

        $param = array();
        $param['type'] = $type;
        $param['picture'] = $picture;
        $param['is_recom'] = $is_recom;
        $param['is_hot'] = $is_hot;
        $param['goods'] = $goods;
      
        return view('index',$param);
    }

    //会员注册
    public function register()
    {
        $pid = input('pid');
        $name = \think\Db::name('member')->where(array('id'=>$pid))->value('username');
         return view('register',array('name'=>$name));
    } 
}