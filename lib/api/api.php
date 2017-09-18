<?php
require_once('password_compat.php');

define('DB_USER', 'root');
define('DB_PASS', 'xMpATcMXEYb7zHkqjAatYRJQ');
define('DB_NAME', 'forty2');
define('DB_HOST', 'localhost');
try{
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
}catch(PDOException $e){
    die('db_error');
}

// Set this as an XML file
header('Content-Type: text/xml');

if(!isset($_POST['action']) ||empty($_POST['action'])) {
    $_POST['action'] = 'invalid';
    echo returnXML($_POST['action'], 0, "empty_request", "");
    die();
}

switch($_POST['action']) {
    case 'login':
        if(empty($_POST['username']) || empty($_POST['password'])) {
            echo returnXML($_POST['action'], 0, "invalid_request", "");
            die();
        }

        $query = $pdo->prepare('
			SELECT id, activated, password_hash
	        FROM f2users
	        WHERE (username = :username OR email = :email)
	        LIMIT 1;
			');
        $query->bindValue(":username", $_POST['username'], PDO::PARAM_INT);
        $query->bindValue(":email", $_POST['username'], PDO::PARAM_STR);
        $query->execute();

        $tmp = $query->fetch();

        if(count($tmp) > 0 && password_verify($_POST['password'], $tmp['password_hash'])) {
            // User exists!
            if($tmp['activated'] == 0) {
                echo returnXML($_POST['action'], 0, "account_not_activated", "");
                die();
            }

            $query= $pdo->prepare("SELECT * FROM f2bans WHERE user_id = :uid");
            $query->bindValue(":uid", $tmp['id'], PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll();

            if(count($result) > 0) {
                echo returnXML($_POST['action'], 0, "account_suspended", "");
                die();
            }

            // Session handler
            // Delete old sessions
            $query = $pdo->prepare("DELETE FROM f2sessions WHERE user_id = :uid");
            $query->bindValue(":uid", $tmp['id'], PDO::PARAM_INT);
            $query->execute();

            // Create new session
            $now = new DateTime("now");
            $query = $pdo->prepare("INSERT INTO f2sessions (user_id, last_tick) VALUES (:uid, :tick)");
            $query->bindValue(":uid", $tmp['id'], PDO::PARAM_INT);
            $query->bindValue(":tick", $now->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            $query->execute();

            echo returnXML($_POST['action'], 1, "logged_in", $pdo->lastInsertId());
            die();

        }else{
            echo returnXML($_POST['action'], 0, "invalid_username_or_password", "");
            die();
        }

        break;

    case 'tick':
        if( empty($_POST['session']) || !validateSession($_POST['session']) ) {
            echo returnXML($_POST['action'], 0, "invalid_session" . @$_POST['session'], ""); die();
        }
        // Update last active time for the user with this session
        $now = new DateTime("now");
        $query = $pdo->prepare("UPDATE f2sessions SET last_tick = :tick WHERE id = :sid");
        $query->bindValue(":tick", $now->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $query->bindValue(":sid", $_POST['session'], PDO::PARAM_STR);
        $query->execute();
        echo returnXML($_POST['action'], 1, "tick_updated", $_POST['session']);
        break;

    case 'logout':
        if( empty($_POST['session']) || !validateSession($_POST['session']) ) {
            echo returnXML($_POST['action'], 0, "invalid_session", "");
        }
        $query = $pdo->prepare("DELETE FROM f2sessions WHERE id = :sid");
        $query->bindValue(":sid", hexdec($_POST['session']), PDO::PARAM_INT);
        break;

    default:
        echo returnXML($_POST['action'], 0, "invalid_request", @$_POST['session']);
        break;
}

function returnXML($api, $status, $message, $session, $content = "") {
    $_xml = "<xml><config><session>{$session}</session><api>{$api}</api><status>{$status}</status><message>{$message}</message></config><content>{$content}</content></xml>";
    $_tmp = new SimpleXMLElement(html_entity_decode($_xml, ENT_NOQUOTES, 'UTF-8'));
    return $_tmp->asXML();
}

function validateSession($ses) {
    global $pdo;
    // Retrieve sessions with this ID where last_tick is less than 1 minute ago
    $query = $pdo->prepare("SELECT count(*) FROM f2sessions WHERE (id = :sid AND last_tick > NOW() - INTERVAL 1 MINUTE)");
    $query->bindValue(":sid", $ses, PDO::PARAM_INT);
    $query->execute();

    return (boolean)$query->fetch()[0];
}
