<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:56:"F:\web\www\nlsm./application/index\view\index\index.html";i:1538304655;}*/ ?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no,
    minimal-ui"/>
    <meta name="format-detection" content="telephone=no, email=no, date=no, address=no">
    <title>呗尔商城</title>
    <link rel="stylesheet" href="./css/body.css">
    <link rel="stylesheet" href="./css/swiper-4.1.0.min.css">
    <link rel="stylesheet" href="./css/goods.css">
</head>

<body>
<!-- <div id='loading'><img src='./image/loading.png' alt=''></div>
<div id="top"></div>
<div class="swiper-welcome hide">
    <div class="swiper-wrapper">
        <div class='swiper-slide' style='background:url(./image/welcome1.jpg) no-repeat center center / cover'></div>
        <div class='swiper-slide' style='background:url(./image/welcome2.jpg) no-repeat center center / cover'></div>
        <div class='swiper-slide' style='background:url(./image/welcome3.jpg) no-repeat center center / cover' onclick="removeWelcome()"></div>
    </div>
    <div class="swiper-pagination"></div>
</div> -->

<!-- <div class='header'>
    <div>
        <span id="city" onclick='tohtml("city.html");'>&nbsp;&nbsp;广州</span>
        <div class="right" tapmode="highlight" onclick="search();">搜索</div>
        <div class="middle">
            <img src="./image/header/search_groupbuy.png">
            <input type="text" id="keyword" placeholder="请输入搜索内容" />
        </div>
    </div>
</div> -->

<!--banner图开始-->
<div class="banner" id="banner">
    <div class="bannerlist">
        <ul><div style="height: 150px;"></div></ul>
    </div>
    <div class="tablist">
        <ul></ul>
    </div>
</div>
<!--banner图结束-->

<!-- <div id='comment'>
    <em></em>
    <ul class="header_notice_right home-msg open-win" win='message'  param='{"type":1}'></ul>
</div> -->

<!--首页菜单一 开始-->
<!-- <nav class="h-menu-b ">
    <ul class="swiper-wrapper">
      <div class='swiper-slide'>
        <li  onclick='tohtml("home.html");'><img src="./image/1-1.png" alt=""/><sapn class="ti">阳光商城</sapn></li>
        <li  style="clear:both;" onclick='tohtml("goods_list.html?typeid=100");'><img src="./image/1-6.png" alt=""/><sapn class="ti">活动专区</sapn></li>
      </div>
      <div class='swiper-slide'>
        <li  onclick='tohtml("business.html");'><img src="./image/1-2.png" alt=""/><sapn class="ti">商业街</sapn></li>
        <li  onclick='tohtml("goods_list.html?typeid=101");'><img src="./image/1-7.png" alt=""/><sapn class="ti">品牌专区</sapn></li>
      </div>
      <div class='swiper-slide'>
        <li  onclick='tohtml("goods_list.html?typeid=94");'><img src="./image/1-3.png" alt=""/><sapn class="ti">日用百货</sapn></li>
        <li  onclick='tohtml("goods_list.html?typeid=47");'><img src="./image/1-8.png" alt=""/><sapn class="ti">居家用品</sapn></li>
      </div>
      <div class='swiper-slide'>
        <li  onclick='tohtml("goods_list.html?typeid=2");'><img src="./image/1-4.png" alt=""/><sapn class="ti">美食城</sapn></li>
        <li  onclick='tohtml("goods_list.html?typeid=26");'><img src="./image/1-9.png" alt=""/><sapn class="ti">地方特产</sapn></li>
      </div>
      <div class='swiper-slide'>
        <li  onclick='tohtml("goods_list.html?typeid=3");'><img src="./image/1-5.png" alt=""/><sapn class="ti">家电数码</sapn></li>
        <li  onclick=''><img src="./image/1-10.png" alt=""/><sapn class="ti">快递中心</sapn></li>
      </div>

    </ul>
</nav> -->
<!--首页菜单一  结束-->

<!--券分兑商品 开始-->
<!-- <section class="integral">
    <div class="floor">
        <div class="floor_item">
            <div class="con_1"></div>
        </div>
        <div class="floor_item">
           <div class="con_2"></div>
        </div>
        <div class="floor_item">
            <div class="con_3">
               <span class="col_l"></span>
               <span class="col_r"></span>
            </div>
        </div>
    </div>
</section> -->
<!--券分兑商品 结束-->

<h2 class='title_details'>猜你喜欢<span id="go_goods_list">您的专属推荐产品>></span></h2>
<div id='goods'>
    <div>

        <div class="swiper-container_indexgoods">
            <ul class="swiper-wrapper">

            </ul>
        </div>
    </div>
