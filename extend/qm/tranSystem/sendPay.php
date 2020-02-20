<?php
 
require_once ("../lib/YopClient.php");
require_once ("../lib/YopClient3.php");
require_once ("../lib/Util/YopSignUtils.php");
require_once("../conf/conf.php");

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


//支付下单
function pay(){

    global  $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);


    //加入请求参数
    $request->addParam("requestNo",$_REQUEST['requestNo']);
    $request->addParam("merchantUserId", $_REQUEST['merchantUserId']); 
    $request->addParam("orderAmount", $_REQUEST['orderAmount']);
    $request->addParam("fundAmount", $_REQUEST['fundAmount']);
    $request->addParam("payTool", $_REQUEST['payTool']);
    $request->addParam("bindCardId", $_REQUEST['bindCardId']);
	$request->addParam("merchantOrderDate", $_REQUEST['merchantOrderDate']);
    $request->addParam("merchantExpireTime", $_REQUEST['merchantExpireTime']); 
    $request->addParam("bankCode", $_REQUEST['bankCode']);
    $request->addParam("trxExtraInfo", $_REQUEST['trxExtraInfo']);
    $request->addParam("serverCallbackUrl", $_REQUEST['serverCallbackUrl']);
    $request->addParam("webCallbackUrl", $_REQUEST['webCallbackUrl']);
	$request->addParam("mcc", $_REQUEST['mcc']);
    $request->addParam("productCatalog", $_REQUEST['productCatalog']); 
    $request->addParam("productName", $_REQUEST['productName']);
    $request->addParam("productDesc", $_REQUEST['productDesc']);
    $request->addParam("openId", $_REQUEST['openId']);
    $request->addParam("templateType", $_REQUEST['templateType']);
	$request->addParam("couponNos", $_REQUEST['couponNos']);
    $request->addParam("marketingExtraInfo", $_REQUEST['marketingExtraInfo']); 
    $request->addParam("merchantBizType", $_REQUEST['merchantBizType']);
    $request->addParam("divideRuleType", $_REQUEST['divideRuleType']);
    $request->addParam("divideDetail", $_REQUEST['divideDetail']);
    $request->addParam("divideCallbackUrl", $_REQUEST['divideCallbackUrl']);
	$request->addParam("accountPayMerchantNo", $_REQUEST['accountPayMerchantNo']);
	$request->addParam("ip", $_REQUEST['ip']);
    $request->addParam("payEmpowerNo", $_REQUEST['payEmpowerNo']);
    $request->addParam("merchantTerminalId", $_REQUEST['merchantTerminalId']);
    $request->addParam("merchantStoreNo", $_REQUEST['merchantStoreNo']);
	$request->addParam("platForm", $_REQUEST['platForm']);
    $request->addParam("appName", $_REQUEST['appName']);
	$request->addParam("appStatement", $_REQUEST['appStatement']);
		
	
    $response = YopClient3::post("/rest/v1.0/payplus/order/consume", $request);
	
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
 
    return $data;
    
 }
  $array=pay();  
  
 if( $array['result'] == NULL)
 {
 	echo "error:".$array['error'];
  return;}
 else{
 $result= $array['result'] ;
 //var_dump($result);
}
 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 支付 </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    支付--返回参数
</th>
		  	</tr>
	        <tr >
				<td width="35%" align="left">&nbsp;请求返回码</td>
				<td width="5%"  align="center"> : </td>
				<td width="45"  align="left"> <?php echo $result['code'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="20%" align="left">code</td>
            </tr>

        <tr>
            <td width="35%" align="left">&nbsp;请求返回信息</td>
            <td width="5%"  align="center"> : </td>
            <td width="35%" align="left"> <?php echo  $result['message'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">message</td>
        </tr>
        <tr >
            <td width="35%" align="left">&nbsp;请求号</td>
            <td width="5%"  align="center"> : </td>
            <td width="45"  align="left"> <?php echo $result['requestNo'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">requestNo</td>
        </tr>

        <tr>
            <td width="35%" align="left">&nbsp;订单金额</td>
            <td width="5%"  align="center"> : </td>
            <td width="35%" align="left"> <?php echo  $result['orderAmount'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">orderAmount</td>
        </tr>
        <tr >
            <td width="35%" align="left">&nbsp;需支付金额</td>
            <td width="5%"  align="center"> : </td>
            <td width="45"  align="left"> <?php echo $result['fundAmount'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">merchantUserId</td>
        </tr>

        <tr>
            <td width="35%" align="left">&nbsp;支付url</td>
            <td width="5%"  align="center"> : </td>
            <td width="35%" align="left"><a href="<?php echo $result['redirectUrl'];?>"><?php echo $result['redirectUrl'] ;?></a> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">redirectUrl</td>
        </tr>
        <tr>
            <td width="35%" align="left">&nbsp;支付状态</td>
            <td width="5%"  align="center"> : </td>
            <td width="35%" align="left"> <?php echo  $result['status'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">status</td>
        </tr>
        <tr >
            <td width="35%" align="left">&nbsp;实时分账状态</td>
            <td width="5%"  align="center"> : </td>
            <td width="45"  align="left"> <?php echo $result['divideCheck'];?> </td>
            <td width="5%"  align="center"> - </td>
            <td width="20%" align="left">divideCheck</td>
        </tr>

      </table>
    </body>
</html>
