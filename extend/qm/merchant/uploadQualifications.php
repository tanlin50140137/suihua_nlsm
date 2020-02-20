<?php

 
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
                    <td colspan="2" bgcolor="#CEE7BD">分账方资质文件信息</td>
                </tr>

                <form method="post" action="sendUploadQualifications.php" targe="_blank" >
                    <tr>
                        <td align="left">&nbsp;&nbsp;分账方编号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="ledgerNo" id="ledgerNo"  value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>

                      <tr height="50">
                      <td width="15%" align="left">&nbsp;&nbsp;证件类型:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="qualificationType" required>
                             <option value="ID_CARD_FRONT">身份证正面</option>
                             <option value="ID_CARD_BACK">身份证背面</option>
                             <option value="BANK_CARD_FRONT">银行卡正面</option>
                             <option value="BANK_CARD_BACK">银行卡背面</option>
                             <option value="PERSON_PHOTO">手持身份证照片</option>
                             <option value="BUSSINESS_LICENSE">营业执照</option>
                             <option value="BUSSINESS_CERTIFICATES">工商证</option>
                             <option value="BANK_ACCOUNT_LICENCE">银行开户许可证</option>
                             <option value="ORGANIZATION_CODE">组织机构代码证</option>
                             <option value="TAX_REGISTRATION">税务登记证</option>
                </select>
            </td>
            
        </tr>
 
                    
                      
                    <tr>
                        <td align="left">&nbsp;&nbsp;文件路径</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="file" id="file"  value="/var/www/html/1.jpg"/>
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
