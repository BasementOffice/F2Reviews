<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 3/25/15
 * Time: 5:34 PM
 */
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

// Check if an action has been set
if(!empty($_POST) && isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'login': {
            // Check if all data has been sent
            if(!isset($_POST['username'], $_POST['password'])) {$msg = 'corrupt_data';}
            $_tmp = $f2user->mod_login($_POST['username'], $_POST['password']);
            if($_tmp['status'] == FALSE) {
                $msg = $_tmp['message'];
                // Login errors don't need further processing.
            }else{
                $msg = 'success';
            }
        break;}

        case 'register': {
        // Check if all data has been sent
        if(!isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password2'])) {$msg = 'corrupt_data';}
            $_tmp = $f2user->add_register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password2']);
            if($_tmp['status'] == FALSE) {
                $msg = $_tmp['message'];
                // Registration errors don't need further processing.
            }else{
                // Send activation key
                $f2backend->send_sendActivationKey($_tmp['content']['id'], $_tmp['content']['key']);
                $msg = 'success';
            }
        break;}

        case 'logout': {
            $f2user->mod_logout(); // Shouldn't throw any errors.
        break;}

        case 'config': {
            if( !$f2user->get_loggedIn() ) { $msg = ""; break;} // Throw an unknown error
            if(isset($_POST['password'], $_POST['password2'])) {
                $_tmp = $f2user->change_password($_POST['password'], $_POST['password2']);

                if($_tmp['status'] == FALSE){
                    $msg = $_tmp['message'];
                }else{
                    $msg = 'success';
                }
            }
            if(isset($_POST['email'])){
                $_tmp = $f2user->change_email($_POST['email']);

                if($_tmp['status'] == FALSE){
                    $msg = $_tmp['message'];
                }else{
                    $msg = 'success';
                }
            }
        break;}

        default: {
            $msg = 'fatal_error_no_data_received';
        break;}

        case 'getNotified': {
            $msg = "nvt";
            if($f2user->get_loggedIn()){
                echo 4;
            }else{
                echo 0;
            }
        break;}

        case 'getToken': {
            $msg = "nvt";
            echo $_SESSION['token'];
        break;}

        case 'user_tick': {
            $msg = "nvt";
            if($f2user->get_loggedIn()){
                if($f2user->user_tick() == 1){
                    echo "Succesfully updated last active time!";
                }else{
                    echo "An error accurred";
                }
            }else{
                echo "You are not logged in!";
            }
        break;}
    }
}else{
    $msg = 'fatal_error_no_data_received';
}


if(isset($msg)) {
    switch($msg) {
        case 'nvt': {
            //placebo message
        break;}

        case 'success': {
            echo 'success';
        break;}

        case 'invalid_password': {
            echo 'Password contains illegal characters.';
        break;}

        case 'password_dont_match': {
            echo 'Entered passwords don\'t match.';
        break;}

        case 'account_suspended': {
            echo 'This account has been suspended';
        break;}

        case 'invalid_username': {
            echo 'Username contains illegal characters.';
        break;}

        case 'invalid_email': {
            echo 'This is not a valid e-mail address.';
        break;}

        case 'username_exists': {
            echo 'This username and/or e-mail is already in use.';
        break;}

        case 'corrupt_data':
        case 'fatal_error_no_data_received': {
            echo 'Incorrect or corrupted data.';
        break;}

        case 'invalid_user_or_pass': {
            echo 'Invalid username and/or password.';
        break;}

        case 'account_not_activated': {
            echo 'This account has not been activated.';
        break;}

        case 'sql_error': {
            echo 'Something\'s wrong with the database';
        break;}

        default: {
            echo 'An unknown error has occured.';
        break;}
    }
}else{
    echo 'An unknown error has occured.';
}