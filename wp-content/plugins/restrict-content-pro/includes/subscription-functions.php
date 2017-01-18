<?php

/****************************************
* Functions for getting non-member
* specific info about subscription
* levels
*****************************************/

/*
* Gets an array of all available subscription levels
* @param $status string - the status of subscription levels we want to retrieve: active, inactive, or all
* @param $cache bool - whether to pull from a cache or not
* return mixed - array of objects if levels exist, false otherwise
*/
function rcp_get_subscription_levels( $status = 'all' ) {
	global $wpdb, $rcp_db_name;

	$rcp_levels = new RCP_Levels();

	$levels = $rcp_levels->get_levels( array( 'status' => $status ) );

	if( $levels )
		return $levels;
	else
		return array();
}

/*
* Gets all details of a specified subscription level
* @param int $id - the ID of the subscription level to retrieve
* return mixed - object on success, false otherwise
*/
function rcp_get_subscription_details( $id ) {
	$levels = new RCP_Levels();
	$level = $levels->get_level( $id );
	if( $level )
		return $level;
	return false;
}

/*
* Gets all details of a specified subscription level
* @param int $name - the name of the subscription level to retrieve
* return mixed - object on success, false otherwise
*/
function rcp_get_subscription_details_by_name( $name ) {
	global $wpdb, $rcp_db_name;
	$level = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $rcp_db_name . " WHERE name='%s';", $name ) );
	if( $level )
		return $level[0];
	return false;
}

/*
* Gets the name of a specified subscription level
* @param int $id - the ID of the subscription level to retrieve
* return string - name of subscription, or error message on failure
*/
function rcp_get_subscription_name( $id ) {

	$levels_db = new RCP_Levels;
	return stripslashes( $levels_db->get_level_field( $id, 'name' ) );
}

/*
* Gets the duration of a subscription
* @param int $id - the ID of the subscription level to retrieve
* return object - length and unit (m/d/y) of subscription
*/
function rcp_get_subscription_length( $id ) {
	global $wpdb, $rcp_db_name;
	$level_length = $wpdb->get_results( $wpdb->prepare( "SELECT duration, duration_unit FROM " . $rcp_db_name . " WHERE id='%d';", $id ) );
	if( $level_length )
		return $level_length[0];
	return false;
}

/*
* Gets the day of expiration of a subscription from the current day
* @param int $id - the ID of the subscription level to retrieve
* return string - nicely formatted date of expiration
*/
function rcp_calculate_subscription_expiration( $id ) {
	$length = rcp_get_subscription_length( $id );
	return rcp_calc_member_expiration( $length );
}

/*
* Gets the price of a subscription level
* @param int $id - the ID of the subscription level to retrieve
* return mixed - price of subscription level, false on failure
*/
function rcp_get_subscription_price( $id ) {
	$levels = new RCP_Levels();
	$price = $levels->get_level_field( $id, 'price' );
	if( $price )
		return $price;
	return false;
}

/*
* Gets the signup fee of a subscription level
* @param int $id - the ID of the subscription level to retrieve
* return mixed - signup fee if any, false otherwise
*/
function rcp_get_subscription_fee( $id ) {
	$levels = new RCP_Levels();
	$fee = $levels->get_level_field( $id, 'fee' );
	if( $fee )
		return $fee;
	return false;
}

/*
* Gets the description of a subscription level
* @param int $id - the ID of the subscription level to retrieve
* return mixed - description
*/
function rcp_get_subscription_description( $id ) {
	$levels = new RCP_Levels();
	$desc = $levels->get_level_field( $id, 'description' );
	return apply_filters( 'rcp_get_subscription_description', stripslashes( $desc ), $id );
}

/*
* Gets the access level of a subscription package
* @param int $id - the ID of the subscription level to retrieve
* return int - the numerical access level the subscription gives
*/
function rcp_get_subscription_access_level( $id ) {
	$levels = new RCP_Levels();
	$level = $levels->get_level_field( $id, 'level' );
	if( $level )
		return $level;
	return false;
}

