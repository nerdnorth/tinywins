<?php

function rcp_email_subscription_status( $user_id, $status = 'active' ) {

	global $rcp_options;

	$user_info     = get_userdata( $user_id );
	$message       = '';
	$admin_message = '';

	$admin_emails   = array();
	$admin_emails[] = get_option('admin_email');
	$admin_emails   = apply_filters( 'rcp_admin_notice_emails', $admin_emails );

	$site_name      = stripslashes_deep( html_entity_decode( get_bloginfo('name'), ENT_COMPAT, 'UTF-8' ) );

	$from_name      = isset( $rcp_options['from_name'] ) ? $rcp_options['from_name'] : $site_name;
	$from_name      = apply_filters( 'rcp_emails_from_name', $from_name, $user_id, $status );

	$from_email     = isset( $rcp_options['from_email'] ) ? $rcp_options['from_email'] : get_option( 'admin_email' );
	$from_email     = apply_filters( 'rcp_emails_from_address', $from_email );

	$headers        = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers       .= "Reply-To: ". $from_email . "\r\n";
	$headers        = apply_filters( 'rcp_email_headers', $headers, $user_id, $status );

	// Allow add-ons to add file attachments.
	$attachments = apply_filters( 'rcp_email_attachments', array(), $user_id, $status );

	switch ($status) :

		case "active" :

			if( rcp_is_trialing( $user_id ) ) {
				break;
			}

			if( ! isset( $rcp_options['disable_active_email'] ) ) {

				$message = isset( $rcp_options['active_email'] ) ? $rcp_options['active_email'] : '';
				$message = apply_filters( 'rcp_subscription_active_email', $message, $user_id, $status );
				$subject = isset( $rcp_options['active_subject'] ) ? $rcp_options['active_subject'] : '';
				$subject = apply_filters( 'rcp_subscription_active_subject', $subject, $user_id, $status );
				wp_mail( $user_info->user_email, $subject, rcp_filter_email_tags( $message, $user_id, $user_info->display_name), $headers, $attachments );

			}

			if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
				$admin_message = __('Hello', 'rcp') . "\n\n" . $user_info->display_name .  ' (' . $user_info->user_login . ') ' . __('is now subscribed to', 'rcp') . ' ' . $site_name . ".\n\n" . __('Subscription level', 'rcp') . ': ' . rcp_get_subscription($user_id) . "\n\n";
				$admin_message = apply_filters('rcp_before_admin_email_active_thanks', $admin_message, $user_id);
				$admin_message .= __('Thank you', 'rcp');
				wp_mail( $admin_emails, __('New subscription on ', 'rcp') . $site_name, $admin_message, $headers, $attachments );
			}
		break;

		case "cancelled" :

			if( ! isset( $rcp_options['disable_cancelled_email'] ) ) {

				$message = isset( $rcp_options['cancelled_email'] ) ? $rcp_options['cancelled_email'] : '';
				$message = apply_filters( 'rcp_subscription_cancelled_email', $message, $user_id, $status );
				$subject = isset( $rcp_options['cancelled_subject'] ) ? $rcp_options['cancelled_subject'] : '';
				$subject = apply_filters( 'rcp_subscription_cancelled_subject', $subject, $user_id, $status );
				wp_mail( $user_info->user_email, $subject, rcp_filter_email_tags($message, $user_id, $user_info->display_name), $headers, $attachments );

			}

			if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
				$admin_message = __('Hello', 'rcp') . "\n\n" . $user_info->display_name .  ' (' . $user_info->user_login . ') ' . __('has cancelled their subscription to', 'rcp') . ' ' . $site_name . ".\n\n" . __('Their subscription level was', 'rcp') . ': ' . rcp_get_subscription($user_id) . "\n\n";
				$admin_message = apply_filters('rcp_before_admin_email_cancelled_thanks', $admin_message, $user_id);
				$admin_message .= __('Thank you', 'rcp');
				wp_mail( $admin_emails, __('Cancelled subscription on ', 'rcp') . $site_name, $admin_message, $headers, $attachments );
			}

		break;

		case "expired" :

			if( ! isset( $rcp_options['disable_expired_email'] ) ) {

				$message = isset( $rcp_options['expired_email'] ) ? $rcp_options['expired_email'] : '';
				$message = apply_filters( 'rcp_subscription_expired_email', $message, $user_id, $status );

				$subject = isset( $rcp_options['expired_subject'] ) ? $rcp_options['expired_subject'] : '';
				$subject = apply_filters( 'rcp_subscription_expired_subject', $subject, $user_id, $status );

				wp_mail( $user_info->user_email, $subject, rcp_filter_email_tags($message, $user_id, $user_info->display_name), $headers, $attachments );

				add_user_meta( $user_id, '_rcp_expired_email_sent', 'yes' );

			}

			if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
				$admin_message = __('Hello', 'rcp') . "\n\n" . $user_info->display_name . "'s " . __('subscription has expired', 'rcp') . "\n\n";
				$admin_message = apply_filters('rcp_before_admin_email_expired_thanks', $admin_message, $user_id);
				$admin_message .= __('Thank you', 'rcp');
				wp_mail( $admin_emails, __('Expired subscription on ', 'rcp') . $site_name, $admin_message, $headers, $attachments );
			}



		break;

		case "free" :

			if( ! isset( $rcp_options['disable_free_email'] ) ) {

				$message = isset( $rcp_options['free_email'] ) ? $rcp_options['free_email'] : '';
				$message = apply_filters( 'rcp_subscription_free_email', $message, $user_id, $status );

				$subject = isset( $rcp_options['free_subject'] ) ? $rcp_options['free_subject'] : '';
				$subject = apply_filters( 'rcp_subscription_free_subject', $subject, $user_id, $status );

				wp_mail( $user_info->user_email, $subject, rcp_filter_email_tags($message, $user_id, $user_info->display_name), $headers, $attachments );

			}

			if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
				$admin_message = __('Hello', 'rcp') . "\n\n" . $user_info->display_name .  ' (' . $user_info->user_login . ') ' . __('is now subscribed to', 'rcp') . ' ' . $site_name . ".\n\n" . __('Subscription level', 'rcp') . ': ' . rcp_get_subscription($user_id) . "\n\n";
				$admin_message = apply_filters('rcp_before_admin_email_free_thanks', $admin_message, $user_id);
				$admin_message .= __('Thank you', 'rcp');
				wp_mail( $admin_emails, __('New free subscription on ', 'rcp') . $site_name, $admin_message, $headers, $attachments );
			}

		break;

		case "trial" :

			if( ! isset( $rcp_options['disable_trial_email'] ) ) {

				$message = isset( $rcp_options['trial_email'] ) ? $rcp_options['trial_email'] : '';
				$message = apply_filters( 'rcp_subscription_trial_email', $message, $user_id, $status );

				$subject = isset( $rcp_options['trial_subject'] ) ? $rcp_options['trial_subject'] : '';
				$subject = apply_filters( 'rcp_subscription_trial_subject', $subject, $user_id, $status );

				wp_mail( $user_info->user_email, $subject, rcp_filter_email_tags($message, $user_id, $user_info->display_name), $headers, $attachments );

			}

			if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
				$admin_message = __('Hello', 'rcp') . "\n\n" . $user_info->display_name .  ' (' . $user_info->user_login . ') ' . __('is now subscribed to', 'rcp') . ' ' . $site_name . ".\n\n" . __('Subscription level', 'rcp') . ': ' . rcp_get_subscription($user_id) . "\n\n";
				$admin_message = apply_filters('rcp_before_admin_email_trial_thanks', $admin_message, $user_id);
				$admin_message .= __('Thank you', 'rcp');
				wp_mail( $admin_emails, __('New trial subscription on ', 'rcp') . $site_name, $admin_message, $headers, $attachments );
			}

		break;

		default:
			break;

	endswitch;
}

