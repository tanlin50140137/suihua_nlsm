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

class Picture extends Common {

    // 获取所有广告图片
    public function index() {
        //验证用户权限
        Common::checkpower(27);
        // 查询广告图片数据
        $list = get_picture();
        if ($list) {
            foreach($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $list[$key] = $value;
            }
        }
        return view('index',array('list'=>$list));
    }

    // 添加广告图片
    public function add() {
        //验证用户权限
        Common::checkpower(27);
        if (request()->isPost()) {
            //拼接数据
            $data = array();
            //广告名称过滤
            $data['name'] = input('post.name','');
            if(!$data['name']){
                return json(array('success'=>false,'info'=>'请输入广告名称！'));exit;
            }
            //广告图片列表信息
            $data['image'] = input('post.image/a','');
            if($data['image']){
                foreach ($data['image'] as $key => $value) {
                    //处理base64编码的图片上传
                    if(strpos($value['logo'], 'data:image') !== false){
                        //创建文件夹
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                            mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                        }
                        //保存文件路径
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $value['logo'], $result)){
                            $type = $result[2];
                            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $value['logo'])), FILE_USE_INCLUDE_PATH)) {
                                    return json(array('success'=>false,'info'=>'图片上传失败！'));
                                }else{
                                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                                    $image = \think\Image::open('.'.$path);
                                    $image->thumb(800, 800)->save('.'.$path);
                                    $value['logo'] = $path;
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
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);
            $data['create_time'] = time();
            //添加广告图片
            if (\think\Db::name("picture")->insert($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('picture');
                //记录系统日志
                admin_log('添加 '.$data['name'].' 广告图片');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else {
            return view('add');
        }
    }

    // 修改广告图片
    public function edit() {
        //验证用户权限
        Common::checkpower(27);
        if (request()->isPost()) {
            //拼接数据
            $data = array();
            //广告名称过滤
            $data['name'] = input('post.name','');
            if(!$data['name']){
                return json(array('success'=>false,'info'=>'请输入广告名称！'));exit;
            }
            //广告图片列表信息
            $data['image'] = input('post.image/a','');
            if($data['image']){
                foreach ($data['image'] as $key => $value) {
                    //处理base64编码的图片上传
                    if(strpos($value['logo'], 'data:image') !== false){
                        //创建文件夹
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                            mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                        }
                        //保存文件路径
                        $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $value['logo'], $result)){
                            $type = $result[2];
                            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $value['logo'])), FILE_USE_INCLUDE_PATH)) {
                                    return json(array('success'=>false,'info'=>'图片上传失败！'));
                                }else{
                                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                                    $image = \think\Image::open('.'.$path);
                                    $image->thumb(800, 800)->save('.'.$path);
                                    $value['logo'] = $path;
                                }
                            }else{
                                //文件类型错误
                                return json(array('success'=>false,'info'=>'图片上传类型错误!'));
                            }
                         
                        }else{
                            //文件错误
                            return json(array('success'=>false,'info'=>'文件错误!'));
                        }
                    }
                    $data['image'][$key] = $value;
                }
            }
            $data['image'] = json_encode($data['image'],JSON_UNESCAPED_UNICODE);
            //修改广告图片
            $id = input('post.id','0');
            if(\think\Db::name('picture')->where(array('id'=>$id))->update($data)){
                //修改数据后清除缓存
                \think\Cache::rm('picture');
                //记录系统日志
                admin_log('修改 ID:'.$id.' 广告图片信息');
                return json(array('success'=>true,'info'=>'修改成功！'));exit;
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));exit;
            } 
        }else {
            $id = input('get.id', '0');
            // 查询广告图片数据
            $picture = get_picture();
            //查找数据
            foreach ($picture as $key => $value) {
                if($value['id'] == $id){$list = $value;break;}
            }
            if (!isset($list)){return error('不存在的广告图片数据！');exit;}
            return view('edit',array('list'=>$list));
        }
    }

    // 删除广告图片
    public function delete() {
        //验证用户权限
        Common::checkpower(27);
        $idlist = input('post.idlist', '');
        if (in_array($idlist,array('1','2','3'))){return json(array('success'=>false,'info'=>'系统广告图片不能删除！'));exit;} 
        if (\think\Db::name('picture')->where(array('id' => $idlist))->delete()) {
            // 修改数据后清除缓存
            \think\Cache::rm('picture');
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 广告图片');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            //获取修改错误原因
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}