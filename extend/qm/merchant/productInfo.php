 <html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>开通产品信息生成JSON</title>
    <style type="text/css">
        body {
            text-align: center
        }

        div {
            display: inline-block;
            width: 80%;
            border: solid 1px #6BBE18;
            text-align: center;
            font-weight: bold;
        }

        span {
            display: block;
            text-align: center;
            margin: 30px 0 30px 0;
        }

        table {
            border-collapse: collapse;
            display: inline-block;
            text-align: center;
            margin: 0px 0 30px 0;
        }

        td, th {
            border: solid 2px #6BBE18;
            width: 150px;
        }

        tr {
            height: 20px;
        }

        .close {
            display: none;
        }

    </style>


<script type="text/javascript">
    function control(a) {
        var id = a.id + "_value";
        var value = document.getElementById(id);
        if (value.className == "" || value.className == null) {
            value.className = "close";
            var input = value.getElementsByTagName("input");
            for (var i = 0; i < input.length; i++) {
                input[i].required = false;
            }
        } else {
            value.className = "";
            var input = value.getElementsByTagName("input");
            for (var i = 0; i < input.length; i++) {                
				if(input[i].type != "radio" && input[i].type != "checkbox" ){
					input[i].required = true;
				}
            }
        }
    }

    function payController(a) {
        var id = a.id + "_SEL";
        var value = document.getElementById(id);
        if (value.className == "" || value.className == null) {
            value.className = "close";
            var input = value.getElementsByTagName("input");
            for (var i = 0; i < input.length; i++) {
                input[i].required = false;
            }
        } else {
            value.className = "";
            var input = value.getElementsByTagName("input");

            for (var i = 0; i < input.length; i++) {
				
				if(input[i].type != "radio" && input[i].type != "checkbox" ){
					input[i].required = true;
				}

            }
        }
    }

    function accountController(a) {
        var id = a.id + "_PAY";
        var value = document.getElementById(id);
        if (value.className == "" || value.className == null) {
            value.className = "close";
            var input = value.getElementsByTagName("input");
            for (var i = 0; i < input.length; i++) {
                input[i].required = false;
            }
        } else {
            value.className = "";
            var input = value.getElementsByTagName("input");

            for (var i = 0; i < input.length; i++) {
				
				if(input[i].type != "radio" && input[i].type != "checkbox" ){
					input[i].required = true;
				}
            }

        }

    }

    function RemitController(a) {
        var id = a.id + "_PAY";
        var value = document.getElementById(id);
        if (value.className == "" || value.className == null) {
            value.className = "close";
            var input = value.getElementsByTagName("input");
            for (var i = 0; i < input.length; i++) {
                input[i].required = false;
            }
        } else {
            value.className = "";
            var input = value.getElementsByTagName("input");

            for (var i = 0; i < input.length; i++) {
                if(input[i].type != "radio" && input[i].type != "checkbox" ){
					input[i].required = true;
				}

            }
        }
    }

    function check() {
        //判断是否勾选了支付场景
        var payScenarioMap = document.getElementsByName("adslTypeMap[]");
        var scenarioFlag = false;
        for (var i = 0; i < payScenarioMap.length; i++) {
            if (payScenarioMap[i].checked && !scenarioFlag) {
                scenarioFlag = true;
            }
        }

        //判断支付产品是否有未填写
        var payProductMap = document.getElementsByName("payProductMap[]");
        var productFlag = false;
        for (var i = 0; i < payProductMap.length; i++) {
            if (payProductMap[i].checked) {
                var id = payProductMap[i].id + "_SEL";
                var div = document.getElementById(id);
                var inputs = div.getElementsByTagName("input");
                for (var j = 0; j < inputs.length; j++) {
                    if (inputs[j].value != "") {
                        productFlag = true;
                        break;
                    } else {
                        productFlag = false;
                    }
                }
            }
        }
		
		//判断是否勾选用户认证
        var realnameProduct = document.getElementById("USERREALNAMEAUTH");
		var customerauthtypeMap = document.getElementsByName("customerauthtype");		
		
        var realnameFlag = true;
		if (realnameProduct.checked)
		{
			realnameFlag = false;
			for (var i = 0; i < customerauthtypeMap.length; i++) {
				if (customerauthtypeMap[i].checked)
				{
					realnameFlag = true;
				}
			}
		}
		if (!realnameFlag)
		{
			alert("用户认证类型勾选错误！");
			return false;
		}
		
		//判断是否勾选是否付款到银行
        var remitbankProduct = document.getElementById("REMITBANK");
		var remitScenarioMap = document.getElementsByName("remitScenario[]");		
		
        var remitbankFlag = true;
		if (remitbankProduct.checked)
		{
			remitbankFlag = false;
			for (var i = 0; i < remitScenarioMap.length; i++) {
				if (remitScenarioMap[i].checked)
				{
					remitbankFlag = true;
				}
			}
		}
		if (!remitbankFlag)
		{
			alert("付款到银行类型勾选错误！");
			return false;
		}
		

        //判断是否勾选了出款产品
        var remitflag = true;
        var remiteProductMap = document.getElementsByName("remiteProductMap[]");
        if (remiteProductMap.checked) {
			var remitflag = false;
            for (var i = 0; i < remiteProductMap.length; i++) {
                if (remiteProductMap[i].checked) {
                    var id = remiteProductMap[i].id + "_PAY";
                    var div = document.getElementById(id);
                    var inputs = div.getElementsByTagName("input");
                    for (var j = 0; j < inputs.length; j++) {
                        if (inputs[j].value != "") {
                            remitflag = true;
                            break;
                        } else {
                            remitflag = false;
                        }
                    }
                }
            }

        }
		if (!scenarioFlag) {
			alert("商户支付场景填写有误！");
			return false;
		}else if (!productFlag) {
			alert("商户支付产品填写有误！");
			return false;
		} else if (!scenarioFlag) {
			alert("商户产品填写有误！");
			return false;
		} else if (!remitflag) {
			alert("商户出款产品填写有误！");
			return false;
		}
    }
