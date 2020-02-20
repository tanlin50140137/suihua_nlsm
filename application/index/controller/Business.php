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

// 指定允许其他域名访问  
header('Access-Control-Allow-Origin:*');  
// 响应类型  
header('Access-Control-Allow-Methods:POST');  
// 响应头设置  
header('Access-Control-Allow-Headers:x-requested-with,content-type');  

class Business extends \think\Controller
{
    public function index(){
        // 验证用户是否登录
        if(!session('?business')){
            // 进来表示已登录
            $this->redirect('Business/login');exit;
        }
        //查找数据
        $list = get_business(session('business.id'));
        $list['total'] = \think\Db::name('business_money_log')->where(array('uid'=>$list['id'],'money'=>array('GT',0)))->sum('money');
     
        //赋值数据集View模板输出  
        $data = array();
        $data['list'] = $list;
        return view('index',$data);
    }

    //会员登陆
    public function login()
    {
        if (request()->isPost()) {
            //拼接条件
            $where = array();
            //用户账号
            $where['username'] = input('post.username','');
            //用户密码
            $password = input('post.password','');
            $where['password'] = password(sha1($password));
            $where['is_state'] = 1;

            $list = \think\Db::name('business')->field(true)->where($where)->find();
            //判断用户是否登陆成功
            if($list){
                $appkey = password(md5($list['username'].time()));
                $list['appkey'] = $appkey;
                \think\Db::name('business')->where(array('id'=>$list['id']))->setField('appkey', $appkey);
                //修改数据后清除缓存
                \think\Cache::rm('business_'.$list['id']);

                // if(!$list['code']){
                    //引入phpqrcode库文件
                    require_once("./extend/phpqrcode.php");
                    $url = 'http://'.$_SERVER['SERVER_NAME'].'/index/Business/payment?id='.$list['id'];
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
                    imagecopymerge($dst, $src, 65, 65, 0, 0, 25, 25, 100);
                    @unlink($QIMG); //删除二维码与logo的合成图片
                    @unlink($QRB);  //删除服务器上二维码图片
                
                    header("Content-type: image/png");
                    imagepng($dst,$_SERVER['DOCUMENT_ROOT'].$code);//根据需要生成相应的图片
                    imagedestroy($dst);
                    imagedestroy($src);
                    //如果是文件直接删除
                    unlink($_SERVER['DOCUMENT_ROOT'].$thumb);
            
                    $list['code'] = $code;
                // }
                $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
                $list['code'] = 'http://'.$_SERVER['SERVER_NAME'].$list['code'];
                $list['total'] = \think\Db::name('business_money_log')->where(array('uid'=>$list['id'],'money'=>array('GT',0)))->sum('money');
                $list['total'] = sprintf("%.2f",$list['total']);
                //登陆成功 把数据存放到$_SESSION
                session('business',$list);
                //显示后台首页
                return json(array('success'=>true,'info'=>'登录成功！','list'=>$list));exit;
            }else{
                // 登录失败
                return json(array('success'=>false,'info'=>'账号信息有误！'));exit;
            }
        }else{
            // 验证用户是否登录
            if(session('?business')){
                // 进来表示已登录
                $this->redirect('Business/index');exit;
            }
            //赋值数据集View模板输出  
            $data = array();
            return view('login',$data);
        }
       
        
    }  

    //会员注册
    public function register()
    {
        $pid = input('pid');
        return view('register',array('pid'=>$pid));
    } 

