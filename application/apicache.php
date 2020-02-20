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
 * 获取我的收藏商品
 * @param string    $uid 用户ID
 * @return  array
 */
function goods_collect($uid) {
    //从缓存获取
    $list = \think\Cache::get('goods_collect_'.$uid);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('goods_collect')->alias('c')
                                                ->field('c.*,g.goods_name,g.goods_logo,g.goods_price,g.market_price,g.goods_salse')
                                                ->join('__GOODS__ g','c.goods_id = g.goods_id')
                                                ->where(array('c.uid'=>$uid,'g.is_state'=>1))
                                                ->select();
        \think\Cache::tag('goods')->set('goods_collect_'.$uid,$list);
    }
    return $list;
}
/**
 * 获取我的收藏商家
 * @param string    $uid 用户ID
 * @return  array
 */
function business_collect($uid) {
    //从缓存获取
    $list = \think\Cache::get('business_collect_'.$uid);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('business_collect')->alias('c')
                                            ->field('c.*,b.name,b.logo,b.remark')
                                            ->join('__BUSINESS__ b','c.bus_id = b.id')
                                            ->where(array('c.uid'=>$uid,'b.is_state'=>1))
                                            ->select();
        \think\Cache::tag('business')->set('business_collect_'.$uid,$list);
    }
    return $list;
}
/**
 * 获取商品评论
 * @param string    $goods_id 商品ID
 * @return  array
 */
function goods_comment($goods_id) {
    //从缓存获取
    $list = \think\Cache::get('goods_comment_'.$goods_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('goods_comment')->field(true)->where(array('goods_id'=>$goods_id,'is_state'=>1))->select();
        \think\Cache::tag('goods')->set('goods_comment_'.$goods_id,$list);
    }
    return $list;
}