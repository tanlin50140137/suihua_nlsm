<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
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

function payRefund()
{
    global $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center", $yop_public_key);
    $request->addParam("requestNo", $_REQUEST['requestNo']);
    $request->addParam("trxRequestNo", $_REQUEST['trxRequestNo']); 
    $request->addParam("refundAmount", $_REQUEST['refundAmount']);
    $request->addParam("refundWay", $_REQUEST['refundWay']);
	  $request->addParam("couponNos", $_REQUEST['couponNos']);
    $request->addParam("serverCallbackUrl", $_REQUEST['serverCallbackUrl']);
	 $request->addParam("webCallbackUrl", $_REQUEST['webCallbackUrl']);
    $response = YopClient3::post("/rest/v1.0/payplus/refund/refund", $request);
    $data = (array)$response;
    $result = $data['result'];
    if ($response->validSign == 1) {
        echo "返回结果签名验证成功!\n";
    }
    return $result;
}
$result=payRefund();
?>


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 退款 </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    退款--返回参数
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
                    <td width="25%" align="left">&nbsp;退款金额</td>
                    <td width="5%"  align="center"> : </td>
                    <td width="45"  align="left"> <?php echo $result['refundedAmount'];?> </td>
                    <td width="5%"  align="center"> - </td>
                    <td width="30%" align="left">refundedAmount</td>
                </tr>

                <tr>
                    <td width="25%" align="left">&nbsp;退款状态</td>
                    <td width="5%"  align="center"> : </td>
                    <td width="35%" align="left"> <?php echo  $result['status'];?> </td>
                    <td width="5%"  align="center"> - </td>
                    <td width="30%" align="left">status</td>
                </tr>
                <tr >
                    <td width="25%" align="left">&nbsp;退款方式</td>
                    <td width="5%"  align="center"> : </td>
                    <td width="45"  align="left"> <?php echo $result['refundWay'];?> </td>
                    <td width="5%"  align="center"> - </td>
                    <td width="30%" align="left">refundWay</td>
                </tr>
</table>
</body>
</html>

