<?
include_once dirname(dirname(__FILE__)) . '/libs/initialise.php';
error_handle($page,'500 - Internal Server Error','','');
header('Location: /pages/home.php');
?>