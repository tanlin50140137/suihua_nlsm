<?php
namespace app\api\controller;
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

class Index
{
    public function index()
    {
        // \think\Db::name('business_transfer')->where(array('is_state'=>1))->delete();
        // dump($list);
exit;
        
        // $data = array();
        // $data['order_id'] = '2018090685843103626';                                 //订单号
        // $data['uid'] = '4';                                             //会员id
        // $data['create_time'] = '1536211529';                                  //下单时间
        // $data['paytype'] = '微信支付';                                 //支付方式
        
        // $data['discount'] = '9'; 
        // $data['huitui'] = '0'; 
        // $data['pid'] = '26'; 
        // $data['payprice'] = '713'; 

        // \think\Db::name('business_transfer')->insert($data);exit;

        // //处理数据库操作 例如修改订单状态 
        // $order = \think\Db::name('business_transfer')->field(true)->where(array('order_id'=>'2018090685843103626'))->find();
        // if(!$order){exit('SUCCESS');}
        // //更新订单支付信息
        // $data = array();
        // $data['is_state'] = 2;
        // $data['paytype'] = '微信支付(移动端)';
        // //返回金额按分为单位
        // $data['paymoney'] = '713';
        // $data['remark'] = 'oP_DM0TeVWqxBDLKYIOsilK5qcnU';

        // \think\Db::name('business_transfer')->where(array('order_id'=>'2018090685843103626'))->update($data);
        
        // //订单分佣
        // $result = commision_business($order);
            
        // return view();exit;
        
        //易宝支付
        require_once ("./extend/qm/lib/YopClient.php");
        require_once ("./extend/qm/lib/YopClient3.php");
        require_once ("./extend/qm/lib/Util/YopSignUtils.php");
        require_once ("./extend/qm/conf/conf.php");

        $request = new \YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);
        
        $requestNo = randChar();
        //注册用户
        $request->addParam("requestNo", $requestNo);
        $request->addParam("merchantUserId", $requestNo);

        $response = \YopClient3::post("/rest/v1.0/payplus/user/register", $request);
        
        $merchantUserId = $requestNo;
        $returnUrl = 'http://'.$_SERVER['HTTP_HOST'].'/api/index/payment';

        //实名认证
        // $request->addParam("requestNo", $requestNo);
        // $request->addParam("merchantUserId", $merchantUserId); 
        // $request->addParam("merchantUserId", $merchantUserId); 
        // $request->addParam("serverCallbackUrl", $returnUrl);
        // $request->addParam("webCallbackUrl", $returnUrl);
        // $request->addParam("returnUrl", $returnUrl);
        // $request->addParam("clientSource", 'MOBILE');
        // $request->addParam("templateType", 'APP');
     
        // $response = \YopClient3::post("/rest/v1.0/payplus/user/auth", $request);

        //绑定银行卡
        // $response = \YopClient3::post("/rest/v1.0/payplus/user/bindCard", $request);

        //支付订单
        $order_id = randChar();
        $payprice = '0.01';
        $payTool = 'SALESB2C';
        $bankCode = '001581044020';

        //加入请求参数
        $request->addParam("requestNo",$order_id);
        $request->addParam("merchantUserId", $merchantUserId); 
        $request->addParam("orderAmount", $payprice);
        $request->addParam("fundAmount", $payprice);
        $request->addParam("payTool", $payTool);
        $request->addParam("bindCardId", '');
        $request->addParam("merchantOrderDate", date('Y-m-d H:i:s'));
        $request->addParam("merchantExpireTime", ''); 
        $request->addParam("bankCode", $bankCode);
        $request->addParam("trxExtraInfo", '');
        $request->addParam("serverCallbackUrl", $returnUrl);
        $request->addParam("webCallbackUrl", $returnUrl);
        $request->addParam("mcc", '3101');
        $request->addParam("productCatalog", '3101'); 
        $request->addParam("productName", 'iphone x');
        $request->addParam("productDesc", '256G');
        $request->addParam("openId", '');
        $request->addParam("templateType", 'APP');
        $request->addParam("couponNos", '');
        $request->addParam("marketingExtraInfo", ''); 
        $request->addParam("merchantBizType",'');
        $request->addParam("divideRuleType", '');
        $request->addParam("divideDetail",'');
        $request->addParam("divideCallbackUrl", '');
        $request->addParam("accountPayMerchantNo", '');
        $request->addParam("ip", '120.79.16.38');
        // $request->addParam("payEmpowerNo", $_REQUEST['payEmpowerNo']);
        // $request->addParam("merchantTerminalId", $_REQUEST['merchantTerminalId']);
        // $request->addParam("merchantStoreNo", $_REQUEST['merchantStoreNo']);
        // $request->addParam("platForm", $_REQUEST['platForm']);
        // $request->addParam("appName", $_REQUEST['appName']);
        // $request->addParam("appStatement", $_REQUEST['appStatement']);
            
        
        $response = \YopClient3::post("/rest/v1.0/payplus/order/consume", $request);

        // //取得返回结果
        $result = object_array($response);
        $url = $result['result']['redirectUrl'];
        header("Location:".$url);
        // return json(array('success'=>true,'info'=>'','list'=>$url));
    }

    public function payment(){

        return view();
    }
}

function object_array($array) { 
    if(is_object($array)) { 
        $array = (array)$array; 
     } if(is_array($array)) { 
         foreach($array as $key=>$value) { 
             $array[$key] = object_array($value); 
             } 
     } 
     return $array; 
}