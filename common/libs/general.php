<?
// form_date_picker - Create a date picker
function form_date_picker($custom_date='',$mode=''){
	$this_year=date('Y');
	$ten_years=$this_year+11;
	if($custom_date==''){ // Use todays date
		$selected_day=date('j');
		$selected_month=date('m');
		$selected_year=date('Y');
	}else{ // Use a custom date (YYYY-MM-DD format required)
		$date_parts=explode('-',$custom_date);
		$selected_day=$date_parts['2'];
		$selected_month=$date_parts['1'];
		$selected_year=$date_parts['0'];
	}
	$f='<p class="date select';
	if($mode=='nodate'){ $f.=' disabled'; }
	$f.='">';
	$f.='<input type="checkbox" name="due_mode_select" id="due_mode_select" value="date"';
	if($mode!='nodate'){ $f.=' checked="checked"'; }
	$f.='><label for="due_mode_select">Due date</label>';
	$f.='<select id="date_day" name="date_day">';
	for($i=1;$i<32;$i++){
		$n=$i;
		if($n<10){ $n='0'.$n; }
		$f.='<option label="'.$i.'" value="'.$n.'"';
		if($i==$selected_day){ $f.=' selected="selected"'; }
		$f.='>'.$i.'</option>';
	}
	$f.='</select>';
	$f.='<select id="date_month" name="date_month">';
	for($i=1;$i<13;$i++){
		$timestamp=mktime(0,0,0,$i,1,$this_year);
		$l=date('M',$timestamp);
		if($i<10){ $i='0'.$i; }
		$f.='<option label="'.$l.'" value="'.$i.'"';
		if($i==$selected_month){ $f.=' selected="selected"'; }
		$f.='>'.$l.'</option>';
	}
	$f.='</select>';
	$f.='<select id="date_year" name="date_year">';
	for($i=$this_year;$i<$ten_years;$i++){
		$f.='<option label="'.$i.'" value="'.$i.'"';
		if($i==$selected_year){ $f.=' selected="selected"'; }
		$f.='>'.$i.'</option>';
	}
	$f.='</select>';
	$f.='</p>';

	return $f;
}

// icon - Create an element for a Font Awesome icon
function icon($icon_name,$icon_title=''){
	$h='<i class="icon icon-'.$icon_name.'"></i>';
	if($icon_title!=''){ $h.='<span>'.$icon_title.'</span>'; }
	return $h;
}

// input_clean - Cleans input strings and protects from code injections
function input_clean($input_string,$option=''){
	if($input_string!=''){
		//$input_string=mysql_real_escape_string($input_string);
		$input_string=htmlspecialchars($input_string,ENT_IGNORE,'utf-8');
		$input_string=strip_tags($input_string);
		$input_string=stripslashes($input_string);
		
		if($option=='numeric'){// Return only numeric characters
			$input_string=preg_replace('/[^0-9]+/','',$input_string);
		}elseif($option=='alpha'){// Return only alpha characters
			$input_string=preg_replace('/[^a-zA-Z]+/','',$input_string);
		}
		return $input_string;
	}
}

// is_dev - Check if this user is a developer of this app
function is_dev(){
	$isdev=false;
	$ip=$_SERVER['REMOTE_ADDR'];
	if(isset($_GET['dev'])){
		if($_GET['dev']=='1'){ $isdev=true; }
	}
	return $isdev;
}

// is_mobile - Check if this device is a mobile
function is_mobile(){
	$mobiles='mobile,android,blackberry,iemobile,kindle,midp,opera m,palm,wap,xda,symbian,windows ce,windows phone';
	$agent=strtolower($_SERVER['HTTP_USER_AGENT']);
	foreach(explode(',',$mobiles) as $mobile){
		if(strpos($agent,$mobile)!==false){
			return true;
		}
	}
}

