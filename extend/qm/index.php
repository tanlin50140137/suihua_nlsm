<!DOCTYPE html>
<html>
<head>
    <meta  charset=utf-8>
    <title>钱麦</title>
    <style type="text/css">
        table { border-collapse:collapse }

        td { font-size:18px; text-indent:2em; height:30px; }
    </style>
</head>
<body>
<script language="javascript" id="clientEventHandlersJS">
    <!--
    var number=6;

    function LMYC() {
        var lbmc;

        for (i=1;i<=number;i++) {
            lbmc = eval('LM' + i);

            lbmc.style.display = 'none';
        }
    }

    function ShowFLT(i) {
        lbmc = eval('LM' + i);

        if (lbmc.style.display == 'none') {
            LMYC();

            lbmc.style.display = '';
        }
        else {

            lbmc.style.display = 'none';
        }
    }
    //-->
</script>
<table width="400" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#CCCCCC">
    <th align="center" height="20" colspan="1" bgcolor="#6BBE18">
        钱麦接口演示
    </th>
    <tr>
        <td  > + <a onclick="javascript:ShowFLT(1)" href="javascript:void(null)">交易系统</a></td>
    </tr>

    <tr id="LM1"  style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
               <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/recharge.php" >用户充值</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/transfer.php" >用户转账</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/withdraw.php" >用户提现</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/getWithdrawUrl.php" >用户提现--页面版</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/queryWithdraw.php" >用户提现查询</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/merchantTransfer.php" >商户转账</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/queryTransfer.php" >转账查询</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/pay.php" >支付下单</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a href="./tranSystem/query.php" >订单查询</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/refund.php" >退款</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/refundQuery.php" >退款查询 </a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/divide.php" >分账</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/querydivide.php" >分账查询</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a href="./tranSystem/remit.php" >企业付款</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/queryRemit.php" >企业付款查询</a></td>
                </tr>
                    <tr>
                    <td style="padding-left:20px;" > --<a href="./tranSystem/merchantBatchTransfer.php" >商户批量转账</a></td>
                </tr>
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/queryBatch.php" >商户批量转账查询</a></td>
                </tr>
            </table></td>
    </tr>

    <tr>
        <td> + <a onclick="javascript:ShowFLT(2)" href="javascript:void(null)">账务系统 </a></td>
    </tr>
    <tr id="LM2" style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./tranSystem/downLoad.php" >下载对账单</a></td>
                </tr>
            </table></td>
    </tr>
    <tr>
        <td> + <a onclick="javascript:ShowFLT(3)" href="javascript:void(null)">账户系统 </a></td>
    </tr>
    <tr id="LM3" style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/registerLedgerMerchant.php" >分账方基本信息注册</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/uploadQualifications.php" >分账方资质上传</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/queryLedgerStatus.php" >查询分账方审核信息</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/queryMerchantBalance.php" >商户余额查询</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/register.php" >用户账户注册</a></td>
                </tr>
				
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/auth.php" >实名认证</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/bindCard.php" >用户绑卡</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/queryBindCardList.php" >绑卡列表查询</a></td>
                </tr>
				
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/unbindCard.php" >解绑卡</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/getPswdResetUrl.php" >重置、修改支付密码</a></td>
					
						 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/getPswdVerifyUrl.php" >验证支付密码</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/queryUserBalance.php" >用户余额查询</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./user/queryUserInfo.php" >用户信息查询</a></td>
                </tr>
		
            </table></td>
    </tr>
    <tr>
        <td> + <a onclick="javascript:ShowFLT(4)" href="javascript:void(null)">商户入网 </a></td>
    </tr>
    <tr id="LM4" style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/upload.php" >上传资质文件信息</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/registerSubMerchant.php" >提交子商户入网信息</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/querySubMerchantStatus.php" >查询子商户入网信息</a></td>
                </tr>
				 
				
            </table></td>
    </tr>
	
	    <tr>
        <td> + <a onclick="javascript:ShowFLT(5)" href="javascript:void(null)">基础数据 </a></td>
    </tr>
    <tr id="LM5" style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/province.php" >获取省份信息</a></td>
                </tr>
				
				  <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/city.php" >获取城市信息</a></td>
                </tr>
				
				  <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/county.php" >获取区县信息</a></td>
                </tr>
				 <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/cardBinInfo.php" >卡BIN识别</a></td>
                </tr>
					 <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/headBank.php" >获取总行信息</a></td>
                </tr>
						 <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/branchBank.php" >获取支行信息</a></td>
                </tr>
						 <tr>
                    <td style="padding-left:20px;" > --<a  href="./basicData/categoryCode.php" >获取商户行业类别码</a></td>
                </tr>
            </table></td>
    </tr>
	    <tr>
        <td> + <a onclick="javascript:ShowFLT(6)" href="javascript:void(null)">生成商户入网JSON信息 </a></td>
    </tr>
    <tr id="LM6" style="DISPLAY: none" >
        <td><table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/baseInfo.php" >商户基本信息</a></td>
                </tr>
				
				  <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/selletInfo.php" >结算信息</a></td>
                </tr>
				
				  <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/productInfo.php" >开通产品信息</a></td>
                </tr>
		   <tr>
                    <td style="padding-left:20px;" > --<a  href="./merchant/certificationInfo.php" >资质信息</a></td>
                </tr>
            </table></td>
    </tr>
</table>
</body>
</html></td>
</tr>
</table>

