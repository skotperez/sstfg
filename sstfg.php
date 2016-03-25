<?php
/*
Plugin Name: Small Steps To Feel Good
Description: This plugin adds a SSTFG ticket system to a WordPress site.
Version: 0.2
Author: montera34
Author URI: http://montera34.com
License: GPLv3
Domain Path: /lang/
 */

// VARS
////
require_once('sstfg-config.php');


// ACTIONS and FILTERS
////

// load plugin text domain for string translations
add_action( 'plugins_loaded', 'sstfg_load_textdomain' );

// Add custom post types and taxonomies
add_action(  'init', 'sstfg_create_post_type', 0 );
add_action( 'init', 'sstfg_build_taxonomies', 0 );
register_activation_hook( __FILE__, 'sstfg_rewrite_flush' );

// Adds custom metaboxes to billet post type
add_action("add_meta_boxes_billet", "sstfg_billet_metaboxes");
add_action("save_post", "save_sstfg_billet_metaboxes", 10, 3);

// Register new user contact Methods: custom profile fields
add_filter( 'user_contactmethods', 'sstfg_extra_user_profile_fields' );

// Load script and styles
add_action( 'wp_enqueue_scripts', 'sstfg_register_load_scripts' );

// Hide admin bar to subscribers
add_action('set_current_user', 'sstfg_disable_admin_bar');
// No access to admin panel for subscribers
add_action( 'admin_init', 'sstfg_redirect_admin' );


// SHORTCODES
////

// show edit user options form
add_shortcode('sstfg_user_profile', 'sstfg_form_user_edit_profile');

// conditional sentences depending on subscription type
add_shortcode( 'sstfg_if', 'sstfg_if_subscription_type' );

//  show access to ticket forms
add_shortcode( 'sstfg_tickets_panel', 'sstfg_access_to_tickets_panel' );


// INCLUDES
//// 

// PAGE TEMPLATES CREATOR
include("include/page-templater.php");


// TEXT DOMAIN AND STRING TRANSLATION
function sstfg_load_textdomain() {
	load_plugin_textdomain( 'sstfg', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
}

// Register and load scripts
function sstfg_register_load_scripts() {
	if ( is_page_template('sstfg-form.php') ) {
		wp_enqueue_script(
			'sstfg-js',
			plugins_url( 'js/sstfg.js' , __FILE__),
			array('jquery'),
			'0.1',
			TRUE
		);
	}	
} // end register load map scripts

// Control rights for subscribers
function sstfg_disable_admin_bar() {
	if (!current_user_can('edit_posts')) {
		//add_filter('show_admin_bar', '__return_false');
		show_admin_bar(false);
	}
}
function sstfg_redirect_admin(){
	if ( ! defined('DOING_AJAX') && ! current_user_can('edit_posts') ) {
		wp_redirect( site_url() );
		exit;
	}
}

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
		'menu_icon' => 'dashicons-tickets-alt',
		'hierarchical' => true, // if true this post type will be as pages
		'query_var' => true,
		'supports' => array('title', 'editor','author','page-attributes'),
		'rewrite' => array('slug'=>'billet','with_front'=>false),
		'can_export' => true,
		'_builtin' => false,
	));
}

