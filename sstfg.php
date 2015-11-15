<?php
/*
Plugin Name: Small Steps To Feel Good
Description: This plugin adds a SSTFG ticket system to the WordPress site bebooda.org.
Version: 0.1
Author: montera34
Author URI: http://montera34.com
License: GPLv3
*/

// ACTIONS and FILTERS

// Add custom post type
add_action(  'init', 'sstfg_create_post_type', 0 );
// Custom Taxonomies
add_action( 'init', 'sstfg_build_taxonomies', 0 );
// rewrite flush rules
register_activation_hook( __FILE__, 'sstfg_rewrite_flush' );
// Register new user contact Methods: custom profile fields
add_filter( 'user_contactmethods', 'sstfg_extra_user_profile_fields' );

// end ACTIONS and FILTERS

// register post types
function sstfg_create_post_type() {
	// Billet post type
	register_post_type( 'billet', array(
		'labels' => array(
			'name' => __( 'Tickets','sstfg' ),
			'singular_name' => __( 'Ticket','sstfg' ),
			'add_new_item' => __( 'Add ticket','sstfg' ),
			'edit' => __( 'Edit','sstfg' ),
			'edit_item' => __( 'Edit this ticket','sstfg' ),
			'new_item' => __( 'New ticket','sstfg' ),
			'view' => __( 'View ticket','sstfg' ),
			'view_item' => __( 'View this ticket','sstfg' ),
			'search_items' => __( 'Search tickets','sstfg' ),
			'not_found' => __( 'No tickets found','sstfg' ),
			'not_found_in_trash' => __( 'No tickets in the trash','sstfg' )
		),
		'description' => '',
		'has_archive' => false,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'menu_position' => 5,
		//'menu_icon' => get_template_directory_uri() . '/images/quincem-dashboard-pt-badge.png',
		'hierarchical' => true, // if true this post type will be as pages
		'query_var' => true,
		'supports' => array('title', 'editor','author'),
		'rewrite' => array('slug'=>'billet','with_front'=>false),
		'can_export' => true,
		'_builtin' => false,
	));
}

// register taxonomies
function sstfg_build_taxonomies() {
	// Fecha taxonomy
	register_taxonomy( 'sequence', array('billet'), array(
		'hierarchical' => true,
		'label' => __( 'Sequence','sstfg' ),
		'name' => __( 'Sequences','sstfg' ),
		'query_var' => 'sequence',
		'rewrite' => array( 'slug' => 'sequence', 'with_front' => false ),
		'show_admin_column' => true
	) );
}

// rewrite flush rules to init post type and taxonomies
function sstfg_rewrite_flush() {
	sstfg_create_post_type();
	sstfg_build_taxonomies();
	flush_rewrite_rules();
}

// sstfg extra fields in user profile
$extra_fields = array(
//	array(
//		'name' => __('Extra personal information', 'sstfg'),
//		'label' => 'user_mobile',
//		'type' => 'group'
//	),
	array(
		'name' => __('Mobile phone', 'sstfg'),
		'label' => 'user_mobile',
		'type' => 'input'
	),
//	array(
//		'name' => __('Ticket access', 'sstfg'),
//		'label' => 'user_ticket_access',
//		'type' => 'group'
//	),
	array(
		'name' => __('Ticket Access mode', 'sstfg'),
		'label' => 'user_ticket_access_mode',
		'type' => 'radio',
		'options' => array(
			'manual' => __('Manual', 'sstfg'),
			'automatic' => __('Automatic', 'sstfg')
		)
	),
	array(
		'name' => __('Ticket access regularity', 'sstfg'),
		'label' => 'user_ticket_access_regularity',
		'type' => 'radio',
		'options' => array(
			'once' => __('Once a week', 'sstfg'),
			'twice' => __('Twice a week', 'sstfg')
		)
	),
	array(
		'name' => __('Ticket order', 'sstfg'),
		'label' => 'user_ticket_order',
		'type' => 'radio',
		'options' => array(
			'random' => __('Random', 'sstfg'),
			'sequential' => __('Sequential', 'sstfg')
		)
	),
	array(
		'name' => __('Send me the tickets to my email address', 'sstfg'),
		'label' => 'user_ticket_order',
		'type' => 'checkbox'
	)
);

// Register new user contact Methods: custom profile fields
function sstfg_extra_user_profile_fields( $user ) {
	global $extra_fields;
	foreach ( $extra_fields as $ef ) {
	 	$user_fields_method[$ef['label']] = $ef['name'];
	}
	return $user_fields_method;

} // end Register new user contact Methods: custom profile fields
?>
