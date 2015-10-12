<?

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/initialise.php';

// Format the requested URL and extract page (e.g. /project/123) and query string (e.g. ?something=1&sample=yes)
$path = explode('?', ltrim($_SERVER["REQUEST_URI"], '/'));
$section = explode('/', $path['0']);
$query = false;
if (array_key_exists('1', $path)) {
	$query = explode('&', $path['1']);
}

if (file_exists($root_path . '/pages/' . $section['0'] . '.php')) {
    // A related page was found, redirect to it
	if ($section['0'] == 'project' && array_key_exists('1', $section) && is_numeric($section['1'])) {
		if (array_key_exists('3', $section) && is_numeric($section['3'])) {
			// Redirect to selected task (e.g. /project/123/task/123)
			redirect('/pages/task.php?project=' . $section['1'] . '&task=' . $section['3']);
		} else {
			// Redirect to selected project (e.g./project/123)
			redirect('/pages/project.php?id=' . $section['1']);
		}
	} else {
		// Redirect to selected page (e.g. /more)
		redirect('/pages/' . $section['0'] . '.php');
	}
} else {
    // 404 - page not found
	error_handle($_SERVER["REQUEST_URI"], '404 - Page not found', '', '');
}

// Default to redirecting to the home page
redirect('/pages/home.php');
