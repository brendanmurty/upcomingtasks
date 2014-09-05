<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/initialise.php';
if(!user_exists()){ redirect('/'); }// Redirect if user not logged in
if(!isset($_POST['task_name'])){// Show the new task form
	include_once $root_path.'/common/layout-header.php';
	$form_task_action='/pages/newtask.php';
	$date_picker=form_date_picker('','newtask');
	if(isset($_GET['project']) && $_GET['project']!='' && isset($_GET['list']) && $_GET['list']!=''){// Pre-select a list
		$task_lists=bc_tasklists(input_clean($_GET['project'],'numeric'),input_clean($_GET['list'],'numeric'));
	}else{// No list pre-selection
		$task_lists=bc_tasklists('','');
	}
?>
	<form id="form_task" name="form_task" method="post" action="<?=$form_task_action?>">
		<p>
			<textarea class="text" name="task_name" id="task_name" autofocus="autofocus"></textarea>
		</p>
		<?= $task_lists ?>
		<?= $date_picker ?>
		<p class="buttons">
			<input type="hidden" name="due_mode" id="due_mode" value="date" />
			<input type="submit" class="submit" value="Add task" />
		</p>
	</form>
<?
	include_once $root_path.'/common/layout-footer.php';
}elseif(isset($_POST['task_name']) && $_POST['task_name']!=''){// Create a new task
	$task_name=input_clean($_POST['task_name'],'');
	$task_due='';
	if(isset($_POST['date_day'])&&$_POST['due_mode']=='date'){// Form the due date if required
		$date_day=input_clean($_POST['date_day'],'numeric');
		$date_month=input_clean($_POST['date_month'],'numeric');
		$date_year=input_clean($_POST['date_year'],'numeric');
		$task_due=$date_year.'-'.$date_month.'-'.$date_day;
	}
	$list_selected=input_clean($_POST['task_lists'],'');
	$list_selected=explode('-',$list_selected);
	$project_id=$list_selected['0'];
	$list_id=$list_selected['1'];
	bc_task_new($task_name,$task_due,$project_id,$list_id);
}
?>