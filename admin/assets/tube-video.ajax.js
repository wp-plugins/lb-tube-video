// JavaScript Document
var GCFAjax = {};
(function($){

GCFAjax.loadInternalAddonParams = function(container, feed_id, name){
	if(typeof container == 'string') container = $('#'+container);
	container.html("loading...");
	$.ajax({
		url: ajaxurl,
		data: {
			action: 'load_internal_params',
			name: name,
			feed_id: feed_id
		},
		success: function(res){
			container.html(res);	
		},
		error: function(){
			container.html('error');	
		}
	});
}

})(jQuery);