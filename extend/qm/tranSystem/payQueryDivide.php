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



//支付
function payQueryDivide(){

    global  $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);

    
    //加入请求参数

    $request->addParam("trxRequestNo",$_REQUEST['trxRequestNo']);
    $request->addParam("divideRequestNo", $_REQUEST['divideRequestNo']);//固定值1

    $response = YopClient3::post("/rest/v1.0/payplus/divide/query", $request);
    $data=(array)$response;
    $result=$data['result'];
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    return $result;
}
$result=payQueryDivide();
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title> 分账查询明细</title>
</head>
<body>
<br /> <br />
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
            分账查询明细--返回参数
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
				<td width="35%" align="left">&nbsp;分账详情</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"><textarea cols="70" rows="10">  <?php  if (empty($result['divideOrderList'])) {echo "";} else {echo preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", json_encode($result['divideOrderList'])), "\n";  }?>  </textarea></td>
				 
				 <td width="5%"  align="center"> - </td> 
				<td width="20%" align="left">divideOrderList</td> 
			</tr>



</table>
</body>
</html>

