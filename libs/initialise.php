<?
set_error_handler('error_handle');

if (!isset($_SESSION)) {
	session_start();
}

// Include the required library files
$libs = dirname(__FILE__);
include_once $libs . '/auth.php';
include_once $libs . '/general.php';
include_once $libs . '/user.php';
include_once $libs . '/basecamp.php';
include_once $libs . '/database.php';

// Setup the timezone to match the current user's preferences
user_timezone_set(user_timezone_get());

// Setup application information variables
$app_name = 'UpcomingTasks';
$app_info = 'UpcomingTasks is the simplified way to manage your Basecamp 2 tasks when you\'re away from your computer.';
$app_welcome = 'Quickly manage your projects and tasks, view progress and select a theme that suits your mood. Free for all Basecamp 2 and Basecamp 2 Personal accounts.';

// Automatically load code classes stored in "/classes"
function __autoload($class_name) {
	$class_path = dirname(__DIR__) . '/classes/' . str_replace('\\', '/', $class_name) . '.php';

	if (file_exists($class_path)) {
		require_once($class_path);
		return true;
	}

	return false;
}

// error_handle - Custom error handler
function error_handle($errno, $errstr, $errfile, $errline) {
	// Construct the error description content
	$errtext = date('Y-m-d g:i:sa') . "\r\n" . 'Error ' . $errno;

	if ($errfile != '') {
		$errfile = str_replace('/var/www/html/upcomingtasks.com', '', $errfile);
	    $errtext .= ' in '.$errfile;
	}

	if ($errline != '') {
	    $errtext .= ' (line '.$errline.')';
	}

	$errtext .= ': ' . $errstr . "\r\n";

	// Construct the URL to the requested page
	$current_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	// Add user and request details
	$errtext .= 'URL: ' . $current_url . "\r\n";
	$errtext .= 'Basecamp ID: ' . user_id() . "\r\n";
	$errtext .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";

	if (is_local()) {
		// Local - Show the details of the error on the page
		echo '<div class="notification error">' . nl2br($errtext) . '</div>';
	} else {
		// Production - Send an email to the administrator about this error
		if ($errno != '404') {
			$headers = 'From: ' . $GLOBALS['auth_error_email_from'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();
			$mail = @mail($GLOBALS['auth_error_email_to'], 'UpcomingTasks Error: ' . $errno, $errtext, $headers);
		}
	}
}
?>
