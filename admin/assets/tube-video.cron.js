// JavaScript Document
(function($){
var cron = {};
cron.getNewFeeds = function(params){
	params = params || {};
	params.success = params.success || function(){};
	
	var request = function(){
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {action: 'tube_video_get_new_feeds'},
			success: function(res){
				for(var pro in res.feeds){	
					var it = res.feeds[pro];
					if(parseInt(it.total)>0){
						$("#title_"+pro).addClass("has-item");
						$("#count_"+pro).html('('+it.total+')');
					}else{
						$("#title_"+pro).removeClass("has-item");
						$("#count_"+pro).html('');	
					}
				}
				if(res.finish)
					params.success();
				else{
					request();
					//alert("xong");	
				}
			}
		});
	}
	request();
}
cron.getContents = function(params, onSuccess, onError){
	paprams = params || {};
	params.success = params.success || function(){};
	params.error = params.error || function(){};
	params.tryParse = params.tryParse || false;
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		dataType: 'json',
		data: {
			action: 'tube_video_get_contents', 
			feed_id: params.feed_id,
			try_parse: params.tryParse,
			start: params.start
		},
		success: function(res){			
			params.success(res);
		},
		error: function(){
			params.error();
		}
	});	
}
window.cron = cron;

$(document).ready(function(){
					   
});

})(jQuery);

function getNewFeeds(params, onSuccess){
	onSuccess = onSuccess || function(){}
	cron.getNewFeeds(params);
	setTimeout(function(){
		getNewFeeds(params);
	}, tubeVideoSettings.auto_check_time*1000);
}
