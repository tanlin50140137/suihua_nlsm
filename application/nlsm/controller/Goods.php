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

class Goods extends Common
{
    // 显示商品列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(24);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //获取商品分类
        $type = get_goods_type();
        //商品分类
        $typeid = input('get.typeid','');     
        if($typeid){
            $typelist = get_child($type,$typeid);
            $idlist = array($typeid);
            foreach ($typelist as $key => $value) {
                array_push($idlist,$value['id']);
            }
            $where['typeid'] = array('IN',implode(',', $idlist));
            $pageParam['query']['typeid'] = $typeid;
        }
        //商品状态
        $is_state = input('get.is_state','');     
        if($is_state){
            $where['is_state'] = $is_state;
            $pageParam['query']['is_state'] = $is_state;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['goods_name|goods_id'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('goods')->field('goods_id')->where($where)->order('sort asc,goods_id desc')->paginate(20,false,$pageParam);
        // dump($list);
        // 遍历拼接数据信息
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_goods($value['goods_id']);
            //商品分类
            foreach ($type as $k => $val) {
                if($value['typeid'] == $val['id']){$value['typename'] = $val['name'];break;}
            }
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $data[$key] = $value;
        }
        //统计商品数量
        $count = \think\Db::name('goods')->where($where)->group('is_state')->column('count(goods_id)','is_state');
        // dump($count);
        $count[0] = 0;
        for ($i=1; $i < 4 ; $i++) { 
            $count[$i] = isset($count[$i]) ? $count[$i] : 0;
            $count[0] = $count[0] + $count[$i];
        }
        //赋值数据集View模板输出  
        $param = array();
        $param['keyword'] = $keyword;
        $param['list'] = $list;
        $param['data'] = $data;
        $param['type'] = $type;
        $param['typeid'] = $typeid;
        $param['is_state'] = $is_state;
        $param['count'] = $count;
        return view('index',$param);
    }

