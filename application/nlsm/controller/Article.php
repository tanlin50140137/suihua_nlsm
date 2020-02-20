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

class Article extends Common
{
   // 显示文章列表页面
    public function index(){
        //验证用户权限
        Common::checkpower(29);
        //拼接条件
        $where = array();
        $pageParam = ['query' =>[]];
        //文章分类   
        $typeid = input('get.typeid','0'); 
        if($typeid){
            $where['typeid'] = $typeid;
            $pageParam['query']['typeid'] = $typeid;
        }
        //输入搜索内容
        $keyword = input('get.keyword','');     
        if($keyword){
            $where['title|author'] = array('LIKE', "%{$keyword}%");
            $pageParam['query']['keyword'] = $keyword;
        }

        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('article')->field('id')->where($where)->order('sort')->paginate(20,false,$pageParam);
        //获取文章分类
        $type = get_article_type();
        $data = $list->all();
        foreach ($data as $key => $value) {
            $value = get_article($value['id']);
            // 格式化时间
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['typename'] = '';
            //转换文章对应的分类名称
            foreach ($type as $k => $val) {
                if($value['typeid'] == $val['id']){$value['typename'] = $val['name'];break;}
            }
            $data[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('index',array('keyword'=>$keyword,'list'=>$list,'data'=>$data,'type'=>$type,'typeid'=>$typeid));
    }

    // 添加文章
    public function add(){
        //验证用户权限
        Common::checkpower(29);
        if (request()->isPost()) {
            //接收输入数据
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
            $data['create_time'] = time();
            if (\think\Db::name('article')->insert($data)) {
                //记录系统日志
                admin_log('添加 '.$data['title'].' 文章');
                //清除Api获取文章相关接口缓存
                \think\Cache::clear('article');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            $type = get_article_type();
            return view('add',array('type'=>$type));
        }
    }

    // 修改文章信息
    public function edit(){
        //验证用户权限
        Common::checkpower(29);
        //判断修改操作还是显示修改页面
        if (request()->isPost()) {
            //要修改文章id
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
            //修改文章
            if (\think\Db::name('article')->where(array('id'=>$id))->update($data)) {
                 //修改数据后清除缓存
                \think\Cache::rm('article_'.$id);
                //记录系统日志
                admin_log('修改 ID:'.$id.' 文章信息');
                //清除Api获取文章相关接口缓存
                \think\Cache::clear('article');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            $type = get_article_type();
            $id = input('get.id', '0');
            $list = get_article($id);
            //赋值数据集View模板输出  
            return view('edit',array('list'=>$list,'type'=>$type));
        }
    }

    //修改文章状态
    public function state() {
        //验证用户权限
        Common::checkpower(29);
        //获取修改会员id
        $id = input('post.id','0');
        $state = input('post.state','0');
        if (\think\Db::name('article')->where(array('id'=>$id))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('article_'.$id);
            //记录系统日志
            admin_log("修改 ID:".$id." 文章状态");
            //清除Api获取文章相关接口缓存
            \think\Cache::clear('article');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 删除文章
    public function delete(){
        //验证用户权限
        Common::checkpower(29);
        $idlist = input('post.idlist','0');
        if(\think\Db::name('article')->where(array('id' => array('IN', $idlist)))->delete()) {
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 文章');
            //清除Api获取文章相关接口缓存
            \think\Cache::clear('article');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    // 显示文章分类
    public function type(){
        //验证用户权限
        Common::checkpower(28);
        //获取文章分类
        $list = get_article_type();
        return view('type',array('list'=>$list));
    }

    // 添加文章分类
    public function type_add(){
        //验证用户权限
        Common::checkpower(28);
        if (request()->isPost()) {
            //接收输入数据
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

            if (\think\Db::name('article_type')->insert($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('article_type');
                //记录系统日志
                admin_log('添加 '.$data['name'].' 文章分类');
                return json(array('success'=>true,'info'=>'添加成功！'));exit;
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));exit;
            }
        }else{
            return view('type_add');
        }
    }

    // 修改文章信息
    public function type_edit(){
        //验证用户权限
        Common::checkpower(29);
        //判断修改操作还是显示修改页面
        if (request()->isPost()) {
            //要修改文章id
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
            //修改文章
            if (\think\Db::name('article_type')->where(array('id'=>$id))->update($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('article_type');
                //记录系统日志
                admin_log('修改 ID:'.$id.' 文章信息');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            $type = get_article_type();
            $id = input('get.id', '0');
            $list = array();
            foreach ($type as $key => $value) {
               if($value['id'] == $id){$list = $value;break;}
            }
            //赋值数据集View模板输出  
            return view('type_edit',array('list'=>$list,'type'=>$type));
        }
    }
    
    // 删除文章分类
    public function type_del(){
        //验证用户权限
        Common::checkpower(28);
        //删除栏目id
        $idlist = input('post.idlist', '0');
        //判断栏目下是否有文章
        if(\think\Db::name('article')->where(array('typeid'=>array('IN', $idlist)))->count() > 0){
            return json(array('success'=>false,'info'=>'删除失败，栏目下有文章！'));exit;
        }
        if (\think\Db::name('article_type')->where(array('id'=>array('IN', $idlist)))->delete()) {
            //修改数据后清除缓存
            \think\Cache::rm('article_type');
            //记录系统日志
            admin_log('删除 ID:'.$idlist.' 文章分类');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}
