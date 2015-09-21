<?
$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/common/layout-header.php';
?>

<h2><?= $app_name ?> Pro</h2>
<p>For a small monthly cost, an <?= $app_name ?> Pro account gives access to additional features, including:</p>

<ul class="features">
    <li class="released">Editing project details</li>
    <li class="released">Assign tasks to other people</li>
    <li class="released">User profile images in comments</li>
    <li class="planned">Time zone localisation support</li>
    <li class="planned">Add attachments to comments</li>
    <li class="planned">Show posted &amp; updated times for comments</li>
    <li class="planned">Push notifications</li>
    <li class="planned">Convert Basecamp links to <?= $app_name ?> links</li>
    <li class="planned">Inline third-party service previews</li>
    <li class="planned">Calendar view</li>
    <li class="planned">Add Markdown formatting support to task comments</li>
    <li class="planned">Order projects by starred</li>
</ul>

<h3>What about the free account?</h3>
<p>All current <?= $app_name ?> features will continue to work as they are for the life of the product. Your data will never be shared with any third-party and there will never be advertisements to get in the way.</p>

<h3>Why should I subscribe?</h3>
<p><?= $app_name ?> was created as a side-project in September 2012 by <a href="http://brendan.murty.id.au/">Brendan Murty</a>. Along with access to additional features, paying for an <?= $app_name ?> Pro account helps support future development and pay for ongoing maintenance costs.</p>

<h3>What if I want to cancel or pause my subscription?</h3>
<p>You're free to downgrade to a free account at any time. There is no cancellation fee. You can re-subscribe again whenever you like.</p>

<h3>How do I subscribe?</h3>
<p><?= $app_name ?> Pro will be available for a small monthly cost as soon as some initial Pro features are built.</p>

<?
include_once $root_path . '/common/layout-footer.php';
?>
