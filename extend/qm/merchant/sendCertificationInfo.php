<?php
error_reporting(0);
 
  
	$data=array(
    idCardFaceUrl=>$_REQUEST['idCardFaceUrl'],
    idCardConUrl=>$_REQUEST['idCardConUrl'],
    businesslicenseUrl=>$_REQUEST['businesslicenseUrl'],
    unifiedCertificateUrl=>$_REQUEST['unifiedCertificateUrl'],
    taxRegisterCertificateUrl=>$_REQUEST['taxRegisterCertificateUrl'],
    organCodeCertificateUrl=>$_REQUEST['organCodeCertificateUrl'],
    bankOrganUrl=>$_REQUEST['bankOrganUrl'],
    storePhotoUrl=>$_REQUEST['storePhotoUrl'],
    agreementUrl=>$_REQUEST['agreementUrl'],
    settlementCard=>$_REQUEST['settlementCard'],
    scenePhoneUrl=>$_REQUEST['scenePhoneUrl'],
    industryLicenseUrl=>$_REQUEST['industryLicenseUrl'],
     
	);
	
	
	
$certificationInfo=json_encode($data);
 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 入网资质信息JSON </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    入网资质信息JSON
</th>
		  	</tr>
	        <tr >
				<td width="15%" align="left">&nbsp;certificationInfo</td>
				<td width="5%"  align="center"> : </td>
				<td width="80"  align="left"> <textarea cols="80" rows="10"><?php echo $certificationInfo;?> </textarea></td>
               
            </tr>

       
      </table>
    </body>
</html>
