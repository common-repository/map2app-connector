<?php 



function map2app_admin_init() {
	global $wp_version;
	
	// all admin functions are disabled in old versions
	if ( !function_exists('is_multisite') && version_compare( $wp_version, '3.0', '<' ) ) {

		function map2app_version_warning() {
			echo '
			<div id="map2app-warning" class="updated fade"><p><strong>'.sprintf(__('Map2App Connector %s requires WordPress 3.0 or higher.','map2app'), MAP2APP_VERSION) .'</strong> '.sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress'). '</p></div>
			';
		}
		add_action('admin_notices', 'map2app_version_warning');

		return;
	}

	
	
	if ( is_admin() ){ // admin actions
		
		
		add_action( 'admin_menu', 'add_map2app_menu' );
	} else {
		// non-admin enqueues, actions, and filters
	}
	
	
	
	
}
add_action('init', 'map2app_admin_init');

function register_map2app_settings() { // whitelist options
	register_setting( 'map2app-group', 'map2app_user_key' );
	register_setting( 'map2app-group', 'map2app_api_secret' );
	register_setting( 'map2app-group', 'map2app_album' );
}

function add_map2app_menu(){
	add_action( 'admin_init', 'register_map2app_settings' );
	//create new top-level menu
	add_menu_page(__('Map2App Post Upload','map2app'), __('Map2App','map2app'), 'administrator', __FILE__, 'map2app_page',plugins_url('/images/map2app-favicon.png', __FILE__));
	//add_submenu_page( __FILE__,__('Map2App Plugin Settings','map2app'),__('Settings','map2app'),'administrator',__FILE__.'_settings','map2app_settings_page' );
}

//MAP2APP options page: admin can set map2app userkey and apisecret
function map2app_settings_page() {
	?>
	<div class="wrap">
	
	<h2><?php _e('Map2App Settings','map2app');?></h2>
	
	<form method="post" action="options.php">
	    <?php settings_fields( 'map2app-group' ); ?>
	    <?php do_settings_sections( 'map2app-group' ); ?>
	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row"><?php _e('Map2App Userkey','map2app');?></th>
	        <td><input type="text" name="map2app_user_key" value="<?php echo get_option('map2app_user_key'); ?>" /></td>
	        </tr>
	         
	        <tr valign="top">
	        <th scope="row"><?php _e('Map2App API Secret','map2app');?></th>
	        <td><input type="text" name="map2app_api_secret" value="<?php echo get_option('map2app_api_secret'); ?>" /></td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row"><?php _e('Map2App Image Album','map2app');?></th>
	        <td><input type="text" name="map2app_album" value="<?php echo get_option('map2app_album'); ?>" /></td>
	        </tr>
	        
	    </table>
	    
	    <?php submit_button(); ?>
	
	</form>
	</div>
<?php 
}
	