// register taxonomies
function sstfg_build_taxonomies() {
	// Base sequence taxonomy
	register_taxonomy( 'sequence-base', array('billet'), array(
		'labels' => array(
			'name' => __( 'Base Sequences','sstfg' ),
			'singular_name' => __( 'Base Sequence','sstfg' ),
			'search_items' => __( 'Search Base Sequences','sstfg' ),
			'all_items' => __( 'All Base Sequences','sstfg' ),
			'parent_item' => __( 'Parent Base Sequence','sstfg' ),
			'parent_item_colon' => __( 'Parent Base Sequence:','sstfg' ),
			'edit_item' => __( 'Edit Base Sequence','sstfg' ),
			'update_item' => __( 'Update Base Sequence','sstfg' ),
			'add_new_item' => __( 'Add new Base Sequence','sstfg' ),
			'new_item_name' => __( 'Name of the new Base Sequence' ),
			'menu_name' => __( 'Base Sequence','sstfg' )
		),
		'hierarchical' => true,
		'query_var' => 'sequence-base',
		'rewrite' => array( 'slug' => 'sequence-base', 'with_front' => false ),
		'show_admin_column' => true
	) );
	//  sequence composse taxonomy
	register_taxonomy( 'sequence-composee', array('billet'), array(
		'labels' => array(
			'name' => __( 'Mixed Sequences','sstfg' ),
			'singular_name' => __( 'Mixed Sequence','sstfg' ),
			'search_items' => __( 'Search Mixed Sequences','sstfg' ),
			'all_items' => __( 'All Mixed Sequences','sstfg' ),
			'parent_item' => __( 'Parent Mixed Sequence','sstfg' ),
			'parent_item_colon' => __( 'Parent Mixed Sequence:','sstfg' ),
			'edit_item' => __( 'Edit Mixed Sequence','sstfg' ),
			'update_item' => __( 'Update Mixed Sequence','sstfg' ),
			'add_new_item' => __( 'Add new Mixed Sequence','sstfg' ),
			'new_item_name' => __( 'Name of the new Mixed Sequence' ),
			'menu_name' => __( 'Mixed Sequence' )
		),
		'hierarchical' => true,
		'query_var' => 'sequence-composee',
		'rewrite' => array( 'slug' => 'sequence-composee', 'with_front' => false ),
		'show_admin_column' => true
	) );

}

// rewrite flush rules to init post type and taxonomies
function sstfg_rewrite_flush() {
	sstfg_create_post_type();
	sstfg_build_taxonomies();
	flush_rewrite_rules();
}

// Adds custom metaboxes to billet post type
function sstfg_billet_metaboxes_callback($object) {
	$terms = get_terms('sequence-composee','hide_empty=0');
	if ( !is_array($terms) )
		return;

	wp_nonce_field(basename(__FILE__), "sstfg-billet-order-nonce"); 
	foreach ( $terms as $t ) {
		$cf_label = sprintf(__("Order in sequence %s","sstfg"),$t->name);
		$cf_id = "sstfg_billet_order_".$t->slug; ?>
			<div>
				<label for="<?php echo $cf_id; ?>"><?php echo $cf_label; ?></label>
				<input name="<?php echo $cf_id; ?>" type="text" value="<?php echo get_post_meta($object->ID, $cf_id, true); ?>" />
			</div>
	<?php }
}

function sstfg_billet_metaboxes() {
	add_meta_box('sstfg_billet_orders', __("Order of this ticket for each sequence","sstfg"), "sstfg_billet_metaboxes_callback", "billet", "normal", "high");
}

function save_sstfg_billet_metaboxes($post_id, $post, $update) {
	if ( !isset($_POST["sstfg-billet-order-nonce"]) || !wp_verify_nonce($_POST["sstfg-billet-order-nonce"], basename(__FILE__)) )
		return $post_id;
	if( !current_user_can("edit_post", $post_id))
		return $post_id;

	if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
		return $post_id;

	if('billet' != $post->post_type )
		return $post_id;

	$terms = get_terms('sequence-composee','hide_empty=0');
	if ( !is_array($terms) )
		return;

	foreach ( $terms as $t ) {
		$cf_label = sprintf(__("Order in sequence %s","sstfg"),$t->name);
		$cf_id = "sstfg_billet_order_".$t->slug; 

		if( isset($_POST[$cf_id]) ) {
			$cf_value = sanitize_text_field($_POST[$cf_id]);
			update_post_meta($post_id, $cf_id, $cf_value);
		}
	}   
}
// END Adds custom metaboxes to billet post type

