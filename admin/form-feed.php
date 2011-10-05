<style type="text/css">
table.form-new td{
	padding:5px;
}
#check_rss_result{
	background-color:#179B1B;
	color:#FFFFFF;
	padding:3px;
	display:none;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
}
#check_rss_result.error{
	background-color:#FF0000;
	color:#FFFFFF;
}
</style>

<form method="post" action="admin.php?page=tube-video-manage-feeds&noheader=true" name="adminForm">
	<div class="wrap">
		<div id="icon-wp-grabcontent" class="icon32"><br /></div>
		<h2><?php tube_video_get_request("id", 0) ? _e("Edit Feed") : _e("New Feed");  ?></h2>
        <table>
        	<tr>
            	<td>
                	<button type="button" class="button" onclick="doTask('apply');">Apply</button>
                    <button type="button" class="button" onclick="doTask('save');">Save</button>
                    <a class="button" href="admin.php?page=tube-video-manage-feeds">Cancel</a>
                </td>
            </tr>
        </table>
        <div class="metabox-holder">
            <div class="postbox" style="margin:3px;">
            	<h3>General</h3>
                <table class="form-new" width="100%">
                	<tr>
                    	<td>Title</td>
                        <td><input type="text" name="title" value="<?php echo $feed->title;?>" size="100" id="rss_title" /></td>                    </tr>                    
                    <tr>
                    	<td>RSS</td>
                        <td>
                        	<input type="text" name="urls" value="<?php echo $feed->urls;?>" size="100" id="rss_url" />
                        	<span id="check_rss_result" style=""></span>
                        </td>
                    </tr>                    
                </table>
            </div>                           
		</div>
	</div>        
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $feed->id;?>" />
</form>
<script>

(function($){
	$("#rss_url")
	.change(function(){
		var rss = $(this).val();
		var check_rss_result = $("#check_rss_result");
		check_rss_result
			.hide()
			.html("Checking...")
			.removeClass("error")
			.show();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'tube_video_check_rss',
				rss: rss
			},
			dataType: 'json',
			success: function(res){
				
				if(typeof res != 'object' || res.valid == 0){
					check_rss_result
						.hide()
						.html("RSS Error")
						.addClass("error")
						.show();
					$("#rss_title").val("");
					return;
				}
				check_rss_result
					.hide()
					.html("RSS Valid")
					.removeClass("error")
					.show();
				$("#rss_title").val(res.title);
			}
		});
	});
})(jQuery);
</script>