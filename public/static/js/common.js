//弹窗函数对象
function bpop() {
	this.speed = 0
}
/**
* 添加弹窗
* info 内容，type类型：(1只有确认)(2确认-取消), callback,默认false
*/
bpop.prototype.add = function(info, type, callback) {
	this.clean();
	var btn = '<span class="btn-item btn-item-red btn-confirm" onclick="bpop.clean('+callback+');">确认</span>';
	btn = type == 2 ? btn = btn + '<span class="btn-item btn-cancel" onclick="bpop.clean();">取消</span>' : btn;
	var html = '<div class="bpop"><div class="bpop-center"><div class="bpop-title">网站提醒</div><div class="bpop-body">'+info+'</div><div class="bpop-btn">'+btn+'</div></div></div><div class="zbodys"></div>';
	$('body').append(html);
	this.fixed();
}
//定位
bpop.prototype.fixed = function() {
	this.center('bpop');
	$('body .bpop').addClass('bpop-css3');
}
//清除
bpop.prototype.clean = function(callback) {
	$('body .bpop').removeClass('bpop-css3');
	$('body .zbodys, body .bpop-load, body .bpop-tip').remove();
	$('body .bpop').fadeOut(function() {
		$(this).remove();
	}, 0);
	//如果存在回调函数则启用
	if (callback) {
		callback();
	}
}
//loading加载
bpop.prototype.addLoading = function(z) {
	this.clean();
	$('body .zbodys, body .bpop, body .bpop-load').remove();
	$('body').append('<div class="bpop-load" />');
	if (z) $('body').append('<div class="zbodys" />');
	this.center('bpop-load');
}
//loading加载
bpop.prototype.parentLoading = function(z) {
	$(window.top.document).find('body .zbodys, body .bpop-load, body .bpop-tip').remove();
	var loads = $('<div class="bpop-load" />');
	$(window.top.document).find('body').append(loads);
	if (z) $(window.top.document).find('body').append('<div class="zbodys" />');
	$(window.top.document).find(loads).css({
		'left' : ($(window.top).width()/2 - $(window.top.document).find(loads).width()/2) + 'px',
		'top' : ($(window.top).height()/2 - $(window.top.document).find(loads).height()/2) + 'px'
	});
}
//tip提醒 1 正确,2提示,3错误
bpop.prototype.tip = function(info, status, times, url, z) {
	clearTimeout(this.speed);
	this.clean();
	$('body .bpop-tip').remove();
	//是否启用遮罩
	var cls = status == 2 ? 't' : status == 3 ? 'n' : '';
	var html = '<div class="bpop-tip"><div class="bpop-tip-center"><span class="bpop-s bpop-'+cls+'"></span><span class="bpop-font">'+info+'</span><div class="bpop-clear"></div></div></div>';
	$('body').append(html);
	if (z) $('body').append('<div class="zbodys" />');
	this.center('bpop-tip');
	//自动关闭
	this.speed = setTimeout(function() {
		if (url) {
			//如果是重载
			if (url == 'load' || url == '?') {
				window.location.reload();
			}else {
				location.href = url;
			}
		}else {
			bpop.clean();
		}
	}, times*1000);
}
//元素居中
bpop.prototype.center = function(cls) {
	$('body .'+cls).css({
		'left' : ($(window).width()/2 - $('body .'+cls).width()/2) + 'px',
		'top' : ($(window).height()/2 - $('body .'+cls).height()/2) + 'px'
	});
}
//创建对象
var bpop = new bpop();


