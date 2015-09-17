<?
$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/common/layout-header.php';
?>

<h2><?= $app_name ?> Pro</h2>
<p>For a small monthly cost, an <?= $app_name ?> Pro account gives access to additional features. A Pro account gives you access to the following:</p>

<ul class="features">
    <li>Editing project details</li>
    <li>User profile images in comments</li>
    <li>Time zone localisation support</li>
    <li>Assign tasks to other people</li>
</ul>

<h3>What about the free account?</h3>
<p>All current <?= $app_name ?> features will continue to work as they are for the life of the product. Your data will not be shared with third-parties and there will never be advertisements to get in the way.</p>

<h3>Why should I subscribe?</h3>
<p><?= $app_name ?> was created as a side-project in September 2012 by <a href="http://brendan.murty.id.au/">Brendan Murty</a>. Paying for a Pro account helps support future development and pay for ongoing maintenance costs.</p>

<h3>What if I want to cancel my subscription?</h3>
<p>You're free to downgrade your account at any time. There is no cancellation fee. You can re-subscribe again whenever you like.</p>

<h3>How do I subscribe?</h3>
<p><?= $app_name ?> Pro will be available for a small monthly cost as soon as some initial Pro features are built.</p>

<?
include_once $root_path . '/common/layout-footer.php';
?>
