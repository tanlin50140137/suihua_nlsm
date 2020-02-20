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



function queryUserInfo(){
	
       global $merchantNo;
	   global $private_key;
	   global $yop_public_key;
	     
    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);
     
    
 
	 
	 $request->addParam("merchantUserId", $_REQUEST['merchantUserId']);
	 
	
    $response = YopClient3::post("/rest/v1.0/payplus/user/queryUserInfo", $request);
	 // var_dump($response);
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
    
    return $data;
    
 }
  $array=queryUserInfo();  
  
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
<title>用户信息查询结果</title>
</head>
	<body>	
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
					用户信息查询结果
				</th>
		  	</tr>
          <tr >
				<td width="25%" align="left">&nbsp;商户用户姓名</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['name'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">name</td> 
				 
			</tr>
			   <tr >
				<td width="25%" align="left">&nbsp;商户用户注册时间</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['registerTime'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">registerTime</td> 
				 
			</tr>
			   <tr >
				<td width="25%" align="left">&nbsp;商户用户实名状态</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['caStatus'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">caStatus</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;认证时间</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['caTime'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">caTime</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;证件类型</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['credentialsType'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">credentialsType</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;证件号码</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['credentialsNo'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">credentialsNo</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;用户等级</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['memberCategory'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">memberCategory</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;证件上传认证状态</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['caStatus'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">caStatus</td> 
				 
			</tr>
					 
			   <tr >
				<td width="25%" align="left">&nbsp;商户用户实名状态</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['uploadCertificateAuthStatus'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">uploadCertificateAuthStatus</td> 
				 
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

				
			 
 

		</table>

	</body>
</html>