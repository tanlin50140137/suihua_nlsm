<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>商户基本信息生成JSON</title>
  

</head>
 
	<body>
		<table width="80%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
					基本信息JSON	
				</th>
		  	</tr> 

			<form method="post" action="sendBaseInfo.php" target="_blank">
	 		
       <tr height="30">
            <td width="25%" align="left">直接上级商编&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="pMerchantNo" value=""> </td>
            <td width="15%">pMerchantNo<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="30">
            <td width="25%" align="left">商户类型&nbsp;:</td>
            <td width="60%" align="left">
                <select name="merchantType">
                    <option value="ENTERPRISE">企业</option>
                    <option value="PERSONSENGAGE">个体工商</option>
                </select>
            <td width="15%">merchantType<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">商户全称&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="merchantName" value=""> </td>
            <td width="15%">merchantName<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">商户简称&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="merchantSName" value=""> </td>
            <td width="15%">merchantSName<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">证件类型&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="certificateType" value="BUSINESSLICENCE"> </td>
            <td width="15%">certificateType<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">证件号&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="certificateNo" value=""> </td>
            <td width="15%">certificateNo </td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">法人姓名&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="customerName" value=""> </td>
            <td width="15%">customerName<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">法人身份证号&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="idCardNo" value=""> </td>
            <td width="15%">idCardNo<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">商户一级分类&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="fCategory" value=""> </td>
            <td width="15%">fCategory<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">商户二级分类&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="sCategory" value=""> </td>
            <td width="15%">sCategory<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">经营范围&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="merchantScope" value=""> </td>
            <td width="15%">merchantScope<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">经营地址- 省&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="province" value=""> </td>
            <td width="15%">province<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">经营地址- 市&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="city" value=""> </td>
            <td width="15%">city<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">经营地址- 区/县&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="area" value=""> </td>
            <td width="15%">area<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">经营地址具体地址&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="address" value=""> </td>
            <td width="15%">address<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">联系人姓名&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="linkMan" value=""> </td>
            <td width="15%">linkMan<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">联系人手机&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="linkMobile" value=""> </td>
            <td width="15%">linkMobile<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">联系人邮箱&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="linkEmail" value=""> </td>
            <td width="15%">linkEmail<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">税务登记证编号&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="taxCertificateNo" value=""placeholder="商户类型为企业证件类型为营业执照则必填"> </td>
            <td width="15%">taxCertificateNo<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">开户许可证编号&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="openCertificateNo" value=""placeholder="商户类型为企业则必填"> </td>
            <td width="15%" >openCertificateNo<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left"> 组织机构代码&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="organCertificateNo" value="" placeholder="商户类型为企业证件类型为营业执照则必填"> </td>
            <td width="15%">organCertificateNo<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">组织机构代码证是否长期有效&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="organType" value=""> </td>
            <td width="15%">organType<span style="color:red" >&nbsp;*</span></td>
        </tr>
        <tr height="50">
            <td width="25%" align="left">组织机构代码有效期&nbsp;:</td>
            <td width="60%" align="left"><input type="text" size="50" name="organExpirEndDate" value=""placeholder="证件类型为营业执照,并且有效期为0时则必填"> </td>
            <td width="15%">organExpirEndDate<span style="color:red" >&nbsp;*</span></td>
        </tr>

        <tr height="50">
            <td></td>
            <td style="padding-left: 250px"><input type="submit" value="提交"></td>
        </tr>

    </table>
