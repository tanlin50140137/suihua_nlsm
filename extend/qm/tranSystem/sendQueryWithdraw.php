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


//提现
function queryWithdraw(){

    global  $private_key;
    global $yop_public_key;
    global $merchantNo;


    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);


    //加入请求参数
    $request->addParam("withdrawRequestNo",$_REQUEST['withdrawRequestNo']);
 
     
   


    $response = YopClient3::post("/rest/v1.0/payplus/withdraw/query", $request);
	
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
 
    return $data;
    
 }
  $array=queryWithdraw();  
  
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
    <title> 用户提现查询 </title>
</head>
<body>
<br /> <br />
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
            用户提现查询--返回参数
        </th>
    </tr>
	    <tr >
        <td width="25%" align="left">&nbsp;提现状态</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['status'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">status</td>
       </tr>
  <tr >
        <td width="25%" align="left">&nbsp;银行编码</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['bankCode'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">bankCode</td>
       </tr>
	     <tr >
        <td width="25%" align="left">&nbsp;卡号后四位</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['cardLast'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">cardLast</td>
       </tr>
	     <tr >
        <td width="25%" align="left">&nbsp;提现金额</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['amount'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">amount</td>
       </tr>
	     <tr >
        <td width="25%" align="left">&nbsp;用户姓名</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['userName'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">userName</td>
       </tr>
	        <tr >
        <td width="25%" align="left">&nbsp;绑定手机号</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['bindMobileNo'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">bindMobileNo</td>
       </tr>
	     <tr >
        <td width="25%" align="left">&nbsp;身份证号</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['credentialsNo'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">credentialsNo</td>
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