    // 发布新商品
    public function add() {
        //验证用户权限
        Common::checkpower(24);
        if (request()->isPost()) {
            //拼接商品的信息
            $data = input('post.', '');
            unset($data['spec_list']);
            unset($data['goods_img']);
            //商品详情
            $data['goods_content'] = isset($_POST['goods_content']) ? $_POST['goods_content'] : '';
            $data['goods_content'] = htmlspecialchars($data['goods_content']);
            //随机生成商品货号
            if (empty($data['goods_sn'])) {$data['goods_sn'] = strtoupper(uniqid(8));}
            //商品图片
            $goods_img = input('post.goods_img/a', '');
            if (!$goods_img) {
                return json(array('success'=>false,'info'=>'请最少上传一张商品图片！'));exit;
            }
            foreach ($goods_img as $key => $value) {
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
                $goods_img[$key] = $value;
            }
            //默认图片为上传第一张
            $data['goods_logo'] = $goods_img[0];
            $data['goods_image'] = json_encode($goods_img,JSON_UNESCAPED_UNICODE);
            $data['create_time'] = time();
            //执行添加商品
            if ($goods_id = \think\Db::name('goods')->insertGetId($data)) {
                //记录系统日志
                admin_log("添加 ID:".$goods_id." 商品");
                //添加商品规格
                if ($data['is_spec'] == 2) {
                    //规格属性
                    $spec_list = input('post.spec_list/a', '');
                    if (!empty($spec_list)) {
                        //商品库存
                        $goods_number = 0;
                        $goods_product = array();
                        foreach ($spec_list as $key => $value) {
                            $goods_product[$key]['goods_id'] = $goods_id; 
                            $goods_product[$key]['goods_name'] = $data['goods_name']; 
                            $goods_product[$key]['goods_unit'] = $data['goods_unit'];
                            $goods_product[$key]['spec_id'] = join(',', $value['spec_id']);
                            $goods_product[$key]['spec_idlist'] = join(',', $value['spec_idlist']);
                            $goods_product[$key]['spec_value'] = join(',', $value['spec_name']);
                            $goods_product[$key]['goods_price'] = $value['goods_price'];
                            $goods_product[$key]['costs_price'] = $value['costs_price'];
                            $goods_product[$key]['market_price'] = $value['market_price'];
                            $goods_product[$key]['goods_weight'] = $value['goods_weight'];
                            $goods_product[$key]['goods_number'] = $value['goods_number'];
                            $goods_product[$key]['is_state'] = isset($value['is_state']) ? 1 : 0;
                            $goods_product[$key]['create_time'] = time();
                            //货号生成
                            if (empty($value['goods_sn'])) {
                                $goods_product[$key]['goods_sn'] = join('', $value['spec_idlist']).mt_rand(1000,9999).'-'.($key+1);
                            }else{
                                $goods_product[$key]['goods_sn'] = $value['goods_sn'];
                            }
                            //处理base64编码的图片上传
                            if(strpos($value['goods_logo'], 'data:image') !== false){
                                //创建文件夹
                                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                                }
                                //保存文件路径
                                $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                                if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $value['goods_logo'], $result)){
                                    $type = $result[2];
                                    if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                                        if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $value['goods_logo'])), FILE_USE_INCLUDE_PATH)) {
                                            return json(array('success'=>false,'info'=>'图片上传失败！'));
                                        }else{
                                            // 按照原图的比例生成一个最大为200*200的缩略图并保存
                                            $image = \think\Image::open('.'.$path);
                                            $image->thumb(800, 800)->save('.'.$path);
                                            $value['goods_logo'] = $path;
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
                            //规格图片
                            $goods_product[$key]['goods_logo'] = $value['goods_logo'];
                            //记录总库存
                            $goods_number = $goods_number + $value['goods_number'];  
                        }
                        //添加商品规格
                        \think\Db::name('goods_product')->insertAll($goods_product);
                        //记录系统日志
                        admin_log("添加 ID:".$goods_id." 商品规格");
                        //修改商品库存
                        \think\Db::name('goods')->where(array('goods_id' => $goods_id))->setField('goods_number',$goods_number);
                    }
                }
                //清除Api获取商品相关接口缓存
                \think\Cache::clear('goods');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //获取商品分类
            $type = get_goods_type();           
            $goods_type = array();
            foreach ($type as $key => $value) {
                if($value['is_state'] == 1){
                    $goods_type[] = $value;
                }
            }
            // 获取配送方式
            $freight = \think\Db::name('freight')->field('id')->where(array('is_state'=>1))->select();
            foreach($freight as $key => $value) {
                $value = get_freight($value['id']);
                $freight[$key] = $value;
            } 
            //获取商家列表
            $business = \think\Db::name('business')->field('id,name')->where(array('is_state'=>1))->select();
            //获取商品规格
            $spec = get_goods_spec();
            //拼接获取对应规格值
            foreach($spec['spec_list'] as $key => $value) {
                $spec_value = array();
                foreach ($spec['spec_value'] as $k => $val) {
                    if($value['id'] == $val['typeid']){
                        $spec_value[] = $val['value'];
                    }
                }
                $value['spec_value'] = join(',', $spec_value);
                $spec['spec_list'][$key] = $value;
            }

            //赋值数据集View模板输出  
            $data = array();
            $data['goods_type'] = $goods_type;
            $data['typelist'] = json_encode($goods_type,JSON_UNESCAPED_UNICODE);
            $data['freight'] = $freight;
            $data['business'] = $business;
            $data['spec'] = json_encode($spec,JSON_UNESCAPED_UNICODE);
            return view('add',$data);
        }
        
    }

    //修改商品信息
    public function edit() {
        //验证用户权限
        Common::checkpower(24);
        if (request()->isPost()) {
            $goods_id = input('post.goods_id', '0');
            //获取商品信息
            $goods = get_goods($goods_id);
            if (!$goods){return json(array('success'=>false,'info'=>'不存在的商品信息！'));exit;} 
            //拼接商品的信息
            $data = input('post.', '');
            unset($data['spec_list']);
            unset($data['goods_img']);
            //商品详情
            $data['goods_content'] = isset($_POST['goods_content']) ? $_POST['goods_content'] : '';
            $data['goods_content'] = htmlspecialchars($data['goods_content']);
            //随机生成商品货号
            if (empty($data['goods_sn'])) {$data['goods_sn'] = strtoupper(uniqid(8));}
            //商品图片
            $goods_img = input('post.goods_img/a', '');
            if (!$goods_img) {
                return json(array('success'=>false,'info'=>'请最少上传一张商品图片！'));exit;
            }
            foreach ($goods_img as $key => $value) {
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
                $goods_img[$key] = $value;
            }
            //默认图片为上传第一张
            $data['goods_logo'] = $goods_img[0];
            $data['goods_image'] = json_encode($goods_img,JSON_UNESCAPED_UNICODE);
            \think\Db::name('goods')->where(array('goods_id' => $goods_id))->update($data);
            //记录系统日志
            admin_log("修改 ID:".$goods_id." 商品信息");
            //规格属性
            $spec_list = input('post.spec_list/a', '');
            if (!empty($spec_list)) {
                //删除所有商品规格
                \think\Db::name('goods_product')->where(array('goods_id' => $goods_id))->delete();
                //商品库存
                $goods_number = 0;
                $goods_product = array();
                foreach ($spec_list as $key => $value) {
                    $goods_product[$key]['goods_id'] = $goods_id; 
                    $goods_product[$key]['goods_name'] = $data['goods_name']; 
                    $goods_product[$key]['goods_unit'] = $data['goods_unit'];
                    $goods_product[$key]['spec_id'] = join(',', $value['spec_id']);
                    $goods_product[$key]['spec_idlist'] = join(',', $value['spec_idlist']);
                    $goods_product[$key]['spec_value'] = join(',', $value['spec_name']);
                    $goods_product[$key]['goods_price'] = $value['goods_price'];
                    $goods_product[$key]['costs_price'] = $value['costs_price'];
                    $goods_product[$key]['market_price'] = $value['market_price'];
                    $goods_product[$key]['goods_weight'] = $value['goods_weight'];
                    $goods_product[$key]['goods_number'] = $value['goods_number'];
                    $goods_product[$key]['is_state'] = isset($value['is_state']) ? 1 : 0;
                    $goods_product[$key]['create_time'] = time();
                    //货号生成
                    if (empty($value['goods_sn'])) {
                        $goods_product[$key]['goods_sn'] = join('', $value['spec_idlist']).mt_rand(1000,9999).'-'.($key+1);
                    }else{
                        $goods_product[$key]['goods_sn'] = $value['goods_sn'];
                    }
                    //处理base64编码的图片上传
                    if(strpos($value['goods_logo'], 'data:image') !== false){
                        //创建文件夹
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                            mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                        }
                        //保存文件路径
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $value['goods_logo'], $result)){
                            $type = $result[2];
                            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $value['goods_logo'])), FILE_USE_INCLUDE_PATH)) {
                                    return json(array('success'=>false,'info'=>'图片上传失败！'));
                                }else{
                                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                                    $image = \think\Image::open('.'.$path);
                                    $image->thumb(800, 800)->save('.'.$path);
                                    $value['goods_logo'] = $path;
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
                    //规格图片
                    $goods_product[$key]['goods_logo'] = $value['goods_logo'];
                    //记录总库存
                    $goods_number = $goods_number + $value['goods_number'];  
                }
                //添加商品规格
                \think\Db::name('goods_product')->insertAll($goods_product);
                //记录系统日志
                admin_log("修改 ID:".$goods_id." 商品规格属性");
                //修改商品库存
                \think\Db::name('goods')->where(array('goods_id' => $goods_id))->setField('goods_number',$goods_number);
            }
            //修改后清除该商品缓存
            \think\Cache::rm('goods_'.$goods_id);
            \think\Cache::rm('goods_product_'.$goods_id);
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');
            return json(array('success'=>true,'info'=>'修改成功'));exit;
        }else{
            //获取商品id
            $goods_id = input('get.id','0');
            //获取商品信息
            $goods = get_goods($goods_id);
            if (!$goods){return error('不存在的商品信息！');exit;} 
            //商品规格
            $goods_spec = get_goods_product($goods_id);
            if ($goods_spec) {
                foreach($goods_spec as $key => $value) {
                    $value['spec_idlist'] = explode(',', $value['spec_idlist']);
                    $value['spec_value'] = explode(',', $value['spec_value']);
                    $goods_spec[$key] = $value;
                }
            }
            $goods_spec = json_encode($goods_spec,JSON_UNESCAPED_UNICODE);
            //获取所有商品分类
            $type = get_goods_type();
            //获取状态正常商品分类                
            $goods_type = array();
            foreach ($type as $value) {
                if($value['is_state'] == 1){
                    $goods_type[] = $value;
                }
            }
            // 获取配送方式
            $freight = \think\Db::name('freight')->field('id')->where(array('is_state'=>1))->select();
            foreach($freight as $key => $value) {
                $value = get_freight($value['id']);
                $freight[$key] = $value;
            } 
            //获取商家列表
            $business = \think\Db::name('business')->field('id,name')->where(array('is_state'=>1))->select();
            //获取商品规格
            $spec = get_goods_spec();
            //拼接获取对应规格值
            foreach($spec['spec_list'] as $key => $value) {
                $spec_value = array();
                foreach ($spec['spec_value'] as $k => $val) {
                    if($value['id'] == $val['typeid']){
                        $spec_value[] = $val['value'];
                    }
                }
                $value['spec_value'] = join(',', $spec_value);
                $spec['spec_list'][$key] = $value;
            }
            //赋值数据集View模板输出  
            $data = array();
            $data['goods'] = $goods;
            $data['goods_spec'] = $goods_spec;
            $data['goods_type'] = $goods_type;
            $data['typelist'] = json_encode($goods_type,JSON_UNESCAPED_UNICODE);
            $data['freight'] = $freight;
            $data['business'] = $business;
            $data['spec'] = json_encode($spec,JSON_UNESCAPED_UNICODE);
            return view('edit',$data);
        }
        
    }

    //修改商品状态
    public function state() {
        //验证用户权限
        Common::checkpower(24);
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('goods')->where(array('goods_id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 商品状态");
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //修改商品排序
    public function order() {
        //验证用户权限
        Common::checkpower(24);
        $id = input('post.id','0');
        $sort = input('post.sort','0');
        if (\think\Db::name('goods')->where(array('goods_id'=>$id))->setField('sort', $sort)) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 商品排序");
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除商品
    public function delete(){
        //验证用户权限
        Common::checkpower(24);
        //获取要删除的商品id
        $idlist = input('post.idlist', '0');
        if(\think\Db::name('goods')->where(array('goods_id' => array('IN', $idlist)))->delete()){
            //删除商品规格
            \think\Db::name('goods_product')->where(array('goods_id' => array('IN', $idlist)))->delete();
            $list = explode(',', $idlist);
            foreach ($list as $key => $value) {
                //修改数据后清除缓存
                \think\Cache::rm('goods_'.$value);
                //修改数据后清除缓存
                \think\Cache::rm('goods_product_'.$value);
            }
            //记录系统日志
            admin_log("删除 ID:".$idlist." 商品");
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 商品分类页面显示
    public function type(){
        //验证用户权限
        Common::checkpower(22);
        //获取所有商品分类
        $list = get_goods_type();
        return view('type',array('list'=>$list));
    }

    // 添加商品分类
    public function type_add(){
        //验证用户权限
        Common::checkpower(22);
        if (request()->isPost()) {
            //拼接添加数据
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
            //添加商品分类
            if ($id = \think\Db::name('goods_type')->insertGetId($data)) {
                //记录系统日志
                admin_log("添加 ID:".$id." 商品分类");
                //修改商品分类排序
                \think\Db::name('goods_type')->where(array('id'=>$id))->setField('sort',$id);
                //修改数据后清除缓存
                \think\Cache::rm('goods_type');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                //获取添加错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else {
            //获取商品分类
            $type = get_goods_type();
            $list = array();
            foreach ($type as $key => $value) {
                if($value['is_state'] == 1){$list[] = $value;}
            }
            //赋值数据集View模板输出 
            return view('type_add',array('list'=>$list));
        }
    }

    // 修改商品分类
    public function type_edit(){
        //验证用户权限
        Common::checkpower(22);
        if (request()->isPost()) {
            // 拼接商品分类信息
            $id = input('post.id','0');
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
            //修改商品分类
            if (\think\Db::name('goods_type')->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('goods_type');
                //记录系统日志
                admin_log("修改 ID:".$id." 商品分类");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            //获取要修改商品分类id
            $id = input('get.id', '0');
            //获取商品分类
            $type = get_goods_type();
            $list = array();
            $typelist = array();
            foreach ($type as $key => $value) {
                if($value['id'] == $id){
                    $list = $value;
                }
                if($value['is_state'] == 1){
                    $typelist[] = $value;
                }
            }
            if (!$list){return error('不存在的数据！');exit;}
            $list['type'] = get_child($typelist,$list['id']);
            $list['typelist'] = array($list['id']);
            foreach ($list['type'] as $key => $value) {
                array_push($list['typelist'], $value['id']);
            }
            //赋值数据集View模板输出 
            return view('type_edit',array('typelist'=>$typelist,'list'=>$list));
        }
    }

    // 删除商品分类
    public function type_del(){
        //验证用户权限
        Common::checkpower(22);
        // 要删除的记录id
        $idlist = input('post.idlist','');
        //判断是否有下级
        if(\think\Db::name('goods_type')->where(array('pid'=>array('IN',$idlist)))->count() > 0){
            return json(array('success'=>false,'info'=>'不能删除,分类拥有子类！'));exit;
        }
        if(\think\Db::name('goods')->where(array('typeid'=>array('IN',$idlist)))->count() > 0){
            return json(array('success'=>false,'info'=>'删除失败,分类下有商品！'));exit;
        }
        //删除商品分类
        if (\think\Db::name('goods_type')->where(array('id'=>array('IN',$idlist)))->delete()) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_type');
            //记录系统日志
            admin_log("删除 ID:".$idlist." 商品分类");
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            //获取删除错误原因
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改商品分类状态
    public function type_state() {
        //验证用户权限
        Common::checkpower(22);
        //获取修改商品分类id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('goods_type')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_type');
            //记录系统日志
            admin_log("修改 ID:".$id." 商品分类状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //修改商品分类首页显示
    public function type_hot() {
        //验证用户权限
        Common::checkpower(22);
        //获取修改商品分类id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('goods_type')->where(array('id'=>$id))->setField('is_hot', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_type');
            //记录系统日志
            admin_log("修改 ID:".$id." 商品分类首页显示");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 商品分类排序修改
    public function type_order(){
        //验证用户权限
        Common::checkpower(22);
        //获取要修改分类id
        $id = input('post.id', '0');
        //获取要排序字段
        $order = input('post.order', 'desc');
        //获取商品分类
        $type = get_goods_type();
        $list = array();
        foreach ($type as $key => $value) {
            if($value['id'] == $id){$list = $value;break;}
        }
        if (!$list){return json(array('success'=>false,'info'=>'不存在的数据！'));exit;}
        $desc = 0;
        //商品排序信息
        $sort = array();
        foreach ($type as $key => $value) {
            if($value['pid'] == $list['pid']){
                if ($order == 'desc') {
                    if($value['sort'] < $list['sort']){
                        if($value['sort']>$desc){
                            $desc = $value['sort'];
                            $sort = $value;
                        }
                    }
                }else {
                    if($value['sort'] > $list['sort']){
                        if(!isset($asc)){
                            $asc = $value['sort'];
                            $sort = $value;
                        }else if($value['sort']<$asc){
                            $asc = $value['sort'];
                            $sort = $value;
                        }
                    }
                }
            }
        }
        //修改商品分类排序
        if ($sort) {
            \think\Db::name('goods_type')->where(array('id' => $list['id']))->setField('sort', $sort['sort']);
            \think\Db::name('goods_type')->where(array('id' => $sort['id']))->setField('sort', $list['sort']);
            //修改数据后清除缓存
            \think\Cache::rm('goods_type');
            //记录系统日志
            admin_log("修改 ID:".$id." 商品分类排序");
        }
        return json(array('success'=>true,'info'=>'修改成功！'));
    }

    // 商品规格管理
    public function spec(){
        //验证用户权限
        Common::checkpower(23);
        //获取商品规格
        $spec = get_goods_spec();
        $list = $spec['spec_list'];
        //拼接获取对应规格值
        foreach($list as $key => $value) {
            $spec_value = array();
            foreach ($spec['spec_value'] as $k => $val) {
                if($value['id'] == $val['typeid']){
                    $spec_value[] = $val['value'];
                }
            }
            $value['spec_value'] = join(',', $spec_value);
            $list[$key] = $value;
        }
        return view('spec',array('list'=>$list,'spec'=>$spec));
    }

    // 添加商品规格
    public function spec_add(){
        //验证用户权限
        Common::checkpower(23);
        if (request()->isPost()) {
            //拼接添加商品规格信息
            $data = input('post.','');
            $spec_value = input('post.spec_value/a','');
            if(!$spec_value){return json(array('success'=>false,'info'=>'请至少添加一个属性值！'));exit;}
            unset($data['spec_value']);
            //添加商品规格
            if ($id = \think\Db::name('goods_spec')->insertGetId($data)) {
                //记录系统日志
                admin_log("添加 ID:".$id." 商品规格");
                //修改商品规格排序
                \think\Db::name('goods_spec')->where(array('id'=>$id))->setField('sort',$id);
                $list = array();
                foreach ($spec_value as $key => $value) {
                    $list[] = array(
                        'typeid' => $id,
                        'value' => $value,
                        'sort' => $key
                    );
                }
                //添加商品规格值
                \think\Db::name('goods_spec_value')->insertAll($list);
                //记录系统日志
                admin_log("添加 ID:".$id." 商品规格值");
                //修改后清除缓存
                \think\Cache::rm('goods_spec');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                //获取添加错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else{
            return view('spec_add');
        }
    } 

    // 修改商品规格
    public function spec_edit(){
        //验证用户权限
        Common::checkpower(23);
        if (request()->isPost()) {
            // 拼接商品规格信息
            $id = input('post.id','0');
            $data = input('post.','');
            $spec_value = input('post.spec_value/a','');
            unset($data['spec_value']);
            //修改商品规格
            $id = input('post.id','0');
            \think\Db::name('goods_spec')->where(array('id'=>$id))->update($data);
            //记录系统日志
            admin_log("修改 ID:".$id." 商品规格");
            if ($spec_value) {
                //删除值列表
                \think\Db::name('goods_spec_value')->where(array('typeid'=>$id))->delete();
                $list = array();
                foreach ($spec_value as $key => $value) {
                    $list[] = array(
                        'typeid' => $id,
                        'value' => $value,
                        'sort' => $key
                    );
                }
                //添加商品规格值
                \think\Db::name('goods_spec_value')->insertAll($list);
                //记录系统日志
                admin_log("修改 ID:".$id." 商品规格值");
            }
            //修改后清除缓存
            \think\Cache::rm('goods_spec');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else{
            //获取要修改的商品规格id
            $id = input('get.id', '0');
            //获取商品规格
            $spec = get_goods_spec();
            $list = array();
            foreach ($spec['spec_list'] as $value) {
                if($value['id'] == $id){$list = $value;break;}
            }
            if (!$list){return error('请求错误！');exit;}
            //获取商品规格值
            $list['spec_value'] = array();
            foreach ($spec['spec_value'] as $value) {
                if($value['typeid'] == $id){
                    $list['spec_value'][] = $value;
                }
                
            }
            //赋值数据集View模板输出
            return view('spec_edit',array('list'=>$list));
        }
    } 

    //修改商品规格状态
    public function spec_state() {
        //验证用户权限
        Common::checkpower(23);
        //获取修改商品分类id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('goods_spec')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('goods_spec');
            //记录系统日志
            admin_log("修改 ID:".$id." 商品规格状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 商品规格排序修改
    public function spec_order(){
        //验证用户权限
        Common::checkpower(23);
        //获取要修改的商品规格id
        $id = input('post.id', '0');
        //获取要修改的字段值
        $order = input('post.order', 'desc');
        //获取商品规格
        $spec = get_goods_spec();
        $list = array();
        foreach ($spec['spec_list'] as $value) {
            if($value['id'] == $id){$list = $value;break;}
        }
        if (!$list){return json(array('success'=>false,'info'=>'不存在的数据！'));exit;}
        $desc = 0;
        //商品规格排序信息
        $sort = array();
        foreach ($spec['spec_list'] as $value) {
            if ($order == 'desc') {
                if($value['sort'] < $list['sort']){
                    if($value['sort'] > $desc){
                        $desc = $value['sort'];
                        $sort = $value;
                    }
                }
            }else {
                if($value['sort'] > $list['sort']){
                    if(!isset($asc)){
                        $asc = $value['sort'];
                        $sort = $value;
                    }else if($value['sort'] < $asc){
                        $asc = $value['sort'];
                        $sort = $value;
                    }
                }
            }
        }
        //修改商品规格排序
        if($sort) {
            \think\Db::name('goods_spec')->where(array('id' => $list['id']))->setField('sort', $sort['sort']);
            \think\Db::name('goods_spec')->where(array('id' => $sort['id']))->setField('sort', $list['sort']);
            //修改后清除缓存
            \think\Cache::rm('goods_spec');
            //记录系统日志
            admin_log("修改 ID:".$id." 商品规格排序");
        }
        return json(array('success'=>true,'info'=>'修改成功！'));
    }

    // 删除商品规格
    public function spec_del(){
        //验证用户权限
        Common::checkpower(23);
        //获取要删除的商品规格id
        $idlist = input('post.idlist', '0');
        //删除商品规格
        if (\think\Db::name('goods_spec')->where(array('id'=>array('IN', $idlist)))->delete()) {
            //删除值列表
            \think\Db::name('goods_spec_value')->where(array('typeid'=>array('IN', $idlist)))->delete();
            //修改后清除缓存
            \think\Cache::rm('goods_spec');
            //记录系统日志
            admin_log("删除 ID:".$idlist." 商品规格");
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            //获取删除错误原因
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}