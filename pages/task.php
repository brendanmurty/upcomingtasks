<?
if (!isset($_SESSION)) {
	session_start();
}

$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
loading_start();
if( (isset($_GET['project']) && isset($_GET['task'])) || (isset($project) && isset($task)) ){
	// Check this is a valid task request
	if(!isset($project)){ $project=input_clean($_GET['project'],'numeric'); }
	if(!isset($task)){ $task=input_clean($_GET['task'],'numeric'); }
	if($project!='' && $task!=''){
		if(isset($_GET['mode'])){
			// Processing mode
			$mode=input_clean($_GET['mode'],'alpha');
			if(isset($_POST['date_day'])){
				// Clean and construct the new due date
				$date_day=input_clean($_POST['date_day'],'numeric');
				$date_month=input_clean($_POST['date_month'],'numeric');
				$date_year=input_clean($_POST['date_year'],'numeric');
				$date_due=$date_year.'-'.$date_month.'-'.$date_day;
			}
			switch($mode){
			    case 'complete':
			        bc_task_complete($project,$task,'complete');
			        break;
			    case 'incomplete':
			    	bc_task_complete($project,$task,'incomplete');
			    	break;
			    case 'edit':
			    	$task_name=input_clean($_POST['task_name'],'');
					$task_due='';
					if($_POST['due_mode']=='date'){
						// Format the due date if required
						if(isset($_POST['date_day'])){
							$date_day=input_clean($_POST['date_day'],'numeric');
							$date_month=input_clean($_POST['date_month'],'numeric');
							$date_year=input_clean($_POST['date_year'],'numeric');
							$task_due=$date_year.'-'.$date_month.'-'.$date_day;
						}
					}
			    	bc_task_edit($task_name,$task_due,$project,$task,"");
			    	break;
			    case 'dueremove':
			    	bc_task_due_set($project,$task,'');
			    	break;
			    case 'deletetask':
			    	bc_task_delete($project,$task);
			    	break;
			    default:
			    	redirect('/pages/task.php?project='.$project.'&task='.$task);
			    	break;
			}
		}else{
			// Task info page
			loading_done();
			print bc_task($project,$task);
		}
	}else{
		redirect('/pages/home.php');
	}
}else{
	redirect('/pages/home.php');
}

include_once $root_path.'/common/layout-footer.php';
?>
