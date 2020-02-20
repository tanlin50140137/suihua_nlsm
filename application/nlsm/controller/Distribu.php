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

class Distribu extends Common
{
	//地区管理
	public function region() {
        //验证用户权限
        Common::checkpower(30);
        $parent_id = input('get.parent_id', '0');
        $list = get_parent_region($parent_id);
        $result = array();
        if ($parent_id) {
            $result = get_region($parent_id);
        }else {
            $result['region_name'] = '顶级地区';
            $result['region_type'] = -1;
        }
        return view('region',array('result'=>$result,'list'=>$list,'parent_id'=>$parent_id));
    }

    //新增地区
    public function region_add(){
        //验证用户权限
        Common::checkpower(30);
        $data = array();
        $data['region_name'] = input('post.region_name', '');
        if(!$data['region_name']){return json(array('success'=>false,'info'=>'请填写地区名称！'));exit;}
        $data['region_type'] = input('post.region_type','');
        $data['parent_id'] = input('post.parent_id','0');
        //自动创建并验证数据
        if (\think\Db::name('region')->insert($data)) {
            //修改后清除缓存
            \think\Cache::rm('parent_region_'.$data['parent_id']);
            //记录系统日志
            admin_log("添加地区：{$data['region_name']}");
            //修改后自动生成js文件
            $this->generate();
            return json(array('success'=>true,'info'=>'添加成功！'));
        }else {
            //获取添加错误原因
            return json(array('success'=>false,'info'=>'添加失败！'));
        }
    }

    //修改地区信息
    public function region_edit(){
        //验证用户权限
        Common::checkpower(30);
        if (request()->isPost()) {
            $data = array();
            $data['region_id'] = input('post.region_id', '');
            $data['region_name'] = input('post.region_name', '');
            if(!$data['region_name']){return json(array('success'=>false,'info'=>'请填写地区名称！'));exit;}
            $data['parent_id'] = input('post.parent_id','');
            //自动创建并验证数据
            if (\think\Db::name('region')->where(array('region_id'=>$data['region_id']))->update($data)) {
                //修改后清除缓存
                \think\Cache::rm('region_'.$data['region_id']);
                \think\Cache::rm('parent_region_'.$data['parent_id']);
                //记录系统日志
                admin_log("修改地区：{$data['region_name']}");
                //修改后自动生成js文件
                $this->generate();
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else {
            $region_id = input('get.region_id','0');
            $list = get_region($region_id);
            return view('region_edit',array('list'=>$list));
        }
    }

    //删除地区
    public function region_del(){
        //验证用户权限
        Common::checkpower(30);
        $region_id = input('post.idlist', '');
        $region = get_region($region_id);
        if (\think\Db::name('region')->where(array('region_id' => $region_id))->delete()) {
            //修改后清除缓存
            \think\Cache::rm('region_'.$region['region_id']);
            \think\Cache::rm('parent_region_'.$region['parent_id']);
            //记录系统日志
            admin_log("删除 ".$region['region_name']." 地区");
            //修改后自动生成js文件
            $this->generate();
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //更新JS文件
    public function generate() {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/public/static/js/region-list.js';
        $list = \think\Db::name('region')->field(true)->select();
        $html = "var region_list = Array(\n";
        foreach ($list as $key => $value) {
            $html .= "{'id':{$list[$key]['region_id']},'parent_id':{$list[$key]['parent_id']},'name':'{$list[$key]['region_name']}'}";
            if ($key != count($list)-1) $html .= ',';
        }
        $html .= "\n);";
        //写入文件
        file_put_contents($filename, $html);
    }

    //配送运费设置
    public function freight(){
        //验证用户权限
        Common::checkpower(31);
        $list = \think\Db::name('freight')->field('id')->select();
        if ($list) {
            foreach($list as $key => $value) {
                $value = get_freight($value['id']);
                // 格式化时间
                $value['create_time'] = date('Y-m-d H:i', $value['create_time']);
                $list[$key] = $value;
            }
        }
        return view('freight',array('list'=>$list));
    }

    //添加配送运费板块
    public function freight_add(){
        if (request()->isPost()) {
            // 接收用户提交数据
            $data = input('post.');
            $name = input('post.name','');
            if(!$name){return json(array('success'=>false,'info'=>'运费名称不能为空！'));exit;}
            $data['create_time'] = time();
            //添加数据
            if (\think\Db::name('freight')->insert($data)) {
                //记录系统日志
                admin_log("添加 ".$data['name']." 配送运费");
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                //获取添加错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else {
            return view('freight_add');
        }
    }

    //修改配送运费信息
    public function freight_edit(){
        if (request()->isPost()) {
            // 接收用户提交数据
            $id = input('post.id','0');
            $data = input('post.');
            $name = input('post.name','');
            if(!$name){return json(array('success'=>false,'info'=>'运费名称不能为空！'));exit;}
            //修改数据
            if (\think\Db::name('freight')->where(array('id'=>$id))->update($data)) {
                //修改后清除缓存
                \think\Cache::rm('freight_'.$id);
                //记录系统日志
                 admin_log("修改 ".$data['name']." 配送运费");
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else {
            $id = input('get.id', '0');
            //获取配送运费信息
            $list = get_freight($id);
            if (!$list){return error('不存在的数据！');exit;}
            return view('freight_edit',array('list'=>$list));
        }
    }

    //删除配送运费板块
    public function freight_del(){
        //验证用户权限
        Common::checkpower(31);
        $id = input('post.idlist', '0');
        if ($id == 1){return json(array('success'=>false,'info'=>'默认配送运费不能删除！'));exit;}
        if (\think\Db::name('freight')->where(array('id' => $id))->delete()) {
            //修改后清除缓存
            \think\Cache::rm('freight_'.$id);
            //记录系统日志
            admin_log("删除 ID:".$id." 配送运费");
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }
}