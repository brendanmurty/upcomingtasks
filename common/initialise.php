<?
set_error_handler('error_handle');
date_default_timezone_set('Australia/Sydney');
session_start();

// Include the required libs files
include_once 'libs/auth.php';
include_once 'libs/general.php';
include_once 'libs/user.php';
include_once 'libs/basecamp.php';
include_once 'libs/database.php';

// Setup common variables
$app_name='UpcomingTasks';
$app_info='UpcomingTasks is the simplified way to manage your Basecamp tasks when you\'re away from your computer.';
$app_welcome='Quickly manage your projects and tasks, view progress and select a theme that suits your mood. Free for all Basecamp and Basecamp Personal accounts.';
$link_share='';
$link_donate='';

// Get the path to the top level app folder
$root_path=dirname(dirname(__FILE__));

// error_handle - Custom error handler
function error_handle($errno,$errstr,$errfile,$errline){
	$errfile=str_replace('/var/www/html/upcomingtasks.com','',$errfile);
	if($errstr!='A session had already been started - ignoring session_start()' && strstr($_SERVER['REQUEST_URI'],'favicon.ico') == false && strstr($errstr,'404') == false){
		$mail_text=date('Y-m-d g:i:sa')."\r\n".'Error '.$errno;
		if($errfile!=''){ $mail_text.=' in '.$errfile; }
		if($errline!=''){ $mail_text.=' (line '.$errline.')'; }
		$mail_text.=': '.$errstr."\r\n";
		if(isset($_SERVER['HTTPS'])){
			$current_url='https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		}else{
			$current_url='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		}
		$mail_text.='URL: '.$current_url."\r\n";
		if(user_exists()){ $mail_text.='Basecamp ID: '.$_COOKIE['bc_id']."\r\n"; }
		$mail_text.='IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
		$mail_headers='From: '.$GLOBALS['auth_error_email_from']."\r\n".'X-Mailer: PHP/'.phpversion();
		$mail_result=@mail($GLOBALS['auth_error_email_to'],'UpcomingTasks Error',$mail_text,$mail_headers);
	}
}
?>