function rcp_email_expiring_notice( $user_id = 0 ) {

	global $rcp_options;
	$user_info = get_userdata( $user_id );
	$message   = ! empty( $rcp_options['renew_notice_email'] ) ? $rcp_options['renew_notice_email'] : false;

	if( ! $message ) {
		return;
	}

	$site_name  = stripslashes_deep( html_entity_decode( get_bloginfo('name'), ENT_COMPAT, 'UTF-8' ) );

	$from_name  = isset( $rcp_options['from_name'] ) ? $rcp_options['from_name'] : $site_name;
	$from_name  = apply_filters( 'rcp_emails_from_name', $from_name, $user_id, 'active' );

	$from_email = isset( $rcp_options['from_email'] ) ? $rcp_options['from_email'] : get_option( 'admin_email' );
	$from_email = apply_filters( 'rcp_emails_from_address', $from_email );

	$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers    .= "Reply-To: ". $from_email . "\r\n";
	$headers    = apply_filters( 'rcp_email_headers', $headers, $user_id, 'active' );

	$message    = rcp_filter_email_tags( $message, $user_id, $user_info->display_name );

	wp_mail( $user_info->user_email, $rcp_options['renewal_subject'], $message, $headers );
}

function rcp_filter_email_tags( $message, $user_id, $display_name ) {

	$user = get_userdata( $user_id );

	$site_name = stripslashes_deep( html_entity_decode( get_bloginfo('name'), ENT_COMPAT, 'UTF-8' ) );

	$rcp_payments = new RCP_Payments();

	$message = str_replace( '%blogname%', $site_name, $message );
	$message = str_replace( '%username%', $user->user_login, $message );
	$message = str_replace( '%useremail%', $user->user_email, $message );
	$message = str_replace( '%firstname%', html_entity_decode( $user->user_firstname, ENT_COMPAT, 'UTF-8' ), $message );
	$message = str_replace( '%lastname%', html_entity_decode( $user->user_lastname, ENT_COMPAT, 'UTF-8' ), $message );
	$message = str_replace( '%displayname%', html_entity_decode( $display_name, ENT_COMPAT, 'UTF-8' ), $message );
	$message = str_replace( '%expiration%', rcp_get_expiration_date( $user_id ), $message );
	$message = str_replace( '%subscription_name%', html_entity_decode( rcp_get_subscription($user_id), ENT_COMPAT, 'UTF-8' ), $message );
	$message = str_replace( '%subscription_key%', rcp_get_subscription_key($user_id), $message );
	$message = str_replace( '%amount%', html_entity_decode( rcp_currency_filter( $rcp_payments->last_payment_of_user( $user_id ) ), ENT_COMPAT, 'UTF-8' ), $message );

	return apply_filters( 'rcp_email_tags', $message, $user_id );
}

