<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
 
require_once ("../lib/YopClient.php");
require_once ("../lib/YopClient3.php");
require_once ("../lib/Util/YopSignUtils.php");
require_once("../conf/conf.php");
 
/**************************************************************
     *  php5.4以下
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
    cardNo=>$_REQUEST['cardNo'],
    bankAccountType=>$_REQUEST['bankAccountType'],
    remitType=>$_REQUEST['remitType'],
    //摘要
    leaveWord=>$_REQUEST['leaveWord'],
    //非大银行市省支行信息必传
    //开户所以省
    province=>$_REQUEST['province'],
    //开户市
    city=>$_REQUEST['city'],
    //银行编码 总行名称两传一即可
    bankCode=>$_REQUEST['bankCode'],
    //总行名称
    bankName=>$_REQUEST['bankName'],
    //支行信息
    branchBankName=>$_REQUEST['branchBankName'],
    //付款金额
    value=>$_REQUEST['value'],
    //收款人姓名
    userName=>$_REQUEST['userName'],
    //收款人手机号
    payeeMobile=>$_REQUEST['payeeMobile']);

$remitInfos=JSON($data);

//企业付款
function remit($remitInfos){

    global  $private_key;
    global $yop_public_key;
    global $merchantNo;
   
    $request = new YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);



    $request->addParam("requestNo",$_REQUEST['requestNo']); 
    $request->addParam("serverCallbackUrl", $_REQUEST['serverCallbackUrl']);   
    $request->addParam("remitInfos", $remitInfos);
    $request->addParam("urgency", $_REQUEST['urgency']);

    $response = YopClient3::post("/rest/v1.0/payplus/remit/remit", $request);
	//var_dump(  $response);
    $data=(array)$response;
    $result=$data['result'];
    if($response->validSign==1){
        echo "返回结果签名验证成功!\n";
    }
    return $result;
}
$result=remit($remitInfos);
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title> 企业付款 </title>
</head>
<body>
<br /> <br />
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0" style="border:solid 1px #107929">
    <tr>
        <th align="center" height="30" colspan="5" bgcolor="#6BBE18">
            企业付款--返回参数
        </th>
    </tr>
    <tr >
        <td width="25%" align="left">&nbsp;请求返回码</td>
        <td width="5%"  align="center"> : </td>
        <td width="45"  align="left"> <?php echo $result['code'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">code</td>
    </tr>

    <tr>
        <td width="25%" align="left">&nbsp;请求返回信息</td>
        <td width="5%"  align="center"> : </td>
        <td width="35%" align="left"> <?php echo  $result['message'];?> </td>
        <td width="5%"  align="center"> - </td>
        <td width="30%" align="left">message</td>
    </tr>

</table>
</body>
</html>

