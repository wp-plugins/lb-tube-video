<?php
if (!session_id())
    session_start();
if(!isset($_SESSION['tube_video_user_state']))
	$_SESSION['tube_video_user_state'] = '';
function tube_video_get_user_state_from_request($key, $request, $default, $type = null){
	$old_state = tube_video_get_user_state( $key );
	$cur_state = (!is_null($old_state)) ? $old_state : $default;
	$new_state = tube_video_get_request($request, null);
	// Save the new value only if it was set in this request
	if ($new_state !== null) {
		tube_video_set_user_state($key, $new_state);
	} else {
		$new_state = $cur_state;
	}
	return $new_state;
}
function tube_video_get_user_state($key, $default = null){
	$states = json_decode($_SESSION['tube_video_user_state']);
	return $states->$key ? $states->$key : $default;
}
function tube_video_set_user_state($key, $value){
	$states = json_decode($_SESSION['tube_video_user_state']);
	if(!states) $states = new stdClass();
	$states->$key = $value;
	$_SESSION['tube_video_user_state'] = json_encode($states);
}
function tube_video_get_request($key, $default = ""){
	return $_REQUEST[$key] ? $_REQUEST[$key] : $default;
}
function tube_video_to_object($array){
	$ret = new stdClass();
	if(is_array($array)){
		if(count($array)){
			foreach($array as $k => $v) {
				if(!is_numeric($k))
					$ret->$k = $v;
			}
		}
		return $ret;
	}else if(is_object($array)){
		return $array;
	}
	return new stdClass();
}
function tube_video_update_table($table, $data){
	global $wpdb;	
	
	$data = tube_video_to_object($data);
	$query = "
		SELECT id
		FROM {$table}
		WHERE id = '".$data->id."'
	";
	$id = $wpdb->get_var($query);
	
	$query = "
		SHOW COLUMNS FROM {$table}
	";
	$result = $wpdb->get_results($query);
	$insertQuery = "";
	$updateQuery = array();
	
	$insertFields = array();
	$insertValues = array();
	
	for($i = 0, $n = count($result); $i < $n; $i++){
		$field = $result[$i]->Field;
		if(isset($data->$field) && $field != 'id'){
			$insertFields[] = $field;
			
			if(is_array($data->$field))
				$val = json_encode($data->$field);
			else $val = $data->$field;
			$insertValues[] = $val;
			
			$updateQuery[] = $field."='".$val."'";
		}
	}
	if($id)
		$query = "UPDATE {$table} SET ".implode(",", $updateQuery) . " WHERE id = '".$id."'";
	else
		$query = "INSERT INTO {$table}(".implode(",", $insertFields).") VALUES('".implode("','", $insertValues)."')";
	
	//die($query);
	$wpdb->query($query);
	return $id ? $id : $wpdb->insert_id;
}
function tube_video_get_id($string, $prefix = 'id'){
	return strtolower($prefix.md5($string));
}
function tube_video_get_cache($key){
	if(file_exists(TUBE_VIDEO_CACHE.'/'.$key.'.cache')){
		return json_decode(file_get_contents(TUBE_VIDEO_CACHE.'/'.$key.'.cache'));
	}
	return null;
}
function tube_video_set_cache($key, $value){
	if($cache = tube_video_get_cache($key)){
	}
}
function tube_video_exists_feed($fid, $id){
	global $wpdb;
	$sql = "
		SELECT id
		FROM {$wpdb->tube_video_contents}
		WHERE feed_id = {$fid} AND feed_key = '{$id}'		
	";
	return count($wpdb->get_results($sql)) ? true : false;
}
function tube_video_get_addon($name, $type = 'external'){
	static $addons;
	if(is_null($addons)) $addons = array('external' => array(), 'internal' => array());
	if(!isset($addons[$type][$name])){
		$addon_dir = TUBE_VIDEO_ADDON."/{$type}/{$name}/{$name}.php";
		if(file_exists($addon_dir)){
			require_once($addon_dir);
		}else{
			$addon = new stdClass();
			$addons[$type][$name] = $addon;
			return $addons[$type][$name];
		}
		$addonName = ucfirst($type) . ucfirst($name);
		$instance = new $addonName;	
		$addons[$type][$name] = $instance;
	}else{
		return $addons[$type][$name];
	}
	return $addons[$type][$name];
}
function tube_video_parse_content($url, $params = null){	
	global $wpdb;
	$addon = 'readability';
	if($params->addon){
		$addon_dir = TUBE_VIDEO_ADDON."/{$params->addon}/{$params->addon}.php";
		if(file_exists($addon_dir)){
			require_once(TUBE_VIDEO_ADDON."/{$params->addon}/{$params->addon}.php");
			$addon = $params->addon;
		}else{
			require_once(TUBE_VIDEO_ADDON.'/readability/readability.php');
		}			
	}else{
		require_once(TUBE_VIDEO_ADDON.'/readability/readability.php');
	}
	$addon = ucfirst($addon) . 'Content';
	$addon = new $addon;	
	
	$html = $addon->parse($url);
	$qry = '';
	// Create post object
	$my_post = array(
		/*'ID' => 12,*/
		'post_author' => 1,
		'post_date' => date("Y-m-d h:i:s"),
		'post_date_gmt' => date("Y-m-d h:i:s"),
		'post_content' => strip_tags($html),
		'post_title' => $feeds->$id->title,
		'post_excerpt' => '',
		'post_status' => 'publish',
		'comment_status' => 'open',
		'ping_status' => 'open',
		'post_password' => '',
		'post_name' => '',
		'to_ping' => null,
		'pinged' => null,
		'post_modified' => date("Y-m-d h:i:s"),
		'post_modified_gmt' => date("Y-m-d h:i:s"),
		'post_content_filtered' => null,
		'post_parent' => 0,
		'guid' => null,
		'menu_order' => 0,
		'post_type' => 'post',
		'post_mime_type' => null,
		'comment_count' => 0,
		'filter' => 'raw'
	);
	return $html;
}
function tube_video_id($url) {
	$url_string = parse_url($url, PHP_URL_QUERY);
	parse_str($url_string, $args);
	return isset($args['v']) ? $args['v'] : false;
}
function tube_video_parse_item_info($item){
	$link = $item->get_permalink();
	$cache = TUBE_VIDEO_CACHE.'/'.md5($link).'.item';
	$cache_time = 30*60;
	$usecache = false;
	if(file_exists($cache)){
		if(time() - fileatime($cache) < $cache_time){
			$usecache = true;
		}
	}
	if(!$usecache){
		$use_dom = 0;
		$return = new stdClass();
	
		$return->title = $item->get_title();
		$return->date = $item->get_date();
		$return->link = $item->get_permalink();
		$return->tube_id = tube_video_id($item->get_permalink());
		$html = $item->get_description();
		
		if($use_dom){
			$doc = new DOMDocument();
			@$doc->loadHTML($html);
		
			$tds = $doc->getElementsByTagName("td");
	
			// try to parse description		
			$return->description = $tds->item(1)->getElementsByTagName("div")->item(1)->nodeValue;
			
			$divs = $tds->item(2)->getElementsByTagName("div");
			
			// try to parse user
			$return->user = new stdClass();
				$a = $divs->item(0)->getElementsByTagName("a")->item(0);
				$return->user->name = $a->nodeValue;
				$return->user->link = $a->getAttribute("href");
			
			// try to parse views
			$return->views = preg_replace("![^0-9]!", "", $divs->item(1)->nodeValue);
			
			// try to parse starts
			$return->stars = preg_replace("!<div.*>|<\/div>!msU", "", $doc->saveXML($divs->item(2)));
							
			// try to parse ratings
			$return->ratings = preg_replace("![^0-9]!", "", $divs->item(3)->nodeValue);
			
			// try to parse length
			$return->length = $tds->item(3)->getElementsByTagName("span")->item(1)->nodeValue;
			
			// try to parse category
			$return->category = new stdClass();
				$a = $tds->item(4)->getElementsByTagName("a")->item(0);
				$return->category->name = $a->nodeValue;
				$return->category->link = $a->getAttribute("href");
		}else{
			$return->description = "";
			
			// try to parse user
			if(preg_match("!From.*href=\"(.*)\">(.*)</a>!msU", $html, $matches)){
				$return->user = new stdClass();
				$return->user->name = $matches[2];
				$return->user->link = $matches[1];
			}
			
			// try to parse views
			if(preg_match_all('!Views.*([0-9]+).*<\/div>!msU', $html, $matches)){
				$return->views = (preg_replace("![^0-9]!", "", $matches[0][0]));
			}
			
			// try to parse stars
			if(preg_match_all('!<img.*>!msU', $html, $matches)){
				$return->stars = '';
				for($n = count($matches[0]), $i = $n - 1; $i >= $n - 5; $i--)
					$return->stars = $matches[0][$i] . $return->stars;
			}
			
			// try to parse ratings
			if(preg_match_all('!>[^>]([0-9]+)(.*)ratings!msU', $html, $matches)){
				preg_match("!>([0-9]+)!", $matches[0][0], $submatches);
				$return->ratings = $submatches[1];
			}
			
			// try to parse length
			if(preg_match("![0-9]?[0-9]:[0-9][0-9]?!", $html, $matches))
				$return->length = $matches[0];
			
			// try to parse category	
			if(preg_match("!More in.*href=\"(.*)\">(.*)</a>!msU", $html, $matches)){
				$return->category = new stdClass();
				$return->category->name = $matches[2];
				$return->category->link = $matches[1];
			}				
		}
		// try to parse preview image
		$return->preview = 'http://i.ytimg.com/vi/'.$return->tube_id.'/0.jpg';	
		
		file_put_contents($cache, json_encode($return));
		///
	}else{
		$return = json_decode(file_get_contents($cache));		
	}
	return $return;	
}
function tube_video_get_videos($rss, $start = 0, $limit = null){
	require_once(TUBE_VIDEO_LIB.'/simplepie/simplepie.php');
	if((int)$rss) {
		global $wpdb;
		$rss = $wpdb->get_var("SELECT urls FROM {$wpdb->tube_video_feeds} WHERE id={$rss}");
	}
	$cache = TUBE_VIDEO_CACHE.'/'.tube_video_get_id($rss, 'rss').'.cache';
	if(file_exists($cache)){
		$content = file_get_contents($cache);
	}else{
		///$content = 
	}
	
	$feed = new SimplePie();
	$feed->set_feed_url($rss);
	$feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
	$feed->set_timeout(20);
	$feed->enable_cache(true);
	$feed->set_cache_duration(1800);
	$feed->set_cache_location(TUBE_VIDEO_CACHE);
	$feed->set_stupidly_fast(true);
	$feed->enable_order_by_date(false); // we don't want to do anything to the feed
	$feed->set_url_replacements(array());
	$ret = new stdClass();
	$ret->items = array();
	if($feed->init()){
		foreach($feed->get_items() as $item){			
			$new_item = tube_video_parse_item_info($item);
			$ret->items[] = $new_item;
		}
	}else{
		
	}
	
	$ret->total = count($ret->items);
	if($limit > 0)
		$ret->items = array_slice($ret->items, $start, $limit);
	else $ret->items = array_slice($ret->items, $start); 
	return $ret;
}
function tube_video_get_rss(){
	global $wpdb;
	$query = "
		SELECT *
		FROM {$wpdb->tube_video_feeds}
	";
	return $wpdb->get_results($query);
}
function tube_video_parse_rss($url){
	$cache = TUBE_VIDEO_CACHE.'/'.tube_video_get_id($url, 'rss').'.cache';
	if(file_exists($cache)){
		$content = file_get_contents($cache);
	}else{
		
	}
}