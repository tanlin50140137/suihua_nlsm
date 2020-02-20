<?php
error_reporting(0);
 
 
	$data=array(
    bankCard=>$_REQUEST['bankCard'],
    headBankCode=>$_REQUEST['headBankCode'],
    bankProvince=>$_REQUEST['bankProvince'],
    bankCity=>$_REQUEST['bankCity'],
    subBankCode=>$_REQUEST['subBankCode'],
    settlementType=>$_REQUEST['settlementType'],
    settlementCycle=>$_REQUEST['settlementCycle'],
    
 
	);
	
	
	
$selletInfo=json_encode($data);
 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 结算信息JSON </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    结算信息JSON
</th>
		  	</tr>
	        <tr >
				<td width="15%" align="left">&nbsp;selletInfo</td>
				<td width="5%"  align="center"> : </td>
				<td width="80"  align="left"> <textarea cols="80" rows="10"><?php echo $selletInfo;?> </textarea></td>
               
            </tr>

       
      </table>
    </body>
</html>
