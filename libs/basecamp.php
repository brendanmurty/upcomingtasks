<?php

// bc_account - Select the first Basecamp 2 account found in the users account list
function bc_account($bc_token) {
	if ($bc_token) {
		$url = 'https://launchpad.37signals.com/authorization.json';
		$ch = curl_init();

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$bc_token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent'],
			CURLOPT_SSL_VERIFYPEER => false
		);

		curl_setopt_array($ch, $options);
		$result = json_decode(curl_exec($ch), true);

		if ($result) {
			if (isset($result['accounts'])) {
				$count = count($result['accounts']);
				for ($i=0; $i<$count; $i++) {
					if ($result['accounts'][$i]['product'] == 'bcx') {
						return $result['accounts'][$i]['id'];
						break;
					}
				}
			}
		}
	}


	return '';
}

// bc_account_select - Produce a list of all bcx accounts the user has access to
function bc_account_select($mode = 'list') {
	$result=bc_results('https://launchpad.37signals.com/authorization.json');
	if($result!=''){
		$s='';
		if(isset($result['accounts'])){
			$accounts=$result['accounts'];
			if($accounts!=''){
				$count=count($accounts);
				for($i=0;$i<$count;$i++){
					$account_id='';
					$account_name='';
					if($result['accounts'][$i]['product']=='bcx'){
						$account_id=$result['accounts'][$i]['id'];
						$account_name=$result['accounts'][$i]['name'];
						$s .= '<li';

						if ($mode == 'list' && user_account() == $account_id) {
						    $s .= ' class="selected"';
						}

						$s .= '><a href="/pages/account.php?mode=select&id=' . $account_id . '">' . $account_name . '</a></li>';
					}
				}
			}
			if($s!=''){
				return '<ul class="account-select">'.$s.'</ul>';
			}else{
				return '<p class="error">Sorry, no eligible Basecamp accounts were found. Please <a href="https://basecamp.com/2">create a Basecamp 2 account</a> to use this app.</p>';
			}
		}else{
			return '<p class="error">Sorry, UpcomingTasks couldn\'t connect to your Basecamp account.</p>';
		}
	}
}

// bc_comment_attachments - List the attachments of a comment
function bc_comment_attachment($project_id,$comment_id){
	$result=bc_results('/projects/'.$project_id.'/attachments.json');
	if($result!=''){
		$count=count($result);
		for($i=0;$i<$count;$i++){
			$key=$result[$i]['key'];
			if($key==$comment_id){
				$att_name=$result[$i]['name'];
				$att_link=$result[$i]['url'];
				$att_parts=pathinfo($att_link);
				$att_ext=strtolower($att_parts['extension']);

				if($att_ext=='gif' || $att_ext=='jpg' || $att_ext=='jpeg' || $att_ext=='png'){// Image attachment
					$att_link='/pages/image.php?path='.$result[$i]['url'];
				}

				return '<p class="attachment"><em>Attachment:</em><a tabindex="-1" href="'.$att_link.'">'.$att_name.'</a></p>';
			}
		}
	}
}

// bc_comment_new - Create a new comment
function bc_comment_new($project_id,$task_id,$comment=''){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$comment=input_clean($comment);
	$api_url='/projects/'.$project_id.'/todos/'.$task_id.'/comments.json';

	if($comment!=''){
		$data_array=array('content'=>$comment);
		bc_post($api_url,$data_array,'new');
	}
}

// bc_list_name - Get the name of a certain list
function bc_list_name($project_id,$list_id){
	if($list_id){
		$list_id=input_clean($list_id,'numeric');
		$project_id=input_clean($project_id,'numeric');
		$result=bc_results('/projects/'.$project_id.'/todolists/'.$list_id.'.json');
		return $result['name'];
	}
}

// bc_user_box - Return information about the authenticated user
function bc_user_box(){
	$result = bc_results('/people/me.json');
	$u = '';

	if (is_array($result)) {
		if (array_key_exists('avatar_url', $result) && array_key_exists('name', $result)) {
			$u  = '<img src="' . $result['avatar_url'] . '" /></span>';
			$u .= '<strong>' . $result['name'] . '</strong>';
			$u .= '<a id="button_logout" href="#">Logout</a>';

			if (pro_user()) {
				$u .= '<a class="pro" href="/pages/pro.php">Pro</a>';
			} else {
				$u .= '<a class="upgrade" href="/pages/pro.php">Upgrade</a>';
			}
		}
	}

	return $u;
}

