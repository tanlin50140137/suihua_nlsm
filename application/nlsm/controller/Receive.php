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

class Receive extends Common
{
    // 套餐l领取
    public function index(){
        //验证用户权限
        Common::checkpower(49);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['c.name|a.username|b.paytype'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }

        $start_time = input('get.start_time','');
        $pageParam['query']['start_time'] = $start_time;     
        $end_time = input('get.end_time','');
        $pageParam['query']['end_time'] = $end_time;  
        if (!empty($start_time) && !empty($end_time)) {
            $where['a.create_time'] = array(array('EGT', strtotime($start_time)), array('ELT', strtotime($end_time)+(60*60*24)), 'AND');
        }else if (!empty($start_time)) {
            $where['a.create_time'] = array('EGT', strtotime($start_time));
        }else if (!empty($end_time)) {
            $where['a.create_time'] = array('ELT', strtotime($end_time)+(60*60*24));
        }

        //查询满足要求的数据并且每页显示24条数据
        // $list = \think\Db::name('setmeal_log')->field(true)->where($where)->paginate(20,false,$pageParam);
        // 查询操作日志数据 并且每页显示20条数据
        $list = \think\Db::name('setmeal_log')->alias('a')
                                     ->field('a.*,s.name,s.logo,s.goods,b.payprice,c.name busname')
                                     ->join('__SETMEAL__ s','s.id = a.goods','LEFT')
                                     ->join('__BUSINESS_TRANSFER__ b','b.order_id = a.order_id','LEFT')
                                     ->join('__BUSINESS__ c','c.id = b.uid','LEFT')
                                     ->where($where)
                                     ->order("a.create_time desc")
                                     ->paginate(50,false,$pageParam);

        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //订单状态
            // $value['state'] = $this->get_state($value['is_state']);
            // //购买商品数据
            // $value['goods'] = json_decode($value['goods'],true);
            $data[$key] = $value;
        }

        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['data'] = $data;
        $param['list'] = $list;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        return view('index',$param);
    }

    //删除套餐
    public function delete(){
        //验证用户权限
        Common::checkpower(48);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除商家
        if (\think\Db::name('setmeal_log')->where(array('id'=>array('IN',$idlist)))->delete()) {
          
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 套餐');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改会员状态
    public function state() {
        //验证用户权限
        Common::checkpower(18);
        //获取修改会员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('setmeal')->where(array('id'=>$id))->setField('is_state', $state)) {
           
            //记录系统日志
            admin_log("修改 ID:".$id." 会员状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }
}