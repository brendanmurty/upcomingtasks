<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/header.php';
if(!user_exists()){
$screenshots=list_screenshots();
?>
<div id="welcome">
	<p><?= $app_info ?></p>
	<p><?= $app_welcome ?></p>
	<?= $screenshots ?>
	<a class="button login" href="/pages/login.php">Login with Basecamp</a>
</div>
<?
}else{
	loading_temp();
	print bc_tasks_all();
}
include_once $root_path.'/common/footer.php';
?>
