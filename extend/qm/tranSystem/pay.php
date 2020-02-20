<?php
 
date_default_timezone_set('Asia/Shanghai');
$requestNo = "QM" . date("ymd_His") . rand(10, 99);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">


	<title>1.7支付下单</title>
    <style type="text/css">
        tr.saleClose {
            display: none;
        }

        tr.wechatappClose {
            display: none;
        }

        tr.bindidClose {
            display: none;
        }

        tr.wechatofficalClose {
            display: none;
        }

        tr.wechatMicropayClose {
            display: none;
        }

tr.wechatScanClose{
    display: none;
}

    </style>
    <script type="text/javascript">
//网银
        function openSale() {
            document.getElementById('sale01').className='';
        }
        function closeSale() {
            document.getElementById('sale01').className ='saleClose';
        }
		//绑卡
        function openBind() {
            document.getElementById('bind01').className='';
        }
        function closeBind() {
            document.getElementById('bind01').className='bindidClose';
        }
		//微 信 H5 支付
        function openWechatApp() {
            document.getElementById('wechatapp01').className='';
            document.getElementById('wechatapp02').className='';
            document.getElementById('wechatapp03').className='';
        }
        function closeWechatApp() {
            document.getElementById('wechatapp01').className='wechatappClose';
            document.getElementById('wechatapp02').className='wechatappClose';
            document.getElementById('wechatapp03').className='wechatappClose';
        }
		//微信公众号支付
        function openWechatOffical() {
            document.getElementById('wechatoffical01').className='';
        }
		 
        function closeWechatOffical() {
            document.getElementById('wechatoffical01').className='wechatofficalClose';
        }
		// 被扫支付
        function openWechatmicro() {
            document.getElementById('wechatmicro01').className='';
            document.getElementById('wechatmicro02').className='';
            document.getElementById('wechatmicro03').className='';
        }
        function closeWechatmicro() {
            document.getElementById('wechatmicro01').className='wechatMicropayClose';
            document.getElementById('wechatmicro02').className='wechatMicropayClose';
            document.getElementById('wechatmicro03').className='wechatMicropayClose';
        }
		/*微信用户扫码支付
        function openwechatscan(){
            document.getElementById('userIp').className='';
        }
        function closewechatscan(){
        document.getElementById('userIp').className='wechatScanClose';
        }*/
    </script>
	
<html>
<head>
	  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付接口</title>	