//弹窗加载对象
function dpop() {}
/*
* width		宽度
* height	高度
* bremove	是否使用遮照
* reloads	是否刷新
*/
dpop.prototype.add = function(width, height, title, url, bremove, reloads, screens) {
	var width = String(width);
	var height = String(height);
	if (width == '' && height == '')  {
		width = '300px';
		height = '220px';
	}else {
		//判断有无px或者
		if (width.indexOf('%') == -1) {
			width = width+'px';
			height = height+'px';
		}
	}
	//生成唯一class码
	var cls = 'cloud-class-'+Math.ceil(Math.random()*1000);
	url = url+'#'+cls;
	var pops = $('<div id="dpop" class="'+cls+'" style="width:'+width+';height:'+height+';"></div>');
	$('body').append(pops);
	pops.html('<div class="dpop_title">'+title+'<div class="dpop_close" title="关闭窗口" onclick="dpop.close(\''+cls+'\', '+reloads+');"></div><div class="dpop_fd" title="放大窗口"></div><div class="dpop_sx" title="缩小窗口"></div></div><div class="frame"><iframe scrolling="0" width="100%" height="98%" src="'+url+'" frameborder="0" name="iframe_pop"></iframe></div>');
	//是否需要遮照层
	if (bremove) $('body').append('<div class="zbody '+cls+'"></div>');
	//判断是第几个窗口
	var height = pops.height();
	pops.find('.frame').css({ 'height' : (height-31)+'px' });
	//将窗口定位
	this.fixed(pops, height);
	this.divmove(pops);
	if (screens)
		this.winsf(pops, $(window).width(), $(window).height());
}
//添加窗口，不是iframe
dpop.prototype.addbody = function(width, height, title, html, reloads) {
	this.closeAll();
	//生成唯一class码
	var cls = 'cloud-class-'+Math.ceil(Math.random()*1000);
	$('body').append('<div class="zbody '+cls+'"></div>');
	var pops = $('<div id="dpop" class="'+cls+'" style="width:'+width+'px;height:'+height+'px;" />');
	pops.html('<div class="dpop_title">'+title+'<div class="dpop_close" title="关闭窗口" onclick="dpop.close(\''+cls+'\', '+reloads+');"></div></div><div class="bodys">'+html+'</div>');
	$('body').append(pops);
	pops.find('.bodys').css({ 'height' : (height-31)+'px' });
	//this.divmove(pops);
	//将窗口定位
	this.fixed(pops, height);
}
//窗口定位
dpop.prototype.fixed = function(obj, height) {
	var left = ($(window).width()/2)-obj.width()/2;
	var top = ($(window).height()/2)-(obj.height()/2);
	if (top < 0) {
		top = 0;
	}
	obj.css({'left':left+'px','top':top+'px','zIndex':999});
}
//关闭窗口
dpop.prototype.close = function(obj, reloads) {
	$('.'+obj).remove();
	// $('body .dropdown-toggle').dropdown();
	//判断是否是函数
	if (typeof(reloads) == 'function') {
		//如果是函数，则执行回调函数
		reloads(1);
	}else {
		if (reloads) window.location.reload();
	}
}
//关闭所有窗口
dpop.prototype.closeAll = function() {
	$('body #dpop, body .zbody').remove();
}
//关闭父窗口
dpop.prototype.parentClose = function() {
	$(window.parent.document).find('body #dpop, body .zbody').remove();
	// $('body .dropdown-toggle').dropdown();
}
//窗口移动
dpop.prototype.divmove = function(obj) {
	var fwidth = obj.width();
	var fheight = obj.height();
	var objleft;
	var objtop;
	//给当前的div的dtitle元素绑定鼠标按下事件
	obj.dblclick(function(event) {
		dpop.winsf(obj, fwidth, fheight);
	});
	obj.find('.dpop_fd, .dpop_sx').click(function(event) {
		dpop.winsf(obj, fwidth, fheight);
	});
	obj.mousedown(function(event) {
		event.preventDefault();
		event.stopImmediatePropagation();
		obj.css({'width':obj.width()+'px','height':obj.height()+'px'});
		obj.find('iframe').hide();
		$('body #dpop').css({'zIndex':998});
		obj.css({'zIndex':999});
		var offset = $(this).offset();
		var x = event.clientX - offset.left;
		var y = event.clientY - offset.top + $(window).scrollTop();
		$(document).mousemove(function(event) {
			event.preventDefault();
			var _left = event.clientX - x;
			var _top = event.clientY - y;
			obj.css('left',_left+'px');
			if (_top < 0) {
				obj.css('top','0px');
			}else {
				obj.css('top',_top+'px');
			}
			return false;
		});
		obj.mouseup(function() {
			$(document).unbind()					//解除
			obj.find('iframe').show();
			return false;
		});
	});
}
dpop.prototype.winsf = function(obj, fwidth, fheight) {
	var width = $(window).width();
	var height = $(window).height();
	if (obj.width() != width) {
		//保存放大时的位置
		objleft = obj.css('left');
		objtop = obj.css('top');
		obj.css({'width':width+'px','height':height+'px','left':'0px','top':'0px'});

		obj.find('.frame').css({ 'height' : (height-31)+'px' });

		obj.find('.dpop_fd').hide();
		obj.find('.dpop_sx').show();
	}else {
		obj.css({'width':fwidth+'px','height':fheight+'px','left':objleft,'top':objtop});

		obj.find('.frame').css({ 'height' : (fheight-31)+'px' });

		obj.find('.dpop_fd').show();
		obj.find('.dpop_sx').hide();
	}
}
//创建对象
var dpop = new dpop();

