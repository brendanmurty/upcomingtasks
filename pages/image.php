<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
if(isset($_GET["path"])){ $path=$_GET["path"]; }
if(isset($path) && (substr($path,0,8)=='/images/' || substr($path,0,21)=='https://basecamp.com/')){
	if(file_exists(dirname(dirname(__FILE__)).$path) || substr($path,0,21)=='https://basecamp.com/'){
		if(isset($_SERVER["HTTP_REFERER"])){
			$close_link=$_SERVER["HTTP_REFERER"];
		}else{
			$close_link='/pages/home.php';
		}

		$title=basename($path);
		$title=explode(".",$title);
		$title=ucfirst($title["0"]);
		$title=str_replace("-"," ",$title);
		$title=str_replace("_"," ",$title);
		
		echo '<h1>'.$title.'</h1><p class="image"><img src="'.$path.'" alt="Image: '.$title.'" /></p><p class="buttons"><a href="'.$close_link.'">Close</a></p>';
	}else{
		redirect('/pages/home.php');
	}
}else{
	redirect('/pages/home.php');
}
include_once $root_path.'/common/layout-footer.php';
?>