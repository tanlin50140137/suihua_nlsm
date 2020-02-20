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

class Setmeal extends Common
{
    // 赠送套餐
    public function index(){
        //验证用户权限
        Common::checkpower(48);
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
        $list = \think\Db::name('setmeal')->field(true)->where($where)->order('id desc')->paginate(20,false,$pageParam);
        $data = $list->all();
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加套餐
    public function add(){
        //验证用户权限
        Common::checkpower(48);
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

            //套餐产品设置
            $data['goodsid'] = input('post.goodsid');
            $data['goodsid'] = $data['goodsid'] ? explode(',', $data['goodsid']) : array();
            $data['goodslist'] = input('post.goodslist');
            $data['goodslist'] = $data['goodslist'] ? explode(',', $data['goodslist']) : array();
            if(!empty($data['goodslist'])){
                $data['goodslist'] = array_combine($data['goodsid'],$data['goodslist']);
            }
            unset($data['goodsid']);
            $data['goodslist'] = json_encode($data['goodslist'],JSON_UNESCAPED_UNICODE);

            if (\think\Db::name('setmeal')->insert($data)) {
            
                //记录系统日志
                admin_log('添加 '.$data['name'].' 套餐');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //赋值数据集View模板输出  
            $goods = \think\Db::name('setmeal_goods')->field(true)->select();
            //赋值数据集View模板输出  
            return view('add',array('goods'=>$goods));
        }
    }

    // 修改套餐
    public function edit(){
        //验证用户权限
        Common::checkpower(48);
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

            //套餐产品设置
            $data['goodsid'] = input('post.goodsid');
            $data['goodsid'] = $data['goodsid'] ? explode(',', $data['goodsid']) : array();
            $data['goodslist'] = input('post.goodslist');
            $data['goodslist'] = $data['goodslist'] ? explode(',', $data['goodslist']) : array();
            if(!empty($data['goodslist'])){
                $data['goodslist'] = array_combine($data['goodsid'],$data['goodslist']);
            }
            unset($data['goodsid']);
            $data['goodslist'] = json_encode($data['goodslist'],JSON_UNESCAPED_UNICODE);

            if (\think\Db::name('setmeal')->where(array('id'=>$id))->update($data)) {
                //记录系统日志
                admin_log('修改 '.$data['name'].' 套餐');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            //获取修改商家id
            $id = input('get.id', 0);
            $list = \think\Db::name('setmeal')->where(array('id'=>$id))->find();
            $list['goodslist'] = json_decode($list['goodslist'],true);

            $goods = \think\Db::name('setmeal_goods')->field(true)->select();

            if($goods){
                foreach ($goods as $key => $value) {
                    $value['goodslist'] = isset($list['goodslist']['goods_'.$value['id']]) ? $list['goodslist']['goods_'.$value['id']] :  '';
                    $goods[$key] = $value;
                }
            }

            //赋值数据集View模板输出  
            return view('edit',array('list'=>$list,'goods'=>$goods));
        }
    }

    //删除套餐
    public function delete(){
        //验证用户权限
        Common::checkpower(48);
        //获取要删除商家id
        $idlist = input('post.idlist', '');
        //删除商家
        if (\think\Db::name('setmeal')->where(array('id'=>array('IN',$idlist)))->delete()) {
          
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 套餐');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //修改套餐状态
    public function state() {
        //验证用户权限
        Common::checkpower(48);
        //获取修改会员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('setmeal')->where(array('id'=>$id))->setField('is_state', $state)) {
           
            //记录系统日志
            admin_log("修改 ID:".$id." 套餐状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 套餐商品
    public function goods(){
        //验证用户权限
        Common::checkpower(51);
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
        $list = \think\Db::name('setmeal_goods')->field(true)->where($where)->paginate(20,false,$pageParam);
        $data = $list->all();
        //赋值数据集View模板输出  
        return view('goods',array('keyword'=>$keyword,'list'=>$list,'data'=>$data));
    }

    // 添加套餐商品
    public function goods_add(){
        //验证用户权限
        Common::checkpower(51);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            $data['name'] = input('post.name', '');
            if(!$data['name']){return json(array('success'=>false,'info'=>'请输入商品名称！'));exit;}
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
            if (\think\Db::name('setmeal_goods')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['name'].' 套餐商品');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            //赋值数据集View模板输出  
            return view('goods_add');
        }
    }

    // 修改套餐商品
    public function goods_edit(){
        //验证用户权限
        Common::checkpower(51);
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
            if (\think\Db::name('setmeal_goods')->where(array('id'=>$id))->update($data)) {
                //记录系统日志
                admin_log('修改 '.$data['name'].' 套餐商品');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            }
        }else{
            //获取修改商家id
            $id = input('get.id', 0);
            $list = \think\Db::name('setmeal_goods')->where(array('id'=>$id))->find();
            
            //赋值数据集View模板输出  
            return view('goods_edit',array('list'=>$list));
        }
    }

    //删除套餐商品
    public function goods_del(){
        //验证用户权限
        Common::checkpower(51);
        //获取要删除套餐商品id
        $idlist = input('post.idlist', '');
        //删除套餐商品
        if (\think\Db::name('setmeal_goods')->where(array('id'=>array('IN',$idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 套餐商品');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}