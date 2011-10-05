<?php
?>
<form method="post" action="admin.php?page=gcf-manage-categories&noheader=true" name="adminForm">
	<div class="wrap">
		<div id="icon-wp-grabcontent" class="icon32"><br /></div>
		<h2><?php gcf_get_request("id", 0) ? _e("Edit Category") : _e("New Category");  ?></h2>
        <table>
        	<tr>
            	<td>
                	<button type="button" class="button" onclick="doTask('apply');">Apply</button>
                    <button type="button" class="button" onclick="doTask('save');">Save</button>
                    <a class="button" href="admin.php?page=gcf-manage-categories">Cancel</a>
                </td>
            </tr>
        </table>
        <div class="metabox-holder">
            <div class="postbox" style="margin:3px;">
            	<h3>General</h3>
                <table>
                	<tr>
                    	<td>Title</td>
                        <td><input type="text" name="title" value="<?php echo $category->title;?>" size="50" /></td>
                    </tr>                    
                    <tr>
                    	<td>Description</td>
                        <td><textarea name="description" cols="100" rows="10"><?php echo $category->description;?></textarea></td>
                    </tr>                    
                </table>
            </div>
		</div>
	</div>        
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $category->id;?>" />
</form>
<script>

</script>