// bc_peoplelist - Return a dropdown list of todo lists
function bc_peoplelist(){
	$results = bc_results('/people.json');
	$list = false;

	if (is_array($results)) {
		for ($i = 0; $i < count($results); $i++) {
			if (array_key_exists($i, $results)) {
				$person_id = $results[$i]['id'];
				$person_name = $results[$i]['name'];

				$list .= '<option label="' . $person_name . '" value="' . $person_id . '"';

				if ($person_id == user_id()) {
					// Select the current user by default
					$list .= ' selected';
				}

				$list .= '>' . $person_name . '</option>';
			}
		}
	}
	if ($list) {
		return '<p class="people_list select"><select id="people_list" name="people_list">' . $list . '</select></p>';
	}else{
		return '';
	}
}

// bc_post - Post data to Basecamp
function bc_post($api_url,$data_array,$method='new'){
	if($api_url && $data_array){
	    $db = db_connect();
	    $sql = "SELECT bc_account, bc_token FROM users WHERE bc_id=" . db_clean($db, user_id()) . " LIMIT 1";
		$db_result = db_query($db, $sql);
		db_disconnect($db);
		$api_url='https://basecamp.com/'.$db_result['bc_account'].'/api/v1'.$api_url;
		$ch=curl_init();
		$options=array(
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$db_result['bc_token']),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent'],
			CURLOPT_SSL_VERIFYPEER => false
		);
		if($method=='new'){
			$options[CURLOPT_POST]=count($data_array);
		}elseif($method=='update'){
			$options[CURLOPT_CUSTOMREQUEST]='PUT';
		}elseif($method=='delete'){
			$options[CURLOPT_CUSTOMREQUEST]='DELETE';
		}
		if(is_array($data_array) && $data_array!=''){// Format the data for posting to Basecamp
			$data=json_encode($data_array);
			$data=str_replace('&amp;amp;','&',$data);
			$data=str_replace('&amp;lt;','<',$data);
			$data=str_replace('&amp;gt;','>',$data);
			$options[CURLOPT_POSTFIELDS]=$data;
		}
		curl_setopt_array($ch,$options);
		return json_decode(curl_exec($ch), true);
	}
}

// bc_project_id_from_url - Return the name of the specificied project
function bc_project_id_from_url($api_url){
	if($api_url){
		$p = explode('/projects/', $api_url);
		$p = explode('-', $p[1]);
		if(strpos($p[0], '/') !== false){
			$p = explode('/', $p[0]);
		}
		return $p[0];
	}
}

// bc_project_name_from_url - Return the name of the specificied project (given the url)
function bc_project_name_from_url($api_url){
	if($api_url){
		$p=explode('/projects/',$api_url);
		$p=explode('/todo',$p[1]);
		$p=str_replace(range(0,9),'',$p[0]);
		$p=trim(str_replace('-',' ',$p));
		if(strlen($p)<=3){
			return strtoupper($p);
		}else{
			return ucwords(strtolower($p));
		}
	}
}

// bc_project_description - Return the name of the specificied project (given the id)
function bc_project_description($id) {
	$id = input_clean($id, 'numeric');
	$r = bc_results('/projects/' . $id . '.json');
	if(count($r) > 0) {
		return $r['description'];
	}
}

// bc_project_name - Return the name of the specificied project (given the id)
function bc_project_name($id) {
	$id = input_clean($id, 'numeric');
	$r = bc_results('/projects/' . $id . '.json');
	if(count($r) > 0) {
		return $r['name'];
	}
}

