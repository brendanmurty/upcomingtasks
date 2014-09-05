<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/initialise.php';
if(isset($_POST['comment']) && $_POST['comment']!=''){
	$project=input_clean($_GET['project'],'numeric');
	$task=input_clean($_GET['task'],'numeric');
	$comment=input_clean($_POST['comment'],'');
	bc_comment_new($project,$task,$comment);
	redirect('/pages/task.php?project='.$project.'&task='.$task);
}else{
	redirect('/');
}
?>