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

// 指定允许其他域名访问  
header('Access-Control-Allow-Origin:*');  
// 响应类型  
header('Access-Control-Allow-Methods:POST');  
// 响应头设置  
header('Access-Control-Allow-Headers:x-requested-with,content-type');  

class Setmeal
{
    public function index()
    {
        $id = input('id');

        $order = \think\Db::name('business_transfer')->field('uid,paymoney')->where(array('order_id'=>$id))->find();
        
        if(\think\Db::name('setmeal_log')->where(array('order_id'=>$id))->count() > 0){
            echo '<script>alert("已经领取过了！");window.location.href="'.url('Business/payment').'?id='.$order['uid'].'"</script>';exit;
        }

        $setmeal = \think\Db::name('business')->where(array('id'=>$order['uid']))->value('setmeal');
        
        $where = array();
        $where['minprice'] = array('ELT',$order['paymoney']);
        $where['maxprice'] = array('EGT',$order['paymoney']);
        $where['id'] = array('IN',$setmeal);
           

        $list = \think\Db::name('setmeal')->field(true)->where($where)->select();

        if (request()->isPost()) {
            return json(array('success'=>true,'info'=>'','list'=>$list));
        }else{
            //赋值数据集View模板输出  
            $data = array();
            $data['id'] = $id;
            $data['list'] = $list;
            return view('index',$data);
        }
       
    }
   
    

    //领取
    public function receive(){
        $data = array();
        $data['mobile'] = input('mobile');
        $data['username'] = input('username');
        $data['goods'] = input('goods');
        $data['order_id'] = input('id');
        $data['create_time'] = time();
        
        

        $order = \think\Db::name('business_transfer')->field(true)->where(array('order_id'=>$data['order_id'],'rebate_time'=>0))->find();

        $data['bus_id'] = $order['uid'];

        if(!$data['mobile']){
            return json(array('success'=>false,'info'=>'请输入手机号！'));exit;
        }
        if(!$data['goods']){
            return json(array('success'=>false,'info'=>'请选择套餐！'));exit;
        }
        if(\think\Db::name('setmeal_log')->where(array('order_id'=>$data['order_id']))->count() > 0){
            return json(array('success'=>false,'info'=>'已经领取过了！'));exit;
        }

        //获取套餐商品信息
        $goods = \think\Db::name('setmeal')->where(array('id'=>$data['goods']))->value('goodslist');
        if(!$goods){
            return json(array('success'=>false,'info'=>'该产品已经领取完了！'));exit;
        }
        $data['goodslist'] = $goods;
        $goods = json_decode($goods,true);
        foreach ($goods as $key => $value) {
            $key = str_replace("goods_","",$key);
            $value = $value ? $value : 0;
            if(\think\Db::name('business_setmeal')->where(array('bus_id'=>$data['bus_id'],'goods_id'=>$key))->sum('number') < $value){
                return json(array('success'=>false,'info'=>'该产品已经领取完了！'));exit;
            }
        }

        if ($id = \think\Db::name('setmeal_log')->insertGetId($data)) {
            foreach ($goods as $key => $value) {
                $key = str_replace("goods_","",$key);
                $value = $value ? $value : 0;
                $log = array();
                $log['number'] = '-'.$value;
                $log['create_time'] = time();
                $log['bus_id'] = $data['bus_id'];
                $log['goods_id'] = $key;
                $log['remark'] = $id;
                \think\Db::name('business_setmeal')->insert($log);
            }

            // 找到推荐人
            $user = get_member($order['pid']);
            if($user){
                $money = \think\Db::name('setmeal')->where(array('id'=>$data['goods']))->value('huitui');
                \think\Db::name('member')->where(array('id'=>$user['id']))->setInc('money',$money);
                //写入日志
                member_money_log("商家收款",$user['id'],"商家收款返利",$money,$id);

                \think\Db::name('setmeal_log')->where(array('id'=>$id))->setField('huitui',$money);
            }

            \think\Db::name('business_transfer')->where(array('order_id'=>$data['order_id']))->setField('rebate_time',time());

            return json(array('success'=>true,'info'=>'领取成功！'));exit;
        }else {
            //获取修改错误原因
            return json(array('success'=>false,'info'=>'领取失败！'));exit;
        }
    }

     //领取
    public function goods(){
        $id = input('id');
        $list = \think\Db::name('setmeal')->field(true)->where(array('id'=>$id))->find();
        //赋值数据集View模板输出  
        $data = array();
        $data['id'] = $id;
        $data['list'] = $list;
        return view('goods',$data);
    }

}
