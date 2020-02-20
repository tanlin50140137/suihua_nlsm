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
        <td><table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                </tr>


                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">上传资质文件信息</td>
                </tr>

                <form method="post" action="sendUpload.php" targe="_blank" >
                    <tr>
                        <td align="left">&nbsp;&nbsp;请求号</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="requestNo" id="requestNo"  value="<?php echo $requestNo ?>"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>

                    <tr>
                        <td align="left">&nbsp;&nbsp;文件校验方式</td>
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="checkStyle" id="checkStyle"  value="MD5"/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
 
                    <tr> 
                        <td align="left">&nbsp;&nbsp;文件格式</td> 
                        <td align="left">&nbsp;&nbsp;<input size="50" type="text" name="fileType" id="fileType"  value=".jpg"/> 
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr> 
                      
                    <tr>
                        <td align="left">&nbsp;&nbsp;文件</td>
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