// bc_project - Return a list of all tasks assigned to the project
function bc_project($id){
	$id=input_clean($id,'numeric');
	$r1=bc_results('/projects/'.$id.'.json');
	$r2=bc_results('/projects/'.$id.'/todolists.json');

	$h='<li><span class="project-name">'.$r1['name'].'</span><span class="project-description">'.$r1['description'].'</span>';

	if (pro_user()) {
		$h .= '<a class="action" href="/pages/project.php?id=' . $id . '&action=edit" title="Edit project details">' . icon('pencil', 'Edit') . '</a>';
	}

	$h.='<ul class="list">';
	for($i=0;$i<count($r2);$i++){
		if($r2[$i]['name']!=''){
			$h.='<li><a class="action" href="/pages/newtask.php?project='.$id.'&list='.$r2[$i]['id'].'" title="Add a task to the \''.$r2[$i]['name'].'\' project">'.icon('plus','New').'</a><span class="list-name">'.$r2[$i]['name'].'</span><span class="list-description">'.$r2[$i]['description'].'</span><ul class="task task-multiple">';
			$r3=bc_results('/projects/'.$id.'/todolists/'.$r2[$i]['id'].'.json');
			for($j=0;$j<count($r3['todos']['remaining']);$j++){
				$h.='<li><a href="/pages/task.php?project='.$id.'&task='.$r3['todos']['remaining'][$j]['id'].'"><span class="task-name">'.$r3['todos']['remaining'][$j]['content'].'</span></a></li>';
			}
			$h.='</ul></li>';
		}
	}
	$r4=bc_results('/projects/'.$id.'/todolists/completed.json');
	for($k=0;$k<count($r4);$k++){
		$h.='<li class="completed"><a class="action" href="/pages/newtask.php?project='.$id.'&list='.$r4[$k]['id'].'" title="Add a task to the \''.$r4[$k]['name'].'\' project">'.icon('plus','New').'</a><span class="list-name">'.$r4[$k]['name'].'</span>';
	}
	$h.='</ul></li>';
	return '<ul class="project project-single">'."\r\n".$h.'</ul>';
}

// bc_project_edit - Edit a project
function bc_project_edit($project_name, $project_description, $project_id) {
	$project_name = input_clean($project_name);
	$project_description = input_clean($project_description);
	$project_id = input_clean($project_id, 'numeric');
	$api_url = '/projects/' . $project_id . '.json';
	$data_array = array('name' => $project_name, 'description' => $project_description);
	$result = bc_post($api_url, $data_array, 'update');
	redirect('/pages/project.php?id=' . $project_id);
}

// bc_projects - Return a list of all projects in an account
function bc_projects(){
	$results=bc_results('/projects.json');
	$r='';
	if(is_array($results)){
		if(array_key_exists(0,$results)){
			$count=count($results);
			for($i=0;$i<$count;$i++){
				$r.='<li><a tabindex="-1" href="/pages/project.php?id='.$results[$i]['id'].'"><span class="project-name">'.$results[$i]['name'].'</span><span class="project-description">'.$results[$i]['description'].'</span></a></li>'."\r\n";
			}
			return '<ul class="project project-multiple">'."\r\n".$r.'</ul>';
		}
	}
}

// bc_projects_count - Returns the number of projects in the current Basecamp account
function bc_projects_count(){
	$results=bc_results('/projects.json');
	if(is_array($results)){
		if(array_key_exists(0,$results)){
			return count($results);
		}
	}
}

// bc_projects_first - Returns the ID of the first project in the account
function bc_projects_first(){
	$results=bc_results('/projects.json');
	if(is_array($results)){
		if(array_key_exists(0,$results)){
			return $results[0]['id'];
		}
	}
}

// bc_results - Simple query to the Basecamp API
function bc_results($api_url=''){
	if (!isset($_SESSION)) {
		session_start();
	}

	if($api_url!='' && user_id() > 0){
	    $db = db_connect();
	    $sql = "SELECT bc_account, bc_token FROM users WHERE bc_id=" . db_clean($db, user_id()) . " LIMIT 1";
		$result = db_query($db, $sql);
		db_disconnect($db);
		return bc_results_main($api_url, $result['bc_token'], $result['bc_account']);
	}else{
		return '';
	}
}

// bc_results_main - Query the Basecamp API and return the result
function bc_results_main($api_url='',$token='',$account=''){
	if($api_url!='' && $token!='' && $account!=''){
		set_time_limit(60);

		if(substr($api_url,0,1) == '/') {
			$api_url='https://basecamp.com/'.$account.'/api/v1'.$api_url;
		}

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent'],
			CURLOPT_SSL_VERIFYPEER => false
		);

		curl_setopt_array($ch, $options);
		$results = curl_exec($ch);

		return json_decode($results, 'true');
	}else{
		return '';
	}
}

