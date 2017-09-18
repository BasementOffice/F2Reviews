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
//$f2frontend -> setView();

if(empty($_GET['page1'])){
    $_GET['page1'] = '';
}
$body = "";

//$startUrl = 'http://forty2reviews.com/';
$startUrl = &$config['start_url'];

switch ($_GET['page1']) {
    case 'home':
    case '': {
        include_once('lib/pages/home.php');
    break;}

    case 'news': {
        include_once('lib/pages/news.php');
    break;}

    case 'games': {
        include_once('lib/pages/games.php');
    break;}

    case 'animu': {
        include_once('lib/pages/animu.php');
    break;}

    case 'user': {
        include_once('lib/pages/user.php');
    break;}

    case 'review': {
        include_once('lib/pages/review.php');
    break;}

    case 'item': {
        include_once('lib/pages/item.php');
    break;}

    default: {
        $_GET['error'] = "404";
        include_once('error.php');
        exit();
    break;}
}

include_once('lib/base/head.php');
echo $body;
include_once('lib/base/footer.php');