</head>
	<body>
		<table width="80%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
					请输入订单请求参数	
				</th>
		  	</tr> 

			<form method="post" action="sendPay.php" target="_blank"  ">
				<tr >
					<td width="20%" align="left">&nbsp;支付请求号</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="requestNo" value="<?php echo $requestNo?>"/>
						<span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">requestNo</td> 
				</tr>
		<tr >
					<td width="20%" align="left">&nbsp;商户用户标识</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="merchantUserId" value=""/>						
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">merchantUserId</td> 
				</tr>
				<tr >
					<td width="20%" align="left">&nbsp;订单金额</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="orderAmount" value="0.01" />
						<span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">orderAmount</td> 
				</tr>
	<tr >
					<td width="20%" align="left">&nbsp;需支付金额</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="fundAmount" value="0.01" />
						<span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">fundAmount</td> 
				</tr>
				 

			 

				<tr >
					<td width="20%" align="left">&nbsp;支付下单时间</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="merchantOrderDate" value="<?php echo date("Y-m-d H:i:s", time());?>" /><span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">merchantOrderDate</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;订单有效期</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="merchantExpireTime"    value=""  />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">merchantExpireTime</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;指定银行编码</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="bankCode" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">bankCode</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;风控参数</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="trxExtraInfo" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">trxExtraInfo</td> 
				</tr>
				<tr >
					<td width="20%" align="left">&nbsp;后台服务通知地址</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="serverCallbackUrl" value="http://10.151.31.134/test/qm/callback/callback.php" />
						<span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">serverCallbackUrl</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;前端页面通知地址</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="webCallbackUrl" value="http://10.151.31.134/test/qm/callback/fcallback.php" />	
						<span style="color:#FF0000;font-weight:100;">*</span>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">webCallbackUrl</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;行业类别码</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="mcc" value="5969" />
						<span style="color:#FF0000;font-weight:100;">*</span></td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">mcc</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;产品类别码</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="productCatalog"  value="5969" />
						<span style="color:#FF0000;font-weight:100;">*</span></td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">productCatalog</td> 
				</tr>

				<tr >
					<td width="20%" align="left">&nbsp;商品名称</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="productName" value="测试" />
					<span style="color:#FF0000;font-weight:100;">*</span></td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">productName</td> 
				</tr>


				<tr >
					<td width="20%" align="left">&nbsp;商品描述</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="productDesc" value="test" />
					<span style="color:#FF0000;font-weight:100;">*</span></td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">productDesc</td> 
				</tr>

				
				 <tr>
                      <td width="20%" align="left">&nbsp;模板类型</td>
                     <td width="5%"  align="center"> : &nbsp;</td> 
					 <td width="55%" align="left"> 
                             <select name="templateType" required>
                             <option value="WAP">WAP-显示H5页面title</option>
                             <option value="APP">APP-不显示H5页面title</option>
                             
                </select>
            </td>  
               <td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">templateType</td> 			</tr>
		   	 
				<tr >
					<td width="20%" align="left">&nbsp;卡券列表</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="couponNos" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">couponNos</td> 
				</tr>
				<tr >
					<td width="20%" align="left">&nbsp;营销补充信息</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="marketingExtraInfo" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">marketingExtraInfo</td> 
				</tr>
				<tr >
					<td width="20%" align="left">&nbsp;商户业务类型</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="merchantBizType" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">merchantBizType</td> 
				</tr>
				<tr >
					<td width="20%" align="left">&nbsp;分账规则类型</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input type="radio" name="divideRuleType" id="divideRuleType_0" value="NONE" checked />
						<label for="divideRuleType_0">NONE</label>
						<input type="radio" name="divideRuleType" id="divideRuleType_1" value="DETAIL"/>
						<label for="divideRuleType_1">DETAIL</label>
							<input type="radio" name="divideRuleType" id="divideRuleType_0" value="SUM"  />
						<label for="divideRuleType_0">SUM</label>
						<input type="radio" name="divideRuleType" id="divideRuleType_1" value="CONSTANT"/>
						<label for="divideRuleType_1">CONSTANT</label>
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">divideRuleType</td> 
				</tr> 

				<tr >
					<td width="20%" align="left">&nbsp;分账详情</td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="divideDetail" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">divideDetail</td> 
				</tr><tr >
					<td width="20%" align="left">&nbsp;付款方商编 </td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="accountPayMerchantNo" value="" />
					</td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">accountPayMerchantNo</td> 
				</tr> 
				<tr >
					<td width="20%" align="left">&nbsp;发起调用的服务器ip </td>
					<td width="5%"  align="center"> : &nbsp;</td> 
					<td width="55%" align="left"> 
						<input size="70" type="text" name="ip" value="127.0.0.1" />
					<span style="color:#FF0000;font-weight:100;">*</span></td>
					<td width="5%"  align="center"> - </td> 
					<td width="15%" align="left">ip</td> 
				</tr> 
				
				  <tr height="50">
            <td width="20%" align="left">指定支付方式</td>
			<td width="5%"  align="left"> : &nbsp;</td> 
            <td width="55%" align="left">
                
               <input type="radio" name="payTool" id="BALANCE" value="BALANCE"
                       onclick= "closeSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="BALANCE">账户余额支付</label>
                <input type="radio" name="payTool" id="BINDCARD" value="BINDCARD"
                onclick="openBind();closeWechatApp();closeSale();closeWechatmicro();closeWechatOffical();"/><label for="BINDCARD">账户绑卡支付</label> 
                <input type="radio" name="payTool" id="YEEPAYCASHIER" value="YEEPAYCASHIER"
                       onclick= "closeSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="YEEPAYCASHIER">易宝一键支付</label>
                <input type="radio" name="payTool" id="SALESB2C" value="SALESB2C"
                       onclick= "openSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="SALESB2C">网银个人支付</label>
				<input type="radio" name="payTool" id="SALESB2B" value="SALESB2B"
                       onclick= "openSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical(); "/><label for="SALESB2B">网银企业支付</label>
                
                 
                <input type="radio" name="payTool" id="WECHATSCAN" value="WECHATSCAN"
                       onclick="closeSale();closeBind();closeWechatmicro();closeWechatOffical();closeWechatApp()"/><label for="WECHATSCAN">微信用户扫码支付</label>
                <input type="radio" name="payTool" id="WECHATAPP" value="WECHATAPP"
                       onclick="openWechatApp();closeSale();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="WECHATAPP">微信 H5 支付</label>
                <input type="radio" name="payTool" id="WECHATOFFICIAL" value="WECHATOFFICIAL"
                       onclick="openWechatOffical();closeWechatApp();closeBind();closeWechatmicro();closeSale();"/><label for="WECHATOFFICIAL">微信公众号支付</label>
                <input type="radio" name="payTool" id="WECHAT_MICROPAY" value="WECHAT_MICROPAY"
                       onclick="openWechatmicro();closeBind();closeSale();closeWechatOffical();closeWechatApp();"/><label for="WECHAT_MICROPAY">微信被扫支付</label>
                <input type="radio" name="payTool" id="WECHATSDK" value="WECHATSDK"
                       onclick="openWechatApp();closeSale();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="WECHATSDK">微信 APP 支付</label>
                 
                <input type="radio" name="payTool" id="ALIPAYSCAN" value="ALIPAYSCAN"  
				onclick="closeSale();closeBind();closeWechatmicro();closeWechatOffical();closeWechatApp()" /><label for="ALIPAYSCAN">支付宝用户扫码支付</label>
               
			   <input type="radio" name="payTool" id="ALIPAYAPP" value="ALIPAYAPP" 
				onclick="closeWechatApp();closeSale();closeBind();closeWechatmicro();closeWechatOffical();" /><label for="ALIPAYAPP">支付宝 H5 支付</label>
                
				<input type="radio" name="payTool" id="ALIPAYSDK" value="ALIPAYSDK" 
				onclick="closeWechatApp();closeSale();closeBind();closeWechatmicro();closeWechatOffical();" /><label for="ALIPAYSDK">支付宝 APP 支付</label>
                
				<input type="radio" name="payTool" id="ALIPAY_MICROPAY" value="ALIPAY_MICROPAY" 
				onclick="openWechatmicro();closeBind();closeSale();closeWechatOffical();closeWechatApp();"/><label for="ALIPAY_MICROPAY">支付宝被扫支付</label>
               
			   <input type="radio" name="payTool" id="ACCOUNT_PAY" value="ACCOUNT_PAY"     
			   onclick= "closeSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="ACCOUNT_PAY" >企业账户支付</label>
                 
				 <input type="radio" name="payTool" id="ACCOUNT_PAY_API" value="ACCOUNT_PAY_API"    
				 onclick= "closeSale();closeWechatApp();closeBind();closeWechatmicro();closeWechatOffical();"/><label for="ACCOUNT_PAY_API">企业账户支付API</label>
            </td>	<td width="5%"  align="center"> - </td> 
            <td width="15%">payTool</td>
        </tr>
        
        <tr  class="saleClose" id="sale01" height="50">
            <td width="20%" align="left">指定银行编码</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" name="bankCode" id="bankCode"  ></td>
            <td width="5%"  align="center"> - </td> 
		    <td width="15%">bankCode</td>
        </tr>
       
        <tr class="bindidClose" id="bind01" height="50">
            <td width="20%" align="left">绑卡ID</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" name="bindCardId"  id="bindCardId" value="">&nbsp;<span style="color:#FF0000;font-weight:100;">*</span>  </td>
             <td width="5%"  align="center"> - </td> 
			 <td width="15%">bindCardId</td>
        </tr>
    
      
        <tr  class="wechatofficalClose" id="wechatoffical01"height="50">
            <td width="15%" align="left">微信openid</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="openId"name="openId" ></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">openId<span style="color:red ">*</span></td>
        </tr>
       
        <tr  class="wechatMicropayClose" id="wechatmicro01"height="50">
            <td width="15%" align="left">授权码</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="payEmpowerNo" name="payEmpowerNo" ></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">payEmpowerNo</td>
        </tr>
        <tr  class="wechatMicropayClose"id="wechatmicro02" height="50">
            <td width="15%" align="left">设备号</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="merchantTerminalId" name="merchantTerminalId" ></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">merchantTerminalId</td>
        </tr>
        <tr  class="wechatMicropayClose" id="wechatmicro03"height="50">
            <td width="15%" align="left">门店编码</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="merchantStoreNo" name="merchantStoreNo" ></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">merchantStoreNo</td>
        </tr>
       
        <tr class="wechatappClose" id="wechatapp01"height="50">
            <td width="15%" align="left">系统</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="platForm" name="platForm" placeholder="系统(iOS/android)"></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">platForm</td>
        </tr>
        <tr class="wechatappClose" id="wechatapp02" height="50">
            <td width="15%" align="left">应用名称</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="appName" name="appName" placeholder="在应用中心的应用名称"></td>
             <td width="5%"  align="center"> - </td>   <td width="20%">appName</td>
        </tr>
        <tr class="wechatappClose" id="wechatapp03"height="50">
            <td width="15%" align="left">应用ID</td>
			<td width="5%"  align="center"> : &nbsp;</td> 
            <td width="55%" align="left"><input type="text" size="70" id="appStatement" name="appStatement" placeholder="应用中心的ID"></td>
              <td width="5%"  align="center"> - </td>  <td width="20%">appStatement</td>
        </tr>
                               

				<tr >
					<td width="20%" align="left">&nbsp;</td>
					<td width="5%"  align="center">&nbsp;</td> 
					<td width="55%" align="left"> 
						<input type="submit" value="提交订单" />
					</td>
					<td width="5%"  align="center">&nbsp;</td> 
					<td width="15%" align="left">&nbsp;</td> 
				</tr>

			</form>
		</table>
</body>
</html>
