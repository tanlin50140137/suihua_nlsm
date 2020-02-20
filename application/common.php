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
 *网站错误信息显示
 * @param string $remark 错误信息
 * @return  view
 */
function error($remark){
    $remark = $remark ? $remark : '页面让狗狗叼走了！';
    echo '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:(</h1><p> <span style="font-size:30px">'.$remark.'</span></p><span style="font-size:22px;"></span></div>';
    exit;
}
/**
 * 获取网站配置信息
 * @param string    $name 配置标识
 * @return  array
 */
function get_config($name) {
    //从缓存获取
    $list = \think\Cache::get('config_'.$name);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('config')->where(array('name'=>$name))->value('value');
        $list = json_decode($list,true);
        \think\Cache::set('config_'.$name,$list);
    }
    return $list;
}
/**
 * 读取获取支付方式
 * @param string $name 配置标识
 * @return  array
 */
function get_payment($name) {
    //从缓存获取
    $list = \think\Cache::get('payment_'.$name);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('payment')->field(true)->where(array('name'=>$name))->find();
        $value = json_decode($list['value'],true);
        $list = array_merge($list,$value);
        \think\Cache::set('payment_'.$name,$list);
    }
    return $list;
}
/**
 * 读取普通会员信息
 * @param intval    $id 会员ID
 * @return array
 */
function get_member($id) {
    //从缓存获取
    $list = \think\Cache::get('member_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('member')->field(true)->where(array('id'=>$id))->find();
        if($list){
            //查找上级会员
            $list['idlist'] = get_parent_member($id);
            //获取会员等级分组信息
            $level = get_member_level();
            //转换用户对应的等级名称
            foreach ($level as $key => $value) {
                if($list['level_id'] == $value['id']){$list['level_name'] = $value['name'];break;}
            }

            //引入phpqrcode库文件
            require_once("./extend/phpqrcode.php");
            $url = 'http://'.$_SERVER['SERVER_NAME'].'/index/index/register?pid='.$id;
            $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
            $matrixPointSize = "6"; //生成图片大小 ：1到10
            $QRcode = new \QRcode();
            //保存文件
            $list['code'] = "/public/qrcode/user_".$id.'.jpg';
            $QRcode::png($url, $_SERVER['DOCUMENT_ROOT'].$list['code'], $errorCorrectionLevel, $matrixPointSize);
            \think\Db::name('member')->where(array('id'=>$id))->setField('code',$list['code']);

            $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$list['userface']);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            $thumb = '/public/userface/'.randChar().'.png';
            $image->thumb(110, 110)->save($_SERVER['DOCUMENT_ROOT'].$thumb);

            $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$list['code']);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            $image->thumb(250, 250)->save($_SERVER['DOCUMENT_ROOT'].$list['code']);

            $dst_path = '/public/image/backgroud.png';//背景图片路径
            $src_path = $list['code'];//覆盖图
            //创建图片的实例
            $dst = imagecreatefromstring(file_get_contents($_SERVER['DOCUMENT_ROOT'].$dst_path));
            $src = imagecreatefromstring(file_get_contents($_SERVER['DOCUMENT_ROOT'].$src_path));
            $src2 = imagecreatefromstring(file_get_contents($_SERVER['DOCUMENT_ROOT'].$thumb));

            //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
            imagecopymerge($dst, $src, 215, 550, 0, 0, 250, 250, 100);
            imagecopymerge($dst, $src2, 270, 305, 0, 0, 110, 110, 100);
            @unlink($QIMG); //删除二维码与logo的合成图片
            @unlink($QRB);  //删除服务器上二维码图片
        
            header("Content-type: image/png");
            imagepng($dst,$_SERVER['DOCUMENT_ROOT'].$list['code']);//根据需要生成相应的图片
            imagedestroy($dst);
            imagedestroy($src);

            $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$list['code']);
            // 给原图左上角添加水印并保存water_image.png
            $font = $_SERVER['DOCUMENT_ROOT']."/public/wryh.ttf";
            $image->text($list['nickname'],$font,15,'#333333',1,array(300,430))->save($_SERVER['DOCUMENT_ROOT'].$list['code']);
            //如果是文件直接删除
            unlink($_SERVER['DOCUMENT_ROOT'].$thumb);

            //会员上级
            if($list['pid']){$user = get_member($list['pid']);}
            $list['prevname'] = isset($user['username']) ? $user['username'] : '';
            $list['prevnick'] = isset($user['nickname']) ? $user['nickname'] : '';
        }
        \think\Cache::set('member_'.$id, $list);
    }
    return $list;
}
/**
 * 读取普通会员等级分组信息
 * @return  array
 */
function get_member_level() {
    //从缓存获取
    $list = \think\Cache::get('member_level');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('member_level')->field(true)->select();
        \think\Cache::set('member_level', $list);
    }
    return $list;
}
/**
 * 查找上级会员
 * @param string $id 用户会员id
 * @return  array
 */
function get_parent_member($id) {
    $list = array();
    if ($id) {
        $pid = \think\Db::name('member')->where(array('id'=>$id))->value('pid');
        if ($pid) {
            $list[] = $pid;
            $list = array_merge($list, get_parent_member($pid));
        }
    }
    return $list;
}
/**
 * 查找上级会员信息
 * @param string $id 用户会员id
 * @param string $level 用户会员等级
 * @return  array
 */
function get_level_member($id) {
    //查找上级会员
    $idlist = get_parent_member($id);
    //拼接上级会员信息
    $list = array();
    $user = get_member($id);
    if($idlist ){
        foreach ($idlist as $key => $value) {
           $member = get_member($value);
           if($member['level_id'] > $user['level_id']){
                 $list[$member['level_id']] = $member;
                 $user = $member;
           }
        }
    }
    return $list;
}
/**
 * 会员券分日志记录
 * @param $type 券分策略标识
 * @param $uid 用户id
 * @param $remark 内容描述
 * @param $point 券分数值
 * @param $order_id 订单号
 */
