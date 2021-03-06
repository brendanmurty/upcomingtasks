<?php

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/libs/initialise.php';

// Asset update dates
$date_update_css = "20161014";
$date_update_js  = "20160626";

// Extract the current page's name
$req = ltrim($_SERVER["REQUEST_URI"], '/');
if (!isset($page)) {
	$req_parts = explode('?', $req);
	$page_parts = pathinfo($req_parts[0]);
	$page = $page_parts['filename'];
}

// Setup the page class
$page_class = 'page_' . $page;
$page_class .= (user_exists() ? ' user_yes' : ' user_no');

// Create the navigation
$nav = navigation($page, '');

// Extract the current theme
$theme_selected = theme_get();
$theme_requested = form_get('theme', 'alpha');

// Overwrite selected theme if requested via query string
if ($theme_requested) {
	if (file_exists($root_path . '/styles/' . $theme_requested . '.css')) {
		$theme_selected = $theme_requested;
	}
}

$page_class .= ' theme_' . $theme_selected;

// Create the robots meta content
$meta_robots = 'index,follow';
if (user_exists()) {
	$meta_robots = 'noindex,nofollow';
}

// Check for dev mode
$is_dev = false;
if(is_dev() || is_local()){
	$is_dev = true;
	$page_class .= ' dev';
}

// Apply default app values if not set
if (!isset($app_name)) {
	$app_name = 'UpcomingTasks';
}

if (!isset($app_info)) {
	$app_info = 'The simplified way to manage your Basecamp tasks when you\'re away from your computer.';
}

$page_notification = 'This service will be retired on November 15, 2016. <a href="http://b.murty.io/post/farewell-upcomingtasks">Learn more</a>.';

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo $app_name ?></title>
<meta name="description" content="<?php echo $app_info ?>">
<meta name="robots" content="<?php echo $meta_robots ?>">
<meta name="handheldfriendly" content="true">
<meta name="mobileoptimized" content="480">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta name="application-name" content="UpcomingTasks"/>
<meta name="theme-color" content="#0D7F07">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="msapplication-TileColor" content="#12AF09"/>
<meta name="msapplication-TileImage" content="/images/logo-144-app.png"/>
<meta name="msapplication-starturl" content="/pages/home.php" />
<meta name="google-site-verification" content="7nvJtlVAcDOSBaTnKAYOh1JtBt9t-0vvj3TJhggy0II" />
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="/images/logo-16-clear.png">
<link rel="apple-touch-icon-precomposed" href="/images/logo-114.png">
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Source+Sans+Pro">
<link rel="stylesheet" href="/styles/common.css?v=<?php echo $date_update_css?>">
<link rel="stylesheet" href="/styles/<?php echo $theme_selected?>.css?v=<?php echo $date_update_css?>">
<script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<script src="https://use.fontawesome.com/c4caff9ff7.js"></script>
<?php if ($is_dev) {// Dev JS ?>
<script src="/scripts/js/upcomingtasks-dev.js?v=<?php echo $date_update_js?>"></script>
<?php }else{ // Standard JS ?>
<script src="/scripts/js/upcomingtasks.js?v=<?php echo $date_update_js?>"></script>
<?php } if ($page!='authenticate') {// Google Analytics & Screen size fixes ?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-710527-10']);
_gaq.push(['_trackPageview']);
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<?php } ?>
</head>
<body class="<?php echo $page_class ?>">
	<div id="loading-icon" style="display:none"><span class="fa fa-spinner fa-spin"></span></div>
	<div id="container">
		<a id="top"></a>

		<header>
			<?php if ($nav != '') { print $nav; } ?>
		</header>

		<!--[if lt IE 9]>
		<div class="notification error">
			<p>UpcomingTasks requires a modern browser, please upgrade your browser to enjoy the full experience.</p>
		</div>
		<![endif]-->

		<?php if (isset($page_notification)) { ?>
		<div class="notification error">
			<p><?php print $page_notification; ?></p>
		</div>
		<?php } ?>

		<div id="content">
