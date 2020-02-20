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

class Payment
{
    //支付订单
    public function payorder(){
        //验证会员appkey获取id
        $appkey = input('get.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){
            // 进来表示未登录
            header('Location:'.url('Index/login'));exit;
        }

        //支付订单类型
        $act = input('get.act','');
        switch ($act) {
            
            //支付会员购买商品订单
            case 'order':
                //订单号
                $order_id = input('get.id','');
               
                //获取订单信息
                $order = \think\Db::name('member_order')->where(array('order_id'=>$order_id,'uid'=>$id,'is_state'=>1))->find();
                if(!$order){return view('Index/error',array('remark'=>'订单已支付！'));exit;} 
                //一分钱测试
                // $order['payprice'] = 0.01;
                //支付金额单位是分所以得乘以100
                $price = $order['payprice'] * 100;
                if($order['paytype'] == '微信支付'){
                    //获取微信配置信息
                    $weixin = get_payment('wxpay');
                    // dump($weixin);exit;
                    //判断手机微信端访问
                    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){

                        require_once("./extend/weixin/WxPay.Api.php");
                        require_once("./extend/weixin/WxPay.JsApiPay.php");

                        //①、获取用户openid
                        $tools = new \JsApiPay();
                        $openId = $tools->GetOpenid();

                        //②、统一下单
                        $input = new \WxPayUnifiedOrder();
                        $input->SetBody("会员升级");
                        $input->SetAttach("test");
                        $input->SetOut_trade_no($order['order_id']);
                        $input->SetTotal_fee($price);
                        $input->SetTime_start(date("YmdHis"));
                        $input->SetTime_expire(date("YmdHis", time() + 600));
                        $input->SetGoods_tag("test");
                        $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'].'/index/callback/wxpay_order/');
                        $input->SetTrade_type("JSAPI");
                        $input->SetOpenid($openId);
                        $order = \WxPayApi::unifiedOrder($input);
                        $jsApiParameters = $tools->GetJsApiParameters($order);
                        //赋值数据集View模板输出  

                        return view('wxpay',array('jsApiParameters'=>$jsApiParameters,'order_id'=>$order_id));
                    }else{
                        //H5支付

                        $data = array();
                        $data['appid'] = $weixin['appid'];   //微信AppID
                        $data['mch_id'] = $weixin['mchid'];   //商户号
                        $data['nonce_str'] = uniqid().rand(1111111,9999999);
                        $data['body'] = "会员升级";
                        $data['out_trade_no'] = $order['order_id'];
                        $data['total_fee'] = $price;
                        $data['spbill_create_ip'] = request()->ip();
                        $data['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/index/callback/wxpay_order/';
                        $data['trade_type'] = 'MWEB';
                        $data['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}';
                    
                        //签名
                        $data['sign'] = get_sign($data,$weixin['key']);
                        //生成xml并且生成签名
                        $postXml = create_hongbao_xml($data);

                        //提交请求
                        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                        $responseXml = curl_post_ssl($url,$postXml);
                        $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
                        //转换成数组
                        $responseArr = ( array ) $responseObj;
                        if($responseArr['return_code'] == 'SUCCESS'){
                            header('Location:'.$responseArr['mweb_url']);
                        }else{
                            // 进来表示已支付
                            return view('Index/error',array('remark'=>'支付信息有误！'));exit;
                        }
                    }
                }else if($order['paytype'] == '支付宝支付'){
                    require_once("./extend/alipay/alipay.config.php");
                    require_once("./extend/alipay/alipay_submit.class.php");

                    /**************************请求参数**************************/

                            //商户订单号，商户网站订单系统中唯一订单号，必填
                            $out_trade_no = $order_id;

                            //订单名称，必填
                            $subject = '商品下单';

                            //付款金额，必填 单位元
                            // $total_fee = 0.01;//1分钱测试
                            $total_fee = $order['payprice'];

                            //用户付款中途退出返回商户网站的地址，必填
                            $show_url = 'http://'.$_SERVER['HTTP_HOST'].'/index/index/orderdetails.html?id='.$order_id;

                            //商品描述，可空
                            $body = '';

                    /************************************************************/

                    //构造要请求的参数数组，无需改动
                    $parameter = array(
                        "service"       => 'alipay.wap.create.direct.pay.by.user',
                        "partner"       => $alipay_config['partner'],
                        "seller_id"  => $alipay_config['seller_id'],
                        "payment_type"  => $alipay_config['payment_type'],
                        "notify_url"    => 'http://'.$_SERVER['HTTP_HOST'].'/index/callback/alipay_order/',
                        "return_url"    => 'http://'.$_SERVER['HTTP_HOST'].'/index/callback/alipay_order/',
                        "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
                        "out_trade_no"  => $out_trade_no,
                        "subject"   => $subject,
                        "total_fee" => $total_fee,
                        "show_url"  => $show_url,
                        "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
                        "body"  => $body,
                        //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
                        //如"参数名"    => "参数值"   注：上一个参数末尾需要“,”逗号。
                    );

                    //建立请求
                    $alipaySubmit = new \AlipaySubmit($alipay_config);
                    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
                    echo $html_text;
                }else{
                    return view('Index/error',array('remark'=>'请选择支付方式！'));exit;
                }
                break;
            
            default:
                return view('Index/error',array('remark'=>'网络异常，请稍后重试！'));exit;
                break;
        }
    }
}