/**
 * Retrieve the number of active subscribers on a subscription level
 *
 * @since       2.6
 * @access      public
 * @return      int
*/
function rcp_get_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key   = $id . '_' . $status . '_member_count';
	$count = $rcp_levels_db->get_meta( $id, $key, true );

	if( '' === $count ) {

		$count = rcp_count_members( $id, $status );
		$rcp_levels_db->update_meta( $id, $key, (int) $count );

	}

	$count = (int) max( $count, 0 );

	return apply_filters( 'rcp_get_subscription_member_count', $count, $id, $status );
}

/**
 * Increments the number of active subscribers on a subscription level
 *
 * @since       2.6
 * @access      public
 * @return      void
*/
function rcp_increment_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key    = $id . '_' . $status . '_member_count';
	$count  = rcp_get_subscription_member_count( $id, $status );
	$count += 1;

	$rcp_levels_db->update_meta( $id, $key, (int) $count );

	do_action( 'rcp_increment_subscription_member_count', $id, $count, $status );
}

/**
 * Decrements the number of active subscribers on a subscription level
 *
 * @since       2.6
 * @access      public
 * @return      void
*/
function rcp_decrement_subscription_member_count( $id, $status = 'active' ) {

	global $rcp_levels_db;

	$key    = $id . '_' . $status . '_member_count';
	$count  = rcp_get_subscription_member_count( $id, $status );
	$count -= 1;
	$count  = max( $count, 0 );

	$rcp_levels_db->update_meta( $id, $key, (int) $count );

	do_action( 'rcp_decrement_subscription_member_count', $id, $count, $status );
}

/*
* Get a formatted duration unit name for subscription lengths
* @param string $unit - the duration unit to return a formatted string for
* @param int - the duration of the subscription level
* return string - a formatted unit display. Example "days" becomes "Days". Return is localized
*/
function rcp_filter_duration_unit( $unit, $length ) {
	$new_unit = '';
	switch ( $unit ) :
		case 'day' :
			if( $length > 1 )
				$new_unit = __( 'Days', 'rcp' );
			else
				$new_unit = __( 'Day', 'rcp' );
		break;
		case 'month' :
			if( $length > 1 )
				$new_unit = __( 'Months', 'rcp' );
			else
				$new_unit = __( 'Month', 'rcp' );
		break;
		case 'year' :
			if( $length > 1 )
				$new_unit = __( 'Years', 'rcp' );
			else
				$new_unit = __( 'Year', 'rcp' );
		break;
	endswitch;
	return $new_unit;
}

/*
* Checks to see if there are any paid subscription levels created
*
* @since 1.1.9
* @return boolean - TRUE if paid levels exist, false if only free
*/
function rcp_has_paid_levels() {
	return ( bool ) rcp_get_paid_levels();
}

/**
 * Return the paid levels
 *
 * @since 2.5
 * @return array()
 */
function rcp_get_paid_levels() {

	$paid_levels = array();

	foreach( rcp_get_subscription_levels() as $level ) {
		if( $level->price > 0 && $level->status == 'active' ) {
			$paid_levels[] = $level;
		}
	}

	return apply_filters( 'rcp_get_paid_levels', $paid_levels );

}

/*
* Retrieves available access levels
*
* @since 1.3.2
* @return array
*/
function rcp_get_access_levels() {
	$levels = array(
		0 => 'None',
		1 => '1',
		2 => '2',
		3 => '3',
		4 => '4',
		5 => '5',
		6 => '6',
		7 => '7',
		8 => '8',
		9 => '9',
		10 => '10'
	);
	return apply_filters( 'rcp_access_levels', $levels );
}


/*
 * Generates a new subscription key
 *
 * @since 1.3.2
 * @return array
 */
function rcp_generate_subscription_key() {
	return apply_filters( 'rcp_subscription_key', urlencode( strtolower( md5( uniqid() ) ) ) );
}


/*
 * Determines if a subscription level should be shown
 *
 * @since 1.3.2.3
 * @return bool
 */
