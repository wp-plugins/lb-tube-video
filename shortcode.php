<?php
//require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
//require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-admin/includes/admin.php');
add_action('admin_init', 'tube_video_fronend_action');
function tube_video_fronend_action(){
	if ( !is_user_logged_in() ) {
    	add_action('wp_ajax_nopriv_tube_video_get_videos_by_rss', 'tube_video_get_videos_by_rss');
	} else {
    	add_action('wp_ajax_tube_video_get_videos_by_rss', 'tube_video_get_videos_by_rss');
    }
}
?>
<?php 

function tube_video_get_videos_by_rss($options = array()){
	$atts = $_POST['settings'] ? $_POST['settings'] : $options['settings'];
	//print_r($_POST);
	$items_per_page = $atts['items_per_page'];
	$current_page = tube_video_get_user_state_from_request('tube_video.current_page', 'current_page',1);//!isset($_POST['page']) ? 1 : $_POST['page'];
	
	
	$rss_id = $options['rss_id'] ? $options['rss_id'] : tube_video_get_user_state_from_request('tube_video.rss_id', 'rss_id', $options['rss_id']);
	$start = ($current_page-1)*$items_per_page;
	$end = $start+$items_per_page;
	$tube = tube_video_get_videos($rss_id);//, ($current_page-1)*$items_per_page, $items_per_page);
	
	$videos = $tube->items;//array_slice($tube->items, ($current_page-1)*$items_per_page, $items_per_page);
	
	tube_video_pagination(
		$tube->total, 
		array(
			'current_page'=>$current_page, 
			'items_per_page' => $items_per_page,
			'rss_id' => $rss_id
		)
	);
	ob_start();
	?>

    <ul id="videos-list" class="videos-list" style="margin-bottom:0;">
	<?php
	$i = 0;
    foreach($videos as $vid){
		if($i >= $start && $i < $end){
	?>
		<li>
		<span class="video-item">
			<span class="preview" onmouseover="TUBEVIDEO.hover(this);" onmouseout="TUBEVIDEO.mouseout(this);" onclick="TUBEVIDEO.play('<?php echo $vid->tube_id;?>');">
				<img src="<?php echo $vid->preview;?>" width="<?php echo $atts['thumb_width'];?>" height="<?php echo $atts['thumb_height'];?>" />
                <span class="play-icon-timelength"><span><?php echo $vid->length;?></span></span>
				<span class="play-icon">
					<span class="play-icon-overlay"></span>
					<span class="play-icon-button"></span>
                    
				</span>
                
			</span>
			<span class="video-title"><?php echo $vid->title;?></span>
			<br>
			<a href="<?php echo $vid->link;?>" rel="prettyPhoto[group_<?php echo $vid->id;?>]" class="lightbox" title="<?php echo $vid->title;?>">Play in Lightbox</a>
		</span>  
        <div class="popup-info" style="display:none;">
            Length:<?php echo $vid->length;?><br />
            Views:<?php echo $vid->views;?><br />
            Ratings:<?php echo $vid->rating_starts;?> <?php echo $vid->ratings;?> ratings<br />
            User:<a href="<?php echo $vid->user->link;?>"><?php echo $vid->user->name;?></a><br />
            Category:<a href="<?php echo $vid->category->link;?>"><?php echo $vid->category->name;?></a>
        </div>              
		</li>
	<?php
		}else{
	?>
    	<li style="display:none;"><a href="<?php echo $vid->link;?>" rel="prettyPhoto[group_<?php echo $vid->id;?>]" class="lightbox">Play in Lightbox</a>
        
        </li>
    <?php
		}
		$i++;
	}
	?>
    </ul>
    <script>
	(function($){
		$(".lightbox").prettyPhoto();
		$.each($('.preview'), function(){
			$(this).CreateBubblePopup( {
				align: 'left',
				innerHtml: $(this).parent().parent().find('.popup-info').html(),//'click to add <br />the product!', 
				innerHtmlStyle: {
					color:'#333', 
					'text-align':'left'
				},
				position: 'top',
				tail: {align: 'left'},
				selectable: true,
				themeName: 	'orange',
				themePath: 	'<?php echo TUBE_VIDEO_URL?>/libraries/bubblePopup/jquerybubblepopup-theme'
			});
		});
	})(jQuery);
	</script>
    <?php
    tube_video_pagination(
		$tube->total, 
		array(
			'current_page'=>$current_page, 
			'items_per_page' => $items_per_page,
			'rss_id' => $rss_id
		)
	);
	$html = ob_get_clean();
	if($_POST['action'] == 'tube_video_get_videos_by_rss'){
		echo $html;
		die();
	}
	return $html;
}
function tube_video_pagination($total, $options){//$current = 1, $items_per_page = 10){
	$total_pages = ($total - $total % $options['items_per_page']) / $options['items_per_page'];
	$total_pages = ($total % $options['items_per_page'] == 0) ? $total_pages : $total_pages + 1;
	echo '<ul class="tube-video-pagination" style="margin-bottom:0;">';
	for($i = 1; $i <= $total_pages; $i++){
		$classes = array();
		if($i == $options['current_page']) $classes[] = 'current';
		if($i == 1) $classes[] = 'first';		
		if($i == $total_pages) $classes[] = 'last';
		echo '<li'.(count($classes) ? ' class="'.implode(" ", $classes).'"' : '').'><a href="javascript:void(0);" onclick="TUBEVIDEO.load_videos('.$options['rss_id'].', '.$i.');">'.($i).'</a></li>';
	}
	echo '</ul>';
}
function lb_tube_video_list($atts){
	$defaults = array(		
		'rss_id' => 0,
		'thumb_width' => 133,
		'thumb_height' => 103,
		'items_per_page' => 10,
		'player_size' => '800|600'
	);
	$atts = shortcode_atts($defaults, $atts);
	$items_per_page = 10;
	$current_page = 1;
	
	$rss_id = tube_video_get_user_state_from_request('tube_video.rss_id', 'rss_id', $atts['rss_id']);
	
	$tube = tube_video_get_videos($rss_id, ($current_page-1)*$items_per_page, $items_per_page);
	$videos = $tube->items;
	$categories = tube_video_get_rss();
	$player_size = explode("|", $atts['player_size']);
	ob_start();
	?>
    <style>
	
	</style>
    <script>
	var ajaxurl = '<?php echo get_bloginfo("siteurl");?>/wp-admin/admin-ajax.php';
	var TUBEVIDEO = {};
	(function($){
		TUBEVIDEO.settings = <?php echo json_encode($atts);?>;		
		TUBEVIDEO.load_videos = function(rss_id, page){
			$("#video-list-container-overlay").css({display:'block'});
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'tube_video_get_videos_by_rss',
					rss_id: rss_id,
					current_page: page || 1,
					settings: TUBEVIDEO.settings
				},
				success: function(res){
					$("#videos-list-container").html(res);
					$("#video-list-container-overlay").css({display:'none'});
				}
			});
		}
		TUBEVIDEO.play = function(tube_id){
			var video = $('#player-container').find('iframe');
			var src = 'http://www.youtube.com/embed/'+tube_id+'?rel=1&amp;autoplay=1';
			if(!video[0]){
				var player_size = TUBEVIDEO.settings.player_size.split("|");
				video = $('<iframe src="'+src+'"></iframe>');				
				video.attr({
					width: player_size[0],
					height: player_size[1],
					frameborder: 'no'
				});
				$('#player-container')
				.html(video);
			}else{
				video.attr({src: src});
			}
		}
		TUBEVIDEO.hover = function(elem){
			//return;
			$(elem).find(".play-icon").animate({opacity: 1},300, function(){
				return;
				var anim = function(elem){
					elem
					.animate({opacity: 0}, 750, function(){
						elem.animate({opacity: 1}, 1500, function(){
							setTimeout(function(){anim(elem)},2000);
						});
					});
				}
				anim($(this).find(".play-icon-button"));
			});
			
		}
		TUBEVIDEO.mouseout = function(elem){
			//return;
			$(elem).find(".play-icon").animate({opacity: 0}).find(".play-icon-button").stop();
		}
		TUBEVIDEO.lightbox = function(elem){
			$.prettyPhoto.open($(elem).attr("href"));
			return false;
		}
		$(document).ready(function(){
			$(".tvl-categories li")
			.click(function(){
				$(".tvl-categories li:not("+$(this).index()+")").removeClass("current");
				$(this).addClass("current");
			});
		});
	})(jQuery);
	</script>
    <div id="tvl-wrapper" class="tvl-wrapper">
    	<div id="player-container" class="player-container" style="width:<?php echo $player_size[0];?>px;height:<?php echo $player_size[1];?>px;">
        	<p>Please select a Video from list below to play</p>
        </div>
        <ul class="tvl-categories" style="margin-bottom:0;">
        <?php
        foreach($categories as $cat){
		?>
        	<li<?php echo $cat->id == $atts['rss_id'] ? ' class="current"' : '';?>><a href="javascript:void(0);" onclick="TUBEVIDEO.load_videos(<?php echo $cat->id;?>);"><?php echo $cat->title;?></a></li>
        <?php
		}
		?>
        </ul>
        <div style="position:relative;">
        <div id="videos-list-container">        
        <?php echo tube_video_get_videos_by_rss(array('rss_id'=>$atts['rss_id'], 'settings'=>$atts));?>
        </div>
        	<div style="" id="video-list-container-overlay"></div>
        </div>
    </div>
<?php
	return ob_get_clean();
}