</div>

<div id='goodsList'>

    <div id='content'>
        <div>
            <ul>

            </ul>
        </div>
        <div class="null"><span>已经到底了...</span></div>
    </div>
  <div class="row footer">
    <div style="text-align: center;" onclick="foot();">&copy; 1996-2018 <a href="http://www.miitbeian.gov.cn" target="_blank">粤ICP备18075220号</a></div>
</div>
</div>


<!--底部-->
<!-- div style="width:100%;height:57px;"></div>
<ul class='nav'>
    <li><a class="selected1"><em></em><span>首页</span></a></li>
    <li><a><em></em><span>分类</span></a></li>
    <li><a><em></em><span>劵分区</span></a></li>
    <li><a><em></em><span>购物车</span></a></li>
    <li><a><em></em><span>我的</span></a></li>
</ul> -->
<!--底部 END-->
</body>

<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript" src="./js/touchslide.1.1.js"></script>
<script type="text/javascript" src="./js/swiper-4.1.0.min.js"></script>
<script type="text/javascript" src="./js/layer/layer.min.js"></script>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=7zIY0Lm4gVhSId4u9PQ9kl6okzfao2N3"></script>
<script>
    //获取坐标
    // getlocation();

    // //获取手机定位
    // function getlocation(){

    //    //  if(localStorage.city == undefined ||　localStorage.city == '本机') {
    //    //      //百度地图获取坐标
    //    //      var geolocation = new BMap.Geolocation();
    //    //      var pt;
    //    //      geolocation.getCurrentPosition(function (r) {
    //    //          if (this.getStatus() == BMAP_STATUS_SUCCESS) {

    //    //              localStorage.longitude = r.point.lng;
    //    //              localStorage.latitude = r.point.lat; 
    //    //          }
    //    //      });
          
    //    // }else{
    //    //      $.post(serverurl+'/api/app/getlocation',{'city':localStorage.city,'token':token},function(data){

    //    //          localStorage.longitude = data.longitude;
    //    //          localStorage.latitude = data.latitude;  
    //    //          $("#city").html(data.city);

    //    //      },"json");
    //    // }
    // }

    var page = 1;
    var iscroll = 1;

    $(function(){

      var swiper = new Swiper('.swiper-container', {
        slidesPerView: 5,
        slidesPerGroup: 5,
        loopFillGroupWithBlank: true,
      });

        //首次加载动画
        if(localStorage.Welcome == 'yes'){
            $('.swiper-welcome').remove();
        }else{
            $('.swiper-welcome').show();

            var mySwiper = new Swiper ('.swiper-welcome', {
                preventLinksPropagation : false,
                pagination: {
                  el: '.swiper-pagination',
                },
                resistanceRatio : 0
            })
        }

        // 获取banner图
        $.post(serverurl+'/api/app/index_banner',{'token':token},function(data){
            if(data.list){
                //顶部轮播图
                var html = '';
                for(x in data.list.header){
                    html = html + '<li><img onclick="tohtml(\'helpdetail.html?id='+data.list.header[x].url+'\');" class="lazy" src="'+data.list.header[x].logo+'"></li>';
                }
                $('.bannerlist ul').html(html);
                //顶部轮播图特效
                TouchSlide({
                    slideCell:"#banner",
                    titCell:".tablist ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
                    mainCell:".bannerlist ul",
                    effect:"leftLoop",
                    autoPage:true,//自动分页
                    autoPlay:true //自动播放
                });
                //中部广告图
                // var html = '';
                // for(x in data.list.center){
                //     html = html + '<img onclick="tohtml(\'goods.html?id='+data.list.center[x].url+'\');" src="'+data.list.center[x].logo+'">';
                // }
                // $('.con_1').html(html);
                // //下部广告图
                // var html = '';
                // for(x in data.list.footer){
                //     html = html + '<a onclick="tohtml(\'goods.html?id='+data.list.footer[x].url+'\');"><img src="'+data.list.footer[x].logo+'"></a>';
                // }
                // $('.con_2').html(html);
                // //底部广告图
                // var html1 = '';
                // var html2 = '';
                // for(x in data.list.bottom){
                //     if(x == 0){
                //         html1 = '<a onclick="tohtml(\'goods.html?id='+data.list.bottom[x].url+'\');"><img src="'+data.list.bottom[x].logo+'"></a>';
                //     }else{
                //         html2 = html2 + '<a onclick="tohtml(\'goods.html?id='+data.list.bottom[x].url+'\');"><img src="'+data.list.bottom[x].logo+'"></a>';
                //     }
                // }
                // $('.col_l').html(html1);
                // $('.col_r').html(html2);
            }else{
                layer.msg(data.info);
            }
        },"json");

        //获取滚动公告信息
        $.post(serverurl+'/api/app/help',{'token':token,'typeid':2},function(data){
            if(data.list){
                var html = '';
                for(x in data.list){
                    html = html + "<li><p>"+data.list[x].title+"</p></li>";
                }
                $('.header_notice_right').html(html);
                //消息滚动
                var s =  $('.header_notice_right li').height();
                var timer;
                var num = 0;
                timer=setInterval(function(){
                    num++;
                    if(num>=$('.header_notice_right li').length){
                        num=0;
                        $('.header_notice_right li').eq(0).animate({
                            'marginTop':0
                        },0);
                    }
                    $('.header_notice_right li').eq(0).animate({
                        'marginTop':-(num*s)
                    },500);
                },3000);
            }else{
                layer.msg(data.info);
            }
        },"json");

        //获取今日特价商品
        $.post(serverurl+'/api/app/index_goods',{'token':token,'typeid':2},function(data){
            $("#loading").hide();
            if(data.list){
                var html = '';
                for(x in data.list){
                    html = html + "<li class='swiper-slide' onclick='tohtml(\"goods.html?id="+data.list[x].goods_id+"\");'><em style='background:url("+data.list[x].goods_logo+") no-repeat center center / contain #fff'></em><h4>"+data.list[x].goods_name+"</h4><b>￥"+data.list[x].market_price+"</b><strong>￥"+data.list[x].goods_price+"</strong></li>";
                }
                $('#goods ul').html(html);
                //滑动效果
                var mySwiper = new Swiper ('.swiper-container_indexgoods', {
                    slidesPerView:3,
                    spaceBetween:10
                })
            }else{
                layer.msg(data.info);
            }
        },"json");

        getGoodsList();
        
        $(window).scroll(
            function() {
            var scrollTop = $(this).scrollTop();
            var scrollHeight = $(document).height();
            var windowHeight = $(this).height();

            if (scrollTop + windowHeight +10 >= scrollHeight) {
                // 此处是滚动条到底部时候触发的事件，在这里写要加载的数据，或者是拉动滚动条的操作
                page = page + 1;
                getGoodsList();

                
                $('#goodsList #content').css('overflow-y','scroll');
                //滚动到底部
                $('#goodsList #content').scroll(function(){
                　　var scrollTop = $(this).scrollTop();
                　　var scrollHeight = $('#goodsList #content ul').height();
                　　var windowHeight = $(this).height();
                　　if( Math.ceil(scrollTop + windowHeight)+10 >= scrollHeight && iscroll == 1){
                        //查询下一页数据
                        page = page + 1;
                        getGoodsList();
                　　}
                });

            }
        });

    })
    //删除首次加载动画
    function removeWelcome(){
        $('.swiper-welcome').fadeOut(300,function(){
            $('.swiper-welcome').remove();
            localStorage.Welcome = 'yes';
        });
    }

    //获取商品列表
    function getGoodsList(field,sort){
        iscroll = 2;

        var typeid = geturldata('typeid');
        var field = field ? field : 'sort';
        var sort = sort ? 'asc' : 'desc';
        var keyword = geturldata('keyword');;
        $("#loading").show();
        $.post(serverurl+'/api/app/goods_list',{'typeid':typeid,'field':field,'sort':sort,'keyword':keyword,'page':page,'limit':6,'token':token},function(data){
            $("#loading").hide();
            if(data.list.length>0){
                iscroll = 1;
                var html = '';
                for(x in data.list){
                    html = html + "<li onclick='tohtml(\"goods.html?id="+data.list[x].goods_id+"\");'>  \
                                        <em style='background:url("+data.list[x].goods_logo+") no-repeat center center / contain'></em>\
                                        <h4>"+data.list[x].goods_name+"</h4>\
                                        <b>￥"+data.list[x].goods_price+"</b><i>￥"+data.list[x].market_price+"</i>\
                                        <span>已售"+data.list[x].goods_salse+"件</span>\
                                    </li>"
                }
                $('#content ul').append(html);

            }else{
               $('#content .null').show();
            }
        },"json");
    }

    //搜索
    function search() {
        var keyword = $('#keyword').val();
        if(keyword.length > 0){
            api.openWin({
                name: 'goods_list',
                reload:true,
                url: './goods_list.html?keyword='+keyword
            });
        }else{
            layer.msg('请输入搜索关键词');
        }
    };
     // setTimeout(function(){
     //                    window.location.href="http://www.miitbeian.gov.cn";
     //                },1000);
</script>
</html>
