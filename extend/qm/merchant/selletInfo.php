 <html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>结算信息生成小工具</title>
 

</head>
<body>
<form  action="sendSelletInfo.php"  method="post"target="_blank">

  <table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
        <tr>
            <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
                结算信息JSON
            </th>
        </tr>

        <tr height="50">
            <td width="25%" align="left">银行账户&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="60" name="bankCard" value=""> </td>
            <td width="15%">bankCard<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">开户银行总行&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="60" name="headBankCode" value=""> </td>
            <td width="15%">headBankCode<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">开户银行省&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="60" name="bankProvince" value=""> </td>
            <td width="15%">bankProvince<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">开户银行市&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="60" name="bankCity" value=""> </td>
            <td width="15%">bankCity<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">开户行支行&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="60" name="subBankCode" value=""> </td>
            <td width="15%">subBankCode<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">结算方式&nbsp;:</td>
            <td width="60%" align="left">
                <select name="settlementType">
                    <option value="SELFSETTLEMENT">自助结算</option>
                    <option value="TERMSETTLEMENT">定期结算</option>
                </select>
            <td width="15%">settlementType<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left"> 结算周期&nbsp;:</td>
            <td width="60%" align="left">
                <select name="settlementCycle">
                    <option value="TCYCLE">T+1</option>
                    <option value="DCYCLE">D+1</option>
                </select>
            <td width="15%">settlementCycle<span style="color:red" >&nbsp;*</span></td>
        </tr>

        <tr height="50">
            <td></td>
            <td style="padding-left: 250px"><input type="submit" value="提交"></td>
        </tr>

    </table>