// sstfg extra fields in user profile
function sstfg_user_extra_fields() {
$extra_fields = array(
	array(
		'name' => __('Subscription','sstfg'),
		'label' => 'sstfg_subscription',
		'type' => 'input',
		'initial' => '1',
		'show_in_frontend' => '0'
	),
	array(
		'name' => __('Current sequence','sstfg'),
		'label' => 'sstfg_current_sequence',
		'type' => 'input',
		'initial' => 'Découverte',
		'show_in_frontend' => '2'
	),
	array(
		'name' => __('Ticket Access mode','sstfg'),
		'label' => 'sstfg_ticket_access_mode',
		'type' => 'radio',
		'options' => array(
			'manual' => __('Manual','sstfg'),
			'automatic' => __('Automatic','sstfg')
		),
		'initial' => 'manual',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Ticket access regularity','sstfg'),
		'label' => 'sstfg_ticket_access_regularity',
		'type' => 'radio',
		'options' => array(
			'daily' => __('Daily','sstfg'),
			'weekly' => __('Weekly','sstfg'),
			'biweekly' => __('Every two weeks','sstfg')
		),
		'initial' => '',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Ticket order','sstfg'),
		'label' => 'sstfg_ticket_order',
		'type' => 'radio',
		'options' => array(
			'rand' => __('Random','sstfg'),
			'menu_order' => __('Sequential','sstfg')
		),
		'initial' => 'menu_order',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Send me the tickets to my email address','sstfg'),
		'label' => 'sstfg_ticket_send_to_mail',
		'type' => 'checkbox',
		'initial' => '',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Gotten Tickets','sstfg'),
		'label' => 'sstfg_tickets',
		'type' => 'input',
		'initial' => '',
		'show_in_frontend' => '0'
	)
);
	return $extra_fields;
}
	
// Register new user contact Methods: custom profile fields
function sstfg_extra_user_profile_fields( $user ) {
	$extra_fields = sstfg_user_extra_fields();
	foreach ( $extra_fields as $ef ) {
		$user_fields_method[$ef['label']] = $ef['name'];
	}
	return $user_fields_method;

} // end Register new user contact Methods: custom profile fields


