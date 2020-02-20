<?php
namespace app\api\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class App 
{
    // 构造函数
    public function __construct() {
        // 指定允许其他域名访问  
        header('Access-Control-Allow-Origin:*');  
        // 响应类型  
        header('Access-Control-Allow-Methods:POST');  
        // 响应头设置  
        header('Access-Control-Allow-Headers:x-requested-with,content-type');  

        //验证数据提交密钥
        $token = input('post.token','');
        $action = '/api/nlsm';
        if($token != md5(date('Y/m/d').$action)){
            $arr = array ('success'=>false,'info'=>urlencode('数据token密钥错误！'));
            // echo urldecode(json_encode($arr));exit;
        }
    }

    // 发送短信验证码
    public function sendCode(){
        //手机号验证
        $mobile = input('post.mobile','');
        //手机号验证
        if(!$mobile){return json(array('success'=>false,'info'=>'请输入手机号！'));exit;}
        if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
            return json(array('success'=>false,'info'=>'手机号格式有误'));exit;
        }
        //判断是否是注册g
        $act = input('post.act','');
        if($act == 'register'){
            if(\think\Db::name('member')->where(array('username'=>$mobile))->count() > 0){
                return json(array('success'=>false,'info'=>'该手机已经注册，请前往登陆！'));exit; 
            }
        }else{
            //验证是否已注册
            if(\think\Db::name('member')->where(array('username'=>$mobile))->count() < 1){
                return json(array('success'=>false,'info'=>'手机不存在，请前往注册！'));exit; 
            }
        }
        //上次发送时间 
        $sendtime = \think\Cache::get('sendtime_'.$mobile);
        //每隔一分钟只能发一次
        if($sendtime > (time()-50)){
            return json(array('success'=>false,'info'=>'验证码发送过于频繁，1分钟后再试！'));exit;
        }
        //生成验证码
        $code = mt_rand(11111,99999);
        /*
            ***聚合数据（JUHE.CN）短信API服务接口PHP请求示例源码
            ***DATE:2015-05-25
        */
        //获取短信通知设置
        $list = get_config('notice');
        header('content-type:text/html;charset=utf-8');
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $smsConf = array(
            'key'   => $list['appkey'], //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => $list['code'], //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>'#code#='.$code //您设置的模板变量，根据实际情况修改
        );
        // 请求发送短信
        $result = $this->juhecurl($sendUrl,$smsConf,1); 
        if($result){
            $result = json_decode($result,true);
            //状态为0，说明短信发送成功
            if($result['error_code'] == 0){
                //记录验证码
                \think\Cache::set('sendtime_'.$mobile,time());
                \think\Cache::set('sendcode_'.$mobile,$code,1800);
                return json(array('success'=>true,'info'=>'验证码发送成功！'));
            }else{
                //状态非0，说明失败
                return json(array('success'=>false,'info'=>"短信发送失败(".$result['error_code'].")：".$result['reason']));
            }
        }else{
            //返回内容异常，以下可根据业务逻辑自行修改
            return json(array('success'=>false,'info'=>"请求发送短信失败！"));
        }
    }

    /*
        ***聚合数据（JUHE.CN）短信API服务接口PHP请求示例源码
        ***发送短信
        ***DATE:2015-05-25
        * 请求接口返回内容
        * @param  string $url [请求的URL地址]
        * @param  string $params [请求的参数]
        * @param  int $ipost [是否采用POST形式]
        * @return  string
    */
    public function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL info: " . curl_info($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }

    // 用户注册
    public function register() {
        // 验证用户是否输入验证码
        $mobile = input('post.mobile','');
        if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
            return json(array('success'=>false,'info'=>'手机号输入有误'));exit;
        } 
        $sendcode = \think\Cache::get('sendcode_'.$mobile);
        if(!$mobile || !$sendcode){
            return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;
        }
        //手机验证码
        $code = input('post.code','');
        if($code != $sendcode){
            return json(array('success'=>false,'info'=>'验证码错误！'));exit;
        }
        //用户密码
        $password = input('post.password','');
        if(strlen($password)<6 || strlen($password)>18){
            return json(array('success'=>false,'info'=>'会员密码请在6-18位数之间！'));exit;
        }
            
        //验证是否已注册
        if(\think\Db::name('member')->where(array('username'=>$mobile))->count() > 0){
            return json(array('success'=>false,'info'=>'该手机已经注册，请前往登陆！'));exit; 
        }
        //推荐人
        $invite = input('post.invite','');
        if($invite){
            $pid = \think\Db::name('member')->where(array('username'=>$invite))->value('id');
            // return json(array('success'=>false,'info'=>'推荐人手机号有误！'));exit; 
        }
        $data = array();
        $data['pid'] = isset($pid) ? $pid : 0;                          //用户推荐人
        $data['username'] = $mobile;                                    //用户手机
        $data['password'] = password(sha1($password));                  //用户密码
        $data['nickname'] = substr_replace($mobile,'****',3,4);         //用户昵称
        $data['userface'] = '/public/image/userface.jpg';               //会员头像
        $data['create_time'] = time();                                  //用户创建时间
        $data['registe_ip'] = request()->ip();                          //用户注册IP

        //获取推荐商家
        $bus_id = \think\Db::name('setmeal_log')->where(array('mobile'=>$mobile))->value('bus_id');
        $data['bus_id'] = $bus_id ? $bus_id : 0;
        if($data['bus_id']){
            $pid =\think\Db::name('business')->where(array('id'=>$data['bus_id']))->value('uid');
            if($pid){
                $data['pid'] = $pid;
            }
        }

        if ($id = \think\Db::name('member')->insertGetId($data)) {
            if($invite){
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
            //清除验证码缓存
            \think\Cache::rm('sendcode_'.$mobile); 
            return json(array('success'=>true,'info'=>'注册成功'));
        }else{
            return json(array('success'=>false,'info'=>'注册信息有误，请重新注册！'));
        }
    } 

    //用户登录
    public function login(){
        //用户账号
        $mobile = input('post.mobile','');
        if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
            return json(array('success'=>false,'info'=>'手机号输入有误'));exit;
        } 
        //验证是否已注册
        if(\think\Db::name('member')->where(array('username'=>$mobile))->count() < 1){
            return json(array('success'=>false,'info'=>'手机不存在，请前往注册！'));exit; 
        }
        //手机验证码登陆
        $code = input('post.code','');
        if($code){
            $sendcode = \think\Cache::get('sendcode_'.$mobile);
            if(!$mobile || !$sendcode){
                return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;
            }
            if($code != $sendcode){
                return json(array('success'=>false,'info'=>'验证码错误！'));exit;
            }
            $list = \think\Db::name('member')->field(true)->where(array('username'=>$mobile,'is_state'=>1))->find();
            //判断用户是否登陆成功
            if($list){
                $appkey = password(md5($mobile.time()));
                $list['appkey'] = $appkey;
                \think\Db::name('member')->where(array('id'=>$list['id']))->setField('appkey', $appkey);
                session('user',$list);
                //登陆成功 把会员数据返回
                if(strpos($list['userface'],'http://') === false){
                    $list['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$list['userface'];
                }
                return json(array('success'=>true,'info'=>'登陆成功！','list'=>$list));
            }else{
                return json(array('success'=>false,'info'=>'账号信息有误！'));
            }
        }
        //用户密码
        $password = input('post.password','');
        $password = password(sha1($password));
        $list = \think\Db::name('member')->field(true)->where(array('username'=>$mobile,'password'=>$password,'is_state'=>1))->find();
        //判断用户是否登陆成功
        if($list){
            $appkey = password(md5($mobile.time()));
            $list['appkey'] = $appkey;
            \think\Db::name('member')->where(array('id'=>$list['id']))->setField('appkey', $appkey);
            session('user',$list);
            //登陆成功 把会员数据返回
            if(strpos($list['userface'],'http://') === false){
                $list['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$list['userface'];
            }
            return json(array('success'=>true,'info'=>'登陆成功！','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'账号信息有误！'));
        }
    }

    //退出登录
    public function logout(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        session('user',null);
        \think\Db::name('member')->where(array('id'=>$id))->setField('appkey', randChar());
        return json(array('success'=>false,'info'=>'退出成功！'));
    }

    //获取用户信息
    public function detail(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $list = get_member($id);
        if ($list) {
            switch ($list['sex']) {
                case '1':
                    $list['sexname'] = '男';
                    break;
                case '2':
                    $list['sexname'] = '女';
                    break;
                default:
                    $list['sexname'] = '保密';
                    break;
            }
            //获取会员等级分组信息
            $level = get_member_level();
            //转换用户对应的等级名称
            foreach ($level as $key => $value) {
                if($list['level_id'] == $value['id']){$list['level_name'] = $value['name'];break;}
            }
            if(strpos($list['userface'],'http://') ===  false){
                $list['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$list['userface'];
            }
            //会员是否已签到
            $list['signin'] = \think\Db::name('member_signin')->where(array('uid'=>$id,'time'=>date('Ymd')))->count();

             //拼接条件
            $where = array();
            $where['pid'] = $id;
            $list['zong'] = \think\Db::name('member')->where($where)->count();


            $idlist = \think\Db::name('member_relation')->where($where)->column('id');
            $idlist = implode(",",$idlist);
            if($idlist){
                $where['id'] = array('IN',$idlist);
                $where['pid'] = array('NEQ',$id);
            }
            $list['sang'] = \think\Db::name('member')->where($where)->count();

            $where['pid'] = $id;

            $list['total'] = \think\Db::name('member')->where($where)->sum('money');

            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d'));
            $where['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');

            $list['number'] = \think\Db::name('member')->where($where)->count();
            $list['month'] = \think\Db::name('member')->where($where)->sum('money');

            $list['code'] = 'http://'.$_SERVER['SERVER_NAME'].$list['code'];

            //是否商家
            $list['bus_id'] = \think\Db::name('business')->where(array('uid'=>$id))->value('id');

            return json(array('success'=>true,'info'=>'','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));
        }
    }

    //统计
    public function price_detail(){
         //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        //获取会员等级分组信息
        // $level = get_member_level();
        //  foreach ($level as $key => $value) {
        //         if($list['level_id'] == $value['id']){$list['level_name'] = $value['name'];break;}
        //     }
        //     if(strpos($list['userface'],'http://') ===  false){
        //         $list['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$list['userface'];
        //     }

        $list = get_member($id);
        if ($list) {
            $idlist = \think\Db::name('member_relation')->where(array('pid'=>$id))->column('id');
            $idlist = implode(",",$idlist);

            //vip
            if($list['level_id'] == 2){
                //统计下级会员
                $list['vip'] = \think\Db::name('member')->where(array('pid'=>$id))->count();
                $list['span'] = "<span>欠".(50 - $list['vip'])."个VIP会员可升级店长！</span>";
            }
            //店长
            if($list['level_id'] == 3){
                $where = array();
                $where['id'] = array('IN',$idlist);
                $where['level_id'] = 2;
                //统计下级会员
                $list['vip'] = \think\Db::name('member')->where($where)->count();
                $where['level_id'] = 3;
                //统计下级会员
                $list['dian'] = \think\Db::name('member')->where($where)->count();
                $list['span'] = "<span>欠".(20 - $list['dian'])."个店长可升级街/镇代理！</span>";
            }
            //街代理
            if($list['level_id'] == 7){
                $where = array();
                $where['id'] = array('IN',$idlist);
                $where['level_id'] = 2;
                //统计下级会员
                $list['vip'] = \think\Db::name('member')->where($where)->count();
                $where['level_id'] = 3;
                //统计下级会员
                $list['dian'] = \think\Db::name('member')->where($where)->count();
                $where['level_id'] = 7;
                //统计下级会员
                $list['jie'] = \think\Db::name('member')->where($where)->count();
                $list['span'] = "<span>欠".(5 - $list['jie'])."个街/镇代理可升级县/区代理！</span>";
            }

            if($list['level_id'] != 3){
                return json(array('success'=>false,'info'=>'不是店长不能查看！','list'=>$list));
            }

            $list['zong'] = \think\Db::name('member')->where(array('pid'=>$id))->count();
            $where = array();
            if($idlist){
                $where['id'] = array('IN',$idlist);
            }else{
                $where['pid'] = $id;
            }

            $list['sang'] = \think\Db::name('member')->where($where)->count();

            $where2 = array();
            $where2['uid'] = $id;
            $where2['type'] = '会员套餐';
            $list['total'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
            if($list['level_id'] >3){
                $where2['type'] = '商品购买';
                $total = \think\Db::name('member_achievement')->where($where2)->sum('money');
                $list['total'] = $list['total'] + $total;
            }

            //这个月
            $time1 = strtotime(date('Y-m'));
            $time2 = strtotime(date('Y-m-d')) + 86400;

            $where['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');
            $where2['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');


            $list['number'] = \think\Db::name('member')->where($where)->count();
            
            $where2['type'] = '会员套餐';
            $list['month'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
            if($list['level_id'] >3){
                $where2['type'] = '商品购买';
                $month = \think\Db::name('member_achievement')->where($where2)->sum('money');
                $list['month'] = $list['month'] + $month;
            }

            return json(array('success'=>true,'info'=>'','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));
        }
    }

    // 代理统计
        public function statistics(){
             //验证会员appkey获取id
            $appkey = input('post.appkey','appkey');
            $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
            if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

            $list = get_member($id);
            if ($list) {

                $type = input('post.type','');
                if($type){
                    switch ($type) {
                        case '7':
                            if($list['level_id'] != 7){
                                return json(array('success'=>false,'info'=>'不是街/镇代理不能查看！'));
                            }
                            break;
                        case '8':
                            if($list['level_id'] != 8){
                                return json(array('success'=>false,'info'=>'不是县/区代理不能查看！'));
                            }
                           break;
                        case '10':
                            if($list['level_id'] !=10){
                                return json(array('success'=>false,'info'=>'不是省/市代理不能查看！'));
                            }
                            break;
                    }
                }

                $list['zong'] = \think\Db::name('member')->where(array('pid'=>$id))->count();

                $idlist = \think\Db::name('member_relation')->where(array('pid'=>$id))->column('id');
                $idlist = implode(",",$idlist);
                $where = array();
                if($idlist){
                    $where['id'] = array('IN',$idlist);
                }else{
                    $where['pid'] = $id;
                }

                $list['sang'] = \think\Db::name('member')->where($where)->count();

                $where2 = array();
                $where2['uid'] = $id;
                $where2['type'] = '会员套餐';
                $list['total'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
                if($list['level_id'] >3){
                    $where2['type'] = '商品购买';
                    $total = \think\Db::name('member_achievement')->where($where2)->sum('money');
                    $list['total'] = $list['total'] + $total;
                }

                //这个月
                $time1 = strtotime(date('Y-m'));
                $time2 = strtotime(date('Y-m-d')) + 86400;

                $where['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');
                $where2['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');


                $list['number'] = \think\Db::name('member')->where($where)->count();
                
                $where2['type'] = '会员套餐';
                $where2['level_id'] = $list['level_id'];
                $list['month'] = \think\Db::name('member_achievement')->where($where2)->sum('money');
                if($list['level_id'] >3){
                    $where2['type'] = '商品购买';
                    $month = \think\Db::name('member_achievement')->where($where2)->sum('money');
                    $list['month'] = $list['month'] + $month;
                }
                return json(array('success'=>true,'info'=>'','list'=>$list));
            }else{
                return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));
            }
        }


    //修改用户信息
    public function edit(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $data = array();
        $nickname = input('post.nickname','');
        if($nickname){
            $data['nickname'] = $nickname;
        }
        $sex = input('post.sex','');
        if($sex){
            $data['sex'] = $sex;
        }
        $birthday = input('post.birthday','');
        if($birthday){
            $data['birthday'] = $birthday;
        }
        $remark = input('post.remark','');
        if($remark){
            $data['remark'] = $remark;
        }
        $card = input('post.card','');
        if($card){
            $data['card'] = $card;
        }
        $truename = input('post.truename','');
        if($truename){
            $data['truename'] = $truename;
        }

        if(isset($_FILES['userface'])){
            //上传文件
            $file = $_FILES['userface'];
            //上传文件限制格式
            $type = array('image/jpg', 'image/gif','image/png','image/jpeg');
            if (!isset($file) || $file['error'] > 0 || !in_array($file['type'],$type)) {
                return json(array('success'=>false,'info'=>'文件上传格式错误'));exit;
            }
            //保存文件
            $path = "/public/userface/".$id.'_'.randChar().'.jpg';
            if(move_uploaded_file($file["tmp_name"], $_SERVER['DOCUMENT_ROOT'].$path)){     
                // 按照原图的比例生成一个最大为200*200的缩略图并保存
                $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$path);
                $image->thumb(400, 400)->save($_SERVER['DOCUMENT_ROOT'].$path);         
                //将头像路径写入数据库
                $data['userface'] = $path;
            }
        }
        $userface = input('post.userface','');
        //处理base64编码的图片上传
        if(strpos($userface, 'data:image') !== false){
            //保存文件
            $path = "/public/userface/".$id.'_'.randChar().'.jpg';
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $userface, $result)){
                $type = $result[2];
                if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $userface)), FILE_USE_INCLUDE_PATH)) {
                        return json(array('success'=>false,'info'=>'图片上传失败！'));
                    }else{
                        // 按照原图的比例生成一个最大为200*200的缩略图并保存
                        $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$path);
                        $image->thumb(400, 400)->save($_SERVER['DOCUMENT_ROOT'].$path);  
    
                        //将头像路径写入数据库
                        $data['userface'] = $path;
                    }
                }else{
                    //文件类型错误
                    return json(array('success'=>false,'info'=>'图片上传类型错误！'));
                }
             
            }else{
                //文件错误
                return json(array('success'=>false,'info'=>'文件错误！'));
            }
        }
        if (\think\Db::name('member')->where(array('id'=>$id))->update($data)) {
            //修改数据后清除缓存
            \think\Cache::rm('member_'.$id);
            $list = get_member($id);
            if ($list) {
                switch ($list['sex']) {
                    case '1':
                        $list['sexname'] = '男';
                        break;
                    case '2':
                        $list['sexname'] = '女';
                        break;
                    default:
                        $list['sexname'] = '保密';
                        break;
                }
                //获取会员等级分组信息
                $level = get_member_level();
                //转换用户对应的等级名称
                foreach ($level as $key => $value) {
                    if($list['level_id'] == $value['id']){$list['level_name'] = $value['name'];break;}
                }
                if(strpos($list['userface'],'http://') ===  false){
                    $list['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$list['userface'];
                }
                //会员是否已签到
                $list['signin'] = \think\Db::name('member_signin')->where(array('uid'=>$id,'time'=>date('Ymd')))->count();

                //拼接条件
                $where = array();
                $where['pid'] = $id;
                $list['zong'] = \think\Db::name('member')->where($where)->count();

                $idlist = \think\Db::name('member_relation')->where($where)->column('id');
                $idlist = implode(",",$idlist);
                if($idlist){
                    $where['id'] = array('IN',$idlist);
                    $where['pid'] = array('NEQ',$id);
                }
                $list['sang'] = \think\Db::name('member')->where($where)->count();

                $where['pid'] = $id;

                $list['total'] = \think\Db::name('member')->where($where)->sum('money');

                //这个月
                $time1 = strtotime(date('Y-m'));
                $time2 = strtotime(date('Y-m-d'));
                $where['create_time'] = array(array('EGT', $time1), array('ELT', $time2), 'AND');

                $list['number'] = \think\Db::name('member')->where($where)->count();
                $list['month'] = \think\Db::name('member')->where($where)->sum('money');
            }
            return json(array('success'=>true,'info'=>'修改成功','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //修改密码
    public function modify(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        //用户密码
        $password = input('post.password','');
        if(strlen($password)<6 || strlen($password)>18){
            return json(array('success'=>false,'info'=>'会员密码请在6-18位数之间！'));exit;
        }
        $password = password(sha1($password));
        if(\think\Db::name('member')->where(array('id'=>$id,'password'=>$password,'is_state'=>1))->count() > 0){
            //用户密码
            $newpassword = input('post.newpassword','');
            $newpassword = password(sha1($newpassword));
            \think\Db::name('member')->where(array('id'=>$id))->setField('password',$newpassword);
            return json(array('success'=>true,'info'=>'修改成功'));
        }else{
            return json(array('success'=>false,'info'=>'密码输入错误！'));exit;
        }
    }

    //忘记密码
    public function forget(){
        // 验证用户是否输入验证码
        $mobile = input('post.mobile','');
        $sendcode = \think\Cache::get('sendcode_'.$mobile);
        if(!$mobile || !$sendcode){
            return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;
        }
        //手机验证码
        $code = input('post.code','');
        if($code != $sendcode){
            return json(array('success'=>false,'info'=>'验证码错误！'));exit;
        }
        //用户密码
        $password = input('post.password','');
        if(strlen($password)<6 || strlen($password)>18){
            return json(array('success'=>false,'info'=>'会员密码请在6-18位数之间！'));exit;
        }
        $password = password(sha1($password));
        \think\Db::name('member')->where(array('username'=>$mobile))->setField('password',$password);
        //清除验证码缓存
        \think\Cache::rm('sendcode_'.$mobile); 
        return json(array('success'=>true,'info'=>'修改成功'));
    }

    //设置支付密码
    public function paypassword(){
        // 验证用户是否输入验证码
        $mobile = input('post.mobile','');
        $sendcode = \think\Cache::get('sendcode_'.$mobile);
        if(!$mobile || !$sendcode){
            return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;
        }
        //手机验证码
        $code = input('post.code','');
        if($code != $sendcode){
            return json(array('success'=>false,'info'=>'验证码错误！'));exit;
        }
        //用户密码
        $password = input('post.password','');
        if(strlen($password)<6 || strlen($password)>18){
            return json(array('success'=>false,'info'=>'会员密码请在6-18位数之间！'));exit;
        }
        $password = password(sha1($password));
        \think\Db::name('member')->where(array('username'=>$mobile))->setField('paypassword',$password);
        //清除验证码缓存
        \think\Cache::rm('sendcode_'.$mobile); 
        return json(array('success'=>true,'info'=>'修改成功'));
    }
    
    //绑定支付宝
    public function setalipay(){
        // 验证用户是否输入验证码
        $mobile = input('post.mobile','');
        $sendcode = \think\Cache::get('sendcode_'.$mobile);
        if(!$mobile || !$sendcode){
            return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;
        }
        //手机验证码
        $code = input('post.code','');
        if($code != $sendcode){
            return json(array('success'=>false,'info'=>'验证码错误！'));exit;
        }
        //支付宝账号
        $alipay = input('post.alipay','');
        \think\Db::name('member')->where(array('username'=>$mobile))->setField('alipay',$alipay);
        //清除验证码缓存
        \think\Cache::rm('sendcode_'.$mobile); 
        
        $id = \think\Db::name('member')->where(array('username'=>$mobile))->value('id');
        \think\Cache::rm('member_'.$id);
        return json(array('success'=>true,'info'=>'修改成功'));
    }

    //获取用户订单数量
    public function order_count(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //统计订单数量
        $count = \think\Db::name('member_order')->where(array('uid'=>$id))->group('is_state')->column('count(order_id)','is_state');
        $count[0] = 0;
        for ($i=1; $i < 10 ; $i++) { 
            $count[$i] = isset($count[$i]) ? $count[$i] : 0;
            $count[0] = $count[0] + $count[$i];
        }
        return json(array('success'=>true,'info'=>'','list'=>$count));
    }

    //获取首页轮播图
    public function index_banner(){
        // 查询广告图片数据
        $picture = get_picture();
        $list = array();
        foreach ($picture as $key => $value) {
            if($value['image']){
                foreach ($value['image'] as $k => $val) {
                    //获取商品信息
                    $goods = get_goods($val['url']);
                    $val['goods_price'] = $goods['goods_price'] ? $goods['goods_price'] : 'xxx';
                    $val['goods_name'] = $goods['goods_name'] ? $goods['goods_name'] : 'xxx';
                    $val['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['logo'];
                    $value['image'][$k] = $val;
                }
                // foreach ($value['image'] as $k => $val) {
                //     //获取商品信息
                //     $article = get_article($val['url']);
                //     $val['title'] = $article['title'] ? $article['title'] : 'xxx';
                //     $val['content'] = $article['content'] ? $article['content'] : 'xxx';
                //     // $val['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['logo'];
                //     // $value['image'][$k] = $val;
                // }
            }
            switch ($value['id']) {
                case '1': $list['header'] = $value['image'];break;
                case '2': $list['center'] = $value['image'];break;
                case '3': $list['footer'] = $value['image'];break;
                case '4': $list['bottom'] = $value['image'];break;
                case '5': $list['business'] = $value['image'];break;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取首页推荐商品
    public function index_goods(){
        //从缓存获取
        $list = \think\Cache::get('index_goods');
        if(!$list){
            //拼接条件
            $where = array();
            $where['is_state'] = 1;
            $where['is_recom'] = 1;
            $list =  \think\Db::name('goods')->field('goods_id')->where($where)->order('sort')->limit(9)->select();
            if($list){
                foreach ($list as $key => $value) {
                    $value = get_goods($value['goods_id']);
                    $value['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['goods_logo'];
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('goods')->set('index_goods',$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取热门搜索名称
    public function search(){
        //热门搜索名称
        $list = get_config('search');
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取商品分类信息
    public function goods_type(){
        //获取所有商品分类
        $type = get_goods_type();
        $list = array();
        foreach ($type as $value) {
            if($value['is_state'] == 1){
                $list[] = $value;
            }
        }
        if($list){
            foreach ($list as $key => $value) {
                $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取商品列表
    public function goods_list(){
        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        // 搜索条件
        $where = array();
        $where['is_state'] = 1;
        $type = input('post.type','');  
        if($type){
            switch ($type) {
                case 'xiang':
                    $where['is_xiang'] = 1;
                    break;
                case 'juan':
                    $where['use_point'] = array('GT',0);
                    break;
            }
        }

        //输入搜索内容
        $keyword = input('post.keyword','');     
        if($keyword){
            $keyword = urldecode($keyword);
            $where['goods_name'] = array('LIKE', "%{$keyword}%");
        }
        $typeid = input('post.typeid','');     
        if($typeid){
            //获取商品分类
            $type = get_goods_type();
            $typelist = get_child($type,$typeid);
            $idlist = array($typeid);
            foreach ($typelist as $key => $value) {
                array_push($idlist,$value['id']);
            }
            $idlist = implode(',', $idlist);
            $where['typeid'] = array('IN',$idlist);
        }
        $bus_id = input('post.bus_id','');     
        if($bus_id){
            $where['bus_id'] = $bus_id;
        }
        $is_recom = input('post.is_recom','');     
        if($is_recom){
            $where['is_recom'] = $is_recom;
        }
        $is_hot = input('post.is_hot','');     
        if($is_hot){
            $where['is_hot'] = $is_hot;
        }
        // 排序
        $sort = input('post.sort','asc');
        $field = input('post.field','sort');
        $name = array('where'=>$where,'field'=>$field,'sort'=>$sort,'page'=>$page,'limit'=>$limit);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('goods_list_'.$name);
        if(!$list){
            $list = \think\Db::name('goods')->field('goods_id')->where($where)->order("{$field} {$sort}")->limit($page,$limit)->select();
            if ($list) {
                foreach ($list as $key => $value) {
                    $value = get_goods($value['goods_id']);
                    $value['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['goods_logo'];
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('goods')->set('goods_list_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

   //获取商品详情
    public function goods_detail(){
        //获取商品id
        $goods_id = input('post.id','0');
        //获取商品信息
        $list = get_goods($goods_id);
        if($list){
            $list['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['goods_logo'];
            foreach ($list['goods_image'] as $key => $value) {
                $value = 'http://'.$_SERVER['SERVER_NAME'].$value;
                $list['goods_image'][$key] = $value;
            }

            //验证会员appkey获取id
            $appkey = input('post.appkey','appkey');
            $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');

            //获取会员收藏的商品
            $list['collect'] = 0;
            $data = goods_collect($id);
            if($data){
                foreach ($data as $key => $value) {
                    if($value['goods_id'] == $list['goods_id']){
                        $list['collect'] = 1;break;
                    }
                }
            }
            //商品评论
            $list['comment'] = goods_comment($goods_id);
            if($list['comment']){
                foreach($list['comment'] as $key => $value) {
                    $user = get_member($value['uid']);
                    $value['nickname'] = $user['nickname'];
                    if(strpos($value['userface'],'http://') ===  false){
                        $value['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$value['userface'];
                    }
                    $value['image'] = json_decode($value['image'],true);
                    if($value['image']){
                        foreach ($value['image'] as $k => $val) {
                            $val = 'http://'.$_SERVER['SERVER_NAME'].$val;
                            $value['image'][$k] = $val;
                        }
                    }
                    $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                    $list['comment'][$key] = $value;
                }
            }
            //查询配送费用
            $list['freight_price'] = get_freight_price($goods_id,'','',1,$list['goods_price']);
            //获取用户设置
            $config = get_config('user');

            //赠送劵分
            $list['zeng_price'] = 0;
            if($list['discount'] < 10){
                $payprice = $list['goods_price'] * $list['discount'] / 10;
                //折扣金额
                $discount = $list['goods_price'] - $payprice;
                
                $list['zeng_price'] = $discount * $config['user'] / 100;
            }
            $list['city'] = '广东-广州';
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }


    // 计算商品规格对应的价格
    public function get_goods_price(){
        //商品id
        $goods_id = input('post.goods_id','0');  
        //配送地址
        $address = input('post.address','');
        //配送商品规格
        $spec_value = input('post.spec_value','');
        //配送商品数量
        $goods_number = input('post.goods_number/d',1);
        //订单金额
        $total = input('post.total','0');  

        //获取商品规格
        $product_list = get_goods_product($goods_id);
        if($product_list){
            $list = array();
            foreach ($product_list as $value) {
                if($value['spec_value'] == $spec_value){
                    $list = $value;
                }
            }
            if(!$list){return json(array('success'=>false,'info'=>'没有该商品规格'));}
            //查询配送费用
            $list['freight_price'] = get_freight_price($goods_id,$address,$spec_value,$goods_number,$total);
            return json(array('success'=>true,'info'=>'','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'商品没有规格'));
        }
    }

    //收藏商品
    public function goods_collect()
    {
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $goods_id = input('post.id','0');
        if(\think\Db::name('goods_collect')->where(array('goods_id'=>$goods_id,'uid'=>$id))->count() > 0){
            if (\think\Db::name('goods_collect')->where(array('goods_id'=>$goods_id,'uid'=>$id))->delete()) {
                //修改数据后清除缓存
                \think\Cache::rm('goods_collect_'.$id);
                return json(array('success'=>true,'info'=>'取消收藏成功'));
            }else{
                return json(array('success'=>false,'info'=>'取消收藏失败！'));
            }
        }else{  
            $data = array('goods_id'=>$goods_id,'uid'=>$id,'create_time'=>time());
            if (\think\Db::name('goods_collect')->insert($data)) {
                //修改数据后清除缓存
                \think\Cache::rm('goods_collect_'.$id);
                return json(array('success'=>true,'info'=>'收藏成功'));
            }else{
                return json(array('success'=>false,'info'=>'收藏失败！'));
            }
        }
    }

    //获取文章列表
    public function help(){
        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        //文总分类
        $where['typeid'] = input('post.typeid','2');
        $where['is_state'] = 1;

        $name = array('where'=>$where,'page'=>$page,'limit'=>$limit);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('article_'.$name);
        if(!$list){
            $list = \think\Db::name('article')->field('id')->where($where)->order('sort')->limit($page,$limit)->select();
            if ($list) {
                foreach ($list as $key => $value) {
                    $value = get_article($value['id']);
                    $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('article')->set('article_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取文章详情
    public function article(){
        $id = input('post.id', '0');
        $list = get_article($id);
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取话费充值套餐
    public function recharg(){
        //从缓存获取
        $list = \think\Cache::get('recharg');
        if(!$list){
            $list = \think\Db::name('recharg')->field('id')->where(array('is_state'=>1))->order('id asc')->select();
            if($list){
                foreach ($list as $key => $value) {
                    $value = get_recharg($value['id']);
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('recharg')->set('recharg',$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取话费充值会员信息
    public function recharg_count(){
        //从缓存获取
        $list = \think\Cache::get('recharg_count');
        if(!$list){
            $list = array();
            $list['count'] = \think\Db::name('recharg_order')->where(array('is_state'=>2))->count();
            $list['number'] = \think\Db::name('recharg_order')->where(array('is_state'=>2))->sum('number');
            $list['list'] = \think\Db::name('recharg_order')->field(true)->where(array('is_state'=>2))->order('create_time desc')->limit(30)->select();
            \think\Cache::tag('recharg')->set('recharg_count',$list);
        }
        if(!empty($list['list'])){
            foreach($list['list'] as $key => $value) {
                $user = get_member($value['uid']);
                $value['nickname'] = substr_replace($user['username'],'****',3,4);
                $value['create_time'] = time_tran($value['create_time']);
                $list['list'][$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //话费充值下单
    public function add_recharg(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //手机号验证
        $mobile = input('post.mobile','');
        //手机号验证
        if(!$mobile){return json(array('success'=>false,'info'=>'请输入手机号'));exit;}
        if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
            return json(array('success'=>false,'info'=>'手机号格式有误'));exit;
        }
        $goods_id = input('post.id','');
        $goods = get_recharg($goods_id);
        if(!$goods){return json(array('success'=>false,'info'=>'请选择充值套餐'));exit;}
        if($goods['is_state'] == 2){return json(array('success'=>false,'info'=>'该充值套餐已下架'));exit;}

        $data = array();
        $data['order_id'] = randChar();                                 
        $data['uid'] = $id;                                            
        $data['goods_id'] = $goods['id'];                                            
        $data['goods_name'] = $goods['name'];                                            
        $data['paytype'] = input('post.paytype','微信支付');     
        if($data['paytype'] == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($data['paytype'] == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }                                   
        $data['payprice'] = $goods['price'];                                            
        $data['mobile'] = $mobile;                                            
        $data['create_time'] = time();
        
        //删除未支付订单
        \think\Db::name('recharg_order')->where(array('uid'=>$id,'is_state'=>1))->delete();

        if (\think\Db::name('recharg_order')->insert($data)) {
            //文件记录订单信息
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/public/order/recharg_'.$data['order_id'].'.txt',json_encode($data,JSON_UNESCAPED_UNICODE)); 
            //一分钱测试
            // $data['payprice'] = 0.01;
            
            if($data['paytype'] == '微信支付'){
                
                $appid = $weixin['wxappid']; //AppID
                $mchid = $weixin['openmchid']; //商户号
                $key = $weixin['openkey']; //商户支付密钥Key
                //支付金额单位是分所以得乘以100
                $price = $data['payprice'] * 100;
                //组装数据
                $param = array();
                $param['appid'] = $appid;
                $param['mch_id'] = $mchid;
                $param['nonce_str'] = $data['order_id'];
                $param['body'] = '话费充值';
                $param['out_trade_no'] = $data['order_id'];
                $param['total_fee'] = $price;
                $param['spbill_create_ip'] = request()->ip();
                $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay_recharg/';
                $param['trade_type'] = 'APP';
                //签名
                $param['sign'] = get_sign($param,$key);
                //生成xml并且生成签名
                $postXml = create_hongbao_xml($param);
                
                //提交请求
                $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                $responseXml = curl_post_ssl($url,$postXml);

                $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
                //转换成数组
                $responseArr = ( array ) $responseObj;

                if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                    $list = array(
                        'appid'         =>  $param['appid'],
                        'partnerid'     =>  $param['mch_id'],
                        'prepayid'      =>  $responseArr['prepay_id'],
                        'package'       =>  'Sign=WXPay',
                        'noncestr'      =>  $param['nonce_str'],
                        'timestamp'     =>  time()
                    );
                    //签名
                    $list['sign'] = get_sign($list,$key);
                    $list['orderid'] = $data['order_id'];
                    return json(array('success'=>true,'info'=>'','list'=>$list,'order_id'=>$data['order_id']));
                }else{
                    return json(array('success'=>false,'info'=>$responseArr['return_msg']));
                }
            }else if($data['paytype'] == '支付宝支付'){

                require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
        
                $aop = new \AopClient();
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $aop->appId = $alipay['appid'];
                $aop->rsaPrivateKey = $alipay['privatekey'];
                $aop->format = "json";
                $aop->charset = "UTF-8";
                $aop->signType = "RSA2";
                $aop->alipayrsaPublicKey = $alipay['publickey'];
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $param = array();
                $param['body'] = '话费充值';
                $param['subject'] = '订单支付';
                $param['out_trade_no'] = $data['order_id'];
                $param['timeout_express'] = '30m';
                $param['total_amount'] = $data['payprice'];
                $param['product_code'] = 'QUICK_MSECURITY_PAY';

                $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
                $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay_recharg/');
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aop->sdkExecute($request);
                return json(array('success'=>true,'info'=>'','list'=>$response,'order_id'=>$data['order_id']));
            }else{
                return json(array('success'=>false,'info'=>'请选择支付方式！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'信息有误，请重新提交！'));
        } 
    }

    //支付充值订单
    public function payrecharg(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','');
        //获取订单信息
        $order = \think\Db::name('recharg_order')->field(true)->where(array('order_id'=>$order_id,'uid'=>$id,'is_state'=>1))->find();
        if(!$order){return json(array('success'=>false,'info'=>'订单已支付！'));} 

        $paytype = input('post.paytype','微信支付');  
        if($paytype == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($paytype == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }
        //一分钱测试
        // $order['payprice'] = 0.01;
        
        if($paytype == '微信支付'){
                
            $appid = $weixin['wxappid']; //AppID
            $mchid = $weixin['openmchid']; //商户号
            $key = $weixin['openkey']; //商户支付密钥Key
            //支付金额单位是分所以得乘以100
            $price = $order['payprice'] * 100;
            //组装数据
            $param = array();
            $param['appid'] = $appid;
            $param['mch_id'] = $mchid;
            $param['nonce_str'] = $order['order_id'];
            $param['body'] = '话费充值';
            $param['out_trade_no'] = $order['order_id'];
            $param['total_fee'] = $price;
            $param['spbill_create_ip'] = request()->ip();
            $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay_recharg/';
            $param['trade_type'] = 'APP';
            //签名
            $param['sign'] = get_sign($param,$key);
            //生成xml并且生成签名
            $postXml = create_hongbao_xml($param);
            
            //提交请求
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $responseXml = curl_post_ssl($url,$postXml);

            $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
            //转换成数组
            $responseArr = ( array ) $responseObj;
            if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                $list = array(
                    'appid'         =>  $param['appid'],
                    'partnerid'     =>  $param['mch_id'],
                    'prepayid'      =>  $responseArr['prepay_id'],
                    'package'       =>  'Sign=WXPay',
                    'noncestr'      =>  $param['nonce_str'],
                    'timestamp'     =>  time()
                );
                //签名
                $list['sign'] = get_sign($list,$key);
                $list['orderid'] = $order['order_id'];
                return json(array('success'=>true,'info'=>'','list'=>$list));
            }else{
                return json(array('success'=>false,'info'=>$responseArr['return_msg']));
            }
        }else if($paytype == '支付宝支付'){

            require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
    
            $aop = new \AopClient();
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = $alipay['appid'];
            $aop->rsaPrivateKey = $alipay['privatekey'];
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";
            $aop->alipayrsaPublicKey = $alipay['publickey'];
            //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
            $request = new \AlipayTradeAppPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $param = array();
            $param['body'] = '话费充值';
            $param['subject'] = '订单支付';
            $param['out_trade_no'] = $order['order_id'];
            $param['timeout_express'] = '30m';
            $param['total_amount'] = $order['payprice'];
            $param['product_code'] = 'QUICK_MSECURITY_PAY';

            $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
            $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay_recharg/');
            $request->setBizContent($bizcontent);
            //这里和普通的接口调用不同，使用的是sdkExecute
            $response = $aop->sdkExecute($request);
            return json(array('success'=>true,'info'=>'','list'=>$response));
        }else{
            return json(array('success'=>false,'info'=>'请选择支付方式！'));
        }
    }

    //获取话费充值订单
    public function setmeal(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        $name = array('uid'=>$id,'page'=>$page,'limit'=>$limit);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('setmeal_'.$name);
        if(!$list){
            //查询满足要求的数据并且每页显示24条数据
            $list = \think\Db::name('recharg_order')->field(true)->where(array('uid'=>$id))->limit($page,$limit)->select();
            if($list){
                foreach ($list as $key => $value) {
                    $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('recharg')->set('setmeal_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取话费充值记录
    public function recharg_log(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['uid'] = $id;
        //订单号
        $id = input('post.id','');
        if($id){
            $where['order_id'] = $id;
        }
        $name = array('where'=>$where,'page'=>$page,'limit'=>$limit);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('recharg_log_'.$name);
        if(!$list){
            //查询满足要求的数据
            $list = \think\Db::name('recharg_log')->field(true)->where($where)->limit($page,$limit)->select();
            if($list){
                foreach ($list as $key => $value) {
                    $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('recharg')->set('recharg_log_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //修改话费充值订单手机号
    public function setmobile(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','0');
        //手机号验证
        $mobile = input('post.mobile','');
        //手机号验证
        if(!$mobile){return json(array('success'=>false,'info'=>'请输入手机号'));exit;}
        if(!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
            return json(array('success'=>false,'info'=>'手机号格式有误'));exit;
        }
        if(\think\Db::name('recharg_order')->where(array('uid'=>$id,'order_id'=>$order_id))->setField('mobile',$mobile)){
            //清除Api获取话费充值相关接口缓存
            \think\Cache::clear('recharg');
            return json(array('success'=>true,'info'=>'修改成功'));
        }else{
            return json(array('success'=>false,'info'=>'修改失败'));
        }
    }

    //获取会员升级套餐
    public function member_goods(){

        // 搜索条件
        $where = array();
        $where['is_state'] = 1;

        //输入搜索内容
        $keyword = input('post.keyword','');     
        if($keyword){
            $where['name'] = "VIP会员套餐";
        }

        $name = array('where'=>$where);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        // $list = \think\Db::name('member_goods')->where('name',"VIP会员套餐")->find();
        // return json($list);
        $list = \think\Cache::get('member_goods_'.$name);
            
        if(!$list){
            $list = \think\Db::name('member_goods')->where('name',"VIP会员套餐")->find();
            // return json($list);exit; 
            // if ($list) {
            //     foreach ($list as $key => $value) {
            //         return json($value);exit;
            //         $value = get_member_goods($value['id']);
            //         $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
            //         $list[$key] = $value;
            //     }
            // }
            \think\Cache::tag('member_goods')->set('member_goods_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }
    //获取会员升级套餐
    public function member_goodst(){

        
       $list = \think\Cache::get('member_goodst');

       if(!$list){
        $list = \think\Db::name('member_goods')->where('name',"店长升级套餐")->find();
            \think\Cache::set('member_goodst',$list);
       }
        
        //从缓存获取
        
        return json($list);
       
        
    }

    //购买升级会员套餐
    public function add_goods(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        // dump($id);
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 
        
        //购买的套餐
        $goods_id = input('post.id','');
        $goods = get_member_goods($goods_id);
        if(!$goods){return json(array('success'=>false,'info'=>'请选择套餐'));exit;}

        $data = array();
        $data['order_id'] = randChar();                                 
        $data['uid'] = $id;                                            
        $data['goods_id'] = $goods['id'];                                            
        $data['paytype'] = input('post.paytype','微信支付');     
        if($data['paytype'] == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($data['paytype'] == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }                                   
        $data['payprice'] = $goods['price'];                                            
        $data['create_time'] = time();
        //判断是否已经购买过该套餐
        if(\think\Db::name('member_goods_order')->where(array('uid'=>$id,'goods_id'=>$goods['id'],'is_state'=>2))->count() > 0){
            return json(array('success'=>false,'info'=>'你已购买该套餐！'));exit;
        }  

        $data['username'] = input('post.username','');                  //收货人
        $data['mobile'] = input('post.mobile','');                      //手机号
        $data['city'] = input('post.city','');                          //地区
        $data['address'] = input('post.address','');                    //详细地址
        $data['remark'] = input('post.remark','');                    
        if(!$data['username'] || !$data['mobile'] || !$data['city'] || !$data['address'] || !$data['remark']){
            return json(array('success'=>false,'info'=>'请填写收货地址！请选择套餐'));
        }   

        //是否已购买未支付
        \think\Db::name('member_goods_order')->insert($data);
        if ($data) {
            //一分钱测试
            // $data['payprice'] = 0.01;
            
            if($data['paytype'] == '微信支付'){
                
                $appid = $weixin['wxappid']; //AppID
                $mchid = $weixin['openmchid']; //商户号
                $key = $weixin['openkey']; //商户支付密钥Key
                //支付金额单位是分所以得乘以100
                $price = $data['payprice'] * 100;
                //组装数据
                $param = array();
                $param['appid'] = $appid;
                $param['mch_id'] = $mchid;
                $param['nonce_str'] = $data['order_id'];
                $param['body'] = '会员升级';
                $param['out_trade_no'] = $data['order_id'];
                $param['total_fee'] = $price;
                $param['spbill_create_ip'] = request()->ip();
                $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay_goods/';
                $param['trade_type'] = 'APP';
                //签名
                $param['sign'] = get_sign($param,$key);
                //生成xml并且生成签名
                $postXml = create_hongbao_xml($param);
                
                //提交请求
                $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                $responseXml = curl_post_ssl($url,$postXml);

                $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
                //转换成数组
                $responseArr = ( array ) $responseObj;
                if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                    $list = array(
                        'appid'         =>  $param['appid'],
                        'partnerid'     =>  $param['mch_id'],
                        'prepayid'      =>  $responseArr['prepay_id'],
                        'package'       =>  'Sign=WXPay',
                        'noncestr'      =>  $param['nonce_str'],
                        'timestamp'     =>  time()
                    );
                    //签名
                    $list['sign'] = get_sign($list,$key);
                    $list['orderid'] = $data['order_id'];
                    return json(array('success'=>true,'info'=>'','list'=>$list,'order_id'=>$data['order_id']));
                }else{
                    return json(array('success'=>false,'info'=>$responseArr['return_msg']));
                }
            }else if($data['paytype'] == '支付宝支付'){

                require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
        
                $aop = new \AopClient();
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $aop->appId = $alipay['appid'];
                $aop->rsaPrivateKey = $alipay['privatekey'];
                $aop->format = "json";
                $aop->charset = "UTF-8";
                $aop->signType = "RSA2";
                $aop->alipayrsaPublicKey = $alipay['publickey'];
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $param = array();
                $param['body'] = '会员升级';
                $param['subject'] = '订单支付';
                $param['out_trade_no'] = $data['order_id'];
                $param['timeout_express'] = '30m';
                $param['total_amount'] = $data['payprice'];
                $param['product_code'] = 'QUICK_MSECURITY_PAY';

                $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
                $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay_goods/');
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aop->sdkExecute($request);
                return json(array('success'=>true,'info'=>'','list'=>$response,'order_id'=>$data['order_id']));
            }else{
                return json(array('success'=>false,'info'=>'请选择支付方式！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'信息有误，请重新提交！'));
        } 
    }

    //获取投资项目列表
    public function invest(){
        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        // 搜索条件
        $where = array();
        $where['is_state'] = 1;
        $where['start_time'] = array('ELT', time());
        $where['end_time'] = array('EGT', time());
        //输入搜索内容
        $keyword = input('post.keyword','');     
        if($keyword){
            $where['name'] = array('LIKE', "%{$keyword}%");
        }
        $name = array('where'=>$where,'page'=>$page,'limit'=>$limit);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('invest_'.$name);
        if(!$list){
            $list = \think\Db::name('invest')->field('id')->where($where)->order("sort")->limit($page,$limit)->select();
            if ($list) {
                foreach ($list as $key => $value) {
                    $value = get_invest($value['id']);
                    $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('invest')->set('invest_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }
    
    //获取投资项目详情
    public function invest_detail(){
        //获取项目id
        $id = input('post.id','0');
        //获取项目信息
        $list = get_invest($id);
        if($list){
            //转换商家介绍参数
            $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
            //多图
            foreach ($list['image'] as $key => $value) {
                $value = 'http://'.$_SERVER['SERVER_NAME'].$value;
                $list['image'][$key] = $value;
            }
            //购买人数
            $list['member'] = \think\Db::name('invest_order')->where(array('goods_id'=>$list['id'],'is_state'=>2))->count();
            //转换项目剩余天数
            $time = strtotime(date('Y-m-d',time()));
            $end_time = strtotime(date('Y-m-d',$list['end_time']));
            if ($time < $end_time) {
                $list['time'] = ($end_time - $time) / 86400;
            }else{
                $list['time'] = 0;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //投资项目下单
    public function add_invest(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 
        
        //投资的项目
        $goods_id = input('post.id','');
        $goods = get_invest($goods_id);
        if(!$goods){return json(array('success'=>false,'info'=>'请选择投资项目'));exit;}

        //投资的股数
        $number = input('post.number','');
        if(!$number){return json(array('success'=>false,'info'=>'请输入投资股数'));exit;}

        $data = array();
        $data['order_id'] = randChar();                                 
        $data['uid'] = $id;                                            
        $data['goods_id'] = $goods['id'];                                            
        $data['paytype'] = input('post.paytype','微信支付');     
        if($data['paytype'] == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($data['paytype'] == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }                                   
        $data['goods_price'] = $goods['price'];                                            
        $data['huitui'] = $goods['huitui'];                                            
        $data['payprice'] = $goods['price'] * $number;                                            
        $data['number'] = $number;                                            
        $data['create_time'] = time();   
        if (\think\Db::name('invest_order')->insert($data)) {
            //一分钱测试
            // $data['payprice'] = 0.01;
            
            if($data['paytype'] == '微信支付'){
                
                $appid = $weixin['wxappid']; //AppID
                $mchid = $weixin['openmchid']; //商户号
                $key = $weixin['openkey']; //商户支付密钥Key
                //支付金额单位是分所以得乘以100
                $price = $data['payprice'] * 100;
                //组装数据
                $param = array();
                $param['appid'] = $appid;
                $param['mch_id'] = $mchid;
                $param['nonce_str'] = $data['order_id'];
                $param['body'] = '投资项目';
                $param['out_trade_no'] = $data['order_id'];
                $param['total_fee'] = $price;
                $param['spbill_create_ip'] = request()->ip();
                $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay_invest/';
                $param['trade_type'] = 'APP';
                //签名
                $param['sign'] = get_sign($param,$key);
                //生成xml并且生成签名
                $postXml = create_hongbao_xml($param);
                
                //提交请求
                $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                $responseXml = curl_post_ssl($url,$postXml);

                $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
                //转换成数组
                $responseArr = ( array ) $responseObj;
                if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                    $list = array(
                        'appid'         =>  $param['appid'],
                        'partnerid'     =>  $param['mch_id'],
                        'prepayid'      =>  $responseArr['prepay_id'],
                        'package'       =>  'Sign=WXPay',
                        'noncestr'      =>  $param['nonce_str'],
                        'timestamp'     =>  time()
                    );
                    //签名
                    $list['sign'] = get_sign($list,$key);
                    $list['orderid'] = $data['order_id'];
                    return json(array('success'=>true,'info'=>'','list'=>$list,'order_id'=>$data['order_id']));
                }else{
                    return json(array('success'=>false,'info'=>$responseArr['return_msg']));
                }
            }else if($data['paytype'] == '支付宝支付'){

                require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
        
                $aop = new \AopClient();
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $aop->appId = $alipay['appid'];
                $aop->rsaPrivateKey = $alipay['privatekey'];
                $aop->format = "json";
                $aop->charset = "UTF-8";
                $aop->signType = "RSA2";
                $aop->alipayrsaPublicKey = $alipay['publickey'];
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $param = array();
                $param['body'] = '投资项目';
                $param['subject'] = '订单支付';
                $param['out_trade_no'] = $data['order_id'];
                $param['timeout_express'] = '30m';
                $param['total_amount'] = $data['payprice'];
                $param['product_code'] = 'QUICK_MSECURITY_PAY';

                $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
                $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay_invest/');
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aop->sdkExecute($request);
                return json(array('success'=>true,'info'=>'','list'=>$response,'order_id'=>$data['order_id']));
            }else{
                return json(array('success'=>false,'info'=>'请选择支付方式！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'信息有误，请重新提交！'));
        } 
    }

    //支付投资订单
    public function payinvest(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','');
        //获取订单信息
        $order = \think\Db::name('invest_order')->field(true)->where(array('order_id'=>$order_id,'is_state'=>1))->find();
        if(!$order){return json(array('success'=>false,'info'=>'订单已支付！'));} 

        $paytype = input('post.paytype','微信支付');  
        if($paytype == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($paytype == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }

        //一分钱测试
        // $order['payprice'] = 0.01;

        if($paytype == '微信支付'){
                
            $appid = $weixin['wxappid']; //AppID
            $mchid = $weixin['openmchid']; //商户号
            $key = $weixin['openkey']; //商户支付密钥Key
            //支付金额单位是分所以得乘以100
            $price = $order['payprice'] * 100;
            //组装数据
            $param = array();
            $param['appid'] = $appid;
            $param['mch_id'] = $mchid;
            $param['nonce_str'] = $order['order_id'];
            $param['body'] = '投资项目';
            $param['out_trade_no'] = $order['order_id'];
            $param['total_fee'] = $price;
            $param['spbill_create_ip'] = request()->ip();
            $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay_invest/';
            $param['trade_type'] = 'APP';
            //签名
            $param['sign'] = get_sign($param,$key);
            //生成xml并且生成签名
            $postXml = create_hongbao_xml($param);
            
            //提交请求
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $responseXml = curl_post_ssl($url,$postXml);

            $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
            //转换成数组
            $responseArr = ( array ) $responseObj;
            if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                $list = array(
                    'appid'         =>  $param['appid'],
                    'partnerid'     =>  $param['mch_id'],
                    'prepayid'      =>  $responseArr['prepay_id'],
                    'package'       =>  'Sign=WXPay',
                    'noncestr'      =>  $param['nonce_str'],
                    'timestamp'     =>  time()
                );
                //签名
                $list['sign'] = get_sign($list,$key);
                $list['orderid'] = $order['order_id'];
                return json(array('success'=>true,'info'=>'','list'=>$list));
            }else{
                return json(array('success'=>false,'info'=>$responseArr['return_msg']));
            }
        }else if($paytype == '支付宝支付'){

            require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
    
            $aop = new \AopClient();
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = $alipay['appid'];
            $aop->rsaPrivateKey = $alipay['privatekey'];
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";
            $aop->alipayrsaPublicKey = $alipay['publickey'];
            //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
            $request = new \AlipayTradeAppPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $param = array();
            $param['body'] = '投资项目';
            $param['subject'] = '订单支付';
            $param['out_trade_no'] = $order['order_id'];
            $param['timeout_express'] = '30m';
            $param['total_amount'] = $order['payprice'];
            $param['product_code'] = 'QUICK_MSECURITY_PAY';

            $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
            $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay_invest/');
            $request->setBizContent($bizcontent);
            //这里和普通的接口调用不同，使用的是sdkExecute
            $response = $aop->sdkExecute($request);
            return json(array('success'=>true,'info'=>'','list'=>$response));
        }else{
            return json(array('success'=>false,'info'=>'请选择支付方式！'));
        }
    }

    //获取投资订单
    public function invest_order(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //搜索条件
        $where = array();
        $where['o.uid'] = $id;
        
        $is_state = input('post.is_state','');
        if($is_state){
            $where['o.is_state'] = $is_state;
        }
        $name = md5(json_encode($where,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('invest_order'.$name);
        if(!$list){
            //查询满足要求的数据并且每页显示24条数据
            $list = \think\Db::name('invest_order')->alias('o')
                                                   ->field('o.*,i.name,i.logo,i.count,i.number numbers,i.price')
                                                   ->join('__INVEST__ i','o.goods_id = i.id')
                                                   ->where($where)
                                                   ->select();

            if($list){
                foreach($list as $key => $value) {
                    $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                    $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('invest')->set('invest_order'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取商家分类
    public function business_type(){
        
        $list = get_business_type();
        if ($list) {
            foreach($list as $key => $value) {
                $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取商家列表
    public function business(){

        //分页
        $limit = input('post.limit','20');
        if($limit > 20){$limit = 20;}
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        // 搜索条件
        $where = array();
        $where['is_state'] = 1;
        //输入搜索内容
        $keyword = input('post.keyword','');     
        if($keyword){
            $where['name'] = array('LIKE', "%{$keyword}%");
        }
        $typeid = input('post.typeid','');     
        if($typeid){
            $where['typeid'] = $typeid;
        }

        //拼接排序条件
        $sort = input('post.sort', 'distance');
        $order = input('post.order', 'asc');

        //经度
        $longitude = input('post.longitude');
        $longitude = (float)$longitude;
        //纬度
        $latitude = input('post.latitude');
        $latitude = (float)$latitude;

        $name = array('where'=>$where,'sort'=>$sort,'order'=>$order,'page'=>$page,'limit'=>$limit,'longitude'=>$longitude,'latitude'=>$latitude);
        $name = md5(json_encode($name,JSON_UNESCAPED_UNICODE));
        //从缓存获取
        $list = \think\Cache::get('business_'.$name);
        if(!$list){
            //近似值算法
            $list = \think\Db::name('business')->field("*,(('$longitude' - longitude)*('$longitude' - longitude) + ('$latitude' - latitude)*('$latitude' - latitude)) AS distance")->where($where)->order("{$sort} {$order}")->limit($page,$limit)->select();
            if ($list) {
                foreach ($list as $key => $value) {
                    if($value['star'] > 5){
                        $value['star'] = 5;
                    }
                    $value['remark'] = $value['remark'] ? $value['remark'] : '';
                    $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                    $value['distance'] = $this->getdistance($longitude, $latitude, $value['longitude'], $value['latitude']);
                    $list[$key] = $value;
                }
            }
            \think\Cache::tag('business')->set('business_'.$name,$list);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }
    
    //计算两点之间的距离
    public function getdistance($lng1,$lat1,$lng2,$lat2){
        //将角度转为狐度 
        $radLat1=deg2rad($lat1);
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;//两纬度之差,纬度<90
        $b=$radLng1-$radLng2;//两经度之差纬度<180
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
        //$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = round($s, 2);
        if($s < 1){
            $s = $s*1000 . 'm';
        }else{
            $s = $s . 'km';
        }
        return $s;
    }

    //获取商家详情
    public function business_detail(){
        //获取商品id
        $id = input('post.id','0');
        //获取商品信息
        $list = get_business($id);
        if($list){

            $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
            //多图
            $list['image'] = json_decode($list['image']);  
            foreach ($list['image'] as $key => $value) {
                $value = 'http://'.$_SERVER['SERVER_NAME'].$value;
                $list['image'][$key] = $value;
            }
            //验证会员appkey获取id
            $appkey = input('post.appkey','appkey');
            $uid = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');

            $list['collect'] = \think\Db::name('business_collect')->where(array('bus_id'=>$list['id'],'uid'=>$uid))->count();
            $list['collect_num'] = \think\Db::name('business_collect')->where(array('bus_id'=>$list['id']))->count();
            $list['create_time'] = date('Y-m-d H:i',$list['create_time']);

        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //商家详情信息
    public function busdetail(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //获取商品信息
        $list = get_business($id);
        $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
        $list['code'] = 'http://'.$_SERVER['SERVER_NAME'].$list['code'];
        $list['total'] = \think\Db::name('business_money_log')->where(array('uid'=>$list['id'],'money'=>array('GT',0)))->sum('money');
        $list['total'] = sprintf("%.2f",$list['total']);
        
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //修改商家信息
    public function busedit(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $data = array();
        $logo = input('post.logo','');
        //处理base64编码的图片上传
        if(strpos($logo, 'data:image') !== false){
            //保存文件路径
            $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $logo, $result)){
                $type = $result[2];
                if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $logo)), FILE_USE_INCLUDE_PATH)) {
                        return json(array('success'=>false,'info'=>'图片上传失败！'));
                    }else{
                        // 按照原图的比例生成一个最大为200*200的缩略图并保存
                        $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$path);
                        $image->thumb(400, 400)->save($_SERVER['DOCUMENT_ROOT'].$path);  
    
                        //将头像路径写入数据库
                        $data['logo'] = $path;
                    }
                }else{
                    //文件类型错误
                    return json(array('success'=>false,'info'=>'图片上传类型错误！'));
                }
             
            }else{
                //文件错误
                return json(array('success'=>false,'info'=>'文件错误！'));
            }
        }
        if (\think\Db::name('business')->where(array('id'=>$id))->update($data)) {
            //修改数据后清除缓存
            \think\Cache::rm('business_'.$id);

            //获取商品信息
            $list = get_business($id);
            $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
            $list['code'] = 'http://'.$_SERVER['SERVER_NAME'].$list['code'];
            $list['total'] = \think\Db::name('business_money_log')->where(array('uid'=>$list['id'],'money'=>array('GT',0)))->sum('money');
   

            return json(array('success'=>true,'info'=>'修改成功','list'=>$list));
        }else{
            return json(array('success'=>false,'info'=>'修改失败！'));
        }
    }

    //收藏商家
    public function business_collect()
    {
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $bus_id = input('post.id','0');
        if(\think\Db::name('business_collect')->where(array('bus_id'=>$bus_id,'uid'=>$id))->count() > 0){
            if (\think\Db::name('business_collect')->where(array('bus_id'=>$bus_id,'uid'=>$id))->delete()) {
                return json(array('success'=>true,'info'=>'取消收藏成功'));
            }else{
                return json(array('success'=>false,'info'=>'取消收藏失败！'));
            }
        }else{  
            $data = array('bus_id'=>$bus_id,'uid'=>$id,'create_time'=>time());
            if (\think\Db::name('business_collect')->insert($data)) {
                return json(array('success'=>true,'info'=>'收藏成功'));
            }else{
                return json(array('success'=>false,'info'=>'收藏失败！'));
            }
        }
    }

    //获取可领取优惠券
    public function coupon(){
        //拼接搜索条件
        $where = array();
        $where['is_state'] = 1;
        $where['start_time'] = array('ELT', time());
        $where['end_time'] = array('EGT', time());
        $bus_id = input('post.bus_id','');     
        if($bus_id){
            $bus_id = '0,'.$bus_id;
            $where['bus_id'] = array('IN',$bus_id);
        }
        $list = \think\Db::name('coupon')->field(true)->where($where)->select();
        if ($list) {
            foreach($list as $key => $val) {
                $val['start_time'] = date('Y/m/d', $val['start_time']);
                $val['end_time'] = date('Y/m/d', $val['end_time']);
                $list[$key] = $val;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //领取优惠券
    public function receive(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $cid = input('post.id','0');
        //拼接搜索条件
        $where = array();
        $where['typeid'] = $cid;
        $where['is_state'] = 1;
        $where['uid'] = 0;
        $where['start_time'] = array('ELT', time());
        $where['end_time'] = array('EGT', time());
        $list = \think\Db::name('coupon_list')->field(true)->where($where)->find();
        if($list){
            if(\think\Db::name('coupon_list')->where(array('uid'=>$id,'typeid'=>$cid))->count() < 1){
                if (\think\Db::name('coupon_list')->where(array('id'=>$list['id']))->setField('uid',$id)) {
                    return json(array('success'=>true,'info'=>'领取成功'));
                }else{
                    return json(array('success'=>false,'info'=>'领取失败！'));
                }
            }else{
                return json(array('success'=>false,'info'=>'你已领取该优惠券！'));
            }
        }else{  
            return json(array('success'=>false,'info'=>'优惠券已领取完！'));
        }
    }

    //获取可使用优惠券
    public function get_coupon(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        $total = input('post.total','0');  
        //拼接搜索条件
        $where = array();
        $where['c.uid'] = $id;
        $where['c.min_price'] = array('ELT',$total);
        $where['c.max_price'] = array('EGT',$total);
        $where['c.is_state'] = 1;
        $where['c.start_time'] = array('ELT', time());
        $where['c.end_time'] = array('EGT', time());
        $bus_id = input('post.bus_id/a','');  
        if($bus_id){
            $bus_id = implode(',', $bus_id);
            $where['c.bus_id'] = array('IN', '0,'.$bus_id);
        }else{
            $where['c.bus_id'] = 0;
        }
        $list = \think\Db::name('coupon_list')->alias('c')->field('c.*,b.name busname')
                                                   ->join('__BUSINESS__ b','c.bus_id = b.id','LEFT')
                                                   ->where($where)
                                                   ->select();
        if ($list) {
            foreach($list as $key => $val) {
                $val['start_time'] = date('Y/m/d', $val['start_time']);
                $val['end_time'] = date('Y/m/d', $val['end_time']);
                $list[$key] = $val;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    } 

    // 添加购物车
    public function add_cart(){ 
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        //商品id
        $goods_id = input('post.goods_id','0');  
        //商品规格
        $spec_value = input('post.spec_value','');
        //商品数量
        $goods_number = input('post.goods_number/d',1);
        //获取商品信息
        $goods = get_goods($goods_id);
        if (!$goods){return json(array('success'=>false,'info'=>'不存在的商品信息！'));exit;} 
        //默认购买一件
        if ($goods_number < 1) {$goods_number = 1;}
        //验证用户输入库存数量
        if ($goods_number > $goods['goods_number']){return json(array('success'=>false,'info'=>'商品库存不足！'));exit;} 
        //获取商品规格
        if($goods['is_spec'] == 2){
            $product_list = get_goods_product($goods_id);
            $list = array();
            foreach ($product_list as $value) {
                if($value['spec_value'] == $spec_value){
                    $list = $value;
                }
            }
            if(!$list){return json(array('success'=>false,'info'=>'商品规格选择错误，请重新选择！'));}
            //验证用户输入购买数量
            if ($goods_number > $list['goods_number']){return json(array('success'=>false,'info'=>'商品库存不足！'));exit;}
            \think\Db::name('goods_product')->where(array('id'=>$list['id']))->setDec('goods_number',$goods_number); 
            //修改后清除该商品缓存
            \think\Cache::rm('goods_product_'.$goods_id);
        }
        //减少商品库存
        \think\Db::name('goods')->where(array('goods_id'=>$goods_id))->setDec('goods_number',$goods_number); 
        //修改后清除该商品缓存
        \think\Cache::rm('goods_'.$goods_id);
        //拼接数据写入数据库
        $data = array();
        $data['uid'] = $id;
        $data['goods_id'] = $goods_id;
        $data['product_id'] = isset($list['id']) ? $list['id'] : 0;
        $data['goods_name'] = $goods['goods_name'];
        $data['goods_price'] = isset($list['goods_price']) ? $list['goods_price'] : $goods['goods_price'];
        $data['vip_price'] = isset($list['vip_price']) ? $list['vip_price'] : $goods['vip_price'];
        $data['goods_number'] = $goods_number;
        $data['use_point'] = $goods['use_point'];
        $data['create_time'] = time();
        $data['spec_value'] = $spec_value;
        $data['goods_logo'] = $goods['goods_logo'];
        $data['goods_sn'] = $goods['goods_sn'];
        $data['bus_id'] = $goods['bus_id'];
        $data['is_spec'] = $goods['is_spec'];
        
        //执行添加商品进入购物车
        $where = array();
        $where['uid'] = $data['uid'];
        $where['goods_id'] = $goods_id;
        $where['product_id'] = $data['product_id'];
        if(\think\Db::name('member_cart')->where($where)->count() > 0){
            if (\think\Db::name('member_cart')->where($where)->setInc('goods_number', $goods_number)) {
                return json(array('success'=>true,'info'=>'加入成功！'));
            }else{
                return json(array('success'=>false,'info'=>'加入失败！'));
            }
        }else{
            if (\think\Db::name('member_cart')->insert($data)) {
                return json(array('success'=>true,'info'=>'加入成功！'));
            }else{
                return json(array('success'=>false,'info'=>'加入失败！'));
            }
        }
    }

    //获取购物车
    public function mycart(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        $user = get_member($id);

        //清除过期购物车商品
        clean_cart();

        $list = \think\Db::name('member_cart')->where(array('uid'=>$id))->select();
        if ($list) {
            foreach($list as $key => $value) {
                if($user['level_id'] > 1){
                    $value['goods_price'] = $value['vip_price'];
                }
                $value['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['goods_logo'];
                //计算商品总价
                $value['total'] = $value['goods_number'] * $value['goods_price'];
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    // 删除购物车
    public function del_cart(){ 
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        //拼接搜索条件
        $where = array();
        $where['uid'] = $id;
        //商品id
        $where['goods_id'] = input('post.goods_id','0');  
        //商品规格
        $where['product_id'] = input('post.product_id','');
        if(!$where['product_id']){$where['is_spec'] = 1;}
        //商品数量
        $goods_number = input('post.goods_number/d',1);
        $number = \think\Db::name('member_cart')->where($where)->value('goods_number');
        if($goods_number > $number){
            $goods_number = $number;
        }
        if (\think\Db::name('member_cart')->where($where)->setDec('goods_number', $goods_number)) {
            if(\think\Db::name('member_cart')->where($where)->value('is_state') == 1){
                //返回增加商品库存
                \think\Db::name('goods')->where(array('goods_id'=>$where['goods_id']))->setInc('goods_number', $goods_number);
                //修改后清除该商品缓存
                \think\Cache::rm('goods_'.$where['goods_id']);
                if($where['product_id']){
                    \think\Db::name('goods_product')->where(array('id'=>$where['product_id']))->setInc('goods_number', $goods_number);
                    \think\Cache::rm('goods_product_'.$where['goods_id']);
                }
            }
            return json(array('success'=>true,'info'=>'删除成功！'));
        }else{
            return json(array('success'=>false,'info'=>'删除失败！'));
        }
    }

    //获取收货地址
    public function address(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

         //拼接搜索条件
        $where = array();
        $where['uid'] = $id;
        //商品id
        $aid = input('post.id','0'); 
        if($aid){$where['id'] = $aid;} 
        $list = \think\Db::name("member_address")->where($where)->order('is_state desc')->select();
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //新增收货地址
    public function add_address(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $data = array();
        $data['username'] = input('post.username','');
        $data['address'] = input('post.address','');
        $data['mobile'] = input('post.mobile','');
        $data['cityid'] = input('post.cityid','');
        $data['city'] = input('post.city','');
        if(\think\Db::name("member_address")->where(array('uid'=>$id,'is_state'=>1))->count() < 1){
            $data['is_state'] = 1;
        }
        $data['uid'] = $id;
        if(\think\Db::name("member_address")->insert($data)){
            return json(array('success'=>true,'info'=>'添加成功'));
        }else{
            return json(array('success'=>false,'info'=>'信息有误！'));
        }
    }

    //修改收货地址
    public function edit_address(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $data = array();
        $data['id'] = input('post.id','0');
        $username = input('post.username','');
        if($username){$data['username'] = $username;}
        $address = input('post.address','');
        if($address){$data['address'] = $address;}
        $mobile = input('post.mobile','');
        if($mobile){$data['mobile'] = $mobile;}
        $cityid = input('post.cityid','');
        if($cityid){$data['cityid'] = $cityid;}
        $city = input('post.city','');
        if($city){$data['city'] = $city;}
        $is_state = input('post.is_state','');
        if($is_state){$data['is_state'] = $is_state;}

        if(isset($data['is_state']) && $data['is_state'] == 1){
            \think\Db::name("member_address")->where(array('uid'=>$id))->setField('is_state',2);
        }
        if(\think\Db::name("member_address")->where(array('id'=>$data['id'],'uid'=>$id))->update($data)){
            return json(array('success'=>true,'info'=>'修改成功'));
        }else{
            return json(array('success'=>false,'info'=>'信息有误！'));
        }
    }

    //删除收货地址
    public function del_address(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $aid = input('post.id','0');
        if(\think\Db::name("member_address")->where(array('id'=>$aid,'uid'=>$id))->delete()){
            return json(array('success'=>true,'info'=>'删除成功'));
        }else{
            return json(array('success'=>false,'info'=>'信息有误！'));
        }
    }

    //获取我的粉丝
    // public function subordinate(){
    //     //验证会员appkey获取id
    //     $appkey = input('post.appkey','appkey');
    //     $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
    //     if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

    //     //分页
    //     $limit = input('post.limit','20');
    //     $page = input('post.page','1');
    //     $page = $page - 1;
    //     $page = $page * $limit;

    //     //拼接条件
    //     $where = array();
    //     $where['pid'] = $id;
    //     $level = input('post.level','');
    //     if($level){
    //         $where['level'] = $level;
    //     }
    //     $idlist = \think\Db::name('member_relation')->where($where)->column('id');
    //     $idlist = implode(",",$idlist);

    //     $where = array();
    //     $where['a.pid'] = $id;
    //     if($idlist){
    //         $where['a.id'] = array('IN',$idlist);
    //         $where['a.pid'] = array('NEQ',$id);
    //     }
    //     $pid = input('post.pid','');
    //     if($pid){
    //         $where['a.pid'] = $id;
    //     }
    //     $list=\think\Db::query("select a.id,a.pid,a.level_id,a.nickname,m.id id1,m.pid pid2,m.nickname,a.userface,a.username,a.level_id,m.point,m.money,m.nickname parname from zsh_member a join zsh_member m on  m.pid=a.id where a.id=".$id.' or a.pid='.$id);
    //     $count = array();
    //     $count[0] = \think\Db::name('member')->alias('a')->where($where)->sum('money');
    //     $count[1] = \think\Db::name('member')->alias('a')->where($where)->sum('point');
    //     $count[2] = \think\Db::name('member')->alias('a')->where($where)->count();
       
    //     if($list){
    //         //获取会员等级分组信息
    //         $level = get_member_level();
    //         foreach ($list as $key => $value) {
    //             $value['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$value['userface'];
    //             $value['username'] = substr_replace($value['username'],'****',3,4);
    //             //转换用户对应的等级名称
    //             foreach ($level as $val) {
    //                 if($val['id'] == $value['level_id']){$value['level_name'] = $val['name'];break;}
    //             }

    //             $list[$key] = $value;
    //         }
    //     }
    //     return json(array('success'=>true,'info'=>'','list'=>$list,'count'=>$count));
    // }

    public function subordinate(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'appkey验证失败！'));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['pid'] = $id;
        $level = input('post.level','');
        if($level){
            $where['level'] = $level;
        }
        
        $idlist = \think\Db::name('member_relation')->where($where)->column('id');
        $idlist = implode(",",$idlist);
        //查询满足要求的数据
        $list = \think\Db::name('member')->alias('a')
                                         ->field('a.id,a.pid,a.nickname,a.userface,a.username,a.level_id,m.nickname parname,m.point,m.money')
                                         ->join('__MEMBER__ m','a.pid = m.id')
                                         ->where(array('a.id'=>array('IN',$idlist)))
                                         ->limit($page,$limit)
                                         ->select();
        $count = array();
        $count[0] = 0;
        $count[1] = 0;
        $count[2] = 0;
        $count[3] = 0;
        if($list){
            //获取会员等级分组信息
            $level = get_member_level();
            foreach ($list as $key => $value) {
                $value['userface'] = 'http://'.$_SERVER['SERVER_NAME'].$value['userface'];
                $value['username'] = substr_replace($value['username'],'****',3,4);
                //转换用户对应的等级名称
                foreach ($level as $val) {
                    if($val['id'] == $value['level_id']){$value['level_name'] = $val['name'];break;}
                }

                if($value['pid'] == $id){
                    $count[0] = $count[0] + $value['money'];
                    $count[1] = $count[1] + $value['point'];
                }else{
                    $count[2] = $count[2] + $value['money'];
                    $count[3] = $count[3] + $value['point'];
                }

                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list,'id'=>$id,'count'=>$count));
    }

    public function getbus(){
        $mobile = input('post.mobile','');
        //拼接条件
        $where = array();
        $where['a.mobile'] = $mobile;
        //查询满足要求的数据
        $list = \think\Db::name('setmeal_log')->alias('a')
                                     ->join('__BUSINESS__ b','b.id = a.bus_id','LEFT')
                                     ->join('__MEMBER__ m','m.id = b.uid','LEFT')
                                     ->where($where)
                                     ->value('m.username');
       
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    public function busteam(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>''));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['a.bus_id'] = $id;
        //查询满足要求的数据
        $list = \think\Db::name('setmeal_log')->alias('a')
                                    ->field('a.*,sum(b.payprice) money')
                                    ->join('__BUSINESS_TRANSFER__ b','b.order_id = a.order_id','LEFT')
                                    ->where($where)
                                    ->order('a.id desc')
                                    ->group('a.mobile')
                                    // ->limit($page,$limit)
                                    ->select();

       foreach ($list as $key => $value) {
            // 格式化时间
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    public function takelog(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>''));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['a.bus_id'] = $id;
        //查询满足要求的数据
        $list = \think\Db::name('setmeal_log')->alias('a')
                                     ->field('a.*,s.name,s.logo,s.goods')
                                     ->join('__SETMEAL__ s','s.id = a.goods','LEFT')
                                     ->where($where)
                                     ->order("a.create_time desc")
                                     // ->limit($page,$limit)
                                     ->select();
                          
       foreach ($list as $key => $value) {
            // 格式化时间
            $list[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    public function goodslog(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>''));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

       //拼接条件
        $where = array();
        $where['s.bus_id'] = $id;
        //查询满足要求的数据并且每页显示24条数据
        $list = \think\Db::name('setmeal_goods')->alias('a')
                                                ->field('a.*,sum(s.number) number')
                                                ->join('zsh_business_setmeal s','a.id = s.goods_id')
                                                ->where($where)
                                                ->group('a.id')
                                                ->limit($page,$limit)
                                                ->select();
        if ($list) {
            foreach($list as $key => $value) {
                $value['goods'] = $value['name'];
                $value['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$value['logo'];
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    // 计算订单运费
    public function order_freight_price(){
        $goods = input('post.goods_list/a','');                              //购买商品
        if(!$goods){return json(array('success'=>false,'info'=>'请选择购买商品！'));}

        $total_price = 0;
        $freight_price = 0;
        foreach ($goods as $key => $value) {
            //获取商品信息
            $list = get_goods($value['goods_id']);
            if (!$list){return json(array('success'=>false,'info'=>'不存在的商品信息！'));exit;} 
            //默认购买一件
            if ($value['goods_number'] < 1) {$value['goods_number'] = 1;}
            //验证用户输入库存数量
            if ($value['goods_number'] > $list['goods_number']){return json(array('success'=>false,'info'=>$list['goods_name'].'商品库存不足！'));exit;} 
            //获取商品规格
            if($list['is_spec'] == 2){
                $product_list = get_goods_product($value['goods_id']);
                $product = array();
                foreach ($product_list as $val) {
                    if($val['spec_value'] == $value['spec_value']){
                        $product = $val;
                    }
                }
                if(!$product){return json(array('success'=>false,'info'=>$list['goods_name'].'商品规格选择错误，请重新选择'));}
                //验证用户输入购买数量
                if ($value['goods_number'] > $product['goods_number']){return json(array('success'=>false,'info'=>$list['goods_name'].'商品库存不足！'));exit;}
            }

            $value['goods_price'] = isset($product['goods_price']) ? $product['goods_price'] : $list['goods_price'];
            //计算订单总价
            $total_price = $total_price + ($value['goods_price'] * $value['goods_number']);

            //计算配送费用
            $value['freight_price'] = get_freight_price($value['goods_id'],'',$value['spec_value'],$value['goods_number'],$total_price);
            $freight_price = $freight_price + $value['freight_price'];
        }
        if($total_price >= 60){
            $freight_price = 0;
        } 
        return json(array('success'=>true,'info'=>'','list'=>$freight_price));
    }

    //用户下单
    public function add_order(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        $data = array();
        $data['order_id'] = randChar();                                 //订单号
        $data['uid'] = $id;                                             //会员id
        $data['create_time'] = time();                                  //下单时间
        $data['remark'] = input('post.remark','');                      //订单备注
        $data['paytype'] = input('post.paytype','');                    //支付方式
        if($data['paytype'] == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
        }else if($data['paytype'] == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
        }
        $data['username'] = input('post.username','');                  //收货人
        $data['mobile'] = input('post.mobile','');                      //手机号
        $data['city'] = input('post.city','');                          //地区
        $district = input('post.district','');                          //送货地址市区
        $data['address'] = input('post.address','');                    //详细地址
        if(!$data['username'] || !$data['mobile'] || !$data['city'] || !$data['address']){
            return json(array('success'=>false,'info'=>'请填写收货地址！'));
        }   

        $goods = input('post.goods/a','');                              //购买商品
        if(!$goods){return json(array('success'=>false,'info'=>'请选择购买商品！'));}
        //  1使用余额，2使用积分
        $data['pointype'] = input('post.type',''); 
        $data['pointype'] = $data['pointype'] ? $data['pointype'] : 0;  
        $data['total_price'] = 0;                                             //订单金额
        $data['use_point'] = 0;
        $data['total_number'] = 0;
        //计算商品配送费用
        $data['freight_price'] = 0;
        $data['bus_id'] = array();
        $data['goods'] = array();

        $user = get_member($id);
        foreach ($goods as $key => $value) {
            $data['goods'][$key] = array();
            $data['goods'][$key]['goods_id'] = $value['goods_id'];
            $data['goods'][$key]['goods_number'] = $value['goods_number'];
            $data['goods'][$key]['spec_value'] = isset($value['spec_value']) ? $value['spec_value'] : '';
            //获取商品信息
            $list = get_goods($value['goods_id']);
            if($user['level_id'] > 1){
                $list['goods_price'] = $list['vip_price'];
            }
            if (!$list){return json(array('success'=>false,'info'=>'不存在的商品信息！'));exit;} 
            //默认购买一件
            if ($value['goods_number'] < 1) {$value['goods_number'] = 1;}
            //验证用户输入库存数量
            if ($value['goods_number'] > $list['goods_number']){return json(array('success'=>false,'info'=>$list['goods_name'].'商品库存不足！'));exit;} 
            //获取商品规格
            if($list['is_spec'] == 2){
                $product_list = get_goods_product($value['goods_id']);
                $product = array();
                foreach ($product_list as $val) {
                    if($val['spec_value'] == $value['spec_value']){
                        $product = $val;
                    }
                }
                if(!$product){return json(array('success'=>false,'info'=>$list['goods_name'].'商品规格选择错误，请重新选择'));}
                //验证用户输入购买数量
                if ($value['goods_number'] > $product['goods_number']){return json(array('success'=>false,'info'=>$list['goods_name'].'商品库存不足！'));exit;}
            }
            array_push($data['bus_id'],$list['bus_id']);
            $data['total_number'] = $data['total_number'] + $value['goods_number'];
            $data['goods'][$key]['bus_id'] = $list['bus_id'];
            $data['goods'][$key]['goods_logo'] = $list['goods_logo'];
            $data['goods'][$key]['goods_name'] = $list['goods_name'];
            $data['goods'][$key]['goods_price'] = isset($product['goods_price']) ? $product['goods_price'] : $list['goods_price'];
            $data['goods'][$key]['product_id'] = isset($product['id']) ? $product['id'] : 0;
            //计算订单总价
            $data['total_price'] = $data['total_price'] + ($data['goods'][$key]['goods_price'] * $value['goods_number']);

            //计算配送费用
            $data['goods'][$key]['freight_price'] = get_freight_price($value['goods_id'],$district,$value['spec_value'],$value['goods_number'],$data['total_price']);
            $data['freight_price'] = $data['freight_price'] + $data['goods'][$key]['freight_price'];

            $data['use_point'] = $data['use_point'] + ($list['use_point'] * $value['goods_number']) + $data['goods'][$key]['freight_price'];

        }
        if($data['total_price'] >= 60){
            $data['freight_price'] = 0;
        }else{}
        $data['goods'] = json_encode($data['goods'],JSON_UNESCAPED_UNICODE);
        $data['bus_id'] = implode(',', $data['bus_id']);
        //获取订单使用积分
        $data['point'] = input('post.point/d','0');
       
        if($data['use_point']<1 && $data['point']){return json(array('success'=>false,'info'=>'订单不可使用券分抵扣'));}
        if($data['point'] > $data['use_point']){return json(array('success'=>false,'info'=>'订单最多可使用'.$data['use_point'].'券分抵扣'));}
        $data['point_price'] = 0;
        //获取积分抵扣金额
        if($data['point']){
            //获取用户设置信息
            $config = get_config('user');
            //积分计算比例
            $data['point_price'] = $data['point'] / $config['ratio'];

            if($data['pointype'] == 1){
                if(\think\Db::name('member')->where(array('id'=>$id,'point'=>array('EGT', $data['point'])))->count() < 1){
                    $data['pointype'] = 2;
                    if(\think\Db::name('member')->where(array('id'=>$id,'money'=>array('EGT', $data['point'])))->count() < 1){
                        return json(array('success'=>false,'info'=>'账户余额不足！'));
                    }
                }
            }
        }

        //使用优惠劵
        $cid = input('post.coupon','0'); 
        if($cid && $data['point_price'] < $data['total_price']){
            //拼接搜索条件
            $where = array();
            $where['id'] = $cid;
            $where['uid'] = $id;
            $where['min_price'] = array('ELT',$data['total_price']);
            $where['max_price'] = array('EGT',$data['total_price']);
            $where['is_state'] = 1;
            $where['start_time'] = array('ELT', time()); 
            $where['end_time'] = array('EGT', time());
            $coupon = \think\Db::name('coupon_list')->field(true)->where($where)->find();
            if($coupon){
                $data['coupon'] = $cid;
                $data['couprice'] = $coupon['money'];
                $data['payprice'] = $data['total_price'] - $coupon['money'];
            }else{
                return json(array('success'=>false,'info'=>'优惠券已使用！'));
            }
        }else{
            $data['payprice'] = $data['total_price'];
        }
        //计算订单付款金额
        $data['payprice'] = $data['payprice'] + $data['freight_price'] - $data['point_price'];
        $data['total_price'] = $data['total_price'] + $data['freight_price'];
        if (\think\Db::name('member_order')->insert($data)) {
            //使用优惠卷
            if(isset($data['couprice'])){
                \think\Db::name('coupon_list')->where($where)->setField('is_state',2);
            }
            //使用积分抵扣
            if(isset($data['point_price'])){

                if($data['pointype'] == 1){
                    \think\Db::name('member')->where(array('id'=>$id))->setDec('point',$data['point']);
                    //写入日志
                    member_point_log("订单支付",$id,"支付".$data['order_id'].'订单使用',-$data['point']);
                }
                if($data['pointype'] == 2){
                    \think\Db::name('member')->where(array('id'=>$id))->setDec('money',$data['point']);
                    //写入日志
                    member_money_log("订单支付",$id,"支付".$data['order_id'].'订单使用',-$data['point']);
                }
            }
            $goods = json_decode($data['goods'],true);
            foreach ($goods as $key => $value) {
                //删除购物车
                $result = \think\Db::name('member_cart')->where(array('goods_id'=>$value['goods_id'],'product_id'=>$value['product_id'],'uid'=>$id))->delete();
                
                if(!$result){
                    //获取商品规格
                    if($value['product_id']){
                        \think\Db::name('goods_product')->where(array('id'=>$value['product_id']))->setDec('goods_number',$value['goods_number']); 
                        //修改后清除该商品缓存
                        \think\Cache::rm('goods_product_'.$value['goods_id']);
                    }
                    //减少商品库存
                    \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setDec('goods_number',$value['goods_number']); 
                    //修改后清除该商品缓存
                    \think\Cache::rm('goods_'.$value['goods_id']);
                }
            }

            //积分支付
            if($data['total_price'] == $data['point_price']){
                //获取订单信息
                $order = get_order($data['order_id'],$id);
                //订单分佣
                $goods = commision_order($order);
                //更新订单商品支付
                $param = array();
                $param['is_state'] = 2;
                $param['paytype'] = '券分支付';
                $param['paytime'] = time();
                $param['goods'] = json_encode($goods,JSON_UNESCAPED_UNICODE);
                \think\Db::name('member_order')->where(array('order_id'=>$data['order_id']))->update($param);
                //修改数据后清除缓存
                \think\Cache::rm($data['uid'].'_order_'.$data['order_id']);
                return json(array('success'=>true,'info'=>'支付成功！','order_id'=>$data['order_id']));exit;
            }

            //一分钱测试
            // $data['payprice'] = 0.01;

            if($data['paytype'] == '微信支付'){
                
                $appid = $weixin['wxappid']; //AppID
                $mchid = $weixin['openmchid']; //商户号
                $key = $weixin['openkey']; //商户支付密钥Key
                //支付金额单位是分所以得乘以100
                $price = $data['payprice'] * 100;
                //组装数据
                $param = array();
                $param['appid'] = $appid;
                $param['mch_id'] = $mchid;
                $param['nonce_str'] = $data['order_id'];
                $param['body'] = '订单支付';
                $param['out_trade_no'] = $data['order_id'];
                $param['total_fee'] = $price;
                $param['spbill_create_ip'] = request()->ip();
                $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay/';
                $param['trade_type'] = 'APP';
                //签名
                $param['sign'] = get_sign($param,$key);
                //生成xml并且生成签名
                $postXml = create_hongbao_xml($param);
                
                //提交请求
                $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
                $responseXml = curl_post_ssl($url,$postXml);

                $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
                //转换成数组
                $responseArr = ( array ) $responseObj;
                if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                    $list = array(
                        'appid'         =>  $param['appid'],
                        'partnerid'     =>  $param['mch_id'],
                        'prepayid'      =>  $responseArr['prepay_id'],
                        'package'       =>  'Sign=WXPay',
                        'noncestr'      =>  $param['nonce_str'],
                        'timestamp'     =>  time()
                    );
                    //签名
                    $list['sign'] = get_sign($list,$key);
                    $list['orderid'] = $data['order_id'];
                    return json(array('success'=>true,'info'=>'下单成功','list'=>$list,'order_id'=>$data['order_id']));
                }else{
                    return json(array('success'=>false,'info'=>$responseArr['return_msg']));
                }
            }else if($data['paytype'] == '支付宝支付'){
                
                require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
        
                $aop = new \AopClient();
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $aop->appId = $alipay['appid'];
                $aop->rsaPrivateKey = $alipay['privatekey'];
                $aop->format = "json";
                $aop->charset = "UTF-8";
                $aop->signType = "RSA2";
                $aop->alipayrsaPublicKey = $alipay['publickey'];
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $param = array();
                $param['body'] = '订单中心';
                $param['subject'] = '订单支付';
                $param['out_trade_no'] = $data['order_id'];
                $param['timeout_express'] = '30m';
                $param['total_amount'] = $data['payprice'];
                $param['product_code'] = 'QUICK_MSECURITY_PAY';

                $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
                $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay/');
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aop->sdkExecute($request);
                return json(array('success'=>true,'info'=>'下单成功','list'=>$response,'order_id'=>$data['order_id']));
            }else if($data['paytype'] == '银行卡支付'){
                //易宝支付
                require_once ("./extend/qm/lib/YopClient.php");
                require_once ("./extend/qm/lib/YopClient3.php");
                require_once ("./extend/qm/lib/Util/YopSignUtils.php");
                require_once ("./extend/qm/conf/conf.php");

                $request = new \YopRequest($merchantNo, $private_key, "https://open.yeepay.com/yop-center",$yop_public_key);
                
                $requestNo = randChar();
                //注册用户
                $request->addParam("requestNo", $requestNo);
                $request->addParam("merchantUserId", $requestNo);

                $response = \YopClient3::post("/rest/v1.0/payplus/user/register", $request);
                
                $merchantUserId = $requestNo;
                $returnUrl = 'http://'.$_SERVER['HTTP_HOST'].'/api/index/payment';


                //支付订单
                $order_id = $data['order_id'];
                $payprice = $data['payprice'];
                $payTool = 'SALESB2C';
                $bankCode = '001581044020';

                //加入请求参数
                $request->addParam("requestNo",$order_id);
                $request->addParam("merchantUserId", $merchantUserId); 
                $request->addParam("orderAmount", $payprice);
                $request->addParam("fundAmount", $payprice);
                $request->addParam("payTool", $payTool);
                $request->addParam("bindCardId", '');
                $request->addParam("merchantOrderDate", date('Y-m-d H:i:s'));
                $request->addParam("merchantExpireTime", ''); 
                $request->addParam("bankCode", $bankCode);
                $request->addParam("trxExtraInfo", '');
                $request->addParam("serverCallbackUrl", $returnUrl);
                $request->addParam("webCallbackUrl", $returnUrl);
                $request->addParam("mcc", '3101');
                $request->addParam("productCatalog", '3101'); 
                $request->addParam("productName", '订单中心');
                $request->addParam("productDesc", '订单支付');
                $request->addParam("openId", '');
                $request->addParam("templateType", 'APP');
                $request->addParam("couponNos", '');
                $request->addParam("marketingExtraInfo", ''); 
                $request->addParam("merchantBizType",'');
                $request->addParam("divideRuleType", '');
                $request->addParam("divideDetail",'');
                $request->addParam("divideCallbackUrl", '');
                $request->addParam("accountPayMerchantNo", '');
                $request->addParam("ip", request()->ip() );
                
                $response = \YopClient3::post("/rest/v1.0/payplus/order/consume", $request);

                // //取得返回结果
                $result = object_array($response);
                $url = $result['result']['redirectUrl'];
                return json(array('success'=>true,'info'=>'下单成功','list'=>$url,'order_id'=>$data['order_id']));
            }else{
                return json(array('success'=>false,'info'=>'请选择支付方式！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'信息有误，请重新提交！'));
        }
    }

    //获取订单详情
    public function order_detail(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        $order_id = input('post.id','');
        $list = get_order($order_id,$id);
        if($list){
            $list['create_time'] = date('Y-m-d H:i',$list['create_time']);
            $list['goods'] = json_decode($list['goods'],true);
            if ($list['goods']) {
                foreach($list['goods'] as $key => $val) {
                    $val['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['goods_logo'];
                    $list['goods'][$key] = $val;
                }
            }
            $list['return_image'] = json_decode($list['return_image'],true);
            if ($list['return_image']) {
                foreach($list['return_image'] as $key => $val) {
                    $val = 'http://'.$_SERVER['SERVER_NAME'].$val;
                    $list['return_image'][$key] = $val;
                }
            }
            $list['logistino'] = \think\Db::name('member_shipping')->where(array('order_id'=>$order_id))->value('logistino');
            $list['logistino'] = $list['logistino'] ? $list['logistino'] : '';
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //支付订单
    public function payorder(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','');
        //获取订单信息
        $order = get_order($order_id,$id);
        if(!$order){return json(array('success'=>false,'info'=>'订单不存在！'));} 
        if($order['is_state'] != 1){return json(array('success'=>false,'info'=>'订单已支付！'));} 
        //1分钱测试
        // $order['payprice'] = 0.01;

        //支付方式
        $paytype = input('post.paytype','');

        if($paytype == '微信支付'){
            //获取微信配置信息
            $weixin = get_payment('wxpay');
            if($weixin['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放微信支付功能！'));exit;
            }
            $appid = $weixin['wxappid']; //AppID
            $mchid = $weixin['openmchid']; //商户号
            $key = $weixin['openkey']; //商户支付密钥Key
            //支付金额单位是分所以得乘以100
            $price = $order['payprice'] * 100;
            //组装数据
            $param = array();
            $param['appid'] = $appid;
            $param['mch_id'] = $mchid;
            $param['nonce_str'] = $order['order_id'];
            $param['body'] = '订单支付';
            $param['out_trade_no'] = $order['order_id'];
            $param['total_fee'] = $price;
            $param['spbill_create_ip'] = request()->ip();
            $param['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/callback/wxpay/';
            $param['trade_type'] = 'APP';
            //签名
            $param['sign'] = get_sign($param,$key);
            //生成xml并且生成签名
            $postXml = create_hongbao_xml($param);
            
            //提交请求
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $responseXml = curl_post_ssl($url,$postXml);

            $responseObj = simplexml_load_string ( $responseXml, 'SimpleXMLElement', LIBXML_NOCDATA );
            //转换成数组
            $responseArr = ( array ) $responseObj;
            if($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == 'SUCCESS'){
                $list = array(
                    'appid'         =>  $param['appid'],
                    'partnerid'     =>  $param['mch_id'],
                    'prepayid'      =>  $responseArr['prepay_id'],
                    'package'       =>  'Sign=WXPay',
                    'noncestr'      =>  $param['nonce_str'],
                    'timestamp'     =>  time()
                );
                //签名
                $list['sign'] = get_sign($list,$key);
                $list['orderid'] = $order['order_id'];
                return json(array('success'=>true,'info'=>'','list'=>$list,'order_id'=>$order['order_id']));
            }else{
                return json(array('success'=>false,'info'=>$responseArr['return_msg']));
            }
        }else if($paytype == '支付宝支付'){
            //获取支付宝配置信息
            $alipay = get_payment('alipay');
            if($alipay['is_state'] == 2){
                return json(array('success'=>false,'info'=>'暂未开放支付宝支付功能！'));exit;
            }
            require_once('./extend/alipay-sdk-PHP-20171027120338/AopSdk.php');
    
            $aop = new \AopClient();
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = $alipay['appid'];
            $aop->rsaPrivateKey = $alipay['privatekey'];
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";
            $aop->alipayrsaPublicKey = $alipay['publickey'];
            //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
            $request = new \AlipayTradeAppPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $param = array();
            $param['body'] = '订单中心';
            $param['subject'] = '订单支付';
            $param['out_trade_no'] = $order['order_id'];
            $param['timeout_express'] = '30m';
            $param['total_amount'] = $order['payprice'];
            $param['product_code'] = 'QUICK_MSECURITY_PAY';

            $bizcontent = json_encode($param, JSON_UNESCAPED_UNICODE);
            $request->setNotifyUrl('http://'.$_SERVER['HTTP_HOST'].'/api/callback/alipay/');
            $request->setBizContent($bizcontent);
            //这里和普通的接口调用不同，使用的是sdkExecute
            $response = $aop->sdkExecute($request);
            return json(array('success'=>true,'info'=>'','list'=>$response,'order_id'=>$order['order_id']));
        }else{
            return json(array('success'=>false,'info'=>'请选择支付方式！'));
        }
    }

    //取消订单
    public function cancel_order(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','');
        $order = get_order($order_id,$id);
        if(!$order){return json(array('success'=>false,'info'=>'订单不存在！'));} 
        if($order['is_state'] != 1){return json(array('success'=>false,'info'=>'当前状态无法改变！'));} 

        if(\think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('is_state',6)){
            //使用优惠卷
            if($order['coupon']){
                \think\Db::name('coupon_list')->where(array('id'=>$order['coupon'],'uid'=>$id))->setField('is_state',1);
            }
            //使用积分抵扣
            if($order['point']){
                \think\Db::name('member')->where(array('id'=>$id))->setInc('point',$order['point']);
                //写入日志
                member_point_log("订单取消",$id,"取消".$order['order_id'].'订单返回',$order['point']);
            }
            $goods = json_decode($order['goods'],true);
            foreach ($goods as $key => $value) {
                //返回增加商品库存
                \think\Db::name('goods')->where(array('goods_id'=>$value['goods_id']))->setInc('goods_number', $value['goods_number']);
                //修改后清除该商品缓存
                \think\Cache::rm('goods_'.$value['goods_id']);
                //修改规格库存
                if($value['product_id']){
                    \think\Db::name('goods_product')->where(array('id'=>$value['product_id']))->setInc('goods_number', $value['goods_number']);
                    \think\Cache::rm('goods_product_'.$value['goods_id']);
                }
                //商品状态
                $value['is_state'] = 6;
                $goods[$key] = $value;
            }
            //修改订单商品状态
            $goods = json_encode($goods,JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('goods',$goods);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            return json(array('success'=>true,'info'=>'订单取消成功'));
        }else{
            return json(array('success'=>false,'info'=>'订单取消失败'));
        }
    }

    //订单确认收货
    public function finish_order(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.id','');
        //获取订单信息
        $order = get_order($order_id,$id);
        if(!$order){return json(array('success'=>false,'info'=>'订单不存在！'));} 
        // 验证订单是否已发货
        if(!in_array($order['is_state'], array('2','3'))){return json(array('success'=>false,'info'=>'当前状态无法改变！'));} 
        
        if(\think\Db::name('member_order')->where(array('order_id'=>$order_id))->setField('is_state',7)){
            $order['goods'] = json_decode($order['goods'],true);
            foreach ($order['goods'] as $key => $value) {
                //获取商品信息
                $goods = get_goods($value['goods_id']);
                $payprice = $goods['goods_price'];
                //判断商品打折
                if($goods['discount'] < 10){
                    $payprice = $goods['goods_price'] * $goods['discount'] / 10;
                }
                
                //商品状态
                $value['is_state'] = 7;
                $order['goods'][$key] = $value;
            }
            //修改订单商品状态
            $order['goods'] = json_encode($order['goods'],JSON_UNESCAPED_UNICODE);
            \think\Db::name('member_order')->where(array('order_id'=>$order['order_id']))->setField('goods',$order['goods']);
            //修改数据后清除缓存
            \think\Cache::rm($order['uid'].'_order_'.$order['order_id']);
            return json(array('success'=>true,'info'=>'修改成功'));
        }else{
            return json(array('success'=>false,'info'=>'修改失败'));
        }
    }

    //获取我的订单
    public function myorder(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}  

        // 清除过期订单自动完成订单信息
        clean_order();

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        $where = array();
        $where['uid'] = $id;
        
        $is_state = input('post.is_state','');
        if($is_state){
            $where['is_state'] = $is_state;
        }
        $list = \think\Db::name('member_order')->field('order_id')->where($where)->order("create_time desc")->limit($page,$limit)->select();
        if($list){
            foreach($list as $key => $value) {
                $value = get_order($value['order_id'],$id);
                $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                $value['goods'] = json_decode($value['goods'],true);
                if ($value['goods']) {
                    foreach($value['goods'] as $k => $val) {
                        $val['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['goods_logo'];
                        $value['goods'][$k] = $val;
                    }
                }
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //我的余额记录
    public function money(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['uid'] = $id;
        $type = input('post.type','');
        if($type){
            $where['type'] = $type;
        }

        $list = \think\Db::name('member_money_log')->field(true)->where($where)->order('create_time desc')->limit($page,$limit)->select();
        if($list){
            foreach ($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                if($value['money'] > 0){
                    $value['money'] = '+'.$value['money'];
                    $value['name'] = '新增';
                }else{
                    $value['name'] = '减少';
                }
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //我的余额记录
    public function busmoney(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['uid'] = $id;
        $type = input('post.type','');
        if($type){
            $where['type'] = $type;
        }

        $list = \think\Db::name('business_money_log')->field(true)->where($where)->order('create_time desc')->limit($page,$limit)->select();
        if($list){
            foreach ($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                if($value['money'] > 0){
                    $value['money'] = '+'.$value['money'];
                    $value['name'] = '新增';
                }else{
                    $value['name'] = '减少';
                }
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //商家收款记录
    public function bustransfer(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('business')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        //拼接条件
        $where = array();
        $where['uid'] = $id;
        $where['is_state'] = 2;

        $list = \think\Db::name('business_transfer')->field(true)->where($where)->order('create_time desc')->limit($page,$limit)->select();
        if($list){
            foreach ($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                $value['is_tatke'] = 1;
                if($value['paymoney'] < 120){
                    $value['is_tatke'] = 2;
                }
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

     //我的积分记录
    public function point(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        //分页
        $limit = input('post.limit','20');
        $page = input('post.page','1');
        $page = $page - 1;
        $page = $page * $limit;

        $list = \think\Db::name('member_point_log')->field(true)->where(array('uid'=>$id))->order('create_time desc')->limit($page,$limit)->select();
        if($list){
            foreach ($list as $key => $value) {
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
                if($value['point'] > 0){
                    $value['point'] = '+'.$value['point'];
                    $value['name'] = '新增';
                }else{
                    $value['name'] = '减少';
                }
                $list[$key] = $value;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //积分转账
    public function transfer_point(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $user = \think\Db::name('member')->field('paypassword,point,username')->where(array('appkey'=>$appkey))->find();
        //支付密码
        $password = input('post.password','');
        $password = password(sha1($password));
        if(!$user['paypassword']){return json(array('success'=>false,'info'=>'请先设置支付密码！'));} 
        if($user['paypassword'] != $password){return json(array('success'=>false,'info'=>'支付密码有误！'));} 

        //转账积分
        $point = input('post.point','');
        if($user['point'] < $point){return json(array('success'=>false,'info'=>'账户券分不足！'));} 

        //转账会员
        $mobile = input('post.mobile','');
        if(\think\Db::name('member')->where(array('username'=>$mobile))->setInc('point',$point)){
            $param = \think\Db::name('member')->field('id,username')->where(array('username'=>$mobile))->find();
            //写入日志
            member_point_log("会员转账",$param['id'],$user['username']."会员转账",$point);

            \think\Db::name('member')->where(array('id'=>$id))->setDec('point',$point);
            //写入日志
            member_point_log("会员转账",$id,"转账给".$param['username'],-$point);
            return json(array('success'=>true,'info'=>'转账成功'));
        }else{
            return json(array('success'=>false,'info'=>'转账失败'));
        }
    }

    //余额转账
    public function transfer_money(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $user = \think\Db::name('member')->field('paypassword,money,username')->where(array('appkey'=>$appkey))->find();
        //支付密码
        $password = input('post.password','');
        $password = password(sha1($password));
        if(!$user['paypassword']){return json(array('success'=>false,'info'=>'请先设置支付密码！'));} 
        if($user['paypassword'] != $password){return json(array('success'=>false,'info'=>'支付密码有误！'));} 

        //转账余额
        $money = input('post.money','');
        if($user['money'] < $money){return json(array('success'=>false,'info'=>'账户余额不足！'));} 

        //转账会员
        $mobile = input('post.mobile','');
        if(\think\Db::name('member')->where(array('username'=>$mobile))->setInc('money',$money)){
            $param = \think\Db::name('member')->field('id,username')->where(array('username'=>$mobile))->find();
            //写入日志
            member_money_log("会员转账",$param['id'],$user['username']."会员转账",$money);

            \think\Db::name('member')->where(array('id'=>$id))->setDec('money',$money);
            //写入日志
            member_money_log("会员转账",$id,"转账给".$param['username'],-$money);
            return json(array('success'=>true,'info'=>'转账成功'));
        }else{
            return json(array('success'=>false,'info'=>'转账失败'));
        }
    }

    //用户余额提现
    public function take_money(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $user = \think\Db::name('member')->field('paypassword,money,username,alipay')->where(array('appkey'=>$appkey))->find();
        //验证码验证
        $sendcode = \think\Cache::get('sendcode_'.$user['username']);
        if(!$sendcode){return json(array('success'=>false,'info'=>'请先获取手机验证码！'));exit;}
        //手机验证码
        $code = input('post.code','');
        if($code != $sendcode){return json(array('success'=>false,'info'=>'验证码错误！'));exit;}

        //转账账号
        if(!$user['alipay']){return json(array('success'=>false,'info'=>'请先绑定支付宝！'));} 
        //支付密码
        $password = input('post.password','');
        $password = password(sha1($password));
        if(!$user['paypassword']){return json(array('success'=>false,'info'=>'请先设置支付密码！'));} 
        if($user['paypassword'] != $password){return json(array('success'=>false,'info'=>'支付密码有误！'));} 

        //提现金额
        $money = input('post.money','');
        if($user['money'] < $money){return json(array('success'=>false,'info'=>'账户余额不足！'));} 
        // if(($user['money']-$money) < 100){return json(array('success'=>false,'info'=>'余额滞纳金不能低于100元！'));} 
        // if($money%100 != 0){return json(array('success'=>false,'info'=>'满100整数才能提现！'));} 
        
        if(\think\Db::name('member')->where(array('id'=>$id))->setDec('money',$money)){
            //获取用户设置信息
            $config = get_config('user');
            //写入日志
            member_money_log("会员提现",$id,"余额提现",-$money);
            //添加提现记录
            $data = array();
            $data['uid'] = $id;
            $data['money'] = $money;
            //手续费用
            $data['expenses'] = $money * $config['money'] / 100;
            $data['create_time'] = time();
            $data['alipay'] = $user['alipay'];
            \think\Db::name('member_take_money')->insert($data);
            //清除验证码缓存
            \think\Cache::rm('sendcode_'.$user['username']); 
            return json(array('success'=>true,'info'=>'提现成功，等待审核！'));
        }else{
            return json(array('success'=>false,'info'=>'提现失败'));
        }
    }

    //获取我的优惠券
    public function mycoupon(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        // 清除已过期的优惠券
        if (\think\Db::name('coupon_list')->where(array('is_state' => 1, 'end_time' => array('LT', time())))->count() > 0) {
            \think\Db::name('coupon_list')->where(array('is_state' => 1, 'end_time' => array('LT', time())))->setField('is_state', 3);
        }
            
        $list = \think\Db::name('coupon_list')->alias('c')->field('c.*,b.name busname')
                                                   ->join('__BUSINESS__ b','c.bus_id = b.id','LEFT')
                                                   ->where(array('c.uid'=>$id,'c.is_state'=>array('NEQ',5)))
                                                   ->select();
        if ($list) {
            foreach($list as $key => $val) {
                $val['start_time'] = date('Y/m/d', $val['start_time']);
                $val['end_time'] = date('Y/m/d', $val['end_time']);
                $list[$key] = $val;
            }
        }
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //删除我的优惠券
    public function delcoupon(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $cid = input('post.id','0');  
        if (\think\Db::name('coupon_list')->where(array('id'=>$cid,'is_state'=>array('NEQ',1)))->setField('is_state', 5)) {
            return json(array('success'=>true,'info'=>'删除成功'));
        }else{
            return json(array('success'=>false,'info'=>'该优惠券不能删除！'));
        }
    }

    //我的收藏
    public function mycollect(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}   

        $list = array();
        $list['goods'] = \think\Db::name('goods_collect')->alias('c')
                                         ->field('c.*,g.goods_name,g.goods_logo,g.goods_price,g.market_price,g.goods_salse')
                                         ->join('__GOODS__ g','c.goods_id = g.goods_id')
                                         ->where(array('c.uid'=>$id,'g.is_state'=>1))
                                         ->select();
        if ($list['goods']) {
            foreach($list['goods'] as $key => $val) {
                $val['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['goods_logo'];
                $list['goods'][$key] = $val;
            }
        }
        $list['business'] = \think\Db::name('business_collect')->alias('c')
                                         ->field('c.*,b.name,b.logo,b.remark')
                                         ->join('__BUSINESS__ b','c.bus_id = b.id')
                                         ->where(array('c.uid'=>$id,'b.is_state'=>1))
                                         ->select();
        if ($list['business']) {
            foreach($list['business'] as $key => $val) {
                $val['goods_salse'] = \think\Db::name('goods')->where(array('bus_id'=>$val['bus_id']))->sum('goods_salse');
                $val['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$val['logo'];
                $list['business'][$key] = $val;
            }
        }

        return json(array('success'=>true,'info'=>'','list'=>$list));
    }

    //获取关于我们基本信息
    public function about(){
        //获取平台设置信息
        $list = get_config('basic');
        $list['logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['logo'];
        return json(array('success'=>true,'info'=>'','list'=>$list));
    }
    
    //意见反馈
    public function feedback(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $data = array();
        $data['uid'] = $id;
        $data['remark'] = input('post.remark','');
        $data['mobile'] = input('post.mobile','');
        $data['create_time'] = time();
       
        if(\think\Db::name('member_comment')->insert($data)){
            return json(array('success'=>true,'info'=>'反馈成功'));
        }else{
            return json(array('success'=>false,'info'=>'反馈失败'));
        }
    }

    // 商家加盟
    public function enter(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $level_id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('level_id');
        if($level_id < 2){return json(array('success'=>false,'info'=>'VIP会员才能申请商家加盟！'));} 


        //验证会员appkey获取id
        $pid = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('pid');
        if(!$pid || \think\Db::name('member')->where(array('id'=>$pid))->count() < 1){
            return json(array('success'=>false,'info'=>'推荐人信息有误！'));
        }
        

        $data = array();
        $data['uid'] = $id;
        $data['pid'] = $pid;
        $data['username'] = input('post.mobile','');
        //用户密码
        $password = input('post.password','');
        if(strlen($password)<6 || strlen($password)>18){
            return json(array('success'=>false,'info'=>'密码请在6-18位数之间！'));exit;
        }
        $data['password'] = password(sha1($password));                  

        $data['name'] = input('post.name','');
        if(!$data['name']){
            return json(array('success'=>false,'info'=>'请输入商家名称！'));exit;
        }
        if(\think\Db::name('business')->where(array('username'=>$data['username']))->count() > 0){
            return json(array('success'=>false,'info'=>'商家账号已存在，请更换！'));exit;
        }

        $data['truename'] = input('post.truename','');
        if(!$data['truename']){return json(array('success'=>false,'info'=>'请输入真实名称！'));exit;}
        $data['alipay'] = input('post.alipay','');
        if(!$data['alipay']){return json(array('success'=>false,'info'=>'请输入支付宝账号！'));exit;}
        $data['mobile'] = input('post.mobile','');
        if(!$data['mobile']){return json(array('success'=>false,'info'=>'请输入联系手机！'));exit;}
        $data['address'] = input('post.address','');
        if(!$data['address']){return json(array('success'=>false,'info'=>'请输入详细地址！'));exit;}

        $data['city'] = '广东省-广州市-白云区';
        $data['cityid'] = '695';

        //上传文件
        $file = input('post.license','');
        if(!$file || $file== '../image/a11.png'){return json(array('success'=>false,'info'=>'请上传证明材料！'));exit;}
        //处理base64编码的图片上传
        if(strpos($file, 'data:image') !== false){
            //创建文件夹
            if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
            }
            //保存文件路径
            $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)){
                $type = $result[2];
                if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $file)), FILE_USE_INCLUDE_PATH)) {
                        return json(array('success'=>false,'info'=>'图片上传失败！'));
                    }else{
                        $data['license'] = $path;
                    }
                }else{
                    //文件类型错误
                    return json(array('success'=>false,'info'=>'图片上传类型错误！'));
                }
            }else{
                //文件错误
                return json(array('success'=>false,'info'=>'文件错误！'));
            }
        }
        $data['type'] = input('post.type','');
        // if($data['type'] == 1){
        //     $data['is_state'] = 1;
        // }else{
            // $data['is_state'] = 3;
        // }
        $data['is_state'] = 3;
        $data['is_xian'] = 1;
        $data['logo'] = '/public/image/image.jpg';
        //商家创建时间                  
        $data['create_time'] = time();  
        //添加商家
        if($bus_id = \think\Db::name('business')->insertGetId($data)){
            //清除Api获取商家相关接口缓存
            \think\Cache::clear('business');
            return json(array('success'=>true,'info'=>'商家加盟申请成功！'));
        }else {
            //获取添加错误原因
            return json(array('success'=>false,'info'=>'商家加盟申请失败！'));
        }
    }

    // 商品发布
    public function release(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $list = get_member($id);

        $data = array();
        $data['bus_id'] = $list['bus_id'];

        $data['goods_name'] = input('post.goods_name','');
        if(!$data['goods_name']){
            return json(array('success'=>false,'info'=>'请输入商品名称！'));exit;
        }

        $data['goods_price'] = input('post.goods_price','');
        if(!$data['goods_price']){return json(array('success'=>false,'info'=>'请输入商品价格！'));exit;}

        $data['discount'] = input('post.discount','');
        $data['discount'] = $data['discount'] ? $data['discount'] : 10;

        $data['market_price'] = input('post.market_price','');
        if(!$data['market_price']){return json(array('success'=>false,'info'=>'请输入商品市场价！'));exit;}
        $data['goods_number'] = input('post.goods_number','');
        if(!$data['goods_number']){return json(array('success'=>false,'info'=>'请输入商品库存！'));exit;}

        //上传文件
        $file = input('post.goods_logo','');
        //处理base64编码的图片上传
        if(strpos($file, 'data:image') !== false){
            //创建文件夹
            if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d'))){ 
                mkdir($_SERVER['DOCUMENT_ROOT'].'/public/upload/'.date('Y-m-d')); 
            }
            //保存文件路径
            $path = "/public/upload/".date('Y-m-d').'/'.randChar().'.jpg';
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)){
                $type = $result[2];
                if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'].$path,base64_decode(str_replace($result[1], '', $file)), FILE_USE_INCLUDE_PATH)) {
                        return json(array('success'=>false,'info'=>'图片上传失败！'));
                    }else{
                        // 按照原图的比例生成一个最大为200*200的缩略图并保存
                        $image = \think\Image::open($_SERVER['DOCUMENT_ROOT'].$path);
                        $image->thumb(800, 800)->save($_SERVER['DOCUMENT_ROOT'].$path);
                        $data['goods_logo'] = $path;
                    }
                }else{
                    //文件类型错误
                    return json(array('success'=>false,'info'=>'图片上传类型错误！'));
                }
            }else{
                //文件错误
                return json(array('success'=>false,'info'=>'文件错误！'));
            }
        }else{
            return json(array('success'=>false,'info'=>'请上传商品图片！'));
        }
        $data['goods_image'] = json_encode(array($data['goods_logo']),JSON_UNESCAPED_UNICODE);
        //随机生成商品货号
        $data['goods_sn'] = strtoupper(uniqid(8));
        //创建时间                  
        $data['create_time'] = time();  
        $data['typeid'] = 5;
        $data['is_state'] = 3;
        $data['is_xian'] = 1;
        //执行添加商品
        if($goods_id = \think\Db::name('goods')->insertGetId($data)){
            
            //清除Api获取商品相关接口缓存
            \think\Cache::clear('goods');
            return json(array('success'=>true,'info'=>'商品发布成功！'));exit;
        }else {
            return json(array('success'=>false,'info'=>'商品发布失败！'));exit;
        }
    }

    //获取手机定位
    public function getlocation(){
        //验证会员appkey获取id
        $city = input('post.city','');
        $ak = 'rhmjONnqWpur81WbSR3iskq6cUDxBbDY';
        
        $list = array();
        if($city){
            //获取本机
            $content = file_get_contents("http://api.map.baidu.com/geocoder/v2/?address=".$city."&output=json&ak=".$ak);

            $result = json_decode($content,true);
            $list['longitude'] = $result['result']['location']['lng'];
            $list['latitude'] = $result['result']['location']['lat'];
            $list['city'] = $city;
        }else{

        }
        return json($list);
    }

    //获取订单支付状态
    public function getstate(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));} 

        $order_id = input('post.order_id','');
        $state = \think\Db::name('member_order')->where(array('order_id'=>$order_id))->value('is_state');

        return json(array('success'=>true,'info'=>'','list'=>$state));
    }

    public function agreement(){
        //验证会员appkey获取id
        $appkey = input('post.appkey','appkey');
        $id = \think\Db::name('member')->where(array('appkey'=>$appkey))->value('id');
        if(!$id){return json(array('success'=>false,'info'=>'登陆异常，请重新登陆！'));}

        $data = array();
        $data['username'] = input('post.username','');
        $data['address'] = input('post.address','');
        $data['mobile'] = input('post.mobile','');
        $data['city'] = input('post.city','');
        $data['idcard'] = input('post.idcard','');
        $data['create_time'] = time();
        $data['is_state'] = 3; 
        $data['uid'] = $id;
        if(\think\Db::name("agreement")->insert($data)){
            return json(array('success'=>true,'info'=>'提交成功'));
        }else{
            return json(array('success'=>false,'info'=>'信息有误！'));
        }
    }

}
