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

class Config extends Common
{
	//基本设置
    public function index()
    {
        //验证用户权限
        Common::checkpower(3);
		if(request()->isPost()){
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
	            $path = "/public/image/logo.png";
	            if(move_uploaded_file($file["tmp_name"], '.'.$path)){  
					// 按照原图的比例生成一个最大为200*200的缩略图并保存
		   //          $image = \think\Image::open('.'.$path);
					// $image->thumb(400, 400)->save('.'.$path);
	                // 上传成功 获取上传文件信息
	                $data['logo'] = $path;
                    //清除浏览器缓存
                    header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );  
                    header("Cache-Control: no-cache, must-revalidate" );
	            }else{
	                //ajax返回上传错误提示错误信息
	                return json(array('success'=>false,'info'=>'文件上传错误！'));exit;
	            }
            }
        	if(\think\Db::name('config')->where(array('name'=>'basic'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))){
                admin_log('修改网站基本设置信息');
                //修改数据后清除缓存
                \think\Cache::rm('config_basic');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                if(isset($path)){return json(array('success'=>true,'info'=>'修改成功！'));}
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
    	}else{
    		//获取平台设置信息
            $list = get_config('basic');
            return view('index',array('list'=>$list));
    	}
    }

    //通知设置
    public function notice()
    {
        //验证用户权限
        Common::checkpower(3);
    	if(request()->isPost()){
    		//接收输入数据
        	$data = input('post.','');
        	if(\think\Db::name('config')->where(array('name'=>'notice'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))){
                admin_log('修改网站通知设置信息');
                //修改数据后清除缓存
                \think\Cache::rm('config_notice');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
    	}else{
    		//获取通知设置信息
            $list = get_config('notice');
            return view('notice',array('list'=>$list));
    	}
    }

    //用户设置
    public function user()
    {
        //验证用户权限
        Common::checkpower(3);
        if(request()->isPost()){
            //接收输入数据
            $data = input('post.','');
            if(\think\Db::name('config')->where(array('name'=>'user'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))){
                admin_log('修改网站用户设置信息');
                //修改数据后清除缓存
                \think\Cache::rm('config_user');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            //获取用户设置信息
            $list = get_config('user');
            return view('user',array('list'=>$list));
        }
    }

    //热门搜索名称
    public function search()
    {
        //验证用户权限
        Common::checkpower(38);
        if(request()->isPost()){
            //接收输入数据
            $spec_value = input('post.spec_value/a','');
            if(!$spec_value){return json(array('success'=>false,'info'=>'请至少添加一个词语！'));exit;}
            $data = array();
            foreach ($spec_value as $key => $value) {
                $data[] = array(
                    'value' => $value,
                    'sort' => $key
                );
            }
            if(\think\Db::name('config')->where(array('name'=>'search'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))){
                admin_log('修改热门搜索名称信息');
                //修改数据后清除缓存
                \think\Cache::rm('config_search');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            //热门搜索名称
            $list = get_config('search');
            return view('search',array('list'=>$list));
        }
    }

}
