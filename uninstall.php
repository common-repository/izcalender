<?php
global $wpdb;

$e_page_id = $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_name="'.get_option('izc_events_page_slug').'"');

$wpdb->query("DROP TABLE ".$wpdb->prefix . "iz_calender; " );
$wpdb->query("DELETE FROM ".$wpdb->prefix . "posts WHERE ID='".get_option('_EPAGEID')."';");

delete_option(	"izc_events_page_nav_item"	);
delete_option(	"izc_db_version"			);
delete_option(	"izc_events_page_slug"		);
delete_option(	"izc_bgcolor_today"			);
delete_option(	"izc_fontcolor_today"		);
delete_option(	"izc_bgcolor_event"			);
delete_option(	"izc_fontcolor_event"		);
delete_option(	"izc_bgcolor_header"		);
delete_option(	"izc_fontcolor_header"		);
delete_option(	"izc_bgcolor_days"			);
delete_option(	"izc_fontcolor_days"		);
delete_option(	'izc_popup_direction'		);
delete_option(	'_EPAGEID'					);
?>