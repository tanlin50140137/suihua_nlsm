<?php

date_default_timezone_set('Asia/Shanghai');
$requestNo = "Transfer" . date("ymd_His") . rand(10, 99);

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
                    <td colspan="2" bgcolor="#CEE7BD">商户转账</td>
                </tr>

                <form method="post" action="sendMerchantTransfer.php" targe="_blank">
                    <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;商户请求号</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="requestNo" id="requestNo"  value="<?php echo $requestNo ?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
  <tr>
                      <td width="30%" align="left">&nbsp;&nbsp;转入方类型</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="toUserType" required>
                             <option value="MEMBER">用户</option>
                             <option value="MERCHANT">商户</option>
                             
                           </select>&nbsp;<span style="color:#FF0000;font-weight:100;">*</span>
                     </td>   
					 </tr>
                    <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;转账金额</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="amount" id="amount"  value="0.01"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
					     
                    <tr>
                        <td width="30%" align="left">&nbsp;&nbsp;转入方编号</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="toUserNo" id="toUserNo"  value=""/>&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
				 
                    
					<tr>
                        <td width="30%" align="left">&nbsp;&nbsp;转账类型</td>
                        <td width="70%" align="left">&nbsp;&nbsp;<input size="50" type="text" name="transferType" id="transferType"  value="USER_TO_USER"/> &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
					 
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
