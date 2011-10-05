<?php
class gcfManageCategory{
	function __construct(){
		switch(gcf_get_request("task")){
			case 'new':
				require_once("form-category.php");
				break;
			case 'edit':
				$category = $this->get_category(gcf_get_request("id"));
				require_once("form-category.php");
				break;
			case 'save':
				$id = $this->save();
				wp_redirect("admin.php?page=gcf-manage-categories");
				break;
			case 'apply':
				$id = $this->save();
				wp_redirect("admin.php?page=gcf-manage-categories&task=edit&id=".$id);
				break;
			case 'publish':
				$this->set_state(gcf_get_request("id"), 1);
				wp_redirect("admin.php?page=gcf-manage-categories");
				break;
			case 'unpublish':
				$this->set_state(gcf_get_request("id"), 0);
				wp_redirect("admin.php?page=gcf-manage-categories");
				break;
			case 'remove':
				$this->remove(gcf_get_request("id"));
				wp_redirect("admin.php?page=gcf-manage-categories");				
				break;
			case 'cancel':
			default:
				$this->display();
		}
		
	}
	function display(){
		global $wpdb;
		
		$list_table = new WP_List_Table();		
		
		$limit = gcf_get_user_state_from_request('gcf_categories_per_page', 'categories_per_page', 10);	

		if($limit)		
			$page = $list_table->get_pagenum();
		else $page=1;
		
		$obj = $this->get_categories($limit, $page);
	?>
    <style type="text/css">
		.wrap .widefat tr td{
			height:30px;
			vertical-align:middle;
		}
	</style>
    <form method="post" action="admin.php?page=gcf-manage-categories&noheader=true" name="adminForm">
	<div class="wrap">
		<div id="icon-wp-grabcontent" class="icon32"><br /></div>
		<h2><?php _e('Manage Categories', ''); ?></h2>
        <table>
        	<tr>
            	<td>
                Show 
                <select name="categories_per_page" onchange="this.form.submit();">
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
                	<a href="admin.php?page=gcf-manage-categories&task=new" class="button">Add New</a>
                </td>
            </tr>
        </table>
        <table width="100%" class="widefat">
        	<thead>
            	<tr>
                	<th width="30"><?php echo _e("Num");?></th>
                    <th width="30"><input type="checkbox" class="chk-all"></th>
                    <th><?php echo _e("Title");?></th>
                    <th width="40"><?php echo _e("Publish");?></th>
                    <th width="40"><?php echo _e("Created");?></th>
                    <th width="40"><?php echo _e("Remove");?></th>
                </tr>
            </thead>
            <tfoot>
            	<tr>
                	<th><?php echo _e("Num");?></th>
                    <th><input type="checkbox" class="chk-all"></th>
                    <th><?php echo _e("Title");?></th>
                    <th><?php echo _e("Publish");?></th>
                    <th><?php echo _e("Created");?></th>
                    <th><?php echo _e("Remove");?></th>
                </tr>
            </tfoot>
            <tbody>
            <?php
            if($obj->total){
				$i = 0;
				foreach($obj->categories as $k => $v){	
					$publish_link = "admin.php?page=gcf-manage-categories&noheader=true&task=".($v->state ? 'unpublish' : 'publish')."&id=".$v->id;		
					$remove_link = "admin.php?page=gcf-manage-categories&noheader=true&task=remove&id=".$v->id;		
				?>
                <tr>
                	<td align="right"><?php echo ($page-1)*$limit+$i+1;?></td>
                    <td align="center"><input type="checkbox" name="id[]" value="<?php echo $v->id;?>" class="chk-row" /></td>
                    <td><a href="admin.php?page=gcf-manage-categories&task=edit&id=<?php echo $v->id;?>"><?php echo $v->title;?></a></td>
                    <td align="center">
						<a href="<?php echo $publish_link;?>">
                        	<img src="<?php echo GCF_URL;?>/admin/assets/images/<?php echo !$v->state ? 'unpublish-16.png' : 'publish-16.png';?>" border="0" />
                        </a>
                    </td>
                    <td><?php echo date("Y.m.d", strtotime($v->created));?></td>
                    <td align="center">
                    	<a href="<?php echo $remove_link;?>">
                        	<img src="<?php echo GCF_URL;?>/admin/assets/images/remove-16.gif" border="0" />
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
	</div> 
    	<input type="hidden" name="task" value="" />        
    </form>       
    <?php
	}
	function save(){
		global $wpdb, $current_user;
      	get_currentuserinfo();
	  
		$data = gcf_to_object($_POST);
		$data->created = date("Y-m-d h:i:s");
		$data->author = $current_user->ID;
		
		$query = "
			SELECT max(ordering) max_ordering
			FROM {$wpdb->gcf_categories}
		";
		$max_ordering = $wpdb->get_var($query);		
		$data->ordering = $max_ordering++;
		return gcf_update_table($wpdb->gcf_categories, $data);
	}
	function get_category($id){
		global $wpdb;
		$sql = "
			SELECT * FROM {$wpdb->gcf_categories}
			WHERE id = {$id}
		";
		return $wpdb->get_row($sql);
	}
	function get_categories($limit, $page = 1){
		global $wpdb;
		$start = ($page-1)*$limit;
		$sql = "
			SELECT * FROM {$wpdb->gcf_categories}
		";
		$total = count($wpdb->get_results($sql));
		$sql = "
			SELECT * FROM {$wpdb->gcf_categories}
			ORDER BY title ASC
			LIMIT $start, $limit			
		";
		$categories = $wpdb->get_results($sql);
		
		$ret = new stdClass();
		$ret->categories = $categories;
		$ret->total = $total;
		
		return $ret;
	}
	function set_state($id, $state = 0){
		global $wpdb;
		settype($id, "array");
		$sql = "
			UPDATE {$wpdb->gcf_categories}
			SET state = {$state}
			WHERE id IN ('".implode("','", $id)."')
		";
		return $wpdb->query($sql);
	}
	function remove($id){
		global $wpdb;
		settype($id, "array");
		$sql = "
			DELETE FROM {$wpdb->gcf_categories}
			WHERE id IN ('".implode("','", $id)."')
		";
		return $wpdb->query($sql);
	}
}

new gcfManageCategory();