// bc_task - Get a task's details and it's attached comments
function bc_task($project_id,$task_id){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$result=bc_results('/projects/'.$project_id.'/todos/'.$task_id.'.json');

	if($result!=''){
		if(bc_projects_count()==1){
			$task_location=bc_list_name($project_id,$result['todolist_id']);
		}else{
			$task_location=bc_project_name($project_id).' - '.bc_list_name($project_id,$result['todolist_id']);
		}

		$assigned_to = 'Me';
		if ($result['assignee']['id'] != user_id()) {
			// This task is assigned to someone else
			$assigned_to = $result['assignee']['name'];
		}

		return bc_task_format(
			$project_id,
			$result['todolist_id'],
			$result['id'],
			$result['content'],
			$task_location,
			'',
			$result['due_at'],
			$result['completed'],
			$assigned_to,
			'page'
		);
	}
}

// bc_task_comments - Get a list of the comments currently assigned to a task
function bc_task_comments($project_id,$task_id){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$result=bc_results('/projects/'.$project_id.'/todos/'.$task_id.'.json');
	if($result!=''){
		if($result['comments_count']>0){
			$r='<ul class="comments">';
			$count=count($result['comments']);
			for($i=0;$i<$count;$i++){
				// Extract the raw comment content
				$comment=$result['comments'][$i]['content'];

				// Format the comment
				if(substr($comment,0,4)=='<br>'){ $comment=substr($comment,4); }
				if(substr($comment,0,5)=='<br/>'){ $comment=substr($comment,5); }
				if(substr($comment,0,7)=='<p></p>'){ $comment=substr($comment,7); }
				$comment=strip_tags($comment,'<ul><ol><li><strong><em><b><i><br>');//<br>
				$comment=htmlspecialchars_decode($comment);
				$comment=preg_replace('/http:\/\/(.*?)\/[^\n <]*/','<a tabindex="-1" href="\\0">\\0</a>',$comment);
				$comment=preg_replace('/https:\/\/(.*?)\/[^\n <]*/','<a tabindex="-1" href="\\0">\\0</a>',$comment);

				// Format the new lines
				$comment=nl2br($comment);
				$comment=str_replace('<ul><br />','<ul>',$comment);
				$comment=str_replace('<ol><br />','<ol>',$comment);
				$comment=str_replace('</li><br />','</li>',$comment);
				$comment=str_replace('</ul><br />','</ul>',$comment);
				$comment=str_replace('</ol><br />','</ol>',$comment);

				// Construct the elements
				$r.='<li>';
				$r.='<div class="comment-content">'.$comment.'</div>';

				$creator = $result['comments'][$i]['creator'];

				if (pro_user() && isset($creator['avatar_url'])) {
					// User profile images in comments (Pro feature)
					if (is_local()) {
						$avatar_url =  $creator['avatar_url'];
					} else {
						$avatar_url = str_replace('http://', 'https://', $creator['avatar_url']);
					}

					$r .= '<div class="comment-avatar">';
					$r .= '<img src="' . $avatar_url . '" height="50" width="50" />';
					$r .= '</div>';
				}

				$r .= '<div class="comment-author">';

				if ($creator['id'] == user_id()) {
				    $r .= 'Me';
				} else {
				    $r .= $creator['name'];
				}

				$r.='</div>';
				$r.='</li>';
			}
			$r.='</ul>';
			return $r;
		}
	}
}

// bc_task_complete - Complete a specific task
function bc_task_complete($project_id,$task_id,$mode='complete'){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$api_url='/projects/'.$project_id.'/todos/'.$task_id.'.json';

	if($mode=='incomplete'){
		$data_array=array('completed'=>false);
	}else{
		$data_array=array('completed'=>true);
	}

	bc_post($api_url,$data_array,'update');
	redirect('/pages/task.php?project='.$project_id.'&task='.$task_id);
}

// bc_task_delete - Delete a task
function bc_task_delete($project_id,$task_id){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$api_url='/projects/'.$project_id.'/todos/'.$task_id.'.json';
	bc_post($api_url,array('due_at'=>NULL),'delete');
	redirect('/');
}

// bc_task_due - Get a task's due date
function bc_task_due($project_id,$task_id){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$result=bc_results('/projects/'.$project_id.'/todos/'.$task_id.'.json');
	if($result!=''){
		return $result['due_at'];
	}else{
		return '';
	}
}

// bc_task_due_set - Set a task's due date (requires YYYY-MM-DD format)
function bc_task_due_set($project_id,$task_id,$task_due){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$api_url='/projects/'.$project_id.'/todos/'.$task_id.'.json';
	if($task_due==''){
		$data_array=array('due_at'=>NULL);
	}else{
		$data_array=array('due_at'=>$task_due);
	}
	bc_post($api_url,$data_array,'update');
	redirect('/pages/task.php?project='.$project_id.'&task='.$task_id);
}

