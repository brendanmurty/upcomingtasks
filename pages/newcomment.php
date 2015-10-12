<?

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/initialise.php';

$project_id = form_get('project', 'numeric');
$task_id = form_get('task', 'numeric');
$task_comment = form_post('comment', '');

if ($project_id && $task_id) {
	if ($task_comment) {
		// Save the new comment
		bc_comment_new($project_id, $task_id, $task_comment);
	}

	redirect('/pages/task.php?project=' . $project_id . '&task=' . $task_id);
} else {
	redirect('/pages/home.php');
}

?>
