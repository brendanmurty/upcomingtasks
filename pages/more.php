<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
?>
<h2>About</h2>
<p><?= $app_name ?> was created by <a tabindex="-1" href="http://brendanmurty.com">Brendan Murty</a> to allow for easy management of <a tabindex="-1" href="http://basecamp.com">Basecamp</a> tasks on a mobile device.</p>
<p>Follow <a href="https://twitter.com/upcomingtasks">UpcomingTasks on Twitter</a> for feature and status updates.</p>
<h2>Tools</h2>
<ul class="tools">
	<li><a href="/pages/addtohome.php">Add to home screen</a></li>
	<li><a href="https://twitter.com/upcomingtasks">Ask for help</a></li>
	<li><a href="https://github.com/brendanmurty/upcomingtasks/issues/new?assignee=brendanmurty">Send feedback</a></li>
	<li><a href="https://twitter.com/share?text=Check out @UpcomingTasks, the simplified way to manage your Basecamp tasks when you're away from your computer -&amp;url=https://upcomingtasks.com">Share on Twitter</a></li>
	<li><a href="/pages/donate.php">Donate to support development</a></li>
</ul>
<?
include_once $root_path.'/common/layout-footer.php';
?>