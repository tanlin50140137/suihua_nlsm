<?php
class Log{
    function log_result($file,$word)
    {
        $fp=fopen($file,"a");
        flock($fp,LOCK_EX);
        fwrite($fp,"支付回调信息：ִ".date("Y-m-d H:i:s",time())."\n".$word."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }}