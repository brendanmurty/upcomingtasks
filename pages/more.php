<?php

$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/libs/header.php';

?>
<h2>About</h2>
<p><?php echo $app_name ?> was created by <a href="http://b.murty.io">Brendan Murty</a> to allow for easy management of <a href="http://basecamp.com/2">Basecamp 2</a> tasks on a mobile device.</p>
<p>Follow <a href="https://twitter.com/upcomingtasks">UpcomingTasks on Twitter</a> for feature and status updates.</p>
<h2>Tools</h2>
<ul class="tools">
	<li><a href="/pages/addtohome.php">Add to home screen</a></li>
	<li><a href="mailto:support@upcomingtasks.com">Email support@upcomingtasks.com</a></li>
	<li><a href="https://bitbucket.org/brendanmurty/upcomingtasks">View source code</a></li>
	<li><a href="https://twitter.com/upcomingtasks">Follow UpcomingTasks on Twitter</a></li>
	<li><a href="https://twitter.com/share?text=Check out @UpcomingTasks, the simplified way to manage your Basecamp tasks when you're away from your computer -&amp;url=http://upcomingtasks.com">Share on Twitter</a></li>
</ul>
<?php

include_once $root_path.'/libs/footer.php';

?>
