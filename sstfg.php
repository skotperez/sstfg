<?php
/*
Plugin Name: Small Steps To Feel Good
Description: This plugin adds a SSTFG ticket system to the WordPress site bebooda.org.
Version: 0.1
Author: montera34
Author URI: http://montera34.com
License: GPLv3
*/

// VARS
require_once('sstfg-config.php');

// ACTIONS and FILTERS
// load plugin text domain for string translations
add_action( 'plugins_loaded', 'sstfg_load_textdomain' );
// Add custom post type
add_action(  'init', 'sstfg_create_post_type', 0 );
// Custom Taxonomies
add_action( 'init', 'sstfg_build_taxonomies', 0 );
// rewrite flush rules
register_activation_hook( __FILE__, 'sstfg_rewrite_flush' );

// Adds custom metaboxes to billet post type
add_action("add_meta_boxes_billet", "sstfg_billet_metaboxes");
add_action("save_post", "save_sstfg_billet_metaboxes", 10, 3);

// Register new user contact Methods: custom profile fields
add_filter( 'user_contactmethods', 'sstfg_extra_user_profile_fields' );


// hook failed login
add_action( 'wp_login_failed', 'sstfg_login_failed' );
// redirect to right log in page when blank username or password
add_action( 'authenticate', 'sstfg_blank_login');
// Load map JavaScript and styles
add_action( 'wp_enqueue_scripts', 'sstfg_register_load_scripts' );
// Hide admin bar to subscribers
add_action('set_current_user', 'sstfg_disable_admin_bar');
// No access to admin panel for subscribers
add_action( 'admin_init', 'sstfg_redirect_admin' );
// allow shortcodes in text widgets
add_filter('widget_text', 'do_shortcode');
// end ACTIONS and FILTERS

