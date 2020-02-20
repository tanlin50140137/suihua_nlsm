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
    pMerchantNo=>$_REQUEST['pMerchantNo'],
    merchantType=>$_REQUEST['merchantType'],
    merchantName=>$_REQUEST['merchantName'],
    merchantSName=>$_REQUEST['merchantSName'],
    certificateType=>$_REQUEST['certificateType'],
    certificateNo=>$_REQUEST['certificateNo'],
    customerName=>$_REQUEST['customerName'],
    idCardNo=>$_REQUEST['idCardNo'],
    fCategory=>$_REQUEST['fCategory'],
    sCategory=>$_REQUEST['sCategory'],
    merchantScope=>$_REQUEST['merchantScope'],
    province=>$_REQUEST['province'],
    city=>$_REQUEST['city'],
    area=>$_REQUEST['area'],
    address=>$_REQUEST['address'],
    linkMan=>$_REQUEST['linkMan'],
    linkMobile=>$_REQUEST['linkMobile'],
    linkEmail=>$_REQUEST['linkEmail'],
    taxCertificateNo=>$_REQUEST['taxCertificateNo'],
    openCertificateNo=>$_REQUEST['openCertificateNo'],
    organCertificateNo=>$_REQUEST['organCertificateNo'],
    organType=>$_REQUEST['organType'],
    organExpirEndDate=>$_REQUEST['organExpirEndDate'],
 
	);
	
	
	
$baseInfo=JSON($data);
 
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> 基本信息JSON </title>
</head>
	<body>
		<br /> <br />
		<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
			<tr>
		  		<th align="center" height="30" colspan="5" bgcolor="#6BBE18">
    基本信息JSON
</th>
		  	</tr>
	        <tr >
				<td width="15%" align="left">&nbsp;baseInfo</td>
				<td width="5%"  align="center"> : </td>
				<td width="80"  align="left"> <textarea cols="80" rows="10"><?php echo $baseInfo;?> </textarea></td>
               
            </tr>

       
      </table>
    </body>
</html>
