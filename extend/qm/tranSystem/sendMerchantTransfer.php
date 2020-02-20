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


//商户转账
function merchantTransfer(){

    global  $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);


    //加入请求参数
    $request->addParam("requestNo",$_REQUEST['requestNo']);
 
    $request->addParam("transferType", $_REQUEST['transferType']); 
    $request->addParam("amount", $_REQUEST['amount']);
    $request->addParam("toUserType", $_REQUEST['toUserType']);
    $request->addParam("toUserNo", $_REQUEST['toUserNo']);
 
    $response = YopClient3::post("/rest/v1.0/payplus/transfer/merchantTransfer", $request);
	//var_dump($response);
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
 
    return $data;
    
 }
  $array=merchantTransfer();  
  
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
    <title> 商户转账 </title>
</head>
<body>
<br /> <br />
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
            商户转账--返回参数
        </th>
    </tr>
	    <tr >
        <td width="25%" align="left">&nbsp;转账请求号</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['requestNo'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">requestNo</td>
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

     
</table>
</body>
</html>
