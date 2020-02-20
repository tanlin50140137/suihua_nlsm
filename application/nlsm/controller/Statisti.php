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

class Statisti extends Common
{
    //会员统计
    public function index()
    {
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        $list['data'] = array();
        $list['list'] = array();
        //输入搜索内容
        $start_time = input('get.start_time','');
        $end_time = input('get.end_time','');
        if (!empty($start_time) && !empty($end_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime($end_time);
        }else if (!empty($start_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime(date('Y-m-d'));
        }else if (!empty($end_time)) {
            $time1 = strtotime('2017-10-01');
            $time2 = strtotime($end_time);
        }
        if(empty($start_time) && empty($end_time)){
            //今天
            $time1 = strtotime(date('Y-m-d'));
            for ($i = 0; $i < 24; $i++) {
                $time2 = $time1 + 60*60;
                $count = \think\Db::name('member')->where(array('create_time' => array(array('EGT', $time1), array('ELT', $time2), 'AND')))->count();
                $list['data'][$i] = '"'.date('H', $time1).'点"';
                $list['list'][$i] = $count;
                $time1 = $time2;
            }
        }else{
            if($time2 > $time1){
                $time = ($time2 - $time1) / 86400;
                for ($i = 0; $i <= $time; $i++) {
                    $time2 = $time1 + 60*60*24;
                    $count = \think\Db::name('member')->where(array('create_time' => array(array('EGT', $time1), array('ELT', $time2), 'AND')))->count();
                    $list['data'][$i] = '"'.date('Y-m-d', $time1).'"';
                    $list['list'][$i] = $count;
                    $time1 = $time2;
                }
            }
        }
        $list['data'] = implode(',', $list['data']);
        $list['list'] = implode(',', $list['list']);

        $count = array();
        $count['count'] = \think\Db::name('member')->count();
        $count['normal'] = \think\Db::name('member')->where(array('is_state' => 1))->count();
        $count['disable'] = \think\Db::name('member')->where(array('is_state' => 2))->count();
        $time = strtotime(date('Y-m-d'));
        $count['today'] = \think\Db::name('member')->where(array('create_time' => array(array('EGT', $time), array('ELT', $time + 60*60*24), 'AND')))->count();
        $count['point'] = \think\Db::name('member')->where(array('is_state' => 1))->sum('point');
        $count['money'] = \think\Db::name('member')->where(array('is_state' => 1))->sum('money');
        
        //赋值数据集View模板输出  
    	//赋值数据集View模板输出  
        $data = array();
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['list'] = $list;
        $data['count'] = $count;
        $data['cur'] = 'user';
        return view('index',$data);
    }

    //会员等级
    public function level()
    {
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        $list['data'] = array();
        $list['list'] = array();
        // 获取等级分组信息
        $level = get_member_level();
        if ($level) {
            foreach($level as $key => $value) {
                //统计分组会员总数
                $value['usernum'] = \think\Db::name('member')->where(array('level_id'=>$value['id']))->count();
                $list['data'][$key] = '"'.$value['name'].'"';
                $list['list'][$key] = "{value:".$value['usernum'].", name:'".$value['name']."'}";
                $level[$key] = $value;
            }
        }
        $list['data'] = implode(',', $list['data']);
        $list['list'] = implode(',', $list['list']);
        //赋值数据集View模板输出  
        $data = array();
        $data['level'] = $level;
        $data['list'] = $list;
        $data['cur'] = 'level';
        return view('level',$data);
    }

    //商品统计
    public function goods()
    {
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        $list['data'] = array();
        $list['list'] = array();
        //输入搜索内容
        $start_time = input('get.start_time','');
        $end_time = input('get.end_time','');
        if (!empty($start_time) && !empty($end_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime($end_time);
        }else if (!empty($start_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime(date('Y-m-d'));
        }else if (!empty($end_time)) {
            $time1 = strtotime('2017-10-01');
            $time2 = strtotime($end_time);
        }else{
            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d'));
        }
        if($time2 > $time1){
            $time = ($time2 - $time1) / 86400;
            for ($i = 0; $i <= $time; $i++) {
                $time2 = $time1 + 60*60*24;
                $count = \think\Db::name('goods')->where(array('create_time' => array(array('EGT', $time1), array('ELT', $time2), 'AND')))->count();
                $list['data'][$i] = '"'.date('Y-m-d', $time1).'"';
                $list['list'][$i] = $count;
                $time1 = $time2;
            }
        }

        $list['data'] = implode(',', $list['data']);
        $list['list'] = implode(',', $list['list']);
        $count = array();
        $count['count'] = \think\Db::name('goods')->count();
        $count['normal'] = \think\Db::name('goods')->where(array('is_state' => 1))->count();
        $count['disable'] = \think\Db::name('goods')->where(array('is_state' => 2))->count();
        $time = strtotime(date('Y-m-d'));
        $count['today'] = \think\Db::name('goods')->where(array('create_time' => array(array('EGT', $time), array('ELT', $time + 60*60*24), 'AND')))->count();

        //赋值数据集View模板输出  
        $data = array();
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['list'] = $list;
        $data['count'] = $count;
        $data['cur'] = 'goods';
        return view('goods',$data);
    }

    //商家统计
    public function business()
    {
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        // 获取省区信息
        $region = get_parent_region(1);
        if ($region) {
            foreach($region as $key => $value) {
                //统计省区商家总数
                $number = \think\Db::name('business')->where(array('city'=>array('LIKE', "{$value['region_name']}%")))->count();
                $value['region_name'] = str_replace("省","",$value['region_name']);
                if($number){
                    $list[$key] = "{name:'".$value['region_name']."', selected:true,value:'".$number."'}";
                }else{
                    $list[$key] = "{name:'".$value['region_name']."', selected:false,value:'0'}";
                }
            }
        }
        $list = implode(',', $list);

        $count = array();
        $count['count'] = \think\Db::name('business')->count();
        $count['normal'] = \think\Db::name('business')->where(array('is_state' => 1))->count();
        $count['disable'] = \think\Db::name('business')->where(array('is_state' => 2))->count();

        //赋值数据集View模板输出  
        $data = array();
        $data['list'] = $list;
        $data['count'] = $count;
        $data['cur'] = 'business';
        return view('business',$data);
    }

    //订单统计
    public function order()
    {
        set_time_limit(0);
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        $list['data'] = array();
        $list['list'] = array();
        $list['name'] = array();

        //统计订单数量
        $count = array();
        $count['count']['is_state'] = 'count';
        $count['count']['state'] = action('Order/get_state','count');
        $count['count']['count'] = \think\Db::name('member_order')->count();
        $order = \think\Db::name('member_order')->group('is_state')->column('count(order_id)','is_state');
        for ($i=1; $i < 10 ; $i++) { 
            $order[$i] = isset($order[$i]) ? $order[$i] : 0;
            $count[$i]['is_state'] = $i;
            $count[$i]['state'] = action('Order/get_state',$i);
            $count[$i]['count'] = $order[$i];
            $list['name'][$i] = '"'.strip_tags($count[$i]['state']).'"';
        }
       
        $time = strtotime(date('Y-m-d'));
        $count['today']['is_state'] = 'today';
        $count['today']['state'] = action('Order/get_state','today');
        $count['today']['count'] = \think\Db::name('member_order')->where(array('create_time' => array(array('EGT', $time), array('ELT', $time + 60*60*24), 'AND')))->count();
        
        //输入搜索内容
        $start_time = input('get.start_time','');
        $end_time = input('get.end_time','');
        if (!empty($start_time) && !empty($end_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime($end_time);
        }else if (!empty($start_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime(date('Y-m-d'));
        }else if (!empty($end_time)) {
            $time1 = strtotime('2017-10-01');
            $time2 = strtotime($end_time);
        }else{
            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d'));
        }

        $arr = array();
        if($time2 > $time1){
            $time = ($time2 - $time1) / 86400;
            if($time > 31){
                echo "<script>alert('范围请在一个月内！');window.history.back();</script>";
            }
            for ($i = 0; $i <= $time; $i++) {
                $time2 = $time1 + 60*60*24;
                $list['data'][$i] = '"'.date('Y-m-d', $time1).'"';
                $arr[] = \think\Db::name('member_order')->where(array('create_time' => array(array('EGT', $time1), array('ELT', $time2), 'AND')))->group('is_state')->column('count(order_id)','is_state');
                $time1 = $time2;
            }
            
        }
        for ($i=1; $i < 10 ; $i++) { 
            $list['list'][$i]['state'] = strip_tags(action('Order/get_state',$i));
            $list['list'][$i]['list'] = array();
            foreach ($arr as $key => $value) {
                if(isset($value[$i])){
                    array_push($list['list'][$i]['list'], $value[$i]);
                }else{
                    array_push($list['list'][$i]['list'], 0);
                }
            }
            $list['list'][$i]['list'] = implode(',', $list['list'][$i]['list']);
        }

        $list['data'] = implode(',', $list['data']);
        $list['name'] = implode(',', $list['name']);

        //赋值数据集View模板输出  
        $data = array();
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['list'] = $list;
        $data['count'] = $count;
        $data['cur'] = 'order';
        return view('order',$data);
    }

    //充值订单
    public function recharg()
    {
        //验证用户权限
        Common::checkpower(32);
        $list = array();
        $list['data'] = array();
        $list['list'] = array();
        //输入搜索内容
        $start_time = input('get.start_time','');
        $end_time = input('get.end_time','');
        if (!empty($start_time) && !empty($end_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime($end_time);
        }else if (!empty($start_time)) {
            $time1 = strtotime($start_time);
            $time2 = strtotime(date('Y-m-d'));
        }else if (!empty($end_time)) {
            $time1 = strtotime('2017-10-01');
            $time2 = strtotime($end_time);
        }else{
            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d'));
        }
        $arr = array();
        if($time2 > $time1){
            $time = ($time2 - $time1) / 86400;
            for ($i = 0; $i <= $time; $i++) {
                $time2 = $time1 + 60*60*24;
                $list['data'][$i] = '"'.date('Y-m-d', $time1).'"';
                $arr[] = \think\Db::name('recharg_order')->where(array('create_time' => array(array('EGT', $time1), array('ELT', $time2), 'AND')))->group('is_state')->column('count(order_id)','is_state');
                $time1 = $time2;
            }
            
        }
        $list['list'][1]['name'] = '未支付订单';
        $list['list'][1]['list'] = array();
        $list['list'][2]['name'] = '已支付订单';
        $list['list'][2]['list'] = array();
        foreach ($arr as $key => $value) {
            if(isset($value[1])){
                array_push($list['list'][1]['list'], $value[1]);
            }else{
                array_push($list['list'][1]['list'], 0);
            }
            if(isset($value[2])){
                array_push($list['list'][2]['list'], $value[2]);
            }else{
                array_push($list['list'][2]['list'], 0);
            }
        }
        $list['list'][1]['list'] = implode(',', $list['list'][1]['list']);
        $list['list'][2]['list'] = implode(',', $list['list'][2]['list']);

        $list['data'] = implode(',', $list['data']);

        $count = array();
        $count['count'] = \think\Db::name('recharg_order')->count();
        $count['normal'] = \think\Db::name('recharg_order')->where(array('is_state' => 2))->count();
        $count['disable'] = \think\Db::name('recharg_order')->where(array('is_state' => 1))->count();
       
        //赋值数据集View模板输出  
        $data = array();
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['list'] = $list;
        $data['count'] = $count;
        $data['cur'] = 'recharg';
        return view('recharg',$data);
    }
}
