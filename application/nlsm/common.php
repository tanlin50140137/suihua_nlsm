<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

/**
 * 操作日志写入
 * @param string    $remark 操作记录
 * @return boolean
 */       
function admin_log($remark) {
	//验证用户是否已经登录
	if(!session('?admin')){return false;exit;}
    $data = array();
    $data['uid'] = session('admin.id');
    $data['remark'] = $remark;
    $data['create_time'] = time();
    $data['registe_ip'] = request()->ip();
    $data['sql'] = \think\Db::getLastSql();
    //写入日志
    \think\Db::name('admin_log')->insert($data);
    return true;
}
/**
 * 读取商城权限分类
 * @return array
 */
function get_power() {
    //从缓存获取
    $list = \think\Cache::get('power');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('power')->field(true)->order('sort asc')->select();
        \think\Cache::set('power', $list);
    }
    return $list;
}
/**
 * 读取管理员信息
 * @param intval    $id 会员ID
 * @return array
 */
function get_admin($id) {
    //从缓存获取
    $list = \think\Cache::get('admin_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('admin')->field(true)->where(array('id'=>$id))->find();
        \think\Cache::set('admin_'.$id, $list);
    }
    return $list;
}
/**
 * 读取管理员拥有权限信息
 * @return array
 */
function get_admin_power() {
    //验证用户是否已经登录
    if(!session('?admin')){return false;exit;}
    //遍历所有网站权限和功能
    $powerlist = get_power();
    //拼接管理员权限信息
    $list = array();
    //获取管理员信息
    $admin = get_admin(session('admin.id'));
    //超级管理员拥有所有权限
    if($admin['id'] == 1){
        foreach ($powerlist as $value) {
            if($value['pid'] == 1){
                $list[] = $value;
            }
        }
        //拼接管理员商城权限和功能
        foreach ($list as $key => $val) {
            $val['list'] = array();
            foreach ($powerlist as $value) {
                if($value['pid'] == $val['id']){
                    $val['list'][] = $value;
                }
            }
            $list[$key] = $val;
        }
    }else{
        $power = explode(',', $admin['power']);
        foreach ($powerlist as $value) {
            if($value['pid'] == 1 && in_array($value['id'],$power) && $value['is_state'] == 1){
                $list[] = $value;
            }
        }
        //拼接管理员商城权限和功能
        foreach ($list as $key => $val) {
            $val['list'] = array();
            foreach ($powerlist as $value) {
                if($value['pid'] == $val['id'] && in_array($value['id'],$power) && $value['is_state'] == 1){
                    $val['list'][] = $value;
                }
            }
            $list[$key] = $val;
        }
    }
    return $list;
}