/**
 * Triggers the expiration notice when an account is marked as expired.
 *
 * @access  public
 * @since   2.0.9
 * @return  void
 */
function rcp_email_on_expiration( $status, $user_id ) {

	if( 'expired' == $status ) {

		// Send expiration email.
		rcp_email_subscription_status( $user_id, 'expired' );

	}

}
add_action( 'rcp_set_status', 'rcp_email_on_expiration', 11, 2 );

/**
 * Triggers the activation notice when an account is marked as active.
 *
 * @access  public
 * @since   2.1
 * @return  void
 */
function rcp_email_on_activation( $status, $user_id ) {

	if( 'active' == $status && get_user_meta( $user_id, '_rcp_new_subscription', true ) ) {

		// Send welcome email.
		rcp_email_subscription_status( $user_id, 'active' );

	}

}
add_action( 'rcp_set_status', 'rcp_email_on_activation', 11, 2 );

/**
 * Triggers the cancellation notice when an account is marked as cancelled.
 *
 * @access  public
 * @since   2.1
 * @return  void
 */
function rcp_email_on_cancellation( $status, $user_id ) {

	if( 'cancelled' == $status ) {

		// Send cancellation email.
		rcp_email_subscription_status( $user_id, 'cancelled' );

	}

}
add_action( 'rcp_set_status', 'rcp_email_on_cancellation', 11, 2 );

/**
 * Triggers an email to the member when a payment is received.
 *
 * @access  public
 * @since   2.3
 * @return  void
 */
function rcp_email_payment_received( $payment_id, $args ) {

	global $rcp_options;

	if ( isset( $rcp_options['disable_payment_received_email'] ) ) {
		return;
	}

	$user_info = get_userdata( $args['user_id'] );

	if( ! $user_info ) {
		return;
	}

	$message = ! empty( $rcp_options['payment_received_email'] ) ? $rcp_options['payment_received_email'] : false;
	$message = rcp_filter_email_tags( $message, $args['user_id'], $user_info->display_name );
	$message = apply_filters( 'rcp_payment_received_email', $message, $payment_id, $args );

	if( ! $message ) {
		return;
	}

	$site_name  = stripslashes_deep( html_entity_decode( get_bloginfo('name'), ENT_COMPAT, 'UTF-8' ) );
	$from_name  = isset( $rcp_options['from_name'] ) ? $rcp_options['from_name'] : $site_name;
	$from_name  = apply_filters( 'rcp_emails_from_name', $from_name, $args['user_id'], rcp_get_status( $args['user_id'] ) );

	$from_email = isset( $rcp_options['from_email'] ) ? $rcp_options['from_email'] : get_option( 'admin_email' );
	$from_email = apply_filters( 'rcp_emails_from_address', $from_email );

	$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers   .= "Reply-To: ". $from_email . "\r\n";
	$headers    = apply_filters( 'rcp_email_headers', $headers, $args['user_id'], rcp_get_status( $args['user_id'] ) );

	wp_mail( $user_info->user_email, $rcp_options['payment_received_subject'], $message, $headers );
}
add_action( 'rcp_insert_payment', 'rcp_email_payment_received', 10, 2 );
