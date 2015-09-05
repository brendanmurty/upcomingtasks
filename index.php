<?php

if ($_SERVER['HTTP_HOST'] == 'upcomingtasks.dev') {
    // Local
    header('Location: /pages/home.php');
} else {
    // Production
    header('Location: https://upcomingtasks.com/pages/home.php');
}
