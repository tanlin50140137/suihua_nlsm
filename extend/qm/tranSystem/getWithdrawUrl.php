<?php

date_default_timezone_set('Asia/Shanghai');
$requestNo = "Withdraw" . date("ymd_His") . rand(10, 99);

?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
</head>
<body>
<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <td><table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                </tr>


                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">用户提现--页面版</td>
                </tr>

                <form method="post" action="sendGetWithdrawUrl.php" targe="_blank">
                    <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;商户请求号</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="requestNo" id="requestNo"  value="<?php echo $requestNo ?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
   
                    <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;商户用户标识</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="merchantUserId" id="merchantUserId"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
					       <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;Web端返回地址</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="webCallbackUrl" id="webCallbackUrl"  value="http://10.151.31.134/test/qm/Callback/fcallback.php"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                 
					 	<tr>
                        <td width="30%" align="left">&nbsp;&nbsp;后台服务通知地址</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="serverCallbackUrl" id="serverCallbackUrl"  value="http://10.151.31.134/test/qm/Callback/callback.php"/> &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
					     <tr>
		                	<td align="left">&nbsp;&nbsp;商户回退页面:</td>
		            	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="returnUrl" id="returnUrl"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
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
                      <td width="15%" align="left">&nbsp;&nbsp;提现周期:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="withdrawDay" required>
                             <option value="0">即时</option>
                             <option value="1">T+1 工作日</option>
                             
                             </select>
                      </td>    
					  </tr>
					<tr>
                        <td align="left">&nbsp;</td>
                        <td align="left">&nbsp;&nbsp;<input type="submit" value="submit" /></td>
                    </tr>
                </form>
                <tr>
                    <td height="5" bgcolor="#6BBE18" colspan="2"></td>
                </tr>
            </table></td>
    </tr>
</table>
</body>
</html>