function member_point_log($type,$uid,$remark,$point,$order_id='0') {
    if(!$point || $point == 0){return true;}
    $data = array(
        'type' => $type,
        'uid' => $uid,
        'remark' => $remark,
        'create_time' => time(),
        'point'=> $point,
        'admin_id'=> session('admin.id') ? session('admin.id') : 0,
        'order_id'=> $order_id,
    );
    //修改数据后清除缓存
    \think\Cache::rm('member_'.$uid);
    //写入日志
    return \think\Db::name('member_point_log')->insert($data);
}
/**
 * 会员余额日志记录
 * @param $type 策略标识
 * @param $uid 用户id
 * @param $remark 内容描述
 * @param $money 金额数值
 * @param $expenses 手续费
 * @param $order_id 订单号
 */
function member_money_log($type,$uid,$remark,$money,$order_id='0',$expenses = '0') {
    if(!$money || $money == 0){return true;}
    $data = array(
        'type' => $type,
        'uid' => $uid,
        'remark' => $remark,
        'create_time' => time(),
        'money'=> $money,
        'expenses'=> $expenses,
        'order_id'=> $order_id,
    );
    //修改数据后清除缓存
    \think\Cache::rm('member_'.$uid);
    //写入日志
    return \think\Db::name('member_money_log')->insert($data);
}
/**
 * 会员业绩日志记录
 * @param $type 策略标识
 * @param $uid 用户id
 * @param $remark 内容描述
 * @param $money 金额数值
 * @param $expenses 手续费
 * @param $order_id 订单号
 */
function member_achievement($type,$uid,$remark,$money,$order_id='0') {
    if(!$money || $money == 0){return true;}
    $user = get_member($uid);
    $data = array(
        'type' => $type,
        'uid' => $uid,
        'remark' => $remark,
        'create_time' => time(),
        'money'=> $money,
        'order_id'=> $order_id,
        'level_id'=> $user['level_id'],
    );
    //写入日志
    return \think\Db::name('member_achievement')->insert($data);
}
/**
 * 读取会员升级套餐信息
 * @param string $id 套餐id
 * @return  array
 */
function get_member_goods($id) {
    //从缓存获取
    $list = \think\Cache::get('member_goods_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('member_goods')->field(true)->where(array('id'=>$id))->find();
        if($list){
            //转换商品详情图片信息
            $list['content'] = htmlspecialchars_decode($list['content']);
        }
        \think\Cache::set('member_goods_'.$id,$list);
    }
    return $list;
}
/**
 * 会员购买升级订单分佣
 * @param $order  订单信息
 * @return  array
 */
function commision_goods($order) {
    //判断会员是否已返佣
    if($order['return_time']){return true;}
    // 找到推荐人
    $user = get_member($order['uid']);
     //获取商品信息
    $goods = get_member_goods($order['goods_id']);
    // 判断是否购买升级商品
    if($goods['level_id'] == 2){
        //成为VIP
        if($user['level_id'] < 2){
            \think\Db::name('member')->where(array('id'=>$order['uid']))->setField('level_id',2);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$order['uid']);
        }
    }else if($goods['level_id'] == 3){
        //成为店长
        if($user['level_id'] < 3){
            \think\Db::name('member')->where(array('id'=>$order['uid']))->setField('level_id',3);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$order['uid']);
        }
    }
    if(empty($user['pid'])){return true;}
    $tuijian = get_member($user['pid']);

    // 如果你的推荐人是VIP会员级别
    if($tuijian['level_id'] == 2) {
        //升级店长
        if(\think\Db::name('member')->where(array('pid'=>$tuijian['id']))->count() > 49){
            \think\Db::name('member')->where(array('id'=>$tuijian['id'],'level_id'=>2))->setField('level_id',3);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$tuijian['id']);
        }

        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['huitui']);
        //写入日志
        member_money_log("会员套餐",$tuijian['id'],"直推会员".$user['nickname']."购买".$goods['name']."套餐返利",$goods['huitui'],$order['order_id']);
    }

    // 如果你的推荐人是店长级别
    if($tuijian['level_id'] > 2) {
        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['diantui']);
        //写入日志
        member_money_log("会员套餐",$tuijian['id'],"直推会员".$user['nickname']."购买".$goods['name']."套餐返利",$goods['diantui'],$order['order_id']);
    }


    //上级会员信息
    $idlist = get_level_member($user['id'],$user['level_id']);
    //记录业绩
    $money  = $goods['price'];
    // 找到是店长的推荐人
    $tuijianthree = isset($idlist[3]) ? $idlist[3] : '';
    // 如果存在
    if( $tuijianthree && $tuijianthree['id'] != $tuijian['id']) {
        //升级街/镇代理
        $nextlist = \think\Db::name('member_relation')->where(array('pid'=>$tuijianthree['id']))->column('id');
        $nextlist = implode(",",$nextlist);

        $where = array();
        $where['id'] = array('IN',$nextlist);
        $where['level_id'] = 3;
        //统计下级会员
        if(\think\Db::name('member')->where($where)->count() > 19){
            \think\Db::name('member')->where(array('id'=>$tuijianthree['id']))->setField('level_id',7);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$tuijianthree['id']);
        }

        //写入日志
        member_achievement("会员套餐",$tuijianthree['id'],"间推会员".$user['nickname']."购买".$goods['name']."套餐返利",$money,$order['order_id']);
    }

    // 找到是街镇代理的推荐人
    $tuijianfour = isset($idlist[7]) ? $idlist[7] : '';
    // 如果存在
    if( $tuijianfour && $tuijianfour['id'] != $tuijian['id']) {
        //升级区代理
        $nextlist = \think\Db::name('member_relation')->where(array('pid'=>$tuijianfour['id']))->column('id');
        $nextlist = implode(",",$nextlist);

        $where = array();
        $where['id'] = array('IN',$nextlist);
        $where['level_id'] = 7;
        //统计下级会员
        if(\think\Db::name('member')->where($where)->count() > 4){
            \think\Db::name('member')->where(array('id'=>$tuijianfour['id']))->setField('level_id',8);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$tuijianfour['id']);
        }

        //写入日志
        member_achievement("会员套餐",$tuijianfour['id'],"间推会员".$user['nickname']."购买".$goods['name']."套餐返利",$money,$order['order_id']);
    }

    // 找到是区代理的推荐人
    $tuijianfive = isset($idlist[8]) ? $idlist[8] : '';
    // 如果存在
    if( $tuijianfive && $tuijianfive['id'] != $tuijian['id']) {
        //写入日志
        member_achievement("会员套餐",$tuijianfive['id'],"间推会员".$user['nickname']."购买".$goods['name']."套餐返利",$money,$order['order_id']);
    }

    // 找到是五星代理的推荐人
    $tuijianten = isset($idlist[10]) ? $idlist[10] : '';
    // 如果存在
    if( $tuijianten) {
        //写入日志
        member_achievement("会员套餐",$tuijianten['id'],"间推会员".$user['nickname']."购买".$goods['name']."套餐返利",$money,$order['order_id']);
    }
    
    \think\Db::name('member_goods_order')->where(array('order_id'=>$order['order_id']))->setField('return_time',time());
    return true;
}
/**
 * 订单分佣
 * @param $order  订单信息
 * @return  array
 */
