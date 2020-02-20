<?php


#读取内容
//获取响应参数
/*$response = $_REQUEST['response'];
$customerIdentification=$_REQUEST['customerIdentification'];
$log = new log();
$log_name = "./callback.log"; //log文件路径
$log->log_result($log_name, "【response】:\n" . $response . "\n"."【customerIdentification】".$customerIdentification."\n");
//JSON转数组
$response=json_decode($response,true);

$source=$response['encryption'];
$sign=$response['signature'];

//解密验签
vertify($source,$sign) ;

function   vertify($source,$sign)
{
    $secretKey = "KkniHbkPolMYQ3/EODtMgQ==";
    $request = new YopRequest("BM12345678903732", $secretKey);
    $request->setEncrypt(true);
    $request->setSignRet(true);
    $request->setSignAlg("sha1");
    //解密
    //var_dump($request);
    echo "<br><br>";
    echo $source;
    $result= YopClient::decrypt($request, $source);
    //判断是否解析出明文
    echo "解密明文：";
    var_dump($result);
    //验签
    $validSign = YopSignUtils::isValidResult($result, $secretKey, "sha1",$sign);
     echo "<br>"."验验结果：".$validSign;
    return   $validSign ;
}8
*/

require_once("../lib/YopClient.php");
require_once("../lib/YopClient3.php");
require_once("../lib/Util/YopSignUtils.php");
require_once("../conf/conf.php");
function callback($source){
  global $private_key;
  global  $public_key;
  return YopSignUtils::decrypt($source,$private_key, $public_key);
}
$data = $_REQUEST["response"];
callback($data);
echo 'SUCCESS';
?>