// SHORTCODES
// show subscription form
add_shortcode('sstfg_subscription', 'sstfg_show_subscription_form');
// show user login/signup form
add_shortcode('sstfg_login_register', 'sstfg_show_user_form');
// show edit user profile form
add_shortcode('sstfg_user_profile', 'sstfg_form_user_edit_profile');
// adds content to a page depending on subscription type
add_shortcode( 'sstfg_if', 'sstfg_if_subscription_type' );
//  show panel to access to ticket
add_shortcode( 'sstfg_tickets_panel', 'sstfg_access_to_tickets_panel' );
// outputs login or logout links, depending on if user is logged in or not
add_shortcode( 'sstfg_loginout_link', 'sstfg_loginout' );
// end SHORTCODES

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
		'menu_icon' => 'dashicons-tickets',
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
$extra_fields = array(
	array(
		'name' => __('Subscription', 'sstfg'),
		'label' => 'sstfg_subscription',
		'type' => 'input',
		'initial' => '1',
		'show_in_frontend' => '0'
	),
	array(
		'name' => __('Current sequence', 'sstfg'),
		'label' => 'sstfg_current_sequence',
		'type' => 'input',
		'initial' => 'Découverte',
		'show_in_frontend' => '2'
	),
	array(
		'name' => __('Ticket Access mode', 'sstfg'),
		'label' => 'sstfg_ticket_access_mode',
		'type' => 'radio',
		'options' => array(
			'manual' => __('Manual', 'sstfg'),
			'automatic' => __('Automatic', 'sstfg')
		),
		'initial' => 'manual',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Ticket access regularity', 'sstfg'),
		'label' => 'sstfg_ticket_access_regularity',
		'type' => 'radio',
		'options' => array(
			'once' => __('Once a week', 'sstfg'),
			'twice' => __('Twice a week', 'sstfg')
		),
		'initial' => '',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Ticket order', 'sstfg'),
		'label' => 'sstfg_ticket_order',
		'type' => 'radio',
		'options' => array(
			'rand' => __('Random', 'sstfg'),
			'menu_order' => __('Sequential', 'sstfg')
		),
		'initial' => 'menu_order',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Send me the tickets to my email address', 'sstfg'),
		'label' => 'sstfg_ticket_send_to_mail',
		'type' => 'checkbox',
		'initial' => '',
		'show_in_frontend' => '1'
	),
	array(
		'name' => __('Gotten Tickets', 'sstfg'),
		'label' => 'sstfg_tickets',
		'type' => 'input',
		'initial' => '',
		'show_in_frontend' => '0'
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


// user login form 
function sstfg_form_user_login( $action,$register_url,$feedback_out ) {
	$login_action = wp_login_url($action);
	$form_out = $feedback_out. "
		<form class='row' id='loginform' name='loginform' method='post' action='" .$login_action. "' role='form'>
			<div class='form-horizontal col-md-12'>
			<fieldset class='form-group'>
				<label for='user_login' class='col-sm-3 control-label'>".__('Username','sstfg')."</label>
				<div class='col-sm-5'>
					<input id='user_login' class='form-control' type='text' value='' name='log' />
				</div>
			</fieldset>
			<fieldset class='form-group'>
				<label for='user_pass' class='col-sm-3 control-label'>".__('Password','sstfg')."</label>
				<div class='col-sm-5'>
					<input id='user_pass' class='form-control' type='password' size='20' value='' name='pwd' />
				</div>
			</fieldset>
			<fieldset class='form-group'>
				<div class='col-sm-offset-3 col-sm-3 checkbox'>
					<label>
						<input id='rememberme' type='checkbox' value='forever' name='rememberme' /> ".__('Remember me','sstfg')."
					</label>
				</div>
				<div class='col-sm-2'>
					<div class='pull-right'>
						<input id='wp-submit' class='btn btn-primary' type='submit' value='".__('Log in','sstfg')."' name='wp-submit' />
					</div>
	    			</div>
			</fieldset>
			</div>
		</form>
		<div class='row'>
			<div class='col-md-5 col-md-offset-3'>
				<div class='pull-right'>
					".__("If you don't have an account yet:",'sstfg')." <a class='btn btn-success' href='".$register_url."'>".__('Sign up','sstfg')."</a>
				</div>
			</div>
		</div>
	";
	return $form_out;

} // end user login form

// redirect to right log in page when log in failed
function sstfg_login_failed( $user ) {
	// check what page the login attempt is coming from
	if ( array_key_exists('ref',$_GET) ) {
		$redirect_url = sanitize_text_field($_GET['ref']);
	} else {
		$ref = $_SERVER['HTTP_REFERER'];
		$ref = preg_replace("/\?.*$/","",$ref);
	}

	// check that were not on the default login page
	if ( !empty($ref) && !strstr($ref,'wp-login') && !strstr($ref,'wp-admin') && $user!=null ) {
		// make sure we don't already have a failed login attempt
		if ( !strstr($ref, '?login=failed' )) {
			// Redirect to the login page and append a querystring of login failed
			wp_redirect( $ref . '?login=failed');
		} else { wp_redirect( $ref ); }

		exit;
	}
} // end redirect to right log in page when log in failed

// redirect to right log in page when blank username or password
function sstfg_blank_login( $user ){
	// check what page the login attempt is coming from
	if ( array_key_exists('ref',$_GET) ) {
		$redirect_url = sanitize_text_field($_GET['ref']);
	} else {
		$ref = $_SERVER['HTTP_REFERER'];
		$ref = preg_replace('/\?.*$/','',$ref);
	}

	$error = false;
	if( array_key_exists('log',$_POST) && sanitize_text_field($_POST['log']) == '' ||
	array_key_exists('log',$_POST) && sanitize_text_field($_POST['pwd']) == '') { $error = true; }

  	// check that were not on the default login page
	if ( !empty($ref) && !strstr($ref,'wp-login') && !strstr($ref,'wp-admin') && $error ) {

		// make sure we don't already have a failed login attempt
		if ( !strstr($ref, '?login=empty') ) {
			// Redirect to the login page and append a querystring of login failed
			wp_redirect( $ref . '?login=empty' );
		} else { wp_redirect( $ref ); }
		exit;

	}

} // end redirect to right log in page when blank username or password

// outputs login or logout links, depending on if user is logged in or not
function sstfg_loginout($atts) {
	extract( shortcode_atts( array(
		'login_url' => '',
	), $atts ));
	if ( is_user_logged_in() ) {
		$url = wp_logout_url($_SERVER['REQUEST_URI']);
		$text = __('Se déconnecter','sstfg');

	} else {
		$url = $login_url;
		$text = __('Se connecter / S\'inscrire','sstfg');

	}
	return "<a class='sstfg-loginout' href='".$url."'>".$text."</a>";
} // end outputs login or logout links, depending on if user is logged in or not

// user register form
function sstfg_form_user_register($action,$login_url) {

	if ( array_key_exists('wp-submit',$_POST) ) {
		$username = sanitize_text_field($_POST['user_login']);
		$email = sanitize_text_field($_POST['user_email']);
		$pass = sanitize_text_field($_POST['user_pass']);
		$pass2 = sanitize_text_field($_POST['user_pass_confirm']);
		$search = " ";
		$username_with_spaces = strpos($username,$search);

		if ( username_exists($username) ) {
			$feedback_type = "danger"; $feedback_text = __('<strong>This username is already in use</strong>. Try someone other.','sstfg');

		} elseif ( validate_username($username) === false || $username_with_spaces !== false ) {
			$feedback_type = "danger"; $feedback_text = __('<strong>This username is not valid</strong>. A username can only have alphanumerical character, no special characters neither spaces','sstfg');

		} elseif ( email_exists($email) ) {
			$feedback_type = "danger"; $feedback_text = __('<strong>This email address is already in use</strong>. Try another one.','sstfg');

		} elseif ( $username == '' || $email == '' || $pass == '' ) {
			$feedback_type = "danger"; $feedback_text = __('<strong>Some of the required fields are empty</strong>.','sstfg');

		} elseif ( $pass != '' && $pass != $pass2 ) {
			$feedback_type = "danger"; $feedback_text = __('<strong>The password doesn\'t match</strong>. Check it, please.','sstfg');

		} else { $feedback_type = ""; }

		if ( $feedback_type != "" ) { $feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>"; }
		else {
			if ( $pass == '' ) { $pass = wp_generate_password( 12, false ); }
			$user_id = wp_create_user( $username, $pass, $email );

			wp_redirect($login_url."&register=success");
			exit;
		}

	} else { $username = ""; $email = ""; $feedback_out = ""; }

	$req_class = " <span class='glyphicon glyphicon-asterisk'></span>";
	$form_out = $feedback_out. "
	<form class='row' name='registerform' action='".$action."' method='post'>
		<div class='form-horizontal col-md-12'>
		<fieldset class='form-group'>
			<label for='user_login' class='col-sm-3 control-label'>".__('Username','sstfg').$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_login' class='form-control' type='text' value='".$username."' name='user_login' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_email' class='col-sm-3 control-label'>".__('Email','sstfg').$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_email' class='form-control' type='text' value='".$email."' name='user_email' />
			</div>
		</fieldset>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass' class='col-sm-3 control-label'>".__('Password','sstfg')."</label>
			<div class='col-sm-5'>
				<input id='user_pass' class='form-control' type='password' size='20' value='' name='user_pass' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass_confirm' class='col-sm-3 control-label'>".__('Confirm password','sstfg')."</label>
			<div class='col-sm-5'>
				<input id='user_pass_confirm' class='form-control' type='password' size='20' value='' name='user_pass_confirm' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<div class='col-sm-offset-3 col-sm-5'>
				<div class='pull-right'>
					<input id='wp-submit' class='btn btn-success' type='submit' value='".__('Sign up','sstfg')."' name='wp-submit' />
				</div>
    			</div>
		</fieldset>
		</div>
	</form>
	<div class='row'>
		<div class='col-md-5 col-md-offset-3'>
			<div class='pull-right'>
				<a class='btn btn-primary' href='".$login_url."'>".__('I already have an account.','sstfg')."</a>
			</div>
		</div>
	</div>
	";
	return $form_out;

} // end display register form

// show user login/signup form
function sstfg_show_user_form( $atts ) {
	if ( is_user_logged_in() ) return;

	extract( shortcode_atts( array(
		'subscription_page_url' => '',
	), $atts ));
	if ( array_key_exists('ref',$_GET) ) {
		$redirect_url = sanitize_text_field($_GET['ref']);
	} else {
		$redirect_url = preg_replace("/\?.*$/","",$subscription_page_url);
	}

	$action = get_permalink();
	$login_action = wp_login_url($redirect_url);
	$login_url = $action."?action=login";
	$register_url = $action."?action=register";

	if ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'register' ) { // if action is register		
		return sstfg_form_user_register($register_url,$login_url);

	} else {	
		if ( array_key_exists('login',$_GET) ) {
			$lost_pass_url = wp_lostpassword_url(get_permalink()."?login=lost-password");
			$login_fail = sanitize_text_field($_GET['login']);
			if ( $login_fail == 'failed' ) { $feedback_type = "danger"; $feedback_text = __('Username or password is not correct. Check them, please. Password forgotten?','sstfg')." <a class='btn btn-default' href='".$lost_pass_url."'>".__('get another one','sstfg')."</a>"; }
			if ( $login_fail == 'empty' ) { $feedback_type = "danger"; $feedback_text = __('Username or password are empty. If you forgot your password','sstfg'). "<a class='btn btn-default' href='".$lost_pass_url."'>".__('get another one','sstfg')."</a>"; }
			elseif ( $login_fail == 'lost-password' ) { $feedback_type = "info"; $feedback_text = __('<strong>A new password has been sent to your email address</strong>. You should receive it in a few moments. It may go to your spam folder.','sstfg'); }
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

		} elseif ( array_key_exists('register',$_GET) ) {
			$register_fail = sanitize_text_field($_GET['register']);
			if ( $register_fail == 'success' ) { $feedback_type = "success"; $feedback_text = __('<strong>Great!</strong> You have signed up successfully. You can log in now.','sstfg'); }
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

		} else { $feedback_out = ""; }

		return sstfg_form_user_login($action,$register_url,$feedback_out);

	} // end if action register or log in
	
} // end show user login/signup form

// subsctription form
function sstfg_form_user_subscription($action,$newkey){
	if ( $newkey == 'true' ) { $btn_text = __('Send me a new verification key','sstfg'); }
	else { $btn_text = __('Subscribe me','sstfg'); }
	$form_out = "
		<form class='row' id='suscriptionform' name='suscriptionform' method='post' action='" .$action. "' role='form'>
			<div class='form-horizontal col-md-12'>
			<fieldset class='form-group'>
				<input id='sstfg-subscription' class='btn btn-primary' type='submit' value='".$btn_text."' name='sstfg-subscription' />
			</fieldset>
			</div>
		</form>
	";
	return $form_out;
} // end subscription form

// verification form
function sstfg_form_user_verification($action = '',$feedback_out = '',$newkey_url ){
	$form_out = $feedback_out. "<h2>".__('Verify your account','sstfg')."</h2>
		<form class='row' id='verificationform' name='verificationform' method='post' action='" .$action. "' role='form'>
			<div class='form-horizontal col-md-12'>
			<p>".__('You can enter below the key you have received in your email. Don\'t you have any verification key? Have you lost yours?','sstfg')." <a href='".$newkey_url."'>".__('get another verification key','sstfg')."</a></p>
			<fieldset class='form-group'>
				<label for='sstfg-key'>
					<input id='sstfg-key' class='btn btn-primary' type='text' value='' name='sstfg-key' />
				</label>
			</fieldset>
			<fieldset class='form-group'>
				<input id='sstfg-verification' class='btn btn-primary' type='submit' value='".__('Verify me','sstfg')."' name='sstfg-verification' />
			</fieldset>
			</div>
		</form>
	";
	return $form_out;
} // end verification form

// show subscription form
function sstfg_show_subscription_form($atts) {
	if ( !is_user_logged_in() ) return;

	extract( shortcode_atts( array(
		'user_panel_url' => '',
	), $atts ));
	if ( array_key_exists('ref',$_GET) ) {
		$redirect_url = sanitize_text_field($_GET['ref']);
	} else {
		$redirect_url = preg_replace("/\?.*$/","",$user_panel_url);
	}
	$action = get_permalink();
	$subscription_url = $action."?action=subscription";
	$newkey_url = $action."?action=subscription&newkey=true";
	$verification_url = $action."?action=verification";
	$feedback_out = "";
	$user_id = get_current_user_id();
	$user_data = get_userdata( $user_id );
	$subscription = get_user_meta( $user_id,'sstfg_subscription', true );
	global $extra_fields;

	// ACTIONS
	// if subscription form has been sent
	if ( array_key_exists('action',$_GET) && array_key_exists('sstfg-subscription',$_POST) ) {
		// generate code and save it in db
		$key = wp_generate_password(18);
		update_user_meta( $user_id, 'sstfg_subscription', $key );
		// send code
		$to = $user_data->user_email;
		$subject = __('Bebooda SSTFG verification','sstfg');
		$message = 
__('Hi,','sstfg')
. "\r\n\r\n" .
__('You have subscribed to SSTFG successfully. Just one more step to be sure that this is your email address.','sstfg')
. "\r\n\r\n" .
__('To verify your email you must introduce the code in this email in the verification page: ','sstfg').$verification_url
. "\r\n\r\n" .
__('Here you have the code to verify your email:','sstfg')
. "\r\n\r\n" .
$key
. "\r\n\r\n" .
__('Welcome to SSTFG.','sstfg')
. "\r\n" .
'Bebooda'
;
		$headers[] = 'From: Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
//		$headers[] = 'Sender: Bebooda Notification System <no-reply@bebooda.org>' . "\r\n";
		$headers[] = 'Sender: Bebooda Notification System <info@montera34.com>' . "\r\n";
		$headers[] = 'Reply-To:  Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
		$headers[] = 'To: <' .$to. '>' . "\r\n";
		// To send HTML mail, the Content-type header must be set, uncomment the following two lines
		//$headers[]  = 'MIME-Version: 1.0' . "\r\n";
		//$headers[] = 'Content-type: text/html; charset=utf-8' . "\r\n";

		wp_mail( $to, $subject, $message, $headers);
		// redirection to verification page
		wp_redirect($verification_url);
		exit;

	} // end if subscription form has been sent

	// if verification form has been sent
	elseif ( array_key_exists('sstfg-verification',$_POST) || array_key_exists('key',$_GET) ) {
		if ( array_key_exists('key',$_GET) ) { $mail_key = sanitize_text_field($_GET['key']); }
		elseif ( array_key_exists('sstfg-key',$_POST) ) { $mail_key = sanitize_text_field($_POST['sstfg-key']); }
		else { $mail_key = ""; }
//	echo $mail_key;
		if ( $mail_key === $subscription && $mail_key != '' ) {
			update_user_meta($user_id,'sstfg_subscription','1');
			update_user_meta($user_id,'sstfg_current_sequence','decouverte');
			foreach ( $extra_fields as $ef ) {
				update_user_meta($user_id,$ef['label'],$ef['initial']);
			}
			wp_redirect($redirect_url."?verification=success");
			exit;
		} else {
			$feedback_type = "danger";
			$feedback_text = __('The key is not correct. Try again.','sstfg');
		}
		$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";
	} // end if verification form has been sent

	// OUTPUTS
	if ( $subscription == '' || array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'subscription' ) { // user not subscribed
		if ( array_key_exists('newkey',$_GET) ) { $newkey = sanitize_text_field($_GET['newkey']); }
		else { $newkey = "false"; }
		return sstfg_form_user_subscription($subscription_url,$newkey);

	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'verification' || $subscription != '' && $subscription != '1' && $subscription != '2' ) { // user subscribed but not verified
		return sstfg_form_user_verification($verification_url,$feedback_out,$newkey_url);

	} elseif ( $subscription == '1' || $subscription == '2' ) { // user subscribed and verified
		wp_redirect($redirect_url);
		exit;

	}

} // end show subscription form

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
		//wp_redirect($login_page_url."?action=subscription"); exit;
		wp_redirect($login_page_url); exit;
	}

	$action = get_permalink();
	global $extra_fields;

	// if edit profile form has been sent
	if ( array_key_exists('wp-submit',$_POST) ) {
	
		$email = sanitize_text_field($_POST['user_email']);
//		$pass = sanitize_text_field($_POST['user_pass']);
//		$pass2 = sanitize_text_field($_POST['user_pass_confirm']);

		foreach ( $extra_fields as $ef ) {
			if ( $ef['show_in_frontend'] == '1' ) {
				$$ef['label'] = sanitize_text_field($_POST[$ef['label']]);
				$fields_to_update[$ef['label']] = $$ef['label'];
			}
		}

//		if ( email_exists($email) && $email != $current_user->user_email ) {
//			$feedback_type = "danger"; $feedback_text = __('<strong>This email address is already in use</strong>. Try another one.','sstfg');

//		} elseif ( $email == '' ) {
//			$feedback_type = "danger"; $feedback_text = __('<strong>Email is a required field</strong>.','sstfg');

//		} elseif ( $pass != '' && $pass != $pass2 ) {
//			$feedback_type = "danger"; $feedback_text = __('<strong>Password doesn\'t match</strong>. Try it again.','sstfg');

//		} else { $feedback_type = ''; }

//		if ( $feedback_type != '' ) {
//			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

//		} else {
			// current user data
//			if ( $pass != '' ) { wp_set_password( $pass, $user_id ); }
			$fields_to_update['ID'] = $user_id;
//			$fields_to_update['user_email'] = $email;
			$updated_id = wp_update_user( $fields_to_update );
			wp_redirect(get_permalink()."?edit_profile=success");
			exit;

//		}

	} // end if edit profile form has been sent

	else {
		if ( array_key_exists('edit_profile',$_GET) && sanitize_text_field($_GET['edit_profile']) == 'success' ) {
			$feedback_type = "success"; $feedback_text = __('Settings for your SSTFG subscription has been updated.','sstfg');

//		elseif ( array_key_exists('verification',$_GET) && sanitize_text_field($_GET['verification']) == 'success' ) {
//			$feedback_type = "success"; $feedback_text = __('Your email has been verified.','sstfg');

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
					<fieldset class='form-group'>
						<label for='".$ef['label']."' class='col-sm-3 control-label'>".$ef['name']."</label>
						<div class='col-sm-5'>
							<input id='".$ef['label']."' class='form-control' type='text' value='".$$ef['label']."' name='".$ef['label']."'".$disabled." />
						</div>
					</fieldset>
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
					<fieldset class='form-group ".$ef['label']."'>
						".$ef['name']."
						<div class='radio'>".$options_out."</div>
						".$help_out."
					</fieldset>
				";
				unset($help_out);
	
			} elseif ( $ef['type'] == 'checkbox' ) {
				if ( $$ef['label'] != '' ) { $checked_out = " checked"; } else { $checked_out = ''; }
				$extra_output .= "
					<fieldset class='form-group ".$ef['label']."'>
						<div class='col-sm-offset-3 col-sm-3 checkbox'>
							<label>
								<input type='checkbox' name='".$ef['label']."' id='".$ef['label']."' value='please'".$checked_out.$disabled." /> ".$ef['name']."
							</label>
						</div>
					</fieldset>
				";
	
			}
		}
	}

	$req_class = " <span class='glyphicon glyphicon-asterisk'></span>";
	$form_out = $feedback_out. "
	<form class='row' name='edit_profile_form' action='".$action."' method='post'>
		<div class='form-horizontal col-md-12'>
		<fieldset class='form-group'>
			<label for='user_login' class='col-sm-3 control-label'>".__('Username','sstfg').$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_login' class='form-control' type='text' value='".$username."' name='user_login' disabled='disabled' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_email' class='col-sm-3 control-label'>".__('Email','sstfg').$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_email' class='form-control' type='text' value='".$email."' name='user_email' disabled='disabled' />
			</div>
		</fieldset>
		".$extra_output."
		<fieldset class='form-group'>
			<div class='col-sm-offset-3 col-sm-5'>
				<div class='pull-right'>
					<input id='wp-submit' class='btn btn-primary' type='submit' value='".__('Update','sstfg')."' name='wp-submit' />
				</div>
    			</div>
		</fieldset>
		</div>
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
	$subject = __('Bebooda SSTFG verification','sstfg');
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
//		$headers[] = 'Sender: Bebooda Notification System <info@montera34.com>' . "\r\n";
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

