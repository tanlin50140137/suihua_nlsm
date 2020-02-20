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

class Payment extends Common
{
    //支付设置
    public function index() {
        //验证用户权限
        Common::checkpower(4);
        $list = \think\Db::name('payment')->order('sort asc')->column('name');
        if($list){
            foreach ($list as $key => $value) {
                $value = get_payment($value);
                $list[$key] = $value;
            }
        }
        return view('index',array('list'=>$list));
    }

    //修改支付方式排序
    public function sort(){
        //验证用户权限
        Common::checkpower(4);
        //获取修改支付方式
        $name = input('post.name', '');
        $order = input('post.order', 'desc');
        //查找数据
        $list = get_payment($name);
        if (!$list){return json(array('success'=>false,'info'=>'不存在的数据！'));exit;}
        if ($order == 'desc') {
            $sort = \think\Db::name('payment')->field('id,sort,name')->where(array('sort'=>array('LT',$list['sort'])))->order('sort desc')->find();
        }else {
            $sort = \think\Db::name('payment')->field('id,sort,name')->where(array('sort'=>array('GT',$list['sort'])))->order('sort asc')->find();
            
        }
        if ($sort) {
            \think\Db::name('payment')->where(array('id' => $list['id']))->setField('sort', $sort['sort']);
            \think\Db::name('payment')->where(array('id' => $sort['id']))->setField('sort', $list['sort']);
            //修改数据后清除缓存
            \think\Cache::rm('payment_'.$name);
            \think\Cache::rm('payment_'.$sort['name']);
            //记录系统日志
            admin_log("修改 ".$name." 支付方式排序");
        }
        return json(array('success'=>true,'info'=>'修改成功！'));
    }

    //修改支付方式状态
    public function state() {
        //验证用户权限
        Common::checkpower(4);
        $name = input('post.name','');
        $state = input('post.state','2');
        if (\think\Db::name('payment')->where(array('name'=>$name))->setField('is_state', $state)) {
            //修改数据后清除缓存
            \think\Cache::rm('payment_'.$name);
            //记录系统日志
            admin_log("修改 ".$name." 支付方式状态");
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //微信支付方式配置
    public function wxpay() {
        //验证用户权限
        Common::checkpower(4);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            //上传文件
            $file = isset($_FILES['sslcert']) ? $_FILES['sslcert'] : '';
            if ($file && $file['error'] == 0) {
                if($file['type'] != 'application/x-zip-compressed'){return json(array('success'=>false,'info'=>'文件上传格式错误！'));exit;}
                //删除旧文件
                deldir($_SERVER['DOCUMENT_ROOT'].'/public/cert/sslcert/');
                //保存文件
                $path = "/public/cert/sslcert/cert.zip";
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){  
                    //生成的压缩包位置
                    $zipurl = $_SERVER['DOCUMENT_ROOT'].'/public/cert/sslcert/';
                    //实例化类库解压压缩包
                    require_once("./extend/phpzip.php");
                    $zip = new \PHPZip();
                    $zip->unzip($zipurl.'cert.zip',$zipurl, true, false);     
                    $data['sslcert'] = $path;
                }
            }
            //上传文件
            $file = isset($_FILES['opensslcert']) ? $_FILES['opensslcert'] : '';
            if ($file && $file['error'] == 0) {
                if($file['type'] != 'application/x-zip-compressed'){return json(array('success'=>false,'info'=>'文件上传格式错误！'));exit;}
                //删除旧文件
                deldir($_SERVER['DOCUMENT_ROOT'].'/public/cert/opensslcert/');
                //保存文件
                $path = "/public/cert/opensslcert/cert.zip";
                if(move_uploaded_file($file["tmp_name"], '.'.$path)){  
                    //生成的压缩包位置
                    $zipurl = $_SERVER['DOCUMENT_ROOT'].'/public/cert/opensslcert/';
                    //实例化类库解压压缩包
                    require_once("./extend/phpzip.php");
                    $zip = new \PHPZip();
                    $zip->unzip($zipurl.'cert.zip',$zipurl, true, false);     
                    $data['opensslcert'] = $path;
                }
            }
            if (\think\Db::name('payment')->where(array('name'=>'wxpay'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))) {
                //修改数据后清除缓存
                \think\Cache::rm('payment_wxpay');
                //记录系统日志
                admin_log("修改 wxpay 支付方式配置信息");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                if(isset($path)){return json(array('success'=>true,'info'=>'修改成功！'));exit;}
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else {
            //查找数据
            $list = get_payment('wxpay');
            return view('wxpay',array('list'=>$list));
        }
    }

    //支付宝支付方式配置
    public function alipay() {
        //验证用户权限
        Common::checkpower(4);
        if (request()->isPost()) {
            //接收输入数据
            $data = input('post.','');
            if (\think\Db::name('payment')->where(array('name'=>'alipay'))->setField('value',json_encode($data,JSON_UNESCAPED_UNICODE))) {
                //修改数据后清除缓存
                \think\Cache::rm('payment_alipay');
                //记录系统日志
                admin_log("修改 alipay 支付方式配置信息");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else {
            //查找数据
            $list = get_payment('alipay');
            return view('alipay',array('list'=>$list));
        }
    }
}