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

class Agreement extends Common
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
            $where['name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('agreement')->field(true)->where($where)->paginate(20,false,$pageParam);
        // 查询操作日志数据 并且每页显示20条数据
        // $list = \think\Db::name('setmeal_log')->alias('a')
        //                              ->field('a.*,s.name,s.logo,s.goods,b.payprice')
        //                              ->join('__SETMEAL__ s','s.id = a.goods','LEFT')
        //                              ->join('__BUSINESS_TRANSFER__ b','b.order_id = a.order_id','LEFT')
        //                              ->where($where)
        //                              ->order("a.create_time desc")
        //                              ->paginate(20,false,$pageParam);

        $data = $list->all();
        // dump($data);
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            switch($value['is_state']) {
                case '0': $value['state'] = '<span style="color:red;">隐藏</span>'; break;
                case '1': $value['state'] = '待审核'; break;
                case '2': $value['state'] = '显示'; break;
                case '3': $value['state'] = '<span style="color:red;">失败</span>'; break;
        }
            $data[$key] = $value;
           } 
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    //删除套餐
    public function delete(){
        //验证用户权限
        Common::checkpower(48);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除商家
        if (\think\Db::name('agreement')->where(array('id'=>array('IN',$idlist)))->delete()) {
          
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
        if (\think\Db::name('agreement')->where(array('id'=>$id))->setField('is_state', $state)) {
           
            //记录系统日志
            admin_log("修改 ID:".$id." 会员状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }
}