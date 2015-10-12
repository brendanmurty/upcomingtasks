<?

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/initialise.php';

// Get the requested url, correct the format, split into sections
$req=ltrim($_SERVER["REQUEST_URI"],'/');
$req_parts=explode('?',$req);
$req=str_ireplace('//','/',$req_parts['0']);
$req_part=explode('/',$req_parts['0']);

// Extract the query string variables
$get_var='';
if(isset($req_parts['1'])){
	$get_variables=$req_parts['1'];
	$get_var_item=explode('&',$get_variables);
	foreach($get_var_item as $value){
		$parts=explode('=',$value);
		$get_var[$parts['0']]=$parts['1'];
	}
}

// Handle custom redirects or show 404
if ($req) {
	if (file_exists($root_path . '/pages/' . $req_part['0'] . '.php')) {
	    // Attempt to find the requested page
		if (user_exists() || $req_part['0'] == 'login' || $req_part['0'] == 'authenticate') {
			// Set this as the current page
			redirect('/pages/' . $req_part['0'] . '.php');
		} elseif (!user_exists() && $req_part['1'] != 'account.php') {
			// Redirect to login page
			redirect('/pages/login.php');
		}
	}else{
	    // 404 - page not found
		if(!isset($page)){$page='';}
		error_handle($page,'404 - Page not found','','');
		redirect('/pages/home.php');
	}
} else {
	// Redirect to the homepage
	redirect('/pages/home.php');
}
?>