// list_screenshots - List the app screenshots (thumbnails should be 150px square and stored in a "thumbnails" subfolder)
function list_screenshots(){
	$top = dirname(dirname(dirname(__FILE__)));
	$folder = '/images/screenshots/';
	$return = '';
	if($path = opendir($top.$folder)){
		while(($file = readdir($path)) !== false){
			if($file != '.' && $file != '..' && $file != 'thumbnails'){
				$return .= '<li><a href="/pages/image.php?path='.$folder.$file.'"><img src="'.$folder.'thumbnails/'.$file.'" alt="'.$file.'" title="View image: '.$file.'" /></a></li>';
			}
		}
	}
	if($return != ''){ return '<ul class="screenshots">'.$return.'</ul>'; }
}

// loading_start - Show a loading image
function loading_start(){
	print '<script>$("body").addClass("loading");$("#loading-icon").show();</script>';
}

// loading_done - Hide the loading image
function loading_done(){
	print '<script>$("body").removeClass("loading");$("#loading-icon").hide();</script>';
}

// loading_temp - Show a loading image for 5 seconds then hide it
function loading_temp(){
	print '<script>$("body").addClass("loading");$("#loading-icon").show();';
	print 'setTimeout(function(){$("body").removeClass("loading");$("#loading-icon").hide();},5000);</script>';
}

// navigation - Create the dynamic navigation bar
function navigation($current_page,$option=''){
	$hm='<li id="action-home"><a href="/pages/home.php">'.icon('home','Home').'</a></li>';
	$def='<li id="action-newtask"><a id="button_new" href="/pages/newtask.php">'.icon('plus','New Task').'</a></li>';
	$def.='<li id="action-projects"><a href="/pages/projects.php">'.icon('folder-close','Projects').'</a></li>';
	$def.='<li id="action-progress"><a href="/pages/progress.php">'.icon('tasks','Progress').'</a></li>';
	$def.='<li id="action-account"><a href="/pages/account.php?mode=options">'.icon('cog','Settings').'</a></li>';
	$def.='<li id="action-more"><a href="/pages/more.php">'.icon('info','More').'</a></li>';
	$a='';
	if(user_exists()){
		if($current_page=='home'){
			$a=$def;
		}else{
			if($current_page!='account'){
				$a=$hm.$def;
			}
		}
	}else{
		if($current_page!='home'&&$current_page!='stats'){
			$a=$hm;
		}
	}
	if($current_page=='home'){
		$current_page='UpcomingTasks';
	}elseif($current_page=='stats'){
		$current_page='UpcomingTasks Stats';
	}elseif($current_page=='account'){
		$current_page='UpcomingTasks Settings';
	}elseif($current_page=='newtask'){
		$current_page='New Task';
	}elseif($current_page=='donate'){
		$current_page='Donate to UpcomingTasks';
	}elseif($current_page=='donated'){
		$current_page='Thanks!';
	}elseif($current_page=='addtohome'){
		$current_page='Add to Home';
	}
	$current_page=ucfirst($current_page);
	if($a==''){
		$a='<nav id="actions" class="empty"><h1>'.$current_page.'</h1></nav>';
	}else{
		$a='<nav id="actions" class="closed"><h1><a id="toggle_nav" href="#">'.icon('chevron-down',$current_page).'</a></h1><ul>'.$a;
		if(user_exists()) $a.='<li class="user">'.bc_user_box().'</li>';
		$a.='</ul></nav>';
	}
	return $a;
}

// redirect - Redirect to a certain URL
function redirect($url){
	if(headers_sent()){
		print "<script>window.location='".$url."';</script>";
	}else{
		header('Location: '.$url);
	}
	exit();
}

// stat_global_users - Count the number of users
function stat_global_users(){
	$sql="SELECT COUNT(*) as count_users FROM users WHERE (number_projects>0) AND (number_tasks>0)";
	$result=db_query($sql);
	return '<div class="stats stats-users"><span class="stats-value">'.vague_count($result['count_users']).'</span><span class="stats-title">Active Users</span></div>';
}

// stat_global_projects - Count the number of global projects
function stat_global_projects(){
	$result=db_query("SELECT SUM(number_projects) as count_projects FROM users");
	return '<div class="stats stats-global stats-projects"><span class="stats-value">'.vague_count($result['count_projects']).'</span><span class="stats-title">Projects</span></div>';
}

