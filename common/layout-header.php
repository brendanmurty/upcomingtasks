<?
date_default_timezone_set('Australia/Sydney');
$root_path = dirname(dirname(__FILE__));
include_once $root_path.'/common/initialise.php';

// Import update dates (added as a query string on the inserts)
$date_update_css = "20131024a";
$date_update_js  = "20130903a";

// Extract the current page's name
$req=ltrim($_SERVER["REQUEST_URI"],'/');
if(!isset($page)){
	$req_parts=explode('?',$req);
	//$page=str_replace('.php','',str_replace('pages/','',$req_parts[0]));
	$page_parts=pathinfo($req_parts[0]);
	$page=$page_parts['filename'];
}

// Setup the page class
$page_class='page_'.$page;
$page_class.=(user_exists() ? ' user_yes':' user_no');
//$page_class.=(is_mobile() ? ' mobile':' desktop');

// Create the navigation
$nav=navigation($page,'');

// Extract the current theme
$theme_selected=theme_get();
if(isset($_GET['theme']) && $_GET['theme']!=''){// Overwrite if requested in qstring
	$theme_requested=input_clean($_GET['theme'],'');
	if(file_exists($root_path.'/styles/'.$theme_requested.'.css')){
		$theme_selected=$theme_requested;
	}
}
$page_class+=' theme_'.$theme_selected;

// Create the robots meta content
$meta_robots='index,follow';
if(user_exists() && $page!='extras' && $page!='stats'){
	$meta_robots='noindex,nofollow';
}else if($page=='stats'){
	$meta_robots='noindex,follow';
}

// Check for dev mode
$is_dev=false;
if(isset($_GET['dev']) && $_GET['dev']=='1'){
	$is_dev=true;
	$page_class.=' dev';
}

// Apply default app values if not set
if(!isset($app_name)){
	$app_name='UpcomingTasks';
}
if(!isset($app_info)){
	$app_info='The simplified way to manage your Basecamp tasks when you\'re away from your computer.';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $app_name ?></title>
<meta name="description" content="<?= $app_info ?>">
<meta name="robots" content="<?= $meta_robots ?>">
<meta name="handheldfriendly" content="true">
<meta name="mobileoptimized" content="480">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">
<meta name="application-name" content="UpcomingTasks"/>
<meta name="msapplication-TileColor" content="#12AF09"/>
<meta name="msapplication-TileImage" content="/images/logo-144-clear.png"/>
<meta name="msapplication-starturl" content="/pages/home.php" />
<meta name="google-site-verification" content="7nvJtlVAcDOSBaTnKAYOh1JtBt9t-0vvj3TJhggy0II" />
<link rel="shortcut icon" href="/images/logo-16-clear.png">
<link rel="apple-touch-icon-precomposed" href="/images/logo-114.png">
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Source+Sans+Pro">
<link rel="stylesheet" href="/styles/common.css?v=<?=$date_update_css?>">
<link rel="stylesheet" href="/styles/<?=$theme_selected?>.css?v=<?=$date_update_css?>">
<script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<? if($is_dev){// Dev JS ?>
<script src="/scripts/upcomingtasks-dev.js?v=<?=$date_update_js?>"></script>
<? }else{ // Standard JS ?>
<script src="/scripts/upcomingtasks.js?v=<?=$date_update_js?>"></script>
<? } if($page!='authenticate'){// Google Analytics & Screen size fixes ?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-710527-10']);
_gaq.push(['_trackPageview']);
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
(function(){
	if("-ms-user-select" in document.documentElement.style && navigator.userAgent.match(/IEMobile\/10\.0/)){
		var msViewportStyle = document.createElement("style");
		msViewportStyle.appendChild(
			document.createTextNode("@-ms-viewport{width:auto !important;height:auto !important}")
		);
		document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
	}
})();
</script>
<? } ?>
</head>
<body class="<?= $page_class ?>">
	<div id="loading-icon" style="display:none"><span class="icon icon-spinner"></span></div>
	<div id="container">
		<a id="top"></a>
		<header>
			<? if($nav!=''){ print $nav; } ?>
		</header>
		<!--[if lt IE 9]>
		<div id="ancient-browser">
			<p>UpcomingTasks requires a modern browser. Some features might not work in this browser.</p>
		</div>
		<![endif]-->
		<div id="content">