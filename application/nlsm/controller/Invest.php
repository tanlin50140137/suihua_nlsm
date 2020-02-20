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

class Invest extends Common
{
    // 投资项目列表
    public function index(){
        //验证用户权限
        Common::checkpower(41);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];

        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['id|name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('invest')->field('id')->where($where)->order("sort asc")->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_invest($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['start_time'] = date('Y-m-d', $value['start_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            $data[$key] = $value;
        }
        
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加投资项目
    public function add(){
        //验证用户权限
        Common::checkpower(41);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'项目名称请在5-30个字符以内！'));exit;
            }
            //项目描述
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);
            //项目轮播图
            $data['image'] = array();
            //上传文件
            $file = isset($_FILES['file']) ? $_FILES['file'] : '';
            if ($file) {
                //上传文件限制格式
                $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                foreach ($file['name'] as $key => $value) {
                    if($file['error'][$key] == 0 && in_array($file['type'][$key],$type)){
                        //保存文件
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(move_uploaded_file($file["tmp_name"][$key], '.'.$path)){  
                            // 按照原图的比例生成一个最大为200*200的缩略图并保存
                            $image = \think\Image::open('.'.$path);
                            $image->thumb(500, 500)->save('.'.$path);
                            // 上传成功 获取上传文件信息
                            array_unshift($data['image'],$path);
                        }
                    }
                }
                
            }
            if(!$data['image']){return json(array('success'=>false,'info'=>'请选择上传项目图片！'));exit;}
            $data['logo'] = $data['image'][0];
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);

            $data['start_time'] = strtotime(input('post.start_time',''));
            $data['end_time'] = strtotime(input('post.end_time',''));
            //创建时间         
            $data['create_time'] = time();

            if (\think\Db::name('invest')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['name'].' 投资项目');
                //清除Api获取投资项目相关接口缓存
                \think\Cache::clear('invest');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            return view('add');
        }
    }
    
    // 修改投资项目
    public function edit(){
        //验证用户权限
        Common::checkpower(41);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','0');
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'项目名称请在5-30个字符以内！'));exit;
            }
            //项目描述
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);
            //项目轮播图
            $data['image'] = input('post.image','');
            $data['image'] = $data['image'] ? explode(',', $data['image']) : array();

            //上传文件
            $file = isset($_FILES['file']) ? $_FILES['file'] : '';
            if ($file) {
                //上传文件限制格式
                $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                foreach ($file['name'] as $key => $value) {
                    if($file['error'][$key] == 0 && in_array($file['type'][$key],$type)){
                        //保存文件
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(move_uploaded_file($file["tmp_name"][$key], '.'.$path)){  
                            // 按照原图的比例生成一个最大为200*200的缩略图并保存
                            $image = \think\Image::open('.'.$path);
                            $image->thumb(500, 500)->save('.'.$path);
                            // 上传成功 获取上传文件信息
                            array_unshift($data['image'],$path);
                        }
                    }
                }
                
            }
            if(!$data['image']){return json(array('success'=>false,'info'=>'请选择上传项目图片！'));exit;}
            $data['logo'] = $data['image'][0];
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);

            $data['start_time'] = strtotime(input('post.start_time',''));
            $data['end_time'] = strtotime(input('post.end_time',''));
            if (\think\Db::name('invest')->where(array('id'=>$id))->update($data)) {
                //修改后清除该套餐缓存
                \think\Cache::rm('invest_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 投资项目信息');
                //清除Api获取投资项目相关接口缓存
                \think\Cache::clear('invest');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            $id = input('get.id','0');
            $list = get_invest($id);
            if (!$list){return error('投资项目不存在！');exit;} 
            $list['start_time'] = date('Y-m-d', $list['start_time']);
            $list['end_time'] = date('Y-m-d', $list['end_time']);
            return view('edit',array('list'=>$list));
        }
    }

    // 修改投资项目显示
    public function state() {
        //验证用户权限
        Common::checkpower(41);
        //获取修改管理员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('invest')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改后清除该套餐缓存
            \think\Cache::rm('invest_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 投资项目状态");
            //清除Api获取投资项目相关接口缓存
            \think\Cache::clear('invest');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //删除投资项目
    public function delete(){
        //验证用户权限
        Common::checkpower(41);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除投资项目
        if (\think\Db::name('invest')->where(array('id'=>array('IN',$idlist)))->delete()) {
            $list = explode(",",$idlist);
            foreach ($list as $key => $value) {
                //修改数据后清除缓存
                \think\Cache::rm('invest_'.$value);
            }
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 投资项目');
            //清除Api获取投资项目相关接口缓存
            \think\Cache::clear('invest');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 投资项目订单
    public function order(){
        //验证用户权限
        Common::checkpower(42);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //投资项目     
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
            $where['o.order_id'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('invest_order')->alias('o')
                                                ->field('o.*,m.nickname,i.name')
                                                ->join('__MEMBER__ m','o.uid = m.id')
                                                ->join('__INVEST__ i','o.goods_id = i.id','LEFT')
                                                ->where($where)
                                                ->order('o.create_time desc')
                                                ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        //订单总数
        $count[1] = \think\Db::name('invest_order')->alias('o')->where(array_merge($where,array('o.is_state'=>1)))->count();
        $count[2] = \think\Db::name('invest_order')->alias('o')->where(array_merge($where,array('o.is_state'=>2)))->count();
        $count[0] = $count[1] + $count[2];
        
        //查询投资项目
        $invest = \think\Db::name('invest')->field('id,name')->where(array('is_state'=>1))->order('sort')->select();

        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['list'] = $list;
        $param['data'] = $data;
        $param['is_state'] = $is_state;
        $param['count'] = $count;
        $param['invest'] = $invest;
        $param['goods_id'] = $goods_id;
        return view('order',$param); 
    }

    //修改投资项目订单支付状态
    public function order_state() {
        //验证用户权限
        Common::checkpower(42);
        //获取修改订单评论id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('invest_order')->where(array('order_id'=>$id))->setField('is_state', $state)) {
            //记录系统日志
            admin_log("修改 ID:".$id." 投资项目订单支付状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除投资项目订单
    public function order_del(){
        //验证用户权限
        Common::checkpower(37);
        //要删除的投资项目id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('invest_order')->where(array('order_id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 投资项目订单');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}
