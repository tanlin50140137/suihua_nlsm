<?php
error_reporting(0);
 
 /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    function JSON($array) {
        arrayRecursive($array, 'urlencode', true);
		
        $json = json_encode($array);
        return urldecode($json);
    }
	/**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
	
	
		
	$data=array(
    adslType=>implode(",", $_REQUEST['adslTypeMap']),
    payProduct=>implode(",", $_REQUEST['payProductMap']),
    accountProduct=>implode(",", $_REQUEST['accountProductMap']),
    remiteProduct=>implode(",", $_REQUEST['remiteProductMap']),
    webUrl=>$_REQUEST['webUrl'],
    webname=>$_REQUEST['webname'],
    webpasswd=>$_REQUEST['webpasswd'],
	webUrl_h5=>$_REQUEST['webUrl_h5'],
    webname_h5=>$_REQUEST['webname_h5'],
    webpasswd_h5=>$_REQUEST['webpasswd_h5'],
    appName=>$_REQUEST['appName'],
    appDownUrl=>$_REQUEST['appDownUrl'],
    cyBankB2BFee=>$_REQUEST['cyBankB2BFee'],
    cyBankB2CFee=>$_REQUEST['cyBankB2CFee'],
    onePayDebitFee=>$_REQUEST['onePayDebitFee'],
    onePayCreditFee=>$_REQUEST['onePayCreditFee'],
    bindCardDebitFee=>$_REQUEST['bindCardDebitFee'],
    bindCardCreditFee=>$_REQUEST['bindCardCreditFee'],
    isSmall=>$_REQUEST['isSmall'],
    memberScanAliFee=>$_REQUEST['memberScanAliFee'],
    memberScanWXFee=>$_REQUEST['memberScanWXFee'],
    merchantScanAliFee=>$_REQUEST['merchantScanAliFee'],
    merchantScanWXFee=>$_REQUEST['merchantScanWXFee'],
    walletPayAliFee=>$_REQUEST['walletPayAliFee'],
    walletPayAli1Fee=>$_REQUEST['walletPayAli1Fee'],
    walletPayAli2Fee=>$_REQUEST['walletPayAli2Fee'],
    walletPayWXFee=>$_REQUEST['walletPayWXFee'],
    walletPayWX1Fee=>$_REQUEST['walletPayWX1Fee'],
    walletPayWXAPPFee=>$_REQUEST['walletPayWXAPPFee'],
    merchantWChat=>$_REQUEST['merchantWChat'],
    merchantAppidWChat=>$_REQUEST['merchantAppidWChat'],
    recomAppidWChat=>$_REQUEST['recomAppidWChat'],
    firstPayAuthList=>$_REQUEST['firstPayAuthList'],
    secondPayAuthList=>$_REQUEST['secondPayAuthList'],
    thirdPayAuthList=>$_REQUEST['thirdPayAuthList'],
    fourPayAuthList=>$_REQUEST['fourPayAuthList'],
    fivePayAuthList=>$_REQUEST['fivePayAuthList'],
    wChatPayFee=>$_REQUEST['wChatPayFee'],
    Alichat=>$_REQUEST['Alichat'],
    alichatFee=>$_REQUEST['alichatFee'],
    merchantAccountPayFee=>$_REQUEST['merchantAccountPayFee'],
    customerauthtype=>$_REQUEST['customerauthtype'],
    authFee=>$_REQUEST['authFee'],
    bindCardFee=>$_REQUEST['bindCardFee'],
    memberRechargeFee=>$_REQUEST['memberRechargeFee'],
    accountTransFee=>$_REQUEST['accountTransFee'],
    accountPayFee=>$_REQUEST['accountPayFee'],
    withdrawFee=>$_REQUEST['withdrawFee'],
    subAccountAuthFee=>$_REQUEST['subAccountAuthFee'],
    splitOrderFee=>$_REQUEST['splitOrderFee'],
    subAccountSettleFee=>$_REQUEST['subAccountSettleFee'],
    rechargeFee=>$_REQUEST['rechargeFee'],
    payToAccountFee=>$_REQUEST['payToAccountFee'],
    enterprisePayFee=>$_REQUEST['enterprisePayFee'],
    payToBankNormalFee=>$_REQUEST['payToBankNormalFee'],
    payToBankActualFee=>$_REQUEST['payToBankActualFee'],
    payToBank7DayFee=>$_REQUEST['payToBank7DayFee'],
    largeAmountSplit=>$_REQUEST['largeAmountSplit'],
    splitBilling=>$_REQUEST['splitBilling'],
    remitScenario=> implode(",", $_REQUEST['remitScenario']),
    remitScenarioAdd=>$_REQUEST['remitScenarioAdd'],
    nextSettleFee=>$_REQUEST['nextSettleFee'],
     
	);
	//remitScenario 中包含其它时必填
	if(in_array("OTHERS",$_REQUEST['remitScenario']))
	$data['remitScenarioAdd']="remitScenarioAdd";

   
	//去空
    foreach( $data as $k=>$v){  
    if( !$v )  
        unset( $data[$k] );  
    }
	//排序
	ksort($data); 
	//转JSON
	$productInfo=JSON($data);
 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 开通产品JSON </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    开通产品JSON
</th>
		  	</tr>
	        <tr >
				<td width="15%" align="left">&nbsp;productInfo</td>
				<td width="5%"  align="center"> : </td>
				<td width="80"  align="left"> <textarea cols="80" rows="10"><?php echo $productInfo;?> </textarea></td>
               
            </tr>

       
      </table>
    </body>
</html>
