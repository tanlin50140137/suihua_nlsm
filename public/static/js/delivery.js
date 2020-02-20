function delivery() {}
//默认加载
delivery.prototype.init = function() {
    dy.show();
    dy.areaOper();
    $('body .add_area').click(function() {
        var region = $(this).parents('.dtable').find('.tr-list').eq(-1).find('.region').val();
        if (region == '') {
            $(this).parents('.dtable').find('.tr-list').eq(-1).find('.red').html('请选择指定地区！');
            return false;
        }
        $(this).parents('.dtable').find('.tr-list').eq(-1).after($('#list-tpl').html());
        dy.areaOper();
    });
}
//更新指定地区
delivery.prototype.areaOper = function() {
    var _this = this;
    $('body .edit_area').click(function(event) {
        event.stopImmediatePropagation();
        _this.regionEdit($(this));
    });
    $('body .del_area').click(function(event) {
        event.stopImmediatePropagation();
        $(this).parents('.tr-list').remove();
    });
}
//显示设置
delivery.prototype.show = function() {
    $('body .typeid').each(function(i) {
        $('.no-data').hide();
        var value = parseInt($(this).val());
        var cls = '';
        switch(value) {
            case 0: cls = 'dtable-unify'; break;
            case 1: cls = 'dtable-weight'; break;
            case 2: cls = 'dtable-number'; break;
        }
        if ($(this).prop('checked')) {
            $('body #'+cls).show();
        }else {
            $('body #'+cls).hide();
        }
        if ($('#dtable-weight').css('display') == 'none' && $('#dtable-number').css('display') == 'none') {
            $('.no-data').show();
        }
    });
}
//更新指定地区
delivery.prototype.regionEdit = function(obj) {
    var areaList = $('#area-tpl').html();
    dpop.addbody(700, 500, '选择地区', areaList);
    var idlist = obj.parents('.tr-list').find('.region').val();
    idlist = idlist.split(',');
    var all_idlist = Array();
    var leftli = '',status,table;
    table = obj.parents('.dtable');
    table.find('.region').each(function() {
        var value = $(this).val().split(',');
        if (value.length > 0) {
            all_idlist = all_idlist.concat(value);
        }
    });
    var _this = this;
    for (i = 0; i < region_list.length; i++) {
        if (region_list[i].parent_id == 1) {
            leftli = leftli + '<li><a href="javascript:;" class="level1"><span class="area-sub"></span><span class="item-name">'+region_list[i].name+'</span></a><ul class="ul">';
            for(x in region_list) {
                status = '';
                if (region_list[x].parent_id == region_list[i].id) {
                    for (h in all_idlist) {
                        if (region_list[x].id == all_idlist[h]) {
                            status = 'disabled';
                            break;
                        }
                    }
                    for (j in idlist) {
                        if (region_list[x].id == idlist[j]) {
                            $('.area-l').append('<li data-id="'+region_list[x].id+'" data-name="'+region_list[x].name+'" class="area-idlist"><a class="area-close">x</a><span class="item-name">'+region_list[x].name+'</span></li>');
                            break;
                        }
                    }
                    leftli = leftli + '<li><a href="javascript:;" class="level2 '+status+'" data-id="'+region_list[x].id+'" data-name="'+region_list[x].name+'">'+region_list[x].name+'</a></li>';
                }
            }
            leftli = leftli + '</ul></li>';
        }
    }
    $('#dpop .area-ul').html(leftli);
    _this.del();
    $('#dpop .title-count').text('已选择'+$('.area-l li').size()+'个');
    $('.area-ul li .level1').click(function(event) {
        event.stopImmediatePropagation();
        $(this).siblings('.ul').toggle();
        $(this).find('.area-sub').toggleClass('area-sub-l');
    });
    $('.area-ul li .level2').click(function(event) {
        event.stopImmediatePropagation();
        if (!$(this).hasClass('disabled')) {
            $(this).toggleClass('selected');
        }
    });
    //添加到列表
    $('#dpop .area-add').click(function() {
        $('.area-ul li .selected').each(function(i) {
            $('.area-l').append('<li data-id="'+$(this).attr('data-id')+'" data-name="'+$(this).attr('data-name')+'" class="area-idlist"><a class="area-close">x</a><span class="item-name">'+$(this).attr('data-name')+'</span></li>');
            $(this).removeClass('selected').addClass('disabled');
        });
        $('#dpop .title-count').text('已选择'+$('.area-l li').size()+'个');
        _this.del();
    });
    //确认
    $('#dpop .area-confirm').click(function() {
        var idList = Array();
        var nameList = Array();
        $('.area-l .area-idlist').each(function(i) {
            idList[i] = $(this).attr('data-id');
            nameList[i] = $(this).attr('data-name');
        });
        idList = idList.join(',');
        obj.parents('.tr-list').find('.region-list-item').html(nameList.join('，'));
        nameList = nameList.join(',');
        obj.parents('.tr-list').find('.region').val(idList);
        obj.parents('.tr-list').find('.namelist').val(nameList);
        obj.parents('.tr-list').find('.red').remove();
        dpop.closeAll();
    });
}
//删除已选
delivery.prototype.del = function() {
    //删除已选
    $('#dpop .area-l .area-close').click(function(event) {
        event.stopImmediatePropagation();
        var _this = $(this).parent();
        $('.area-ul .disabled').each(function() {
            if ($(this).attr('data-id') == _this.attr('data-id')) {
                $(this).removeClass('disabled');
            }
            _this.remove();
            $('#dpop .title-count').text('已选择'+$('.area-l li').size()+'个');
        });
    });
}
//添加指定地区
delivery.prototype.add = function(value) {
    var title,cls;
    switch(value) {
        case 0: title = '统一价'; cls = 'dtable-unify'; break;
        case 1: title = '按重量'; cls = 'dtable-weight'; break;
        case 2: title = '按件数'; cls = 'dtable-number'; break;
        default : return false;
    }
}
//获取数据
delivery.prototype.getData = function() {
    var typeid = parseInt($('body .typeid:checked').val());
    if (typeid != 0 && typeid != 1 && typeid != 2) {
        bpop.tip('请先选择费用计算方式！', 2, 2);
        return false;
    }
    var error = '<span class="red">费用应为0.00至999999.99的数字</span>',
        cls = '',
        name = '',
        values = Array();
    switch(typeid) {
        case 0 : cls = 'dtable-unify'; name = '统一价'; break;
        case 1 : cls = 'dtable-weight'; name = '按重量'; break;
        case 2 : cls = 'dtable-number'; name = '按件数'; break;
        default : cls = ''; break;
    }
    if (cls == '') return false;
    cls = $('body #'+cls);
    for (n = 0; n < cls.find('.tr-list').size(); n++) {
        cls.find('.tr-list').eq(n).find('.red').remove();
        var vallist = {};
        vallist.price = Number(cls.find('.tr-list').eq(n).find('.price').val());
        vallist.region = cls.find('.tr-list').eq(n).find('.region').val();
        if (vallist.region == '') {
            cls.find('.tr-list').eq(n).remove();
            continue;
        }
        
        if ((!vallist.price || vallist.price < 0.00 || vallist.price > 9999999.99) && vallist.price != 0) {
            cls.find('.tr-list').eq(n).find('.td-input').append(error);
            continue;
        }
        vallist.namelist = cls.find('.tr-list').eq(n).find('.namelist').val();
        values[n] = vallist;
    }
    if (values.length <= 0) return false;
    return values;
}
var dy = new delivery();