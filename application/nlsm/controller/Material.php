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

class Material extends Common
{
    // 编辑器上传
    public function controlupload() {
        //为防止上传错误，关闭trace信息
        $action = input('get.action', '');
        switch ($action) {
            case 'config':
                $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("public/plugins/ueditor/php/config.json")), true);
                return json($config);exit;
                break;
            //涂鸦上传
            case 'uploadscrawl':
                break;
            //上传图片
            case 'uploadimage':
                if (!isset($_FILES['upfile'])) {return json(array('state'=>'ERROR','info'=>'上传文件不存在！'));exit;}
                //上传文件
                $file = $_FILES['upfile'];
                //上传文件限制格式
                $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
                if ($file['error'] > 0 || !in_array($file['type'],$type)) {
                    return json(array('state'=>'ERROR','info'=>'文件上传格式错误！'));exit;
                }
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                //保存文件
                $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){   
                    // 按照原图的比例生成一个最大为200*200的缩略图并保存
                    // $image = \think\Image::open('.'.$path);
                    // $image->thumb(500, 500)->save('.'.$path);

                    // 上传成功 获取上传文件信息
                    $data = array();
                    $data['filename'] = $file['name'];
                    $data['filepath'] = $path;
                    $data['filesize'] = $file['size'];
                    $data['filetype'] = strtolower($file['type']);
                    $data['create_time'] = time();
                    $data['action'] = 'listimage';
                    //添加文件
                    \think\Db::name('file')->insert($data);
                    //记录系统日志
                    admin_log('上传 '.$data['filename'].'文件');
                    return json(array('state'=>'SUCCESS','info'=>'上传成功！','url'=>$path));exit;
                }else{
                    //上传错误提示错误信息
                    return json(array('state'=>'ERROR','info'=>'上传失败！'));exit;
                }
                break;
            //上传文件
            case 'uploadfile':
                break;
            case 'uploadvideo':
                if (!isset($_FILES['upfile'])) {return json(array('state'=>'ERROR','info'=>'上传文件不存在！'));exit;}
                //上传文件
                $file = $_FILES['upfile'];
                // 上传文件限制格式
                $filetype = substr(strrchr($file["name"], '.'), 1);
                $type = array('mp4', 'flv', 'wmv', 'rmvb', 'avi', 'mov', 'mkv' ,'3gp');
                if ($file['error'] > 0 || !in_array($filetype,$type)) {
                    return json(array('state'=>'ERROR','info'=>'文件上传格式错误！'));exit;
                }
                //创建文件夹
                if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
                }
                //保存文件
                $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.'.$filetype;
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){   
                    // 上传成功 获取上传文件信息
                    $data = array();
                    $data['filename'] = $file['name'];
                    $data['filepath'] = $path;
                    $data['filesize'] = $file['size'];
                    $data['filetype'] = strtolower($file['type']);
                    $data['create_time'] = time();
                    $data['action'] = 'listvideo';
                    //添加文件
                    \think\Db::name('file')->insert($data);
                    //记录系统日志
                    admin_log('上传 '.$data['filename'].'文件');
                    return json(array('state'=>'SUCCESS','info'=>'上传成功！','url'=>$path));exit;
                }else{
                    //上传错误提示错误信息
                    return json(array('state'=>'ERROR','info'=>'上传失败！'));exit;
                }
                break;
            //列出图片或文件
            case 'listimage':
            case 'listfile':
                $start = input('get.start','0');
                //查询满足要求的总记录数
                $count = \think\Db::name('file')->count();
                $list = \think\Db::name('file')->field('filepath url')->where(array('action'=>$action))->limit($start,20)->select();
                return json(array('state'=>'SUCCESS','info'=>'','total'=>$count,'start'=>$start,'list'=>$list));
                break;
            default:
                return json(array('state'=>'ERROR','info'=>'请求地址出错！'));
                break;
        }
    }

    // 下载文件
    public function download() {
        //获取要下载的文件
        $filename = input('get.filename', '0');
        $filename = parse_url($filename)['path'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$filename)) {
            $filename = iconv("utf-8","gb2312", $_SERVER['DOCUMENT_ROOT'].$filename);
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=".basename($filename));  
            readfile($filename);
        }else {
            return error('文件不存在！');exit;
        }
    }
}