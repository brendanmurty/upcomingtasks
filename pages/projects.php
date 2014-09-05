<?
$root_path=dirname(dirname(__FILE__));
include_once $root_path.'/common/layout-header.php';
loading_start();
print bc_projects();
loading_done();
include_once $root_path.'/common/layout-footer.php';
?>