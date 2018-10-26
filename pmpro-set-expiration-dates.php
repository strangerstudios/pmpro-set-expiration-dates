<?php

/*
Plugin Name: Paid Memberships Pro - Set Expiration Dates Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-set-expiration-dates/
Description: Set a specific expiration date (e.g. 2013-12-31) for a PMPro membership level or discount code. 
Version: .3.2
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

/*
	This first set of functions adds our fields to the edit membership levels page
*/
//add level cost text field to level price settings
function pmprosed_pmpro_membership_level_after_other_settings()
{
    $level_id = intval($_REQUEST['edit']);
    if ($level_id > 0)
        $set_expiration_date = pmpro_getSetExpirationDate($level_id);
    else
        $set_expiration_date = "";
    ?>
    <h3 class="topborder">Set Expiration Date</h3>
    <p>To have this level expire on a specific date, enter it below in YYYY-MM-DD format. <strong>Note:</strong> You
        must also set an expiration date above (e.g. 1 Year) which will be overwritten by the value below.</p>
    <table>
        <tbody class="form-table">
        <tr>
            <th scope="row" valign="top"><label for="set_expiration_date">Expiration Date:</label></th>
            <td>
                <input type="text" name="set_expiration_date" value="<?php echo esc_attr($set_expiration_date); ?>"/>
                <br/>
                <small>YYYY-MM-DD format. Enter "Y" for current year, "Y2" for next year. M, M2 for current/next
                    month.
                </small>
            </td>
        </tr>
        </tbody>
    </table>
    <?php
}
add_action("pmpro_membership_level_after_other_settings", "pmprosed_pmpro_membership_level_after_other_settings");

//save level cost text when the level is saved/added
function pmprosed_pmpro_save_membership_level($level_id)
{
    pmpro_saveSetExpirationDate($level_id, $_REQUEST['set_expiration_date']);            //add level cost text for this level
}
add_action("pmpro_save_membership_level", "pmprosed_pmpro_save_membership_level");

/*
	Function to replace Y and M/etc with actual dates
	Modified by aquiferweb to allow calculation from an existing date,
	rather than always using current time
*/
function pmprosed_fixDate($set_expiration_date,$timestamp_to_work_from)
{
    // handle lower-cased y/m values.
    $set_expiration_date = strtoupper($set_expiration_date);

    //vars to tell us which placeholders are being used
    $has_M = (strpos($set_expiration_date, "M") !== false);
    $has_Y = (strpos($set_expiration_date, "Y") !== false);

    $Y = date("Y", $timestamp_to_work_from);
    $Y2 = intval($Y) + 1;
    $M = date("m", $timestamp_to_work_from);
    if ($M == 12) {
        //set to Jan
        $M2 = "01";

        //set this year to next
        if ($has_Y) {
            $Y = $Y2;
        }
    } else
        $M2 = str_pad(intval($M) + 1, 2, "0", STR_PAD_LEFT);
    $searches = array("Y-", "Y2-", "M-", "M2-");
    $replacements = array($Y . "-", $Y2 . "-", $M . "-", $M2 . "-");

    //note I changed the var name here
    $new_expiration_date = str_replace($searches, $replacements, $set_expiration_date);

    //make sure we don't set expiration dates in the past
    if ($new_expiration_date <= date('Y-m-d', current_time('timestamp'))) {
        if ($has_M) {
            $new_expiration_date = str_replace("M-", "M2-", $set_expiration_date);
        } else {
            $new_expiration_date = str_replace("Y-", "Y2-", $set_expiration_date);  //assume has_Y
        }

        $new_expiration_date = str_replace($searches, $replacements, $new_expiration_date);
    }

    //make sure we use the right day of the month for dates > 28
    $dotm = pmpro_getMatches('/\-([0-3][0-9]$)/', $new_expiration_date, true);
    if (intval($dotm) > 28) {
        $new_expiration_date = date('Y-m-t', strtotime(substr($new_expiration_date, 0, 8) . "01"));
    }

    return $new_expiration_date;
}

