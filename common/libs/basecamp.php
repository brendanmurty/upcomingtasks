<?
// bc_account - Select the first bcx account found in the users account list
function bc_account($bc_token){
	if($bc_token){
		$url='https://launchpad.37signals.com/authorization.json';
		$ch=curl_init();
		$options=array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$bc_token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent']
		);
		curl_setopt_array($ch,$options);
		$result=json_decode(curl_exec($ch),'true');
		if($result!=''){
			if(isset($result['accounts'])){
				$count=count($result['accounts']);
				for($i=0;$i<$count;$i++){
					if($result['accounts'][$i]['product']=='bcx'){
						return $result['accounts'][$i]['id'];
						break;
					}
				}
			}
		}else{
			return '';
		}
	}
}

// bc_account_select - Produce a list of all accounts the user has access to
function bc_account_select($mode='list'){
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
						if($mode=='select'){
							$s.='<option label="'.$account_name.'" value="'.$account_id.'">'.$account_name.'</option>';
						}else{
							$s.='<li><a href="/pages/account.php?mode=select&id='.$account_id.'">'.$account_name.'</a></li>';
						}
					}
				}
			}
			if($s!=''){
				if($mode=='select'){
					return '<form id="account-select"><select name="account-id" onchange="select_redirect(\'account-id\')">'.$s.'</select></form>';
				}else{
					return '<ul class="account-select">'.$s.'</ul>';
				}
			}else{
				return '<p class="error">Sorry, no eligible Basecamp accounts were found. Please <a href="https://basecamp.com/signup">create a Basecamp account</a> to use this app.</p>';
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
	$result=bc_results('/people/me.json');
	$u='<a id="button_logout" href="#">Logout</a>';
	if(is_array($result)){
		if(array_key_exists('avatar_url',$result) && array_key_exists('name',$result)){
			$u='<a id="button_logout" href="#"><span class="image"><img src="'.$result['avatar_url'].'" /></span>';
			$u.='<span class="name">'.$result['name'].'<em>Logout</em></span></a>';
		}
	}
	return $u;
}

