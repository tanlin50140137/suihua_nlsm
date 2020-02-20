<?php
require_once("../lib/Util/AESEncrypter.php");
error_reporting(0);
$data=array(
    accountingType=>"UNION",
    checkDate=>$_REQUEST['checkDate'],
    fileType=>"csv");
$request=json_encode($data);
$secretKey = "uuTE1kMjiH6KKGcMcCy5sw==";
$customerIdentification = "BM12345678901247";
$data=array(
    doEncryption=>"true",
    doSignature=>"true",
    signatureAlg=>"SHA1",
    encryptionAlg=>"AES",
    customerIdentification=>$customerIdentification );
    $encryption = json_encode($data);
    $data['signature']=sha1($secretKey.$request.$secretKey);
    $data['encryption']=AESEncrypter::encode($request,$secretKey);
    $json=json_encode($data);
    $url = "https://plus.yeepay.com/pp-portal-app/auth/check/download?hmac=".urlencode($json);
    header("Location: $url");
?>
