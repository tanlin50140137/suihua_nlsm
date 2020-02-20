<?php
namespace app\nlsm\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Coupon extends Common
{
    // 优惠券列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(20);
        // 清除已过期的优惠券
        if (\think\Db::name('coupon_list')->where(array('is_state' => 1, 'end_time' => array('LT', time())))->count() > 0) {
            \think\Db::name('coupon_list')->where(array('is_state' => 1, 'end_time' => array('LT', time())))->setField('is_state', 3);
        }
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        $typeid = input('get.typeid', '');
        if($typeid){
            $where['c.typeid'] = $typeid; 
            $pageParam['query']['typeid'] = $typeid;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['c.name|c.number|b.name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('coupon_list')->alias('c')
                                              ->field('c.*,m.nickname,b.name busname')
                                              ->join('__MEMBER__ m','c.uid = m.id','LEFT')
                                              ->join('__BUSINESS__ b','c.bus_id = b.id','LEFT')
                                              ->where($where)
                                              ->order('id desc')
                                              ->paginate(20,false,$pageParam);
        //遍历拼接数据信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['start_time'] = date('Y-m-d', $value['start_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            switch ($value['is_state']) {
                case '1':
                    $value['state'] = '未使用';
                    break;
                case '2':
                    $value['state'] = '<span style="color:red;">已使用</span>';
                    break;
                case '3':
                    $value['state'] = '<span style="color:red;">已过期</span>';
                    break;
                case '4':
                    $value['state'] = '<span style="color:red;">已冻结</span>';
                    break;
                case '5':
                    $value['state'] = '<span style="color:red;">用户删除</span>';
                    break;
            }
            $value['busname'] = $value['busname'] ? $value['busname'] : '平台通用';
            $value['nickname'] = $value['nickname'] ? $value['nickname'] : '';
            $value['order_id'] = $value['order_id'] ? $value['order_id'] : '';
            $value['usetime'] = $value['usetime'] ? date('Y-m-d H:i:s', $value['usetime']) : '';
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 删除优惠券
    public function delete(){
        //验证用户权限
        Common::checkpower(20);
        //要删除的优惠券id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('coupon_list')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 优惠券');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //冻结优惠券
    public function state() {
        //验证用户权限
        Common::checkpower(20);
        //获取修改优惠券类型id
        $idlist = input('post.idlist', '');
        $state = input('post.state','1');
        if($state == 1){
            if (\think\Db::name('coupon_list')->where(array('id' => array('IN', $idlist),'is_state'=>1))->setField('is_state', 4)) {
                //记录系统日志
                admin_log("冻结 ID:".$idlist." 优惠券");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else if($state == 4){
            if (\think\Db::name('coupon_list')->where(array('id' => array('IN', $idlist),'is_state'=>4))->setField('is_state', 1)) {
                //记录系统日志
                admin_log("解冻 ID:".$idlist." 优惠券");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'该优惠券不能进行冻结操作！'));exit;
        }
        
    }

    // 显示优惠券类型页面
    public function type(){
        //验证用户权限
        Common::checkpower(19);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['c.name|b.name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('coupon')->alias('c')
                                         ->field('c.*,b.name busname')
                                         ->join('__BUSINESS__ b','c.bus_id = b.id','LEFT')
                                         ->where($where)
                                         ->order('id')
                                         ->paginate(20,false,$pageParam);
        //遍历拼接数据信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['start_time'] = date('Y-m-d', $value['start_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            $value['busname'] = $value['busname'] ? $value['busname'] : '平台通用';
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('type',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加优惠券类型
    public function type_add(){
        //验证用户权限
        Common::checkpower(19);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['start_time'] = strtotime(input('post.start_time',''));
            $data['end_time'] = strtotime(input('post.end_time',''));
            $data['prefix'] = strtoupper($data['prefix']);
            $data['create_time'] = time();
            //添加优惠券类型
            if($id = \think\Db::name('coupon')->insertGetId($data)){
                //记录系统日志
                admin_log('添加 ID:'.$id.' 优惠券类型');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else{
            //商家列表
            $business = \think\Db::name('Business')->field('id,name')->where(array('is_state'=>1))->select();
            //赋值数据集View模板输出  
            return view('type_add',array('business'=>$business));
        }
    }

    // 修改优惠券类型
    public function type_edit(){
        //验证用户权限
        Common::checkpower(19);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','0');
            $data = input('post.','');
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['prefix'] = strtoupper($data['prefix']);
            //添加优惠券类型
            if($id = \think\Db::name('coupon')->where(array('id'=>$id))->update($data)){
                //记录系统日志
                admin_log('修改 ID:'.$id.' 优惠券类型信息');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            $id = input('get.id', 0);
            $list = \think\Db::name('coupon')->field(true)->where(array('id'=>$id))->find();
            if (!$list){return error('该类型优惠券不能进行修改操作！');exit;} 
            $list['start_time'] = date('Y-m-d', $list['start_time']);
            $list['end_time'] = date('Y-m-d', $list['end_time']);
            //商家列表
            $business = \think\Db::name('Business')->field('id,name')->where(array('is_state'=>1))->select();
            //赋值数据集View模板输出  
            return view('type_edit',array('list'=>$list,'business'=>$business));
        }
    }

    // 删除优惠券类型
    public function type_del(){
        //验证用户权限
        Common::checkpower(19);
        //要删除的优惠券类型id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('coupon')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 优惠券类型');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改优惠券类型状态
    public function type_state() {
        //验证用户权限
        Common::checkpower(19);
        //获取修改优惠券类型id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('coupon')->where(array('id'=>$id))->setField('is_state', $state)) {
            //记录系统日志
            admin_log("修改 ID:".$id." 优惠券类型状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //发放优惠券
    public function send() {
        //验证用户权限
        Common::checkpower(20);
        if (request()->isPost()) {
            //设置程序运行不超时
            set_time_limit(0);
            $date = strtotime(date('Y-m-d'));
            $id = input('post.id', '0');
            //判断优惠券类型是否正确
            $list = \think\Db::name('coupon')->field(true)->where(array('id'=>$id,'is_state'=>1))->find();
            if (!$list){return json(array('success'=>false,'info'=>'该类型优惠券不能进行发放操作！'));exit;} 
            if ($date < $list['start_time'] || $date > $list['end_time']) {
                return json(array('success'=>false,'info'=>'优惠券类型未到发放时间或已过发放时间！'));exit;
            }
            //获取发放数量
            $number = input('post.number', 0);
            if (!$number) {return json(array('success'=>false,'info'=>'请输入优惠券发放数量！'));exit;}
            //添加生成批次优惠券
            $data = array();
            for($i = 0; $i < $number; $i++) {
                $cpns_sn = $list['prefix'].str_pad(mt_rand(1, 99999999),8,'0',STR_PAD_LEFT);
                $data[] = array(
                    'typeid' => $list['id'],
                    'number' => $cpns_sn,
                    'name' => $list['name'],
                    'money' => $list['money'],
                    'min_price' => $list['min_price'],
                    'max_price' => $list['max_price'],
                    'start_time' => $list['start_time'],
                    'end_time' => $list['end_time'],
                    'bus_id' => $list['bus_id'],
                );
            }
            \think\Db::name('coupon_list')->insertAll($data);
            //记录系统日志
            admin_log('生成 '.$number.' 张 '.$list['name'].'优惠券');
            return json(array('success'=>true,'info'=>'优惠券生成成功！'));
        }else{
            $id = input('get.id', '0');
            $list = \think\Db::name('coupon')->field(true)->where(array('id'=>$id,'is_state'=>1))->find();
            if (!$list){return error('该类型优惠券不能进行发放操作！');exit;} 
            //判断类型是否可以发放
            $date = strtotime(date('Y-m-d'));
            if ($date < $list['start_time'] || $date > $list['end_time']) {
                return error('优惠券类型未到发放时间或已过发放时间！');exit;
            }
            return view('send',array('list'=>$list));
        }
    }
}