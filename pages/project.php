<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/initialise.php';
if(isset($_GET['id'])||isset($project_id)){
	if(!isset($project_id)){ $project_id=input_clean($_GET['id'],'numeric'); }
	if($project_id==''){
		redirect('/pages/projects.php');
	}else{
		include_once $root_path.'/common/layout-header.php';
		loading_temp();
		print bc_project($project_id);
		include_once $root_path.'/common/layout-footer.php';
	}
}else{
	redirect('/pages/projects.php');
}
?>