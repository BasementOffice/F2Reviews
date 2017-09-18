<?php
// Autoload
function __autoload($className) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . "lib/classes/" . $className . '.class.php')) {
        require ($_SERVER['DOCUMENT_ROOT'] . "lib/classes/" . $className . '.class.php');
    }else{
        require ($_SERVER['DOCUMENT_ROOT'] . "lib/" . $className . '.php');
    }
}

session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/config.cfg.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/include.php');
header('Content-Type: text/html; charset=UTF-8');
// Checks
if($f2user->get_loggedIn()) {
    // User is logged in, checking.. Stuff...
    $isBanned = $f2user->get_isBanned();
    if($isBanned['status'] == TRUE) {
        $datetime = new DateTime($isBanned['content']['ban_expiration']);
        $banned_until = $datetime->format("d/m/Y");
        $reason = $isBanned['content']['reason'];
        $warning = "<h3>Your account has been suspended.<br/>Reason: $reason<br/>Until: $banned_until</h3>";
    }
}


if($f2user->get_loggedIn()) {

}

