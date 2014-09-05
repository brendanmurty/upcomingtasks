<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
?>
<p>Love that <?= $app_name ?> is awesome and free to use? Show your appreciation by donating via PayPal. To begin, just press the button below. No amount is too small!</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="form_donate" name="form_donate">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="8Z3GUAPP5GLYL">
	<input type="image" src="/images/donate.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
</form>
<? include_once $root_path.'/common/layout-footer.php'; ?>