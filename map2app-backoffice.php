<?php 

/**
 * Map2App Wordpress Backoffice Functions: upload posts, manage uplodade ones
 */

// MAP2APP post selection page
function map2app_page() {
	try {
	
		
	
	if(MAP2APP_TEST){
		echo '<br/>';
		print_r();
		echo '<br/>';
	}
	//if there're article to store on map2app
	if(!empty($_POST['post_ids'])){
			
		echo '<div id="message" class="updated fade"><p><strong>'.__('We are uploading your content to Map2App.','map2app').'</strong></p></div>';

			//upload selected posts on map2app
			upload_posts();

	}
	// get all wordpress posts
	// WP_Query arguments
	$args = array (
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => '-1'
	);

	// The Query
	$the_query = new WP_Query( $args );


	
	?>
<!-- page wrapper -->
<div class="wrap">
	<h2>
		<?php _e('Sync your posts with Map2App','map2app');?>
	</h2>
	<?php $config_page_url=get_site_url().'/wp-admin/admin.php?page=map2app/admin.php_settings'; ?>
	<?php if(!get_option('map2app_user_key')||!get_option('map2app_api_secret')){ ?><div
		id="message" class="error"><?php _e('Missing  Map2App userKey and/or apiSecret.','map2app'); ?></div><?php } ?>
	<pre>
	<?php 
	
	
	?>
	</pre>
	<!-- Settings form -->
	<div class="wrap"
		style="border-style: solid; border-width: 2px; border-color: gray; padding: 6px">

		<!-- Map2app wordpress settings -->
		<h2><?php _e('Map2App Settings','map2app');?></h2>
		
		<form method="post" action="options.php">
	    <?php settings_fields( 'map2app-group' ); ?>
	    <?php do_settings_sections( 'map2app-group' ); ?>
	    <table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Map2App Userkey','map2app');?></th>
					<td><input type="text" name="map2app_user_key"
						value="<?php echo get_option('map2app_user_key'); ?>" /></td>
					<td rowspan="3">
						<p>In order to connect this plugin to your map2app account you must:</p>
						<ol class="map2app-help">
							<li>Signup to map2app from here: <a target="_blank" href="http://www.map2app.com/signup?utm_source=wordpress&utm_medium=plugin&utm_content=connector&utm_campaign=<?php echo get_bloginfo("name"); ?>">www.map2app.com/signup</a></li>
							<li>Once you are logged in visit <a target="_blank"
								href="http://cms.map2app.com/session/useraccounts/myself?utm_source=wordpress&utm_medium=plugin&utm_content=connector&utm_campaign=<?php echo get_bloginfo("name"); ?>">this
									link</a> to obtain your User key and API key</li>
							<li>Copy your user Key and your API key and paste them here on the left</li>
							<li>Save changes</li>
						</ol>

					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e('Map2App API Secret','map2app');?></th>
					<td><input type="text" name="map2app_api_secret"
						value="<?php echo get_option('map2app_api_secret'); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e('Map2App Image Album','map2app');?></th>
					<td><input type="text" name="map2app_album"
						value="<?php echo get_option('map2app_album'); ?>" /></td>
				</tr>

			</table>
	    
	    <?php submit_button(); ?>
		</form>
	
	</div>
	<!-- post selection form -->
	<div class="post-uploader-wrap" style="clear: both;">
		<form id="map2app" method="post"
			action="<?php admin_url('admin.php?page=map2app/admin.php'); ?>">

			
			<div style="padding-bottom: 65px;">
			
			<?php 
				$map2app_user=get_map2app_user();
				if($map2app_user&&count($map2app_user['languagesBackoffice'])>1){ ?>
			<div class="lang-selector">
				<label><?php _e('It appears that you have more than one language in your map2app account.<br/>
Please select the language you want to add your content to: ','map2app'); ?></label>
				
				<select name="lang" form="map2app" id="lang_selector">
					<?php 
						foreach ($map2app_user['languagesBackoffice'] as $lang){
						?> <option value="<?php echo $lang; ?>"><?php echo $lang; ?></option>
						<?php 
						}		
			?></select>
			</div>
			<?php } ?>
			<div style="float: right"><?php submit_button(__('Upload','map2app')); ?>
						<p class="abort" style="display:none"><input type="button" class="button abort" id="submit" value="Stop Upload"></p>
			</div>
			</div>
			<?php 
			//get post categories to filter articles by category
			$args = array(
					'type'                     => 'post',
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => 1,
					'hierarchical'             => 1,
					'taxonomy'                 => 'category',
					'pad_counts'               => true
			
			);
			
			$categories = get_categories( $args );?>
			<div>
			<div id="table-wrapper" style="width: 100%;height: 100%;position:relative; padding-top:20px;">
			<div id="table-fog" style="height: 100%!important;width: 100%!important;position:absolute!important;background:rgba(100,100,100,.1);display:none;margin-top:10px">
			</div><!-- end table-fog -->
			<div id="table filters" style="clear:both;margin-bottom:12px">
				<div id="category_filter" style="float:left;margin-right: 20px;">
					<label><?php _e('Filter by category: ','map2app'); ?></label>
					<select name="category" id="category_select">
					<option value="all" selected="selected">All</option>
					<?php
						foreach ( $categories as $cat ) {
						?> <option value="<?php echo $cat->slug; ?>"><?php echo $cat->name.' ('.$cat->count.')'; ?></option>
						<?php
						}
		
				?></select>
				</div>
				<div id="upload_filter" style="float:left;margin-right: 20px;">
					<label><?php _e('Filter by status: ','map2app'); ?></label>
					<select name="status" id="status_select">
						<option value="all" selected="selected">All</option>
						<option value="selected">Selected</option>
						<option value="uploaded">Uploaded</option>
						<option value="not_uploaded">Not uploaded</option>
						<option value="error">Error</option>
					</select>
				</div>
				<div style="padding-top:5px;font-size: 14px;">Selected posts: <span class="selected-count">0</span></div>
			</div>

			<table class="form-table">
				<tr bgcolor="#000" class="map2app-header">
					<th class="map2app-checkbox"><input type="checkbox" id="selectall"
						name="check" value="" /></th>
					<th>Title:</th>
					<th>Status:</th>
				</tr>	
			
			<?php 
			// The Loop
			if ( $the_query->have_posts() ) {
				$new_posts='';
				
				$color='9';
				while ( $the_query->have_posts() ) {
					
					$the_query->the_post();
					$map2app_mapping=get_post_map2app_status(get_the_id());
					$uploaded=($map2app_mapping!=null&&$map2app_mapping->map2app_id!=''&&$map2app_mapping->map2app_id!=null)?1:0;
						$post_categories=get_the_category( get_the_id());
						$class_categories='';
						foreach ($post_categories as $cat){
							$class_categories.=' '.$cat->slug;
						}
						$new_posts=$new_posts.'<tr bgcolor="#'.str_repeat ( $color,3).'" class="map2app-row '.($uploaded?'uploaded':'not_uploaded').' '.$class_categories.'">
						<td class="map2app-checkbox" width="5%"><input type="checkbox" name="post_ids[]" class="map2app-post check-'.get_the_ID().'"
						value="'.get_the_ID().'" '.($uploaded?'':'checked="checked"').'>
						</td>
						<td>'.get_the_title().'</td><td width="25%" class="status-'.get_the_ID().'">';
						if($uploaded)
							$new_posts.='<span style="color: green;" class="uploaded">UPLOADED</span>';
						else
							$new_posts.='<span style="color: black;">NOT UPLOADED</span>';
						$new_posts.= '</td>';
					
					$color=($color=='9'?'B':'9');
					}
				?>
				
			
			<?php echo $new_posts;
			
			/* Restore original Post Data */
			wp_reset_postdata();
			?>
			</table>
			
			</div><!-- end table-wrapper -->
			</div>
			<div style="float: right"><?php submit_button(__('Upload','map2app')); ?>
			<p class="abort" style="display:none"><input type="button" class="button abort" id="submit" value="Stop Upload"></p>
			</div>
			
		<?php 

		    	} else {?>
			<h3>
				<?php _e('No Wordpress posts found.','map2app'); ?>
			</h3>
			<?php 
		    	}

		    	?>

	</form>
	</div>
</div>
<!-- page wrapper end-->
<?php 
	}catch(Exception $e) {
		//send an email to know how many posts were uploaded
		$message="Error from site: ".get_site_url()." by map2app user: ".$user['email']."\n"."Caught exception: ".$e->getMessage();
		wp_mail("webmaster@poistory.it","Error in Wordpress to Map2App Upload", $message);
		throw new Exception('error uploading Articles to Map2App Service!');
	}
}


?>
