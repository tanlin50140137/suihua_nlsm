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

class Power extends Common
{
    //后台权限
    public function index()
    {
        //验证用户权限
        Common::checkpower(5);
    	//查找权限分类
        $power = get_power();
        //查找权限下级分类
        $list = $this->power(1);
        foreach ($list as $key => $value) {
        	$value['list'] = $this->power($value['id']);
        	$list[$key] = $value;
        }
        //赋值数据集View模板输出  
        return view('index',array('list'=>$list));
    }

    // 查询下级权限功能分类
    private function power($pid = 0) {
        // 查询所有的下级权限
        $power = get_power();
        $list = array();
        foreach ($power as $key => $value) {
            if($value['pid'] == $pid){
                $list[] = $value;
            }
        }
        return $list;
    }

    // 修改权限状态：1启用 0禁用
    public function state(){
        //验证用户权限
        Common::checkpower(5);
        //要改变的权限id
        $id = input('post.id','0');
        //要改变的状态
        $state = input('post.state','1');
        //拼接搜索条件
        $where = array();
        $where['id|pid'] = $id;
        //修改权限状态
        if(\think\Db::name('power')->where($where)->update(array('is_state'=>$state))){
            //修改数据后清除权限缓存
            \think\Cache::rm('power');
            //记录系统日志
            admin_log('修改 ID:'.$id.' 权限状态');
            return json(array('success'=>true,'info'=>'修改成功！'));
        }else {
            //获取修改错误原因
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    // 添加权限分类
    public function add(){
        //验证用户权限
        Common::checkpower(5);
        if(request()->isPost()){
            //获取要修改的权限分类信息
            $data = input('post.','');
            $name = input('post.name','');
            if(!$name){return json(array('success'=>false,'info'=>'权限名不能为空！'));exit;}
            //添加权限分类
            $data['pid'] = input('post.pid','0');
            $data['sort'] = input('post.sort','50');
            $data['create_time'] = time();     
            if (\think\Db::name('power')->insert($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('power');
                //记录系统日志
                admin_log('添加 '.$name.' 权限分类');
                return json(array('success'=>true,'info'=>'添加成功！'));
            }else {
                //获取添加错误原因
                return json(array('success'=>false,'info'=>'添加失败！'));
            }
        }else{
            // 要添加的权限分类pid
            $pid = input('get.pid','0');
            //获取所有权限信息
            $power = $this->power(1);
            //获取状态正常的权限信息
            $list = array();
            foreach ($power as $key => $value) {
                if($value['is_state'] == 1){
                    $list[] = $value;
                }
            }
            //赋值数据集View模板输出  
            return view('add',array('list'=>$list,'pid'=>$pid));
        }
    }

    // 修改权限分类信息
    public function edit(){
        //验证用户权限
        Common::checkpower(5);
        if(request()->isPost()){
            //要修改的权限id
            $id = input('post.id','0');
            //要修改的权限父级id
            $pid = input('post.pid','0');
            $name = input('post.name','');
            if(!$name){return json(array('success'=>false,'info'=>'权限名不能为空！'));exit;}
            // 拼接需要修改的字段数据
            $data = input('post.','');
            //父级id不能是它的子类id
            if(\think\Db::name('power')->where(array('id'=>$pid,'pid'=>$id))->count() > 0){
                return json(array('success'=>false,'info'=>'父级分类不能是它的子类！'));exit;
            }
            //修改权限
            if(\think\Db::name('power')->where(array('id'=>$id))->update($data)){
                //原修改之前权限的信息数据
                $power = get_power();
                $list = array();
                foreach ($power as $key => $value) {
                    if($value['id'] == $id){$list = $value;break;}
                }
                //判断是否是修改状态，子类也要跟着变 
                if($data['is_state'] != $list['is_state']){
                    \think\Db::name('power')->where(array('pid'=>$id))->setField('is_state',$data['is_state']);
                }
                //修改数据后清除缓存
                \think\Cache::rm('power');
                //记录系统日志
                admin_log('修改 ID:'.$id.' 权限分类信息');
                return json(array('success'=>true,'info'=>'修改成功！'));
            }else {
                //获取修改错误原因
                return json(array('success'=>false,'info'=>'修改失败！'));
            }
        }else{
            // 要修改的权限id
            $id = input('get.id','0');
            //获取所有权限信息
            $power = get_power();
            $list = array();
            //获取要修改的权限信息
            $data = array();
            foreach ($power as $key => $value) {
                if($value['id'] != $id && $value['pid'] == 1 && $value['is_state'] == 1){
                    $list[] = $value;
                }
                if($value['id'] == $id){
                    $data = $value;
                }
            }
            //赋值数据集View模板输出  
            return view('edit',array('list'=>$list,'data'=>$data));
        }
    }

    // 删除权限分类信息
    public function delete(){
        //验证用户权限
        Common::checkpower(5);
        // 要删除的权限分类id
        $id = input('post.idlist','0');
        //判断是否有下级
        if(\think\Db::name('power')->where(array('pid'=>$id))->count() > 0){
            return json(array('success'=>false,'info'=>'不能删除,权限拥有子类！'));exit;
        }
        //删除权限分类
        if(\think\Db::name('power')->where(array('id'=>$id,'is_system'=>0))->delete()){
            //修改数据后清除缓存
            \think\Cache::rm('power');
            //记录系统日志
            admin_log('删除 ID:'.$id.' 权限分类');
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else {
            //获取删除错误原因
            return json(array('success'=>false,'info'=>'系统自带权限不能删除！'));
        }
    }
}
