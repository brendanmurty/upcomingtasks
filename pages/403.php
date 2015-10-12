<?
include_once dirname(dirname(__FILE__)) . '/common/initialise.php';
if(!isset($page)){ $page=''; }
error_handle($page,'403 - Forbidden','','');
header('Location: /pages/home.php');
?>