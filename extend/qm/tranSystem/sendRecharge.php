<?php
/**
 * Created by PhpStorm.
 * User: wilson
 * Date: 16/6/17
 * Time: 18:44
 */
require_once ("../lib/YopClient.php");
require_once ("../lib/YopClient3.php");
require_once ("../lib/Util/YopSignUtils.php");
require_once("../conf/conf.php");



//充值
function recharge(){

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
	$request->addParam("merchantExpireTime", $_REQUEST['merchantExpireTime']);
    $request->addParam("merchantOrderDate", $_REQUEST['merchantOrderDate']); 
    $request->addParam("serverCallbackUrl",  $_REQUEST['serverCallbackUrl']);
    $request->addParam("webCallbackUrl",  $_REQUEST['webCallbackUrl']);
    $request->addParam("templateType",  $_REQUEST['templateType']);


    $response = YopClient3::post("/rest/v1.0/payplus/order/recharge", $request);
    $data=(array)$response;
    $result=$data['result'];
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    return $result;
}
$result=recharge();
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title> 用户充值 </title>
</head>
<body>
<br /> <br />
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
            用户充值--返回参数
        </th>
    </tr>
    <tr >
        <td width="25%" align="left">&nbsp;请求返回码</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['code'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">code</td>
    </tr>

    <tr>
        <td width="25%" align="left">&nbsp;请求返回信息</td>
        <td width="5%"  align="center"> : </td>
        <td width="35%" align="left"> <?php echo  $result['message'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">message</td>
    </tr>
    <tr >
        <td width="25%" align="left">&nbsp;请求号</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['requestNo'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">requestNo</td>
    </tr>

    <tr>
        <td width="25%" align="left">&nbsp;订单金额</td>
        <td width="5%"  align="center"> : </td>
        <td width="35%" align="left"> <?php echo  $result['orderAmount'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">orderAmount</td>
    </tr>
    <tr >
        <td width="25%" align="left">&nbsp;需支付金额</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['fundAmount'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">fundAmount</td>
    </tr>

    <tr>
        <td width="25%" align="left">&nbsp;支付url</td>
        <td width="5%"  align="center"> : </td>
        <td width="35%" align="left"> <a href="<?php echo $result['redirectUrl'];?>"><?php echo $result['redirectUrl'] ;?></a>  </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">redirectUrl</td>
    </tr>
    <tr>
        <td width="25%" align="left">&nbsp;支付状态</td>
        <td width="5%"  align="center"> : </td>
        <td width="35%" align="left"> <?php echo  $result['status'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">status</td>
    </tr>
    <tr >
        <td width="25%" align="left">&nbsp;实时分账状态</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['divideCheck'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">divideCheck</td>
    </tr>

</table>
</body>
</html>
