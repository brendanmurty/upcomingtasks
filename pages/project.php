<?php

$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/libs/initialise.php';

if(form_get('id', 'numeric')){
	if (!isset($project_id)) {
		$project_id = form_get('id', 'numeric');
	}

	if ($project_id == '') {
		redirect('/pages/projects.php');
	} else {
		include_once $root_path.'/libs/header.php';

		if (pro_user() && form_get('action', 'alpha') == 'edit') {
			// Edit a project form (Pro only)
			$project_description = bc_project_description($project_id);
			$project_name = bc_project_name($project_id);
			$cancel_icon = icon('times', 'Cancel');

?>
		<form id="form_project_edit" name="form_project_edit" method="post" action="/pages/project.php?id=<?php echo $project_id ?>&amp;action=save">
			<p>
				<input type="text" class="text" name="project-name" value="<?php echo $project_name ?>" />
			</p>
			<p>
				<input type="text" class="text" name="project-description" value="<?php echo $project_description ?>" />
			</p>
			<p class="buttons">
				<input type="submit" value="Save" name="submit" class="submit" />
				<a class="cancel-edit" href="/pages/project.php?id=<?php echo $project_id ?>"><?php echo $cancel_icon ?></a>
			</p>
		</form>
<?php

		} elseif (pro_user() && form_get('action', 'alpha') == 'save') {
			// Save project details (Pro only)
			bc_project_edit(
				form_post('project-name', 'none'),
				form_post('project-description', 'none'),
				form_get('id', 'numeric')
			);
		} else {
			// View a project
			loading_temp();
			echo bc_project($project_id);
		}

		include_once $root_path.'/libs/footer.php';
	}
}else{
	redirect('/pages/projects.php');
}

?>
