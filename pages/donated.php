<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
?>
<p class="message thanks">Thank you for supporting <?= $app_name ?>!</p>
<p>Your awesome donation will assist with future development.</p>
<?
include_once $root_path.'/common/layout-footer.php';
?>