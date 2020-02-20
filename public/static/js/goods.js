//tab隐藏
$('.tab-goods .tr-th').click(function() {
	var status = 'show';
	if (!$(this).hasClass('open-th')) {
		$(this).addClass('open-th').find('.fa').removeClass('fa-minus').addClass('fa-plus');
		status = 'hide';
	}else {
		$(this).removeClass('open-th').find('.fa').removeClass('fa-plus').addClass('fa-minus');
	}
	for(i = 0; i < $(this).nextAll('tr').size(); i++) {
		if ($(this).nextAll('tr').eq(i).hasClass('rtd')) {
			if (status == 'show') {
				$(this).nextAll('tr').eq(i).show();
			}else {
				$(this).nextAll('tr').eq(i).hide();
			}
		}else {
			break;
		}
	}
});
//商品分类选择初始化事件
$(".typeid").live("change",function(event){
    event.stopImmediatePropagation();
    var id = $(this).val();
    $(this).nextAll('.typeid').remove();
    var option = '';
    for(x in goods_type) {
        if (goods_type[x].pid == id) {
            option = option + '<option value="'+goods_type[x]['id']+'">'+goods_type[x]['name']+'</option>';
        }
    }
    if (option) {
        var html = '<select class="form-control select-list input-sm typeid" name="typeid" size="10">';
        html = html + option + '</select>';
        $(this).after(html);
    }
    //分类路径显示
    var list = Array();
    $('body .typeid').each(function(i) {
        if ($(this).find(':selected')) {
            list[i] = $(this).find(':selected').text();
        }
    });
    if (list.length > 0) {
        list = list.join(' -> ');
        $('body .cat-name').html(list);
    }else {
        $('body .cat-name').text('未选择');
    }
});
//商品规格选择初始化事件
$('.is_spec').on('ifChanged', function(event){
	if ($('.is_spec:checked').val() == 2) {
		show_spec();
	}else {
		$('.spec-list').hide();
		if ($('.spec-all').css('display') == 'none') {
			$('.spec-all').show();
			// setTimeout(function() {
			// 	$('html,body').animate({scrollTop:$('.tr-th').eq(1).offset().top}, 200);
			// }, 100);
		}
	}
});
//显示商品规格
function show_spec(){
	if ($('.is_spec').parents('.rtd').css('display') != 'none') {
		if ($('.spec-list').css('display') == 'none') {
			$('.spec-list').show();
			// setTimeout(function() {
			// 	$('html,body').animate({scrollTop:$('.tr-th').eq(1).offset().top}, 200);
			// }, 100);
		}
	}else {
		$('.spec-list').hide();
	}
	$('.spec-all').hide();
	if (spec_list.length > 0) {
		var spec_html = '';
		for (x in spec_list) {
			spec_html = spec_html + '<div class="spec-s-item spec-item-'+spec_list[x]['id']+'" data-id="'+spec_list[x]['id']+'"><div class="spec-t">'+spec_list[x]['name']+'</div><div class="spec-b checkbox i-checks">';
			for(j in spec_data) {
				if (spec_data[j]['typeid'] == spec_list[x]['id']) {
					//如果是修改，存在goods_spec
					if (goods_spec) {
						var spec_checked = '';
						for (var i = 0; i < goods_spec.length; i++) {
							//根据规格的id或者名字默认选中
							if(goods_spec[i].spec_idlist.indexOf(spec_data[j]['id']) != -1 || goods_spec[i].spec_value.indexOf(spec_data[j]['value']) != -1){
								spec_checked = 'checked';break;
							}
						}
						spec_html = spec_html + '<label class="checkbox-inline"><div class="icheckbox_square-green"><input type="checkbox" class="square-red" data-id="'+spec_list[x]['id']+'" value="'+spec_data[j]['id']+'" data-name="'+spec_data[j]['value']+'" '+spec_checked+' /></div><i></i> '+spec_data[j]['value']+' &nbsp;&nbsp;</label>';
					}else {
						spec_html = spec_html + '<label class="checkbox-inline"><div class="icheckbox_square-green"><input type="checkbox" class="square-red" data-id="'+spec_list[x]['id']+'" value="'+spec_data[j]['id']+'" data-name="'+spec_data[j]['value']+'" /></div><i></i> '+spec_data[j]['value']+' &nbsp;&nbsp;</label>';
					}
				}
			}
			spec_html = spec_html + '</div></div>';
		}
		$('.spec-s').html(spec_html);
		$('.spec-s').find('input[type="checkbox"].square-red').iCheck({
	        checkboxClass: 'icheckbox_square-green'
	    });
	    $('.spec-s').find('input[type="checkbox"].square-red').each(function() {
	    	if ($(this).attr('checked')) {
	    		$(this).iCheck('check');
	    	}
	    });
	    $('.spec-s').find('.square-red').on('ifChanged', function(event){
			spec_value_show();
		});
	    spec_value_show();
	}
}
//清除所有规格
function clear_spec(confirms){
	if (!confirms)
	bpop.add('确定要清除所有规格吗？', 2, function(){
		spec_res = {};
		$('.spec-table .th-name').remove();
		$('.spec-table tbody').html('');
		$('.spec-table .th-name').remove();
		$('.spec-s .square-red').prop('checked', false);
		$('.spec-s .checkbox-inline .icheckbox_square-green').removeClass('checked');
		$('.spec-s .checkbox-inline .icheckbox_square-blue').removeClass('checked').attr('aria-checked', false);
	});
}
//查找规格信息
function select_spec(id) {
	var list = Array();
	for (x in spec_data) {
        if (spec_data[x]['id'] == id) {
            list = spec_data[x];break;
        }
    }
    return list;
}
//查找规格类型信息
function select_spec_type(id) {
	var list = Array();
	for (x in spec_list) {
        if (spec_list[x]['id'] == id) {
            list = spec_list[x];break;
        }
    }
    return list;
}
//显示选中商品规格
function spec_value_show(){
	//清除现有的规格
	clear_spec(true);

	var td_html = '';
		td_html = td_html + '<td><div class="image-item">';
		td_html = td_html + '<img src="/public/image/image.jpg" class="logo" />';
		td_html = td_html + '<input type="file" class="hide form-control pinput file" name="goods_logo" />';
		td_html = td_html + '</div></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm input-100 goods_sn" name="goods_sn" /></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm goods_price" name="goods_price"/></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm costs_price" name="costs_price" /></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm market_price" name="market_price" /></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm goods_number" name="goods_number" /></td>';
    	td_html = td_html + '<td><input type="text" class="form-control input-sm goods_weight" name="goods_weight" /></td>';
    	td_html = td_html + '<td><input type="checkbox" class="is_state" name="is_state" value="1" checked /></td>';

	//重来
	var itemInx = $('.spec-s').find('.spec-s-item').size();
	var specsItem = $('.spec-s').find('.spec-s-item');
	//循环类目规格
	$('.spec-table').find('.tr').remove();
	$('.spec-table').find('.th-name').remove();
	//查找有的选择规格
	var inx_id = Array();		//有选择参数的规格
	for(var j = 0; j < itemInx; j++) {
		if (specsItem.eq(j).find('.square-red:checked').size() > 0) {
			inx_id = inx_id.concat(specsItem.eq(j).attr('data-id'));
		}
	}

	//如果不存在，则直接返回
	if (inx_id.length <= 0) return false;
	var th_html = '';
	for (var i = 0; i < inx_id.length; i++) {
		var data = select_spec_type(inx_id[i]);
		th_html = th_html + '<th class="th-name th-'+data.id+'" data-id="'+data.id+'">'+data.name+'</th>';
	}
	$('.spec-table').find('.th').prepend(th_html);

	var temp_tdRow = Array(),		//需要合并单元格总数
		tdRow = Array(),			//需要合并单元格总数
		tempRes = Array();			//行结果
	for (var i = 0; i < inx_id.length; i++) {
		tempRes[i] = Array();

		temp_tdRow[i] = $('.spec-s').find('.spec-item-'+inx_id[i]+' .square-red:checked').size();

		for (j = 0; j < temp_tdRow[i]; j++) {
			var id = $('.spec-s').find('.spec-item-'+inx_id[i]+' .square-red:checked').eq(j).val();
			var data = select_spec(id);
			tempRes[i].push(data);
		}
	}

	//需要合并的单元格
	for (j = 1; j < temp_tdRow.length; j++) {
		var number = 0;
		for (i = j; i < temp_tdRow.length; i++) {
			number = number == 0 ? temp_tdRow[i] : number * temp_tdRow[i];
		}
		tdRow = tdRow.concat(number);
	}
	tempRes = tospecRes(tempRes, 0);
	spec_res = tempRes;
	var tr_html = '',
		trtd_html = '',
		td_html_1 = '',
		all_id = Array(),
		product_id = 0;
	var ainx = Array();
	//console.log(tdRow);
	for (var i = 0; i < tempRes.length; i++) {
		var spec_idlist = Array();
		var spec_value = Array();
		trtd_html = '';
		for (var j = 0; j < tempRes[i].length; j++) {
			spec_idlist.push(tempRes[i][j].id);
			spec_value.push(tempRes[i][j].value);
		}
		data_id = spec_idlist.join(',');
		var spec_value = spec_value.join(',');
		//如果是修改，存在goods_spec
		var proRes = false;
		if (goods_spec) {
			for (var k = 0, len = goods_spec.length; k < len; k++) {
				if (data_id == goods_spec[k].spec_idlist.join(',') || spec_value == goods_spec[k].spec_value.join(',')) {
					proRes = goods_spec[k];
					break;
				}
			}
		}
		product_id = 0;
		for (var j = 0; j < tempRes[i].length; j++) {
			var ids = 'spec-'+tempRes[i][j].id+'-'+Math.ceil(Math.random()*9999),
				cls = ids+'s',
				imgs = '';
			if (proRes) {

				if (tdRow.length > 0) {
					var rowspan = tdRow[j] ? 'rowspan="'+tdRow[j]+'"' : '';
					if (!ainx[j]) {
						ainx[j] = tempRes[i][j].id;
						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+proRes.spec_value[j]+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (ainx[j] != tempRes[i][j].id) {
						ainx[j] = tempRes[i][j].id;
						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+proRes.spec_value[j]+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (temp_tdRow[j] == 1 && ((i) % tdRow[j] == 0 || i == 0)) {

						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+proRes.spec_value[j]+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (j > tdRow.length - 1) {

						trtd_html = trtd_html + '<td><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+proRes.spec_value[j]+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';
					}
				}else {
					trtd_html = trtd_html + '<td><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+proRes.spec_value[j]+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';
				}
				
				td_html_1 = '';
				td_html_1 = td_html_1 + '<td><div class="image-item">';
				td_html_1 = td_html_1 + '<img src="'+proRes.goods_logo+'" class="logo" />';
				td_html_1 = td_html_1 + '<input type="file" class="hide form-control pinput file" name="goods_logo" />';
				td_html_1 = td_html_1 + '</div></td>';
				td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm input-100 goods_sn" name="goods_sn" value="'+proRes.goods_sn+'"/></td>';
				td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm goods_price" name="goods_price" value="'+proRes.goods_price+'"/></td>';
			    td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm costs_price" name="costs_price" value="'+proRes.costs_price+'"/></td>';
			    td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm market_price" name="market_price" value="'+proRes.market_price+'"/></td>';
			    td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm goods_number" name="goods_number" value="'+proRes.goods_number+'"/></td>';
			    td_html_1 = td_html_1 + '<td><input type="text" class="form-control input-sm goods_weight" name="goods_weight" value="'+proRes.goods_weight+'"/></td>';
			    if (proRes.is_state == 1) {
			    	td_html_1 = td_html_1 + '<td><input type="checkbox" class="is_state" name="is_state" value="1" checked /></td>';
			    }else {
			    	td_html_1 = td_html_1 + '<td><input type="checkbox" class="is_state" name="is_state" value="1" /></td>';
			    }
			    product_id = proRes.product_id;
			}else {
				product_id = 0;
				
				if (tdRow.length > 0) {
					var rowspan = tdRow[j] ? 'rowspan="'+tdRow[j]+'"' : '';
					if (!ainx[j]) {
						ainx[j] = tempRes[i][j].id;

						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+tempRes[i][j].value+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (ainx[j] != tempRes[i][j].id) {
						ainx[j] = tempRes[i][j].id;
						
						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+tempRes[i][j].value+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (temp_tdRow[j] == 1 && ((i) % tdRow[j] == 0 || i == 0)) {

						trtd_html = trtd_html + '<td '+rowspan+'><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+tempRes[i][j].value+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';

					} else if (j > tdRow.length - 1) {

						trtd_html = trtd_html + '<td><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+tempRes[i][j].value+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';
					}
				}else {
					trtd_html = trtd_html + '<td><input type="text" class="form-control input-sm spec-name" name="spec-name" value="'+tempRes[i][j].value+'" />'+imgs+'<input type="hidden" class="id" value="'+tempRes[i][j].id+'" /></td>';
				}
				td_html_1 = td_html;
			}
		}

		tr_html = tr_html + '<tr class="tr tr-open" data-id="'+data_id+'"><input type="hidden" class="product_id" value="'+product_id+'" />' + trtd_html;
		tr_html = tr_html + td_html_1 + '</tr>';
	}
	//console.log(goods_spec[0]);
	$('.spec-table').find('tbody').html(tr_html);
	copy_value('goods_price');
	copy_value('costs_price');
	copy_value('market_price');
	copy_value('goods_number');
	copy_value('goods_weight');
}
//复制文本框值
function copy_value(cls) {
	var tables = $('.spec-table');
	tables.find('.'+cls).blur(function() {
		if ($(this).val() != '') {
			var state = true;
			var len = tables.find('.'+cls).size();
			var inx = $(this).index();
			for (var i = 0; i < len; i++) {
				if (tables.find('.'+cls).eq(i).val() != '' && i != inx) {
					state = false;break;
				}
			}
			if (state) {
				tables.find('.'+cls).val($(this).val());
			}
		}
	});
}
//笛卡尔积算法
function tospecRes(temp, inx, res) {
	var data = Array();
	if(inx >= temp.length) {
		data.push(res); 
		return data;
	};
	var aArr = temp[inx];
	if(!res) res = Array();
	for(var i = 0; i < aArr.length; i++) {
		var ares = res.slice(0, res.length);
		ares.push(aArr[i]);
		data = data.concat(tospecRes(temp, inx+1, ares));
	}
	return data;
}