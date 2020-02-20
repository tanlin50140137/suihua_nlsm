/*

 @Name: layui WebIM 1.0.0
 @Author：贤心
 @Date: 2014-04-25
 @Blog: http://sentsin.com
 
 */
 
;!function(win, undefined){

var aName = $('#username').attr('info');
var apic = $('#div').attr('info');

var config = {
    msgurl: 'mailbox.html?msg=',
    chatlogurl: 'mailbox.html?user=',
    aniTime: 200,
    right: -232,
    api: {
        friend: 'friend', //好友列表接口
        group: 'group', //群组列表接口 
        chatlog: 'chatlog', //聊天记录接口
        groups: 'groups', //群组成员接口
        sendurl: 'send' //发送消息接口
    },
    user: { //当前用户信息
        name: aName,
        face: '/Public/'+apic
    },
    
    
    chating: {},
    hosts: (function(){
        var dk = location.href.match(/\:\d+/);
        dk = dk ? dk[0] : '';
        return 'http://' + document.domain + dk + '/';
    })(),
    json: function(url, data, callback, error){
        return $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: callback,
            error: error
        });
    },
    stopMP: function(e){
        e ? e.stopPropagation() : e.cancelBubble = true;
    }
}, dom = [$(window), $(document), $('html'), $('body')], xxim = {};

//主界面tab
xxim.tabs = function(index){
    var node = xxim.node;
    node.tabs.eq(index).addClass('xxim_tabnow').siblings().removeClass('xxim_tabnow');
    node.list.eq(index).show().siblings('.xxim_list').hide();
    if(node.list.eq(index).find('li').length === 0){
        xxim.getDates(index);
    }
};

//节点
xxim.renode = function(){
    var node = xxim.node = {
        tabs: $('#xxim_tabs>span'),
        list: $('.xxim_list'),
        online: $('.xxim_online'),
        setonline: $('.xxim_setonline'),
        onlinetex: $('#xxim_onlinetex'),
        xximon: $('#xxim_on'),
        layimFooter: $('#xxim_bottom'),
        xximHide: $('#xxim_hide'),
        xximSearch: $('#xxim_searchkey'),
        searchMian: $('#xxim_searchmain'),
        closeSearch: $('#xxim_closesearch'),
        layimMin: $('#layim_min')
    }; 
};

//主界面缩放
xxim.expend = function(){
    var node = xxim.node;
    if(xxim.layimNode.attr('state') !== '1'){
        xxim.layimNode.stop().animate({right: config.right}, config.aniTime, function(){
            node.xximon.addClass('xxim_off');
            try{
                localStorage.layimState = 1;
            }catch(e){}
            xxim.layimNode.attr({state: 1});
            node.layimFooter.addClass('xxim_expend').stop().animate({marginLeft: config.right}, config.aniTime/2);
            node.xximHide.addClass('xxim_show');
        });
    } else {
        xxim.layimNode.stop().animate({right: 1}, config.aniTime, function(){
            node.xximon.removeClass('xxim_off');
            try{
                localStorage.layimState = 2;
            }catch(e){}
            xxim.layimNode.removeAttr('state');
            node.layimFooter.removeClass('xxim_expend');
            node.xximHide.removeClass('xxim_show');
        });
        node.layimFooter.stop().animate({marginLeft: 0}, config.aniTime);
    }
};

//初始化窗口格局
xxim.layinit = function(){
    var node = xxim.node;
    
    //主界面
    try{
        /*
        if(!localStorage.layimState){       
            config.aniTime = 0;
            localStorage.layimState = 1;
        }
        */
        if(localStorage.layimState === '1'){
            xxim.layimNode.attr({state: 1}).css({right: config.right});
            node.xximon.addClass('xxim_off');
            node.layimFooter.addClass('xxim_expend').css({marginLeft: config.right});
            node.xximHide.addClass('xxim_show');
        }
    }catch(e){
        //layer.msg(e.message, 5, -1);
    }
};
chatchange = '';