// edit user profile form
function sstfg_form_user_edit_profile($atts){
	extract( shortcode_atts( array(
		'login_page_url' => SSTFG_LOGIN_URL,
	), $atts ));
	if ( !is_user_logged_in() ) {
		wp_redirect($login_page_url); exit;
	}

	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );

	if ( $user_subscription != '1' && $user_subscription != '1.5' && $user_subscription != '2' ) {
		wp_redirect($login_page_url); exit;
	}

	$action = get_permalink();
	$extra_fields = sstfg_user_extra_fields();

	// if edit profile form has been sent
	if ( array_key_exists('wp-submit',$_POST) ) {
	
		//$email = sanitize_text_field($_POST['user_email']);

		foreach ( $extra_fields as $ef ) {
			if ( $ef['show_in_frontend'] == '1' && $ef['label'] != 'sstfg_current_sequence' ) {
				$$ef['label'] = sanitize_text_field($_POST[$ef['label']]);
				$fields_to_update[$ef['label']] = $$ef['label'];
			}
		}

		$fields_to_update['ID'] = $user_id;
		$ticket_access_mode_old = get_user_meta($user_id,'sstfg_ticket_access_mode',true);
		$ticket_access_regularity_old = get_user_meta($user_id,'sstfg_ticket_access_regularity',true);
		$updated_id = wp_update_user( $fields_to_update );

		$redirect_params = "?edit_profile=success";
		if ( $ticket_access_mode_old == 'manual' && $fields_to_update['sstfg_ticket_access_mode'] == 'automatic' ) {
			// add schedule event using wp-cron
			sstfg_scheduled_access_to_tickets($user_id,'daily');
			update_user_meta( $user_id,'sstfg_ticket_access_regularity','daily' );
			$redirect_params = "?edit_profile=scheduled";
		} elseif ( $ticket_access_mode_old == 'automatic' && $fields_to_update['sstfg_ticket_access_mode'] == 'manual' ) {
			sstfg_unscheduled_access_to_tickets($user_id);
			update_user_meta( $user_id,'sstfg_ticket_access_regularity','' );
			$redirect_params = "?edit_profile=unscheduled";
		} elseif ( $fields_to_update['sstfg_ticket_access_mode'] == 'automatic' && $fields_to_update['sstfg_ticket_regularity'] != $ticket_access_regularity_old && $ticket_access_regularity_old != '' ) {
			sstfg_unscheduled_access_to_tickets($user_id);
			sstfg_scheduled_access_to_tickets($user_id,$fields_to_update['sstfg_ticket_regularity']);
		}

		wp_redirect(get_permalink().$redirect_params);
		exit;

	} // end if edit profile form has been sent

	else {
		if ( array_key_exists('edit_profile',$_GET) && sanitize_text_field($_GET['edit_profile']) == 'success' ) {
			$feedback_type = "success"; $feedback_text = __('Settings for your SSTFG subscription has been updated.','sstfg');
		} elseif ( array_key_exists('edit_profile',$_GET) && sanitize_text_field($_GET['edit_profile']) == 'scheduled' ) {
			$feedback_type = "info"; $feedback_text = __('You have changed the way you get your SSTFG tickets: from now on you will receive your tickets daily in your mailbox. Remember you can get your ticket manually too using the button above.','sstfg');
		} elseif ( array_key_exists('edit_profile',$_GET) && sanitize_text_field($_GET['edit_profile']) == 'unscheduled' ) {
			$feedback_type = "info"; $feedback_text = __('You have changed the way you get your SSTFG tickets: from now on you won\'t receive anymore tickets automatically in your mailbox. Remember you still can get them manually using the button above.','sstfg');

		} else { $feedback_type = ''; }

		if ( $feedback_type != '' ) { 
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";
		} else { $feedback_out = ''; }

		$username = $current_user->user_login;
		$email = $current_user->user_email;
		foreach ( $extra_fields as $ef ) {
			$$ef['label'] = $current_user->$ef['label'];
		}

	}

	$extra_output = "";
	foreach ( $extra_fields as $ef ) {
		if ( $ef['show_in_frontend'] != '0' ) {
			if ( $ef['show_in_frontend'] == '2' ) { $disabled = " disabled"; } else { $disabled = ""; }
			if ( $ef['type'] == 'input' ) {
				$extra_output .= "
					<label for='".$ef['label']."'><strong>".$ef['name']."</strong></label>
					<input id='".$ef['label']."' type='text' value='".$$ef['label']."' name='".$ef['label']."'".$disabled." />
				";
	
			} elseif ( $ef['type'] == 'radio' ) {
				$options_out = "";
				foreach ( $ef['options'] as $k => $v ) {
					if ( $user_subscription == '1' && $ef['label'] == 'sstfg_ticket_order' && $k == 'rand' ) {
						// if user subscription is decouverte then deactivate random mode
						$disabled_out = " disabled"; $help_out = "<p class='help-block col-sm-4'><small>".__('Random mode is not available in this type of subscription.')."</small></p>";} else { $disabled_out = ""; }
					if ( $$ef['label'] == $k ) { $checked_out = " checked"; } else { $checked_out = ''; }
					$options_out .= "<label><input type='radio' name='".$ef['label']."' id='".$k."' value='".$k."'".$checked_out.$disabled_out.$disabled." /> ".$v."</label>";
				}
				if ( !isset($help_out) ) { $help_out = ""; }
				$extra_output .= "
					<fieldset class='".$ef['label']."'>
						<strong>".$ef['name']."</strong>
						<div class='radio'>".$options_out."</div>
						".$help_out."
					</fieldset>
				";
				unset($help_out);
	
			} elseif ( $ef['type'] == 'checkbox' ) {
				if ( $$ef['label'] != '' ) { $checked_out = " checked"; } else { $checked_out = ''; }
				$extra_output .= "
					<fieldset class='checkbox ".$ef['label']."'>
						<label>
							<input type='checkbox' name='".$ef['label']."' id='".$ef['label']."' value='please'".$checked_out.$disabled." /> <strong>".$ef['name']."</strong>
						</label>
					</fieldset>
				";
	
			}
		}
	}

	$form_out = $feedback_out. "
	<form class='row-fluid' name='edit_profile_form' action='".$action."' method='post'>
		<fieldset class='span4'>
			<label for='user_login'><strong>".__('Username','sstfg')."</strong></label>
			<input id='user_login' class='form-control' type='text' value='".$username."' name='user_login' disabled='disabled' />
			<label for='user_email'><strong>".__('Email','sstfg')."</strong></label>
			<input id='user_email' class='form-control' type='text' value='".$email."' name='user_email' disabled='disabled' />
		</fieldset>
		<fieldset class='span4'>
		".$extra_output."
		</fieldset>
		<fieldset class='span4'>
			<input id='wp-submit' class='sbutton square noshadow medium btn-info' type='submit' value='".__('Update','sstfg')."' name='wp-submit' />
		</fieldset>
	</form>
	";
	return $form_out;

} // end edit user profile form

