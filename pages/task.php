<?

if (!isset($_SESSION)) {
	session_start();
}

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/common/header.php';

$project_id = form_get('project', 'numeric');
$task_id = form_get('task', 'numeric');
$page_mode = form_get('mode', 'alpha');

if ($project_id && $task_id) {
	if ($page_mode) {
		// Alter a task
		switch ($page_mode) {
		    case 'complete':
		        bc_task_complete($project_id, $task_id, 'complete');
		        break;
		    case 'incomplete':
		    	bc_task_complete($project_id, $task_id, 'incomplete');
		    	break;
		    case 'edit':
		    	$task_name = form_post('task_name', 'none');
				$task_due = '';

				if (form_post('due_mode', 'alpha') == 'date') {
					$date_day = form_post('date_day', 'numeric');
					$date_month = form_post('date_month', 'numeric');
					$date_year = form_post('date_year', 'numeric');
					$task_due = $date_year . '-' . $date_month . '-' . $date_day;
				}

				$person_id = user_id();
				if (pro_user()) {
					// Allow the user to assign this task to another person (Pro feature)
					$person_selected = form_post('people_list', 'numeric');
					if ($person_selected) {
						$person_id = $person_selected;
					}
				}

		    	bc_task_edit($task_name, $task_due, $project_id, $task_id, '', $person_id);
		    	break;
		    case 'dueremove':
		    	bc_task_due_set($project_id, $task_id, '');
		    	break;
		    case 'deletetask':
		    	bc_task_delete($project_id, $task_id);
		    	break;
		    default:
		    	redirect('/pages/task.php?project=' . $project_id . '&task=' . $task_id);
		    	break;
		}
	} else {
		// Task info page
		print bc_task($project_id, $task_id);
	}
} else {
	redirect('/pages/home.php');
}

include_once $root_path . '/common/footer.php';

?>
