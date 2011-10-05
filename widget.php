<?php
class TubeVideoWidget extends WP_Widget {
    /** constructor */
	private $xxx = 0;
    function TubeVideoWidget() {
        parent::WP_Widget(false, 'Tube Video', array('description'=>'Show and play video from YouTube RSS in widget'));	
		
		
    }
    /**  */
    function widget($args, $instance) {		
        extract( $args );

        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget; 
		if ( $title ){
			echo $before_title . $title . $after_title; 
		}
		$title 			= esc_attr($instance['title']);
		$rss_id 		= ($instance['rss_id']);
        $thumb_width	= $instance['thumb_width'] ? esc_attr($instance['thumb_width']) : 100;
        $thumb_height 	= $instance['thumb_height'] ? esc_attr($instance['thumb_height']) : 100;
		$max_items 		= $instance['max_items'] ? esc_attr($instance['max_items']) : 10;
		$title_click	= $instance['title_click'];
		$image_click	= $instance['image_click'];
		$open_yt_page 	= $instance['open_yt_page'];
		$post_page_id	= $instance['post_page_id'];
		$video_width 	= $instance['video_width'];
		$video_height 	= $instance['video_height'];
				
		$option = get_option("tube_video_widget");
		if(!$option){ 
			$option = new stdClass();
		}
		$key = 'widget_'.md5($widget_id);
		$option->$key = new stdClass();
		$option->$key->pid = $post_page_id;
		$option->$key->video_size = array($video_width, $video_height);
		$option->$key->video_position = $instance['video_position'];
		update_option("tube_video_widget", $option);
		
		global $wpdb;
		$query = "
			SELECT *
			FROM {$wpdb->tube_video_feeds}
			WHERE id = {$rss_id}
		";
		$rss = $wpdb->get_row($query);
		$start = 0;
		if($videos = tube_video_get_videos($rss->urls, $start, $max_items)){
			$uniqid = uniqid("rel");
			$js = array();
			$post_link = get_permalink($post_page_id);
			switch($title_click){
				case 0: // lightbox
				case '':
					$js[] = '$("#'.$key.'-list a.video-title-link").prettyPhoto();';
					break;
				case 1: // post/page
					$js[] = '$("#'.$key.'-list a.video-title-link").click(function(){play($(this).attr("rel"));return false;});';					
					break;
				case 2: // youtube
					break;
			}
			
			switch($image_click){
				case 0: // lightbox
				case '':
					$js[] = '$("#'.$key.'-list a.video-preview").prettyPhoto();';
					break;
				case 1: // post/page
					$js[] = '$("#'.$key.'-list a.video-preview").click(function(){play($(this).attr("rel"));return false;});';
					break;
				case 2: // youtube
					break;
			}
			?>
			<script>		
            jQuery(function($){
            <?php
            if(count($js)){
                echo implode("\n", $js);
            }
            ?>
                var play = function(vid){
                    $("#<?php echo $key;?>")
                    .find("input[name=watch]")
                    .val(vid);
                    $("#<?php echo $key;?>").submit();
                }
            })
            </script>
            
            <form id="<?php echo $key;?>" action="<?php echo $post_link;?>" method="post">
                <input type="hidden" name="watch" value="" />
                <input type="hidden" name="widget" value="<?php echo $key;?>" />
            </form>
            <?php
		
		?>
        <ul class="tube-video-widget" id="<?php echo $key.'-list';?>">
        <?php
			foreach($videos->items as $vid){
				$num = (int)substr_count($vid->stars, "icn_star_full");
				$dec = (int)substr_count($vid->stars, "icn_star_half");
				$average_ratings = $num+$dec/2;
				$tlink = (in_array($title_click, array('', 0, 2)) ? $vid->link : $post_link.'?watch='.$vid->tube_id);
				$ilink = (in_array($image_click, array('', 0, 2)) ? $vid->link : $post_link.'?watch='.$vid->tube_id);
				$target = ($open_yt_page == 'new_window' ? ' target="_blank"' : '');
			?>
            <li class="video-item">
            <div class="item-wrapper">
            	<div class="video-title">
                	<a href="<?php echo $tlink;?>" rel="<?php echo $vid->tube_id;?>" class="video-title-link"<?php echo $target;?>><?php echo $vid->title;?></a>
                    (<a href="<?php echo $vid->category->link;?>" target="_blank"><?php echo $vid->category->name;?></a>)
				</div>
            	<div class="preview">
                    <a href="<?php echo $ilink;?>" class="video-preview"<?php echo $target;?> rel="<?php echo $vid->tube_id;?>">
                    <img src="<?php echo $vid->preview;?>" width="<?php echo $thumb_width;?>" height="<?php echo $thumb_height;?>" />
                    </a>
                    <span class="video-time"><span><?php echo $vid->length;?></span></span>                    
                </div>
                <div class="right">
                	<span class="rating-stars">
                    	<span class="rating-stars-current" style="width:<?php echo $average_ratings*20;?>%;"></span>
                    </span>
                    <p><span><?php echo $vid->ratings;?></span> ratings</p>
                	<p><span><?php echo $vid->views;?></span> views</p>
                </div>
			</div>               
            </li>
            <?php			
			}
		?>
        </ul>
        <?php 
			
		}
		echo $after_widget; 
    }
    /* */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['rss_id'] = $new_instance['rss_id'];
		$instance['thumb_width'] =  $new_instance['thumb_width'];
		$instance['thumb_height'] = $new_instance['thumb_height'];
		$instance['max_items'] = $new_instance['max_items'];
		$instance['title_click'] = $new_instance['title_click'];
		$instance['image_click'] = $new_instance['image_click'];
		$instance['open_yt_page'] = $new_instance['open_yt_page'];
		$instance['post_page_id'] = $new_instance['post_page_id'];
		$instance['video_width'] = $new_instance['video_width'];
		$instance['video_height'] = $new_instance['video_height'];
		$instance['video_position'] = $new_instance['video_position'];
		