// stat_global_tasks - Count the number of global tasks
function stat_global_tasks(){
	$result=db_query("SELECT SUM(number_tasks) as count_tasks FROM users");
	return '<div class="stats stats-global stats-tasks"><span class="stats-value">'.vague_count($result['count_tasks']).'</span><span class="stats-title">Tasks</span></div>';
}

// stat_projects - Count the number of projects for the current user
function stat_projects(){
	session_start();
	$result=db_query("SELECT number_projects FROM users WHERE bc_id='".$_COOKIE['bc_id']."' LIMIT 1");
	$p='';
	if($result['number_projects']>1){ $p='s'; }
	return '<div class="stats stats-this stats-projects"><span class="stats-value">'.vague_count($result['number_projects']).'</span><span class="stats-title">Project'.$p.'</span></div>';
}

// stat_tasks - Count the number of tasks for the current user
function stat_tasks(){
	session_start();
	$result=db_query("SELECT number_tasks FROM users WHERE bc_id='".$_COOKIE['bc_id']."' LIMIT 1");
	$p='';
	if($result['number_tasks']>1){ $p='s'; }
	return '<div class="stats stats-this stats-tasks"><span class="stats-value">'.vague_count($result['number_tasks']).'</span><span class="stats-title">Task'.$p.'</span></div>';
}

// test - Print a test string
function test($string){
	print "\r\n".'<!-- '.$string.' -->'."\r\n";
}

// theme_get - Return the currently selected theme name
function theme_get(){
	$theme_selected='light';// Set the default theme
	session_start();
	if(isset($_COOKIE['bc_theme']) && $_COOKIE['bc_theme']!=''){
		$theme_requested=input_clean($_COOKIE['bc_theme'],'');
		if(file_exists(dirname(dirname(dirname(__FILE__))).'/styles/'.$theme_requested.'.css')){
			$theme_selected=$theme_requested;
		}
	}
	return $theme_selected;
}

// theme_list - Return a list of the available themes
function theme_list(){
	$folder=dirname(dirname(dirname(__FILE__))).'/styles/';
	
	// Extract a list of files in the folder and sort them
	$dir=opendir($folder);
	$files=array();
	while($files[]=readdir($dir));
	sort($files);
	closedir($dir);
	
	// Create the theme selector list
	$return='';
	foreach($files as $file){
		$ext=pathinfo($file,PATHINFO_EXTENSION);
		if($file!='.' && $file!='..' && $ext=='css' && $file!='common.css'){
			$theme_name=str_replace('.'.$ext,'',$file);
			$theme_title=str_replace("_"," ",str_replace("-"," ",$theme_name));
			$theme_title=ucwords(strtolower($theme_title));
			$return.='<li><a href="/pages/account.php?mode=settheme&theme='.$theme_name.'">'.$theme_title.'</a></li>';
		}
	}
	if($return!=''){ return '<ul class="themes">'.$return.'</ul>'; }
}

// theme_set - Update the theme in use
function theme_set($theme){
	if($theme!=''){
		$theme=input_clean($theme,'');
		$root_path=dirname(dirname(dirname(__FILE__)));
		if(file_exists($root_path.'/styles/'.$theme.'.css')){
			session_start();
			setcookie("bc_theme",$theme,time()+60*60*24*14);
		}
	}
}

// vague_count - Return a vague number (32, 124, 1.42k, 1.73m)
function vague_count($number){
	if(is_numeric($number) && $number!=''){
		switch(strlen($number)){
			case 1:
			case 2:
			case 3:
				return $number;
				break;
			case 4:
				return round(($number/1000),2).'k';
				break;
			case 5:
				return round(($number/1000),2).'k';
				break;
			case 6:
				return round(($number/1000),2).'k';
				break;
			case 7:
				return round(($number/1000000),2).'m';
				break;
			case 8:
				return round(($number/1000000),2).'m';
				break;
			case 9:
				return round(($number/1000000),2).'m';
				break;
			case 10:
				return round(($number/1000000000),2).'b';
				break;
			default:
				return $number;
		}
	}else{
		return $number;
	}
}
?>