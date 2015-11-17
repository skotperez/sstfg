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
// hook failed login
add_action( 'wp_login_failed', 'sstfg_login_failed' );
// redirect to right log in page when blank username or password
add_action( 'authenticate', 'sstfg_blank_login');
// Load map JavaScript and styles
add_action( 'wp_enqueue_scripts', 'sstfg_register_load_scripts' );
// end ACTIONS and FILTERS

// SHORTCODES
// show subscription form
add_shortcode('sstfg_subscription', 'sstfg_show_subscription_form');
// show user login/signup form
add_shortcode('sstfg_login_register', 'sstfg_show_user_form');
// show edit user profile form
add_shortcode('sstfg_user_profile', 'sstfg_form_user_edit_profile');
// end SHORTCODES

// PAGE TEMPLATES CREATOR
include("include/page-templater.php");

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
	array(
		'name' => __('Mobile phone', 'sstfg'),
		'label' => 'user_mobile',
		'type' => 'input'
	),
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
		'label' => 'user_ticket_send_to_mail',
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
					".__("If you don't have an account yet:",'sstfg')." <a class='btn btn-success' href='".$register_url."'>".__("Sign up",'sstfg')."</a>
				</div>
			</div>
		</div>
	";
	return $form_out;

} // end user login form

// redirect to right log in page when log in failed
function sstfg_login_failed( $user ) {
	// check what page the login attempt is coming from
	$ref = $_SERVER['HTTP_REFERER'];
	$ref = preg_replace("/\?.*$/","",$ref);

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
	$ref = $_SERVER['HTTP_REFERER'];
	$ref = preg_replace('/\?.*$/','',$ref);

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

// user register form
function sstfg_form_user_register($action,$login_url) {

	if ( array_key_exists('wp-submit',$_POST) ) {
		$username = sanitize_text_field($_POST['user_login']);
		$email = sanitize_text_field($_POST['user_email']);
		$pass = sanitize_text_field($_POST['user_pass']);
		$pass2 = sanitize_text_field($_POST['user_pass_confirm']);

		if ( username_exists($username) ) {
			$feedback_type = "danger"; $feedback_text = "<strong>El nombre de usuario que elegiste ya existe</strong>. Tendrás que elegir otro.";

		} elseif ( validate_username($username) === false ) {
			$feedback_type = "danger"; $feedback_text = "<strong>El nombre de usuario que elegiste no es válido</strong>. Los nombres de usuario solo pueden estar formados por caracteres alfanuméricos.";

		} elseif ( email_exists($email) ) {
			$feedback_type = "danger"; $feedback_text = "<strong>La dirección de correo que elegiste ya está asociada a otro usuario</strong>. Tendrás que usar otra.";

		} elseif ( $username == '' || $email == '' ) {
			$feedback_type = "danger"; $feedback_text = "<strong>Alguno de los campos requeridos para el registro no fueron rellenados</strong>. Solo son dos: vuelve a intentarlo.";

		} elseif ( $pass != '' && $pass != $pass2 ) {
			$feedback_type = "danger"; $feedback_text = "<strong>La contraseña no coincide</strong>. Inténtalo otra vez.";

		} elseif ( !array_key_exists('user_accept',$_POST) || sanitize_text_field($_POST['user_accept']) != 'Acepto' ) {
			$feedback_type = "danger"; $feedback_text = "<strong>Tienes que aceptar las condiciones legales</strong>. Y quizás leerlas antes.";

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
			<label for='user_login' class='col-sm-3 control-label'>Nombre de usuario ".$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_login' class='form-control' type='text' value='".$username."' name='user_login' />
			</div>
			<p class='help-block col-sm-4'><small><span class='glyphicon glyphicon-asterisk'></span> Campos requeridos.<br /><strong>Sin espacios, sin caracteres especiales</strong>.</small></p>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_email' class='col-sm-3 control-label'>Correo electrónico ".$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_email' class='form-control' type='text' value='".$email."' name='user_email' />
			</div>
			<p class='help-block col-sm-4'><small><strong>Para enviarte una nueva contraseña</strong> en caso de que lo necesites: no enviamos spam ni vendemos tus datos.</small></p>
		</fieldset>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass' class='col-sm-3 control-label'>Contraseña</label>
			<div class='col-sm-5'>
				<input id='user_pass' class='form-control' type='password' size='20' value='' name='user_pass' />
			</div>
			<p class='help-block col-sm-4'><small>No rellenes este campo si quieres recibir una contraseña generada automáticamente en tu dirección de correo electrónico.</small></p>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass_confirm' class='col-sm-3 control-label'>Confirma la contraseña</label>
			<div class='col-sm-5'>
				<input id='user_pass_confirm' class='form-control' type='password' size='20' value='' name='user_pass_confirm' />
			</div>
			<p class='help-block col-sm-4'><small><strong>Elige una contraseña fuerte</strong>: incluye letras y números, mayúsculas y minúsculas, caracteres especiales.</small></p>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_legal' class='col-sm-3 control-label'>Condiciones legales</label>
			<div class='col-sm-5'>
			<textarea id='user_legal' name='user_legal' class='form-control' rows='10' disabled>
CLAÚSULA INFORMATIVA DE REGISTRO DE NUEVO USUARIO:

De acuerdo con lo dispuesto en el artículo 5 de la Ley Orgánica 15/1999, de 13 de diciembre, de protección de datos de carácter personal (LOPD) el solicitante queda informado de:

1.- Sus datos personales se incorporarán a los ficheros de datos personales cuya titularidad ostenta ASA. ASOCIACIÓN SOSTENIBILIDAD Y ARQUITECTURA, con domicilio social en el Pº de la Castellana, 12. Madrid 28046.

2.- Las finalidades de los tratamientos de los datos personales serán:

    Gestión de la información generada para creación de estadísticas y conclusiones en relación a materiales más empleados y carga medioambiental de los mismos en los proyectos y obras en la edificación. 
    Gestión de la información relacionada con la Huella de Carbono en la edificación para la mejora o modificación de los documentos de apoyo.
    Gestión y organización de las actividades de difusión y formación que se desarrollen en torno a los indicadores ambientales, cambio climático, sostenibilidad y arquitectura.
    
3.- Las cesiones de datos que se efectuarán serán las siguientes:

    Las que resulten de la ejecución de las finalidades del tratamiento de los datos, que el solicitante declara conocer y aceptar.
    Las que se realicen bajo los supuestos previstos en el artículo 11 de la LOPD.

4.- Ejercicio de derechos de acceso, rectificación, cancelación y oposición: los interesados podrán ejercitar estos derechos en los términos recogidos en la LOPD y normativa de desarrollo, dirigiéndose a la Secretaría de la Asociación en su domicilio (Pº Castellana, 12 / 28046 Madrid).
</textarea>
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<div class='col-sm-offset-3 col-sm-5'>
				<label for='user_accept'>
					<input name='user_accept' id='user_accept' type='checkbox' value='Acepto' />
					He leído y acepto estas condiciones legales y la <a href='/politica-privacidad' target='_blank'>Política de privacidad</a>
				</label>
				<div class='pull-right'>
					<input id='wp-submit' class='btn btn-success' type='submit' value='Regístrate' name='wp-submit' />
				</div>
    			</div>
		</fieldset>
		</div>
	</form>
	<div class='row'>
		<div class='col-md-5 col-md-offset-3'>
			<div class='pull-right'>
				¿Ya tienes cuenta? <a class='btn btn-primary' href='".$login_url."'>Inicia sesión</a>
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
	$redirect_url = preg_replace("/\?.*$/","",$subscription_page_url);
	$action = get_permalink();
	$login_action = wp_login_url($redirect_url);
	$login_url = $action."?action=login";
	$register_url = $action."?action=register";

//	if ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) != 'login' || !array_key_exists('action',$_GET) ) { // if action is register
	if ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'register' ) { // if action is register		
		return sstfg_form_user_register($action,$login_url);

	} else {	
		if ( array_key_exists('login',$_GET) ) {
			$lost_pass_url = wp_lostpassword_url(get_permalink()."?login=lost-password");
			$login_fail = sanitize_text_field($_GET['login']);
			if ( $login_fail == 'failed' ) { $feedback_type = "danger"; $feedback_text = "El nombre de usuario o la contraseña no son correctos. Por favor, inténtalo de nuevo. Si olvidaste tu contraseña, puedes <a class='btn btn-default' href='".$lost_pass_url."'>solicitar una nueva</a>"; }
			if ( $login_fail == 'empty' ) { $feedback_type = "danger"; $feedback_text = "No rellenaste el nombre de usuario o la contraseña; necesitamos ambos para iniciar tu sesión. Si olvidaste tu contraseña, puedes <a class='btn btn-default' href='".$lost_pass_url."'>solicitar una nueva</a>"; }
			elseif ( $login_fail == 'lost-password' ) { $feedback_type = "info"; $feedback_text = "<strong>Hemos enviado una nueva contraseña a tu dirección de correo</strong>. Debería llegar a tu buzón en un minuto; recuerda que puede haber ido a la carpeta de spam."; }
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

		} elseif ( array_key_exists('register',$_GET) ) {
			$register_fail = sanitize_text_field($_GET['register']);
			if ( $register_fail == 'success' ) { $feedback_type = "success"; $feedback_text = "<strong>¡Bien!</strong> Te has registrado con éxito. Ahora puedes iniciar sesión y evaluar un proyecto."; }
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

		} else { $feedback_out = ""; }

		return sstfg_form_user_login($action,$register_url,$feedback_out);

//	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'register' ) { // if action is register
//	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'edit' ) { // if action is edit profile
//		return hce_edit_userdata_form();

	} // end if action register or log in
	
} // end show user login/signup form

// subsctription form
function sstfg_form_user_subscription($action){
	$form_out = "
		<form class='row' id='suscriptionform' name='suscriptionform' method='post' action='" .$action. "' role='form'>
			<div class='form-horizontal col-md-12'>
			<fieldset class='form-group'>
				<input id='sstfg-subscription' class='btn btn-primary' type='submit' value='".__('Subscribe me','sstfg')."' name='sstfg-subscription' />
			</fieldset>
			</div>
		</form>
	";
	return $form_out;
} // end subscription form

// verification form
function sstfg_form_user_verification($action = '',$feedback_out = '',$subscription_url ){
	$form_out = $feedback_out. "<h2>".__('Verify your account','sstfg')."</h2>
		<form class='row' id='verificationform' name='verificationform' method='post' action='" .$action. "' role='form'>
			<div class='form-horizontal col-md-12'>
			<p>".__('Introduce below the key you have received in your email. If you have no verification key or you have lost yours, you can <a href="'.$subscription_url.'">ask for another verification key</a>.','sstfg')."</p>
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
	$redirect_url = preg_replace("/\?.*$/","",$user_panel_url);
	$action = get_permalink();
	$subscription_url = $action."?action=subscription";
	$verification_url = $action."?action=verification";
	$feedback_out = "";
	$user_id = get_current_user_id();
	$user_data = get_userdata( $user_id );
	$subscription = get_user_meta( $user_id,'sstfg_subscription', true );

	// ACTIONS
	// if subscription form has been sent
	if ( array_key_exists('action',$_GET) && array_key_exists('sstfg-subscription',$_POST) ) {
		// generate code and save it in db
		$key = wp_generate_password(18,true,true);
		update_user_meta( $user_id, 'sstfg_subscription', $key );
		// send code
		$to = $user_data->user_email;
		$subject = "Bebooda SSTFG verification";
		$message = '
Hi ' .$user_data->user_login. ','
. "\r\n\r\n" .
'You have subscribed to SSTFG successfully. Just one more step to be sure that this is your email.'
. "\r\n\r\n" .
'To verify your email you can visit the following link: '.$verification_url.'&key='.$key
. "\r\n\r\n" .
'If that did not work you can introduce the code directly in the verification page: '.$verification_url
. "\r\n\r\n" .
'Here you have the code to verify your email:'
. "\r\n\r\n" .
$key
. "\r\n\r\n" .
'Welcome to SSTFG.'
. "\r\n" .
'Bebooda'
;
		$headers[] = 'From: Bebooda SSTFG <sstfg@bebooda.org>' . "\r\n";
		$headers[] = 'Sender: Bebooda Notification System <no-reply@bebooda.org>' . "\r\n";
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
	elseif ( array_key_exists('sstfg-verification',$_POST) ) {
		if ( array_key_exists('key',$_GET) ) { $mail_key = sanitize_text_field($_GET['key']); }
		elseif ( array_key_exists('sstfg-key',$_POST) ) { $mail_key = sanitize_text_field($_POST['sstfg-key']); }
		else { $mail_key = ""; }

		if ( $mail_key === $subscription && $mail_key != '' ) {
			update_user_meta($user_id,'sstfg_subscription','1');
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
		return sstfg_form_user_subscription($subscription_url);

	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'verification' || $subscription != '' && $subscription != '1' && $subscription != '2' ) { // user subscribed but not verified
		return sstfg_form_user_verification($verification_url,$feedback_out,$subscription_url);

	} elseif ( $subscription == '1' || $subscription == '2' ) { // user subscribed and verified
		wp_redirect($redirect_url);
		exit;

	}

} // end show subscription form

// edit user profile form
function sstfg_form_user_edit_profile(){
	$action = get_permalink();
	global $extra_fields;

	// if edit profile form has been sent
	if ( array_key_exists('wp-submit',$_POST) ) {
		global $current_user;
		get_currentuserinfo();

		$email = sanitize_text_field($_POST['user_email']);
		$pass = sanitize_text_field($_POST['user_pass']);
		$pass2 = sanitize_text_field($_POST['user_pass_confirm']);

		foreach ( $extra_fields as $ef ) {
			$$ef['label'] = sanitize_text_field($_POST[$ef['label']]);
			$fields_to_update[$ef['label']] = $$ef['label'];
		}

		if ( email_exists($email) && $email != $current_user->user_email ) {
			$feedback_type = "danger"; $feedback_text = "<strong>La dirección de correo que elegiste ya está asociada a otro usuario</strong>. Tendrás que usar otra.";

		} elseif ( $email == '' ) {
			$feedback_type = "danger"; $feedback_text = "<strong>El correo electrónico es un campo obligatorio</strong>: no puedes dejarlo en blanco.";

		} elseif ( $pass != '' && $pass != $pass2 ) {
			$feedback_type = "danger"; $feedback_text = "<strong>La contraseña no coincide</strong>. Inténtalo otra vez.";

		} else { $feedback_type = ''; }

		if ( $feedback_type != '' ) {
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";

		} else {
			// current user data
			$user_id = $current_user->ID;
			if ( $pass != '' ) { wp_set_password( $pass, $user_id ); }
			$fields_to_update['ID'] = $user_id;
			$fields_to_update['user_email'] = $email;
			$updated_id = wp_update_user( $fields_to_update );
			wp_redirect(get_permalink()."?edit_profile=success");
			exit;

		}

	} // end if edit profile form has been sent

	else {
		if ( array_key_exists('edit_profile',$_GET) && sanitize_text_field($_GET['edit_profile']) == 'success' ) {
			$feedback_type = "success"; $feedback_text = __('Your profile has been updated.','sstfg');

		} 
		elseif ( array_key_exists('verification',$_GET) && sanitize_text_field($_GET['verification']) == 'success' ) {
			$feedback_type = "success"; $feedback_text = __('Your email has been verified.','sstfg');

		} else { $feedback_type = ''; }

		if ( $feedback_type != '' ) { 
			$feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>";
		} else { $feedback_out = ''; }

		global $current_user;
		get_currentuserinfo();
		$username = $current_user->user_login;
		$email = $current_user->user_email;
		foreach ( $extra_fields as $ef ) {
			$$ef['label'] = $current_user->$ef['label'];
		}

	}

	$extra_output = "";
	foreach ( $extra_fields as $ef ) {
		if ( $ef['type'] == 'input' ) {
			$extra_output .= "
				<fieldset class='form-group'>
					<label for='".$ef['label']."' class='col-sm-3 control-label'>".$ef['name']."</label>
					<div class='col-sm-5'>
						<input id='".$ef['label']."' class='form-control' type='text' value='".$$ef['label']."' name='".$ef['label']."' />
					</div>
				</fieldset>
			";

		} elseif ( $ef['type'] == 'radio' ) {
			$options_out = "";
			foreach ( $ef['options'] as $k => $v ) {
				if ( $$ef['label'] == $k ) { $checked_out = " checked"; } else { $checked_out = ''; }
				$options_out .= "<label><input type='radio' name='".$ef['label']."' id='".$k."' value='".$k."'".$checked_out."> ".$v."</label>";
			}
			$extra_output .= "
				<fieldset class='form-group ".$ef['label']."'>
					".$ef['name']."
					<div class='radio'>".$options_out."</div>
				</fieldset>
			";

		} elseif ( $ef['type'] == 'checkbox' ) {
			if ( $$ef['label'] != '' ) { $checked_out = " checked"; } else { $checked_out = ''; }
			$extra_output .= "
				<fieldset class='form-group ".$ef['label']."'>
					<div class='col-sm-offset-3 col-sm-3 checkbox'>
						<label>
							<input type='checkbox' name='".$ef['label']."' id='".$ef['label']."' value='please'".$checked_out."> ".$ef['name']."
						</label>
					</div>
				</fieldset>
			";

		}
	}

	$req_class = " <span class='glyphicon glyphicon-asterisk'></span>";
	$form_out = $feedback_out. "
	<form class='row' name='edit_profile_form' action='".$action."' method='post'>
		<div class='form-horizontal col-md-12'>
		<fieldset class='form-group'>
			<label for='user_login' class='col-sm-3 control-label'>Nombre de usuario ".$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_login' class='form-control' type='text' value='".$username."' name='user_login' disabled='disabled' />
			</div>
			<p class='help-block col-sm-4'><small><span class='glyphicon glyphicon-asterisk'></span> Campos requeridos.<br /><strong>El nombre de usuario no se puede cambiar</strong>.</small></p>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_email' class='col-sm-3 control-label'>Correo electrónico ".$req_class."</label>
			<div class='col-sm-5'>
				<input id='user_email' class='form-control' type='text' value='".$email."' name='user_email' />
			</div>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass' class='col-sm-3 control-label'>Nueva contraseña</label>
			<div class='col-sm-5'>
				<input id='user_pass' class='form-control' type='password' size='20' value='' name='user_pass' />
			</div>
			<p class='help-block col-sm-4'><small><strong>Si deseas cambiar la contraseña del usuario</strong>, escribe aquí la nueva. En caso contrario, deja las casillas en blanco.</small></p>
		</fieldset>
		<fieldset class='form-group'>
			<label for='user_pass_confirm' class='col-sm-3 control-label'>Confirma nueva contraseña</label>
			<div class='col-sm-5'>
				<input id='user_pass_confirm' class='form-control' type='password' size='20' value='' name='user_pass_confirm' />
			</div>
			<p class='help-block col-sm-4'><small>Recuerda elegir una contraseña fuerte: incluye letras y números, mayúsculas y minúsculas, caracteres especiales.</small></p>
		</fieldset>
		".$extra_output."
		<fieldset class='form-group'>
			<div class='col-sm-offset-3 col-sm-5'>
				<div class='pull-right'>
					<input id='wp-submit' class='btn btn-primary' type='submit' value='Actualizar' name='wp-submit' />
				</div>
    			</div>
		</fieldset>
		</div>
	</form>
	";
	return $form_out;

} // end edit user profile form

?>