// bc_post - Post data to Basecamp
function bc_post($api_url,$data_array,$method='new'){
	if($api_url && $data_array){
		$db_result=db_query("SELECT bc_account, bc_token FROM users WHERE bc_id='".$_COOKIE['bc_id']."' LIMIT 1");
		$api_url='https://basecamp.com/'.$db_result['bc_account'].'/api/v1'.$api_url;
		$ch=curl_init();
		$options=array(
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$db_result['bc_token']),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent']
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
		return json_decode(curl_exec($ch),'true');
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

// bc_project_name - Return the name of the specificied project (given the id)
function bc_project_name($id){
	$id=input_clean($id,'numeric');
	$r=bc_results('/projects/'.$id.'.json');
	$c=count($r);
	if($c>0){ return $r['name']; }
}

// bc_project - Return a list of all tasks assigned to the project
function bc_project($id){
	$id=input_clean($id,'numeric');
	$r1=bc_results('/projects/'.$id.'.json');
	$r2=bc_results('/projects/'.$id.'/todolists.json');
	
	$h='<li><span class="project-name">'.$r1['name'].'</span><span class="project-description">'.$r1['description'].'</span><ul class="list">';
	for($i=0;$i<count($r2);$i++){
		if($r2[$i]['name']!=''){
			$h.='<li><a class="add-task" href="/pages/newtask.php?project='.$id.'&list='.$r2[$i]['id'].'" title="Add a task to the \''.$r2[$i]['name'].'\' project">'.icon('plus','New').'</a><span class="list-name">'.$r2[$i]['name'].'</span><span class="list-description">'.$r2[$i]['description'].'</span><ul class="task task-multiple">';
			$r3=bc_results('/projects/'.$id.'/todolists/'.$r2[$i]['id'].'.json');
			for($j=0;$j<count($r3['todos']['remaining']);$j++){
				$h.='<li><a href="/pages/task.php?project='.$id.'&task='.$r3['todos']['remaining'][$j]['id'].'"><span class="task-name">'.$r3['todos']['remaining'][$j]['content'].'</span></a></li>';
			}
			$h.='</ul></li>';
		}
	}
	$r4=bc_results('/projects/'.$id.'/todolists/completed.json');
	for($k=0;$k<count($r4);$k++){
		$h.='<li class="completed"><a class="add-task" href="/pages/newtask.php?project='.$id.'&list='.$r4[$k]['id'].'" title="Add a task to the \''.$r4[$k]['name'].'\' project">'.icon('plus','New').'</a><span class="list-name">'.$r4[$k]['name'].'</span>';
	}
	$h.='</ul></li>';
	return '<ul class="project project-single">'."\r\n".$h.'</ul>';
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

// bc_results - Simple query to the Basecamp API
function bc_results($api_url=''){
	session_start();
	if($api_url!='' && isset($_COOKIE['bc_id'])){
		$db_result=db_query("SELECT bc_account, bc_token FROM users WHERE bc_id='".$_COOKIE['bc_id']."' LIMIT 1");
		return bc_results_main($api_url,$db_result['bc_token'],$db_result['bc_account']);
	}else{
		return '';
	}
}

// bc_results_main - Query the Basecamp API and return the result
function bc_results_main($api_url='',$token='',$account=''){
	if($api_url!='' && $token!='' && $account!=''){
		if(substr($api_url,0,1)=='/'){
			$api_url='https://basecamp.com/'.$account.'/api/v1'.$api_url;
		}
		$ch=curl_init();
		$options=array(
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent']
		);
		curl_setopt_array($ch,$options);
		$results=curl_exec($ch);		
		return json_decode($results,'true');
	}else{
		return '';
	}
}

// bc_task - Get a task's details and it's attached comments
function bc_task($project_id,$task_id){
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$result=bc_results('/projects/'.$project_id.'/todos/'.$task_id.'.json');
	$r='';
	if($result!=''){
		$r.=bc_task_format($project_id,$result['todolist_id'],$result['id'],$result['content'],bc_project_name($project_id).' - '.bc_list_name($project_id,$result['todolist_id']),'',$result['due_at'],$result['completed'],'page');
		return $r;
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
				$r.='<div class="comment-author">'.$result['comments'][$i]['creator']['name'].'</div>';
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
function bc_task_edit($task_name,$task_due='',$project_id,$task_id,$list_id=''){
	$task_name=input_clean($task_name,'');
	$task_due=input_clean($task_due,'');
	$project_id=input_clean($project_id,'numeric');
	$task_id=input_clean($task_id,'numeric');
	$api_url='/projects/'.$project_id.'/todos/'.$task_id.'.json';
	if($list_id!=''){
		$list_id=input_clean($list_id,'numeric');
		$data_array=array('content'=>$task_name,'todolist_id'=>$list_id,'due_at'=>$task_due,'assignee'=>array('id'=>$_COOKIE['bc_id'],'type'=>'Person'));
	}else{
		$data_array=array('content'=>$task_name,'due_at'=>$task_due,'assignee'=>array('id'=>$_COOKIE['bc_id'],'type'=>'Person'));
	}
	$result=bc_post($api_url,$data_array,'update');
	redirect('/pages/task.php?project='.$project_id.'&task='.$task_id);
}

// bc_task_format - Turn entered task data into generic HTML
function bc_task_format($project_id,$list_id,$task_id,$task_name,$task_location='',$task_comments='',$task_due='',$completed='no',$mode='list'){
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
	if($date!=''){ $r.='<span class="task-due">'.$date.'</span>'; }
	if($mode=='list'){
		$r.='</a></li>';
	}elseif($mode=='page'){
		$r.='</li></ul>';
		$r.='<nav class="task_actions"><ul>';
		if($completed=='yes'){
			$r.='<li><a id="button_task_complete" href="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=incomplete">'.icon('check-empty','Re-open').'</a></li>';
		}else{
			$r.='<li><a id="button_task_complete" href="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=complete">'.icon('check','Complete').'</a></li>';
			$r.='<li><a id="button_task_edit" href="#">'.icon('edit','Edit').'</a></li>';
			$r.='<li><a id="button_task_delete" href="#">'.icon('trash','Delete').'</a></li>';
		}
		$r.='</ul></nav>';

		$r.='<form id="form_task_edit" name="form_task_edit" class="hidden" method="post" action="/pages/task.php?project='.$project_id.'&task='.$task_id.'&mode=edit">';
		$r.='<p class="task-name"><textarea class="text" name="task_name" id="task_name" autofocus="autofocus"></textarea></p>';
		if($task_due==''){
			$r.=form_date_picker($task_due,'nodate');
		}else{
			$r.=form_date_picker($task_due,'');
		}
		$r.='<p class="buttons"><input type="hidden" name="due_mode" id="due_mode" value="date" /><input type="button" value="Update task" class="submit" id="button_update_task" /><a class="cancel-edit" id="button_task_canceledit" href="#">'.icon('remove-circle','Cancel').'</a></p></form>';
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
function bc_task_new($task_name,$task_due='',$project_id,$list_id){
	$task_name=input_clean($task_name,'');
	$task_due=input_clean($task_due,'');
	$project_id=input_clean($project_id,'numeric');
	$list_id=input_clean($list_id,'numeric');
	$api_url='/projects/'.$project_id.'/todolists/'.$list_id.'/todos.json';
	$data_array=array('content'=>$task_name,'due_at'=>$task_due,'assignee'=>array('id'=>$_COOKIE['bc_id'],'type'=>'Person'));
	$result=bc_post($api_url,$data_array,'new');
	if(isset($result['id'])){
		$task_id=$result['id'];
		redirect('/pages/task.php?project='.$project_id.'&task='.$task_id);
	}
}

// bc_tasks_all - Get all tasks assigned to the authenticated user
function bc_tasks_all(){
	$results=bc_results('/people/'.$_COOKIE['bc_id'].'/assigned_todos.json');
	$o='';
	$r='';
	if(is_array($results)){
		if(array_key_exists(0,$results)){
			if($results[0]['assigned_todos']!=''){
				$count=count($results);
				for($i=0;$i<$count;$i++){
					$count2=count($results[$i]['assigned_todos']);
					for($j=0;$j<$count2;$j++){
						$r .= bc_task_format($results[$i]['bucket']['id'], $results[$i]['id'], $results[$i]['assigned_todos'][$j]['id'], $results[$i]['assigned_todos'][$j]['content'], $results[$i]['bucket']['name'].' - '.$results[$i]['name'], '', $results[$i]['assigned_todos'][$j]['due_on'], '', 'list');
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
	$results=bc_results('/people/'.$_COOKIE['bc_id'].'/events.json?since='.$oldest_date);
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
					$r.='<!-- '.$url.' -->';
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

// bc_tasklists - Return a dropdown list of todo lists
function bc_tasklists($selected_project='',$selected_list=''){
	$results=bc_results('/projects.json');
	$l=(array)'';
	if(is_array($results)){
		$count=count($results);
		for($i=0;$i<$count;$i++){
			if(array_key_exists($i,$results)){
				$project_id=$results[$i]['id'];
				$project_name=$results[$i]['name'];
				
				$results2=bc_results('/projects/'.$project_id.'/todolists.json');
				$count2=count($results2);
				for($j=0;$j<$count2;$j++){
					$list_id=$results2[$j]['id'];
					$list_name=$results2[$j]['name'];
					$o='<option label="'.$project_name.' - '.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
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
					$o='<option label="'.$project_name.' - '.$list_name.'" value="'.$project_id.'-'.$list_id.'"';
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
		return '<p class="task_list select"><select id="task_lists" name="task_lists">'.$output.'</select></p>';
	}else{
		return '';
	}
}

// bc_get_id - Get the Basecamp ID of a user
function bc_user_id($bc_token,$bc_account){
	if($bc_token && $bc_account){
		$url='https://basecamp.com/'.$bc_account.'/api/v1/people/me.json';
		$ch=curl_init();
		$options=array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json','Authorization: Bearer '.$bc_token),
			CURLOPT_USERAGENT => $GLOBALS['auth_user_agent']
		);
		curl_setopt_array($ch,$options);
		$result=json_decode(curl_exec($ch),'true');
		$user_id=$result['id'];		
		return $user_id;
	}
}

// bc_user_status - Return information about the authenticated user
function bc_user_status(){
	$result=bc_results('/people/me.json');
	if($result){
		return '<div class="user-status"><span class="image"><img src="'.$result['avatar_url'].'" /></span><span class="name">'.$result['name'].'</span></div>';
	}
}
?>