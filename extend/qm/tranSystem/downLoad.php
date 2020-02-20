<?php
date_default_timezone_set("PRC");
$nowtime = time();
$rq = date("Y-m-d", $nowtime);
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
    <meta name="generator" content="FFKJ.Net" />
    <link rev="MADE" href="mailto:FFKJ@FFKJ.Net">
    <title>对账文件下载</title>
    <link rel="stylesheet" type="text/css" href="../Skins/Admin_Style.Css" />
    <script language="JavaScript" src="muban/mydate.js"></script>
</head>
<body>
<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <td><table width="100%" border="0" align="center" cellpadding="5" cellspacing="1">
                </tr>

                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">对账文件下载</td>
                </tr>

                <form method="post" action="sendDownLoad.php" targe="_blank">
                    <tr>
                        <td align="left">&nbsp;&nbsp;对账单日期</td>
                        <td align="left">&nbsp;&nbsp;<input type="text" name="checkDate" onfocus="MyCalendar.SetDate(this)" value=""/>
                            &nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td></tr>
                    <tr>
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