// upgrade subscription type
function sstfg_decouverte_to_approfondissement($user_id,$user_subscription) {
	if ( $user_subscription != 1.5 )
		return;

	update_user_meta($user_id,'sstfg_subscription','2');
	update_user_meta($user_id,'sstfg_current_sequence','Approfondissement');
	update_user_meta($user_id,'sstfg_ticket_order','rand');

	$sstfg_url = get_permalink();
	$user_data = get_userdata( $user_id );
	$to = $user_data->user_email;
	$subject = __('Your SSTFG subscription has been updated','sstfg');
	$message = 
"<p>".__('Hi','sstfg')." ".$user_data->user_login.",</p>"
. "\r\n\r\n" .
"<p>".__('You have upgraded your SSTFG subscription from Decouverte to Approfondissement.','sstfg')."</p>"
. "\r\n\r\n" .
"<p>".__('Now you can access to the whole tickets. In this Approfondissement phase you can choose the way you get your tickets: sequentially or randomly.','sstfg')."</p>"
. "\r\n\r\n" .
"<p>".__('You can access your tickets and change your subscription settings here:','sstfg')." <a href='".$sstfg_url."'>".$sstfg_url."</a></p>"
. "\r\n\r\n" .
"<p>".__('Enjoy your tickets!','sstfg')
. "\r\n" .
"<br />Bebooda.</p>"
;
		$headers[] = 'From: Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
//		$headers[] = 'Sender: Bebooda Notification System <no-reply@bebooda.org>' . "\r\n";
		$headers[] = 'Reply-To:  Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
//		$headers[] = 'To: <' .$to. '>' . "\r\n";
		// To send HTML mail, the Content-type header must be set, uncomment the following two lines
		$headers[]  = 'MIME-Version: 1.0' . "\r\n";
		$headers[] = 'Content-type: text/html; charset=utf-8' . "\r\n";

		wp_mail( $to, $subject, $message, $headers);

} // upgra subscription type

// adds content to a page depending on subscription type
function sstfg_if_subscription_type( $atts, $content = null ) {
	if ( !is_user_logged_in() ) { $user_subscription = 0; }
	else {
		$user_id = get_current_user_id();
		$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );
	}

	extract( shortcode_atts( array(
		'subscription' => '',
	), $atts ));
	if ( array_key_exists('upgrade',$_GET) && sanitize_text_field($_GET['upgrade']) == 'approfondissement' ) {
		sstfg_decouverte_to_approfondissement($user_id,$user_subscription);
		wp_redirect(get_permalink()); exit;
	}
	$subscriptions = explode(",",$subscription);
	foreach ( $subscriptions as $s ) {
		if ( $s == $user_subscription )
			return $content ;
	}
	return;
} // end adds content to a page depending on subscription type

