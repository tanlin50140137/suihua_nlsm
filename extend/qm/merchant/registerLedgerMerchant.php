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
        <td><table width="100%" border="0" align="left" cellpadding="5" cellspacing="1">
                </tr>


                <tr>
                    <td colspan="2" bgcolor="#CEE7BD">分账方入网</td>
                </tr>

                <form method="post" action="sendRegisterSubMerchant.php" targe="_blank" >
                    
                        </tr>
		  
		    <tr>
		  	<td align="left">&nbsp;商户请求号:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="requestNo" id="requestNo"  value="<?php  echo $requestNo ?>"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
		  	<tr>
		  	<td align="left">&nbsp;&nbsp;分账方所在省份:</td>
		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="province" id="province"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
 	  	<tr>
		  	<td align="left">&nbsp;&nbsp;分账方所在城市:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="city" id="city"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr>
 
		  	  	 <tr height="50">
                      <td width="15%" align="left">&nbsp;&nbsp;商户类型:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="customerStyle" required>
                             <option value="PERSON">个人</option>
                             <option value="ENTERPRISE">企业</option>         
                </select>
            </td> </tr> 
	  
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;营业执照号:</td>
		  		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="businessLicence" id="businessLicence"  value=""/> </td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;签约名称:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="signedName" id="signedName"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;身份证号:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="idCard" id="idCard"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  
	  	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;法人姓名:</td>
		  		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="legalPerson" id="legalPerson"  value=""/> </td>
      </tr> 
	  	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;联系人:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="contactor" id="contactor"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;绑定手机:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bindMobile" id="bindMobile"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  
	  	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;邮箱:</td>
		  		  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="email" id="email"  value=""/> </td>
      </tr> 
	  
	  
	  	  	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;结算银行卡号:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bankAccountNumber" id="bankAccountNumber"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;开户名:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="accountName" id="accountName"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  	  	  		  	  	  	  	 <tr height="50">
                      <td width="15%" align="left">&nbsp;&nbsp;卡类型:</td>
                      <td width="70%" align="left">&nbsp;
                             <select name="bankAccountType" required>
                             <option value="PRIVATE_CASH">对私</option>
                             <option value="PUBLIC_CASH">对公</option>         
                </select>
            </td> </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;开户行:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bankName" id="bankName"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  	  	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;开户省:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bankCardProvince" id="bankCardProvince"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;开户市:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="bankCardCity" id="bankCardCity"  value=""/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;最低结算额:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="minSettleAmount" id="minSettleAmount"  value="0.01"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
	  		  	  	<tr>
		  	<td align="left">&nbsp;&nbsp;风险预存期:</td>
		  			  	<td align="left">&nbsp;&nbsp;<input size="45" type="text" name="riskReserveDay" id="riskReserveDay"  value="1"/>&nbsp;&nbsp;<span style="color:#FF0000;font-weight:100;">*</span></td>
      </tr> 
		  <tr>
		  	<td align="left">&nbsp;</td>
		  	<td align="left">&nbsp;&nbsp;<input type="submit" value="submit" /></td>
      </tr>
            </table></td>
    </tr>
</table>
</body>
</html>
