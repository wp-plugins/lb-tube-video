<?php
/*
Plugin Name: LB Tube Video
Plugin URI: http://lessbugs.com
Description: Display and play tube video from youtube RSS
Version: 1.0
Author: Tu Nguyen
Author URI: http://lessbugs.com
*/
class TubeVideo{
	function __construct(){
		require_once("widget.php");
		$this->define_consts();
		$this->define_tables();
		$this->requires();
		
		add_action("admin_head", array($this, 'admin_head'));
		//add_action("admin_footer", array($this, 'admin_footer'));
		add_action("admin_menu", array($this, 'admin_menu'));
		add_action('wp_head', array($this, 'frontend_head'));
		//add_action("wp_footer", array($this, 'frontend_footer_script'));
		$this->add_ajax();
		register_activation_hook(TUBE_VIDEO_FILE, array($this, "on_active"));
		register_deactivation_hook(TUBE_VIDEO_FILE, array($this, "on_deactive"));
		
		add_action('admin_init', array($this, 'localize_script'));
		// shortcode
		//add_shortcode('tube-video', 'lb_tube_video_list');
		add_shortcode('youtube-video-embed', array($this, 'tube_video_embed'));
		
		add_filter('the_content', array($this, 'add_video'), 100);
		
	}
	function tube_video_embed($atts){
		$defaults = array(		
			'watch' => '',
			'width' => 200,
			'height' => 140
		);
		$atts = shortcode_atts($defaults, $atts);
		echo "sadasdasda";
		ob_start();
		print_r($atts);
		?>asda sdasdasda
        <iframe src="http://www.youtube.com/embed/<?php echo $atts['watch'];?>?rel=1&amp;autoplay=1" width="<?php echo $atts['width'];?>" height="<?php echo $atts['height'];?>" frameborder="no"></iframe>
        <?php
		return ob_get_clean();
	}
	function add_video($content){
		global $post;
		$option = get_option('tube_video_widget');
		if(isset($_POST['widget'])){
			$instance 	= $option->$_POST['widget'];
			$size		= $instance->video_size;
			$pos		= $instance->video_position;
			if($instance->pid != $post->ID || get_option('tube_video_play_vide') == 'lightbox') return $content;
			$html = '
				<iframe src="http://www.youtube.com/embed/'.$_POST['watch'].'?rel=1&amp;autoplay=1" width="'.$size[0].'" height="'.$size[1].'" frameborder="no"></iframe>
			';
			return ($pos == 0) ? $html."<br />".$content : $content."<br />".$html;
		}else{
			$html = "";
		}
		return $content;
	}
	function define_consts(){
		define("TUBE_VIEO_CAP", "TUBE_VIDIEO");
		define("TUBE_VIDEO_FILE", __FILE__);
		define("TUBE_VIDEO_DIR", dirname(__FILE__));
		define('TUBE_VIDEO_FOLDER', plugin_basename(TUBE_VIDEO_DIR));
		define("TUBE_VIDEO_TMP_DIR", TUBE_VIDEO_DIR . "/tmp");
		define("TUBE_VIDEO_LANG", TUBE_VIDEO_DIR . "/lang");
		define("TUBE_VIDEO_DOMAIN", "get_content_feed");
		if(!is_dir(TUBE_VIDEO_TMP_DIR)) {
			if (!mkdir(TUBE_VIDEO_TMP_DIR, 777, true)) {
		    	//die('Failed to create folders...');
			}
		}
		define('TUBE_VIDEO_URL', get_bloginfo("siteurl")."/wp-content/plugins/".TUBE_VIDEO_FOLDER);
		define('TUBE_VIDEO_LIB', TUBE_VIDEO_DIR.'/libraries');
		define('TUBE_VIDEO_CACHE', TUBE_VIDEO_DIR.'/cache');
		define('TUBE_VIDEO_ADDON', TUBE_VIDEO_DIR.'/admin/addons');
		//define("TUBE_VIDEO_FEEDS_PER_PAGE")
	}
	function define_tables(){
		global $wpdb;
		$wpdb->tube_video_feeds	 		= $wpdb->prefix . 'tube_video_feeds';
		$wpdb->tube_video_categories		= $wpdb->prefix . 'tube_video_categories';
		$wpdb->tube_video_contents	= $wpdb->prefix . 'tube_video_contents';
	}
	function requires(){
		require_once("includes/functions.php");
		require_once("includes/ajax.php");
		require_once("shortcode.php");
	}
	function add_options(){
		update_option("TUBE_VIDEO_FEEDS_PER_PAGE", 10);
	}
	function remove_options(){
	
	}
	function admin_menu(){
		$role = get_role('administrator');

		add_menu_page( 
			'Tube Video', // page title
			'Tube Video', // menu title
			TUBE_VIDEO_CAP, // cap
			'tube-video-manage-feeds', // slug
			array (&$this, 'show_menu') // function
		);
		add_submenu_page( 
			'tube-video-manage-feeds' , // parent slug
			'Tube Video RSS', // page title 
			'Tube Video RSS', // menu title
			TUBE_VIDEO_CAP, // cap
			'tube-video-manage-feeds', // slug
			array (&$this, 'show_menu') // function
		);
		
		
	}
	function admin_head(){
		?>
        <script type="text/javascript">
			var tubeVideoSettings = {
				pluginurl: '<?php echo TUBE_VIDEO_URL;?>',
				auto_check_time: 20 //seconds
			}
		</script>
        <?php
		$this->admin_styles();
		$this->admin_scripts();
		
	}
	function frontend_head(){
		$this->frontend_styles();
		$this->frontend_scripts();
	}
	function localize_script(){
		//wp_localize_script( 'TUBE_VIDEO_config', 'TUBE_VIDEOConfig', array( 'TUBE_VIDEOurl' => TUBE_VIDEO_URL,  'autochecktime' => '20') );
		//echo 'XXXXXXXXXXXXXXXXXX';
	}
	function admin_scripts(){
		wp_register_script("tube-video-admin-script", TUBE_VIDEO_URL."/admin/assets/tube-video.global.js", array('jquery'));
		wp_register_script("tube-video-admin-ajax", TUBE_VIDEO_URL."/admin/assets/tube-video.ajax.js", array('jquery'));
		wp_register_script("tube-video-prettyPhoto", TUBE_VIDEO_URL."/libraries/prettyPhoto/js/jquery.prettyPhoto.js", array('jquery'));
		
		if(tube_video_get_request("page") == 'tube-video-manage-feeds' && tube_video_get_request("task") == null){
			wp_register_script("tube-video-cron", TUBE_VIDEO_URL."/admin/assets/tube-video.cron.js");
		}
		wp_print_scripts(array(
			'tube-video-admin-script',
			'tube-video-cron',
			'tube-video-admin-ajax',
			'tube-video-prettyPhoto'
		));	
	}
	function admin_styles(){
		wp_register_style("tube-video-admin-style", TUBE_VIDEO_URL."/admin/assets/style.css");
		wp_register_style("tube-video-prettyPhoto", TUBE_VIDEO_URL."/libraries/prettyPhoto/css/prettyPhoto.css");
		wp_print_styles(array(
			'tube-video-admin-style',
			"tube-video-prettyPhoto"
		));	
	}
	function frontend_scripts(){
		wp_register_script("tube-video-prettyPhoto", TUBE_VIDEO_URL."/libraries/prettyPhoto/js/jquery.prettyPhoto.js", array('jquery'));
		wp_register_script("tube-video-bubblePopup", TUBE_VIDEO_URL."/libraries/bubblePopup/js/jquery.bubblepopup.v2.3.1.min.js", array('jquery'));		
		wp_print_scripts(array(
			'tube-video-prettyPhoto',
			'tube-video-bubblePopup'
		));	
	}
	function frontend_styles(){
		wp_register_style("tube-video-prettyPhoto", TUBE_VIDEO_URL."/libraries/prettyPhoto/css/prettyPhoto.css");
		wp_register_style("tube-video-bubblePopup", TUBE_VIDEO_URL."/libraries/bubblePopup/css/jquery.bubblepopup.v2.3.1.css");
		wp_register_style("tube-video-admin-style", TUBE_VIDEO_URL."/assets/style.css");
		wp_print_styles(array(
			"tube-video-prettyPhoto",
			"tube-video-bubblePopup",
			"tube-video-admin-style"
		));	
	}
	function show_menu(){
		switch($_GET['page']){
			case 'tube-video-global-config':
				$this->display();
				break;
			case 'tube-video-manage-feeds':
				require_once("admin/manage-feeds.php");
				break;
			case 'tube-video-manage-categories':
				require_once("admin/manage-categories.php");
				break;
		}
	}
	function on_active(){
		$role = get_role('administrator');
		if(!$role->has_cap(TUBE_VIDEO_CAP)) {
			$role->add_cap(TUBE_VIDEO_CAP);
		}
	}
	function on_deactive(){
		$role = get_role('administrator');
		if($role->has_cap(TUBE_VIDEO_CAP)) {
			$role->remove_cap(TUBE_VIDEO_CAP);
		}
	}
	function add_ajax(){
		add_action('wp_ajax_tube_video_get_new_feeds', 'tube_video_get_new_feeds');
		add_action('wp_ajax_tube_video_get_feed_board', 'tube_video_get_feed_board');
		add_action('wp_ajax_tube_video_get_contents', 'tube_video_get_contents');
		
		add_action('wp_ajax_tube_video_check_rss', 'tube_video_check_rss');
		add_action('wp_ajax_tube_video_load_internal_params', 'tube_video_load_internal_params');
	}
	function display(){
	?>
    <div class="wrap">
		<div id="icon-wp-grabcontent" class="icon32"><br /></div>
		<h2><?php _e('Global Configuration', ''); ?></h2>
	</div>        
    <?php
	}
}
new TubeVideo();