/*
	Update expiration date of level at checkout.
*/
function pmprosed_pmpro_checkout_level($level, $discount_code_id = null)
{
    global $wpdb;

    if (empty($discount_code_id) && !empty($_REQUEST['discount_code'])) {
        //get discount code passed in
        $discount_code = preg_replace("/[^A-Za-z0-9\-]/", "", $_REQUEST['discount_code']);

        if (!empty($discount_code)) {
            $discount_code_id = $wpdb->get_var("SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = '" . esc_sql($discount_code) . "' LIMIT 1");
        } else {
            $discount_code_id = NULL;
        }
    }

    //does this level have a set expiration date?
    $set_expiration_date = pmpro_getSetExpirationDate($level->id, $discount_code_id);

    //check for Y
    if (strpos($set_expiration_date, "Y") !== false) {
        $used_y = true;
    }

    if (!empty($set_expiration_date)) {
		
		// check if the user already has an expiration date for this level
		// use this if they do, if not, use current timestamp
		$timestamp_to_work_from = pmprosed_get_existing_expiry_date($level);
   
        //replace vars
		// added $timestamp_to_work_from to allow calculation from existing expiry date
        $set_expiration_date = pmprosed_fixDate($set_expiration_date,$timestamp_to_work_from);

        //how many days until expiration
        $todays_date = time();
        $time_left = strtotime($set_expiration_date) - $todays_date;
        if ($time_left > 0) {
            $days_left = ceil($time_left / (60 * 60 * 24));

            //update number and period
            $level->expiration_number = $days_left;
            $level->expiration_period = "Day";

            return $level;    //stop
        } elseif (!empty($used_y)) {
            $timestamp = strtotime($set_expiration_date);

            //add one year to expiration date
            $set_expiration_date = date("Y-m-d", mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp) + 1));

            //how many days until expiration
            $time_left = strtotime($set_expiration_date) - $todays_date;
            $days_left = ceil($time_left / (60 * 60 * 24));

            //update number and period
            $level->expiration_number = $days_left;
            $level->expiration_period = "Day";

            return $level; //stop
        } else {
            //expiration already here, don't let people signup
            $level = NULL;

            return $level; //stop
        }
    }

    return $level;    //no change
}
add_filter("pmpro_checkout_level", "pmprosed_pmpro_checkout_level");
add_filter('pmpro_discount_code_level', 'pmprosed_pmpro_checkout_level', 10, 2);

/*
	Update expiration date of level during IPN requests
	NB almost identical to previous function EXCEPT uses different arguments
	Does not currently change any functionality, but allows later modifications that rely on knowing the $user_id during an IPN request
*/
function pmprosed_pmpro_ipnhandler($level, $user_id)
{
    global $wpdb;

	// removed first mention of $discount_code_id here as it is not passed to this function
	// unsure if $_REQUEST is ever used with IPN??
	// Left this section in in case it is (aquiferweb)
    if (!empty($_REQUEST['discount_code'])) {
        //get discount code passed in
        $discount_code = preg_replace("/[^A-Za-z0-9\-]/", "", $_REQUEST['discount_code']);
        if (!empty($discount_code)) {
            $discount_code_id = $wpdb->get_var("SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = '" . esc_sql($discount_code) . "' LIMIT 1");
        } else {
            $discount_code_id = NULL;
        }
    }else{
		$discount_code_id = NULL;
	}

    //does this level have a set expiration date?
    $set_expiration_date = pmpro_getSetExpirationDate($level->id, $discount_code_id);

    //check for Y
    if (strpos($set_expiration_date, "Y") !== false) {
        $used_y = true;
    }

    if (!empty($set_expiration_date)) {
			
		// check if the user already has an expiration date for this level
		// use this if they do, if not, use current timestamp
		$timestamp_to_work_from = pmprosed_get_existing_expiry_date($level,$user_id);
		
        //replace vars
		// added $timestamp_to_work_from to allow calculation from existing expiry date
        $set_expiration_date = pmprosed_fixDate($set_expiration_date,$timestamp_to_work_from);

        //how many days until expiration
        $todays_date = time();
        $time_left = strtotime($set_expiration_date) - $todays_date;
        if ($time_left > 0) {
            $days_left = ceil($time_left / (60 * 60 * 24));

            //update number and period
            $level->expiration_number = $days_left;
            $level->expiration_period = "Day";

            return $level;    //stop
        } elseif (!empty($used_y)) {
            $timestamp = strtotime($set_expiration_date);

            //add one year to expiration date
            $set_expiration_date = date("Y-m-d", mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp) + 1));

            //how many days until expiration
            $time_left = strtotime($set_expiration_date) - $todays_date;
            $days_left = ceil($time_left / (60 * 60 * 24));

            //update number and period
            $level->expiration_number = $days_left;
            $level->expiration_period = "Day";

            return $level; //stop
        } else {
            //expiration already here, don't let people signup
            $level = NULL;

            return $level; //stop
        }
    }

    return $level;    //no change
}

// added priority and number of variables here
add_filter('pmpro_ipnhandler_level', 'pmprosed_pmpro_ipnhandler',15,2);

/*	
	This function will save a the set expiration dates into wp_options.
*/
function pmpro_saveSetExpirationDate($level_id, $set_expiration_date, $code_id = NULL)
{
    if ($code_id) {
        $key = "pmprosed_" . $level_id . "_" . $code_id;
    } else {
        $key = "pmprosed_" . $level_id;
    }

    update_option($key, $set_expiration_date);
}

