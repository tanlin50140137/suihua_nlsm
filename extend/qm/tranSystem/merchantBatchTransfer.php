<?php

date_default_timezone_set('Asia/Shanghai');
$batch = "Batch" . date("ymd_His") . rand(10, 99);
 
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
</head>
<body>
<table width="75%" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <td><table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                </tr>


                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">商户批量转账</td>
                </tr>

                <form method="post" action="sendMerchantBatchTransfer.php" targe="_blank">
                    <tr>
                        <td align="left">&nbsp;&nbsp;批次号</td>
                        <td align="left">&nbsp;&nbsp;<input size="80" type="text" name="batchNo" id="batchNo"  value="<?php echo $batch ?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>

                    
                    <tr>
                        <td align="left">&nbsp;&nbsp;服务器回调地址</td>
                        <td align="left">&nbsp;&nbsp;<input size="80" type="text" name="serverCallbackUrl" id="serverCallbackUrl"  value="http://localhost:801/yopphpsdk/TranSystem/Callback/callback.php"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                   
				   
                  
                     	<tr>
		         	 <td align="left">&nbsp;&nbsp;转账详情列表:</td>
		  	          <td align="left">&nbsp;&nbsp;<textarea id="transferList" style="width: 71%;" name="transferList" rows="10"  >
[{
"requestNo": "001",
"amount": "0.01",
"toUserType": "MERCHANT",
"transferType": "USER_TO_USER",
"toUserNo": "111"
},
{
"requestNo": "002",
"amount": "0.01",
"toUserType": "MEMBER",
"transferType": "USER_TO_USER",
"toUserNo": "222"
}]</textarea>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
							
				   
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
