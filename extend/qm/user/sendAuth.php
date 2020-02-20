<?php
include '../conf/conf.php';
require_once ("../lib/YopClient3.php");

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



function auth(){
	
       global $merchantNo;
	   global $private_key;
	   global $yop_public_key;
	     
    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);
     
    
 
	 $request->addParam("requestNo", $_REQUEST['requestNo']);
	 $request->addParam("merchantUserId", $_REQUEST['merchantUserId']); 
	 $request->addParam("serverCallbackUrl", $_REQUEST['serverCallbackUrl']);
	 $request->addParam("webCallbackUrl", $_REQUEST['webCallbackUrl']);
	 $request->addParam("returnUrl", $_REQUEST['returnUrl']);
	 $request->addParam("clientSource", $_REQUEST['clientSource']);
	 $request->addParam("templateType", $_REQUEST['templateType']);
	 
    $response = YopClient3::post("/rest/v1.0/payplus/user/auth", $request);
	 // var_dump($response);
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
    
    return $data;
    
 }
  $array=auth();  
  
 if( $array['result'] == NULL)
 {
 	echo "error:".$array['error'];
  return;}
 else{
 $result= $array['result'] ;
  // var_dump($result);
}
?> 


<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>实名认证返回参数</title>
</head>
	<body>	
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
					实名认证返回参数
				</th>
		  	</tr>

				<tr >
				<td width="25%" align="left">&nbsp;返回码</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['code'];?> </td>
				<td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">code</td> 
			</tr>

			<tr>
				<td width="25%" align="left">&nbsp;返回信息</td>
				<td width="5%"  align="center"> : </td> 
				<td width="35%" align="left"> <?php echo $result['message'];?> </td>
				<td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">message</td> 
			</tr>

				<tr >
				<td width="25%" align="left">&nbsp;重定向地址</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['redirectUrl'] ;?>  </td>
				 
				 
			</tr>
			 
 

		</table>

	</body>
</html>