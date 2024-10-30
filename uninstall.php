<?php
//uninstall plugin

	//if uninstall not called from WordPress exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
		
		exit();
	}



	//delete_option( 'map2app_user_key' );
	//delete_option('map2app_api_secret');
	delete_option('map2app_album');
	// For site options in multisite
	//delete_site_option( $option_name );

	//drop a custom db table
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}map2app_mapping" );

//register_uninstall_hook( __FILE__,'uninstall_map2app');