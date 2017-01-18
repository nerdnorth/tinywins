<?php

function rcp_show_captcha() {
	global $rcp_options;
	// reCaptcha
	if( isset( $rcp_options['enable_recaptcha'] ) && ! empty( $rcp_options['recaptcha_public_key'] ) ) :
?>
	<div id="rcp_recaptcha"class="g-recaptcha" data-sitekey="<?php echo esc_attr( $rcp_options['recaptcha_public_key'] ); ?>"></div>
	<input type="hidden" name="g-recaptcha-remoteip" value=<?php echo esc_attr( rcp_get_ip() ); ?> /><br/>
<?php
	endif;
}
add_action( 'rcp_before_registration_submit_field', 'rcp_show_captcha', 100 );


function rcp_validate_captcha( $data ) {

	global $rcp_options;

	if( ! isset( $rcp_options['enable_recaptcha'] ) || empty( $rcp_options['recaptcha_public_key'] ) || empty( $rcp_options['recaptcha_private_key'] ) ) {
		return;
	}

	if ( empty( $data['g-recaptcha-response'] ) || empty( $data['g-recaptcha-remoteip'] ) ) {
		rcp_errors()->add( 'invalid_recaptcha', __( 'Please verify that you are not a robot', 'rcp' ), 'register' );
		return;
	}

	$verify = wp_safe_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'body' => array(
				'secret'   => trim( $rcp_options['recaptcha_private_key'] ),
				'response' => $data['g-recaptcha-response'],
				'remoteip' => $data['g-recaptcha-remoteip']
			)
		)
	);

	$verify = json_decode( wp_remote_retrieve_body( $verify ) );

	if( empty( $verify->success ) || true !== $verify->success ) {
		rcp_errors()->add( 'invalid_recaptcha', __( 'Please verify that you are not a robot', 'rcp' ), 'register' );
	}

}
add_action( 'rcp_form_errors', 'rcp_validate_captcha' );