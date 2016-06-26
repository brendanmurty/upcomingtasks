<?php

include_once dirname(dirname(__FILE__)) . '/libs/initialise.php';
error_handle('403', 'Internal Server Error - ' . $_SERVER["REQUEST_URI"], '', '');
header('Location: /pages/home.php');

?>
