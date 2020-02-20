<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

// 微信接口函数文件
/**
* @function 签名
* @param array $data 签名数据
	例如：
 	appid：    wxd111665abv58f4f
	mch_id：    10000100
	device_info：  1000
	Body：    test
	nonce_str：  ibuaiVcKdpRxkhJA
	第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	第二步：拼接支付密钥：
	stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	9CF3B7"
*/
function get_sign($param,$key) {
	ksort ( $param );
	$buff = "";
	ksort ( $param );
	foreach ( $param as $k => $v ) {
		if (null != $v && "null" != $v && "sign" != $k) {
			$buff .= $k . "=" . $v . "&";
		}
	}
	$content = "";
	if (strlen ( $buff ) > 0) {
		$content = substr ( $buff, 0, strlen ( $buff ) - 1 );
	}
	$signStr = $content . "&key=" . $key;
	return strtoupper ( md5 ( $signStr ) );
}

function create_hongbao_xml($arr) {
	$xml = "<xml>";
	foreach ( $arr as $key => $val ) {
		if (is_numeric ( $val )) {
			$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
		} else {
			$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
		}
	}
	$xml .= "</xml>";
	return $xml;
}

//提交请求
function curl_post_ssl($url, $vars, $second = 30, $aHeader = array()) {
	$ch = curl_init ();
	//超时时间
	curl_setopt ( $ch, CURLOPT_TIMEOUT, $second );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	//这里设置代理，如果有的话
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
	
	//cert 与 key 分别属于两个.pem文件
	// curl_setopt ( $ch, CURLOPT_SSLCERT, $_SERVER['DOCUMENT_ROOT'].'/public/cret/sslcert/apiclient_cert.pem' );
	// curl_setopt ( $ch, CURLOPT_SSLKEY, $_SERVER['DOCUMENT_ROOT'].'/public/cret/sslcert/apiclient_key.pem' );
	// curl_setopt ( $ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'].'/public/cret/sslcert/rootca.pem' );
	
	if (count ( $aHeader ) >= 1) {
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $aHeader );
	}
	
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $vars );
	$data = curl_exec ( $ch );
	if ($data) {
		curl_close ( $ch );
		return $data;
	} else {
		$error = curl_errno ( $ch );
		curl_close ( $ch );
		return false;
	}
}
/**
 * 获取微信公众号access_token,有效期7200s
 * @return  string
 */
function access_token() {
	$weixin = get_payment('wxpay');
    //从缓存获取
    $list = \think\Cache::get('access_token');
    if(!$list){
        //没有缓存查询数据，写入缓存
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$weixin['appid'].'&secret='.$weixin['appsecret'];
        $result = https_request($url);
        //将json数据转为array数组格式
        $result = json_decode($result, true);
        $list = $result['access_token'];
        \think\Cache::set('access_token',$list,5000);
    }
    return $list;
}
