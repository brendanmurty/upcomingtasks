<?php

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/initialise.php';

$error_denied = '<p class="error denied">A Basecamp 2 account is required to login.</p>';
$error_authentication = '<p class="error">There was an error authenticating, please <a href="/pages/login.php">try again</a>.</p>';

$message = $error_authentication;
$auth_code = form_get('code', 'none');
$auth_error = form_get('error', 'none');

if ($auth_code) {
	// User accepted authentication, save account information to database
	user_authenticate($auth_code);
} elseif ($auth_error) {
	// There was an error authenticating
	if ($auth_error == 'access_denied') {
		$message = $error_denied;
	} else {
		$message = $error_authentication;
	}
} else {
	// General authentication error
	$message = $error_authentication;
}

include_once $root_path . '/libs/header.php';
print $message;
include_once $root_path . '/libs/footer.php';

?>
