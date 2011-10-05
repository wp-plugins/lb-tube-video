<?php
class TubeVideFeeds{
	function __construct(){
		switch(tube_video_get_request("task")){
			case 'new':
				require_once("form-feed.php");
				break;
			case 'edit':
				$feed = $this->get_feed(tube_video_get_request("id"));
				require_once("form-feed.php");
				break;
			case 'save':
				$id = $this->save();
				wp_redirect("admin.php?page=tube-video-manage-feeds");
				break;
			case 'apply':
				$id = $this->save();
				wp_redirect("admin.php?page=tube-video-manage-feeds&task=edit&id=".$id);
				break;
			case 'publish':
				$this->set_state(tube_video_get_request("id"), 1);
				wp_redirect("admin.php?page=tube-video-manage-feeds");
				break;
			case 'unpublish':
				$this->set_state(tube_video_get_request("id"), 0);
				wp_redirect("admin.php?page=tube-video-manage-feeds");
				break;
			case 'remove':
				$this->remove(tube_video_get_request("id"));
				wp_redirect("admin.php?page=tube-video-manage-feeds");				
				break;
			case 'cancel':
			default:
				$this->display();
		}
		
	}
	function display(){
		global $wpdb;
		
		$list_table = new WP_List_Table();		
		
		$limit = tube_video_get_user_state_from_request('tube_video_feeds_per_page', 'feeds_per_page', 10);	
		if($limit)		
			$page = $list_table->get_pagenum();
		else $page=1;
		
		$obj = $this->get_feeds($limit, $page);
	?>
    <style type="text/css">
		.wrap .widefat tr td{
			height:30px;
			vertical-align:middle;
		}
		.feed-title{}
		.feed-title span{
			margin-left:5px;	
		}
		.feed-title.has-item a{
			font-weight:bold;	
		}
		.wrap .widefat tr.selected td{
			background-color:#F2F2F2;	
		}
		.wrap .widefat tr.selected td .feed-title a{
			color:#d54e21;	
		}
	</style>
    <form method="post" action="admin.php?page=tube-video-manage-feeds" name="adminForm">
	<div class="wrap">
		<div id="icon-wp-grabcontent" class="icon32"><br /></div>
		<h2><?php _e('Manage Feeds', ''); ?></h2>
        <table>
        	<tr>
            	<td>
                Show 
                <select name="feeds_per_page" onchange="this.form.submit();">
                <?php 
					foreach(array(5, 10, 15, 20, 25, 50, 100) as $v){
	                	echo '<option value="' . $v . '"'.($limit == $v ? ' selected="selected"' : '').'>' . $v . '</option>';
					}
				?>
                </select>	
                items
                </td>
                <td>                	
                	<select name="bulk_action" onchange="this.form.btn_bulk_actions.disabled = (this.value == '');">
                    	<option value=""><?php echo _e("Bulk Actions");?></option>
                    	<option value="publish"><?php echo _e("Publish");?></option>
                        <option value="unpublish"><?php echo _e("Unpublish");?></option>
                    	<option value="remove"><?php echo _e("Remove");?></option>
                    </select>
                    <button type="button" class="button-primary" name="btn_bulk_actions" onclick="doTask(this.form.bulk_action.value);" disabled="disabled">Apply</button>
                </td>
                <td>
                	<a href="admin.php?page=tube-video-manage-feeds&task=new" class="button">Add New</a>
                </td>
            </tr>
        </table>
        <table width="100%">
        	<tr><td width="45%" valign="top">
        <table width="100%" class="widefat">
        	<thead>
            	<tr>
                	<th width="30"><?php echo _e("Num");?></th>
                    <th width="30"><input type="checkbox" class="chk-all"></th>
                    <th><?php echo _e("Title");?></th>
                    <th width="40"><?php echo _e("Created");?></th>
                    <th width="20"><?php echo _e("Edit");?></th>                    
                    <th width="40"><?php echo _e("Remove");?></th>
                </tr>
            </thead>
            <tfoot>
            	<tr>
                	<th><?php echo _e("Num");?></th>
                    <th><input type="checkbox" class="chk-all"></th>
                    <th><?php echo _e("Title");?></th>
                    <th><?php echo _e("Created");?></th>
                    <th><?php echo _e("Edit");?></th>                    
                    <th><?php echo _e("Remove");?></th>
                </tr>
            </tfoot>
            <tbody>
            <?php
            
            if($obj->total){
				$i = 0;
				foreach($obj->feeds as $k => $v){	
					$publish_link = "admin.php?page=tube-video-manage-feeds&noheader=true&task=".($v->state ? 'unpublish' : 'publish')."&id=".$v->id;		
					$remove_link = "admin.php?page=tube-video-manage-feeds&noheader=true&task=remove&id=".$v->id;		
					
					$to_id = tube_video_get_id($v->urls).'_'.$v->id;
					
					$videos = tube_video_get_videos($v->urls);		
					
				?>
                <tr class="feed-row" id="feed_<?php echo $to_id;?>">
                	<td align="right"><?php echo ($page-1)*$limit+$i+1;?></td>
                    <td align="center"><input type="checkbox" name="id[]" value="<?php echo $v->id;?>" class="chk-row" /></td>
                    <td>
                    	<div class="feed-title<?php echo $videos->total ? ' has-item' : '';?>" id="title_<?php echo $to_id;?>">
                    	<a href="<?php echo $v->id;?>">
							<?php echo $v->title;?><span id="count_<?php echo $to_id;?>">
                            <?php echo $videos->total ? ' ('.$videos->total.')' : '';?>
                            </span>
                        </a>					
                        </div>	                    
                    </td>                    
                    <td><?php echo date("Y.m.d", strtotime($v->created));?></td>
                    <td align="center">
                    	<a href="admin.php?page=tube-video-manage-feeds&task=edit&id=<?php echo $v->id;?>" title="Edit">
							<img src="<?php echo TUBE_VIDEO_URL;?>/admin/assets/images/edit-16.png" border="0" alt="Edit" />
                        </a>                    	
                    </td>
                    <td align="center">
                    	<a href="<?php echo $remove_link;?>">
                        	<img src="<?php echo TUBE_VIDEO_URL;?>/admin/assets/images/remove-16.gif" border="0" />
						</a>
                    </td>
                </tr>                
                <?php
				$i++;
				}
			}else{
			?>
            <tr>
            	<td colspan="6">No items</td>
            </tr>
            <?php
			}
			?>
            </tbody>
        </table>
        <div class="metabox-holder" style="margin:10px 0;">
            <div class="postbox">
            	<h3>Help Tube Video Development</h3>
                <div style="padding:10px;">    
                <p>
                If you feel this plugin is helpful so please make a donate to help me support you back and continue to developing.
                </p>            
                <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=tunnhn@gmail.com&amp;lc=VN&amp;item_name=lb Tube Video&amp;currency_code=USD&amp;bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">
                	<img src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" alt="Make payments with PayPal - it's fast, free and secure!" border="0" />
                </a>
                </div>
			</div>
        </div>  
        </td>
        <td valign="top" width="55%">
        	<div class="metabox-holder" style="padding-top:0;padding-left:30px;">
            <div class="postbox">
            	<h3>Information</h3>
                <div id="feed_board" style="margin:10px;"></div>
			</div>
            </div>                
        </td>
        </tr>
        </table>
	</div> 
    	<input type="hidden" name="task" value="" />        
    </form>   
    <?php
	}
	function save(){
		global $wpdb, $current_user;
      	get_currentuserinfo();
		$data = tube_video_to_object($_POST);
		
	  	if(count($this->get_feed(tube_video_get_request("id")))){
		
		}else{
			$data->created = date("Y-m-d h:i:s");
			$data->author = $current_user->ID;
		}
			
		
		$query = "
			SELECT max(ordering) max_ordering
			FROM {$wpdb->tube_video_feeds}
		";
		$max_ordering = $wpdb->get_var($query);		
		$data->ordering = $max_ordering++;
		return tube_video_update_table($wpdb->tube_video_feeds, $data);
	}
	function get_feed($id){
		global $wpdb;
		$sql = "
			SELECT * FROM {$wpdb->tube_video_feeds}
			WHERE id = {$id}
		";
		$row = $wpdb->get_row($sql);
		if($row->params) $row->params = json_decode($row->params);
		return $row;
	}
	function get_feeds($limit, $page = 1){
		global $wpdb;
		
		
		$start = ($page-1)*$limit;
		$sql = "
			SELECT f.id
			FROM {$wpdb->tube_video_feeds} f
			GROUP BY f.id
		";
		$total = count($wpdb->get_results($sql));
		$sql = "
			SELECT f.*
			FROM {$wpdb->tube_video_feeds} f
			ORDER BY title ASC
			LIMIT $start, $limit			
		";
		$feeds = $wpdb->get_results($sql);
		
		$ret = new stdClass();
		$ret->feeds = $feeds;
		$ret->total = $total;
		
		return $ret;
	}
	function set_state($id, $state = 0){
		global $wpdb;
		settype($id, "array");
		$sql = "
			UPDATE {$wpdb->tube_video_feeds}
			SET state = {$state}
			WHERE id IN ('".implode("','", $id)."')
		";
		return $wpdb->query($sql);
	}
	function remove($id){
		global $wpdb;
		settype($id, "array");
		$sql = "
			DELETE FROM {$wpdb->tube_video_feeds}
			WHERE id IN ('".implode("','", $id)."')
		";
		return $wpdb->query($sql);
	}
	function get_external_addons($selected = null){
		
		$addons = array(
			'readability' => 'Readability',
			'yahoo' => 'Yahoo'
		);
		ob_start();
	?>
    <select name="params[external_addon]">
    <?php foreach($addons as $key => $name){?>
    	<option value="<?php echo $key;?>"<?php echo $key == $selected ? ' selected="selected"' : '';?>><?php echo $name;?></option>
    <?php }?>
    </select>
    <?php
		$html = ob_get_clean();
		return $html;
	}
	function get_internal_addons($selected = null){
		
		$addons = array(
			'wppost' => 'Wordpress Content',
			'wpecommerce' => 'WP E-Commerce'
		);
		ob_start();
	?>
    <select name="params[internal_addon]" onchange="GCFAjax.loadInternalAddonParams('inernal_addon_params', '<?php echo tube_video_get_request('id');?>', this.value);">
    <?php foreach($addons as $key => $name){?>
    	<option value="<?php echo $key;?>"<?php echo $key == $selected ? ' selected="selected"' : '';?>><?php echo $name;?></option>
    <?php }?>
    </select>
    <?php
		$html = ob_get_clean();
		return $html;
	}
}
$manage_feeds = new TubeVideFeeds();
