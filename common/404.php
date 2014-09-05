<?
include_once 'initialise.php';
$root_path=dirname(dirname(__FILE__));

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
if($req!=''){
	// Extract the path parts of the request
	$request_first=$req_part['0'];
	$request_second='';
	$request_third='';
	$request_forth='';
	if(isset($req_part['1'])){ $request_second=$req_part['1']; }
	if(isset($req_part['2'])){ $request_third=$req_part['2']; }
	if(isset($req_part['3'])){ $request_forth=$req_part['3']; }
	
	// Redirect for rename of extras page
	//if($req='/pages/extras.php'){ exit(header('Location: /pages/more.php')); }
	
	if(file_exists($root_path.'/pages/'.$request_first.'.php')){// Attempt to find the requested page
		if(user_exists() || $request_first=='login' || $request_first=='authenticate' || $request_first=='donated'){
			// Set this as the current page
			redirect('/pages/'.$request_first.'.php');
		}elseif(!user_exists() && $request_second!='account.php'){
			// Redirect to login page
			redirect('/pages/login.php');
		}
	/*}elseif($request_first=='project' && $request_third=='task'){// Allow /project/1234/task/5678
		//header('Location: /pages/task.php?project='.input_clean($request_second,'numeric').'&task='.input_clean($request_forth,'numeric'));
		$project=input_clean($request_second,'numeric');
		$task=input_clean($request_forth,'numeric');
		print $project.', '.$task;
		include_once $root_path.'/pages/task.php';
	*/
	}else{// 404 - page not found
		if(!isset($page)){$page='';}
		error_handle($page,'404 - Page not found','','');
		redirect('/pages/home.php');
	}
}else{
	// Redirect to the homepage
	redirect('/pages/home.php');
}
?>