//聊天窗口
xxim.popchat = function(param){

    var node = xxim.node, log = {};
    
    log.success = function(layero){
        layer.setMove();
        xxim.chatbox = layero.find('#layim_chatbox');
        log.chatlist = xxim.chatbox.find('.layim_chatmore>ul');
        
        log.chatlist.html('<li data-id="'+ param.id +'" type="'+ param.type +'"  id="layim_user'+ param.type + param.id +'"><span>'+ param.name +'</span><em>×</em></li>')
        xxim.tabchat(param, xxim.chatbox);
        
        //最小化聊天窗
        xxim.chatbox.find('.layer_setmin').on('click', function(){
            var indexs = layero.attr('times');
            layero.hide();
            node.layimMin.text(xxim.nowchat.name).show();
        });
        
        //关闭窗口
        xxim.chatbox.find('.layim_close').on('click', function(){
            // alert(1);
            clearInterval(change);
            var indexs = layero.attr('times');
            layer.close(indexs);
            xxim.chatbox = null;
            config.chating = {};
            config.chatings = 0;
        });
        
        //关闭某个聊天
        log.chatlist.on('mouseenter', 'li', function(){
            clearInterval(change);
            $(this).find('em').show();
        }).on('mouseleave', 'li', function(){
            $(this).find('em').hide();
        });
        log.chatlist.on('click', 'li em', function(e){
            var parents = $(this).parent(), dataType = parents.attr('type');
            var dataId = parents.attr('data-id'), index = parents.index();
            var chatlist = log.chatlist.find('li'), indexs;
            
            config.stopMP(e);
            
            delete config.chating[dataType + dataId];
            config.chatings--;
            
            parents.remove();
            $('#layim_area'+ dataType + dataId).remove();
            if(dataType === 'group'){
                $('#layim_group'+ dataType + dataId).remove();
            }
            
            if(parents.hasClass('layim_chatnow')){
                if(index === config.chatings){
                    indexs = index - 1;
                } else {
                    indexs = index + 1;
                }
                xxim.tabchat(config.chating[chatlist.eq(indexs).attr('type') + chatlist.eq(indexs).attr('data-id')]);
            }
            
            if(log.chatlist.find('li').length === 1){
                log.chatlist.parent().hide();
            } 
        });
        
        //聊天选项卡
        log.chatlist.on('click', 'li', function(){

                clearInterval(change);

            if(chatchange!==''){

                 clearInterval(chatchange);
            }

            var othis = $(this), dataType = othis.attr('type'), dataId = othis.attr('data-id');
            xxim.tabchat(config.chating[dataType + dataId]);
            var fname = othis.find('span').html();
            var bid = othis.attr('data-id');
            keys = 'one'+bid;

        nowFname = fname;
        chatchange = setInterval(function(){

             /*调用doWebim函数*/
             doWebim(keys);
               
        },1000);

           
        });
        
        //发送热键切换
        log.sendType = $('#layim_sendtype'), log.sendTypes = log.sendType.find('span');
        $('#layim_enter').on('click', function(e){
            config.stopMP(e);
            log.sendType.show();
        });
        log.sendTypes.on('click', function(){
            log.sendTypes.find('i').text('')
            $(this).find('i').text('√');
        });
        
        xxim.transmit();
    };
        faceimg = '';
       for(i=0;i<60;i++){  //通过循环创建60个表情，可扩展
         faceimg+='<li><a onclick="doInsert(this);"><img src="/static/plugins/layer/face/'+(i+1)+'.gif" face="<emt>'+(i+1)+'.gif</emt>"/ ></a></li>';
        };
    log.html = '<div class="layim_chatbox" id="layim_chatbox">'
            +'<h6>'
            +'<span class="layim_move"></span>'
            +'    <a href="'+ param.url +'" class="layim_face" target="_blank"><img src="'+ param.face +'" ></a>'
            +'    <a href="'+ param.url +'" class="layim_names" target="_blank">'+ param.name +'</a>'
            +'    <span class="layim_rightbtn">'
            +'        <i class="layer_setmin">—</i>'
            +'        <i class="layim_close">&times;</i>'
            +'    </span>'
            +'</h6>'
            +'<div class="layim_chatmore" id="layim_chatmore">'
            +'    <ul class="layim_chatlist"></ul>'
            +'</div>'
            +'<div class="layim_groups" id="layim_groups"></div>'
            +'<div class="layim_chat" id="layim">'
            +'    <div class="layim_chatarea" id="layim_chatarea">'
            +'        <ul class="layim_chatview layim_chatthis"  id="layim_area'+ param.type + param.id +'"></ul>'
            +'    </div>'
            +'    <div class="layim_tool">'
            +'<div id="SmohanFaceBox"><div class="Content"><h3><span>常用表情</span><a class="close” title="关闭" onclick="showFace()"></a></h3><ul>'+faceimg+'</ul></div></div>'
            +'        <i class="layim_addface fa fa-meh-o" title="发送表情" id="expression" onclick="showFace()"></i>'
            +'        '
            +'       '
            +'        '
            +'    </div>'
            +'    <textarea class="layim_write" id="layim_write"></textarea>'
            +'    <div class="layim_send">'
            +'        <div class="layim_sendbtn" id="layim_sendbtn">发送<span class="layim_enter" id="layim_enter"><em class="layim_zero"></em></span></div>'
            +'        <div class="layim_sendtype" id="layim_sendtype">'
            +'            <span><i>√</i>按Enter键发送</span>'
            +'            <span><i></i>按Ctrl+Enter键发送</span>'
            +'        </div>'
            +'    </div>'
            +'</div>'
            +'</div>';

    if(config.chatings < 1){
        $.layer({
            type: 1,
            border: [0],
            title: false,
            shade: [0],
            area: ['620px', '493px'],
            move: '.layim_chatbox .layim_move',
            moveType: 1,
            closeBtn: false,
            offset: [(($(window).height() - 493)/2)+'px', ''],
            page: {
                html: log.html
            }, success: function(layero){
                log.success(layero);
            }
        })
    } else {
        log.chatmore = xxim.chatbox.find('#layim_chatmore');
        log.chatarea = xxim.chatbox.find('#layim_chatarea');
        
        log.chatmore.show();
        
        log.chatmore.find('ul>li').removeClass('layim_chatnow');
        log.chatmore.find('ul').append('<li data-id="'+ param.id +'" type="'+ param.type +'" id="layim_user'+ param.type + param.id +'" class="layim_chatnow"><span>'+ param.name +'</span><em>×</em></li>');
        
        log.chatarea.find('.layim_chatview').removeClass('layim_chatthis');
        log.chatarea.append('<ul class="layim_chatview layim_chatthis" id="layim_area'+ param.type + param.id +'"></ul>');
        
        xxim.tabchat(param);
    }
    
    //群组
    log.chatgroup = xxim.chatbox.find('#layim_groups');
    if(param.type === 'group'){
        log.chatgroup.find('ul').removeClass('layim_groupthis');
        log.chatgroup.append('<ul class="layim_groupthis" id="layim_group'+ param.type + param.id +'"></ul>');
        xxim.getGroups(param);
    }
    //点击群员切换聊天窗
    log.chatgroup.on('click', 'ul>li', function(){
        xxim.popchatbox($(this));
    });
};

