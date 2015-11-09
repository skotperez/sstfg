<?php
/*
Plugin Name: Small Steps To Feel Good
Description: This plugin adds a SSTFG ticket system to the WordPress site bebooda.org.
Version: 0.1
Author: montera34
Author URI: http://montera34.com
License: GPLv3
*/

// Add custom post type
add_action(  'init', 'sstfg_create_post_type', 0 );
// Custom Taxonomies
add_action( 'init', 'sstfg_build_taxonomies', 0 );
// rewrite flush rules
register_activation_hook( __FILE__, 'sstfg_rewrite_flush' );

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

function sstfg_rewrite_flush() {
	sstfg_create_post_type();
	sstfg_build_taxonomies();
	flush_rewrite_rules();
}