// get ticket
function sstfg_get_ticket($user_id,$new_or_last) {
	$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );
	$user_sequence_name = get_user_meta( $user_id,'sstfg_current_sequence', true );
		$user_sequence_data = get_term_by( 'name',$user_sequence_name,'sequence-composee','ARRAY_A' );
		$user_sequence = $user_sequence_data['slug'];
	$user_mode = get_user_meta( $user_id,'sstfg_ticket_order', true );
		if ( $user_mode == 'menu_order' ) { $user_mode = "meta_value_num menu_order"; }
	$user_tickets = get_user_meta( $user_id,'sstfg_tickets', true );
	// LAST
	if ( $new_or_last == 'last' ) {
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
	elseif ( $new_or_last == 'new' ) {
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
			if ( $new_or_last == 'new' ) {
				update_user_meta( $user_id,'sstfg_tickets',$user_tickets);
			}
			$count_user_tickets = count($user_tickets);
			$pdfs = get_attached_media( 'application/pdf', $t->ID );
		}	
		// upgrade to subscription 1.5 if last decouverte ticket
		if ( $user_subscription == '1' && $count_all_tickets == $count_user_tickets && $count_all_tickets != 0 && $user_mode == 'meta_value_num menu_order' )
			update_user_meta($user_id,'sstfg_subscription', '1.5');
		// end approfondissement
		if ( $user_subscription == '2' && $count_all_tickets == $count_user_tickets && $count_all_tickets != 0 && $user_mode == 'meta_value_num menu_order' )
			update_user_meta($user_id,'sstfg_subscription', '2.5');

		// build download link
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active('s2member/s2member.php') ) {
			$name = get_post_meta($t->ID,'_sstfg_protected_pdf',true);
			$pdf_url = "/?s2member_file_download=access-s2member-ccap-sstfg/".$name;
		} else {
			foreach ( $pdfs as $p ) { $pdf_url = $p->guid; }
		}
		// OUTPUT
		$ticket_out = "
			<p>".__('Here you have your ticket:','sstfg')."</p>
			<p><strong>".$t->post_title."</strong>: <a class='sbutton square noshadow medium mainthemebgcolor' href='".$pdf_url."' target='_blank'><i class='icon-download'></i> ".__('Download it (PDF)','sstfg')."</a></p>
		";

	} elseif ( $new_or_last == 'last' && $count != 1 && !is_array($user_tickets) ) {
		$ticket_out = "<p class='alert alert-warning' role='alert'>".__('It seems that you still don\'t have any tickets.','sstfg')."</p>";

	} else {
		$ticket_out = "<p class='alert alert-danger' role='alert'>".__('Something was wrong. We cannot serve you a new ticket.','sstfg')."</p>";
	}
	return $ticket_out;

}
// get new ticket
function sstfg_new_ticket($user_id) {
	$user_subscription = get_user_meta( $user_id,'sstfg_subscription', true );
	$user_sequence = get_user_meta( $user_id,'sstfg_current_sequence', true );
	$user_mode = get_user_meta( $user_id,'sstfg_ticket_order', true );
	$user_tickets = get_user_meta( $user_id,'sstfg_tickets', true );
	if (is_array($user_tickets)) {
		foreach ( $user_tickets as $ut ) { $user_tickets_id[] = $ut['ID']; }
	} else { $user_tickets_id = ""; }

	if ( $user_subscription == '1' ) {
		// get all tickets in current phase
		$args = array(
			'post_type' => 'billet',
			'posts_per_page' => '-1',
			'tax_query' => array(
				array(
					'taxonomy' => 'sequence-composee',
					'field'    => 'name',
					'terms'    => $user_sequence,
				),
			)
		);
		$tickets = get_posts($args);
		$count_all_tickets = count($tickets);
		// get ticket to serve now
		$args = array(
			'post_type' => 'billet',
			'posts_per_page' => '1',
			'orderby' => $user_mode,
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'sequence-composee',
					'field'    => 'name',
					'terms'    => $user_sequence,
				),
			),
			'post__not_in' => $user_tickets_id
		);
		$tickets = get_posts($args);
		$count = count($tickets);
		if ( $count == 1 ) {
			//update user list of tickets
			foreach ( $tickets as $t ) {
				$user_tickets[] = array(
					'ID' => $t->ID,
					'date' => time()
				);
				update_user_meta( $user_id,'sstfg_tickets',$user_tickets);
				$count_user_tickets = count($user_tickets);
				$pdfs = get_attached_media( 'application/pdf', $t->ID );
			}
			// build download link
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active('s2member/s2member.php') ) {
				$name = get_post_meta($t->ID,'_sstfg_protected_pdf',true);
				$pdf_url = "/?s2member_file_download=access-s2member-ccap-sstfg/".$name;
			} else {
				foreach ( $pdfs as $p ) { $pdf_url = $p->guid; }
			}
			// OUTPUT
			$ticket_out = "
				<p>".__('Here you have your new ticket:','sstfg')."</p>
				<p><strong>".$t->post_title."</strong>: <a href='".$pdf_url."' target='_blank'>".__('Download it (PDF)','sstfg')."</a></p>
			";
		} else {
			$count_user_tickets = "";
			$ticket_out = "<p class='alert alert-danger' role='alert'>".__('Something was wrong. We cannot serve you a new ticket.','sstfg')."</p>";
		}
	
		if ( $count_all_tickets == $count_user_tickets && $count_all_tickets != 0 && $user_mode == 'menu_order' ) {
			update_user_meta($user_id,'sstfg_subscription', '1.5');
//			$ticket_out .= "<p class='alert alert-info' role='alert'><small>".__('This is the last ticket of the Decouverte sequence: you have finished it. Now you are ready to subscribe to the complete Small Steps To Feel Good sequence. You can do this from your User panel','sstfg')."</small></p>";
		}

	} elseif ( $user_subscription == '1.5' ) {
		$ticket_out = "<p class='alert alert-info' role='alert'>".__('You have finish the Decouverte sequence: to get more tickets <a href="/user-panel">you must change your subscription</a>.','sstfg')."</p>";

	} elseif ( $user_subscription == '2' ) {
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
			),
		);

	}

	return $ticket_out;
} // end get new ticket

