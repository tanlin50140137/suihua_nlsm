<?php

date_default_timezone_set('Asia/Shanghai');
$requestNo = "QYFK" . date("ymd_His") . rand(10, 99);

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
                    <td colspan="2" bgcolor="#CEE7BD">企业付款</td>
                </tr>

                <form method="post" action="payRemit.php" targe="_blank">
                    <tr>
                        <td align="left">&nbsp;&nbsp;商户请求号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="requestNo" id="requestNo"  value="<?php echo $requestNo; ?>" />
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;后台服务通知地址</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="serverCallbackUrl" id="serverCallbackUrl"  value="http://localhost:801/yopphpsdk/Callback/callback.php"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;付款到账时间</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="urgency" id="urgency"  value="" placeholder="1：加急出款 0非加急出款"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
					 <td align="left">&nbsp;&nbsp;付款详情：</td>
                    </tr><tr>
                        <td align="left">&nbsp;&nbsp;收款方银行卡号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="cardNo" id="cardNo"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                  
                    <tr>
                        <td align="left">&nbsp;&nbsp;银行账户类型</td>
                        <td align="left">&nbsp;&nbsp;
                            <input size="50" type="radio" name="bankAccountType" id="bankAccountType"  value="pr" checked/>对私
                            <input size="50" type="radio" name="bankAccountType" id="bankAccountType"  value="pu"/>对公
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;付款类型</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="remitType" id="remitType"  value="AMOUNT"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
							 <tr>  
                        <td align="left">&nbsp;&nbsp;留言信息</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="leaveWord" id="leaveWord"  value="易宝支付"/>
                            </td></tr>
							
								 <tr>  
                        <td align="left">&nbsp;&nbsp;开户行所在省</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="province" id="province"  value=""/>
                            </td></tr>
							
								 <tr>  
                        <td align="left">&nbsp;&nbsp;开户行所在市</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="city" id="city"  value=""/>
                            </td></tr>
								 <tr>  
                        <td align="left">&nbsp;&nbsp;银行编码</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="bankCode" id="bankCode"  value=""/>
                            </td></tr>
								 <tr>  
                        <td align="left">&nbsp;&nbsp;总行名称</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="bankName" id="bankName"  value=""/>
                            </td></tr>
								 <tr>  
                        <td align="left">&nbsp;&nbsp;支行名称</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="branchBankName" id="branchBankName"  value=""/>
                            </td></tr>
                    <tr> 
                        <td align="left">&nbsp;&nbsp;打款金额</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="value" id="value"  value="0.01"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>  
                        <td align="left">&nbsp;&nbsp;收款人姓名</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="userName" id="userName"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>  
                        <td align="left">&nbsp;&nbsp;收款人手机号码</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="payeeMobile" id="payeeMobile"  value=""/>
                            </td></tr>
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
