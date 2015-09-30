<?
set_error_handler('error_handle');

if (!isset($_SESSION)) {
	session_start();
}

// Include the required libs files
include_once 'libs/auth.php';
include_once 'libs/general.php';
include_once 'libs/user.php';
include_once 'libs/basecamp.php';
include_once 'libs/database.php';

// Setup the timezone
user_timezone_set(user_timezone_get());

// Setup common variables
$app_name = 'UpcomingTasks';
$app_info = 'UpcomingTasks is the simplified way to manage your Basecamp tasks when you\'re away from your computer.';
$app_welcome = 'Quickly manage your projects and tasks, view progress and select a theme that suits your mood. Free for all Basecamp and Basecamp Personal accounts.';

// Get the path to the top level app folder
$root_path = dirname(dirname(__FILE__));

// error_handle - Custom error handler
function error_handle($errno, $errstr, $errfile, $errline) {
	$errtext = date('Y-m-d g:i:sa') . "\r\n" . 'Error ' . $errno;

	if($errfile != '') {
		$errfile = str_replace('/var/www/html/upcomingtasks.com', '', $errfile);
	    $errtext .= ' in '.$errfile;
	}

	if($errline != '') {
	    $errtext .= ' (line '.$errline.')';
	}

	$errtext .= ': ' . $errstr . "\r\n";

	if(isset($_SERVER['HTTPS'])) {
		$current_url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}else{
		$current_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}

	$errtext .= 'URL: ' . $current_url . "\r\n";
	$errtext .= 'Basecamp ID: ' . user_id() . "\r\n";
	$errtext .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";

	if (is_local()) {
		// Local
		echo '<div class="error-message">' . nl2br($errtext) . '</div>';
	} else {
		// Production
		$headers = 'From: ' . $GLOBALS['auth_error_email_from'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		$mail = @mail($GLOBALS['auth_error_email_to'], 'UpcomingTasks Error', $errtext, $headers);
	}
}
?>