		return $instance;
    }
    /**  */
    function form($instance) {		
        $title 			= esc_attr($instance['title']);
		$rss_id 		= ($instance['rss_id']);
        $thumb_width	= $instance['thumb_width'] ? ($instance['thumb_width']) : 100;
        $thumb_height 	= $instance['thumb_height'] ? ($instance['thumb_height']) : 100;
		$max_items 		= $instance['max_items'] ? ($instance['max_items']) : 10;
		$title_click	= $instance['title_click'];
		$image_click	= $instance['image_click'];
		$open_yt_page	= $instance['open_yt_page'];
		$post_page_id	= $instance['post_page_id'];
		$video_width	= $instance['video_width'];
		$video_height	= $instance['video_height'];
		$video_position	= $instance['video_position'];
		
        ?>
        
        <p>
        	<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title'); ?> 
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
		</p>
        <p>
        	<label for="<?php echo $this->get_field_id('rss_id'); ?>"><?php _e('Rss'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('rss_id'); ?>" id="<?php echo $this->get_field_id('rss_id'); ?>" class="widefat">
			<?php
			global $wpdb;
			$sql = "
				SELECT *
				FROM {$wpdb->tube_video_feeds}
			";
			$rss = $wpdb->get_results($sql);
			if($rss) {
				foreach($rss as $r) {
				echo '<option value="'.$r->id.'" ';
				if ($r->id == $rss_id) echo 'selected="selected"';
				echo '>'.$r->title.'</option>'."\n\t"; 
				}
			}
?>
				</select>
</p>
            <p><?php _e('Thumb Size'); ?><br /><label for="<?php echo $this->get_field_id('thumb_width'); ?>"> <input class="widefat" id="<?php echo $this->get_field_id('thumb_width'); ?>" name="<?php echo $this->get_field_name('thumb_width'); ?>" type="text" value="<?php echo $thumb_width; ?>" style="width:50px;" /></label> x <label for="<?php echo $this->get_field_id('thumb_height'); ?>"><input class="widefat" id="<?php echo $this->get_field_id('thumb_height'); ?>" name="<?php echo $this->get_field_name('thumb_height'); ?>" type="text" value="<?php echo $thumb_height; ?>" style="width:50px;" /></label></p>
        	<p>
            	<?php _e('Items show'); ?> <input class="widefat" id="<?php echo $this->get_field_id('max_items'); ?>" name="<?php echo $this->get_field_name('max_items'); ?>" type="text" value="<?php echo $max_items; ?>" />
            </p>
            <p>
            	<?php _e('Title Click'); ?> 
                
                <select name="<?php echo $this->get_field_name('title_click'); ?>" id="<?php echo $this->get_field_id('title_click'); ?>" class="widefat">
                	<option value="0"<?php echo $title_click=='0' ? ' selected="selected"' : '';?>>Play Video in Lightbox</option>
                	<option value="1"<?php echo $title_click=='1' ? ' selected="selected"' : '';?>>Play Video in Post/Page</option>                
                    <option value="2"<?php echo $title_click=='2' ? ' selected="selected"' : '';?>>Link to YouTube</option> 
				</select>
            </p>
            <p>
            	<?php _e('Image Click'); ?>
                <select name="<?php echo $this->get_field_name('image_click'); ?>" id="<?php echo $this->get_field_id('image_click'); ?>" class="widefat">
                	<option value="0"<?php echo $image_click=='0' ? ' selected="selected"' : '';?>>Play Video in Lightbox</option>
                	<option value="1"<?php echo $image_click=='1' ? ' selected="selected"' : '';?>>Play Video in Post/Page</option>   
                    <option value="2"<?php echo $image_click=='2' ? ' selected="selected"' : '';?>>Link to YouTube</option>              
                </select>
            </p>
            <p>
            	<?php _e('Open YouTube Page'); ?>
                <select name="<?php echo $this->get_field_name('open_yt_page'); ?>" id="<?php echo $this->get_field_id('open_yt_page'); ?>" class="widefat">
                	<option value="new_window"<?php echo $open_yt_page=='new_window' ? ' selected="selected"' : '';?>>In New Window</option>
                	<option value="parent_window"<?php echo $open_yt_page=='parent_window' ? ' selected="selected"' : '';?>>Parent Window</option>                </select>
            </p>            
            <p>
            	<?php _e('Post / Page ID'); ?> <input class="widefat" id="<?php echo $this->get_field_id('post_page_id'); ?>" name="<?php echo $this->get_field_name('post_page_id'); ?>" type="text" value="<?php echo $post_page_id; ?>" />
            </p>
            <p>
            	<?php _e('Video Position'); ?> 
                <select name="<?php echo $this->get_field_name('video_position'); ?>" id="<?php echo $this->get_field_id('video_position'); ?>" class="widefat">
                	<option value="0"<?php echo in_array($video_position, array(0, '')) ? ' selected="selected"' : '';?>>Top</option>
                	<option value="1"<?php echo $video_position=='1' ? ' selected="selected"' : '';?>>Bottom</option>                
				</select>
            </p>
            <p>
            	<?php _e('Video Size (width x height)'); ?> <br /><input class="widefat" style="width:50px;" id="<?php echo $this->get_field_id('video_width'); ?>" name="<?php echo $this->get_field_name('video_width'); ?>" type="text" value="<?php echo $video_width; ?>" />
                x 
                <input class="widefat" style="width:50px;" id="<?php echo $this->get_field_id('video_height'); ?>" name="<?php echo $this->get_field_name('video_height'); ?>" type="text" value="<?php echo $video_height; ?>" />
            </p>
        <?php		
    }
    function add_video($content){
		return "xxx";
	}
} // class FooWidget
add_action('widgets_init', create_function('', 'return register_widget("TubeVideoWidget");'));
?>