function commision_order($order) {
    //获取用户设置
    $config = get_config('user');

    //判断订单是否使用优惠券
    if($order['coupon']){
        //修改优惠券为已使用
        $coupon = array();
        $coupon['is_state'] = 2;
        $coupon['usetime'] = time();
        $coupon['order_id'] = $order['order_id'];
        \think\Db::name('coupon_list')->where(array('id'=>$order['coupon'],'uid'=>$order['uid']))->update($coupon);
    }
    // 找到推荐人
    $user = get_member($order['uid']);
    $tuijian = get_member($user['pid']);
     //上级会员信息
    $idlist = get_level_member($user['id'],$user['level_id']);

    // //会员自动升级
    // if($tuijian){
    //     //获取下级会员
    //     $idlist = \think\Db::name('member')->where(array('pid'=>$user['pid']))->column('id');
    //     $idlist = implode(',', $idlist);
    //     $where = array();
    //     $where['uid'] = array('IN',$idlist);
    //     $where['is_state'] = array('IN','2,3,7,9');
    //     $total = \think\Db::name('member_order')->where($where)->sum('payprice');
    //     $total = $total + $order['payprice']; 
    //     $consume = 0;
    //     //获取会员等级分组信息
    //     $level = get_member_level();
    //     if ($level) {
    //         foreach($level as $key => $value) {
    //             if($value['id'] == 2){
    //                 $consume = $value['consume'];
    //             }
    //         }
    //     }
    //     if($total >= $consume && $tuijian['level_id'] == 1){
    //         \think\Db::name('member')->where(array('id'=>$user['pid']))->setField('level_id',2);
    //         //修改数据后清除缓存
    //         \think\Cache::rm('member_'.$user['pid']);
    //     }
    // }

    //订单购买商品
    $order['goods'] = json_decode($order['goods'],true);
    foreach ($order['goods'] as $key => $value) {
        $number = $value['goods_number'];
        //获取商品信息
        $goods = get_goods($value['goods_id']);
        // if($order['point_price'] > 0){
        //     $payprice = $goods['goods_price'] - $goods['use_point'];
        // }else{
        //     $payprice = $goods['goods_price'];
        // }
        $payprice = $goods['goods_price'] * $number;
        //判断商品打折
        if($goods['discount'] < 10){
            $payprice = $goods['goods_price'] * $goods['discount'] / 10;
            //折扣金额
            $discount = ($goods['goods_price'] - $payprice) * $number;

            //分佣给下单会员
            $point = $discount * $config['user'] / 100;
            $money = $point / 2;
            // 如果下单人是普通会员
            if($user['level_id'] == 1 || $user['money'] == 0) {
                \think\Db::name('member')->where(array('id'=>$user['id']))->setInc('point',$point);
                //写入日志
                member_point_log("商家商品打折",$user['id'],"商家商品".$goods['goods_name']."打折返利",$point,$order['order_id']);
            }
            // 如果下单人是VIP会员/商家会员
            if($user['level_id'] == 2 || $user['level_id'] == 3) {
                if($user['money'] > 0){
                    $point = $money;
                    //直推奖励
                    \think\Db::name('member')->where(array('id'=>$user['id']))->setInc('point',$point);
                    //写入日志
                    member_point_log("商家商品打折",$user['id'],"商家商品".$goods['goods_name']."打折返利",$point,$order['order_id']);
                    
                    \think\Db::name('member')->where(array('id'=>$user['id']))->setInc('money',$money);
                    //写入日志
                    member_money_log("商家商品打折",$user['id'],"商家商品".$goods['goods_name']."打折返利",$money,$order['order_id']);
                }
            }

            if($tuijian){
            
                //分佣给下单会员推荐人
                $point = $discount * $config['parent'] / 100;
                $money = $point / 2;
                // 如果你的推荐人是普通会员
                if($tuijian['level_id'] == 1 || $tuijian['money'] == 0) {
                    //直推奖励
                    \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('point',$point);
                    //写入日志
                    member_point_log("商家商品打折",$tuijian['id'],"商家商品".$goods['goods_name']."打折返利",$point,$order['order_id']);
                }

                // 如果你的推荐人是VIP会员/商家会员
                if($tuijian['level_id'] == 2 || $tuijian['level_id'] == 3) {
                    if($tuijian['money'] > 0){
                        $point = $money;
                        //直推奖励
                        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('point',$point);
                        //写入日志
                        member_point_log("商家商品打折",$tuijian['id'],"商家商品".$goods['goods_name']."打折返利",$point,$order['order_id']);
                        
                        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$money);
                        //写入日志
                        member_money_log("商家商品打折",$tuijian['id'],"商家商品".$goods['goods_name']."打折返利",$money,$order['order_id']);

                    }
                }
            }
        }
        //商家实时转账
        if($goods['bus_id']){
            //获取商家信息
            $business = get_business($goods['bus_id']);
            if($business){
                $payprice = $goods['costs_price']  * $number;

                //提现转账
                \think\Db::name('business')->where(array('id'=>$goods['bus_id']))->setInc('money',$payprice);
                //写入日志
                business_money_log("商品购买",$goods['bus_id'],$user['nickname']."购买".$goods['goods_name'],$payprice,$order['order_id']);
                
                //短信通知下单
                if($business['mobile']){

                }

                if($goods['is_xian'] == 1){
                    $business['is_take'] == 1;
                }
            }
        }

        if($tuijian){
            
            //计算返利金额
            $money  = $value['goods_price'] * $goods['huitui'] / 100 * $number;
            //直推奖励可提现余额
            // $money = $point / 2;
            
            // 如果你的推荐人是普通会员
            // if($tuijian['level_id'] == 1 || $tuijian['money'] == 0) {
            //     //直推奖励
            //     \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('point',$point);
            //     //写入日志
            //     member_point_log("商品购买",$tuijian['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."返利",$point,$order['order_id']);
            // }
            // 如果你的推荐人是VIP会员/商家会员
            if($tuijian['level_id'] == 2 || $tuijian['level_id'] == 3) {
                // if($tuijian['money'] > 0){
                    // $point = $money;
                    //直推奖励
                    // \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('point',$point);
                    //写入日志
                    // member_point_log("商品购买",$tuijian['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."返利",$point,$order['order_id']);
                    
                    \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$money);
                    //写入日志
                    member_money_log("商品购买",$tuijian['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."[X".$number."]返利",$money,$order['order_id']);
                // }
            }

            //满足金额转余额
            // $tuijian = get_member($user['pid']);
            // if($tuijian['money'] == 0 && $tuijian['point'] >= $consume){
            //     $money = $tuijian['point'] / 2;
            //     //直推奖励
            //     \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setDec('point',$money);
            //     //写入日志
            //     member_point_log("会员升级",$tuijian['id'],"会员升级券分转余额",-$money,$order['order_id']);
                
            //     \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$money);
            //     //写入日志
            //     member_money_log("会员升级",$tuijian['id'],"会员升级券分返利",$money,$order['order_id']);
            // }

            //记录业绩
            $money  = $value['goods_price'] * $number;
            // 找到是店长的推荐人
            $tuijianthree = isset($idlist[3]) ? $idlist[3] : '';
            // 如果存在
            if( $tuijianthree ) {
                //写入日志
                member_achievement("商品购买",$tuijianthree['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."[X".$number."]返利",$money,$order['order_id']);
            }
            // 找到是街镇代理的推荐人
            $tuijianfour = isset($idlist[7]) ? $idlist[7] : '';
            // 如果存在
            if( $tuijianfour ) {
                //写入日志
                member_achievement("商品购买",$tuijianfour['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."[X".$number."]返利",$money,$order['order_id']);
            }
            // 找到是区代理的推荐人
            $tuijianfive = isset($idlist[8]) ? $idlist[8] : '';
            // 如果存在
            if( $tuijianfive ) {
                //写入日志
                member_achievement("商品购买",$tuijianfive['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."[X".$number."]返利",$money,$order['order_id']);
            }

            // 找到是五星代理的推荐人
            $tuijianten = isset($idlist[10]) ? $idlist[10] : '';
            // 如果存在
            if( $tuijianten ) {
                //写入日志
                member_achievement("商品购买",$tuijianten['id'],"下级会员".$user['nickname']."购买".$goods['goods_name']."[X".$number."]返利",$money,$order['order_id']);
            }
        }
        //商品状态
        $value['is_state'] = 2;
        //已发货数量
        $value['sendnum'] = 0;
        $order['goods'][$key] = $value;

        //增加销量
        \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setInc('goods_salse',$number);
        //修改后清除该商品缓存
        \think\Cache::rm('goods_'.$value['goods_id']);
        \think\Cache::rm('goods_product_'.$value['goods_id']);
        //清除Api获取商品相关接口缓存
        \think\Cache::clear('goods');
    }
    return $order['goods'];
}
/**
 * 读取商家信息
 * @param string $id 商家id
 * @return  array
 */
function get_business($id) {
    if(!$id){return false;}
    //从缓存获取
    $list = \think\Cache::get('business_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('business')->field(true)->where(array('id'=>$id))->find();
        if($list){
            $list['content'] = htmlspecialchars_decode($list['content']);
            $list['setmeal'] = $list['setmeal'] ? explode(',', $list['setmeal']) : array();
            
            //引入phpqrcode库文件
            require_once("./extend/phpqrcode.php");
            $url = 'http://app.nlsm168.com/index/business/payment?id='.$list['id'];
            $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
            $matrixPointSize = "4"; //生成图片大小 ：1到10
            $QRcode = new \QRcode();
            //保存文件
            $code = "/public/qrcode/".$list['id'].'.jpg';
            $QRcode::png($url, $_SERVER['DOCUMENT_ROOT'].$code, $errorCorrectionLevel, $matrixPointSize);
            \think\Db::name('business')->where(array('id'=>$list['id']))->setField('code',$code);

            $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$list['logo']);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
            $thumb = '/public/upload/'.randChar().'.png';
            $image->thumb(25, 25)->save($_SERVER['DOCUMENT_ROOT'].$thumb);

            $dst_path = $code;//背景图片路径
            $src_path = $thumb;//覆盖图
            //创建图片的实例
            $dst = imagecreatefromstring(file_get_contents($_SERVER['DOCUMENT_ROOT'].$dst_path));
            $src = imagecreatefromstring(file_get_contents($_SERVER['DOCUMENT_ROOT'].$src_path));

            //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
            imagecopymerge($dst, $src, 60, 65, 0, 0, 25, 25, 100);
            @unlink($QIMG); //删除二维码与logo的合成图片
            @unlink($QRB);  //删除服务器上二维码图片
        
            header("Content-type: image/png");
            imagepng($dst,$_SERVER['DOCUMENT_ROOT'].$code);//根据需要生成相应的图片
            imagedestroy($dst);
            imagedestroy($src);
            //如果是文件直接删除
            unlink($_SERVER['DOCUMENT_ROOT'].$thumb);
    
            $list['code'] = $code;
        }

        \think\Cache::set('business_'.$id,$list);

    }
    return $list;
}
/**
 * 读取商家分类信息
 * @return  array
 */
function get_business_type() {
    //从缓存获取
    $list = \think\Cache::get('business_type');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('business_type')->field(true)->select();
        \think\Cache::set('business_type',$list);
    }
    return $list;
}
/**
 * 商家余额日志记录
 * @param $type 策略标识
 * @param $uid 用户id
 * @param $remark 内容描述
 * @param $money 金额数值
 * @param $expenses 手续费
 * @param $order_id 订单号
 */
function business_money_log($type,$uid,$remark,$money,$order_id='0',$expenses = '0') {
    if(!$money){return true;}
    $data = array(
        'type' => $type,
        'uid' => $uid,
        'remark' => $remark,
        'create_time' => time(),
        'money'=> $money,
        'expenses'=> $expenses,
        'order_id'=> $order_id,
    );
    //修改数据后清除缓存
    \think\Cache::rm('business_'.$uid);
    //写入日志
    return \think\Db::name('business_money_log')->insert($data);
}
/**
 * 获取数组子分类
 * @param $param array 列表数组
 * @param $id number ID号
 * @param $level number 层级
 * @return  array
*/
function get_child($param, $id = 0, $level = 0) {
    $list = array();
    if ($param) {
        foreach($param as $key => $value) {
            if ($value['pid'] == $id) {
                $value['html'] = str_repeat('--', $level);
                $value['level'] = $level;
                $list[] = $value;
                $list = array_merge($list, get_child($param, $value['id'], $level+1));
            }
        }
    }
    return $list;
}
/**
 * 获取数组上级分类
 * @param $param array 列表数组
 * @param $id number ID号
 * @return  array
*/
function get_parent($param,$id) {
    $list = array();
    if ($id) {
        foreach ($param as $key => $value) {
            if ($value['id'] == $id) {
                $list[] = $value;
                $list = array_merge($list, get_parent($param,$value['pid']));
            }
        }
    }
    asort($list);
    return $list;
}
/**
 * 获取商品信息
 * @param $goods_id number 商品ID号
 * @return  array
 */
function get_goods($goods_id) {
    //从缓存获取
    $list = \think\Cache::get('goods_'.$goods_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('goods')->field(true)->where(array('goods_id'=>$goods_id))->find();
        if($list){
            //转换商品详情图片信息
            $list['goods_content'] = htmlspecialchars_decode($list['goods_content']);
            $list['goods_image'] = json_decode($list['goods_image'],true);
            // 获取上级分类
            $type = get_goods_type();
            $list['typelist'] = get_parent($type,$list['typeid']);
            //转换商品规格信息
            $list['goods_spec'] = goods_spec($goods_id);
        }
        \think\Cache::set('goods_'.$goods_id,$list);
    }
    return $list;
}
/**
 * 读取获取商品对应的规格属性
 * @param $goods_id 商品goods_id
 * @return  array
 */
function goods_spec($goods_id) {
    //拼接获取对应规格
    $list = array();
    //获取商品信息
    $goods_product = get_goods_product($goods_id);
    if(!$goods_product){return $list;}
    $spec_id = explode(',', $goods_product[0]['spec_id']);
    //获取商品规格
    $spec = get_goods_spec();
    foreach ($spec['spec_list'] as $key => $value) {
        if(in_array($value['id'],$spec_id)){
            $list[$value['id']] = $value;
        }
    }
    //拼接获取对应规格数值
    $spec_value = array();
    foreach ($spec['spec_value'] as $key => $value) {
        foreach ($goods_product as $ke => $val) {
            $val['spec_idlist'] = explode(',', $val['spec_idlist']);
            foreach ($val['spec_idlist'] as $k => $v) {
                if($value['id'] == $v){
                    $spec_value[$value['id']] = $value;
                }
            }
        }
    }
    foreach($list as $key => $value) {
        $value['list'] = array();
        foreach ($spec_value as $k => $val) {
            if($value['id'] == $val['typeid']){
                $value['list'][$k] = $val;
            }
        }
        $list[$key] = $value;
    }
    return $list;
}
/**
 * 获取商品配送运费
 * @return  array
 * @param $goods_id  配送商品id
 * @param $address 配送地址
 * @param $spec_value 配送商品规格
 * @param $goods_number 配送商品数量
 * @param $payprice 订单金额
 */
function get_freight_price($goods_id,$address,$spec_value = '',$goods_number = 1,$payprice = 0) {
    //获取商品信息
    $goods = get_goods($goods_id);
    //获取配送方式
    $list = get_freight($goods['freight_id']);
    if(!$list){return 0.00;}
    $freight = $list['value'][0];
    foreach ($list['value'] as $key => $value) {
        $namelist = explode(',', $value['namelist']);
        if(in_array($address, $namelist)){$freight = $value;break;}
    }
    if($spec_value){
        //获取商品规格
        $goods_product = get_goods_product($goods_id);
        $product = array();
        foreach ($goods_product as $key => $value) {
            if($value['spec_value'] == $spec_value){$product = $value;break;}
        }
        if(!$product){return 0.00;}
    }
    $price = '';
    switch ($list['typeid']) {
        case '1':
            //按重量
            if(isset($product['goods_weight'])){
                $price = $product['goods_weight'] * $freight['price'];
            }else if($goods['goods_weight']){
                $price = $goods['goods_weight'] * $freight['price'];
            }else{
                $price = $freight['price'];
            }
            break;
        case '2':
            //按件数
            $price = $goods_number * $freight['price'];
            break;
        default:
            //统一价
            $price = $freight['price'];
            break;
    }
    if($payprice >= 60){
        $price == 0;
    }
    $price = sprintf("%.2f",$price);
    return $price;
}
/**
 * 读取获取商品规格值数据
 * @param $goods_id number 商品ID号
 * @return  array
 */
function get_goods_product($goods_id) {
    //从缓存获取
    $list = \think\Cache::get('goods_product_'.$goods_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('goods_product')->field(true)->where(array('goods_id'=>$goods_id))->select();
        \think\Cache::set('goods_product_'.$goods_id,$list);
    }
    return $list;
}
/**
 * 读取商品分类信息
 * @return  array
 */
function get_goods_type() {
    //从缓存获取
    $list = \think\Cache::get('goods_type');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('goods_type')->field(true)->order('sort asc')->select();
        $list = get_child($list);
        \think\Cache::set('goods_type',$list);
    }
    return $list;
}
/**
 * 读取获取商品规格
 * @return  array
 */
function get_goods_spec() {
    //从缓存获取
    $list = \think\Cache::get('goods_spec');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = array();
        $list['spec_list'] = \think\Db::name('goods_spec')->field(true)->order('sort asc')->select();
        $list['spec_value'] = \think\Db::name('goods_spec_value')->field(true)->order('sort asc')->select();
        \think\Cache::set('goods_spec',$list);
    }
    return $list;
}
/**
 * 获取配送运费信息
 * @param $id number 配送运费ID号
 * @return  array
 */
function get_freight($id) {
    //从缓存获取
    $list = \think\Cache::get('freight_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('freight')->field(true)->where(array('id'=>$id))->find();
        if($list){
            $list['value'] = json_decode($list['value'],true);
            switch ($list['typeid']) {
                case '1': $list['typename'] = '按重量';break;
                case '2': $list['typename'] = '按件数';break;
                default:  $list['typename'] = '统一价';break;
            }
        }
        \think\Cache::set('freight_'.$id,$list);
    }
    return $list;
}
/**
 * 获取配送地区信息
 * @param $region_id number 地区ID号
 * @return  array
 */
function get_region($region_id) {
    //从缓存获取
    $list = \think\Cache::get('region_'.$region_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('region')->field(true)->where(array('region_id'=>$region_id))->find();
        \think\Cache::set('region_'.$region_id,$list);
    }
    return $list;
}
/**
 * 获取配送地区下级信息
 * @param $parent_id number 地区上级ID号
 * @return  array
 */
function get_parent_region($parent_id) {
    //从缓存获取
    $list = \think\Cache::get('parent_region_'.$parent_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('region')->field(true)->where(array('parent_id'=>$parent_id))->select();
        \think\Cache::set('parent_region_'.$parent_id,$list);
    }
    return $list;
}
/**
 * 获取话费充值套餐信息
 * @param $id number 话费充值套餐ID号
 * @return  array
 */
function get_recharg($id) {
    //从缓存获取
    $list = \think\Cache::get('recharg_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('recharg')->field(true)->where(array('id'=>$id))->find();
        \think\Cache::set('recharg_'.$id,$list);
    }
    return $list;
}
/**
 * 读取项目投资信息
 * @param string $id 项目id
 * @return  array
 */
function get_invest($id) {
    //从缓存获取
    $list = \think\Cache::get('invest_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('invest')->field(true)->where(array('id'=>$id))->find();
        if($list){
            //转换商品详情图片信息
            $list['content'] = htmlspecialchars_decode($list['content']);
            $list['image'] = $list['image'] ? json_decode($list['image'],true) : '';
        }
        \think\Cache::set('invest_'.$id,$list);
    }
    return $list;
}
/**
 * 获取网站广告图片信息
 * @return  array
 */
function get_picture() { 
    //从缓存获取
    $list = \think\Cache::get('picture');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('picture')->field(true)->order("create_time desc")->select();
        if($list){
            foreach($list as $key => $value) {
                $value['image'] = json_decode($value['image'],true);
                if($value['image']){
                    $sort = array();
                    foreach ($value['image'] as $k => $val){
                        // 取得列的列表根据sort排序
                        $sort[$k] = $val['sort'];
                    }
                    array_multisort($sort, SORT_ASC, $value['image']);
                }
                $list[$key] = $value;
            }
        }
        \think\Cache::set('picture',$list);
    }
    return $list;
}
/**
 * 读取文章分类
 * @return  array
 */
function get_article_type() {
    //从缓存获取
    $list = \think\Cache::get('article_type');
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('article_type')->field(true)->order("sort asc")->select();
        \think\Cache::set('article_type',$list);
    }
    return $list;
}

