<?
include_once dirname(dirname(__FILE__)).'/common/initialise.php';

$error_denied='<p class="error denied">A Basecamp account is required to login.</p>';
$error_authentication='<p class="error">There was an error authenticating, please <a href="/pages/login.php">try again</a>.</p>';

$message=$error_authentication;
if(isset($_GET['code'])){// User accepted authentication, setup in database
	if($_GET['code']==''){
		$message=$error_authentication;
	}else{
		user_authenticate($_GET['code']);
	}
}elseif(isset($_GET['error'])){// There was an error authenticating
	if($_GET['error']=='access_denied'){
		$message=$error_denied;
	}else{
		$message=$error_authentication;
	}
}else{
	$message=$error_authentication;
}

include_once dirname(dirname(__FILE__)).'/common/layout-header.php';
print $message;
include_once dirname(dirname(__FILE__)).'/common/layout-footer.php';
?>