function rcp_show_subscription_level( $level_id = 0, $user_id = 0 ) {

	if( empty( $user_id ) )
		$user_id = get_current_user_id();

	$ret = true;

	$user_level = rcp_get_subscription_id( $user_id );
	$sub_length = rcp_get_subscription_length( $level_id );
	$sub_price 	= rcp_get_subscription_price( $level_id );

	// Don't show free trial if user has already used it. Don't show if sub is free and user is already free
	if( ( is_user_logged_in() && $sub_price == '0' && $sub_length->duration > 0 && rcp_has_used_trial( $user_id ) ) || ( is_user_logged_in() && $sub_price == '0' && $user_level == $level_id ) ) {
		$ret = false;
	}

	return apply_filters( 'rcp_show_subscription_level', $ret, $level_id, $user_id );
}


/**
 * Retrieve the subscription levels a post/page is restricted to
 *
 * @since       v1.6
 * @access      public
 * @param       $post_id INT the ID of the post to retrieve levels for
 * @return      array
*/
function rcp_get_content_subscription_levels( $post_id = 0 ) {
	$levels = get_post_meta( $post_id, 'rcp_subscription_level', true );

	if( 'all' == $levels ) {
		// This is for backwards compatibility from when RCP didn't allow content to be restricted to multiple levels
		return false;
	}

	if( 'any' !== $levels && 'any-paid' !== $levels && ! empty( $levels ) && ! is_array( $levels ) ) {
		$levels = array( $levels );
	}
	return apply_filters( 'rcp_get_content_subscription_levels', $levels, $post_id );
}


/**
 * Retrieve the renewal reminder periods
 *
 * @since       v1.6
 * @access      public
 * @return      array
*/
function rcp_get_renewal_reminder_periods() {
	$periods = array(
		'none'      => __( 'None, reminders disabled', 'rcp' ),
		'+1 day'    => __( 'One day before expiration', 'rcp' ),
		'+2 days'   => __( 'Two days before expiration', 'rcp' ),
		'+3 days'   => __( 'Three days before expiration', 'rcp' ),
		'+4 days'   => __( 'Four days before expiration', 'rcp' ),
		'+5 days'   => __( 'Five days before expiration', 'rcp' ),
		'+6 days'   => __( 'Six days before expiration', 'rcp' ),
		'+1 week'   => __( 'One week before expiration', 'rcp' ),
		'+2 weeks'  => __( 'Two weeks before expiration', 'rcp' ),
		'+3 weeks'  => __( 'Three weeks before expiration', 'rcp' ),
		'+1 month'  => __( 'One month before expiration', 'rcp' ),
		'+2 months' => __( 'Two months before expiration', 'rcp' ),
		'+3 months' => __( 'Three months before expiration', 'rcp' ),
	);
	return apply_filters( 'rcp_renewal_reminder_periods', $periods );
}


/**
 * Retrieve the renewal reminder period that is enabled
 *
 * @since       v1.6
 * @access      public
 * @return      string
*/
function rcp_get_renewal_reminder_period() {
	global $rcp_options;
	$period = isset( $rcp_options['renewal_reminder_period'] ) ? $rcp_options['renewal_reminder_period'] : 'none';
	return apply_filters( 'rcp_get_renewal_reminder_period', $period );
}

/**
 * Get taxonomies that can be restricted
 *
 * @since 2.5
 * @param string $output
 * @return mixed|void
 */
function rcp_get_restricted_taxonomies( $output = 'names' ) {
	return apply_filters( 'rcp_get_restricted_taxonomies', get_taxonomies( array( 'public' => true, 'show_ui' => true ), $output ) );
}

/**
 * Get restrictions for the provided term_id
 *
 * @since 2.5
 * @param $term_id
 *
 * @return mixed|void
 */
function rcp_get_term_restrictions( $term_id ) {

	// fallback to older method of handling term meta if term meta does not exist
	if ( ( ! function_exists( 'get_term_meta' ) ) || ! $restrictions = get_term_meta( $term_id, 'rcp_restricted_meta', true ) ) {
		$restrictions = get_option( "rcp_category_meta_$term_id" );
	}

	return apply_filters( 'rcp_get_term_restrictions', $restrictions, $term_id );
}