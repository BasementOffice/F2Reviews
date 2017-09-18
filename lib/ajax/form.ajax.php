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

if(empty($_POST)) {
    // Nothing was sent!
    echo 'fatal_error_no_data_received';
}

switch($_POST['form']) {
    case 'login':
        echo '
        <h2>Login form</h2>
        <form id="login-form">
            <input type="text" class="inputBox" placeholder="Username" name="username" required>
            <input type="password" class="inputBox" placeholder="Password"  name="password" required>
            <input type="hidden" name="action" value="login" />
            <a class="submit" rel="login-submit">Login</a>
        </form>
        ';
        break;

    case 'register':
        global $config;
        echo '
        <h2>Registration form</h2>
        <form id="register-form">
            <input type="text" class="inputBox" placeholder="Username" name="username" required>
            <input type="password" class="inputBox" placeholder="Password"  name="password" required>
            <input type="password" class="inputBox" placeholder="Verify password"  name="password2" required>
            <input type="email" class="inputBox" placeholder="E-mail"  name="email" required>
            <input type="hidden" name="action" value="register" />
            <div class="message warning">
                <h3>Password must contain: 1 lowercase char, 1 uppercase char, 1 digit and 1 special sign. (Min. ' . $config['minpass_length'] . ' characters)</h3>
            </div>
            <a class="submit" rel="register-submit">Register</a>
        </form>
        ';
        break;

    case 'config':
        echo '
        <h2>Settings</h2>
        <form id="config-form">
            <p>Change your password</p>
            <input type="password" class="inputBox" placeholder="Password"  name="password" required>
            <input type="password" class="inputBox" placeholder="Verify password"  name="password2" required>
            <p>Change your email address (Verification required)</p>
            <input type="email" class="inputBox" placeholder="E-mail"  name="email" required>
            <input type="hidden" name="action" value="config" />
            <a class="submit" rel="config-submit">Apply Settings</a>
        </form>
        ';
        break;

    case 'add-review':
        if( !$f2user->get_loggedIn()) {
            echo '<div class="message warning"><h3>You have to be logged in to do this.</h3></div>';
            break;
        }
        if( empty($_POST['item']) ) {
            echo '<div class="message error"><h3>No data was received.</h3></div>';
            break;
        }
        if( !is_numeric($_POST['item']) || !$f2item->get_itemExists($_POST['item']) ) {
            echo '<div class="message warning"><h3>Invalid item.</h3></div>';
            break;
        }
        if( !$f2user->get_loggedIn() || $f2user->get_isBanned($f2user->user_id)['status']  || !$f2user->get_hasAccess($f2user->user_id, "regular")) {
            echo '<div class="message warning"><h3>You do not have permission to do this action.</h3></div>';
            break;
        }
        if( $f2user->get_isBanned($f2user->user_id)['status']  || !$f2user->get_hasAccess($f2user->user_id, "regular")) {
            echo '<div class="message warning"><h3>You do not have permission to do this action.</h3></div>';
            break;
        }
        $item = $f2item->get_item($_POST['item'])['content'];
        echo '
        <h2>Add a review</h2>
        <form id="add-review-form-form">
        <h3 class="itemname">'.$item['item_name'].'</h3>
            <input type="hidden" name="action" value="review-add" />
            <input type="hidden" name="item" value="'.$_POST['item'].'" />
            <input type="text" name="title" placeholder="Enter a title here." maxlength="64" />
            <textarea name="review" placeholder="Your thoughts about this product."></textarea>
            <input type="range" min="1" max="10" step="0.1" value="6.0" name="rating" onchange="document.getElementById(\'v\').innerHTML = this.value;" oninput="document.getElementById(\'v\').innerHTML = this.value;" /><span id="v">6.0</span>
            <a class="submit" rel="review-submit">Submit review</a>
        </form>
        ';
        break;

    default:
        echo 'Error';
        break;
}