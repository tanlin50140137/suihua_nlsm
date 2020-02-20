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

class Business extends Common
{
    // 商家列表
    public function index(){
        //验证用户权限
        Common::checkpower(21);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //商家分类
        $typeid = input('get.typeid','0');     
        if($typeid){
            $where['a.typeid'] = $typeid;
            $pageParam['query']['typeid'] = $typeid;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['a.name|a.username|m.username'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查找商家分类
        $type = get_business_type();

        // 查询操作日志数据 并且每页显示20条数据
        $list = \think\Db::name('business')->alias('a')
                                     ->field('a.*,m.username nickname')
                                     ->join('__MEMBER__ m','a.uid = m.id','LEFT')
                                     ->where($where)
                                     ->order("a.sort asc,id desc")
                                     ->paginate(20,false,$pageParam);
        $data = $list->all();
        foreach ($data as $key => $value) {
            // $value = get_business($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            switch($value['is_state']) {
                case '0': $value['state'] = '<span style="color:red;">隐藏</span>'; break;
                case '1': $value['state'] = '待审核'; break;
                case '2': $value['state'] = '显示'; break;
                case '3': $value['state'] = '<span style="color:red;">失败</span>'; break;
            }
            $value['typename'] = '自营商家';
            //转换商家对应的分类名称
            foreach ($type as $k => $val) {
                if($value['typeid'] == $val['id']){$value['typename'] = $val['name'];break;}
            }
            $value['total'] = \think\Db::name('business_money_log')->where(array('uid'=>$value['id'],'money'=>array('GT',0)))->sum('money');
            $data[$key] = $value;
        }

        $money = \think\Db::name('business')->alias('a')
                                     ->join('__MEMBER__ m','a.uid = m.id','LEFT')
                                     ->where($where)
                                     ->value('sum(a.money)');
        $total =  \think\Db::name('business_money_log')->where(array('money'=>array('GT',0)))->sum('money');
        //赋值数据集View模板输出 
        
        $param = array();
        $param['keyword'] = $keyword;
        $param['list'] = $list;
        $param['data'] = $data;
        $param['type'] = $type;
        $param['typeid'] = $typeid;
        $param['money'] = $money;
        $param['total'] = $total;
        return view('index',$param);
    }

    // 添加商家
    public function add(){
        //验证用户权限
        Common::checkpower(21);
        if (request()->isPost()) {
            $data = input('post.','');
            //用户账号过滤
            $data['username'] = input('post.username','');
            if(strlen($data['username']) < 5 || strlen($data['username']) > 30){
                return json(array('success'=>false,'info'=>'商家账号请在5-30个字符以内！'));exit;
            }
            if(\think\Db::name('business')->where(array('username'=>$data['username']))->count() > 0){
                return json(array('success'=>false,'info'=>'商家账号已存在，请更换！'));exit;
            }
            //加密用户密码
            $data['password'] = input('post.password','');
            if(strlen($data['password'])<6 || strlen($data['password'])>18){return json(array('success'=>false,'info'=>'请输入6-18位数之间商家密码！'));exit;}
            //用户密码加密
            $data['password'] = password(sha1($data['password']));    

            $truename = input('post.truename','');
            if(!$truename){return json(array('success'=>false,'info'=>'请输入收款人姓名！'));exit;}
            $alipay = input('post.alipay','');
            if(!$truename){return json(array('success'=>false,'info'=>'请输入收款支付宝！'));exit;}

            $landmark = input('post.landmark','');
            if(!$landmark){return json(array('success'=>false,'info'=>'请选择商家坐标！'));exit;}
            $landmark = explode(',', $landmark);
            //商家地址坐标
            $data['longitude'] = (float)$landmark[0];
            $data['latitude'] = (float)$landmark[1];
            //商家介绍
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);
            //商家地址
            $data['city'] = input('post.city','');
            if(!$data['city']){return json(array('success'=>false,'info'=>'请选择商家地区！'));exit;}

            //商家轮播图
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
                            $image->thumb(800, 800)->save('.'.$path);
                            // 上传成功 获取上传文件信息
                            array_unshift($data['image'],$path);
                        }
                    }
                }
                
            }
            // if(!$data['image']){return json(array('success'=>false,'info'=>'请选择上传商家图片！'));exit;}
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);
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
                    $image->thumb(800, 800)->save('.'.$path);
                    // 上传成功 获取上传文件信息
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }
            //上传文件
            $file = isset($_FILES['license']) ? $_FILES['license'] : '';
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
                    // 上传成功 获取上传文件信息
                    $data['license'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }
            //商家创建时间                  
            $data['create_time'] = time();  
            //添加商家
            if($id =  \think\Db::name('business')->insertGetId($data)){
                //记录系统日志
                admin_log('添加 ID:'.$id.' 商家');
                //清除Api获取商家相关接口缓存
                \think\Cache::clear('business');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                //获取添加错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else {
            //查找商家分类
            $type = get_business_type();
            //赋值数据集View模板输出 
            return view('add',array('type'=>$type));
        }
    }

    //修改商家
    public function edit(){
        //验证用户权限
        Common::checkpower(21);
        if (request()->isPost()) {
            $id = input('post.id','0');
            $data = input('post.','');
            
            $truename = input('post.truename','');
            if(!$truename){return json(array('success'=>false,'info'=>'请输入收款人姓名！'));exit;}
            $alipay = input('post.alipay','');
            if(!$truename){return json(array('success'=>false,'info'=>'请输入收款支付宝！'));exit;}

            $landmark = input('post.landmark','');
            if(!$landmark){return json(array('success'=>false,'info'=>'请选择商家坐标！'));exit;}
            $landmark = explode(',', $landmark);
            //商家地址坐标
            $data['longitude'] = (float)$landmark[0];
            $data['latitude'] = (float)$landmark[1];
            //商家介绍
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            $data['content'] = htmlspecialchars($data['content']);
            //加密商家密码
            $data['password'] = input('post.password','');
            if($data['password']){
                //用户密码加密
                $data['password'] = password(sha1($data['password']));     
                //记录系统日志
                admin_log('修改 ID:'.$id.' 商家密码');
            }else{
                unset($data['password']);
            }
            //商家地址
            $data['city'] = input('post.city','');
            if(!$data['city']){return json(array('success'=>false,'info'=>'请选择商家地区！'));exit;}

            //商家轮播图
            $data['image'] = input('post.image','');
            $data['image'] = $data['image'] ? explode('、', $data['image']) : array();

            //上传文件
            if (is_array($data['image'])) {
                foreach ($data['image'] as $key => $value) {
                    //处理base64编码的图片上传
                    if(strpos($value, 'data:image') !== false){
                        //创建文件夹
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                            mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                        }
                        //保存文件路径
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $value, $result)){
                            $type = $result[2];
                            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $value)), FILE_USE_INCLUDE_PATH)) {
                                    return json(array('success'=>false,'info'=>'图片上传失败！'));
                                }else{
                                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                                    $image = \think\Image::open('.'.$path);
                                    $image->thumb(800, 800)->save('.'.$path);
                                    $value = $path;
                                }
                            }else{
                                //文件类型错误
                                return json(array('success'=>false,'info'=>'图片上传类型错误！'));
                            }
                         
                        }else{
                            //文件错误
                            return json(array('success'=>false,'info'=>'文件错误！'));
                        }
                    }
                    $data['image'][$key] = $value;
                }
            }
            // if(!$data['image']){return json(array('success'=>false,'info'=>'请选择上传商家图片！'));exit;}
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);
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
                    $image->thumb(800, 800)->save('.'.$path);
                    // 上传成功 获取上传文件信息
                    $data['logo'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }
            //上传文件
            $file = isset($_FILES['license']) ? $_FILES['license'] : '';
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
                    // 上传成功 获取上传文件信息
                    $data['license'] = $path;
                }else{
                    //ajax返回上传错误提示错误信息
                    return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
                }
            }

            $data['setmeal'] = input('post.setmeal/a','');
            $data['setmeal'] = $data['setmeal'] ? implode(',', $data['setmeal']) : '';

            //修改商家
            if(\think\Db::name('business')->where(array('id'=>$id))->update($data)){
                if($data['is_state'] == 1){
                    \think\Db::name('member')->where(array('bus_id'=>$id))->setField('level_id',3);
                    $uid = \think\Db::name('member')->where(array('bus_id'=>$id))->value('id');
                    //修改数据后清除缓存
                    \think\Cache::rm('member_'.$uid);
                }
                //修改数据后清除缓存
                \think\Cache::rm('business_'.$data['id']);
                //记录系统日志
                admin_log('修改 ID:'.$data['id'].' 商家信息');
                //清除Api获取商家相关接口缓存
                \think\Cache::clear('business');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            } 
        }else {
            //获取修改商家id
            $id = input('get.id', 0);
            //查找数据
            $list = get_business($id);
            if (!$list){return error("不存在的数据!");exit;} 
            //多图
            $list['image'] = json_decode($list['image']);  
            //查找商家分类
            $type = get_business_type();

            $setmeal = \think\Db::name('setmeal')->field('id,name')->select();
            //赋值数据集View模板输出 
            return view('edit',array('list'=>$list,'type'=>$type,'setmeal'=>$setmeal));
        }
    }

    //删除商家
    public function delete(){
        //验证用户权限
        Common::checkpower(21);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除商家
        if (\think\Db::name('business')->where(array('id'=>array('IN',$idlist)))->delete()) {
            $list = explode(",",$idlist);
            foreach ($list as $key => $value) {
                //修改数据后清除缓存
                \think\Cache::rm('business_'.$value);
            }
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 商家');
            //清除Api获取商家相关接口缓存
            \think\Cache::clear('business');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 修改商家显示
    public function state() {
        //验证用户权限
        Common::checkpower(21);
        //获取修改管理员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('business')->where(array('id'=>$id))->setField('is_state', $state)) {
            if($state == 1){
                \think\Db::name('member')->where(array('bus_id'=>$id))->setField('level_id',3);
                $uid = \think\Db::name('member')->where(array('bus_id'=>$id))->value('id');
                //修改数据后清除缓存
                \think\Cache::rm('member_'.$uid);
            }
            //记录系统日志
            admin_log("修改 ID:".$id." 商家状态");
            //修改数据后清除缓存
            \think\Cache::rm('business_'.$id);
            //清除Api获取商家相关接口缓存
            \think\Cache::clear('business');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 商家分类
    public function type(){
        //验证用户权限
        Common::checkpower(34);
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
        $list = \think\Db::name('business_type')->field(true)->where($where)->paginate(20,false,$pageParam);
        $data = $list->all();
        //赋值数据集View模板输出  
        return view('type',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加商家分类
    public function type_add(){
        //验证用户权限
        Common::checkpower(34);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['name'] = input('post.name', '');
            if(!$data['name']){return json(array('success'=>false,'info'=>'请输入分类名称！'));exit;}
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
            $data['create_time'] = time();
            if (\think\Db::name('business_type')->insert($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('business_type');
                //记录系统日志
                admin_log('添加 '.$data['name'].' 商家分类');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //赋值数据集View模板输出  
            return view('type_add');
        }
    }

    // 修改商家分类
    public function type_edit(){
        //验证用户权限
        Common::checkpower(34);
        if (request()->isPost()) {
            //接收输入数据
            $id = input('post.id','');
            $data = input('post.','');
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
            if (\think\Db::name('business_type')->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('business_type');
                //记录系统日志
                admin_log('修改 '.$data['name'].' 商家分类');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            //获取修改商家id
            $id = input('get.id', 0);
            //查找商家分类
            $type = get_business_type();
            $list = array();
            foreach ($type as $key => $value) {
                if($value['id'] == $id){$list = $value;break;}
            }
            //赋值数据集View模板输出  
            return view('type_edit',array('list'=>$list));
        }
    }

    //删除商家分类
    public function type_del(){
        //验证用户权限
        Common::checkpower(34);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除商家
        if (\think\Db::name('business_type')->where(array('id'=>array('IN',$idlist)))->delete()) {
            //修改数据后清除缓存
            \think\Cache::rm('business_type');
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 商家分类');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //查看用户余额信息
    public function money(){
        //验证用户权限
        Common::checkpower(21);
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

        $start_time = input('get.start_time','');
        $pageParam['query']['start_time'] = $start_time;     
        $end_time = input('get.end_time','');
        $pageParam['query']['end_time'] = $end_time;  
        if (!empty($start_time) && !empty($end_time)) {
            $where['create_time'] = array(array('EGT', strtotime($start_time)), array('ELT', strtotime($end_time)+(60*60*24)), 'AND');
        }else if (!empty($start_time)) {
            $where['create_time'] = array('EGT', strtotime($start_time));
        }else if (!empty($end_time)) {
            $where['create_time'] = array('ELT', strtotime($end_time)+(60*60*24));
        }

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('business_money_log')->field(true)->where($where)->order('create_time desc')->paginate(20,false,$pageParam);
        //遍历拼接普通会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['order_id'] = $value['order_id'] ? $value['order_id'] :'';
            $data[$key] = $value;
        }

        $business = get_business($id);
        $money = $business['money'];

        $where['money'] = array('GT',0);
        $total = \think\Db::name('business_money_log')->where($where)->value('sum(money)');

        //赋值数据集View模板输出  
        $param = array();
        $param['data'] = $data;
        $param['list'] = $list;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $param['money'] = $money;
        $param['total'] = $total;
        $param['id'] = $id;
        $param['keyword'] = $keyword;
        $param['type'] = $type;
        return view('money',$param);
    } 

    // 赠送套餐
    public function setmeal(){
        //验证用户权限
        Common::checkpower(21);
        $id = input('get.id','0');

        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        $pageParam['query']['id'] = $id;     

        $where['bus_id'] = $id;

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('setmeal_goods')->alias('a')
                                           ->field('a.*,sum(s.number) number')
                                           ->join('zsh_business_setmeal s','a.id = s.goods_id and s.bus_id='.$id,'LEFT')
                                           ->group('a.id')
                                           ->paginate(24,false,$pageParam);

        foreach ($list as $key => $value) {
            $value['number'] = $value['number'] ? $value['number'] : 0;
            $list[$key] = $value; 
        }

        $data = $list->all();
        
        //赋值数据集View模板输出  
        return view('setmeal',array('list'=>$list,'data'=>$data,'id'=>$id));
    }

    public function setedit(){
        //验证用户权限
        Common::checkpower(50);

        //获取修改管理员id
        $id = input('post.id','0');
        $bus_id = input('post.bus_id','0');
        $value = input('post.value','0');
        
        $data = array();
        $data['number'] = $value;
        $data['create_time'] = time();
        $data['bus_id'] = $bus_id;
        $data['goods_id'] = $id;

        if (\think\Db::name('business_setmeal')->insert($data)) {
           
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }


    //转账商家
    public function money_edit(){
        //验证用户权限
        Common::checkpower(21);
        if (request()->isPost()) {
            //要修改的普通会员id
            $id = input('post.id','0');
            $business = get_business($id);
            //修改余额
            $money = input('post.money','0');
            //修改说明
            $remark = input('post.remark','');
            $remark = $remark ? $remark : '后台商家转账';

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
            $param['payee_account'] = $business['alipay'];
            $param['amount'] = $money;
            $param['payer_show_name'] = '广州南领商贸有限公司';
            $param['payee_real_name'] = $business['truename'];
            $param['remark'] = $remark;

            $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
            $request->setBizContent($bizcontent);
            $result = $aop->execute ( $request); 
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if(!empty($resultCode) && $resultCode == 10000){
                \think\Db::name('business')->where('id',$id)->setDec('money',$money);

                //修改数据后清除缓存
                \think\Cache::rm('business_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 商家余额（'.$money.'）');

                //写入日志
                business_money_log("后台商家转账",$id,"后台商家转账",-$money,$param['out_biz_no']);
                return json(array('success'=>true,'info'=>'转账成功！')); 
            }else{
                return json(array('success'=>false,'info'=>$result->alipay_fund_trans_toaccount_transfer_response->sub_msg));
            }
           
        }else{
            //要修改的普通会员id
            $id = input('get.id','0');
            $list = get_business($id);
            //赋值数据集View模板输出 
            return view('money_edit',array('id'=>$id,'money'=>$list['money']));
        }
    }


    public function statisti(){
         //验证用户权限
        Common::checkpower(21);

        // \think\Db::name('business_transfer')->where(array('payprice'=>0))->delete();

        //拼接条件
        $where = array();
        $where['a.is_state'] = 2;
        $pageParam = ['query' =>[]];
        
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['a.order_id|a.paytype|a.paymoney|b.name'] = $keyword;
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

        // 查询操作日志数据 并且每页显示20条数据
        $list = \think\Db::name('business_transfer')->alias('a')
                                     ->field('a.*,b.name')
                                     ->join('__BUSINESS__ b','a.uid = b.id','LEFT')
                                     ->where($where)
                                     ->order("a.create_time desc")
                                     ->paginate(50,false,$pageParam);

        //遍历拼接普通会员信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }

        $total = \think\Db::name('business_transfer')->alias('a')->join('__BUSINESS__ b','a.uid = b.id','LEFT')->where($where)->value('sum(a.payprice)');

        //赋值数据集View模板输出  
        $param = array();
        $param['data'] = $data;
        $param['list'] = $list;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $param['total'] = $total;
        $param['keyword'] = $keyword;
        return view('statisti',$param);
    }


    // 库存记录
    public function stock(){
        \think\Db::name('business_setmeal')->where(array('number'=>0))->delete();
        //验证用户权限
        Common::checkpower(21);
        $id = input('get.id','0');

        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        $pageParam['query']['id'] = $id;     

        $where['a.bus_id'] = $id;

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('business_setmeal')->alias('a')
                                           ->field('a.*,s.name,s.logo')
                                           ->join('zsh_setmeal_goods s','a.goods_id = s.id')
                                           ->where($where)
                                           ->order('a.create_time desc')
                                           ->paginate(24,false,$pageParam);

        foreach ($list as $key => $value) {
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $list[$key] = $value; 
        }

        $data = $list->all();
        
        //赋值数据集View模板输出  
        return view('stock',array('list'=>$list,'data'=>$data,'id'=>$id));
    }
}