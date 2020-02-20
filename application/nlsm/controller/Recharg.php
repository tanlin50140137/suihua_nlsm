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

class Recharg extends Common
{
    // 显示商品列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(33);
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
        $list = \think\Db::name('recharg')->field('id')->where($where)->order('sort')->paginate(20,false,$pageParam);
        
        //遍历拼接信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_recharg($value['id']);
            // 格式化时间
            $val['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加商品
    public function add(){
        //验证用户权限
        Common::checkpower(33);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'商品名称请在5-30个字符以内！'));exit;
            }
            //创建时间         
            $data['create_time'] = time();
            //添加商品
            if (\think\Db::name('recharg')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['name'].' 话费充值商品');
                //清除Api获取话费充值相关接口缓存
                \think\Cache::clear('recharg');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            return view('add');
        }
    }
    
    // 修改商品
    public function edit(){
        //验证用户权限
        Common::checkpower(33);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','0');
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'商品名称请在5-30个字符以内！'));exit;
            }
            //添加商品
            if (\think\Db::name('recharg')->where(array('id'=>$id))->update($data)) {
                //修改后清除该套餐缓存
                \think\Cache::rm('recharg_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 话费充值商品信息');
                //清除Api获取话费充值相关接口缓存
                \think\Cache::clear('recharg');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            $id = input('get.id','0');
            $list = get_recharg($id);
            return view('edit',array('list'=>$list));
        }
    }

    //修改商品状态
    public function state() {
        //验证用户权限
        Common::checkpower(33);
        //获取修改商品id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('recharg')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改后清除该套餐缓存
            \think\Cache::rm('recharg_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 话费充值商品状态");
            //清除Api获取话费充值相关接口缓存
            \think\Cache::clear('recharg');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除商品
    public function delete(){
        //验证用户权限
        Common::checkpower(18);
        //要删除的商品id
        $idlist = input('post.idlist','0');
        //删除商品
        if(\think\Db::name('recharg')->where(array('id' => array('IN', $idlist)))->delete()){
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 话费充值商品');
            //清除Api获取话费充值相关接口缓存
            \think\Cache::clear('recharg');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 充值订单
    public function order(){
        //验证用户权限
        Common::checkpower(37);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //充值套餐     
        $goods_id = input('get.goods_id','0'); 
        if($goods_id){
            $where['o.goods_id'] = $goods_id;
            $pageParam['query']['goods_id'] = $goods_id;
        }
        //支付状态
        $is_state = input('get.is_state','');     
        if($is_state){
            $where['o.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['o.order_id|o.mobile'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }

        //拼接排序信息
        $order = input('get.order','desc');  
        $sort = input('get.sort','create_time');  

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('recharg_order')->alias('o')
                                                ->field('o.*,m.nickname,r.name')
                                                ->join('__MEMBER__ m','o.uid = m.id')
                                                ->join('__RECHARG__ r','o.goods_id = r.id')
                                                ->where($where)
                                                ->order("o.{$sort} {$order}")
                                                ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['return_time'] = $value['return_time'] ? date('Y-m-d H:i:s',$value['return_time']) : '';
            $data[$key] = $value;
        }
        //订单总数
        $count = 0;
        //查询话费套餐
        $recharg = \think\Db::name('recharg')->field('id,name')->where(array('is_state'=>1))->order('sort')->select();
        if($recharg){
            foreach ($recharg as $key => $value) {
                $value['count'] = \think\Db::name('recharg_order')->alias('o')->where(array_merge($where,array('o.goods_id'=>$value['id'])))->count();
                $count = $count + $value['count'];
                $recharg[$key] = $value;
            }
        }
        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['list'] = $list;
        $param['data'] = $data;
        $param['sort'] = $sort;
        $param['count'] = $count;
        $param['order'] = $order == 'asc' ? 'desc' :'asc';
        $param['is_state'] = $is_state;
        $param['recharg'] = $recharg;
        $param['goods_id'] = $goods_id;
        return view('order',$param); 
    }

    //修改充值订单支付状态
    public function order_state() {
        //验证用户权限
        Common::checkpower(37);
        //获取修改订单id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('recharg_order')->where(array('order_id'=>$id))->setField('is_state', $state)) {
            if($state == 2){
                //请求话费充值返利
                https_request('http://'.$_SERVER['HTTP_HOST'].'/api/index/everyday/');
            }
            //记录系统日志
            admin_log("修改 ID:".$id." 充值订单支付状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除充值订单
    public function order_del(){
        //验证用户权限
        Common::checkpower(37);
        //要删除的订单id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('recharg_order')->where(array('order_id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 充值订单');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //获取话费充值记录
    public function recharg_log(){
        //拼接条件
        $where = array();
        //订单号
        $order_id = input('get.id','0');
        $where['order_id'] = $order_id;
        //查询满足要求的数据
        $list = \think\Db::name('recharg_log')->field(true)->where($where)->select();
        if($list){
            foreach ($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                $list[$key] = $value;
            }
        }
        //赋值数据集View模板输出  
        return view('recharg_log',array('list'=>$list));
    }

    //手动充值话费
    public function recharg(){
         if (request()->isPost()) {
            //接收输入数据
            $data = array();
            $data['uid'] = session('admin.id');
            $data['create_time'] = time();
            $data['mobile'] = input('post.mobile','');
            $data['money']= input('post.money','');
            $data['remark'] = input('post.remark','');
            $data['order_id'] = input('post.order_id','');
            $data['out_trade_no'] = randChar(); //商家自定的订单号
            if(\think\Db::name('recharg_order')->where(array('order_id'=>$data['order_id']))->count() < 1){
                return json(array('success'=>false,'info'=>'订单号不存在！'));
            }
            //手机号验证
            if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $data['mobile'])){
                return json(array('success'=>false,'info'=>'手机号格式有误'));exit;
            }
            //获取通知设置信息
            $config = get_config('notice');
            //----------------------------------
            // 聚合数据-手机话费充值API调用示例代码
            //----------------------------------
            header('Content-type:text/html;charset=utf-8');
            require_once "./extend/recharge.php"; //引入文件

            //接口基本信息配置
            $appkey = $config['mappkey']; //从聚合申请的话费充值appkey
            $openid = $config['openid']; //注册聚合账号就会分配的openid，在个人中心可以查看
            $recharge = new \recharge($appkey,$openid);
            //提交话费充值
            // $telRechargeRes = $recharge->telcz($data['mobile'],intval($data['money']),$data['out_trade_no']); #可以选择的面额5、10、20、30、50、100、300
            $telRechargeRes = array();
            $telRechargeRes['error_code'] = 0;
            if($telRechargeRes['error_code'] =='0'){
                \think\Db::name('recharg_record')->insert($data);
                //记录系统日志
                admin_log('补充 ID:'.$data['order_id'].' 话费充值订单');
                return json(array('success'=>true,'info'=>'充值成功！'));
            }else{
                return json(array('success'=>false,'info'=>$telRechargeRes['reason']));
            }
        }else{
            return view('recharg');
        }
    }

    // 充值补充订单
    public function record(){
        //验证用户权限
        Common::checkpower(45);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['order_id|mobile|out_trade_no'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('recharg_record')->field(true)->where($where)->order("id desc")->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        
        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['list'] = $list;
        $param['data'] = $data;
        return view('record',$param); 
    }

    // 删除手动充值订单
    public function record_del(){
        //验证用户权限
        Common::checkpower(45);
        //要删除的订单id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('recharg_record')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 手动充值订单');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}
