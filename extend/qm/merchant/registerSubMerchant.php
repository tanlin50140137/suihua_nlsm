<?php

date_default_timezone_set('Asia/Shanghai');
$requestNo = "QM" . date("ymd_His") . rand(10, 99);

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
</head>
<body>
<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <td><table width="100%" border="0" align="left" cellpadding="5" cellspacing="1">
                </tr>


                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">子商户入网</td>
                </tr>

                <form method="post" action="sendRegisterSubMerchant.php" targe="_blank" >
                    
                        </tr>
		  
		    <tr>
		  	<td align="left">&nbsp;请求编号:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="requestNo" id="requestNo"  value="<?php  echo $requestNo ?>"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  	<tr>
		  	<td align="left">&nbsp;&nbsp;商户基本信息:</td>
		  	<td align="left">&nbsp;&nbsp;<textarea id="baseInfo" style="width: 69%;" name="baseInfo" rows="5"  > </textarea>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span>  <a href="./baseInfo.php" target="_Blank">生成JSON</a> </td>
      </tr>
 	  	<tr>
		  	<td align="left">&nbsp;&nbsp;结算信息:</td>
		  	<td align="left">&nbsp;&nbsp;<textarea id="selletInfo" style="width: 69%;" name="selletInfo" rows="5"  > </textarea>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span><a href="./selletInfo.php"target="_Blank">生成JSON</a></td>
      </tr>
 
		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;开通产品信息:</td>
		  	<td align="left">&nbsp;&nbsp;<textarea id="productInfo" style="width: 69%;" name="productInfo" rows="5"  > </textarea>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span><a href="./productInfo.php" target="_Blank">生成JSON</a></td>
      </tr> 
	  
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;资质信息:</td>
		  	<td align="left">&nbsp;&nbsp;<textarea id="certificationInfo" style="width: 69%;" name="certificationInfo" rows="5"  > </textarea>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span><a href="./certificationInfo.php" target="_Blank">生成JSON</a></td>
      </tr> 
	  
		  <tr>
		  	<td align="left">&nbsp;</td>
		  	<td align="left">&nbsp;&nbsp;<input type="submit" value="submit" /></td>
      </tr>
            </table></td>
    </tr>
</table>
</body>
</html>
