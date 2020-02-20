<?php
namespace app\index\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Callback 
{
    //话费充值——微信支付回调
    public function wxpay_recharg(){
        $xml = file_get_contents('php://input', 'r');
        if(empty($xml)){exit('');}
        //获取微信配置信息
        $weixin = get_payment('wxpay');

        $responseObj = simplexml_load_string ( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
        //转换成数组
        $responseArr = ( array ) $responseObj;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/wxpay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr, JSON_UNESCAPED_UNICODE)); 
        //商户支付密钥Key
        $key = $weixin['key']; 
        $sign = get_sign($responseArr,$key);
        // 判断签名是否正确  判断支付状态
        if ($responseArr['sign'] == $sign && $responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('recharg_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/orderset'));exit('SUCCESS');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '微信支付(移动端)';
            //返回金额按分为单位
            $data['paymoney'] = intval($responseArr['total_fee']) / 100;
            \think\Db::name('recharg_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            //写入用户押金余额
            if(\think\Db::name('member')->where(array('id'=>$order['uid']))->setInc('point',$order['payprice'])){
                //写入日志
                member_point_log("话费押金",$order['uid'],"话费充值押金",$order['payprice'],$order['order_id']);
            }
            
            //订单分佣
            $result = commision_recharg($order);
            //清除Api获取话费充值相关接口缓存
            \think\Cache::clear('recharg');
            exit('SUCCESS');
        }
    }

    //话费充值——支付宝支付回调
    public function alipay_recharg(){
        require_once("./extend/alipay/alipay.config.php");
        require_once("./extend/alipay/alipay_notify.class.php");
        $responseArr = empty($_POST) ? $_GET : $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/alipay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr)); 
        $alipayNotify = new \AlipayNotify($alipay_config);
        $sign = $alipayNotify->verifyNotify();
        // 判断签名是否正确  判断支付状态
        if ($sign && $responseArr['trade_status'] == 'TRADE_SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('recharg_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/orderset'));exit('success');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '支付宝支付(移动端)';
            //返回金额按元为单位
            $data['paymoney'] = $responseArr['total_fee'];
            \think\Db::name('recharg_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            //写入用户押金余额
            if(\think\Db::name('member')->where(array('id'=>$order['uid']))->setInc('point',$order['payprice'])){
                //写入日志
                member_point_log("话费押金",$order['uid'],"话费充值押金",$order['payprice'],$order['order_id']);
            }
            //订单分佣
            $result = commision_recharg($order);
            //清除Api获取话费充值相关接口缓存
            \think\Cache::clear('recharg');
            exit('success');
        }
        
        //跳转到订单
        header('Location:'.url('User/orderset'));
    }

    //升级套餐——微信支付回调
    public function wxpay_goods(){
        $xml = file_get_contents('php://input', 'r');
        if(empty($xml)){exit('');}
        //获取微信配置信息
        $weixin = get_payment('wxpay');

        $responseObj = simplexml_load_string ( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
        //转换成数组
        $responseArr = ( array ) $responseObj;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/wxpay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr, JSON_UNESCAPED_UNICODE)); 
        //商户支付密钥Key
        $key = $weixin['key']; 
        $sign = get_sign($responseArr,$key);
        // 判断签名是否正确  判断支付状态
        if ($responseArr['sign'] == $sign && $responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('member_goods_order')->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/index'));exit('SUCCESS');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '微信支付(移动端)';
            //返回金额按分为单位
            $data['paymoney'] = intval($responseArr['total_fee']) / 100;
            \think\Db::name('member_goods_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            
            //订单分佣
            $result = commision_goods($order);
            exit('SUCCESS');
        }
    }

    //升级套餐——支付宝支付回调
    public function alipay_goods(){
        require_once("./extend/alipay/alipay.config.php");
        require_once("./extend/alipay/alipay_notify.class.php");
        $responseArr = empty($_POST) ? $_GET : $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/alipay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr)); 
        $alipayNotify = new \AlipayNotify($alipay_config);
        $sign = $alipayNotify->verifyNotify();
        // 判断签名是否正确  判断支付状态
        if ($sign && $responseArr['trade_status'] == 'TRADE_SUCCESS') {
            $order = \think\Db::name('member_goods_order')->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/index'));exit('success');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '支付宝支付(移动端)';
            //返回金额按元为单位
            $data['paymoney'] = $responseArr['total_fee'];
            \think\Db::name('member_goods_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);

            //订单分佣
            $result = commision_goods($order);
            exit('success');
        }
        //跳转到订单
        header('Location:'.url('User/index'));
    }

    //投资项目——微信支付回调
    public function wxpay_invest(){
        $xml = file_get_contents('php://input', 'r');
        if(empty($xml)){exit('');}
        //获取微信配置信息
        $weixin = get_payment('wxpay');

        $responseObj = simplexml_load_string ( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
        //转换成数组
        $responseArr = ( array ) $responseObj;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/wxpay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr, JSON_UNESCAPED_UNICODE)); 
        //商户支付密钥Key
        $key = $weixin['key']; 
        $sign = get_sign($responseArr,$key);
        // 判断签名是否正确  判断支付状态
        if ($responseArr['sign'] == $sign && $responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('invest_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/myinvest'));exit('SUCCESS');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '微信支付(移动端)';
            //返回金额按分为单位
            $data['paymoney'] = intval($responseArr['total_fee']) / 100;
            \think\Db::name('invest_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            // 找到推荐人
            $user = get_member($order['uid']);
            if($user['pid']){
                //计算上级返利金额
                $money = $order['payprice'] * $order['huitui'] / 100;
                //写入用户余额
                if(\think\Db::name('member')->where(array('id'=>$user['pid']))->setInc('money',$money)){
                    //投资的项目
                    $goods = get_invest($order['goods_id']);
                    //写入日志
                    member_money_log("项目投资",$user['pid'],"下级会员".$user['nickname']."投资".$goods['name']."项目返利",$money,$order['order_id']);
                }
            }
            //增加项目已购股数
            if(\think\Db::name('invest')->where(array('id'=>$order['goods_id']))->setInc('number',$order['number'])){
                //修改后清除该套餐缓存
                \think\Cache::rm('invest_'.$order['goods_id']);
                //清除Api获取投资项目相关接口缓存
                \think\Cache::clear('invest');
            }
            exit('SUCCESS');
        }
    }

    //投资项目——支付宝支付回调
    public function alipay_invest(){
        require_once("./extend/alipay/alipay.config.php");
        require_once("./extend/alipay/alipay_notify.class.php");
        $responseArr = empty($_POST) ? $_GET : $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/alipay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr)); 
        $alipayNotify = new \AlipayNotify($alipay_config);
        $sign = $alipayNotify->verifyNotify();
        // 判断签名是否正确  判断支付状态
        if ($sign && $responseArr['trade_status'] == 'TRADE_SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('invest_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('User/myinvest'));exit('success');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '支付宝支付(移动端)';
            //返回金额按元为单位
            $data['paymoney'] = $responseArr['total_fee'];
            \think\Db::name('invest_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            // 找到推荐人
            $user = get_member($order['uid']);
            if($user['pid']){
                //计算上级返利金额
                $money = $order['payprice'] * $order['huitui'] / 100;
                //写入用户余额
                if(\think\Db::name('member')->where(array('id'=>$user['pid']))->setInc('money',$money)){
                    //投资的项目
                    $goods = get_invest($order['goods_id']);
                    //写入日志
                    member_money_log("项目投资",$user['pid'],"下级会员".$user['nickname']."投资".$goods['name']."项目返利",$money,$order['order_id']);
                }
            }
            //增加项目已购股数
            if(\think\Db::name('invest')->where(array('id'=>$order['goods_id']))->setInc('number',$order['number'])){
                //修改后清除该套餐缓存
                \think\Cache::rm('invest_'.$order['goods_id']);
                //清除Api获取投资项目相关接口缓存
                \think\Cache::clear('invest');
            }
            exit('success');
        }
        //跳转到订单
        header('Location:'.url('User/myinvest'));
    }

    //商品下单——微信支付回调
    public function wxpay_order(){
        $xml = file_get_contents('php://input', 'r');
        if(empty($xml)){exit('');}
        //获取微信配置信息
        $weixin = get_payment('wxpay');

        $responseObj = simplexml_load_string ( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
        //转换成数组
        $responseArr = ( array ) $responseObj;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/wxpay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr, JSON_UNESCAPED_UNICODE)); 
        //商户支付密钥Key
        $key = $weixin['key']; 
        $sign = get_sign($responseArr,$key);
        // 判断签名是否正确  判断支付状态
        if ($responseArr['sign'] == $sign && $responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('member_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('Index/orderList'));exit('SUCCESS');}
            //订单分佣
            $goods = commision_order($order);
            //更新订单商品支付
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '微信支付(移动端)';
            $data['paytime'] = time();
            //返回金额按分为单位
            $data['paymoney'] = intval($responseArr['total_fee']) / 100;
            $data['goods'] = json_encode($goods,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');

            exit('SUCCESS');
        }
    }

    //商品下单——支付宝支付回调
    public function alipay_order(){
        require_once("./extend/alipay/alipay.config.php");
        require_once("./extend/alipay/alipay_notify.class.php");
        $responseArr = empty($_POST) ? $_GET : $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/alipay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr)); 
        $alipayNotify = new \AlipayNotify($alipay_config);
        $sign = $alipayNotify->verifyNotify();
        // 判断签名是否正确  判断支付状态
        if ($sign && $responseArr['trade_status'] == 'TRADE_SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('member_order')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){header('Location:'.url('Index/orderList'));exit('success');}
           
            //订单分佣
            $goods = commision_order($order);
            //更新订单商品支付
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '支付宝支付(移动端)';
            $data['paytime'] = time();
            //返回金额按元为单位
            $data['paymoney'] = $responseArr['total_fee'];
            $data['goods'] = json_encode($goods,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');

            exit('success');
        }
        //跳转到订单
        header('Location:'.url('Index/orderList'));
    }
}
