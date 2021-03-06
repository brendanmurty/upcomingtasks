<?php

// user_authenticate - Handle response from Basecamp authentication
function user_authenticate($auth_code = ''){
	if($auth_code != ''){
		$oauth_authenticate_url = $GLOBALS['auth_api_confirm_url'] . '&code=' . $auth_code;
		$cu = curl_init();
		$options = array(
			CURLOPT_URL => $oauth_authenticate_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $oauth_authenticate_url,
			CURLOPT_SSL_VERIFYPEER => false
		);
		curl_setopt_array($cu, $options);

		$result = json_decode(curl_exec($cu), true);

		if (!$result) {
			// Authentication issue
			error_handle('auth', 'Could not extract Basecamp authentication information', $_SERVER['SCRIPT_FILENAME'], '');
			redirect('/pages/home.php');
		}

		// Extract the relevant details from the Basecamp response
		if (isset($result['access_token'])) {
			$bc_token = $result['access_token'];
			$bc_account = bc_account($bc_token);

			if (!$bc_account) {
				// Can't find a suitable Basecamp account
				$root_path = dirname(dirname(__FILE__));
				include_once $root_path . '/libs/header.php';
				print '<p class="error">Sorry, no eligible Basecamp 2 accounts were found. Please <a href="https://basecamp.com/2">create a Basecamp 2 account</a> to use this app.</p>';
				include_once $root_path . '/libs/footer.php';
				exit();
			}

			$bc_id = bc_user_id($bc_token, $bc_account);

			if (!$bc_id) {
				// Can't extract the user's Basecamp ID, attempt to find a relevant database entry
				$db = db_connect();
				$sql = "SELECT bc_id FROM users WHERE bc_account = " . db_clean($db, $bc_account) . " AND bc_token = '' AND bc_id <> 0 LIMIT 1";
				$result = db_query($db, $sql);
				db_disconnect($db);

				if (is_array($result) && array_key_exists('bc_id', $result)) {
					// Relevant database entry found, use this Basecamp ID
					$bc_id = $result['bc_id'];
				} else {
					// No relevant database entry found, log the error and redirect to the home page
					error_handle('auth', 'Could not determine Basecamp user ID for account ' . $bc_account . ' - ' . print_r($result, true), $_SERVER['SCRIPT_FILENAME'], '');
					redirect('/pages/home.php');
				}
			}

			// Setup the user session
			if (!isset($_SESSION)) {
				session_start();
			}

			// Add a cookie to store the current Basecamp ID with a 14 day expiry
			setcookie("bc_id", $bc_id, time()+60*60*24*14);

			// Get extra user details
			$bc_email = '';
			$bc_name = '';
			$result2 = bc_results_main('https://launchpad.37signals.com/authorization.json',$bc_token,$bc_account);
			if ($result2) {
				$bc_email = $result2['identity']['email_address'];
				$bc_name_first = $result2['identity']['first_name'];
				$bc_name_last = $result2['identity']['last_name'];
			}

			// Save the details to the database
			$db = db_connect();
			$sql_values = "bc_account=" . db_clean($db, $bc_account). ", first_name=" . db_clean($db, $bc_name_first) . ", last_name=" . db_clean($db, $bc_name_last) . ", ";
			$sql_values .= "email=" . db_clean($db, $bc_email) . ", bc_token=" . db_clean($db, $bc_token);
			$sql = "INSERT INTO users SET bc_id = " . db_clean($db, $bc_id) . ", $sql_values ON DUPLICATE KEY UPDATE $sql_values";
			db_query($db, $sql);
			db_disconnect($db);

			// Redirect to account selection page
			redirect('/pages/account.php');
		}
	}
}

// user_email - Get the email of the logged in user
function user_email(){
	if (user_exists()) {
	    $db = db_connect();
		$sql = "SELECT email FROM users WHERE bc_id=" . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);
		if(is_array($result)){
			if(array_key_exists('email',$result)){
				return $result['email'];
			}
		}
	}
}

// user_name - Get the full name of the logged in user
function user_name(){
	$name = '';

	if (user_exists()) {
	    $db = db_connect();
		$sql = "SELECT first_name, last_name FROM users WHERE bc_id=" . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);
		if (is_array($result)) {
			if (array_key_exists('first_name', $result)) {
				$name = $result['first_name'];
			}

			if (array_key_exists('last_name', $result)) {
				$name .= ' ' . $result['last_name'];
			}
		}
	}

	return $name;
}

// user_exists - Check if a user is logged in
function user_exists(){
	if(user_id() > 0){
		return true;
	}else{
		return false;
	}
}

