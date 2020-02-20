<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
require_once ("../lib/YopClient3.php");
require_once ("../lib/YopRequest.php");
require_once ("../lib/Util/YopSignUtils.php");
require_once("../conf/conf.php");
function payQuery()
{
    global  $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);

    $request->addParam("trxRequestNo", $_REQUEST['trxRequestNo']);


    $response = YopClient3::post("/rest/v1.0/payplus/order/query", $request);
    $data=(array)$response;
    $result=$data['result'];
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    return $result;
}
$result=payQuery();
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 支付查詢 </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    支付查詢--返回参数
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
                <td width="25%" align="left">&nbsp;请求返回码</td>
                <td width="5%"  align="center"> : </td>
                <td width="45"  align="left"> <?php echo $result['merchantUserId'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">merchantUserId</td>
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
                <td width="30%" align="left">merchantUserId</td>
            </tr>

            <tr>
                <td width="25%" align="left">&nbsp;已支付金额</td>
                <td width="5%"  align="center"> : </td>
                <td width="35%" align="left"> <?php echo  $result['paidAmount'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">orderAmount</td>
            </tr>
            <tr>
                <td width="25%" align="left">&nbsp;支付状态</td>
                <td width="5%"  align="center"> : </td>
                <td width="35%" align="left"> <?php echo  $result['status'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">status</td>
            </tr>
            <tr >
                <td width="25%" align="left">&nbsp;支付方式</td>
                <td width="5%"  align="center"> : </td>
                <td width="45"  align="left"> <?php echo $result['payTool'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">payTool</td>
            </tr>

            <tr >
                <td width="25%" align="left">&nbsp;银行卡类别</td>
                <td width="5%"  align="center"> : </td>
                <td width="45"  align="left"> <?php echo $result['cardType'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">merchantUserId</td>
            </tr>

            <tr>
                <td width="25%" align="left">&nbsp;费率</td>
                <td width="5%"  align="center"> : </td>
                <td width="35%" align="left"> <?php echo  $result['fee'];?> </td>
                <td width="5%"  align="center"> - </td>
                <td width="30%" align="left">fee</td>
            </tr>
</table>
</body>
</html>
