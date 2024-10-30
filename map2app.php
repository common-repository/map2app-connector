<?php
/**
 * @package Map2app
 */
/*
Plugin Name: Map2App Connector
Plugin URI: http://poistory.info
Description: A plugin to upload wordpress posts to map2app.com.
Version: 1.0
Author: Poistory S.r.l.
Author URI: http://poistory.it
License: GPL2
*/

/*  
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 
 // Make sure we don't expose any info if called directly
 if ( !function_exists( 'add_action' ) ) {
 	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
 	exit;
 }
 //get global wp db manager
 
 
 define('MAP2APP_VERSION', '0.9');
 define('MAP2APP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
 define('MAP2APP_TEST', false);
 
 load_plugin_textdomain('map2app', false,plugin_dir_path( __FILE__ ) . '/languages' );
 
 if ( is_admin() ){
 	
 	require_once plugin_dir_path( __FILE__ ) . '/admin.php';
 	require_once plugin_dir_path( __FILE__ ) . '/functions/map2app-requests.php';
 	require_once plugin_dir_path( __FILE__ ) . '/map2app-backoffice.php';
 	require_once plugin_dir_path( __FILE__ ) . '/functions/upload_posts.php';
 }
 
 //DB SETTINGS AND FUNCTIONS
 function create_map2app_id_table(){
 	global $wpdb;
 	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 	$querystr="CREATE TABLE ".$wpdb->prefix."map2app_mapping (
 				post_id integer NOT NULL, 
 				map2app_id VARCHAR(255),
 				map2app_category_id VARCHAR(255),
 				PRIMARY KEY  (post_id)
				);";
 	
 	//echo 'create table';
 	dbDelta($querystr);
 	
 	if(!get_option('map2app_album')||get_option('map2app_album')=='')update_option('map2app_album','WP-'.get_bloginfo('name'));
 }
 add_action( 'admin_init', 'create_map2app_id_table' );
 
 //update map2app post status (post is already uploaded?)
 function update_post_status($post_id,$map2app_key,$map2app_category_id){
 	global $wpdb;
 	
 	$querystr='INSERT INTO '.$wpdb->prefix."map2app_mapping (post_id,map2app_id,map2app_category_id) VALUES(%d,%s,%s) ON DUPLICATE KEY UPDATE map2app_id = '%s';";
 	
 	$result = $wpdb->query($wpdb->prepare($querystr,$post_id,$map2app_key,$map2app_category_id,$map2app_key));
 	
 }
 
 function get_post_map2app_status($post_id){
 	global $wpdb;
 	$thepost=$wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."map2app_mapping WHERE post_id = %s",$post_id ));
 	return $thepost;
 }
 
 //register css and js
 function map2app_plugin_admin_init() {
 	/* Register our stylesheet. */
 	wp_register_style( 'map2appStylesheet', plugins_url('map2app.css', __FILE__) );
 	wp_register_script( 'map2app-script', plugins_url('map2app.js', __FILE__)  );
 	
 	wp_enqueue_style( 'map2appStylesheet' );
 	wp_enqueue_script( 'map2app-script' );
 	wp_enqueue_script("jquery");
 	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
 	wp_localize_script( 'map2app-script', 'map2app',
 	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'load_icon'=>plugins_url('/images/loading.gif', __FILE__) ) );
 }
 add_action( 'admin_init', 'map2app_plugin_admin_init' );
 
?>