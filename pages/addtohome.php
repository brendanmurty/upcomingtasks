<?
if(!isset($_GET['mode'])){// Show the standard information
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
?>

<h3>iOS</h3>
<ol>
<li>Visit the <a href="/pages/home.php">homepage</a></li>
<li>Press the action button (the box with the arrow)</li>
<li>Press <em>Add to Home Screen</em></li>
</ol>

<h3>Android</h3>
<ol>
<li>Bookmark the <a href="/pages/home.php">homepage</a></li>
<li>Open the browser <em>bookmarks</em> screen</li>
<li>Long press the bookmark you want</li>
<li>Select <em>Add to Home screen</em></li>
</ol>

<h3>Windows Phone</h3>
<ol>
<li>Visit the <a href="/pages/addtohome.php?mode=wp">Windows Phone tile page</a></li>
<li>Press the <em>...</em> in the bottom right corner</li>
<li>Select <em>pin to start</em></li>
</ol>

<h3>Windows 8</h3>
<ol>
<li>Visit the <a href="/pages/home.php">homepage</a> in Internet Explorer 10</li>
<li>Swipe up from the bottom of the screen to reveal the navigation bar</li>
<li>Press the pin button</li>
<li>Select <em>Pin to Start</em></li>
<li>Press the <em>Pin to Start</em> button</li>
</ol>

<?
include_once $root_path.'/common/layout-footer.php';
}elseif(isset($_GET['mode']) && $_GET['mode']=='wp'){// Show the custom Windows Phone page
?>
<!doctype html>
<html>
<head>
<title>UpcomingTasks</title>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="maximum-scale=1,minimum-scale=1,width=device-width">
<link rel="stylesheet" href="/styles/common.css">
</head>
<body class="page_addtohome_wp">
<div id="icon"><i class="icon-ok"></i></div>
<div id="instructions">Tap <strong>...</strong> and select <strong>pin to start</strong></div>
<script>
(function(){
	if("-ms-user-select" in document.documentElement.style && navigator.userAgent.match(/IEMobile\/10\.0/)){
		var msViewportStyle = document.createElement("style");
		msViewportStyle.appendChild(
			document.createTextNode("@-ms-viewport{width:auto !important;height:auto !important}")
		);
		document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
	}
})();
var url = 'http://upcomingtasks.com/pages/home.php';
if(localStorage.getItem(url)) {
	window.location.replace(url);
}else{
	localStorage.setItem(url, true);
}
</script>
</body>
</html>
<? } ?>