</script>
</head>
<body>
<form id="myForm" action="sendProductInfo.php" target="_blank" method="post">
    <div style="background: #6BBE18;" >
        productInfo生成JSON
    </div>
        <div>
		<span>商户支付场景：
		<input type="checkbox" name="adslTypeMap[]" id="WEB" onclick="control(this)" value="WEB">WEB网站
		<input type="checkbox" name="adslTypeMap[]" id="H5" onclick="control(this)" value="H5">H5页面
		<input type="checkbox" name="adslTypeMap[]" id="app" onclick="control(this)" value="APP">APP
		<input type="checkbox" name="adslTypeMap[]" value="WCHAT">公众号支付
		<input type="checkbox" name="adslTypeMap[]" value="FACETOFACE">面对面支付
		<input type="checkbox" name="adslTypeMap[]" value="ALI">生活号支付
		</span>
        </div>

        <div id="WEB_value" class="close">
            <table style="margin: 0">
            <span>商户接入网址：<input name="webUrl">(用于WEB页面)</span>
			 <span>测试账号:<input id="webname" name="webname"></span>
			 <span>测试密码:<input id="webpasswd" name="webpasswd"></span>
            </table>
        </div>
        <div id="H5_value" class="close">
            <table style="margin: 0">
            <span>商户接入网址：<input name="webUrl_h5">(用于H5页面)</span>
			 <span>测试账号:<input name="webname_h5"></span>
			 <span>测试密码:<input name="webpasswd_h5"></span>
            </table>
        </div>

        <div id="app_value" class="close">
            <table style="margin: 0">
                <tr>
                    <td style="border: none;"><span>APP名称：<input name="appName"></span></td>
                    <td style="border: none;"><span>APP下载地址：<input name="appDownUrl"></span></td>
                </tr>
            </table>
        </div>
        </div>
        <div>
		<span>商户支付产品：
		<input type="checkbox" name="payProductMap[]" id="ONE_KEY_PAY" value="ONE_KEY_PAY" onclick="payController(this)">一键支付
		<input type="checkbox" name="payProductMap[]" id="NET_BANK" value="NET_BANK" onclick="payController(this)">网银支付
		<input type="checkbox" name="payProductMap[]" id="BIND_CARD_PAY" value="BIND_CARD_PAY"
               onclick="payController(this)">绑卡支付
		<input type="checkbox" name="payProductMap[]" id="USER_SCAN_PAY" value="USER_SCAN_PAY"
               onclick="payController(this)">用户扫码
		<input type="checkbox" name="payProductMap[]" id="MERCHANT_SCAN_PAY" value="MERCHANT_SCAN_PAY"
               onclick="payController(this)">商家扫码
		<input type="checkbox" name="payProductMap[]" id="EWALLET_PAY" value="EWALLET_PAY" onclick="payController(this)">钱包支付
		<input type="checkbox" name="payProductMap[]" id="OFFICIAL_ACCOUNT_PAY" value="OFFICIAL_ACCOUNT_PAY"
               onclick="payController(this)">公众号支付
		<input type="checkbox" name="payProductMap[]" id="ZFB_SHH" value="ZFB_SHH" onclick="payController(this)">生活号支付
              <input type="checkbox" name="payProductMap[]" id="ACCOUNT_PAY" value="ACCOUNT_PAY" onclick="payController(this)">商户账户支付
		</span>
        </div>


        <div id="ONE_KEY_PAY_SEL" class="close">
            <span style="background: #6BBE18;">一键支付</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>借记卡</td>
                    <td>百分比</td>
                    <td><input type="text" name="onePayDebitFee">%</td>
                </tr>
                <tr>
                    <td>贷记卡</td>
                    <td>百分比</td>
                    <td><input type="text" name="onePayCreditFee">%</td>
                </tr>
            </table>
        </div>

        <div id="NET_BANK_SEL" class="close">
            <span style="background: #6BBE18;">网银支付</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>网银B2C</td>
                    <td>百分比</td>
                    <td><input type="text" name="cyBankB2CFee">%</td>
                </tr>
                <tr>
                    <td>网银B2B</td>
                    <td>单笔</td>
                    <td><input type="text" name="cyBankB2BFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="BIND_CARD_PAY_SEL" class="close">
           <span style="background: #6BBE18;">绑卡支付</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>绑卡支付借记卡</td>
                    <td>百分比</td>
                    <td><input type="text" name="bindCardDebitFee">%</td>
                </tr>
                <tr>
                    <td>绑卡支付贷记卡</td>
                    <td>百分比</td>
                    <td><input type="text" name="bindCardCreditFee">%</td>
                </tr>
                <tr>
                    <td>小额免密</td>
                    <td>
                        <input type="radio" name="isSmall" id="0" value="0">不开通
                        <label for="0"></label>
                        <input type="radio" name="isSmall" id="1" value="1">开通
                        <label for="1"></label>
                    </td>
                    <td>  </td> 
                </tr>
            </table>
        </div>
        <div id="USER_SCAN_PAY_SEL" class="close">
            <span style="background: #6BBE18;">用户扫码</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>微信</td>
                    <td>百分比</td>
                    <td><input type="text" name="memberScanWXFee">%</td>
                </tr>
                <tr>
                    <td>支付宝</td>
                    <td>百分比</td>
                    <td><input type="text" name="memberScanAliFee">%</td>
                </tr>

            </table>
        </div>
        <div id="MERCHANT_SCAN_PAY_SEL" class="close">
            <span style="background: #6BBE18;">商家扫码</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>微信</td>
                    <td>百分比</td>
                    <td><input type="text" name="merchantScanWXFee">%</td>
                </tr>
                <tr>
                    <td>支付宝</td>
                    <td>百分比</td>
                    <td><input type="text" name="merchantScanAliFee">%</td>
                </tr>

            </table>
        </div>
        <div id="EWALLET_PAY_SEL" class="close">
            <span style="background: #6BBE18;">钱包支付</span>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>支付宝 H5标准版</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayAliFee">%</td>
                </tr>

                <tr>
                    <td>支付宝 H5正式版</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayAli1Fee">%</td>
                </tr>
				     <tr>
                    <td>支付宝 H5专业版</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayAli2Fee">%</td>
                 <tr>
				 <tr> <tr>
				    <tr>
                    <td>微信 H5专业版</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayWXFee">%</td>
                </tr>

                <tr>
                    <td>微信 H5标准版</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayWX1Fee">%</td>
                </tr>
				     <tr>
                    <td>微信 App</td>
                    <td>百分比</td>
                    <td><input type="text" name="walletPayWXAPPFee">%</td>
                </tr>
            </table>
        </div>
        <div id="OFFICIAL_ACCOUNT_PAY_SEL" class="close">
            <span style="background: #6BBE18;">公众号支付</span>
            <table>
                <tr>
                    <td>商户公众微信号：</td>
                    <td><input name="merchantWChat"></td>
                    <td>公众服务号APPID：</td>
                    <td><input name="merchantAppidWChat"></td>
                    <td>推荐关注服务号APPID：</td>
                    <td><input name="recomAppidWChat"></td>
                </tr>
                <tr>

                    <td>支付授权目录1：</td>
                    <td><input name="firstPayAuthList"></td>
                    <td>支付授权目录2：</td>
                    <td><input name="secondPayAuthList"></td>
                    <td>支付授权目录3：</td>
                    <td><input name="thirdPayAuthList"></td>
                </tr>
                <tr>
                    <td>支付授权目录4：</td>
                    <td><input name="fourPayAuthList"></td>
                    <td>支付授权目录5：</td>
                    <td><input name="fivePayAuthList"></td>
                </tr>
            </table>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>公众号支付</td>
                    <td>百分比</td>
                    <td><input type="text" name="wChatPayFee">%</td>
                </tr>
            </table>
        </div>

        <div id="ZFB_SHH_SEL" class="close">
            <span style="background: #6BBE18;">生活号支付</span>
            <table>
                <tr>
                    <td>支付号PID：</td>
                    <td><input name="alichat"></td>
                </tr>
            </table><br>
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>生活号支付</td>
                    <td>百分比</td>
                    <td><input type="text" name="alichatFee">%</td>
                </tr>
            </table>
        </div>
    
	
	<div id="ACCOUNT_PAY_SEL" class="close">
            <span style="background: #6BBE18;">商户账户支付</span>
          
            <table>
                <tr>
                    <th>支付产品</th>
                    <th>计费策略</th>
                    <th>交易手续费</th>
                </tr>
                <tr>
                    <td>商户账户</td>
                    <td>百分比</td>
                    <td><input type="text" name="merchantAccountPayFee">%</td>
                </tr>
            </table>
        </div>

        <div>
		<span>账户产品：
		<input type="checkbox" name="accountProductMap[]" id="USERREALNAMEAUTH" value="USER_REAL_NAME_AUTH"
               onclick="accountController(this)">用户认证
		<input type="checkbox" name="accountProductMap[]" id="AccountBINDCARD" value="BIND_CARD"
               onclick="accountController(this)">绑卡
		<input type="checkbox" name="accountProductMap[]" id="USERRECHARGE" value="USER_RECHARGE"
               onclick="accountController(this)">用户充值
        <input type="checkbox" name="accountProductMap[]" id="ACCOUNTTRANSFER" value="ACCOUNT_TRANSFER"
               onclick="accountController(this)">账户间转账
		<input type="checkbox" name="accountProductMap[]" id="MERCHANTACCOUNTPAY" value="MERCHANT_ACCOUNT_PAY"
               onclick="accountController(this)">会员账户支付
		<input type="checkbox" name="accountProductMap[]" id="WITHDRAW" value="WITHDRAW"
               onclick="accountController(this)">提现

		</span>
        </div>
        <div id="USERREALNAMEAUTH_PAY" class="close">
            <span>用户认证类型：<input type="radio" name="customerauthtype" id="normal" value="normal">普通版
		<input type="radio" name="customerauthtype" id="standard" value="standard">标准版
                <input type="radio" name="customerauthtype" id="advance" value="advance">增强版
         </span>
      
            <span style="background: #6BBE18;">用户认证</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>用户认证</td>
                    <td><input type="text" name="authFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="AccountBINDCARD_PAY" class="close">
            <span style="background: #6BBE18;">绑卡</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>绑卡</td>
                    <td><input type="text" name="bindCardFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="USERRECHARGE_PAY" class="close">
            <span style="background: #6BBE18;">用户充值</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>用户充值</td>
                    <td><input type="text" name="memberRechargeFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="ACCOUNTTRANSFER_PAY" class="close">
            <span style="background: #6BBE18;">账户间转账</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>账户间转账</td>
                    <td><input type="text" name="accountTransFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="MERCHANTACCOUNTPAY_PAY" class="close">
            <span style="background: #6BBE18;">会员账户支付</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>百分比</th>
                </tr>

                <tr>
                    <td>会员账户支付</td>
                    <td><input type="text" name="accountPayFee">%</td>
                </tr>
            </table>
        </div>
        <div id="WITHDRAW_PAY" class="close">
            <span style="background: #6BBE18;">提现</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>提现</td>
                    <td><input type="text" name="withdrawFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="NETBANK_PAY" class="close">
            <span style="background: #6BBE18;">子商户或分账方认证</span>
            <table>
                <tr>

                    <th>账户产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>子商户或分账方认证</td>
                    <td><input type="text" name="subAccountAuthFee">元/笔</td>
                </tr>
            </table>
        </div>

        <div>
		<span>出款产品：
		<input type="checkbox" name="remiteProductMap[]" id="ORDERSHARED" value="ORDER_SHARED"
               onclick="RemitController(this)">订单分账
		<input type="checkbox" name="remiteProductMap[]" id="MERCHANTRECHARGE" value="MERCHANT_RECHARGE"
               onclick="RemitController(this)">商户充值
		<input type="checkbox" name="remiteProductMap[]" id="REMITACCOUNT" value="REMIT_ACCOUNT"
               onclick="RemitController(this)">付款到账户
        <input type="checkbox" name="remiteProductMap[]" id="SUBMERCHANTSETTLEMENT" value="SUB_MERCHANT_SETTLEMENT"
             onclick="RemitController(this)">子商户结算
        <input type="checkbox" name="remiteProductMap[]" id="ENTERPRISEREMIT" value="ENTERPRISEREMIT"
             onclick="RemitController(this)">企业付款
        <input type="checkbox" name="remiteProductMap[]" id="REMITBANK" value="REMIT_BANK"
             onclick="RemitController(this)">付款到银行
        <input type="checkbox" name="remiteProductMap[]" id="REMITSETTLEMENT" value="REMIT_SETTLEMENT"
			  onclick="RemitController(this)">D+1结算
		</span>
        </div>
		
        </div>
        <div id="ORDERSHARED_PAY" class="close">
            <span style="background: #6BBE18;">订单分账</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>订单分账</td>
                    <td><input type="text" name="splitOrderFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="SUBMERCHANTSETTLEMENT_PAY" class="close">
            <span style="background: #6BBE18;">子商户结算</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>子商户结算</td>
                    <td><input type="text" name="subAccountSettleFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="MERCHANTRECHARGE_PAY" class="close">
            <span style="background: #6BBE18;">商户充值</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>商户充值</td>
                    <td><input type="text" name="rechargeFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="REMITACCOUNT_PAY" class="close">
            <span style="background: #6BBE18;">付款到账户</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>付款到账户</td>
                    <td><input type="text" name="payToAccountFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="ENTERPRISEREMIT_PAY" class="close">
            <span style="background: #6BBE18;">企业付款</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>企业付款</td>
                    <td><input type="text" name="enterprisePayFee">元/笔</td>
                </tr>
            </table>
        </div>
        <div id="REMITBANK_PAY" class="close">
        <span style="background: #6BBE18;">付款到银行</span>
			<table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>普通</td>
                    <td><input type="text" name="payToBankNormalFee">%</td>
                </tr>
				  <tr>
                    <td>实时</td>
                    <td><input type="text" name="payToBankActualFee">%</td>
                </tr>
				  <tr>
                    <td> 7*24 小时</td>
                    <td><input type="text" name="payToBank7DayFee">%</td>
                </tr>
				
				<tr >
					<td width="20%" align="left">&nbsp;大额拆分</td>
					 
					<td width="55%" align="left"> 
						<input type="radio" name="largeAmountSplit" id="largeAmountSplit_0" value="YES"  />
						<label for="largeAmountSplit_0">是</label>
						<input type="radio" name="largeAmountSplit" id="largeAmountSplit_1" value="NO"/>
						<label for="largeAmountSplit_1">否</label>
						 
						 
					</td>
					 
				</tr> 
				<tr >
					<td width="20%" align="left">&nbsp;拆分计费</td>
					 
					<td width="55%" align="left"> 
						<input type="radio" name="splitBilling" id="splitBilling_0" value="YES"  />
						<label for="splitBilling_0">是</label>
						<input type="radio" name="splitBilling" id="splitBilling_1" value="NO"/>
						<label for="splitBilling_1">否</label>
						 
						 
					</td>
					 
				</tr> 
            </table>
			
		<span>
		<input type="checkbox" name="remitScenario[]" id="VENDER_REMIT" value="VENDER_REMIT">供应商打款
		<input type="checkbox" name="remitScenario[]" id="DISTRIBUTER_REMIT" value="DISTRIBUTER_REMIT">分销商打款
		<input type="checkbox" name="remitScenario[]" id="AGENT_REMIT" value="AGENT_REMIT">代理商打款
		<input type="checkbox" name="remitScenario[]" id="ECOMMERCE_PAYMENT" value="ECOMMERCE_PAYMENT">电商赔付
		<input type="checkbox" name="remitScenario[]" id="INSURANCE_CLAIM" value="INSURANCE_CLAIM">保险理赔
		<input type="checkbox" name="remitScenario[]" id="AIR_REFUND" value="AIR_REFUND">航空退票
		<input type="checkbox" name="remitScenario[]" id="AUTHENTICATION" value="AUTHENTICATION">认证
		<input type="checkbox" name="remitScenario[]" id="PAYROLL_CREDIT" value="PAYROLL_CREDIT">代发工资、报销、奖金<br>
		<input type="checkbox" name="remitScenario[]" id="SPECAIL_EXPENSE" value="SPECAIL_EXPENSE">劳务费、基于合作协议的营销推广费用		 
		<input type="checkbox" name="remitScenario[]" id="OTHERS" value="OTHERS" onclick="control(this)">其他
		</span>
		</span>
        </div>
    </table>
		<div id="REMITSETTLEMENT_PAY" class="close">
            <span style="background: #6BBE18;">D+1结算</span>
            <table>
                <tr>

                    <th>出款产品</th>
                    <th>交易手续费</th>
                </tr>

                <tr>
                    <td>D+1结算</td>
                    <td><input type="text" name="nextSettleFee">元/笔</td>
                </tr>
            </table>
        </div>
	
	
    <div style="border: none;"><span><input type="submit" value="点击生成" onclick="return check()"></span></div>


</form>
</body>
</html>