//地区选择器,使用方法，引入地区库,初始化init
function regions(cls, id, num, top_id, types, callback) {
    this.cls = cls;//元素
    this.id = id ? id : 0;
    this.num = num ? num : 5;//层级
    this.top_id = top_id ? top_id : 0;//最多到顶级ID
    this.types = types ? types : '';//最多到顶级ID
    this.callback = callback;
}
//地区选择初始化
regions.prototype.init = function() {
	var list = this.prevList(this.id);
	if (list.length > 0 && this.types == 'edit') {
		this.updateList(list);
	}else {
		var option = '<option value="0">请选择</option>';
	    for(x in region_list) {
	        if (region_list[x].parent_id == this.id) {
	            option = option + '<option value="'+region_list[x].id+'">'+region_list[x].name+'</option>';
	        }
	    }
	    $(this.cls).html(option);
	    this.areaEvent();
	}
}
//选择事件
regions.prototype.areaEvent = function() {
    var pthis = this;
    var len = $('body '+pthis.cls).size();
	$('body '+pthis.cls).change(function(event) {
        event.stopImmediatePropagation();
        if (typeof(pthis.callback) == 'function')
    		pthis.callback($(this));
        var inx = $(this).index();
        if (inx < pthis.num-1) {
	        pthis.loadArea($(this), $(this).val());
    	}
    });
}
//加载下拉菜单
regions.prototype.loadArea = function(_this, id) {
    _this.nextAll(this.cls).remove();
    if (id <= 0) return false;
    var html = '<select class="'+$(this.cls).attr('class')+'"><option value="0">请选择</option>';
    var list = Array();
    var i = 0;
    for(x in region_list) {
        if (region_list[x].parent_id == id) {
            html = html + '<option value="'+region_list[x].id+'">'+region_list[x].name+'</option>';
            i++;
        }
    }    
    html = html + '</select>';
    if (i > 0) {
        _this.after(html);
        this.areaEvent();
    }
}
//更新城市
regions.prototype.updateList = function(list) {
	var html = '';
	for(x = list.length-1; x >= 0; x--) {
		html = html + '<select class="'+$(this.cls).attr('class')+'"><option value="0">请选择</option>';
		for(j in region_list) {
			if (region_list[j].parent_id == list[x].parent_id) {
				if (region_list[j].id == list[x].id) {
					html = html + '<option value="'+region_list[j].id+'" selected>'+region_list[j].name+'</option>';
				}else {
					html = html + '<option value="'+region_list[j].id+'">'+region_list[j].name+'</option>';
				}
			}
		}
		html = html + '</select>';
	}
	$('body '+this.cls).after(html);
	$('body '+this.cls).eq(0).remove();
	this.areaEvent();
}
//查找所有上级
regions.prototype.prevList = function(id) {
	var list = Array();
	if (id != this.top_id) {
		for(x in region_list) {
			if (region_list[x].id == id) {
				list = list.concat(region_list[x], this.prevList(region_list[x].parent_id));
			}
		}
	}
	return list;
}
//刷新当前页面
function Reload(){
	window.location.reload();
}
//通用删除
function dataDel(url, id, callback) {
    var idlist = '';
    if (id) {
        idlist = id;
    }else {
        var idlist = Array();
	    $('body .idlist:checked').each(function() {
	    	idlist.push($(this).val());
	    });
	    idlist = idlist.join(',');
    }
    if (idlist == '') {
    	bpop.tip('请选中后再操作！', 2, 1);
    }else {
    	urlelement = url;
    	idlistelement = idlist;
    	callbackelement = callback;
    	bpop.add('确定要删除选中数据吗？', 2, function(){
    		bpop.addLoading(true);
	    	$.post(urlelement, {'idlist':idlistelement}, function(data) {
	    		if (data == null || data == undefined || data == '') {
					bpop.tip('数据返回错误！', 2, 2);
					return false;
				}
				if (data.success == true) {
					bpop.tip(data.info, 1, 1, false, true);
					if (typeof(callbackelement) == 'function'){
			        	callbackelement();
					}
				}else {
					bpop.tip(data.info, 2, 1);
				}
	    	}, 'JSON');
    	});
    }
}
//单图片文件上传
$("body").on('click', '.image-item .logo', function(){
	$(this).next().trigger("click"); 
});
$("body").on('change', '.image-item .file', function(){
	var _this = $(this);
	//显示选中图片
	var file = new FileReader();
	var img = _this.prop('files')[0];
	file.readAsDataURL(img);
	file.onload = function(e) {
		_this.prev().attr('src',this.result);
	};
});