    //收款二维码
    public function code(){
        //查找数据
        $list = get_business(session('business.id'));

        $code = \think\Db::name('business')->where(array('id'=>session('business.id')))->value('code');
        if(!$code){
            //引入phpqrcode库文件
            require_once("./extend/phpqrcode.php");
            $url = 'http://app.nlsm168.com/index/Business/payment?id='.session('business.id');
            $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
            $matrixPointSize = "4"; //生成图片大小 ：1到10
            $QRcode = new \QRcode();
            //保存文件
            $code = "/public/qrcode/".session('user.id').'.jpg';
            $QRcode::png($url, $_SERVER['DOCUMENT_ROOT'].$code, $errorCorrectionLevel, $matrixPointSize);
            \think\Db::name('business')->where(array('id'=>session('business.id')))->setField('code',$code);


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
            imagecopymerge($dst, $src, 65, 65, 0, 0, 25, 25, 100);
            @unlink($QIMG); //删除二维码与logo的合成图片
            @unlink($QRB);  //删除服务器上二维码图片
        
            header("Content-type: image/png");
            imagepng($dst,$_SERVER['DOCUMENT_ROOT'].$code);//根据需要生成相应的图片
            imagedestroy($dst);
            imagedestroy($src);
            //如果是文件直接删除
            unlink($_SERVER['DOCUMENT_ROOT'].$thumb);
        }
        //赋值数据集View模板输出 
        $data = array();
        $data['code'] = $code;
        $data['list'] = session('business');
        return view('code',$data);
    }

    //商家收款
    public function payment(){
        $id = input('id');
        //赋值数据集View模板输出  
        $data = array();
        //查找数据
        $data['list'] = get_business($id);
        if(!$data['list']){
            return error('商家信息有误！');
        }

        $openId = session('openId');
        //判断手机微信端访问
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            if(!$openId){
                require_once("./extend/weixin/WxPay.Api.php");
                require_once("./extend/weixin/WxPay.JsApiPay.php");

                //①、获取用户openid
                $tools = new \JsApiPay();
                $openId = $tools->GetOpenid();
                session('openId',$openId);
            }
        }

        $list = array();

        if($openId){
            $money = \think\Db::name('setmeal')->min('minprice');
            $where = array();
            $where['remark'] = $openId;
            $where['is_state'] = 2;
            $where['rebate_time'] = 0;
            $where['paymoney'] = array('GT',$money);
            $list = \think\Db::name('business_transfer')->where($where)->order('order_id desc')->select();
        }
        // dump($list);exit;