function sstfg_send_ticket($user_id,$ticket_name,$ticket_url) {
	$ticket_url = get_home_url().$ticket_url;
	$user_data = get_userdata( $user_id );
	$to = $user_data->user_email;
	$subject = __('Your new ticket:','sstfg')." ".$ticket_name;
	$message = 
"<p>".__('Hi','sstfg')." ".$user_data->user_login.",</p>"
. "\r\n\r\n" .
"<p>".__('Here you have the link to download your new SSTFG ticket:','sstfg')."</p>"
. "\r\n\r\n" .
"<p><a href='".$ticket_url."'>".$ticket_name."</a></p>"
. "\r\n\r\n" .
"<p>".__('If you have problems to access this ticket with the link above, copy and paste the following link in your browser address bar:','sstfg')." <a href='".$sstfg_url."'>".$sstfg_url."</a></p>"
. "\r\n\r\n" .
"<p>".$ticket_url."</p>"
. "\r\n\r\n" .
"<p>".__('Remember that you need to log in first in order to download any ticket.','sstfg')
. "\r\n\r\n" .
"<p>".__('Enjoy your ticket!','sstfg')
. "\r\n" .
"<br />Bebooda.</p>"
;
	$headers[] = 'From: Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
//	$headers[] = 'Sender: Bebooda Notification System <no-reply@bebooda.org>' . "\r\n";
	$headers[] = 'Reply-To:  Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
//	$headers[] = 'To: <' .$to. '>' . "\r\n";
	// To send HTML mail, the Content-type header must be set, uncomment the following two lines
	$headers[]  = 'MIME-Version: 1.0' . "\r\n";
	$headers[] = 'Content-type: text/html; charset=utf-8' . "\r\n";

	wp_mail( $to, $subject, $message, $headers);

}

// get ticket
function sstfg_get_ticket($user_id,$new_or_last) {
	$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );
	$user_sequence_name = get_user_meta( $user_id,'sstfg_current_sequence', true );
		$user_sequence_data = get_term_by( 'name',$user_sequence_name,'sequence-composee','ARRAY_A' );
		$user_sequence = $user_sequence_data['slug'];
	$user_mode = get_user_meta( $user_id,'sstfg_ticket_order', true );
		if ( $user_mode == 'menu_order' ) { $user_mode = "meta_value_num menu_order"; }
	$user_tickets = get_user_meta( $user_id,'sstfg_tickets', true );
	$user_send_me_tickets = get_user_meta( $user_id,'sstfg_ticket_send_to_mail', true );
	// LAST
	if ( $new_or_last == 'last' ) {
		$text_out = __('Your last ticket','sstfg');
		if (is_array($user_tickets)) {
			$last_ticket = end($user_tickets);
			$args = array(
				'post_type' => 'billet',
				'posts_per_page' => '1',
				'p' => $last_ticket['ID']
			);
		}
	}

	// NEW
	elseif ( $new_or_last == 'new' || $new_or_last == 'scheduled' ) {
		$text_out = __('Your new ticket','sstfg');
		if ( $user_subscription == '1.5' )
			return;
//			return "<p class='alert alert-info' role='alert'>".__('You have finish the Decouverte sequence: to get more tickets <a href="/user-panel">you must change your subscription</a>.','sstfg')."</p>";

		if (is_array($user_tickets)) {
			foreach ( $user_tickets as $ut ) { $user_tickets_id[] = $ut['ID']; }
		} else { $user_tickets_id = ""; }

		// get all tickets in current phase
		$args = array(
			'post_type' => 'billet',
			'posts_per_page' => '-1',
			'tax_query' => array(
				array(
					'taxonomy' => 'sequence-composee',
					'field'    => 'slug',
					'terms'    => $user_sequence,
				),
			)
		);
		$tickets = get_posts($args);
		$count_all_tickets = count($tickets);

		if ( $user_subscription == '1' || $user_subscription == '2' ) {
			// get ticket to serve now
			$args = array(
				'post_type' => 'billet',
				'posts_per_page' => '1',
				'orderby' => $user_mode,
				'order' => 'ASC',
				'tax_query' => array(
					array(
						'taxonomy' => 'sequence-composee',
						'field'    => 'slug',
						'terms'    => $user_sequence,
					),
				)
			);
			if ( $user_mode == 'meta_value_num menu_order' ) {
				$args['post__not_in'] = $user_tickets_id;
				$args['meta_key'] = 'sstfg_billet_order_'.$user_sequence;
			}

		}
	} // end NEW or LAST

	$tickets = get_posts($args);
	$count = count($tickets);
	if ( $count == 1 ) {
		//update user list of tickets
		foreach ( $tickets as $t ) {
			$user_tickets[] = array(
				'ID' => $t->ID,
				'date' => time()
			);
			if ( $new_or_last == 'new' || $new_or_last == 'scheduled' ) {
				update_user_meta( $user_id,'sstfg_tickets',$user_tickets);
			}
			$count_user_tickets = count($user_tickets);
			$pdfs = get_attached_media( 'application/pdf', $t->ID );
		}	
		// upgrade to subscription 1.5 if last decouverte ticket
		if ( $user_subscription == '1' && $count_all_tickets == $count_user_tickets && $count_all_tickets != 0 && $user_mode == 'meta_value_num menu_order' )
			update_user_meta($user_id,'sstfg_subscription', '1.5');
		// end approfondissement
		elseif ( $user_subscription == '2' && $count_all_tickets == $count_user_tickets && $count_all_tickets != 0 && $user_mode == 'meta_value_num menu_order' )
			update_user_meta($user_id,'sstfg_subscription', '2.5');

		// build download link
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active('s2member/s2member.php') ) {
			$name = get_post_meta($t->ID,'_sstfg_protected_pdf',true);
			$pdf_url = "/?s2member_file_download=access-s2member-ccap-sstfg/".$name;
		} else {
			foreach ( $pdfs as $p ) { $pdf_url = $p->guid; }
		}
		// SEND TICKET BY MAIL
		if ( $new_or_last == 'new' && $user_send_me_tickets == 'please' || $new_or_last == 'scheduled' )
			sstfg_send_ticket($user_id,$t->post_title,$pdf_url);

		// OUTPUT
		$ticket_out = "
			<div class='well'><p><strong>".$text_out."</strong>: ".$t->post_title."</p><a class='sbutton square noshadow small mainthemebgcolor' href='".$pdf_url."' target='_blank'><i class='icon-download'></i> ".__('Download it (PDF)','sstfg')."</a></div>
		";

	} elseif ( $new_or_last == 'last' && $count != 1 && !is_array($user_tickets) ) {
		$ticket_out = "<p class='alert alert-warning' role='alert'>".__('It seems that you still don\'t have any tickets.','sstfg')."</p>";

	} else {
		$ticket_out = "<p class='alert alert-danger' role='alert'>".__('Something was wrong. We cannot serve you a new ticket.','sstfg')."</p>";
	}
	return $ticket_out;

}


