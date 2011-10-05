// JavaScript Document
function doTask(task){
	document.adminForm.task.value = task;
	document.adminForm.submit();
}
function show_feed(container, id){
	var $ = jQuery;
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			'action': 'tube_video_get_feed_board', 
			'id': id,
			'container': container
		},
		success: function(res){
			if(typeof container == 'string'){
				$('#'+container).html(res);
			}else{
				$(container).html(res);
			}
		}
	});
}
jQuery(document).ready(function($){
	var chks = $(".chk-row");
	$.each(chks, function(){
		$(this)
		.click(function(){
			var chkAll = true;
			for(var i=0, n = chks.length; i < n; i++){
				if(!chks[i].checked){chkAll = false;break;}
			};
			$(".chk-all").attr("checked", chkAll);
		});
	});
	$.each($(".chk-all"), function(){
		$(this)
		.click(function(){
			chks.attr("checked", this.checked);
			$(".chk-all").attr("checked", this.checked);
		});
	});
	$(".remove").click(function(){
		var href = $(this).attr("href");
		var id = [];
		$.each(chks, function(){
			if(this.checked) id.push(this.value);
		});
		if(id.length){
			window.location = href+"&id="+id.join(",");
		}else{
			alert("Please select a least 1 item");
		}
		return false;
	});
	
	if(typeof getNewFeeds != 'undefined'){
	/*getNewFeeds({
		success: function(){
			if($(".feed-row.selected").length == 0)
				$(".feed-title:eq(0)").find("a").trigger("click");
		}				
	});*/
	$(".feed-title")
		.find("a")
		.click(function(){
			$(".feed-row").removeClass("selected");
			$(this).parent().parent().parent().addClass("selected");
			show_feed('feed_board', $(this).attr("href"));
			return false;
		});
	}
	if($(".feed-row.selected").length == 0)
		$(".feed-title:eq(0)").find("a").trigger("click");
});

jQuery.extend({
	random: function(X) {
	    return Math.floor(X * (Math.random() % 1));
	},
	randomBetween: function(MinV, MaxV) {
	  return MinV + jQuery.random(MaxV - MinV + 1);
	}
});