/**
 * 获取文章信息
 * @param $id number 文章ID号
 * @return  array
 */
function get_article($id) { 
    //从缓存获取
    $list = \think\Cache::get('article_'.$id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('article')->field(true)->where(array('id'=>$id))->find();
        if(!$list){return false;}
        $list['content'] = htmlspecialchars_decode($list['content']);
        \think\Cache::set('article_'.$id,$list);
    }
    return $list;
}
/**
 * 会员话费充值订单分佣
 * @param $order  订单信息
 * @return  array
 */
function commision_recharg($order) {
    //获取通知设置信息
    $config = get_config('notice');
    //获取用户设置信息
    $userconfig = get_config('user');
    $goods = get_recharg($order['goods_id']);
    //计算返利金额
    $goods['dianfan'] = $order['payprice'] * $goods['dianfan'] / 100;
    $goods['jingfan'] = $order['payprice'] * $goods['jingfan'] / 100;
    $goods['zongfan'] = $order['payprice'] * $goods['zongfan'] / 100;
    $goods['huitui']  = $order['payprice'] * $goods['huitui'] / 100;
    $goods['diantui'] = $order['payprice'] * $goods['diantui'] / 100;
    $goods['jingtui'] = $order['payprice'] * $goods['jingtui'] / 100;
    $goods['zongtui'] = $order['payprice'] * $goods['zongtui'] / 100;

    //----------------------------------
    // 聚合数据-手机话费充值API调用示例代码
    //----------------------------------
    header('Content-type:text/html;charset=utf-8');
    require_once "./extend/recharge.php"; //引入文件

    //接口基本信息配置
    $appkey = $config['mappkey']; //从聚合申请的话费充值appkey
    $openid = $config['openid']; //注册聚合账号就会分配的openid，在个人中心可以查看
    $recharge = new \recharge($appkey,$openid);
    //提交话费充值
    // $telRechargeRes = $recharge->telcz($order['mobile'],intval($goods['yuecong']),randChar()); #可以选择的面额5、10、20、30、50、100、300
    $telRechargeRes = array();
    $telRechargeRes['error_code'] = 0;
    $telRechargeRes['order_id'] = $order['order_id'];
    if($telRechargeRes['error_code'] =='0'){
        //提交话费充值成功，可以根据实际需求改写以下内容
        $data = array();
        $data['number'] = $order['number'] + 1;
        $data['return_time'] = time();
        \think\Db::name('recharg_order')->where(array('order_id'=>$order['order_id']))->update($data);

        //添加话费充值记录
        $log = array();
        $log['uid'] = $order['uid'];
        $log['mobile'] = $order['mobile'];
        $log['money'] = intval($goods['yuecong']);
        $log['create_time'] = time();
        $log['order_id'] = $order['order_id'];
        $log['number'] = $data['number'];
        \think\Db::name('recharg_log')->insert($log);
    }
    
    // 找到推荐人
    $user = get_member($order['uid']);
    if(!$user['pid']){return true;}
    $tuijian = get_member($user['pid']);
    //上级会员信息
    $idlist = get_level_member($user['id']);
     // 如果你的推荐人是普通会员
    if($tuijian['level_id'] == 1) {
        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['huitui']);
        //写入日志
        member_money_log("话费充值",$tuijian['id'],"下级会员".$user['nickname']."充值".$goods['name']."返利",$goods['huitui'],$order['order_id']);
        // 找到是店长的推荐人
        $tuijiantwo = isset($idlist[0]) ? $idlist[0] : '';
        // 如果存在
        if( $tuijiantwo ) {
            \think\Db::name('member')->where(array('id'=>$tuijiantwo['id']))->setInc('money',$goods['dianfan']);
            //写入日志
            member_money_log("话费充值",$tuijiantwo['id'],"二级会员".$user['nickname']."充值".$goods['name']."返利",$goods['dianfan'],$order['order_id']);
        }
        // 找到是经理的推荐人
        $tuijianthree = isset($idlist[1]) ? $idlist[1] : '';
        // 如果存在
        if( $tuijianthree ) {
            \think\Db::name('member')->where(array('id'=>$tuijianthree['id']))->setInc('money',$goods['jingfan']);
            //写入日志
            member_money_log("话费充值",$tuijianthree['id'],"三级会员".$user['nickname']."充值".$goods['name']."返利",$goods['jingfan'],$order['order_id']);
        }
        // 找到是总监的推荐人
        $tuijianfour = isset($idlist[2]) ? $idlist[2] : '';
        // 如果存在
        if( $tuijianfour ) {
            \think\Db::name('member')->where(array('id'=>$tuijianfour['id']))->setInc('money',$goods['zongfan']);
            //写入日志
            member_money_log("话费充值",$tuijianfour['id'],"四级会员".$user['nickname']."充值".$goods['name']."返利",$goods['zongfan'],$order['order_id']);
        }
    }
    // 如果你的推荐人是店长级别
    if($tuijian['level_id'] == 2) {
        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['diantui']);
        //写入日志
        member_money_log("话费充值",$tuijian['id'],"下级会员".$user['nickname']."充值".$goods['name']."返利",$goods['diantui'],$order['order_id']);
                
        // 找到是经理的推荐人
        $tuijianthree = isset($idlist[1]) ? $idlist[1] : '';
        // 如果存在
        if( $tuijianthree ) {
            \think\Db::name('member')->where(array('id'=>$tuijianthree['id']))->setInc('money',$goods['jingfan']);
            //写入日志
            member_money_log("话费充值",$tuijianthree['id'],"二级会员".$user['nickname']."充值".$goods['name']."返利",$goods['jingfan'],$order['order_id']);
        }
        // 找到是总监的推荐人
        $tuijianfour = isset($idlist[2]) ? $idlist[2] : '';
        // 如果存在
        if( $tuijianfour ) {
            \think\Db::name('member')->where(array('id'=>$tuijianfour['id']))->setInc('money',$goods['zongfan']);
            //写入日志
            member_money_log("话费充值",$tuijianfour['id'],"三级会员".$user['nickname']."充值".$goods['name']."返利",$goods['zongfan'],$order['order_id']);
        }
    }
    // 如果你的推荐人是经理级别
    if($tuijian['level_id'] == 3) {
        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['jingtui']);
        //写入日志
        member_money_log("话费充值",$tuijian['id'],"下级会员".$user['nickname']."充值".$goods['name']."返利",$goods['jingtui'],$order['order_id']);
        
        // 找到是总监的推荐人
        $tuijianfour = isset($idlist[2]) ? $idlist[2] : '';
        // 如果存在
        if( $tuijianfour ) {
            \think\Db::name('member')->where(array('id'=>$tuijianfour['id']))->setInc('money',$goods['zongfan']);
            //写入日志
            member_money_log("话费充值",$tuijianfour['id'],"二级会员".$user['nickname']."充值".$goods['name']."返利",$goods['zongfan'],$order['order_id']);
        }
    }
    // 如果你的推荐人是总监级别
    if($tuijian['level_id'] == 4) {
        //直推奖励
        \think\Db::name('member')->where(array('id'=>$tuijian['id']))->setInc('money',$goods['zongtui']);
        //写入日志
        member_money_log("话费充值",$tuijian['id'],"下级会员".$user['nickname']."充值".$goods['name']."返利",$goods['zongtui'],$order['order_id']);
    }
}
/**
 * 清除过期购物车商品
 */
