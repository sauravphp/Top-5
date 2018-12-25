<?php
/*
Plugin Name: Top5
Description: A plugin that display most viewed 5 pages links for a particular user (User Must be registerd)
Version: 1.2
Author: Saurav Sharma
Author URI: http://sauravdeveloper.byethost14.com/
License: GPL2
Plugin URL: http://sauravdeveloper.byethost14.com/topfive/
*/
register_activation_hook( __FILE__, 'my_plugin_create_db' );
function my_plugin_create_db() {

	global $wpdb;
	$version = get_option( 'my_plugin_version', '1.0' );
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'topfive';
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id mediumint(9),
		all_pages text,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
wp_enqueue_script('customtheme', plugins_url( '/js/demo.js' , __FILE__ ) , array( 'jquery' ));
wp_localize_script( 'customtheme', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));
function Top_FIve() {
    add_menu_page('TopFive Dashboard', 'TopFive', 'manage_options', 'topfive', 'topfive_options_page','http://www.cetusnews.com/images/skysports.ico');
	add_submenu_page( 'Show Topfive', 'Add Topfive', 'Topfive Add', 'manage_options', 'add-topfive', 'Add_Topfive' );
}
function topfive_options_page(){
    $currentID = get_current_user_id();
	global $wpdb;
	$table_name = $wpdb->prefix . "topfive";
	$getID = $wpdb->get_results( "SELECT id FROM ". $table_name. " where user_id=".$currentID);
	$pageID = get_the_id();
	$page_ids=get_all_page_ids();
	if($getID[0]->id){
		foreach($page_ids as $page)
		{
			if($page==$pageID)
			{ 
				$wpdb->get_var("UPDATE ".$table_name ." SET all_pages=CONCAT(all_pages," .-$page.") WHERE user_id=".$currentID);
			}
		}
	}
	else{
		foreach($page_ids as $page)
		{
			if($page==$pageID)
			{ 
				$topfivearray = array();
				$topfivearray['user_id'] = $currentID;
				$topfivearray['all_pages'] = $page;
				$topfivearray['time'] = date('Y/m/d H:i:s');
				$wpdb->insert($table_name,$topfivearray);
			}
		}
	}
}
add_shortcode( 'TOPFIVE', 'topfive_options_page' );
function Topfive(){
	$currentID = get_current_user_id();
	global $wpdb;
	$table_name = $wpdb->prefix . "topfive";
	$getID = $wpdb->get_results( "SELECT all_pages FROM ". $table_name. " where user_id=".$currentID);
	$getID[0]->all_pages;
	$frontID = '-'.get_option('page_on_front');
	$finalIDs = str_replace($frontID,"",$getID[0]->all_pages);
	$array = explode("-",$finalIDs);
	$reduce = array_count_values($array);
	arsort($reduce);
	//$reduce = array_slice($reduce, 0, 5);
	$i=0;
	echo '<ul>';
	foreach($reduce as $key => $value) {
		$i++;
		if($i<6){
			echo '<li>';
			echo '<a href="'.get_page_link($key).'">'.get_the_title( $key ).'</a>';
			echo '</li>';
		}
	}
	echo '</ul>';
}
add_shortcode( 'TOPFIVE_LINKS', 'Topfive' );