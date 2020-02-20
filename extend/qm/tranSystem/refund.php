
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
</head>
<body>
<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <td><table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                </tr>

                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">退款</td>
                </tr>

                <form method="post" action="payRefund.php" targe="_blank">
                    <tr>
                        <td align="left">&nbsp;&nbsp;商户请求号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="requestNo" id="requestNo"  value="<?php echo "REFUMD".time();?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;原支付请求号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="trxRequestNo" id="trxRequestNo"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;退款金额</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="refundAmount" id="refundAmount"  value="0.10"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;退回方式</td>
                        <td align="left">&nbsp;&nbsp;
                            <input size="50" type="radio" name="refundWay" id="refundWay"  value="OLDWAY" checked/>原路退回
                            <input size="50" type="radio" name="refundWay" id="refundWay"  value="BALANCE"/>退回到账户余额
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
							
							 <tr>
                        <td align="left">&nbsp;&nbsp;指定退券</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="couponNos" id="couponNos"  value=""/></td></tr>
							
							 <tr>
                        <td align="left">&nbsp;&nbsp;后台服务通知地址</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="serverCallbackUrl" id="serverCallbackUrl"  value=""/></td></tr>
								 <tr>
                        <td align="left">&nbsp;&nbsp;前端页面通知地址</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="webCallbackUrl" id="webCallbackUrl"  value=""/></td></tr>
							
							
							
                    <tr>
                        <td align="left">&nbsp;</td>
                        <td align="left">&nbsp;&nbsp;<input type="submit" value="submit" /></td>
                    </tr>
                </form>
            </table></td>
    </tr>
</table>
</body>
</html>