/*
	This function will return the expiration date for a level or discount code/level combo
*/
function pmpro_getSetExpirationDate($level_id, $code_id = NULL)
{
    if ($code_id) {
        $key = "pmprosed_" . $level_id . "_" . $code_id;
    } else {
        $key = "pmprosed_" . $level_id;
    }

    return get_option($key, "");
}


/*
	This next set of functions adds our field to the edit discount code page
*/
//add our field to level price settings
function pmprosed_pmpro_discount_code_after_level_settings($code_id, $level)
{
    $set_expiration_date = pmpro_getSetExpirationDate($level->id, $code_id);
    ?>
    <table>
        <tbody class="form-table">
        <tr>
            <td>
        <tr>
            <th scope="row" valign="top"><label for="set_expiration_date">Expiration Date:</label></th>
            <td>
                <input type="text" name="set_expiration_date[]" value="<?php echo esc_attr($set_expiration_date); ?>"/>
                <br/>
                <small>YYYY-MM-DD format. Enter "Y" for current year, "Y2" for next year. M, M2 for current/next month.
                    Be sure to set an expiration date above as well.
                </small>
            </td>
        </tr>
        </td>
        </tr>
        </tbody>
    </table>
    <?php
}
add_action("pmpro_discount_code_after_level_settings", "pmprosed_pmpro_discount_code_after_level_settings", 10, 2);

//save level cost text for the code when the code is saved/added
function pmprosed_pmpro_save_discount_code_level($code_id, $level_id)
{
    $all_levels_a = $_REQUEST['all_levels'];                            //array of level ids checked for this code
    $set_expiration_date_a = $_REQUEST['set_expiration_date'];            //expiration dates for levels checked

    if (!empty($all_levels_a)) {
        $key = array_search($level_id, $all_levels_a);                //which level is it in the list?
        pmpro_saveSetExpirationDate($level_id, $set_expiration_date_a[$key], $code_id);
    }
}
add_action("pmpro_save_discount_code_level", "pmprosed_pmpro_save_discount_code_level", 10, 2);

/*
Function to add links to the plugin row meta
*/
function pmprosed_plugin_row_meta($links, $file)
{
    if (strpos($file, 'pmpro-set-expiration-dates.php') !== false) {
        $new_links = array(
            '<a href="' . esc_url('http://www.paidmembershipspro.com/add-ons/plugins-on-github/pmpro-expiration-date/') . '" title="' . esc_attr(__('View Documentation', 'pmpro')) . '">' . __('Docs', 'pmpro') . '</a>',
            '<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr(__('Visit Customer Support Forum', 'pmpro')) . '">' . __('Support', 'pmpro') . '</a>',
        );
        $links = array_merge($links, $new_links);
    }
    return $links;
}
add_filter('plugin_row_meta', 'pmprosed_plugin_row_meta', 10, 2);

/*
	Update expiration text on levels page.
*/
function pmprosed_pmpro_level_expiration_text($expiration_text, $level)
{
    $set_expiration_date = pmpro_getSetExpirationDate($level->id);

    if (!empty($set_expiration_date)) {
		$timestamp_to_work_from = pmprosed_get_existing_expiry_date($level);		
        $set_expiration_date = pmprosed_fixDate($set_expiration_date,$timestamp_to_work_from);
        $expiration_text = "Membership expires on " . date(get_option('date_format'), strtotime($set_expiration_date, current_time('timestamp'))) . ".";
    }

    return $expiration_text;
}
add_filter('pmpro_level_expiration_text', 'pmprosed_pmpro_level_expiration_text', 10, 2);


// Gets current expiration date of a user (for the level being renewed) 
											  


function pmprosed_get_existing_expiry_date($level,$user_id = null){
	
	// if called during an IPN request, we need to use $user_id passed in by the filter ($user_id is not null)
	// if called by the checkout_level or discount_code filters, we can use $current_user, (and $user_id will be null to start with)
	global $current_user;
	if(!$user_id) $user_id = $current_user->ID;	
	
															  
	// get the current enddate of the membership (for current level)
	$user_level = pmpro_getSpecificMembershipLevelForUser( $user_id, $level->id );
	
	// calculate time remaining for current level
  
											
	$todays_date = current_time( 'timestamp' );
	$expiration_date = $user_level->enddate;
	$time_left = $expiration_date - $todays_date;
	
	if ( empty( $user_level ) || empty( $user_level->enddate ) || $time_left<=0 ) {
		// user has no future expiration date
		// calculate new date from today's timestamp
		$timestamp_to_work_from = current_time('timestamp');
	}else{
		// user has an expiration date in the future for this level
		// return that date 
		// NB we also add one day to ensure that, if we are on the last day of the month, that we move to the first day of the next month
																	
		// this tries to avoid problems when working with the last day of the month	
																	 
		$timestamp_to_work_from = strtotime("+1 day",$user_level->enddate);
	}

	return $timestamp_to_work_from;
}