// get last ticket
function sstfg_last_ticket($user_id) {
	$user_tickets = get_user_meta( $user_id,'sstfg_tickets', true );
	if (is_array($user_tickets)) {
		$last_ticket = end($user_tickets);
		$args = array(
			'post_type' => 'billet',
			'posts_per_page' => '1',
			'p' => $last_ticket['ID']
		);
		$tickets = get_posts($args);
		foreach ( $tickets as $t ) {
			$pdfs = get_attached_media( 'application/pdf', $t->ID );
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active('s2member/s2member.php') ) {
			$name = get_post_meta($t->ID,'_sstfg_protected_pdf',true);
			$pdf_url = "/?s2member_file_download=access-s2member-ccap-sstfg/".$name;
		} else {
			foreach ( $pdfs as $p ) { $pdf_url = $p->guid; }
		}
		$ticket_out = "
			<p>".__('Here you have your last ticket:','sstfg')."</p>
			<p><strong>".$t->post_title."</strong>: <a href='".$pdf_url."' target='_blank'>".__('Download it (PDF)','sstfg')."</a></p>
			";
	} else {
		$ticket_out = "<p class='alert alert-warning' role='alert'>".__('It seems that you still don\'t have any tickets.','sstfg')."</p>";
	}

	return $ticket_out;
} // end get last ticket

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
		
		<form class='row' name='new_ticket_form' action='".$action."' method='post'>
			<fieldset class='form-group'>
				<div class='col-sm-offset-3 col-sm-5'>
					<div class='pull-right'>
						<input id='new_ticket_submit' class='btn btn-primary' type='submit' value='".__('Get new ticket','sstfg')."' name='new_ticket_submit' />
					</div>
    				</div>
			</fieldset>
		</form>
	";
	$last_ticket_out = "
		
		<form class='row' name='last_ticket_form' action='".$action."' method='post'>
			<fieldset class='form-group'>
				<div class='col-sm-offset-3 col-sm-5'>
					<div class='pull-right'>
						<input id='last_ticket_submit' class='btn btn-primary' type='submit' value='".__('Get the last ticket','sstfg')."' name='last_ticket_submit' />
					</div>
    				</div>
			</fieldset>
		</form>
	";

	$output = $ticket.$new_ticket_out.$last_ticket_out;
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
	global $extra_fields;
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
				if ( $ef_value == '' ) { update_user_meta( $customer_id,$ef['label'],$ef['initial'] ); }
			}
		}
	}
}

?>