function clean_cart() {
    //删除商品数量为0的记录
    \think\Db::name('member_cart')->where(array('goods_number'=>array('LT',1)))->delete(); 
    //获取用户设置信息
    $list = get_config('user');
    if(!$list['cart_time']){return false;}
    $time = time() - ($list['cart_time'] * 60 * 60);
    //检测是否有过期的商品,有则删除
    if($goods = \think\Db::name('member_cart')->field('goods_id,product_id,goods_number')->where(array('create_time'=>array('LT', $time),'is_state'=>1))->select()){
        //修改购物车商品状态为过期
        \think\Db::name('member_cart')->where(array('create_time' => array('LT', $time)))->setField('is_state', 2);
        foreach ($goods as $key => $value) {
            //返回增加商品库存
            \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setInc('goods_number', $value['goods_number']);
            //修改后清除该商品缓存
            \think\Cache::rm('goods_'.$value['goods_id']);
            if($value['product_id']){
                \think\Db::name('goods_product')->where(array('id'=>$value['product_id']))->setInc('goods_number', $value['goods_number']);
                \think\Cache::rm('goods_product_'.$value['goods_id']);
            }
        }
    }
}

/**
 * 获取订单信息
 * @param $order_id  订单id
 * @param $uid 用户uid
 * @return  array
 */
