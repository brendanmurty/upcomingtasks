<?

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/common/initialise.php';

$page_mode = form_get('mode', 'alpha');
$account_id = form_get('id', 'numeric');
$selected_theme = form_get('theme', 'alpha');

if($page_mode){
	if($page_mode == 'select' && $account_id){
		// Select this Basecamp account
		$result = bc_results('https://basecamp.com/' . $account_id . '/api/v1/people/me.json');

		if (isset($result['id'])) {
			if ($result['id'] != '0') {
				// Update the relevant database field and the browser cookie
				$db = db_connect();
				$sql = "UPDATE IGNORE users SET bc_id = " . db_clean($db, $result['id']) . ", bc_account="  . db_clean($db, $account_id) . " WHERE bc_id=" . db_clean($db, user_id());
				db_query($db, $sql);
				db_disconnect($db);

				if (!isset($_SESSION)) {
					session_start();
				}

				setcookie("bc_id", $result['id'], time()+60*60*24*14);
			}

			redirect('/pages/home.php');
		}
	} elseif ($page_mode == 'settheme' && $selected_theme) {
		// Set this theme
		theme_set($selected_theme);
		redirect('/pages/home.php');
	} elseif ($page_mode == 'options') {
		// Show all of the options
		include_once $root_path . '/common/layout-header.php';
		print '<h2>Select Account</h2>' . bc_account_select() . '<h2>Select Theme</h2>' . theme_list() . '<p><a class="button" href="/pages/home.php">Cancel</a></p>';
		include_once $root_path . '/common/layout-footer.php';
		exit;
	}
}

// Show list of available accounts
include_once $root_path . '/common/layout-header.php';
$account_select = bc_account_select();
print '<h2>Select Account</h2>' . $account_select;
include_once $root_path . '/common/layout-footer.php';

?>
