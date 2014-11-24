<?
$root_path=dirname(dirname(__FILE__));

if(isset($_GET['mode'])){// Special page mode

	if($_GET['mode']=='select' && isset($_GET['id'])){// Select this Basecamp account
		
		include_once $root_path.'/common/initialise.php';
		$bc_account=input_clean($_GET['id'],'numeric');
		$result=bc_results('https://basecamp.com/'.$bc_account.'/api/v1/people/me.json');

		if(isset($result['id'])){
			if($result['id']!='0'){
				// Update the relevant database field and the browser cookie
				$sql="UPDATE IGNORE users SET bc_id = '".$result['id']."', bc_account='".$bc_account."' WHERE bc_id='".user_id()."' ";
				db_query($sql);
				session_start();
				setcookie("bc_id",$result['id'],time()+60*60*24*14);
			}
			redirect('/pages/home.php');
		}
	
	}elseif($_GET['mode']=='settheme' && isset($_GET['theme'])){// Set this theme
	
		include_once $root_path.'/common/initialise.php';
		theme_set(input_clean($_GET['theme'],''));
		redirect('/pages/home.php');
		
	}elseif($_GET['mode']=='options'){// Show all of the options
		
		include_once $root_path.'/common/layout-header.php';
		print '<h2>Select Account</h2>'.bc_account_select().'<h2>Select Theme</h2>'.theme_list().'<p><a class="button" href="/pages/home.php">Cancel</a></p>';
		include_once $root_path.'/common/layout-footer.php';
		
	}else{// Show list of available accounts
	
		include_once $root_path.'/common/layout-header.php';
		$account_select=bc_account_select();
		print '<h2>Select Account</h2>'.$account_select;
		include_once $root_path.'/common/layout-footer.php';
		
	}

}else{// Show list of available accounts

	include_once $root_path.'/common/layout-header.php';
	$account_select=bc_account_select();
	print '<h2>Select Account</h2>'.$account_select;
	include_once $root_path.'/common/layout-footer.php';
	
}
?>