function get_order($order_id,$uid) {
    //从缓存获取
    $list = \think\Cache::get($uid.'_order_'.$order_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('member_order')->field(true)->where(array('order_id'=>$order_id,'uid'=>$uid))->find();
        \think\Cache::set($uid.'_order_'.$order_id,$list);
    }
    return $list;
}
/**
 * 获取订单发货记录
 * @param $order_id  订单id
 * @return  array
 */
function get_member_shipping($order_id) {
    //从缓存获取
    $list = \think\Cache::get('member_shipping_'.$order_id);
    if(!$list){
        //没有缓存查询数据库，写入缓存
        $list = \think\Db::name('member_shipping')->field(true)->where(array('order_id'=>$order_id))->select();
        \think\Cache::set('member_shipping_'.$order_id,$list);
    }
    return $list;
}
/**
 * 清除过期订单自动完成订单信息
 */
function clean_order() {
    //获取用户设置
    $list = get_config('user');
    if($list['order_time']){
        $time = time() - ($list['order_time'] * 60 * 60);
        //检测是否有过期的订单
        if($order = \think\Db::name('member_order')->field('order_id,uid,goods,coupon,point')->where(array('create_time'=>array('LT', $time),'is_state'=>1))->select()){
            //修改订单状态为过期
            \think\Db::name('member_order')->where(array('create_time' => array('LT', $time),'is_state'=>1))->setField('is_state', 8);
            foreach ($order as $val) {
                //使用优惠卷
                if($val['coupon']){
                    \think\Db::name('coupon_list')->where(array('id'=>$val['coupon'],'uid'=>$id))->setField('is_state',1);
                }
                //使用积分抵扣
                if($val['point']){
                    \think\Db::name('member')->where(array('id'=>$id))->setInc('point',$val['point']);
                    //写入日志
                    member_point_log("订单过期",$id,$val['order_id'].'订单过期返回',$val['point']);
                }
                $goods = json_decode($val['goods'],true);
                foreach ($goods as $key => $value) {
                    //返回增加商品库存
                    \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setInc('goods_number', $value['goods_number']);
                    //修改后清除该商品缓存
                    \think\Cache::rm('goods_'.$value['goods_id']);
                    //修改规格库存
                    if($value['product_id']){
                        \think\Db::name('goods_product')->where(array('id'=>$value['product_id']))->setInc('goods_number', $value['goods_number']);
                        \think\Cache::rm('goods_product_'.$value['goods_id']);
                    }
                    //商品状态
                    $value['is_state'] = 8;
                    $goods[$key] = $value;
                }
                //修改订单商品状态
                $goods = json_encode($goods,JSON_UNESCAPED_UNICODE);
                \think\Db::name('member_order')->where(array('order_id'=>$val['order_id']))->setField('goods',$goods);
                //修改数据后清除缓存
                \think\Cache::rm($val['uid'].'_order_'.$val['order_id']);
            }
        }
    }
    //自动完成到期的订单
    if($list['finish_order']){
        $time = time() - ($list['finish_order'] * 24 * 60 * 60);
        if($order = \think\Db::name('member_order')->field('order_id,uid,goods')->where(array('send_time'=>array('LT', $time),'is_state'=>3))->select()){
            //修改订单状态为完成
            \think\Db::name('member_order')->where(array('send_time' => array('LT', $time),'is_state'=>3))->setField('is_state', 9);
            foreach ($order as $val) {
                $goods = json_decode($val['goods'],true);
                foreach ($goods as $key => $value) {
                    //商品状态
                    $value['is_state'] = 9;
                    $goods[$key] = $value;
                }
                //修改订单商品状态
                $goods = json_encode($goods,JSON_UNESCAPED_UNICODE);
                \think\Db::name('member_order')->where(array('order_id'=>$val['order_id']))->setField('goods',$goods);
                //修改数据后清除缓存
                \think\Cache::rm($val['uid'].'_order_'.$val['order_id']);
            }
        }
    }
}

