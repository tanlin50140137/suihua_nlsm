<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

/**
 * 生成唯一不重复随机数
 * @return string
 */
function randChar(){
    $string = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT). str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT). str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    return $string;
}
/**
 * 密码加密
 * @param string    $password 密码
 * @return string
 */
function password($password) {
  $string = base64_encode(sha1(md5($password)));
  $string = substr($string, 0, 6);
  $password = md5(hash("sha256", $password . $string));
  return $password;
}
/**
 * 替换内容图片url加http://
 * @param string $content 内容信息
 * @return  String
 */
function final_imgurl($content) {
    //提取图片路径的src的正则表达式  
    preg_match_all("/<img(.*)src=\"([^\"]+)\"[^>]+>/isU",$content,$matches);  
    $img = "";  
    if(!empty($matches)) {  
        //注意，上面的正则表达式说明src的值是放在数组的第三个中  
        $img = $matches[2];  
    }else {  
        $img = "";  
    }  
    if (!empty($img)) {  
        $img_url = "http://".$_SERVER['SERVER_NAME'];  
        $patterns= array();  
        $replacements = array();  
        foreach($img as $imgItem){  
            if (preg_match('/(http:\/\/)|(https:\/\/)/i', $imgItem)) {
                $final_imgUrl = $imgItem;  
            }else{
                $final_imgUrl = $img_url.$imgItem;  
            }
            $replacements[] = $final_imgUrl;  
            $img_new = "/".preg_replace("/\//i","\/",$imgItem)."/";  
            $patterns[] = $img_new;  
        }  
        //让数组按照key来排序  
        ksort($patterns);  
        ksort($replacements);  
        //替换内容  
        $content = preg_replace($patterns, $replacements, $content);  
    }
    return $content;
}
/**
 * 个性化时间函数
 * @param string $time 时间戳
 * @return  string
 */
function time_tran($time) { 
    $newtime = time() - $time;
    if ($newtime < 60) { 
        $str = '刚刚'; 
    }elseif ($newtime < 60 * 60) { 
        $min = floor($newtime/60); 
        $str = $min.'分钟前'; 
    }elseif ($newtime < 60 * 60 * 24) { 
        $h = floor($newtime/(60*60)); 
        $str = $h.'小时前'; 
    }else{
        $newtime = strtotime(date('Y-m-d', time())) - strtotime(date('Y-m-d', $time));
        if ($newtime < 60 * 60 * 24 * 3) { 
            $d = floor($newtime/(60*60*24));
            $str = $d == 1 ? '昨天' : '前天';
        }elseif ($newtime < 60 * 60 * 24 * 30) { 
            $str = floor($newtime / (60*60*24)) . '天前';  
        }elseif ($newtime < 60 * 60 * 24 * 30 * 12) { 
            $str = floor($newtime / (60*60*24*30)) . '月前';  
        }else{
            $str = '一年前';
        }
    }
    return $str; 
} 
//清空文件夹函数和清空文件夹后删除空文件夹函数的处理
/**
 * 清空文件夹和清空文件处理函数
 * @param string    $path 文件夹文件路径
 * @return array       
 */
function deldir($path){
    //如果是目录则继续
    if(is_dir($path)){
        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $list = scandir($path);
        foreach($list as $val){
            //排除目录中的.和..
            if($val != "." && $val != ".."){
                //如果是目录则递归子目录，继续操作
                if(is_dir($path.$val)){
                    //子目录中操作删除文件夹和文件
                    deldir($path.$val.'/');
                    //目录清空后删除空文件夹
                    @rmdir($path.$val.'/');
                }else{
                    //如果是文件直接删除
                    unlink($path.$val);
                }
            }
        }
    }
}
/**
 * curl获取请求文本内容
 * @param string    $url URL请求地址
 * @param string    $method 请求方式  POST  GET
 * @param string    $data 请求数据
 * @return array       
 */
function https_request($url, $method ='GET', $data = array()) {
    if ($method == 'POST') {
        //使用crul模拟
        $ch = curl_init();
        //禁用https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //允许请求以文件流的形式返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch); //执行发送
        curl_close($ch);
    }else {
        if (ini_get('allow_fopen_url') == '1') {
            $result = file_get_contents($url);
        }else {
            //使用crul模拟
            $ch = curl_init();
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //禁用https
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        }
    }
    return $result;
}