// bc_task_edit - Edit a task
function bc_task_edit($task_name, $task_due = '', $project_id, $task_id, $list_id = '', $person_id){
	$task_name = input_clean($task_name, '');
	$task_due = input_clean($task_due, '');
	$project_id = input_clean($project_id, 'numeric');
	$task_id = input_clean($task_id, 'numeric');
	$api_url = '/projects/' . $project_id . '/todos/' . $task_id . '.json';

	if ($list_id) {
		$list_id = input_clean($list_id, 'numeric');
		$data_array = array(
			'content' => $task_name,
			'todolist_id' => $list_id,
			'due_at' => $task_due,
			'assignee' => array(
				'id' => $person_id,
				'type' => 'Person'
			)
		);
	} else {
		$data_array = array(
			'content' => $task_name,
			'due_at' => $task_due,
			'assignee' => array(
				'id' => $person_id,
				'type' => 'Person'
			)
		);
	}

	$result = bc_post($api_url, $data_array, 'update');

	redirect('/pages/task.php?project=' . $project_id . '&task=' . $task_id);
}

// bc_task_format - Turn entered task data into generic HTML
function bc_task_format($project_id, $list_id, $task_id, $task_name, $task_location = '', $task_comments = '', $task_due = '', $completed = 'no', $assigned_to_name = 'Me', $mode = 'list'){
	$r='';
	$date='';
	$class='';
	$date_data='9999';
	if($completed===false||$completed==''){$completed='no';}
	if($completed=='yes'||$completed===true||$completed=='1'){
		$completed='yes';
		$class='completed ';
	}
	if($task_due!=''){
		$interval=date_diff(date_create(date('Y-m-d')),date_create($task_due));
		$date_diff=$interval->format('%a');
		$date_data=str_replace('+','',$interval->format('%R%a'));

		$date='Due ';
		if($date_data<0){
			$date='Due '.$interval->format('%a').' day';
			if($date_diff!=1){ $date.='s'; }
			$date.=' ago';
			$class.='overdue';
		}elseif($date_data>=1){
			if($date_data==1){
				$date.='tomorrow';
			}elseif($date_data>1&&$date_data<8){
				$date.='on '.date('l',strtotime($task_due));
			}elseif($date_data>=8&&$date_data<22){
				$date.='in '.$date_diff.' day';
				if($date_diff!=1){ $date.='s'; }
			}else{
				$date.='on '.date('j F',strtotime($task_due));
			}
			if($date_diff==1){
				$class.='tomorrow';
			}elseif($date_diff>=2 && $date_diff<=7){
				$class.='thisweek';
			}else{
				$class.='upcoming';
			}
		}else{
			$date.='today';
			$class.='today';
		}

		if($date_data<10){
			$date_data='00'.$date_data;
		}elseif($date_data<100){
			$date_data='0'.$date_data;
		}
	}else{
		$class.='noduedate';
	}

	$task_name=htmlspecialchars_decode($task_name);
	if($mode=='page'){$r.='<ul class="task task-single">';}
	$r.='<li';
	if($class!=''){$r.=' class="'.$class.'"';}
	$r.=' data-due="'.$date_data.'">';
	if($mode=='list'){ $r.='<a tabindex="-1" href="/pages/task.php?project='.$project_id.'&task='.$task_id.'" class="task-info">'; }
	$r.='<span class="task-name">'.$task_name.'</span>';
	if($task_location!=''){ $r.='<span class="task-location">'.$task_location.'</span>'; }

	if ($mode == 'page') {
		$r .= '<span class="task-assigned-to">Assigned to ' . $assigned_to_name . '</span>';
	}

	if($date!=''){ $r.='<span class="task-due">'.$date.'</span>'; }
	if($mode=='list'){
		$r.='</a></li>';
	}elseif($mode=='page'){
		$r.='</li></ul>';
		$r.='<nav class="task_actions"><ul>';
		if($completed=='yes'){
			$r.='<li><a id="button_task_complete" href="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=incomplete">'.icon('check-square','Re-open').'</a></li>';
		}else{
			$r.='<li><a id="button_task_complete" href="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=complete">'.icon('check-square-o','Complete').'</a></li>';
			$r.='<li><a id="button_task_edit" href="#">'.icon('edit','Edit').'</a></li>';
			$r.='<li><a id="button_task_delete" href="#">'.icon('trash','Delete').'</a></li>';
		}
		$r.='</ul></nav>';

		$r.='<form id="form_task_edit" name="form_task_edit" class="hidden" method="post" action="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=edit">';
		$r.='<p class="task-name"><textarea class="text" name="task_name" id="task_name" autofocus="autofocus"></textarea></p>';

		if (pro_user()) {
			// Allow the user to assign this task to another person (Pro feature)
			$r .= bc_peoplelist();
		}

		if($task_due == ''){
			$r .= form_date_picker($task_due, 'nodate');
		}else{
			$r .= form_date_picker($task_due, '');
		}

		$r.='<p class="buttons"><input type="hidden" name="due_mode" id="due_mode" value="date" /><input type="button" value="Update task" class="submit" id="button_update_task" /><a class="cancel-edit" id="button_task_canceledit" href="#">'.icon('times','Cancel').'</a></p></form>';
		$r.=bc_task_comments($project_id,$task_id).'<form id="form_comment" name="form_comment" method="post" action="/pages/newcomment.php?project='.$project_id.'&task='.$task_id.'">';
		$r.='<p><textarea class="text" name="comment" id="comment"></textarea></p>';
		$r.='<p class="buttons"><input type="submit" value="Add comment" name="submit" class="submit" /></p></form>';
	}

	return $r;
}