//初始化
$(function() {
	//复选框选择
	$('#table .checkbox').change(function() {
		if ($('#table .checkbox').prop('checked') == false) {
			$('#table .idlist').prop('checked', false);
		}else {
			$('#table .idlist').prop('checked', true);
		}
	});
	//table切换
	$('body #tabs').hide();
    $('body #tabs').eq(0).show();
	$('.nav-tabs .tab-item').click(function(event) {
		event.stopImmediatePropagation();
		$(this).addClass('active').siblings('.tab-item').removeClass('active');
		$('body #tabs').hide();
		$('body #tabs').eq($(this).index()).show();
	});
});
//获取表单数据
function getFormValue() {
    var datas = Array();
    var data = Array();
    var types,names,status,value,datatype,rule,errorms;
    var getform = $('.getform');
    var h = 0;
    for(var i = 0; i < getform.find('.pinput').size(); i++) {
        status = true;
        types = getform.find('.pinput').eq(i).attr('type');
        names = getform.find('.pinput').eq(i).attr('name');
        datatype = getform.find('.pinput').eq(i).attr('datatype');
        rule = getform.find('.pinput').eq(i).attr('rule');
        errorms = getform.find('.pinput').eq(i).attr('errorms');
        names = names.replace('[]', '');
        for(var j = 0; j < datas.length; j++) {
            if (datas[j][1] == names) {
                status = false;
                break;
            }
        }
        if (!status) continue;
        var check = { 'datatype' : datatype, 'rule' : rule, 'errorms' : errorms };
        datas[h] = [types, names, check];
        h++;
    }
    data = {};
    for(var i = 0; i < datas.length; i++) {
        switch(datas[i][0]) {
            case 'radio' : 
                value = getform.find('.'+datas[i][1]+':checked').val();
                break;
            case 'checkbox' : 
                value = Array();
                getform.find('.'+datas[i][1]+':checked').each(function(j) {
                    value[j] = $(this).val();
                });
                break;
            case 'text' : 
            case 'password' : 
            case 'textarea' : 
            case 'select' : 
            case 'hidden' : 
            case 'number' : 
                //表单验证
                value = getform.find('.'+datas[i][1]).val();
                break;
            default : value = ''; break;
        }
        var names = "data."+datas[i][1]+"=''";
        eval(names);
        data[datas[i][1]] = value;
    }
    return data;
}