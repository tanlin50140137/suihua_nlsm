<?php

date_default_timezone_set('Asia/Shanghai');
$requestNo = "Divide" . date("ymd_His") . rand(10, 99);

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
                    <td colspan="2" bgcolor="#CEE7BD">订单分账</td>
                </tr>

                <form method="post" action="payDivide.php" targe="_blank">
                    <tr>
                        <td align="left">&nbsp;&nbsp;请求号</td>
                        <td align="left">&nbsp;&nbsp;<input size="100" type="text" name="requestNo" id="requestNo"  value="<?php echo $requestNo ?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>

                    <tr>
                        <td align="left">&nbsp;&nbsp;原支付订单号</td>
                        <td align="left">&nbsp;&nbsp;<input size="100" type="text" name="trxRequestNo" id="trxRequestNo"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;分账详情</td>
                        <td align="left">&nbsp;&nbsp;<input size="100" type="text" name="divideDetail" id="divideDetail"  value="" placeholder="例:BL12345678901260:AMOUNT0.01|BL12345678901261:AMOUNT0.2"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
                    <tr>
                        <td align="left">&nbsp;&nbsp;分账回调地址</td>
                        <td align="left">&nbsp;&nbsp;<input size="100" type="text" name="serverCallbackUrl" id="serverCallbackUrl"  value="http://localhost:801/yopphpsdk/Callback/callback.php"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
							
							 <tr>
                      <td width="20%" align="left">&nbsp;&nbsp;分账规则类型</td>
                      <td width="80%" align="left">&nbsp;
                             <select name="divideRuleTypeEnum" required>
                             <option value="DETAIL">DETAIL</option>
                             <option value="CONSTANT">CONSTANT</option>
							  <option value="SUM">SUM</option>
                             
                            </select>
                       </td>   </tr>
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