// bc_task_id_from_url - Return the id of a todo mentioned from a URL
function bc_task_id_from_url($api_url){
	if($api_url){
		if(strrpos($api_url,'/todos/')!==false){
			$p=explode('/todos/',$api_url);
			$p=explode('-',$p[1]);
			return $p[0];
		}
	}
}

// bc_task_new - Create a task
function bc_task_new($task_name, $task_due = '', $project_id, $list_id, $person_id){
	$task_name = input_clean($task_name, '');
	$task_due = input_clean($task_due, '');
	$project_id = input_clean($project_id, 'numeric');
	$list_id = input_clean($list_id, 'numeric');
	$api_url = '/projects/' . $project_id . '/todolists/' . $list_id . '/todos.json';

	$data_array = array(
		'content' => $task_name,
		'due_at' => $task_due,
		'assignee' => array(
			'id' => $person_id,
			'type' => 'Person'
		)
	);

	$result = bc_post($api_url, $data_array, 'new');

	if (isset($result['id'])) {
		$task_id = $result['id'];
		redirect('/pages/task.php?project=' . $project_id . '&task=' . $task_id);
	}
}

// bc_tasks_all - Get all tasks assigned to the authenticated user
function bc_tasks_all(){
	$results=bc_results('/people/'.user_id().'/assigned_todos.json');
	$project_count=bc_projects_count();
	$o='';
	$r='';
	if(is_array($results)){
		if(array_key_exists(0,$results)){
			if($results[0]['assigned_todos']!=''){
				$count=count($results);
				for($i=0;$i<$count;$i++){
					$count2=count($results[$i]['assigned_todos']);
					for($j=0;$j<$count2;$j++){
						if($project_count==1){
							$task_location=$results[$i]['name'];
						}else{
							$task_location=$results[$i]['bucket']['name'].' - '.$results[$i]['name'];
						}

						$r .= bc_task_format(
							$results[$i]['bucket']['id'],
							$results[$i]['id'],
							$results[$i]['assigned_todos'][$j]['id'],
							$results[$i]['assigned_todos'][$j]['content'],
							$task_location,
							'',
							$results[$i]['assigned_todos'][$j]['due_on'],
							'',
							'Me',
							'list'
						);
					}
				}
				$o='<ul class="task task-multiple">'."\r\n".$r."\r\n".'</ul>'."\r\n";
			}else{
				$o='<p class="tasks message empty">No assigned tasks left.</p>'."\r\n";
			}
		}else{
			$o='<p class="tasks message empty">No tasks found!</p>'."\r\n";
		}
	}else{
		$o='<p class="tasks message empty">You have no remaining tasks, well done!</p>'."\r\n";
	}
	if($o!=''){ return $o; }
}

