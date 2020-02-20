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

class Order extends Common
{
    // 订单列表
    public function index(){
        //验证用户权限
        Common::checkpower(25);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //订单状态
        $is_state = input('get.is_state','0');     
        if($is_state){
            $where['o.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //筛选字段       
        $field = input('get.field','order_id');  
        $pageParam['query']['field'] = $field;
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['o.'.$field] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        $start_time = input('get.start_time','');
        $pageParam['query']['start_time'] = $start_time;     
        $end_time = input('get.end_time','');
        $pageParam['query']['end_time'] = $end_time;  
        if (!empty($start_time) && !empty($end_time)) {
            $where['o.create_time'] = array(array('EGT', strtotime($start_time)), array('ELT', strtotime($end_time)+(60*60*24)), 'AND');
        }else if (!empty($start_time)) {
            $where['o.create_time'] = array('EGT', strtotime($start_time));
        }else if (!empty($end_time)) {
            $where['o.create_time'] = array('ELT', strtotime($end_time)+(60*60*24));
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_order')->alias('o')
                                               ->field('o.*,m.nickname')
                                               ->join('__MEMBER__ m','o.uid = m.id')
                                               ->where($where)
                                               ->order('o.create_time desc')
                                               ->paginate(24,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //订单状态
            $value['state'] = $this->get_state($value['is_state']);
            //购买商品数据
            $value['goods'] = json_decode($value['goods'],true);
            $data[$key] = $value;
        }
        //统计订单数量
        $count = \think\Db::name('member_order')->group('is_state')->column('count(order_id)','is_state');
        $count[0] = 0;
        for ($i=1; $i < 10 ; $i++) { 
            $count[$i] = isset($count[$i]) ? $count[$i] : 0;
            $count[0] = $count[0] + $count[$i];
        }
        //赋值数据集View模板输出  
        $array = array();
        $array['field'] = $field;
        $array['keyword'] = $keyword;
        $array['list'] = $list;
        $array['data'] = $data;
        $array['count'] = $count;
        $array['is_state'] = $is_state;
        $array['start_time'] = $start_time;
        $array['end_time'] = $end_time;
        return view('index',$array);
    }

    //查看订单详细信息
    public function read(){
        //验证用户权限
        Common::checkpower(25);
        //获取查看订单id
        $order_id = input('get.id', '0');
        $uid = input('get.uid', '0');
        //获取订单信息
        $order = get_order($order_id,$uid);
        if (!$order){return error('不存在的订单信息！');exit;} 
        //转换订单时间
        $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
        $order['return_time'] = $order['return_time'] ? date('Y-m-d H:i:s', $order['return_time']) : '';
        $order['refund_time'] = $order['refund_time'] ? date('Y-m-d H:i:s', $order['refund_time']) : '';
        $order['paytime'] = $order['paytime'] ? date('Y-m-d H:i:s', $order['paytime']) : '';
        //订单状态
        $order['state'] = $this->get_state($order['is_state']);
        //购买商品信息
        $order['goods'] = json_decode($order['goods'],true);
        foreach ($order['goods'] as $key => $value) {
            //商品状态
            $value['is_state'] = isset($value['is_state']) ? $value['is_state'] : '1';
            $value['state'] = $this->get_state($value['is_state']);
            //已发货数量
            $value['sendnum'] = isset($value['sendnum']) ? $value['sendnum'] : '0';
            //商品供应商信息
            if($value['bus_id']){
                $business = get_business($value['bus_id']);
                $value['busname'] = $business['name'];
            }else{
                $value['busname'] = '自营商品';
            }
            $order['goods'][$key] = $value;
        }
         //购买用户信息
        $member = \think\Db::name('member')->field('username,nickname')->where(array('id'=>$order['uid']))->find();
        if(!$member){return error("用户不存在！");exit;}
        //获取订单发货记录
        $shipping = get_member_shipping($order_id);
        //退货订单
        $shipping_return = array();
        if($shipping){
            foreach ($shipping as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                if($value['returnnum'] > 0){
                    $shipping_return[] = $value;
                }
                $shipping[$key] = $value;
            }
        }
        //赋值数据集View模板输出  
        $data = array();
        $data['order'] = $order;
        $data['shipping'] = $shipping;
        $data['shipping_return'] = $shipping_return;
        $data['member'] = $member;
        return view('read',$data);
    }

    // 删除订单
    public function delete(){
        //验证用户权限
        Common::checkpower(25);
        //要删除的订单评论id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_order')->where(array('order_id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 订单');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改订单备注
    public function remark(){
        //验证用户权限
        Common::checkpower(25);
        //获取订单id
        $order_id = input('post.id','0');
        $uid = input('post.uid','0');
        //获取订单信息
        $order = get_order($order_id,$uid);
        if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
        //获取订单备注信息
        $remark = input('post.remark','');
        //修改订单信息
        if(\think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('remark',$remark)){
            //修改数据后清除缓存
            \think\Cache::rm($uid.'_order_'.$order_id);
            //记录系统日志
            admin_log('修改 '.$order_id.' 订单备注信息');
            return json(array('success'=>true,'info'=>'修改成功'));
        }else {
            //获取修改错误原因
            return json(array('success'=>false,'info'=>'修改失败'));
        }
    }

    //支付订单
    public function payment(){
        //验证用户权限
        Common::checkpower(25);
         //获取订单id
        $order_id = input('post.id','0');
        $uid = input('post.uid','0');
        //获取订单信息
        $order = get_order($order_id,$uid);
        if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
        // 验证订单是否已支付
        if($order['is_state'] != 1){return json(array('success'=>false,'info'=>'当前状态无法改变！'));} 
        //订单分佣
        $goods = commision_order($order);
        //更新订单商品支付
        $data = array();
        $data['is_state'] = 2;
        $data['paytype'] = '后台支付';
        $data['paytime'] = time();
        $data['goods'] = json_encode($goods,JSON_UNESCAPED_UNICODE);
        \think\Db::name('member_order')->where(array('order_id'=>$order_id))->update($data);
        //修改数据后清除缓存
        \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
        //写入日志
        admin_log('支付 '.$order_id.' 订单');
        return json(array('success'=>true,'info'=>'订单支付成功'));
    }

    //取消订单
    public function cancel(){
        //验证用户权限
        Common::checkpower(25);
        //获取订单id
        $order_id = input('post.id','0');
        $uid = input('post.uid','0');
        //获取订单信息
        $order = get_order($order_id,$uid);
        if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
        // 验证订单是否可改变状态
        if($order['is_state'] != 1){return json(array('success'=>false,'info'=>'当前状态无法改变！'));} 
        //取消订单
        if(\think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('is_state',6)){
            //使用优惠卷
            if($order['coupon']){
                \think\Db::name('coupon_list')->where(array('id'=>$order['coupon'],'uid'=>$id))->setField('is_state',1);
            }
            //使用积分抵扣
            if($order['point']){
                \think\Db::name('member')->where(array('id'=>$id))->setInc('point',$order['point']);
                //写入日志
                member_point_log("订单取消",$id,"取消".$order['order_id'].'订单返回',$order['point']);
            }
            $goods = json_decode($order['goods'],true);
            foreach ($goods as $key => $value) {
                //返回增加商品库存
                \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setInc('goods_number', $value['goods_number']);
                //修改后清除该商品缓存
                \think\Cache::rm('goods_'.$value['goods_id']);
                //修改规格库存
                if($value['product_id']){
                    \think\Db::name('goods_product')->where(array('id'=>$value['product_id']))->setInc('goods_number', $value['goods_number']);
                    \think\Cache::rm('goods_product_'.$value['goods_id']);
                }
                //商品状态
                $value['is_state'] = 6;
                $goods[$key] = $value;
            }
            //修改订单商品状态
            $goods = json_encode($goods,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('goods',$goods);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            //写入日志
            admin_log('取消 '.$order_id.' 订单');
            return json(array('success'=>true,'info'=>'订单取消成功'));
        }else{
            return json(array('success'=>false,'info'=>'订单取消失败'));
        }
    }

    //完成订单
    public function finish(){
        //验证用户权限
        Common::checkpower(25);
        //获取订单id
        $order_id = input('post.id','0');
        $uid = input('post.uid','0');
        //获取订单信息
        $order = get_order($order_id,$uid);
        if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
        // 验证订单是否可改变状态
        if(in_array($order['is_state'],array('6','8','9'))){return json(array('success'=>false,'info'=>'当前状态无法改变！'));} 
        //完成订单
        if(\think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('is_state',9)){
            $goods = json_decode($order['goods'],true);
            foreach ($goods as $key => $value) {
                //商品状态
                if($value['is_state'] != 5){
                    $value['is_state'] = 9;
                }
                $goods[$key] = $value;
            }
            //修改订单商品状态
            $goods = json_encode($goods,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('goods',$goods);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            //写入日志
            admin_log('归档完成 '.$order_id.' 订单');
            return json(array('success'=>true,'info'=>'归档完成'));
        }else{
            return json(array('success'=>false,'info'=>'归档失败'));
        }
    }

    //订单发货操作
    public function shipping() {
        //验证用户权限
        Common::checkpower(25);
        //判断发货操作还是显示发货页面
        if (request()->isPost()) {
            //发货处理
            $items = input('post.items/a','');
            if (!$items){return json(array('success'=>false,'info'=>'该订单暂无需要发货的商品！'));exit;}
            //获取查看订单id
            $order_id = input('post.order_id', '');
            $uid = input('post.uid', '');
            //获取订单信息
            $order = get_order($order_id,$uid);
            if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
            //判断订单是否可以进行发货操作
            if ($order['is_state'] != 2) {return json(array('success'=>false,'info'=>'该订单不能进行发货操作！'));exit;}
            //物流单号
            $logistino = input('post.logistino', '');
            if (!$logistino){return json(array('success'=>false,'info'=>'请填写物流单号！'));exit;} 
            //订单商品
            $goods_list = json_decode($order['goods'],true);
            if (!$goods_list){return json(array('success'=>false,'info'=>'该订单中没有包含任何商品，发货失败！'));exit;}
            //记录日志
            $item_data = array();
            //商品总数
            $itemnum = 0; 
            foreach ($items as $val) {
                $itemnum = $itemnum + $val['sendnum'];
                foreach ($goods_list as $key => $value) {
                    $value['product_id'] = isset($value['product_id']) ? $value['product_id'] : 0;
                    $value['sendnum'] = isset($value['sendnum']) ? $value['sendnum'] : 0;
                    if ($val['key'] == ($key + 1)) {
                        //判断该商品是否已经发货完毕
                        if ($value['goods_number'] > $value['sendnum'] && $val['sendnum'] <= ($value['goods_number'] - $value['sendnum'])) {
                            $item = array(
                                'id' => randChar(),
                                'uid' => $uid,
                                'order_id' => $order_id,
                                'goods_id' => $value['goods_id'],
                                'bus_id' => $value['bus_id'],
                                'product_id' => $value['product_id'],
                                'goods_name' => $value['spec_value'] ? $value['goods_name'].'['.$value['spec_value'].']' : $value['goods_name'],
                                'number' => $val['sendnum'],
                                'logistino' => $logistino,
                                'remark' => input('post.remark',''),
                                'address' => input('post.address',''),
                                'city' => input('post.city',''),
                                'freight_price' => input('post.freight_price',''),
                                'username' => input('post.username',''),
                                'mobile' => input('post.mobile',''),
                                'logistics' => input('post.logistics',''),
                                'create_time' => time()
                            );
                            $item_data[] = $item;
                            $value['is_state'] = 3;
                            $value['last_send'] = $val['sendnum'];
                            $value['sendnum'] = $value['sendnum'] + $val['sendnum'];
                        }
                    }
                    $goods_list[$key] = $value;
                }
            }
            if ($itemnum > $order['total_number']) {
                return json(array('success'=>false,'info'=>'发货数量超过订单商品总数量，请确认填写正确！'));exit;
            }
            //添加发货商品记录
            if (\think\Db::name('member_shipping')->insertAll($item_data)) {
                //已发货商品总数
                $sendnum = 0; 
                foreach ($goods_list as $key => $value) {
                    $sendnum = $sendnum + $value['sendnum'];
                    if($value['spec_value']){
                        $value['goods_name'] = $value['goods_name'].'['.$value['spec_value'].']';
                    }
                    $value['last_send'] = isset($value['last_send']) ? $value['last_send'] : 0;
                    //写入日志
                    admin_log('发货 '.$order_id.' 订单 '.$value['goods_name'].'x'.$value['last_send']);
                }
                //更新订单发货数量
                $goods_list = json_encode($goods_list,JSON_UNESCAPED_UNICODE);
                \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('goods',$goods_list);
                //判断是否已经发完货
                if ($sendnum == $order['total_number']) {
                    \think\Db::name('member_order')->where(array('order_id'=>$order_id))->update(array('is_state'=>3,'send_time'=>time()));
                }
                //修改数据后清除缓存
                \think\Cache::rm($uid.'_order_'.$order_id);
                \think\Cache::rm('member_shipping_'.$order_id);
                return json(array('success'=>true,'info'=>'发货成功！'));exit;
            }else {
                return json(array('success'=>false,'info'=>'发货失败！'));exit;
            }
        }else {
            //获取查看订单id
            $order_id = input('get.id', '0');
            $uid = input('get.uid', '0');
            //获取订单信息
            $order = get_order($order_id,$uid);
            if (!$order){return error('不存在的订单信息！');exit;} 
            //订单状态
            $order['state'] = $this->get_state($order['is_state']);
            $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
            
            //订单商品
            $order['goods'] = json_decode($order['goods'],true);
            foreach ($order['goods'] as $key => $value) {
                //商品状态
                $value['is_state'] = isset($value['is_state']) ? $value['is_state'] : '1';
                $value['state'] = $this->get_state($value['is_state']);
                //已发货数量
                $value['sendnum'] = isset($value['sendnum']) ? $value['sendnum'] : '0';
                //商品供应商信息
                if($value['bus_id']){
                    $business = get_business($value['bus_id']);
                    $value['busname'] = $business['name'];
                }else{
                    $value['busname'] = '自营商品';
                }
                $order['goods'][$key] = $value;
            }
            return view('shipping',array('order'=>$order));
        }
    } 

    //订单退货操作
    public function shipping_return() {
        //验证用户权限
        Common::checkpower(26);
        //判断退货操作还是显示退货页面
        if (request()->isPost()) {
            //退货处理
            $items = input('post.items/a','');
            if (!$items){return json(array('success'=>false,'info'=>'该订单暂无需要退货的商品！'));exit;}
            //获取查看订单id
            $order_id = input('post.order_id', '');
            $uid = input('post.uid', '');
            //获取订单信息
            $order = get_order($order_id,$uid);
            if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
            //获取订单发货记录
            $shipping = get_member_shipping($order_id);
            //记录日志
            $item_data = array();
            //已退货商品总数
            $sendnum = 0; 
            foreach ($items as $val) {
                foreach ($shipping as $key => $value) {
                    if ($val['ship_id'] == $value['id']) {
                        //判断该商品是否已经退货完毕
                        if ($val['sendnum'] <= ($value['number'] - $value['returnnum'])) {
                            $sendnum += $value['returnnum'] + $val['sendnum'];
                            $item = array(
                                'id' => $value['id'],
                                'goods_id' => $value['goods_id'],
                                'goods_name' => $value['goods_name'],
                                'product_id' => $value['product_id'],
                                'returnnum' => $value['returnnum'] + $val['sendnum'],
                                'return_time' => time(),
                                'last_return'=>$val['sendnum']
                            );
                            $item_data[] = $item;
                        }else{
                             return json(array('success'=>false,'info'=>'退货数量输入有误！'));exit;
                        }
                    }else{
                        $sendnum += $value['returnnum'];
                    }
                }
            }
            //判断是否已经退完货
            if ($sendnum == $order['total_number']) {
                \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('is_state',5);
            }
            //订单商品
            $goods_list = json_decode($order['goods'],true);
            //添加退货商品记录
            foreach ($item_data as $key => $value) {
                if (\think\Db::name('member_shipping')->where(array('id'=>$value['id']))->update($value)) {
                    foreach ($goods_list as $k => $val) {
                        if($value['goods_id'] == $val['goods_id'] && $value['product_id'] == $val['product_id']){
                            $val['is_state'] = 5;
                        }
                        $goods_list[$k] = $val;
                    }
                    //写入日志
                    admin_log('退货 '.$order_id.' 订单 '.$value['goods_name'].'x'.$value['last_return']);
                }
            }
            //更新订单发货数量
            $goods_list = json_encode($goods_list,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('goods',$goods_list);
            //修改数据后清除缓存
            \think\Cache::rm($uid.'_order_'.$order_id);
            \think\Cache::rm('member_shipping_'.$order_id);
            return json(array('success'=>true,'info'=>'退货成功！'));exit;
        }else {
            //获取查看订单id
            $order_id = input('get.order_id', '');
            //获取订单发货记录
            $shipping = get_member_shipping($order_id);
            if (!$shipping){return error('不存在的订单发货信息！');exit;} 
            return view('shipping_return',array('item_list'=>$shipping,'order_id'=>$order_id));
        }
    }

    //订单退款
    public function refund(){
        //验证用户权限
        Common::checkpower(25);
        //判断退款操作还是显示退款页面
        if (request()->isPost()) {
            //获取查看订单id
            $order_id = input('post.order_id', '');
            $uid = input('post.uid', '');
            //获取订单信息
            $order = get_order($order_id,$uid);
            if (!$order){return json(array('success'=>false,'info'=>'不存在的订单信息！'));exit;} 
            //退款金额
            $money = input('post.money', '');
            if (!$money){return json(array('success'=>false,'info'=>'请填写正确的退款金额！'));exit;} 
            //退款账号
            $account = input('post.account', '');
            if (!$account){return json(array('success'=>false,'info'=>'请填写正确的退款账号！'));exit;} 
            //退款备注
            $remark = input('post.remark', '');
            $remark = $remark ? $remark : '订单退款';

            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');

            $aop = new \AopClient ();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $alipay['appid'];
            $aop->rsaPrivateKey = $alipay['privatekey'];
            $aop->alipayrsaPublicKey = $alipay['publickey'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset = 'UTF-8';
            $aop->format = 'json';
            $request = new \AlipayFundTransToaccountTransferRequest ();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $param = array();
            $param['out_biz_no'] = randChar();
            $param['payee_type'] = 'ALIPAY_LOGONID';
            $param['payee_account'] = $account;
            $param['amount'] = $money;
            $param['payer_show_name'] = '广州市乐叮当网络科技有限公司';
            $param['remark'] = $remark;

            $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
            $request->setBizContent($bizcontent);
            $result = $aop->execute ( $request); 
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if(empty($resultCode) || $resultCode != 10000){
                return json(array('success'=>false,'info'=>$result->$responseNode->sub_msg));
            }
            //退返积分
            $point = input('post.point', '');
            if($point){
                \think\Db::name('member')->where(array('id'=>$uid))->setInc('point',$point);
                member_point_log("订单退款",$uid,$order_id."订单退款返还",$point,$order_id);
            }
            //订单退款
            $data = array();
            $data['is_cancel'] = 2;
            $data['refund_time'] = time();
            $data['refund_name'] = '支付宝';
            $data['refund_price'] = $money;
            $data['refund_remark'] = $remark;
            $data['refund_id'] = $result->$responseNode->order_id;
            \think\Db::name('member_order')->where(array('order_id'=>$order_id))->update($data);
             //修改数据后清除缓存
            \think\Cache::rm($uid.'_order_'.$order_id);
            //记录系统日志
            admin_log($order_id." 订单退款 ".$money." 元");
            return json(array('success'=>true,'info'=>'订单已退款！'));
            
        }else{
            //获取订单id
            $order_id = input('get.id','0');
            $uid = input('get.uid','0');
            //获取订单信息
            $order = get_order($order_id,$uid);
            if (!$order){return error('不存在的订单信息！');exit;} 
            //判断订单是否可以进行退款操作
            if ($order['is_cancel'] != 1 || in_array($order['is_state'],array('6','8','9'))) {
                return error('该订单不能进行退款操作！');exit;
            }
            //获取用户绑定支付宝
            $alipay = \think\Db::name('member')->where(array('id'=>$order['uid']))->value('alipay');
            //订单状态
            $order['state'] = $this->get_state($order['is_state']);
            $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
            return view('refund',array('order'=>$order,'alipay'=>$alipay));
        }
    }

    //导出excel订单
    public function excel(){
        //验证用户权限
        Common::checkpower(25);
        $where = array();
        $idlist = input('idlist');
        if($idlist){
            $where = array('o.order_id'=>array('IN',$idlist));
        }
        //查询符合条件数据
        $list = \think\Db::name('member_order')->alias('o')
                                        ->field('o.*,m.nickname')
                                        ->join('__MEMBER__ m','o.uid = m.id')
                                        ->where($where)
                                        ->select();
        if(!$list){return error('没有订单数据！');exit;}
        $filename = date('Ymdhis');
        header("Content-type:application/vnd.ms-excel"); 
        header("Content-Disposition:filename=".$filename.".xls");

        $table = "";
        $table .= "<table border='1' style='font-size:16px;'>";
        $table .= '<thead style="border-top: 1px;">';
        $table .= '<th colspan="9" style="text-align: center;border-top: 1px;">订单列表</th>';
        $table .= '</thead>';
        $table .= '<tr>';
        $table .= '<td>订单号</td>';
        $table .= '<td>下单会员</td>';
        $table .= '<td>下单时间</td>';
        $table .= '<td>订单总额</td>';
        $table .= '<td>收货人</td>';
        $table .= '<td>收货人电话</td>';
        $table .= '<td>支付方式</td>';
        $table .= '<td>支付金额</td>';
        $table .= '<td>订单状态</td>';
        $table .= '</tr>';
        foreach ($list as $value) {
            //订单状态
            $value['state'] = $this->get_state($value['is_state']);

            $table .= '<tr>';
            $table .= '<td style="vnd.ms-excel.numberformat:@">'.$value['order_id'].'</td>';
            $table .= '<td>'.$value['nickname'].'</td>';
            $table .= '<td>'.date('Y-m-d H:i', $value['create_time']).'</td>';
            $table .= '<td>'.$value['total_price'].'</td>';
            $table .= '<td>'.$value['username'].'</td>';
            $table .= '<td>'.$value['mobile'].'</td>';
            $table .= '<td>'.$value['paytype'].'</td>';
            $table .= '<td>'.$value['payprice'].'</td>';
            $table .= '<td>'.$value['state'].'</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';
        echo $table;
        exit();
    }

    //退货订单
    public function retreat(){
        //验证用户权限
        Common::checkpower(26);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //订单状态
        $where['o.is_state'] = array('IN', "4,5");
        $is_state = input('get.is_state','');     
        if($is_state){
            $where['o.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //筛选字段       
        $field = input('get.field','order_id');  
        $pageParam['query']['field'] = $field;
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['o.'.$field] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        $start_time = input('get.start_time','');
        $pageParam['query']['start_time'] = $start_time;     
        $end_time = input('get.end_time','');
        $pageParam['query']['end_time'] = $end_time;  
        if (!empty($start_time) && !empty($end_time)) {
            $where['o.create_time'] = array(array('EGT', strtotime($start_time)), array('ELT', strtotime($end_time)+(60*60*24)), 'AND');
        }else if (!empty($start_time)) {
            $where['o.create_time'] = array('EGT', strtotime($start_time));
        }else if (!empty($end_time)) {
            $where['o.create_time'] = array('ELT', strtotime($end_time)+(60*60*24));
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_order')->alias('o')
                                               ->field('o.*,m.nickname')
                                               ->join('__MEMBER__ m','o.uid = m.id')
                                               ->where($where)
                                               ->order('o.create_time desc')
                                               ->paginate(24,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['return_time'] = date('Y-m-d H:i:s',$value['return_time']);
            //订单状态
            $value['state'] = $this->get_state($value['is_state']);
            //退单图片数据
            $value['return_image'] = json_decode($value['return_image'],true);
            $data[$key] = $value;
        }
        //统计订单数量
        $count = \think\Db::name('member_order')->group('is_state')->column('count(order_id)','is_state');
        $count[0] = 0;
        for ($i=1; $i < 10 ; $i++) { 
            $count[$i] = isset($count[$i]) ? $count[$i] : 0;
            $count[0] = $count[0] + $count[$i];
        }
        //赋值数据集View模板输出  
        $array = array();
        $array['field'] = $field;
        $array['keyword'] = $keyword;
        $array['list'] = $list;
        $array['data'] = $data;
        $array['count'] = $count;
        $array['is_state'] = $is_state;
        $array['start_time'] = $start_time;
        $array['end_time'] = $end_time;
        return view('retreat',$array);
    }

    //获取商品购买状态
    public function get_state($state){
        switch ($state) {
            case 'count': $state = '全部'; break;
            case 'today': $state = '今天'; break;
            case '1': $state = '待付款'; break;
            case '2': $state = '待发货'; break;
            case '3': $state = '已发货'; break;
            case '4': $state = '退货中'; break;
            case '5': $state = '已退货'; break;
            case '6': $state = '<font color="#ccc">已取消</font>'; break;
            case '7': $state = '待评论'; break;
            case '8': $state = '<font color="#ccc">已过期</font>'; break;
            case '9': $state = '已完成'; break;
            default:  $state = '<font color="red">待处理</font>'; break;
        }
        return $state;
    }

    // 订单评论
    public function comment(){
        //验证用户权限
        Common::checkpower(35);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['c.remark|m.username|g.goods_name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_discuss')->alias('c')
                                                 ->field('c.*,m.username,g.goods_name')
                                                 ->join('__MEMBER__ m','c.uid = m.id')
                                                 ->join('__GOODS__ g','c.goods_id = g.goods_id','LEFT')
                                                 ->where($where)
                                                 ->paginate(24,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $val) {
            // 格式化时间
            $val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
            $data[$key] = $val;
        }
        //赋值数据集View模板输出  
        return view('comment',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    //修改订单评论状态
    public function comment_state() {
        //验证用户权限
        Common::checkpower(35);
        //获取修改订单评论id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('member_discuss')->where(array('id'=>$id))->setField('is_state', $state)) {
            //记录系统日志
            admin_log("修改 ID:".$id." 订单评论状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除订单评论
    public function comment_del(){
        //验证用户权限
        Common::checkpower(35);
        //要删除的订单评论id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_discuss')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 订单评论');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

}