//定位到某个聊天队列
xxim.tabchat = function(param){
    var node = xxim.node, log = {}, keys = param.type + param.id;
    xxim.nowchat = param;
    
    xxim.chatbox.find('#layim_user'+ keys).addClass('layim_chatnow').siblings().removeClass('layim_chatnow');
    xxim.chatbox.find('#layim_area'+ keys).addClass('layim_chatthis').siblings().removeClass('layim_chatthis');
    xxim.chatbox.find('#layim_group'+ keys).addClass('layim_groupthis').siblings().removeClass('layim_groupthis');
    
    xxim.chatbox.find('.layim_face>img').attr('src', param.face);
    xxim.chatbox.find('.layim_face, .layim_names').attr('href', param.href);
    xxim.chatbox.find('.layim_names').text(param.name);
    
    xxim.chatbox.find('.layim_seechatlog').attr('href', config.chatlogurl + param.id);
   
    log.groups = xxim.chatbox.find('.layim_groups');
    if(param.type === 'group'){
        log.groups.show();
    } else {
        log.groups.hide();
    }
    
    $('#layim_write').focus();
    
};

nowFname = '';
change = '';
//弹出聊天窗
xxim.popchatbox = function(othis){
    if(!chatchange==''){

        clearInterval(chatchange);
    }
    

    if(!change == ''){

        clearInterval(change);
    }
   
    var node = xxim.node, dataId = othis.attr('data-id'), param = {
        id: dataId, //用户ID
        type: othis.attr('type'),
        name: othis.find('.xxim_onename').text(),  //用户名
        face: othis.find('.xxim_oneface').attr('src'),  //用户头像
        href: 'profile.html?user=' + dataId //用户主页
    }, keys = param.type + dataId;
    if(!config.chating[keys]){
        xxim.popchat(param);
        config.chatings++;
    } else {
        xxim.tabchat(param);
    }

    config.chating[keys] = param;

    /*将打开的窗口的用户名存至全局变量*/
    nowFname = param.name;
    
    /*遍历全局数组，如果选择哪个用户就显示该用户发送的消息*/
    var fname = param.name;
    var bid = $('#username').attr('aid');
    change = setInterval(function(){
        /*调用doWebim函数*/
        doWebim(keys);
               
        },1000);

   
};

