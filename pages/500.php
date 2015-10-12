<?
include_once dirname(dirname(__FILE__)) . '/common/initialise.php';
error_handle($page,'500 - Internal Server Error','','');
header('Location: /pages/home.php');
?>