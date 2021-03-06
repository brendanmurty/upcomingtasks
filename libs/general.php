<?php

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

// form_get - Extract the value of a URL parameter
function form_get($url_section, $clean_type = 'alpha') {
	if (isset($_GET[$url_section])) {
		if ($clean_type) {
			$cleaned = input_clean($_GET[$url_section], $clean_type);
			if ($cleaned) {
				return $cleaned;
			}
		} else {
			return $_GET[$url_section];
		}
	}

	return false;
}

// form_post - Extract the value of a form post parameter
function form_post($post_section, $clean_type = 'alpha') {
	if (isset($_POST[$post_section])) {
		if ($clean_type) {
			$cleaned = input_clean($_POST[$post_section], $clean_type);
			if ($cleaned) {
				return $cleaned;
			}
		} else {
			return $_POST[$post_section];
		}
	}

	return false;
}

// icon - Create an element for a Font Awesome icon
function icon($icon_name,$icon_title=''){
	$h='<span class="icon fa fa-'.$icon_name.'"></span>';
	if($icon_title!=''){ $h.='<span class="icon-text-label">'.$icon_title.'</span>'; }
	return $h;
}

// input_clean - Cleans input strings and protects from code injections
function input_clean($input_string, $option = ''){
	if($input_string != '') {
		//$input_string=mysql_real_escape_string($input_string);
		$input_string = htmlspecialchars($input_string,ENT_IGNORE,'utf-8');
		$input_string = strip_tags($input_string);
		$input_string = stripslashes($input_string);

		if ($option == 'numeric') {// Return only numeric characters
			$input_string = preg_replace('/[^0-9]+/', '', $input_string);
		} elseif ($option == 'alpha') {// Return only alpha characters
			$input_string = preg_replace('/[^a-zA-Z]+/', '', $input_string);
		}

		return $input_string;
	}
}

// is_dev - Check if this user is a developer of this app
function is_dev() {
	if (form_get('dev', 'numeric') == '1') {
		return true;
	}

	return false;
}

// is_local - Check if this is a local domain
function is_local() {
	if ($_SERVER['HTTP_HOST'] == 'upcomingtasks.dev') {
		return true;
	}

	return false;
}

// list_screenshots - List the app screenshots (thumbnails should be 150px square)
function list_screenshots(){
	$screenshots = array(
		'list-tasks.png',
		'menu-open.png',
		'multiple-accounts.png',
		'progress-list.png',
		'project-view.png',
		'task.png'
	);

	$list = '';

	foreach ($screenshots as $name) {
		$title = explode(".", $name);
		$title = ucwords(str_replace('-', ' ', $title["0"]));

		$list .= '<li><a href="/pages/image.php?path=/images/screenshots/' . $name . '"><img src="/images/screenshots/thumbnails/' . $name . '" alt="' . $title . '" title="View image: ' . $title . '" /></a></li>';
	}

	if ($list) {
		return '<ul class="screenshots">' . $list . '</ul>';
	}
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

	if(bc_projects_count() == 1){
		// The account only has one project, include a link to view that project directly
		$def.='<li id="action-projects"><a href="/pages/project.php?id='.bc_projects_first().'">'.icon('folder','Project').'</a></li>';
	}else{
		// The account has more than one project, include a link to view a list of all projects
		$def.='<li id="action-projects"><a href="/pages/projects.php">'.icon('folder-close','Projects').'</a></li>';
	}

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
		if ($current_page != 'home') {
			$a=$hm;
		}
	}
	if($current_page=='home'){
		$current_page='UpcomingTasks';
	}elseif($current_page=='account'){
	    if (form_get('mode', 'alpha')) {
			// Logged in user's settings page
	        $a = $hm.$def;
			$current_page = 'Settings';
	    } else {
			// Authentication process select account page
			$current_page = 'UpcomingTasks';
		}
	}elseif($current_page=='newtask'){
		$current_page='New Task';
	}elseif($current_page=='addtohome'){
		$current_page='Add to Home';
	}
	$current_page=ucfirst($current_page);
	if($a==''){
		$a='<nav id="actions" class="empty"><h1>'.icon('ok',$current_page).'</h1></nav>';
	}else{
		$a='<nav id="actions" class="closed"><h1><a id="toggle_nav" href="#">'.icon('chevron-down',$current_page).'</a></h1><ul>'.$a;

		if (user_exists()) {
			$a.='<li class="user">'.bc_user_box().'</li>';
		}

		$a.='</ul></nav>';
	}
	return $a;
}

// redirect - Redirect to a certain URL
function redirect($url){
	if (headers_sent()) {
		print '<script>window.location=\'' . $url . '\';</script>';
	}else{
		header('Location: '.$url);
	}

	exit;
}

// test - Print a test string
function test($string){
	print "\r\n".'<!-- '.$string.' -->'."\r\n";
}

// theme_get - Return the currently selected theme name
function theme_get(){
	$theme_selected = 'light';

	if (!isset($_SESSION)) {
		session_start();
	}

	if (isset($_COOKIE['bc_theme'])) {
		$theme_requested = input_clean($_COOKIE['bc_theme'], '');
		if (file_exists(dirname(dirname(__FILE__)) . '/styles/' . $theme_requested . '.css')) {
			$theme_selected = $theme_requested;
		}
	}

	return $theme_selected;
}

// theme_list - Return a list of the available themes
function theme_list(){
	// Themes array (Theme Title => CSS_file_name)
	$themes = array(
		'Blue' => 'blue',
		'Dark' => 'dark',
		'Light' => 'light',
		'Minimal' => 'minimal',
		'Red' => 'red',
		'Teal' => 'teal'
	);

	$list = '';

	foreach ($themes as $title => $name) {
		$list .= '<li';

		if ($name == theme_get()) {
			$list .= ' class="selected"';
		}

		$list .= '><a href="/pages/account.php?mode=settheme&theme=' . $name . '">' . $title . '</a></li>';
	}

	if ($list) {
		return '<ul class="themes">' . $list . '</ul>';
	}
}

// timezone_list - Create a list of current timezones
function timezone_list() {
    $list = '';

    foreach (timezone_identifiers_list() as $timezone) {
        $list .= '<li';

		if ($timezone == user_timezone_get()) {
			$list .= ' class="selected"';
		}

		$list .= '><a href="/pages/account.php?mode=settimezone&timezone=' . $timezone . '">' . $timezone . '</a></li>';
    }

    return '<ul class="timezones">' . $list . '</ul>';
}

// theme_set - Update the theme in use
function theme_set($theme) {
	if ($theme) {
		$theme = input_clean($theme, '');
		if (file_exists(dirname(dirname(__FILE__)) . '/styles/' . $theme . '.css')) {
		    if (!isset($_SESSION)) {
			    session_start();
		    }

			setcookie("bc_theme", $theme, time()+60*60*24*14);
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