        $data['data'] = $list;
        return view('payment',$data);
    }

    //转帐商家
    public function transfer(){
        $id = input('id');
        $list = get_business($id);
        if(!$list){return error('商家信息有误！');}
        $money = input('money');

        $data = array();
        $data['order_id'] = randChar();                                 //订单号
        $data['uid'] = $id;                                             //会员id
        $data['create_time'] = time();                                  //下单时间
        $data['paytype'] = '支付宝支付';                                 //支付方式
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $data['paytype'] = '微信支付'; 
        }
        $data['discount'] = $list['discount']; 
        $discount = $money * $list['discount'] / 10;
        $data['huitui'] = $money-$discount; 
        $data['huitui'] = $data['huitui'] * $list['huitui'] / 100;
        $data['pid'] = $list['pid']; 
        $data['payprice'] = $money; 

        if (\think\Db::name('business_transfer')->insert($data)) {
            //一分钱测试
            // $data['payprice'] = 0.01;

            //判断手机微信端访问
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                //微信端支付

                require_once("./extend/weixin/WxPay.Api.php");
                require_once("./extend/weixin/WxPay.JsApiPay.php");

                //①、获取用户openid
                $tools = new \JsApiPay();
                $openId = $tools->GetOpenid();

                //②、统一下单
                $input = new \WxPayUnifiedOrder();
                $input->SetBody("商家收款");
                $input->SetAttach("test");
                $input->SetOut_trade_no($data['order_id']);
                $input->SetTotal_fee($data['payprice'] * 100);
                $input->SetTime_start(date("YmdHis"));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetGoods_tag("test");
                $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'].'/index/business/wxpay/');
                $input->SetTrade_type("JSAPI");
                $input->SetOpenid($openId);
                $order = \WxPayApi::unifiedOrder($input);
                $jsApiParameters = $tools->GetJsApiParameters($order);
                //赋值数据集View模板输出  
                return view('wxpay',array('jsApiParameters'=>$jsApiParameters,'money'=>$money,'id'=>$data['order_id']));
            }else{
                require_once("./extend/alipay/alipay.config.php");
                require_once("./extend/alipay/alipay_submit.class.php");

                /**************************请求参数**************************/

                        //商户订单号，商户网站订单系统中唯一订单号，必填
                        $out_trade_no = $data['order_id'];

                        //订单名称，必填
                        $subject = '商家收款';

                        //付款金额，必填 单位元
                        // $total_fee = 0.01;//1分钱测试
                        $total_fee = $data['payprice'];

                        //用户付款中途退出返回商户网站的地址，必填
                        $show_url = 'http://'.$_SERVER['HTTP_HOST'].'/index/business/payment.html?id='.$id;

                        //商品描述，可空
                        $body = '';

                /************************************************************/

                //构造要请求的参数数组，无需改动
                $parameter = array(
                    "service"       => 'alipay.wap.create.direct.pay.by.user',
                    "partner"       => $alipay_config['partner'],
                    "seller_id"  => $alipay_config['seller_id'],
                    "payment_type"  => $alipay_config['payment_type'],
                    "notify_url"    => 'http://'.$_SERVER['HTTP_HOST'].'/index/business/alipay/',
                    "return_url"    => 'http://'.$_SERVER['HTTP_HOST'].'/index/business/alipay/',
                    "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
                    "out_trade_no"  => $out_trade_no,
                    "subject"   => $subject,
                    "total_fee" => $total_fee,
                    "show_url"  => $show_url,
                    // "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
                    "body"  => $body,
                    //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
                    //如"参数名"    => "参数值"   注：上一个参数末尾需要“,”逗号。
                );

                //建立请求
                $alipaySubmit = new \AlipaySubmit($alipay_config);
                $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
                echo $html_text;
            }
        }else{
            return error('信息有误，请重新提交！');
        }
    }


    public function wxpay(){
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
            $order = \think\Db::name('business_transfer')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){exit('SUCCESS');}
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '微信支付(移动端)';
            //返回金额按分为单位
            $data['paymoney'] = intval($responseArr['total_fee']) / 100;
            $data['remark'] = $responseArr['openid'];

            \think\Db::name('business_transfer')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            
            //订单分佣
            $result = commision_business($order);
            exit('SUCCESS');
        }
    }

    //支付宝支付回调
    public function alipay(){
        require_once("./extend/alipay/alipay.config.php");
        require_once("./extend/alipay/alipay_notify.class.php");
        $responseArr = empty($_POST) ? $_GET : $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/paylog/alipay_'.$responseArr['out_trade_no'].'.txt',json_encode($responseArr)); 
        $alipayNotify = new \AlipayNotify($alipay_config);
        $sign = $alipayNotify->verifyNotify();
        // 判断签名是否正确  判断支付状态
        if ($sign && $responseArr['trade_status'] == 'TRADE_SUCCESS') {
            //处理数据库操作 例如修改订单状态 
            $order = \think\Db::name('business_transfer')->field(true)->where(array('order_id'=>$responseArr['out_trade_no'],'is_state'=>1))->find();
            if(!$order){
                header("Location:".url("Setmeal/index")."?id=".$responseArr['out_trade_no']."&money=".$responseArr['total_fee']);
                exit('success');
            }
            //更新订单支付信息
            $data = array();
            $data['is_state'] = 2;
            $data['paytype'] = '支付宝支付(移动端)';
            //返回金额按元为单位
            $data['paymoney'] = $responseArr['total_fee'];
            // $data['remark'] = $responseArr['buyer_logon_id'];
            \think\Db::name('business_transfer')->where(array('order_id'=>$responseArr['out_trade_no']))->update($data);
            
            //订单分佣
            $result = commision_business($order);
            header("Location:".url("Setmeal/index")."?id=".$responseArr['out_trade_no']."&money=".$responseArr['total_fee']);
            exit('success');
        }
        header("Location:".url("Setmeal/index")."?id=".$responseArr['out_trade_no']."&money=".$responseArr['total_fee']);
    }

    // 用户退出操作
    public function logout(){
        //清空$_SESSION
        session('business',null);
        header('Location:'.url('Business/login'));exit;
    }

    public function moneylog(){
         //查找数据
        $list = \think\Db::name('business')->field('appkey,money')->where(array('id'=>session('business.id')))->find();
        //赋值数据集View模板输出  
        $data = array();
        $data['list'] = $list;
        return view('moneylog',$data);
    }
}
