<?php
namespace app\index\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Weixin 
{
	// 跳转微信授权页面
    public function index() {
        // 验证用户是否登录
        if(session('?user')){
            // 进来表示已登录
            header('Location:'.url('User/index'));exit;
        }
        //获取微信配置信息
        $weixin = get_payment('wxpay');
    	//微信授权url
    	$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$weixin['appid'].'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].url('Weixin/login').'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
    	header('location:'.$url);
    } 

    // 微信用户登陆
    public function login(){
    	// 验证用户是否登录
        if(session('?user')){
            // 进来表示已登录
            header('Location:'.url('User/index'));exit;
        }
    	//前端传来的code值
    	$code = request()->param('code');
    	if(!$code){header('Location:'.url('Weixin/index'));exit;}
		//获取微信配置信息
        $weixin = get_payment('wxpay');
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$weixin['appid'].'&secret='.$weixin['appsecret'].'&code='.$code.'&grant_type=authorization_code';
		$result = https_request($url);
		//将json数据转为array数组格式
        $result = json_decode($result, true);
        if(!isset($result['openid'])){header('Location:'.url('Weixin/index'));exit;}
        //获取微信用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$result["access_token"]."&openid=".$result["openid"]."&lang=zh_CN";
        $user = https_request($url);
        //将json数据转为array数组格式
        $user = json_decode($user, true);
        $user['unionid'] = isset($user['unionid']) ? $user['unionid'] : '';
        //判断是否关注公众号
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".access_token()."&openid=".$user['openid']."&lang=zh_CN";
        $result = https_request($url);
        //将json数据转为array数组格式
        $result = json_decode($result, true);
        //检查是否已经存在
        $list = \think\Db::name('member')->field(true)->where(array('openid'=>$user['openid']))->find();
		//如果已经存在
		if ($list) {
            //判断用户是否禁用
	        if($list['is_state'] != 1){
	            // 登录失败
                return view('Index/error',array('remark'=>'登录失败，账号已禁用！'));exit;
	        }
	        $appkey = password(md5($user['openid'].time()));
            $data = array('unionid'=>$user['unionid'],'subscribe'=>$result['subscribe'],'appkey'=>$appkey);
            \think\Db::name('member')->where(array('id'=>$list['id']))->update($data);
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$list['id']);
            session('user',array_merge($list,$data));
		}else{
            //添加微信用户账号
            $data = array();
            //用户openid                             
            $data['openid'] = $user['openid']; 
            //用户昵称  
            $data['nickname'] = $user['nickname']; 
            //用户头像
            $data['userface'] = $user['headimgurl']; 
            //用户性别
            $data['sex'] = $user['sex']; 
            //用户密码加密                                 
            $data['username'] = md5($user['openid']);   
            $data['password'] = md5($user['openid']);   
            //用户创建时间                 
            $data['create_time'] = time();  
            $data['unionid'] = $user['unionid'];
            $data['subscribe'] = $result['subscribe'];
            //用户注册IP                                           
            $data['registe_ip'] = request()->ip();
            $data['pid'] = session('pid') ? session('pid') : 0;
            $id = \think\Db::name('member')->insertGetId($data);
            if($data['pid']){
                //查找上级会员
                $idlist = get_parent_member($id);
                //如果有找到上级，则记录等级关系
                if (!empty($idlist)) {
                    $data = array();
                    foreach ($idlist as $key => $value) {
                        $data[] = array('id'=>$id,'pid'=>$value,'level'=>($key + 1));
                    }
                    \think\Db::name('member_relation')->insertAll($data);
                }
            }
            $appkey = password(md5($user['openid'].time()));
            \think\Db::name('member')->where(array('id'=>$id))->setField('appkey', $appkey);
            $list = get_member($id);
            session('user',$list);
		}
        if(session('?user')){
            // 进来表示已登录
            $url = session('Callback') ? session('Callback') : url('User/index');
            session('Callback',null);
            header('Location:'.$url);exit;
        }else{
        	// 登录失败
            return view('Index/error',array('remark'=>'登录失败，账号有异常！'));exit;
        }
    }

    // 微信转发
    public function js_sdk() {
        //获取微信配置信息
        $weixin = get_payment('wxpay');
        //获取当前url
        $location = request()->param('location');
        $wx = array();
        $wx['timestamp'] = time();
        $wx['noncestr'] = md5(time());
        $wx['jsapi_ticket'] = jsapi_ticket();
        $wx['url'] = $location;
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wx['jsapi_ticket'], $wx['noncestr'], $wx['timestamp'], $wx['url']);
        $wx['signature'] = sha1($string);
        return json($wx);
    }
}