// show panel to access to ticket
function sstfg_access_to_tickets_panel($atts) {
	extract( shortcode_atts( array(
		'login_page_url' => SSTFG_LOGIN_URL,
	), $atts ));
	if ( !is_user_logged_in() ) {
		wp_redirect($login_page_url."?ref=".get_permalink()); exit;
	}

	// current user options
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );

	if ( $user_subscription != '1' && $user_subscription != '1.5' && $user_subscription != '2' ) {
		wp_redirect(SSTFG_WC_PRODUCT_URL); exit;
	}

	$action = get_permalink();

	// ACTIONS
	$ticket = "";
	// if get new ticket form has been sent
	if ( array_key_exists('new_ticket_submit',$_POST) ) {
		//$ticket = sstfg_new_ticket($user_id);
		$ticket = sstfg_get_ticket($user_id,"new");
	}

	// if get last ticket form has been sent
	elseif ( array_key_exists('last_ticket_submit',$_POST) ) {
		//$ticket = sstfg_last_ticket($user_id);
		$ticket = sstfg_get_ticket($user_id,"last");
	}

	// OUTPUT
	$new_ticket_out = "
		<form class='pull-right' name='new_ticket_form' action='".$action."' method='post'>
			<input id='new_ticket_submit' class='sbutton square noshadow medium btn-info' type='submit' value='".__('Get new ticket','sstfg')."' name='new_ticket_submit' />
		</form>
	";
	$last_ticket_out = "
		<form name='last_ticket_form' action='".$action."' method='post'>
			<input id='last_ticket_submit' class='sbutton square noshadow medium btn-default' type='submit' value='".__('Get the last ticket','sstfg')."' name='last_ticket_submit' />
		</form>
	";

	$output = $new_ticket_out.$last_ticket_out.$ticket;
	return $output;

} // end show panel to access to ticket

