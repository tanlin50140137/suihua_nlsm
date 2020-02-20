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

class Member extends Common
{
    // 显示普通会员列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(18);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //会员等级     
        $level_id = input('get.level_id','0'); 
        if($level_id){
            $where['level_id'] = $level_id;
            $pageParam['query']['level_id'] = $level_id;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['id|username|nickname|truename'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //会员总数
        $count = 0;
        //获取会员等级分组信息
        $level = get_member_level();
        if ($level) {
            foreach($level as $key => $value) {
                //统计分组会员总数
                $value['usernum'] = \think\Db::name('member')->where(array_merge($where,array('level_id'=>$value['id'])))->count();
                $count = $count + $value['usernum'];
                $level[$key] = $value;
            }
        }
        //拼接排序信息
        $order = input('get.order','desc');  
        $sort = input('get.sort','id'); 

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member')->field('id')->where($where)->order("{$sort} {$order}")->paginate(20,false,$pageParam);
        //遍历拼接会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_member($value['id']);
            $value['username'] = $value['username']==md5($value['openid']) ? '' : $value['username'];
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //转换用户对应的等级名称
            foreach ($level as $k => $val) {
                if($value['level_id'] == $val['id']){$value['level_name'] = $val['name'];break;}
            }
           
            $where2 = array();
            $where2['uid'] = $value['id'];
            $where2['type'] = '会员套餐';
            $value['total'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
            if($value['level_id'] >3){
                $where2['type'] = '商品购买';
                $total = \think\Db::name('member_achievement')->where($where2)->sum('money');
                $value['total'] = $value['total'] + $total;
            }

            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d')) + 86400;
            $where2['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');
            $where2['type'] = '会员套餐';
            $value['month'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
            if($value['level_id'] >3){
                $where2['type'] = '商品购买';
                $month = \think\Db::name('member_achievement')->where($where2)->sum('money');
                $value['month'] = $value['month'] + $month;
            }
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['level_id'] = $level_id;
        $param['level'] = $level;
        $param['list'] = $list;
        $param['data'] = $data;
        $param['sort'] = $sort;
        $param['count'] = $count;
        $param['order'] = $order == 'asc' ? 'desc' :'asc';
        return view('index',$param);
    }

    //修改会员状态
    public function state() {
        //验证用户权限
        Common::checkpower(18);
        //获取修改会员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('member')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 会员状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 添加会员
    public function add(){
        //验证用户权限
        Common::checkpower(18);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            //用户账号过滤
            $data['username'] = input('post.username','');
            if(strlen($data['username']) < 5 || strlen($data['username']) > 30){
                return json(array('success'=>false,'info'=>'用户名称请在5-30个字符以内！'));exit;
            }
            if(\think\Db::name('member')->where(array('username'=>$data['username']))->count() > 0){
                return json(array('success'=>false,'info'=>'用户账号已存在，请更换！'));exit;
            }
            //加密用户密码
            $data['password'] = input('post.password','');
            if(strlen($data['password'])<6 || strlen($data['password'])>18){return json(array('success'=>false,'info'=>'请输入6-18位数之间会员密码！'));exit;}
            //用户密码加密
            $data['password'] = password(sha1($data['password']));     
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
            //用户创建时间         
            $data['create_time'] = time();
            //用户注册IP                                             
            $data['registe_ip'] = request()->ip();  
            //推荐人
            $invite = input('post.invite','');
            if($invite){
                $data['pid'] = \think\Db::name('member')->where(array('username'=>$invite))->value('id');
            }else{
                $data['pid'] = 0;
            }
            unset($data['invite']);
            //添加会员
            if ($id = \think\Db::name('member')->insertGetId($data)) {
                if($invite){
                    //查找上级会员
                    $idlist = get_parent_member($id);
                    //如果有找到上级，则记录等级关系
                    if (!empty($idlist)) {
                        $list = array();
                        foreach ($idlist as $key => $value) {
                            $list[] = array('id'=>$id,'pid'=>$value,'level'=>($key + 1));
                        }
                        \think\Db::name('member_relation')->insertAll($list);
                    }
                }
                //记录系统日志
                admin_log('添加 '.$data['username'].' 会员');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //获取会员等级分组信息
            $level = get_member_level();
            return view('add',array('level'=>$level));
        }
    }

    // 修改普通会员信息
    public function edit(){
        //验证用户权限
        Common::checkpower(18);
        if (request()->isPost()) {
            //要修改普通会员id
            $id = input('post.id','0');
            //获取要修改的普通会员信息
            $data = input('post.','');
            unset($data['username']);
            //加密用户密码
            if(isset($data['password']) && $data['password']){
                if(strlen($data['password'])<6 || strlen($data['password'])>18){return json(array('success'=>false,'info'=>'请输入6-18位数之间会员密码！'));exit;}
                $data['password'] = password(sha1($data['password']));     //用户密码加密
                //记录系统日志
                admin_log('修改 ID:'.$id.' 普通会员密码');
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
            //修改普通会员
            if (\think\Db::name('member')->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('member_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 普通会员信息');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            //获取普通会员id
            $id = input('get.id','0');
            //获取普通会员信息
            $list = get_member($id);
            $list['create_time'] = date('Y-m-d H:i:s',$list['create_time']);
            //获取所有等级分组信息
            $level = get_member_level();
            
            //获取会员每级分销会员总数
            $count = \think\Db::name('member_relation')->where(array('pid'=>$id))->group('level')->column('count(id)','level');
            $number = 0;
            if($count){
                foreach ($count as $key => $value) {
                    $number = $number + $value;
                }
            }

            //赋值数据集View模板输出  
            $param = array();
            $param['list'] = $list;
            $param['level'] = $level;
            $param['count'] = $count;
            $param['number'] = $number;
            return view('edit',$param);
        }
    }

     //修改会员上级id
    public function superior(){
        //验证用户权限
        Common::checkpower(18);
        
        //要修改普通会员id
        $id = input('post.id','0');
        //父级id
        $pid = input('post.pid','0');
        //验证会员信息
        if (\think\Db::name('member')->where(array('id'=>$id))->count() < 1) {
            return json(array('success'=>false,'info'=>'不存在的用户信息！'));exit;
        }
        if (\think\Db::name('member')->where(array('id'=>$pid))->count() < 1) {
            return json(array('success'=>false,'info'=>'不存在的用户信息！'));exit;
        }
        //查找下级会员
        $list = \think\Db::name('member_relation')->where(array('pid'=>$id))->order('id')->column('id');
        if (in_array($pid, $list)) {
            return json(array('success'=>false,'info'=>'上级会员不能是团队会员！'));exit;
        }

        //删除现有的层级关系网
        \think\Db::name('member_relation')->where(array('id'=>$id))->delete();
        //生成层级关系
        \think\Db::name('member')->where(array('id'=>$id))->setField('pid',$pid);
        //查找上级会员
        $idlist = get_parent_member($id);
        //如果有找到上级，则记录等级关系
        if (!empty($idlist)) {
            $data = array();
            foreach ($idlist as $key => $value) {
                $data[] = array('id'=>$id,'pid'=>$value,'level'=>($key + 1));
            }
            \think\Db::name('member_relation')->insertAll($data);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$id);
        }
        if($list){
            foreach ($list as $key => $value) {
                //删除现有的层级关系网
                \think\Db::name('member_relation')->where(array('id'=>$value))->delete();
                //查找上级会员
                $idlist = get_parent_member($value);
                //如果有找到上级，则记录等级关系
                if (!empty($idlist)) {
                    $data = array();
                    foreach ($idlist as $k => $val) {
                        $data[] = array('id'=>$value,'pid'=>$val,'level'=>($k + 1));
                    }
                    \think\Db::name('member_relation')->insertAll($data);
                    //修改数据后清除缓存
                    \think\Cache::rm('member_'.$value);
                }
            }
        }
        
        return json(array('success'=>true,'info'=>'更新成功！'));
    }

    // 删除会员
    public function delete(){
        //验证用户权限
        Common::checkpower(18);
        //要删除的会员id
        $id = input('post.idlist','0');
        //删除会员
        if(\think\Db::name('member')->where(array('id'=>$id))->delete()){
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$id);
            //删除现有的层级关系网
            \think\Db::name('member_relation')->where(array('id'=>$id))->delete();
            //记录系统日志
            admin_log('删除 ID:'.$id.' 会员');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改用户券分
    public function point(){
        //验证用户权限
        Common::checkpower(18);
        if (request()->isPost()) {
            //要修改的普通会员id
            $id = input('post.id','0');
            //修改券分
            $point = input('post.point','0');
            //修改说明
            $remark = input('post.remark','');
            $remark = $remark ? $remark : '管理员后台修改';
            //将头像路径写入数据库
            if(\think\Db::name('member')->where('id',$id)->setInc('point',$point)){
                //修改数据后清除缓存
                \think\Cache::rm('member_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 用户券分（'.$point.'）');
                //记录会员券分记录
                member_point_log('管理员修改',$id,$remark,$point);
                return json(array('success'=>true,'info'=>'修改成功！')); 
            }else{
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
           
        }else{
            //要修改的普通会员id
            $id = input('get.id','0');
            $list = get_member($id);
            //赋值数据集View模板输出 
            return view('point',array('id'=>$id,'point'=>$list['point']));
        }
    }

    //查看下级会员信息
    public function subordinate(){
        //验证用户权限
        Common::checkpower(18);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];

        //获取会员id
        $id = input('get.id','0');
        $pageParam['query']['id'] = $id;

        //会员等级     
        $level_id = input('get.level_id',''); 
        $pageParam['query']['level_id'] = $level_id;
        if($level_id){
            $where['level_id'] = $level_id;
        }

        //输入搜索内容
        $keyword = input('get.keyword','');     
        $pageParam['query']['keyword'] = $keyword;
        if($keyword){
            $where['id|username|nickname|truename'] = array('LIKE', "%{$keyword}%");
        }

        $level = input('get.level','');
        $pageParam['query']['level'] = $level;
        if($level){
            $idlist = \think\Db::name('member_relation')->where(array('pid'=>$id,'level'=>$level))->column('id');
        }else{
            $idlist = \think\Db::name('member_relation')->where(array('pid'=>$id))->column('id');
        }
        $idlist = implode(",",$idlist);
        $where['id'] = array('IN',$idlist);

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member')->field('id')->where($where)->paginate(20,false,$pageParam);
        //遍历拼接普通会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_member($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }

        //获取会员等级分组信息
        $levellist = get_member_level();

        //赋值数据集View模板输出  
        $param = array();
        $param['data'] = $data;
        $param['list'] = $list;
        $param['levellist'] = $levellist;
        $param['keyword'] = $keyword;
        $param['level'] = $level;
        $param['id'] = $id;
        $param['level_id'] = $level_id;
        return view('subordinate',$param);
    } 

    //查看用户券分信息
    public function integral(){
        //验证用户权限
        Common::checkpower(18);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //获取会员id
        $id = input('get.id','0');
        $pageParam['query']['id'] = $id;
        $where['uid'] = $id;
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_point_log')->field(true)->where(array('uid'=>$id))->order('create_time desc')->paginate(20,false,$pageParam);
        //遍历拼接普通会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['order_id'] = $value['order_id'] ? $value['order_id'] :'';
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('integral',array('data'=>$data,'list'=>$list));
    } 

    //查看用户余额信息
    public function money(){
        //验证用户权限
        Common::checkpower(18);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //获取会员id
        $id = input('get.id','0');
        $pageParam['query']['id'] = $id;
        $where['uid'] = $id;
        //券分类型     
        $type = input('get.type',''); 
        if($type){
            $where['type'] = $type;
            $pageParam['query']['type'] = $type;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['remark|order_id'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_money_log')->field(true)->where($where)->order('create_time desc')->paginate(20,false,$pageParam);
        //遍历拼接普通会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['order_id'] = $value['order_id'] ? $value['order_id'] :'';
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('money',array('data'=>$data,'list'=>$list,'id'=>$id,'keyword'=>$keyword,'type'=>$type));
    } 

    //修改用户余额
    public function money_edit(){
        //验证用户权限
        Common::checkpower(18);
        if (request()->isPost()) {
            //要修改的普通会员id
            $id = input('post.id','0');
            //修改余额
            $money = input('post.money','0');
            //修改说明
            $remark = input('post.remark','');
            $remark = $remark ? $remark : '管理员后台修改';
            //将头像路径写入数据库
            if(\think\Db::name('member')->where('id',$id)->setInc('money',$money)){
                //修改数据后清除缓存
                \think\Cache::rm('member_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 用户余额（'.$money.'）');
                //记录会员余额记录
                member_money_log('管理员修改',$id,$remark,$money);
                return json(array('success'=>true,'info'=>'修改成功！')); 
            }else{
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
           
        }else{
            //要修改的普通会员id
            $id = input('get.id','0');
            $list = get_member($id);

            //赋值数据集View模板输出  
            $param = array();
            $param['id'] = $id;
            $param['money'] = $list['money'];
            return view('money_edit',$param);
        }
    }

    // 会员等级分组管理
    public function level(){
        //验证用户权限
        Common::checkpower(17);
        // 获取等级分组信息
        $list = get_member_level();
        if ($list) {
            foreach($list as $key => $val) {
                //统计分组会员总数
                $val['usernum'] = \think\Db::name('member')->where(array('level_id'=>$val['id']))->count();
                $list[$key] = $val;
            }
        }
        return view('level',array('list'=>$list));
    }

    //添加会员分组
    public function level_add(){
        //验证用户权限
        Common::checkpower(17);
        if (request()->isPost()) {
            $data = input('post.');
            //上传文件
            $file = isset($_FILES['logo']) ? $_FILES['logo'] : '';
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
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'上传文件不存在！'));exit;
                }
            }
            //添加会员分组
            if ($id = \think\Db::name("member_level")->insertGetId($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('member_level');
                //记录系统日志
                admin_log('添加 ID:'.$id.' 普通会员分组');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else {
            return view('level_add');
        }
    }

    //修改会员分组
    public function level_edit(){
        //验证用户权限
        Common::checkpower(17);
        if (request()->isPost()) {
            $id = input('post.id','0');
            $data = input('post.');
            //上传文件
            $file = isset($_FILES['logo']) ? $_FILES['logo'] : '';
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
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'上传文件不存在！'));exit;
                }
            }
            //修改会员分组
            if (\think\Db::name("member_level")->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('member_level');
                //记录系统日志
                admin_log('修改 ID:'.$id.' 普通会员分组信息');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else {
            $id = input('get.id', '0');
            //获取会员分组
            $level = get_member_level();
            $list = array();
            //查找数据
            foreach ($level as $key => $value) {
                if($value['id'] == $id){$list = $value;break;}
            }
            return view('level_edit',array('list'=>$list));
        }
    }

    //删除会员分组
    public function level_del(){
        //验证用户权限
        Common::checkpower(17);
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_level')->where(array('id' => array('IN', $idlist)))->delete()) {
            // 修改数据后清除缓存
            \think\Cache::rm('member_level');
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 会员分组');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            //获取修改错误原因
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 用户留言
    public function comment(){
        //验证用户权限
        Common::checkpower(36);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        $is_state = input('get.is_state','0'); 
        if($is_state){
            $where['c.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['c.remark|m.username'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_comment')->alias('c')
                                       ->field('c.*,m.username')
                                       ->join('__MEMBER__ m','c.uid = m.id')
                                       ->where($where)
                                       ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('comment',array('keyword'=>$keyword,'list'=>$list,'data'=>$data,'is_state'=>$is_state));
    }

    //修改用户留言状态
    public function comment_state() {
        //验证用户权限
        Common::checkpower(36);
        //获取修改用户留言id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('member_comment')->where(array('id'=>$id))->setField('is_state', $state)) {
            //记录系统日志
            admin_log("修改 ID:".$id." 用户留言状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除用户留言
    public function comment_del(){
        //验证用户权限
        Common::checkpower(36);
        //要删除的用户留言id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_comment')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 用户留言');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 用户提现管理
    public function withdraw(){
        //验证用户权限
        Common::checkpower(39);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        $is_state = input('get.is_state',''); 
        if($is_state){
            $where['t.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['t.alipay|m.nickname|t.order_id'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_take_money')->alias('t')
                                                    ->field('t.*,m.nickname')
                                                    ->join('__MEMBER__ m','t.uid = m.id')
                                                    ->where($where)
                                                    ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['return_time'] = $value['return_time'] ? date('Y-m-d H:i:s',$value['return_time']) : '';
            switch ($value['is_state']) {
                case '3': $value['state'] = '申请中'; break;
                case '1': $value['state'] = '<font color="red">已转账</font>'; break;
                case '2': $value['state'] = '<font color="#ccc">已驳回</font>'; break;
            }
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('withdraw',array('keyword'=>$keyword,'list'=>$list,'data'=>$data,'is_state'=>$is_state));
    }

    // 删除用户提现记录
    public function withdraw_del(){
        //验证用户权限
        Common::checkpower(39);
        //要删除的用户留言id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_take_money')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 用户提现记录');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //提现转账
    public function turn(){
        $id = input('post.id','0');
        $list = \think\Db::name('member_take_money')->where(array('id'=>$id))->find();
        if(!$list || $list['is_state'] != 3){return json(array('success'=>false,'info'=>'该状态不能操作！'));}
        
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
        $param['payee_account'] = $list['alipay'];
        $param['amount'] = $list['money'] - $list['expenses'];
        $param['payer_show_name'] = $alipay['company'];
        $param['remark'] = '会员提现';

        $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($bizcontent);
        $result = $aop->execute ( $request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
            \think\Db::name('member_take_money')->where(array('id'=>$id))->update(array('is_state'=>1,'return_time'=>time(),'order_id'=>$param['out_biz_no']));
            //记录系统日志
            admin_log('转账 '.$param['amount'].' 元给 ID:'.$list['uid'].'用户'.$list['alipay'].'支付宝账户');
            return json(array('success'=>true,'info'=>'提现成功！'));
        } else {
            return json(array('success'=>false,'info'=>$result->$responseNode->sub_msg));
        }

    }

    //驳回提现申请
    public function rebut(){
        $id = input('post.id','0');
        $list = \think\Db::name('member_take_money')->where(array('id'=>$id))->find();
        if(!$list || $list['is_state'] != 3){return json(array('success'=>false,'info'=>'该状态不能操作！'));}

        //退款返回用户余额
        \think\Db::name('member')->where(array('id'=>$list['uid']))->setInc('money',$list['money']);
        //修改数据后清除缓存
        \think\Cache::rm('member_'.$list['uid']);
        //写入日志
        member_money_log("用户提现",$list['uid'],"提现驳回",$list['money']);
        //记录系统日志
        admin_log('提现驳回 '.$list['money'].' 元给 ID:'.$list['uid'].'用户');
        \think\Db::name('member_take_money')->where(array('id'=>$id))->setField('is_state',2);
        return json(array('success'=>true,'info'=>'提现已驳回'));
    }

    // 显示会员升级套餐列表页面
    public function goods(){
        //验证用户权限
        Common::checkpower(43);
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
        $list = \think\Db::name('member_goods')->field('id')->where($where)->order('sort asc')->paginate(20,false,$pageParam);
        //获取会员等级分组信息
        $level = get_member_level();
        //遍历拼接数据信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_member_goods($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //转换用户对应的等级名称
            foreach ($level as $k => $val) {
                if($value['level_id'] == $val['id']){$value['level_name'] = $val['name'];break;}
            }
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('goods',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加会员升级套餐
    public function goods_add(){
        //验证用户权限
        Common::checkpower(43);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'套餐名称请在5-30个字符以内！'));exit;
            }
            //上传文件
            $file = isset($_FILES['logo']) ? $_FILES['logo'] : '';
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
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }

            //套餐详情
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);

            //创建时间         
            $data['create_time'] = time();
            //添加会员升级套餐
            if (\think\Db::name('member_goods')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['name'].' 会员升级套餐');
                //清除Api获取会员升级套餐相关接口缓存
                \think\Cache::clear('member_goods');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //获取会员等级分组信息
            $level = get_member_level();
            return view('goods_add',array('level'=>$level));
        }
    }
    
    // 修改会员升级套餐
    public function goods_edit(){
        //验证用户权限
        Common::checkpower(43);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','0');
            $data = input('post.','');
            $data['name'] = input('post.name','');
            if(strlen($data['name']) < 5 || strlen($data['name']) > 30){
                return json(array('success'=>false,'info'=>'套餐名称请在5-30个字符以内！'));exit;
            }
            //上传文件
            $file = isset($_FILES['logo']) ? $_FILES['logo'] : '';
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
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }

            //套餐详情
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);

            //修改会员升级套餐
            if (\think\Db::name('member_goods')->where(array('id'=>$id))->update($data)) {
                //修改后清除该套餐缓存
                \think\Cache::rm('member_goods_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 会员升级套餐信息');
                //清除Api获取会员升级套餐相关接口缓存
                \think\Cache::clear('member_goods');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            $id = input('get.id','0');
            $list = get_member_goods($id);
            //获取会员等级分组信息
            $level = get_member_level();
            return view('goods_edit',array('list'=>$list,'level'=>$level));
        }
    }

    //修改会员升级套餐状态
    public function goods_state() {
        //验证用户权限
        Common::checkpower(43);
        //获取修改会员升级套餐id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('member_goods')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改后清除该套餐缓存
            \think\Cache::rm('member_goods_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 会员升级套餐状态");
            //清除Api获取会员升级套餐相关接口缓存
            \think\Cache::clear('member_goods');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除会员升级套餐
    public function goods_del(){
        //验证用户权限
        Common::checkpower(43);
        //要删除的会员升级套餐id
        $idlist = input('post.idlist','0');
        //删除会员升级套餐
        if(\think\Db::name('member_goods')->where(array('id' => array('IN', $idlist)))->delete()){
            $list = explode(',', $idlist);
            foreach ($list as $key => $value) {
                //修改数据后清除缓存
                \think\Cache::rm('member_goods_'.$value);
            }
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 话费充值商品');
            //清除Api获取会员升级套餐相关接口缓存
            \think\Cache::clear('member_goods');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 升级套餐订单
    public function order(){
        //验证用户权限
        Common::checkpower(44);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //支付状态
        $is_state = input('get.is_state','');     
        if($is_state){
            $where['o.is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['o.order_id|m.nickname|g.name'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('member_goods_order')->alias('o')
                                                ->field('o.*,m.nickname,g.name,g.level_id')
                                                ->join('__MEMBER__ m','o.uid = m.id')
                                                ->join('__MEMBER_GOODS__ g','o.goods_id = g.id')
                                                ->where($where)
                                                ->order('o.create_time desc')
                                                ->paginate(20,false,$pageParam);
        $data = $list->all();
         //获取会员等级分组信息
        $level = get_member_level();

        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //转换用户对应的等级名称
            foreach ($level as $k => $val) {
                if($value['level_id'] == $val['id']){$value['level_name'] = $val['name'];break;}
            }
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('order',array('keyword'=>$keyword,'list'=>$list,'data'=>$data,'is_state'=>$is_state));
    }

    //修改升级套餐订单支付状态
    public function order_state() {
        //验证用户权限
        Common::checkpower(44);
        //获取修改订单评论id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if($state == 2){
            $order = \think\Db::name('member_goods_order')->where(array('order_id'=>$id))->find();
            //订单分佣
            $goods = commision_goods($order);
        }
        if (\think\Db::name('member_goods_order')->where(array('order_id'=>$id))->setField('is_state', $state)) {
            //记录系统日志
            admin_log("修改 ID:".$id." 升级套餐订单支付状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除升级套餐订单订单
    public function order_del(){
        //验证用户权限
        Common::checkpower(44);
        //要删除的订单id
        $idlist = input('post.idlist', '');
        if (\think\Db::name('member_goods_order')->where(array('order_id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 升级套餐订单');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
    
    //发送消息
    public function message(){
        //验证用户权限
        Common::checkpower(18);
        if (request()->isPost()) {
            //接收输入数据
            $title = input('post.title','');
            $remark = input('post.remark','');
            $idlist = input('post.idlist','');

            //拼接发送信息数据
            $data = array();
            if($idlist){
                $idlist = explode(',', $idlist);
            }else{
                $idlist = \think\Db::name('member')->column('id');
            }
            if(is_array($idlist)){
                foreach ($idlist as $key => $value) {
                    $data[$key] = array();
                    $data[$key]['uid'] = $value;
                    $data[$key]['title'] = $title;
                    $data[$key]['remark'] = $remark;
                    $data[$key]['create_time'] = time();
                }
                \think\Db::name('member_message')->insertAll($data);
                return json(array('success'=>true,'info'=>'发送成功！'));
            }else{
                return json(array('success'=>false,'info'=>'发送失败！'));
            }
        }else{
            return view('message');
        }
    }
}