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
                    <td colspan="2" bgcolor="#CEE7BD">验证支付密码</td>
                </tr>

                <form method="post" action="sendGetPswdVerifyUrl.php" targe="_blank" >
               </tr>
	 
		    <tr>
		  	<td align="left">&nbsp;商户请求号:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="requestNo" id="requestNo"  value="<?php  echo $requestNo ?>"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
 
		    <tr>
		  	<td align="left">&nbsp;商户用户标识:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="merchantUserId" id="merchantUserId"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  
		    <tr>
                      <td width="15%" align="left">&nbsp;&nbsp;业务类型:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="tokenBizType" required>
                             <option value="WITHDRAW">提现</option>
                             <option value="TRANSFER">转账</option>
							 <option value="UN_BIND_CARD">解绑卡</option>
                             <option value="VALIDATE_PWD">验密</option>
                             </select>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span>
                      </td>   
			</tr>
		  
		  
		    <tr>
		  	<td align="left">&nbsp;返回地址:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="returnUrl" id="returnUrl"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  	    <tr>
		  	<td align="left">&nbsp;页面回调地址:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="webCallbackUrl" id="webCallbackUrl"  value=""/></td>
      </tr>
		  	  	  
	       <tr>
                      <td width="15%" align="left">&nbsp;&nbsp;模板类型:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="templateType" required>
                             <option value="WAP">WAP-显示H5页面title</option>
                             <option value="APP">APP-不显示H5页面title</option>
                             </select>
                      </td>   
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
