<?php
 
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
                    <td colspan="2" bgcolor="#CEE7BD">绑卡列表查询</td>
                </tr>

                <form method="post" action="sendUnbindCard.php" targe="_blank" >
               </tr>
		      <tr>
		  	<td align="left">&nbsp;商户请求号:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="requestNo" id="requestNo"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  
 
		    <tr>
		  	<td align="left">&nbsp;商户用户标识:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="merchantUserId" id="merchantUserId"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  
		    <tr>
		  	<td align="left">&nbsp;需要解绑的绑卡ID:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bindId" id="bindId"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  
		  
		    <tr>
		  	<td align="left">&nbsp;解绑卡原因:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="reason" id="reason"  value="测试"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  	    <tr>
		  	<td align="left">&nbsp;密码验证结果:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="token" id="token"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
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
