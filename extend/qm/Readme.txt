当前版本:V1.0.0  生成时间:2017-09-02 12:47:10
===========================

###########环境依赖
php v5.5+
安装php的mcrypt扩展 openssl

###########部署步骤
1. 添加php的mcrypt扩展 openssl


###########目录结构描述
|
├── lib        // 函数库
│    ├── YopClient.php               // 对称秘钥请求处理函数集
│    ├── YopClient3.php              // 非对称秘钥请求处理函数集
│    ├── YopConfig.php               // YOP配置信息函数集
│    ├── YopRequest.php              // YOP请求处理函数集
│    ├── YopResponse.php             // YOP返回处理函数集
│    ├──  Util            
│          ├── AESEncrypter.php          // AES函数集
│          ├── Base64Url.php             // Base64Url函数集
│          ├── BlowfishEncrypter.php     // 加解密处理
│          ├── HttpRequest.php           // Http请求函数集
│          ├── HttpUtils.php             // Http处理共通函数集
│          ├── StringBuilder.php         // 字符串创建函数集
│          ├── StringUtils               // 字符串处理函数集
│          └── YopSignUtils.php          // YOP签名共通函数集
├── conf        // 配置
│    ├── conf.php                    // 商户商编、密钥对配置文件
├── Readme.txt                  // help
├── index.php                   // demo样例首页
├── merchant                    // 入网相关接口测试页，仅供参考
├── user                        // 用户相关接口测试页，仅供参考
├── basicData                   // 基础数据相关接口测试页，仅供参考
├── tranSystem                  // 交易相关接口测试页，仅供参考
├── callback                    // 服务器回调测试页，仅供参考
###########V1.0.0 版本内容更新
1. 待更新






