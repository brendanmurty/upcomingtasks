<?
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

		$result = json_decode(curl_exec($cu), 'true') or exit("Authentication issue");

		// Extract the relevant details from the Basecamp response
		if (isset($result['access_token'])) {
			$bc_token = $result['access_token'];
			$bc_account = bc_account($bc_token);

			if($bc_account == ''){
				// Can't find a suitable Basecamp account
				$root_path=dirname(dirname(dirname(__FILE__)));
				include_once $root_path.'/common/layout-header.php';
				print '<p class="error">Sorry, no eligible Basecamp accounts were found. Please <a href="https://basecamp.com/signup">create a Basecamp account</a> to use this app.</p>';
				include_once $root_path.'/common/layout-footer.php';
				exit();
			}

			$bc_id = bc_user_id($bc_token, $bc_account);

			if ($bc_id == '0') {
				// Authentication issue, redirect to home page
				error_handle('auth', 'bc_id is 0', $_SERVER['SCRIPT_FILENAME'], '8');
				redirect('/pages/home.php');
			} else {
				// Setup the user session and the cookie (14 day expiry)
				session_start();
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
}

// user_email - Get the email of the logged in user
function user_email(){
	if(user_id() > 0){
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
	session_start();
	if(isset($_COOKIE['bc_id'])){
		return input_clean($_COOKIE['bc_id'], 'numeric');
	}else{
		return 0;
	}
}

// user_is_pro - Check if the current user is a pro user
function user_is_pro($bc_id=''){
	$user_id = '';
	if($bc_id != ''){
		$user_id = $bc_id;
	}else{
		if(user_exists()){
			$user_id = user_id();
		}else{
			return false;
		}
	}
	if($user_id != ''){
	    $db = db_connect();
		$sql = "SELECT pro FROM users WHERE bc_id=" . db_clean($db, $user_id);
		$result = db_query($db, $sql);
		db_disconnect($db);
		if(is_array($result)){
			if($result['pro']==1){
				return true;
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
}

// user_login_url - Return the oAuth login URL
function user_login_url(){
	return $GLOBALS['auth_api_login_url'];
}

// user_logout - Logout user
function user_logout(){
	session_start();
	setcookie("bc_id","",time()-3600);
	session_destroy();
	redirect('/pages/home.php');
}

?>
