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



function queryUserBalance(){
	
       global $merchantNo;
	   global $private_key;
	   global $yop_public_key;
	     
    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);
     
    
 
	 
	 $request->addParam("merchantUserId", $_REQUEST['merchantUserId']);
	 
	
    $response = YopClient3::post("/rest/v1.0/payplus/user/queryUserBalance", $request);
	 // var_dump($response);
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    //取得返回结果
    $data=object_array($response);
    
    return $data;
    
 }
  $array=queryUserBalance();  
  
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
<title>用户余额查询结果</title>
</head>
	<body>	
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
					用户余额查询结果
				</th>
		  	</tr>
          <tr >
				<td width="25%" align="left">&nbsp;用户余额</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['balance'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">balance</td> 
				 
			</tr>
			   <tr >
				<td width="25%" align="left">&nbsp;可用余额</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['availableAmount'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">availableAmount</td> 
				 
			</tr>
			   <tr >
				<td width="25%" align="left">&nbsp;冻结金额</td>
				<td width="5%"  align="center"> : </td> 
				<td width="45"  align="left"> <?php echo $result['freezeAmount'] ;?>  </td>
				 <td width="5%"  align="center"> - </td> 
				<td width="30%" align="left">freezeAmount</td> 
				 
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