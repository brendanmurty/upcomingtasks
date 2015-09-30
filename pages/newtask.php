<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/initialise.php';

if (!user_exists()) {
	redirect('/pages/home.php');
}

$task_name = form_post('task_name', 'none');
$project_id = form_get('project', 'numeric');
$list_id = form_get('list', 'numeric');

if (!$task_name) {
	// Show the new task form
	include_once $root_path . '/common/header.php';
	$date_picker = form_date_picker('', 'newtask');

	$people_list = '';
	if (pro_user()) {
		// Allow the user to assign this task to another person (Pro feature)
		$people_list = bc_peoplelist();
	}

	if ($project_id && $list_id) {
		// Pre-select a list
		$task_list = bc_tasklist($project_id, $list_id);
	} else {
		// No list pre-selection
		$task_list = bc_tasklist('', '');
	}
?>
	<form id="form_task" name="form_task" method="post" action="/pages/newtask.php">
		<p>
			<textarea class="text" name="task_name" id="task_name" autofocus="autofocus"></textarea>
		</p>
		<?= $task_list ?>
		<?= $date_picker ?>
		<?= $people_list ?>
		<p class="buttons">
			<input type="hidden" name="due_mode" id="due_mode" value="date" />
			<input type="submit" class="submit" value="Add task" />
		</p>
	</form>
<?
	include_once $root_path . '/common/footer.php';
} else {
	// Create a new task
	$task_due = '';

	$date_day = form_post('date_day', 'numeric');
	$date_month = form_post('date_month', 'numeric');
	$date_year = form_post('date_year', 'numeric');
	$due_mode = form_post('due_mode', 'alpha');
	$list_selected = form_post('task_list', 'none');

	$person_id = user_id();
	if (pro_user()) {
		// Allow the user to assign this task to another person (Pro feature)
		$person_selected = form_post('people_list', 'numeric');
		if ($person_selected) {
			$person_id = $person_selected;
		}
	}

	if ($date_day && $due_mode == 'date') {
		// Construct the due date
		$task_due = $date_year . '-' . $date_month . '-' . $date_day;
	}

	// Extract the project and list ids
	$list_selected = explode('-', $list_selected);
	$project_id = $list_selected['0'];
	$list_id = $list_selected['1'];

	bc_task_new($task_name, $task_due, $project_id, $list_id, $person_id);
}
?>
