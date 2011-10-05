<?php 
function tube_video_get_feed_board(){
	require_once(TUBE_VIDEO_LIB.'/simplepie/simplepie.php');
	global $wpdb;
	$sql = "
		SELECT *
		FROM {$wpdb->tube_video_feeds}
		WHERE id = '".$_POST['id']."'
	";
	$result = $wpdb->get_row($sql);	
	$unique_id = tube_video_get_id($result->urls);
	?>
    <?php
	$sql = "
		SELECT *
		FROM {$wpdb->tube_video_contents}
		WHERE feed_id = '".$result->id."'
	";
	$contents = $wpdb->get_results($sql);
	$total = count($contents);
	?>
    <style>
	
	</style>
    <table width="100%" cellpadding="5" class="rss-board">
    	<tr>
        	<td width="100">RSS</td>
            <td><a href="<?php echo $result->urls;?>" target="_blank"><?php echo $result->urls;?></a></td>
        </tr>
        <tr>
        	<td>Videos</td>
            <td><span id="total_new_<?php echo $unique_id;?>"><?php echo $total;?></span></td>
        </tr>
        <tr>
        	<td></td>
            <td>
            	<div id="result"></div>            
                <ul id="recent-parse-content">
                <?php 
				$query = "
					SELECT *
					FROM {$wpdb->tube_video_contents} 
					WHERE feed_id = {$result->id}
				";
				$results = $wpdb->get_results($query);
				foreach($results as $item){
					$obj = json_decode(base64_decode($item->feed_content));

				?>
                	<li>
                    	<?php echo $obj->title;?><br />
                        <a href="<?php echo $obj->link;?>" rel="prettyPhoto[preview]" title="">
                          	<img src="http://i.ytimg.com/vi/<?php echo $obj->tube_id; ?>/0.jpg" width="100" alt="<?php ?>" />
                        </a>
                    </li>
                <?php
				}
				?>
                </ul>
            </td>
        </tr>
    </table>
   
    <script>
		
		(function($){
			//alert(get_feed_content_callback)	
			//$(document).ready(function(){
				$("a[rel^='prettyPhoto']").prettyPhoto();
			//});
					
		})(jQuery);
	</script>
    <?php
	die();
}
function tube_video_get_new_feeds(){
	require_once(TUBE_VIDEO_LIB.'/simplepie/simplepie.php');
	global $wpdb;
	$json = new stdClass();
	
	$current_time = time();
	$cache_time = 20;
	$query = "
		SELECT count(id) as total
		FROM {$wpdb->tube_video_feeds}
		WHERE last_time <= ".($current_time - $cache_time)."
	";
	$total = $wpdb->get_var($query);
	$json->finish = ($total <= 3) ;
	$json->feeds = array();
	$query = "
		SELECT *
		FROM {$wpdb->tube_video_feeds}
		WHERE last_time <= ".($current_time - $cache_time)."
		LIMIT 0, 3
	";
	/*$query = "
		SELECT *
		FROM {$wpdb->tube_video_feeds}		
	";*/
	$id_update = array();
	
	$result = $wpdb->get_results($query);
	$total = count($result);
	
	for($i = 0, $n = $total; $i < $n; $i++){
		$ret = $result[$i];
		$id_update[] = $ret->id;
		
		$id = tube_video_get_id($ret->urls)."_".$ret->id;
		
						
		$feed = new SimplePie();
		$feed->set_feed_url($ret->urls);
		$feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
		$feed->set_timeout(20);
		$feed->enable_cache(false);
		$feed->set_stupidly_fast(true);
		$feed->enable_order_by_date(false); // we don't want to do anything to the feed
		$feed->set_url_replacements(array());
		
		$query = array();
		$xx = array();
		if($feed->init()){							
			foreach($feed->get_items() as $item){
				$content_id = tube_video_get_id($item->get_permalink());			
				if($exists = tube_video_exists_feed($ret->id, $content_id)){
					$xx[] = $content_id."-exists";
				}else{
					$feed_content = json_encode(tube_video_parse_item_info($item));
					$query[] = "(".$ret->id.",'".$content_id."','".base64_encode($feed_content)."','".date("Y-m-d h:i:s")."', '".$wpdb->escape($item->get_title())."', '".$item->get_date("Y-m-d h:i:s")."')";
					$xx[] = $content_id."-NO exists";
				}
				
			}
		}
		$file = TUBE_VIDEO_DIR."/logs.txt";
		if(count($query)){
			$sql = "INSERT INTO {$wpdb->tube_video_contents}(feed_id, feed_key, feed_content, `created`, title, feed_date) VALUES".implode(",", $query);
			$wpdb->query($sql);
			$bb=$sql;
			$old = file_get_contents($file);
			ob_start();
			$wpdb->print_error();
			if($ret->id==26)
			echo $sql;
			$old=$old."\n\n".ob_get_clean();
			file_put_contents($file, $old);
		}
		$sql = "
			SELECT count(id)
			FROM {$wpdb->tube_video_contents}
			WHERE feed_id = '{$ret->id}'
		";
		
		$content_total = $wpdb->get_var($sql);
		
		$json->feeds[$id] = array(
			'total' => $content_total,
			'url' => $ret->urls,
			'sql'=>$bb,
			'xx'=>$xx
		);
		
	}
	if(count($id_update)){
		$query = "
			UPDATE {$wpdb->tube_video_feeds}
			SET last_time = '".($current_time)."'
			WHERE id IN(".implode(",", $id_update).")
		";
		$wpdb->query($query);
	}
	echo json_encode($json);
	die();
}
function tube_video_get_feed_config($fid){
	global $wpdb;
	$sql = "
		SELECT params
		FROM {$wpdb->tube_video_feeds}
		WHERE id = {$fid}
	";
	$params = $wpdb->get_var($sql);
	//print_r($params);
	return json_decode($params);
}
function tube_video_get_feed($id){
	global $wpdb;
	$query = "
		SELECT *
		FROM {$wpdb->tube_video_feeds}
		WHERE id = {$id}
	";
	if($feed = $wpdb->get_row($query)){
		$feed->params = json_decode($feed->params);
	}else{
		$feed = new stdClass();
	}
	return $feed;
}
function tube_video_get_contents(){
	global $wpdb;
	$feed_id = tube_video_get_request('feed_id');

	$feed_info = tube_video_get_feed($feed_id);

	$try_parse = tube_video_get_request('try_parse');
	$start = tube_video_get_request('start', 0);
	$sql = "
		SELECT *
		FROM {$wpdb->tube_video_contents}
		WHERE parsed = 0
		AND feed_id = {$feed_id}
		LIMIT $start,1
	";
	$result = $wpdb->get_row($sql);
	if(!count($result)) {
		$json = new stdClass();
	
		$json->title = null;
		$json->url =  null;
		$json->success = 0;
		echo json_encode($json);
		die();
	}
	$feed = file_get_contents(TUBE_VIDEO_CACHE.'/'.$feed_id.'/'.$result->content_id.'.cache');
	$feed = json_decode($feed);
	
	$content_result = tube_video_get_addon($feed_info->params->external_addon)->parse($feed->url, $feed_info);
	///
	if(!$try_parse){
		$sql = "
			UPDATE {$wpdb->tube_video_contents}
			SET parsed = '1'
			WHERE id = {$result->id}
		";
		$wpdb->query($sql);
	}
	$json = new stdClass();
	
	$json->title = $feed->title;
	$json->url = $feed->url;
	$json->success = 1;
	$json->start = $start;
	//echo TUBE_VIDEO_CACHE.'/'.$feed_id.'/'.$result->content_id.'.cache';;
	
	tube_video_get_addon($feed_info->params->internal_addon, 'internal')->store($content_result, $feed_info);
	
	echo json_encode($json);
	die();
}
function tube_video_check_rss(){
	//include_once(ABSPATH . WPINC . '/rss.php');
	
	require_once(TUBE_VIDEO_LIB.'/simplepie/simplepie.php');
	//global $wpdb;
	
	$rss_url = tube_video_get_request('rss');
	
	//$rss = fetch_rss( $rss_url );
	//$maxitems = ( $db_yt_maxitems ) ? $db_yt_maxitems : 2;
	//$items = array_slice( $rss->items, 0, $maxitems );
	
	//ob_start();
	$feed = new SimplePie();
	$feed->set_feed_url($rss_url);
	$feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
	$feed->set_timeout(20);
	$feed->enable_cache(false);
	$feed->set_stupidly_fast(true);
	$feed->enable_order_by_date(false); // we don't want to do anything to the feed
	$feed->set_url_replacements(array());
	//$feed->init();
	
	$msg = null;
	$json = new stdClass();
	/*$json->valid = 1;
		$json->title = $rss_url;
		$json->item = $feed->get_items();
		
	*/
	if($feed->init()){
		$json->valid = 1;
		$json->title = $feed->get_title();
		$msg = "valid";
	}else{
		$msg = "Not valid";
		$json->valid = 0;
	}
	echo json_encode($json);
	die();
}
function tube_video_load_internal_params(){
	$addon = tube_video_get_addon(tube_video_get_request('name'), 'internal');
	print_r($addon->loadParamsForm(tube_video_get_feed_config(tube_video_get_request('feed_id'))));
	die();
}