function doWebim(keys){
        // alert(keys);
        // alert(nowFname);
        // console.log('当前密匙'+keys);
         var bid = $('#username').attr('aid');
        /*此时就是打开了新的窗口在聊天状态*/
            // console.log('当前发送者：'+nowFname);
             /*清空未读信息*/
            $('#'+nowFname).next().html('');
            $.ajax({
                    type:'get',
                    data:{id:bid,fname:nowFname},
                    url:'changeState/',
                    success:function(data){

                        /*定义一个空数组*/
                        if(data==0){

                            /*没找到未读消息*/
                        }else{
                            // console.log(data);
                           /*改变数据库的状态*/
                            for(var key in data){
                                console.log('数据库键名'+key);
                                if(key==nowFname){
                                    for(var i=0;i<data[key].length;i++){
                                        // /*将未发送的消息显示在界面上*/
                                        //  //聊天模版

                                        var node = xxim.node, log = {};
                                        node.sendbtn = $('#layim_sendbtn');
                                        node.imwrite = $('#layim_write');

                                        //聊天模版
                                        log.html = function(param, type){
                                            return '<li class="'+ (type === 'me' ? 'layim_chateme' : '') +'">'
                                                +'<div class="layim_chatuser">'
                                                    + function(){
                                                        if(type === 'me'){
                                                            return '<span class="layim_chattime">'+ param.time +'</span>'
                                                                    +'<span class="layim_chatname">'+ param.name +'</span>'
                                                                    +'<img src="'+ param.face +'" >';
                                                        } else {
                                                            return '<img src="'+ param.face +'" >'
                                                                    +'<span class="layim_chatname">'+ param.name +'</span>'
                                                                    +'<span class="layim_chattime">'+ param.time +'</span>';      
                                                        }
                                                    }()
                                                +'</div>'
                                                +'<div class="layim_chatsay">'+param.content +'<em class="layim_zero"></em></div>'
                                            +'</li>';
                                        };
                                                
                                        log.imarea = xxim.chatbox.find('#layim_area'+ keys);
                                        log.imarea.append(log.html({
                                            time: data[key][i].sendtime,
                                            name: xxim.nowchat.name,
                                            face: xxim.nowchat.face,
                                            content:data[key][i].content
                                        }));
                                        log.imarea.scrollTop(log.imarea[0].scrollHeight);

                                         var chatbox = $('#layim_chatbox');
                                         if(chatbox[0]){
                                            node.layimMin.hide();
                                            chatbox.parents('.xubox_layer').show();
                                         }
                                    } 
                                }
                            }
                        }
                    }
                });
            }
   