// bc_tasks_progress - Get all tasks events for the current user in the last 30 days
function bc_tasks_progress(){
	$today_date=date('Y-m-d');
	$oldest_date=date('Y-m-d',strtotime('-30 days'.$today_date));
	$results=bc_results('/people/'.user_id().'/events.json?since='.$oldest_date);
	$r='';
	$count=count($results);
	for($i=0;$i<$count;$i++){
		if(is_array($results)){
			if(array_key_exists($i,$results)){
				$location=$results[$i]['bucket']['name'];
				$summary=ucfirst($results[$i]['summary']);
				$url=($results[$i]['url'])?$results[$i]['url']:'';
				if(strrpos($summary,"to-do")!==false){
					$summary_p=explode('<span>:</span> ',$summary);
					$summary_d=preg_replace('/to-do/','task',$summary_p['0'],1);
					$summary_d=str_replace(' for a task','',$summary_d);
					$summary_d=str_replace(' a task','',$summary_d);
					$summary_t=$summary_p['1'];
					$r.='<li><a';
					if($url!=''){
						$r.=' href="task.php?project='.bc_project_id_from_url($url).'&task='.bc_task_id_from_url($url).'"';
					}
					$r.='><span class="task-name">'.$summary_t.'</span><span class="update-details">'.$summary_d.'</span><span class="task-location">'.$location;
					$r.='</a></li>';
				}
			}
		}
	}
	return '<ul class="task task-multiple progress">'."\r\n".$r."\r\n".'</ul>'."\r\n";
}

// bc_tasklist - Return a dropdown list of todo lists
function bc_tasklist($selected_project = '', $selected_list = '') {
	$results=bc_results('/projects.json');
	$l=(array)'';
	if(is_array($results)){
		$count=count($results);
		for($i=0;$i<$count;$i++){
			if(array_key_exists($i,$results)){
				$project_id=$results[$i]['id'];
				$project_name=$results[$i]['name'];
				$number_projects=bc_projects_count();

				$results2=bc_results('/projects/'.$project_id.'/todolists.json');
				$count2=count($results2);
				for($j=0;$j<$count2;$j++){
					$list_id=$results2[$j]['id'];
					$list_name=$results2[$j]['name'];
					if($number_projects==1){
						$o='<option label="'.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
					}else{
						$o='<option label="'.$project_name.' - '.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
					}
					if($project_id.'-'.$list_id==$selected_project.'-'.$selected_list){ $o.=' selected'; }
					$o.='>'.$project_name.' - '.$list_name.'</option>';
					$l[]=$o;
					$o='';
				}

				$results3=bc_results('/projects/'.$project_id.'/todolists/completed.json');
				$count3=count($results3);
				for($k=0;$k<$count3;$k++){
					$list_id=$results3[$k]['id'];
					$list_name=$results3[$k]['name'];
					if($number_projects==1){
						$o='<option label="'.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
					}else{
						$o='<option label="'.$project_name.' - '.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
					}
					if($project_id.'-'.$list_id==$selected_project.'-'.$selected_list){ $o.=' selected'; }
					$o.='>'.$project_name.' - '.$list_name.'</option>';
					$l[]=$o;
					$o='';
				}
			}
		}
	}
	if($l!=''){
		$output='';
		foreach($l as $key=>$val){
			$output.=$val;
		}
		return '<p class="task_list select"><select id="task_list" name="task_list">'.$output.'</select></p>';
	}else{
		return '';
	}
}

// bc_get_id - Get the Basecamp ID of a user
function bc_user_id($bc_token, $bc_account) {
	if ($bc_token && $bc_account) {
		$url = 'https://basecamp.com/' . $bc_account . '/api/v1/people/me.json';
		$ch = curl_init();

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json', 'Authorization: Bearer ' . $bc_token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent'],
			CURLOPT_SSL_VERIFYPEER => false
		);

		curl_setopt_array($ch, $options);
		$result = json_decode(curl_exec($ch), 'true');

		if (!$result) {
			error_handle('auth', 'Error extracting Basecamp ID from user details', $_SERVER['SCRIPT_FILENAME'], '');
		} else {
			if (array_key_exists('id', $result)) {
				return $result['id'];
			}
		}
	}

	return false;
}

// bc_user_status - Return information about the authenticated user
function bc_user_status(){
	$result=bc_results('/people/me.json');
	if($result){
		return '<div class="user-status"><span class="image"><img src="'.$result['avatar_url'].'" /></span><span class="name">'.$result['name'].'</span></div>';
	}
}
?>