// user_id - Get the Basecamp ID of the logged in user
function user_id(){
	if (!isset($_SESSION)) {
		session_start();
	}

	if(isset($_COOKIE['bc_id'])){
		return input_clean($_COOKIE['bc_id'], 'numeric');
	}else{
		return 0;
	}
}

// user_account - Get the Basecamp account ID of the logged in user
function user_account() {
    if (user_exists()) {
		$db = db_connect();
		$sql = 'SELECT bc_account FROM users WHERE bc_id=' . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);

		if (is_array($result)) {
			if ($result['bc_account']) {
				return $result['bc_account'];
			}
		}
	}

	return 0;
}

// user_stripe_get - Get the Stripe customer ID of the logged in user
function user_stripe_get() {
    if (user_exists()) {
		$db = db_connect();
		$sql = 'SELECT stripe_customer_id FROM users WHERE bc_id=' . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);

		if (is_array($result)) {
			if ($result['stripe_customer_id']) {
				return $result['stripe_customer_id'];
			}
		}
	}

	return false;
}

// user_stripe_set - Set the Stripe customer ID of the logged in user
function user_stripe_set($stripe_customer_id) {
    if (user_exists()) {
		$db = db_connect();
		$sql = 'UPDATE users SET stripe_customer_id = ' . db_clean($db, $stripe_customer_id) . ' WHERE bc_id=' . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);
	}
}

// user_stripe_remove - Remove the Stripe customer ID of the logged in user
function user_stripe_remove() {
	if (user_exists()) {
		$db = db_connect();
		$sql = 'UPDATE users SET stripe_customer_id = NULL WHERE bc_id=' . db_clean($db, user_id());
		$result = db_query($db, $sql);
		db_disconnect($db);
	}
}

// user_timezone_get - Get the user's selected timezone from the database
function user_timezone_get() {
	if (isset($_COOKIE['timezone'])) {
		return $_COOKIE['timezone'];
	} else {
		if (user_exists()) {
			$db = db_connect();
			$sql = 'SELECT timezone FROM users WHERE bc_id=' . db_clean($db, user_id());
			$result = db_query($db, $sql);
			db_disconnect($db);

			if (is_array($result)) {
				if ($result['timezone']) {
					return $result['timezone'];
				}
			}
		}
	}

	// Default to Sydney
	return 'Australia/Sydney';
}

// user_timezone_set - Store the user's selected timezone to the database and as a browser cookie
function user_timezone_set($timezone = 'Australia/Sydney') {
    if (user_exists()) {
        $db = db_connect();
    	$sql = 'UPDATE users SET timezone=' . db_clean($db, $timezone) . ' WHERE bc_id=' . db_clean($db, user_id());
    	db_query($db, $sql);
    	db_disconnect($db);
    }

    setcookie("timezone", $timezone, time()+60*60*24*14);
    date_default_timezone_set($timezone);
}

// pro_user - Check if the current user or a specific user has subscribed to the Pro account
function pro_user($bc_id = '') {
	$user_id = '';
	if ($bc_id != '') {
		$user_id = $bc_id;
	} else {
		if (user_exists()) {
			$user_id = user_id();
		}
	}
	if ($user_id != '') {
		$db = db_connect();
		$sql = 'SELECT pro FROM users WHERE bc_id=' . db_clean($db, $user_id);
		$result = db_query($db, $sql);
		db_disconnect($db);

		if (is_array($result)) {
			if ($result['pro'] == 1) {
				return true;
			}
		}
	}

	return false;
}

// pro_user_set - Upgrade the current user to a Pro account
function pro_user_set() {
    if (user_exists()) {
        $db = db_connect();
    	$sql = 'UPDATE users SET pro=1 WHERE bc_id=' . db_clean($db, user_id());
    	db_query($db, $sql);
    	db_disconnect($db);
    }
}

// pro_user_remove - Downgrade the current user to a free account
function pro_user_remove() {
    if (user_exists()) {
        $db = db_connect();
    	$sql = 'UPDATE users SET pro=0 WHERE bc_id=' . db_clean($db, user_id());
    	db_query($db, $sql);
    	db_disconnect($db);
    }
}

// user_login_url - Return the oAuth login URL
function user_login_url(){
	return $GLOBALS['auth_api_login_url'];
}

// user_logout - Logout user
function user_logout(){
	if (!isset($_SESSION)) {
		session_start();
	}

	setcookie("bc_id", "", time()-3600);
	setcookie("timezone", "", time()-3600);
	session_destroy();
	redirect('/pages/home.php');
}

?>
