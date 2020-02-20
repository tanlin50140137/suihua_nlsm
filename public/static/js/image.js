$(function(){
	var imgId = 0;
	var delParent;
	var defaults = {
		fileType         : ["jpg","png","bmp","jpeg"],   // 上传文件的类型
		fileSize         : 1024 * 1024 * 10                  // 上传文件的大小 10M
	};
		/*点击图片的文本框*/
	$(".z_photo .file").live("change",function(){
		var idFile = $(this).attr("id");
		var file = document.getElementById(idFile);
		var imgContainer = $(this).parents(".z_photo"); //存放图片的父亲元素
		var fileList = file.files; //获取的图片文件
		var input = $(this).parent();//文本框的父亲元素
		var imgArr = [];
		//遍历得到的图片文件
		var numUp = imgContainer.find(".up-section").length;
		var totalNum = numUp + fileList.length;  //总的数量
		imgId = imgId + 1;
		if(fileList.length > 5 || totalNum > 5 ){
			alert("上传图片数目不可以超过5个，请重新选择");  //一次选择上传超过5个 或者是已经上传和这次上传的到的总数也不可以超过5个
		}
		else if(numUp < 5){
			fileList = validateUp(fileList);
			for(var i = 0;i<fileList.length;i++){
			 var imgUrl = window.URL.createObjectURL(fileList[i]);
			     imgArr.push(imgUrl);

			var _this = new FileReader();
	        _this.readAsDataURL(fileList[i]);
	        _this.onload = function(e) {
	             var $img = $("<img class='up-img up-opcity'>");
		         $img.attr("src",this.result);
		         $img.appendTo($section);
	        };
			 var $section = $("<section class='up-section fl gallery-team loading'>");
			     imgContainer.prepend($section);
			 var $span = $("<span class='up-span'>");
			     $span.appendTo($section);
			
		     var $img0 = $("<img class='close-upimg'>").on("click",function(event){
				    event.preventDefault();
					event.stopPropagation();
					$(".works-mask").show();
					delParent = $(this).parent();
				});   
				$img0.attr("src","/public/image/a7.png").appendTo($section);
				$img0.attr("data-id",imgId);
		     
		     var $p = $("<p class='img-name-p'>");
		         $p.html(fileList[i].name).appendTo($section);
		    
		     var $gallery = '<div class="oper">';
		     	 $gallery = $gallery + '<span class="prev" onclick="gallery(\'prev\', this,'+imgId+')"><i class="fa fa-arrow-left"></i></span>';
		     	 $gallery = $gallery + 	'<span class="next" onclick="gallery(\'next\', this,'+imgId+')"><i class="fa fa-arrow-right"></i></span>';
		     	 $gallery = $gallery + '</div>';
		     	 $($gallery).appendTo($section);
		   }
		}
		setTimeout(function(){
             $(".up-section").removeClass("loading");
		 	 $(".up-img").removeClass("up-opcity");
		 },450);
		 numUp = imgContainer.find(".up-section").length;
		if(numUp >= 5){
			$(this).parent().hide();
		}
		$(".add-img .file").hide();
		$(".z_file").append('<input type="file" name="file[]" id="image'+imgId+'" class="file" accept="image/jpg,image/jpeg,image/png,image/bmp" />');
	});
	
	
   
    $(".z_photo").delegate(".close-upimg","click",function(){
     	  $(".works-mask").show();
     	  delParent = $(this).parent();
	});
		
	$(".wsdel-ok").click(function(){
		$(".works-mask").hide();
		var numUp = delParent.siblings().length;
		if(numUp < 6){
			delParent.parent().find(".z_file").show();
		}
		delParent.remove();
		var id = delParent.find(".close-upimg").attr('data-id');
		var id = id - 1;if(id == 0){id = '';}
     	$("#image"+id).remove();
	});
	
	$(".wsdel-no").click(function(){
		$(".works-mask").hide();
	});
		
	function validateUp(files){
		var arrFiles = [];//替换的文件数组
		for(var i = 0, file; file = files[i]; i++){
			//获取文件上传的后缀名
			var newStr = file.name.split("").reverse().join("");
			if(newStr.split(".")[0] != null){
					var type = newStr.split(".")[0].split("").reverse().join("");
					if(jQuery.inArray(type, defaults.fileType) > -1){
						// 类型符合，可以上传
						if (file.size >= defaults.fileSize) {
							alert(file.size);
							alert('您这个"'+ file.name +'"文件大小过大');	
						} else {
							// 在这里需要判断当前所有文件中
							arrFiles.push(file);	
						}
					}else{
						alert('您这个"'+ file.name +'"上传类型不符合');	
					}
				}else{
					alert('您这个"'+ file.name +'"没有类型, 无法识别');	
				}
		}
		return arrFiles;
	}
})
//相册图片
function gallery(type, _this,id) {
	var id = id - 1;if(id == 0){id = '';}
    if (type == 'prev') {
        if ($(_this).parents('.gallery-team').prev('.gallery-team').length > 0) {
            $(_this).parents('.gallery-team').prev('.gallery-team').before($(_this).parents('.gallery-team'));
            $("#image"+id).next('.file').after($("#image"+id));

        }
    }else if (type == 'next') {
        if ($(_this).parents('.gallery-team').next('.gallery-team').length > 0) {
            $(_this).parents('.gallery-team').next('.gallery-team').after($(_this).parents('.gallery-team'));
            $("#image"+id).prev('.file').before($("#image"+id));
        }
    }
}	
// function upload(){  
//     //触发 文件选择的click事件  
//     $("#file").trigger("click");  
// }
// //单图片文件上传预览
// function show () {
// 	//显示选中图片
// 	var _this = new FileReader();
// 	var file = document.getElementById('file').files[0];
// 	_this.readAsDataURL(file);
// 	_this.onload = function(e) {
// 		$(".logo").attr('src',this.result);
// 	};
// }