//请求群员
xxim.getGroups = function(param){
    var keys = param.type + param.id, str = '',
    groupss = xxim.chatbox.find('#layim_group'+ keys);
    groupss.addClass('loading');
    config.json(config.api.groups, {}, function(datas){
        if(datas.status === 1){
            var ii = 0, lens = datas.data.length;
            if(lens > 0){
                for(; ii < lens; ii++){
                    str += '<li data-id="'+ datas.data[ii].id +'" type="one"><img src="'+ datas.data[ii].face +'" class="xxim_oneface"><span class="xxim_onename">'+ datas.data[ii].name +'</span></li>';
                }
            } else {
                str = '<li class="layim_errors">没有群员</li>';
            }
            
        } else {
            str = '<li class="layim_errors">'+ datas.msg +'</li>';
        }
        groupss.removeClass('loading');
        groupss.html(str);
    }, function(){
        groupss.removeClass('loading');
        groupss.html('<li class="layim_errors">请求异常</li>');
    });
};

//消息传输
xxim.transmit = function(){
    var node = xxim.node, log = {};
    node.sendbtn = $('#layim_sendbtn');
    node.imwrite = $('#layim_write');
    
    //发送
    log.send = function(){

        var data = {
            content: node.imwrite.val(),
            id: xxim.nowchat.id,
            sign_key: '', //密匙
            _: +new Date
        };

        if(data.content.replace(/\s/g, '') === ''){
            layer.tips('说点啥呗！', '#layim_write', 2);
            node.imwrite.focus();
        } else {

            var d = new Date();
            if(d.getMinutes()>9){

                var a = '';
            }else{

                var a = 0;
            }
            if(d.getSeconds()>9){

                var b = '';
            }else{

                var b = 0;
            }
            if(d.getHours()>9){

                var c = ''
            }else{

                var c = 0;
            }
            var str = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+'\n'+c+d.getHours()+':'+a+d.getMinutes()+':'+b+d.getSeconds();

            /*获取被发送人的id和发送人的用户名*/
           var bid = data.id;
           var fname = $('#username').attr('info');
           var content = data.content;

           /*向数据库聊天信息表插入数据*/
           $.ajax({

                type:'get',
                data:{bid:bid,fname:fname,content:content,sendtime:str},
                url:config.api.sendurl,
                success:function(data){

                    if(data==1){

                             //此处皆为模拟
                            var keys = xxim.nowchat.type + xxim.nowchat.id;
                            
                            //聊天模版
                            log.html = function(param, type){
                                return '<li class="'+ (type === 'me' ? 'layim_chateme' : '') +'">'
                                    +'<div class="layim_chatuser">'
                                        + function(){
                                            if(type === 'me'){
                                                return '<span class="layim_chattime">'+ param.time +'</span>'
                                                       +'<span class="layim_chatname">'+ param.name +'</span>'
                                                       +'<img src="'+ param.face +'" >';
                                            } else {
                                                return '<img src="'+ param.face +'" >'
                                                       +'<span class="layim_chatname">'+ param.name +'</span>'
                                                       +'<span class="layim_chattime">'+ param.time +'</span>';      
                                            }
                                        }()
                                    +'</div>'
                                    +'<div class="layim_chatsay">'+param.content +'<em class="layim_zero"></em></div>'
                                +'</li>';
                            };
                            
                            log.imarea = xxim.chatbox.find('#layim_area'+ keys);
                           
                            var firstContent = content.replace(/<emt>/g,'<img src="/static/plugins/layer/face/');
                            var newContent = firstContent.replace(/<\/emt>/g,'">');
                            log.imarea.append(log.html({
                                time: str,
                                name: config.user.name,
                                face: config.user.face,
                                content: newContent
                            }, 'me'));
                            node.imwrite.val('').focus();
                            log.imarea.scrollTop(log.imarea[0].scrollHeight);
                    }else{

                        return false;
                    }
                }
           });
        

            
        }
       
    };
    node.sendbtn.on('click', log.send);
    
    node.imwrite.keyup(function(e){
        if(e.keyCode === 13){
            log.send();
        }
    });
};

