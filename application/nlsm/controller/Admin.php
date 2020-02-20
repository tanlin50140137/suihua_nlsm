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

class Admin extends Common
{
    // 显示管理员列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(6);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['username|nickname'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('admin')->field('id')->where($where)->order('id')->paginate(20,false,$pageParam);
        
        //遍历拼接管理员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_admin($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    //修改管理员状态
    public function state() {
        //验证用户权限
        Common::checkpower(6);
        //获取修改管理员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        //不能禁用admin管理员
        if($id == 1){return json(array('success'=>false,'info'=>'系统管理员不能禁用！'));exit;}
        if (\think\Db::name('admin')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('admin_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 管理员状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 修改管理员信息
    public function edit(){
        //验证用户权限
        Common::checkpower(6);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','0');
            $data = input('post.','');
            if($id == 1){unset($data['is_state']);}
            //加密用户密码
            $data['password'] = input('post.password','');
            if($data['password']){
                //用户密码加密
                $data['password'] = password(md5($data['password']));     
                //记录系统日志
                admin_log('修改 ID:'.$id.' 管理员密码');
            }else{
                unset($data['password']);
            }
            //上传文件
            $file = isset($_FILES['userface']) ? $_FILES['userface'] : '';
            if ($file && $file['error'] == 0) {
                //上传文件限制格式
                $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
                if (!in_array($file['type'],$type)) {return json(array('success'=>false,'info'=>'文件上传格式错误！'));exit;}
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                //保存文件
                $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){  
                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                    $image = \think\Image::open('.'.$path);
                    $image->thumb(400, 400)->save('.'.$path);
                    // 上传成功 获取上传文件信息
                    $data['userface'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'上传文件不存在！'));exit;
                }
            }
            //管理员权限
            $data['power'] = input('post.power','');
            if(!explode(',', $data['power'])){return json(array('success'=>false,'info'=>'请选择赋予管理员权限！'));exit;}
            //修改管理员
            if (\think\Db::name('admin')->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('admin_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 管理员信息');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            //获取普通会员id
            $id = input('get.id','0');
            //获取普通会员信息
            $list = get_admin($id);
            $list['create_time'] = date('Y-m-d H:i:s',$list['create_time']);
            $list['power'] = explode(',', $list['power']);
            //获取所有权限信息
            $power = get_power();
            //获取状态正常权限信息
            $param = array();
            $powerlist = array();
            foreach ($power as $value) {
                if($value['is_state'] == 1 && $value['pid'] == 1){
                    $powerlist[] = $value;
                }
                array_push($param, $value['id']);
            }
            foreach ($powerlist as $key => $val) {
                $val['list'] = array();
                foreach ($power as $value) {
                    if($value['is_state'] == 1 && $value['pid'] == $val['id']){
                        $val['list'][] = $value;
                    }
                }
                $powerlist[$key] = $val;
            }
            //超级管理员拥有所有权限
            if($id == 1){$list['power'] = $param;}
            //赋值数据集View模板输出  
            return view('edit',array('list'=>$list,'powerlist'=>$powerlist));
        }
    }

    // 添加管理员
    public function add(){
        //验证用户权限
        Common::checkpower(6);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            //用户账号过滤
            $data['username'] = input('post.username','');
            if(strlen($data['username']) < 5 || strlen($data['username']) > 30){
                return json(array('success'=>false,'info'=>'用户名称请在5-30个字符以内！'));exit;
            }
            if(\think\Db::name('admin')->where(array('username'=>$data['username']))->count() > 0){
                return json(array('success'=>false,'info'=>'用户账号已存在，请更换！'));exit;
            }
            //加密用户密码
            $data['password'] = input('post.password','');
            if(strlen($data['password'])<6 || strlen($data['password'])>18){return json(array('success'=>false,'info'=>'请输入6-18位数之间会员密码！'));exit;}
            //用户密码加密
            $data['password'] = password(md5($data['password']));     
            //上传文件
            $file = isset($_FILES['userface']) ? $_FILES['userface'] : '';
            if ($file && $file['error'] == 0) {
                //上传文件限制格式
                $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
                if (!in_array($file['type'],$type)) {return json(array('success'=>false,'info'=>'文件上传格式错误！'));exit;}
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                //保存文件
                $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){  
                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                    $image = \think\Image::open('.'.$path);
                    $image->thumb(400, 400)->save('.'.$path);
                    // 上传成功 获取上传文件信息
                    $data['userface'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'上传文件不存在！'));exit;
                }
            }
            //管理员权限
            $data['power'] = input('post.power','');
            if(!explode(',', $data['power'])){return json(array('success'=>false,'info'=>'请选择赋予管理员权限！'));exit;}
            //用户创建时间         
            $data['create_time'] = time();
            //用户注册IP                                             
            $data['registe_ip'] = request()->ip();      
            //添加管理员
            if (\think\Db::name('admin')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['username'].' 管理员');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //获取所有权限信息
            $power = get_power();
            //获取状态正常权限信息
            $powerlist = array();
            foreach ($power as $value) {
                if($value['is_state'] == 1 && $value['pid'] == 1){
                    $powerlist[] = $value;
                }
            }
            foreach ($powerlist as $key => $val) {
                $val['list'] = array();
                foreach ($power as $value) {
                    if($value['is_state'] == 1 && $value['pid'] == $val['id']){
                        $val['list'][] = $value;
                    }
                }
                $powerlist[$key] = $val;
            }
            //赋值数据集View模板输出  
            return view('add',array('powerlist'=>$powerlist));
        }
    }

    // 删除管理员
    public function delete(){
        //验证用户权限
        Common::checkpower(6);
        //要删除的管理员id
        $id = input('post.idlist','0');
        //不能删除admin管理员
        if($id == 1){return json(array('success'=>false,'info'=>'系统管理员不能修改！'));exit;}
        //删除管理员
        if(\think\Db::name('admin')->where(array('id'=>$id))->delete()){
            //修改数据后清除缓存
            \think\Cache::rm('admin_'.$id);
            //记录系统日志
            admin_log('删除 ID:'.$id.' 后台管理员');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

	// 后台操作日志
    public function log(){
        //验证用户权限
        Common::checkpower(46);
        //拼接搜索条件
        $where = array();
        $pageParam = ['query' =>[]];
        //输入搜索内容
        $keyword = input('get.keyword','');    
        if($keyword){
            $where['m.username|a.remark'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        // 查询操作日志数据 并且每页显示20条数据
        $list = \think\Db::name('admin_log')->alias('a')
                                     ->field('a.*,m.username')
                                     ->join('__ADMIN__ m','a.uid = m.id')
                                     ->where($where)
                                     ->order("a.create_time desc")
                                     ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $val) {
            // 格式化时间
            $val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
            $data[$key] = $val;
        }
        //赋值数据集View模板输出  
        return view('log',array('list'=>$list,'keyword'=>$keyword,'data'=>$data));
    }

    // 删除操作日志记录
    public function logdel(){
        //验证用户权限
        Common::checkpower(46);
        // 要删除的记录id
        $idlist = input('post.idlist','');
        //判断是否是清空
        if($idlist == 'all'){
            //清空记录
            if(\think\Db::name('admin_log')->where(array('id'=>array('GT','0')))->delete()){
                return json(array('success'=>true,'info'=>'记录已清空！'));exit;
            }else{
                return json(array('success'=>false,'info'=>'清空记录失败！'));exit;
            }
        }else{
            //删除记录
            if(\think\Db::name('admin_log')->where(array('id'=>array('IN',$idlist)))->delete()){
                return json(array('success'=>true,'info'=>'记录已删除！'));
            }else {
                return json(array('success'=>false,'info'=>'删除记录失败！'));
            }
        }
    }
}
