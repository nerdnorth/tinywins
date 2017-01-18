<?php

require_once plugin_dir_path( __FILE__ ) . "../../lib/convertkit-api.php";

/**
 * Class ConvertKitContactForm7Integration
 */
class ConvertKitContactForm7Integration {
	protected $api;
	protected $options;

	/**
	 * Constructor
	 */
	public function __construct() {
		$general_options = get_option('_wp_convertkit_settings');
		$this->options   = get_option('_wp_convertkit_integration_wishlistmember_settings');
		$api_key         = $general_options && array_key_exists("api_key", $general_options) ? $general_options['api_key'] : null;
		$api_secret      = $general_options && array_key_exists("api_secret", $general_options) ? $general_options['api_secret'] : null;
		$debug           = $general_options && array_key_exists("debug", $general_options) ? $general_options['debug'] : null;
		$this->api       = new ConvertKitAPI($api_key,$api_secret,$debug);

		add_action( 'wpcf7_submit', array( $this, 'handle_wpcf7_submit' ), 10, 2);
	}

	/**
	 * Handle checking submitted CF7 forms for a CK form mapping.
	 *
	 * If a mapping is found and options exist then the form submitter is subscribed.
	 *
	 * @param $contact_form WPCF7_ContactForm
	 * @param $result
	 */
	public function handle_wpcf7_submit( $contact_form, $result ) {

		if ( $result['demo_mode'] ) {
			return;
		}

		if ( 'mail_sent' == $result['status'] ) {

			$mapping = get_option( '_wp_convertkit_integration_contactform7_settings' );

			foreach ( $mapping as $cf7_id => $ck_id ) {

				if ( $cf7_id == $contact_form->id() ) {
					$submission = WPCF7_Submission::get_instance();
					if ( $submission ) {
						$posted_data = $submission->get_posted_data();

						$name    = $posted_data[ 'your-name' ];
						$email   = $posted_data[ 'your-email' ];

						if ( ! empty( $email ) ) {
							$this->api->form_subscribe( $ck_id, array( 'email' => $email, 'name' => $name ) );
						}
					}
				}
			}
		}
	}

}

$convertkit_contactform7_integration = new ConvertKitContactForm7Integration;