/**/

//事件
xxim.event = function(){
    var node = xxim.node;
    
    //主界面tab
    node.tabs.eq(0).addClass('xxim_tabnow');
    node.tabs.on('click', function(){
        var othis = $(this), index = othis.index();
        xxim.tabs(index);
    });
    
    //列表展收
    node.list.on('click', 'h5', function(){
        var othis = $(this), chat = othis.siblings('.xxim_chatlist'), parentss = othis.find("i");
        if(parentss.hasClass('fa-caret-down')){
            chat.hide();
            parentss.attr('class','fa fa-caret-right');
        } else {
            chat.show();
            parentss.attr('class','fa fa-caret-down');
        }
    });
    
    //设置在线隐身
    node.online.on('click', function(e){
        config.stopMP(e);
        node.setonline.show();
    });
    node.setonline.find('span').on('click', function(e){
        var index = $(this).index();
        config.stopMP(e);
        if(index === 0){
            node.onlinetex.html('在线');
            node.online.removeClass('xxim_offline');
        } else if(index === 1) {
            node.onlinetex.html('隐身');
            node.online.addClass('xxim_offline');
        }
        node.setonline.hide();
    });
    
    node.xximon.on('click', xxim.expend);
    node.xximHide.on('click', xxim.expend);
    
    //搜索
    node.xximSearch.keyup(function(){
        var val = $(this).val().replace(/\s/g, '');
        if(val !== ''){
            node.searchMian.show();
            node.closeSearch.show();
            //此处的搜索ajax参考xxim.getDates
            node.list.eq(3).html('<li class="xxim_errormsg">没有符合条件的结果</li>');
        } else {
            node.searchMian.hide();
            node.closeSearch.hide();
        }
    });
    node.closeSearch.on('click', function(){
        $(this).hide();
        node.searchMian.hide();
        node.xximSearch.val('').focus();
    });
    
    //弹出聊天窗
    config.chatings = 0;
    node.list.on('click', '.xxim_childnode', function(){
      
        var othis = $(this);
        xxim.popchatbox(othis);
    });
    
    //点击最小化栏
    node.layimMin.on('click', function(){
        ;
        $(this).hide();
        $('#layim_chatbox').parents('.xubox_layer').show();
    });
    
    
    //document事件
    dom[1].on('click', function(){
        node.setonline.hide();
        $('#layim_sendtype').hide();
    });
};
   
    /*获取当前管理员ID*/
    var id = $('#username').attr('aid');
    /*动态获取未读消息列表*/
    // setInterval(function(){

    //     $.ajax({
    //         type:'get',
    //         data:{id:id},
    //         url:'showMessage/',
    //         success:function(data){
                
    //             /*定义一个空数组*/
    //             var emp = [];
    //             if(data==0){

    //                 // 没找到未读消息
    //             }else{
    //                 //将未读消息存放到全局变量中
    //               for(var key in data){
                   
    //                 /*在好友列表上显示对应的未读数量*/
    //                 $('#'+key).next().html('<span class="label label-warning-light" style="float:right;margin-top:3px;">'+data[key].length+'条未读</span>');
    //               }
    //             }
    //         }
    //     });
    // },500);
    