add_action('save_post', 'sstfg_ticket_file_move_s2member',9999);
function sstfg_ticket_file_move_s2member() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active('s2member/s2member.php') )
		return;

	global $post;
	if ( $post ) {

	// If this is just a revision, don't continue
	if ( wp_is_post_revision( $post->ID ) )
		return;

	if ( $post->post_type == 'billet' && $post->post_status == 'publish' ) {

		$pdfs = get_attached_media( 'application/pdf', $post->ID );
		if ( count($pdfs) >= 1 ) {
			foreach ( $pdfs as $p ) {
				$name = $p->post_name.".pdf";
				$pdf_url = $p->guid;
			}
			if ( $name == get_post_meta($post->ID,'_sstfg_protected_pdf',true) )
				return;
			$docroot = $_SERVER['DOCUMENT_ROOT'];
			//$upload_dir = wp_upload_dir();
			$pdf_rel = str_replace(get_bloginfo('url'), '', $pdf_url);
			//$file = $upload_dir['basedir'];
			$old = $docroot.$pdf_rel;
			$new = $docroot."/wp-content/plugins/s2member-files/access-s2member-ccap-sstfg/".$name;
			update_post_meta($post->ID,'_sstfg_protected_pdf',$name);
			copy($old, $new) or die("Unable to copy $old to $new.");
		}
	}	
	}
}

add_action('woocommerce_order_status_completed', 'sstfg_add_cap_to_customer', 10, 1);
//add_action('woocommerce_payment_complete', 'sstfg_add_cap_to_customer', 10, 1);
function sstfg_add_cap_to_customer($order_id) {
	$extra_fields = sstfg_user_extra_fields();
	$order = new WC_Order( $order_id );
	$customer_id = (int)$order->user_id;
	$items = $order->get_items();
	foreach ($items as $item) {
		if ($item['product_id']== SSTFG_WC_PRODUCT_ID ) {
			$user = new WP_User( $customer_id );
			$user->add_cap( 'access_s2member_level0' );
			$user->add_cap( 'access_s2member_ccap_sstfg' );
			foreach ( $extra_fields as $ef ) {
				$ef_value = get_user_meta( $customer_id,$ef['label'],true);
				if ( $ef_value == '' || $ef_value === FALSE ) { update_user_meta( $customer_id,$ef['label'],$ef['initial'] ); }
			}
		}
	}
}

add_action('subscription_expired', 'sstfg_remove_cap_to_customer', 10, 2);
add_action('subscription_trial_end', 'sstfg_remove_cap_to_customer', 10, 2);
function sstfg_remove_cap_to_customer( $user_id, $subscription_key ) {
	$sub= wcs_get_subscription_from_key( $subscription_key );
	$user = new WP_User( $user_id );
	$user->remove_cap( 'access_s2member_level0' );
	$user->remove_cap( 'access_s2member_ccap_sstfg' );
}

// CRON TASKS
////
add_filter( 'cron_schedules', 'sstfg_add_weekly_schedule' ); 
function sstfg_add_weekly_schedule( $schedules ) {
	$schedules['weekly'] = array(
		'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
		'display' => __( 'Weekly', 'sstfg' )
	);
	$schedules['biweekly'] = array(
		'interval' => 7 * 24 * 60 * 60 * 2,
		'display' => __( 'Every two Week', 'my-plugin-domain' )
	);
	return $schedules;
}

function sstfg_get_scheduled_ticket_callback($user_id) {
	sstfg_get_ticket($user_id,'scheduled');
	return;
}
function sstfg_scheduled_access_to_tickets($user_id,$periodicity) {
	$args = array($user_id);
	if( !wp_next_scheduled( 'sstfg_get_scheduled_ticket',$args ) )
		wp_schedule_event( time(), $periodicity, 'sstfg_get_scheduled_ticket',$args );
	return;
}
add_action( 'sstfg_get_scheduled_ticket', 'sstfg_get_scheduled_ticket_callback');
function sstfg_unscheduled_access_to_tickets($user_id) {
	$args = array($user_id);
	$timestamp = wp_next_scheduled( 'sstfg_get_scheduled_ticket',$args );
	wp_unschedule_event( $timestamp, 'sstfg_get_scheduled_ticket',$args );
	return;
}

?>
