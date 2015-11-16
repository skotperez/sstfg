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


// user login form 
function sstfg_form_user_login( $redirect_url = '',$register_url,$feedback_out ) {
	$login_action = wp_login_url($redirect_url);
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
function sstfg_form_user_register($register_action,$login_url) {

	if ( array_key_exists('wp-submit',$_POST) && sanitize_text_field($_POST['wp-submit']) == 'Regístrate' ) {
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
			$feedback_type = "danger"; $feedback_text = "<strong>Tienes que aceptar las condiciones legales</strong>. Y quizás leerlas antes de aceptarlas.";

		} else { $feedback_type = ""; }

		if ( $feedback_type != "" ) { $feedback_out = "<div class='alert alert-".$feedback_type."' role='alert'>".$feedback_text."</div>"; }
		else {
			if ( $pass == '' ) { $pass = wp_generate_password( 12, false ); }
			$user_id = wp_create_user( $username, $pass, $email );

			wp_redirect(get_permalink()."?action=login&register=success");
			exit;
		}

	} else { $username = ""; $email = ""; $feedback_out = ""; }

	$req_class = " <span class='glyphicon glyphicon-asterisk'></span>";
	$form_out = $feedback_out. "
	<form class='row' name='registerform' action='".$register_action."' method='post'>
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



add_shortcode('sstfg_login_register', 'sstfg_show_user_form');
// show user login/signup form
function sstfg_show_user_form( $atts ) {
	if ( is_user_logged_in() ) return;

	extract( shortcode_atts( array(
		'redirect_url' => '',
	), $atts ));
	$redirect_url = preg_replace("/\?.*$/","",$redirect_url);
	$login_action = wp_login_url($redirect_url);
	$login_url = get_permalink()."?action=login";
	$register_url = get_permalink()."?action=register";

//	if ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) != 'login' || !array_key_exists('action',$_GET) ) { // if action is register
	if ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'register' ) { // if action is register		
		return sstfg_form_user_register($register_url,$login_url);

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

		return sstfg_form_user_login($redirect_url,$register_url,$feedback_out);

//	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'register' ) { // if action is register
//	} elseif ( array_key_exists('action',$_GET) && sanitize_text_field($_GET['action']) == 'edit' ) { // if action is edit profile
//		return hce_edit_userdata_form();

	} // end if action register or log in
	
} // end show user login/signup form

?>