//请求列表数据
xxim.getDates = function(index){
    var api = [config.api.friend, config.api.group, config.api.chatlog],
        node = xxim.node, myf = node.list.eq(index);
    myf.addClass('loading');
    config.json(api[index], {}, function(datas){
        if(datas.success == true){
            var i = 0, myflen = datas.data.length, str = '', item;
            if(myflen > 0){
                if(index !== 2){
                    for(; i < myflen; i++){
                        str += '<li data-id="'+ datas.data[i].id +'" class="xxim_parentnode">'
                            +'<h5><i class="fa fa-caret-right"></i><span class="xxim_parentname">'+ datas.data[i].name +'</span><em class="xxim_nums">（'+ datas.data[i].nums +'）</em></h5>'
                            +'<ul class="xxim_chatlist">';
                        item = datas.data[i].item;
                        for(var j = 0; j < item.length; j++){
                            str += '<li data-id="'+ item[j].id +'" class="xxim_childnode" type="'+ (index === 0 ? 'one' : 'group') +'"><img src="'+ item[j].face +'" class="xxim_oneface"><span class="xxim_onename" id='+item[j].name+'>'+ item[j].name +'</span><span class="label label-warning-light" style="float:right;margin-top:3px;"></span></li>';
                        }
                        str += '</ul></li>';
                    }
                } else {
                    str += '<li class="xxim_liston">'
                        +'<ul class="xxim_chatlist">';
                    for(; i < myflen; i++){
                        str += '<li data-id="'+ datas.data[i].id +'" class="xxim_childnode" type="one"><img src="'+ datas.data[i].face +'"  class="xxim_oneface"><span  class="xxim_onename">'+ datas.data[i].name +'</span><em class="xxim_time">'+ datas.data[i].time +'</em></li>'; 
                    }
                    str += '</ul></li>';
                }
                myf.html(str);
            } else {
                myf.html('<li class="xxim_errormsg">没有任何数据</li>');
            }
            myf.removeClass('loading');
        } else {
            myf.html('<li class="xxim_errormsg">'+ datas.msg +'</li>');
        }
    }, function(){
        myf.html('<li class="xxim_errormsg">请求失败</li>');
        myf.removeClass('loading');
    });
};

//渲染骨架
xxim.view = (function(){
    var xximNode = xxim.layimNode = $('<div id="xximmm" class="xxim_main">'
            +'<div class="xxim_top" id="xxim_top">'
            +'  <div class="xxim_search"><i class="fa fa-search"></i><input id="xxim_searchkey" /><span id="xxim_closesearch">×</span></div>'
            +'  <div class="xxim_tabs" id="xxim_tabs"><span class="xxim_tabfriend" title="在线管理员"><i class="fa fa-user"></i></span><span class="xxim_tabgroup" title="群组"><i class="fa fa-users"></i></span><span class="xxim_latechat"  title="最近聊天"><i class="fa fa-clock-o"></i></span></div>'
            +'  <ul class="xxim_list" style="display:block"></ul>'
            +'  <ul class="xxim_list"></ul>'
            +'  <ul class="xxim_list"></ul>'
            +'  <ul class="xxim_list xxim_searchmain" id="xxim_searchmain"></ul>'
            +'</div>'
            +'<ul class="xxim_bottom" id="xxim_bottom">'
            +'<li class="xxim_online" id="xxim_online">'
                +'<i class="xxim_nowstate fa fa-check-circle"></i><span id="xxim_onlinetex">在线</span>'
                +'<div class="xxim_setonline">'
                    +'<span><i class="fa fa-check-circle"></i>在线</span>'
                    +'<span class="xxim_setoffline"><i class="fa fa-check-circle"></i>隐身</span>'
                +'</div>'
            +'</li>'
            +'<li class="xxim_mymsg" id="xxim_mymsg" title="我的私信"><i class="fa fa-comment"></i><a href="'+ config.msgurl +'" target="_blank"></a></li>'
            +'<li class="xxim_seter" id="xxim_seter" title="设置">'
                +'<i class="fa fa-gear"></i>'
                +'<div>'
                
                +'</div>'
            +'</li>'
            +'<li class="xxim_hide" id="xxim_hide"><i class="fa fa-exchange"></i></li>'
            +'<li id="xxim_on" class="xxim_icon xxim_on fa fa-ellipsis-v"></li>'
            +'<div class="layim_min" id="layim_min"></div>'
        +'</ul>'
    +'</div>');
    dom[3].append(xximNode);
    
    xxim.renode();
    xxim.getDates(0);
    xxim.event();
    xxim.layinit();
}());

}(window);