//转帐商家
function commision_business($order) {
    //判断会员是否已返佣
    if($order['return_time']){return true;}

    //获取支付宝配置信息
    $alipay = get_payment('alipay');
    require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');

    //获取商家信息
    $business = get_business($order['uid']);
    $payprice = $order['payprice'] * $order['discount'] / 10;

    if($business){

        //提现转账
        \think\Db::name('business')->where(array('id'=>$order['uid']))->setInc('money',$payprice);
        //写入日志
        business_money_log("商家收款",$order['uid'],"商家收款",$payprice,$order['order_id']);

        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $alipay['appid'];
        $aop->rsaPrivateKey = $alipay['privatekey'];
        $aop->alipayrsaPublicKey = $alipay['publickey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayFundTransToaccountTransferRequest ();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $param = array();
        $param['out_biz_no'] = randChar();
        $param['payee_type'] = 'ALIPAY_LOGONID';
        $param['payee_account'] = $business['alipay'];
        $param['amount'] = $payprice;
        $param['payer_show_name'] = '广州南领商贸有限公司';
        $param['payee_real_name'] = $business['truename'];
        $param['remark'] = $order['order_id']."商家收款转账";

        $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($bizcontent);
        $result = $aop->execute ( $request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
            \think\Db::name('business')->where(array('id'=>$order['uid']))->setDec('money',$payprice);
            //写入日志
            business_money_log("商家收款",$order['uid'],$order['order_id']."商家收款转账",-$payprice,$param['out_biz_no']);
        }
    }
    
    \think\Db::name('business_transfer')->where(array('order_id'=>$order['order_id']))->setField('return_time',time());
    return true;
}