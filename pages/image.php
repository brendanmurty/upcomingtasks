<?php

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/header.php';

$image_path = form_get('path', 'none');

if ($image_path && (substr($image_path, 0, 8) == '/images/')) {
	if (file_exists($root_path . $image_path)) {
		// Show a specific image
		if (isset($_SERVER["HTTP_REFERER"])) {
			$close_link = $_SERVER["HTTP_REFERER"];
		} else {
			$close_link = '/pages/home.php';
		}

		$title = basename($image_path);
		$title = explode(".", $title);
		$title = ucwords(str_replace('-', ' ', $title["0"]));

		echo '<h1>' . $title . '</h1><p class="image"><img src="' . $image_path . '" alt="Image: ' . $title . '" /></p><p class="buttons"><a href="' . $close_link . '">Close</a></p>';
	}else{
		redirect('/pages/home.php');
	}
}else{
	redirect('/pages/home.php');
}

include_once $root_path . '/libs/footer.php';
?>
