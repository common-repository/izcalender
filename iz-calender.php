<?php 
/**
 * @package IZ Calender
 * @version 4.0
 */
/*
Plugin Name: IZ Calender
Plugin URI: http://wordpress.org/extend/plugins/izcalender/
Description: IZ-Calender is a user-friendly callender view of events also providing a clean admin interface which enables you to add, update, delete and manage events. Add the calender as a widget to your theme and view upcoming events. 
Author: Paul Cilliers
Version: 4.0
Author URI: www.intisul.co.za
License: GPLv2
*/
global $wpdb;



include_once( dirname(__FILE__) . '/includes/install.php');

if(get_option('izc_db_version')=='1.0'){
	izcalender_install('upgrade');
}

define('_EPAGEID',$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_name="'.get_option('izc_events_page_slug').'"'));


add_option('_EPAGEID',_EPAGEID);
update_option('_EPAGEID',_EPAGEID);

wp_enqueue_script('jquery');
add_action('admin_menu', 'izcalender_menu');

//UI AJAX//
add_action('wp_ajax_nopriv_ui_count_events'			, 'izcalender_ui_count_events'		);
add_action('wp_ajax_nopriv_ui_list_events'			, 'izcalender_ui_list_events'		);
add_action('wp_ajax_nopriv_ui_event_description'	, 'izcalender_ui_event_description'	);
add_action('wp_ajax_nopriv_ui_get_js_calender'		, 'izcalender_user_interface'		);

//UI JS//
wp_register_script('iz-calender-js'	,  WP_PLUGIN_URL . '/izcalender/js/functions.js'	);
wp_enqueue_script ('iz-calender-js');

//UI CSS//
wp_register_style('izcalenderStyle'	,  WP_PLUGIN_URL . '/izcalender/css/iz-calender.css');
wp_enqueue_style ('izcalenderStyle');

if(is_admin())
	{
	//ADMIN AJAX//
	add_action('wp_ajax_ui_list_events'			,	 'izcalender_ui_list_events'		);
	add_action('wp_ajax_ui_event_description'	,	 'izcalender_ui_event_description'	);
	add_action('wp_ajax_ui_count_events'		,	 'izcalender_ui_count_events'		);
	add_action('wp_ajax_ui_get_js_calender'		,	 'izcalender_user_interface'		);
	add_action('wp_ajax_get_fields'				,	 'izcalender_event_creator'			);
	add_action('wp_ajax_my_date_format'			,	 'izcalender_date_format'			);
	
	//ADMIN CSS//
	wp_register_style('adminStyle'  ,  WP_PLUGIN_URL . '/izcalender/css/iz-calender-admin.css');
	wp_enqueue_style ('adminStyle');
	
	wp_register_style('editorStyle'  ,  WP_PLUGIN_URL . '/izcalender/js/editor/jquery.wysiwyg.css');
	wp_enqueue_style ('editorStyle');
	
	//ADMIN JS//
	wp_register_script('iz-calender-js-color-selector'  ,  WP_PLUGIN_URL . '/izcalender/js/jscolor/jscolor.js');
	wp_enqueue_script ('iz-calender-js-color-selector');
	
	wp_register_script('iz-calender-editor-3'  ,  WP_PLUGIN_URL . '/izcalender/js/editor/jquery.wysiwyg.js');
	wp_enqueue_script ('iz-calender-editor-3');
	
	}

//ADMIN MENU
function izcalender_menu() {
	add_menu_page	( 'Calender', 'IZ-Calender', 8, 'iz-calender', 'izcalender_list_events', WP_PLUGIN_URL . '/izcalender/images/izc-icon.png', 199 );
	add_submenu_page( 'iz-calender', 'Add Event','New Event', 8, 'add-event', 'izcalender_admin_interface');
	add_submenu_page( 'iz-calender', 'Settings','Settings', 8, 'izc-settings', 'izcalender_settings');
}

include_once( dirname(__FILE__) . '/includes/admin_interface.php');
include_once( dirname(__FILE__) . '/includes/functions.php');
include_once( dirname(__FILE__) . '/includes/user_interface.php');
include_once( dirname(__FILE__) . '/includes/settings.php');

function IZCalendar(){
	echo	'<div id="ui-izc">';
  	izcalender_user_interface();
		echo 	'<div id="ui-iz-calender-events" class="ui-iz-calender-events" style="display:none; ">
					<div class="close '.((get_option('izc_popup_direction')=='right') ? 'right' : 'left').'"><a href="javascript:closeBox(\'#ui-iz-calender-events\');">X</a></div>
					'.((get_option('izc_popup_direction')=='right') ? '<div class="right arrow-right"></div>' : '').'
					<div class="event-content" id="list-events" ></div>
					'.((get_option('izc_popup_direction')=='left') ? '<div class="left arrow-left"></div>' : '').'
				</div>';
		echo	'<div id="ui-iz-calender-event-description" style="display:none; "><div class="close"><a href="javascript:closeBox(\'#ui-iz-calender-event-description\');">X</a></div>
					<div class="left" id="list-event-description"></div>
					<div class="right"></div>
				</div>';
		echo	'<div id="ui-iz-calender-month"></div>';
	echo	'</div>';
}

function widgetize_izcalender($args) {
    extract($args);
		echo '<div id="izc-widget" class="widget izc-widget">';
			IZCalendar(); 
        echo '</div>'; 
}

function izcalender_widget_control(){
	
	if($_POST['izc_action']=='izc_set_direction')		
		{ 
		update_option('izc_popup_direction',$_POST['izc_popup_direction']); 	
		}
	echo '<h3>Events Popup Direction</h3>';
	echo '<small>Set the direction to which the events should popup.</small><br />';
	echo '<input type="hidden" name="izc_action" value="izc_set_direction">';
	echo '<label><input type="radio" name="izc_popup_direction" value="right" '.((get_option('izc_popup_direction')=='right') ? 'checked="checked"' : '').'>&nbsp;&nbsp;To the Right</label><br />';
	echo '<label><input type="radio" name="izc_popup_direction" value="left" '.((get_option('izc_popup_direction')=='left') ? 'checked="checked"' : '').'>&nbsp;&nbsp;To the Left</label><br /><br />';
}

register_activation_hook(__FILE__,'izcalender_install');
register_deactivation_hook(__FILE__,'izcalender_on_deactivation');

register_sidebar_widget('IZ Calender', 'widgetize_izcalender');
register_widget_control('IZ Calender','izcalender_widget_control');
add_filter( "the_content", "izcalender_get_event_page" );

if(get_option('izc_events_page_nav_item')==0){	
	$output = apply_filters('wp_list_pages ', $output);
	if ( !function_exists('izc_remove_nav_item') ){
		function izc_remove_nav_item($output) {
		return array(_EPAGEID);
		}
	add_filter('wp_list_pages_excludes', 'izc_remove_nav_item');
	}
}


function izcalender_add_dashboard_widgets() {
	
	wp_add_dashboard_widget('example_dashboard_widget', 'IZ Calender', 'izcalender_event_quick_insert');
	
	global $wp_meta_boxes;
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	$example_widget_backup = array('example_dashboard_widget' => $normal_dashboard['example_dashboard_widget']);
	unset($normal_dashboard['example_dashboard_widget']);
	$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;	
} 

function example_remove_dashboard_widgets() {
	global $wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);	
} 

// Hook into the 'wp_dashboard_setup' action to register our function
add_action('wp_dashboard_setup', 'example_remove_dashboard_widgets' );
add_action('wp_dashboard_setup', 'izcalender_add_dashboard_widgets' );

?>