<?php

/****************
f2backend class
 * login system
****************/
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/secret/config.cfg.php");

class f2backend {
    public function logError($page, $message, $parameters) {
        global $pdo, $config;
        $_tmp = new DateTime("now");

        $query = $pdo->prepare("
        INSERT INTO error_logs
        (error_time, error_message, error_parameters, error_page)
        VALUES
        (:etime, :emsg, :eprm, :epage)
        ");
        $query->bindValue(':etime', $_tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $query->bindValue(':emsg', $message, PDO::PARAM_STR);
        $query->bindValue(':eprm', $parameters, PDO::PARAM_STR);
        $query->bindValue(':epage', $page, PDO::PARAM_STR);

        $query->execute();
    }
    function timePassed($time){
        $date = new DateTime($time);
        $now = new DateTime();
        $seconds = $now->format('U') - $date->format('U');
        if($seconds <= 60){
            $returnString = "less than a minute ago";
        }else if($seconds <= 3600){
            $returnString =   floor($seconds / 60);
            if($returnString == 1){
                $returnString .= " minute ago";
            }else{
                $returnString .= " minutes ago";
            }
        }else if($seconds <= 86400){
            $returnString =   floor(floor($seconds / 60) / 60);
            if($returnString == 1){
                $returnString .= " hour ago";
            }else{
                $returnString .= " hours ago";
            }
        }else if($seconds <= 604800){
            $returnString =   floor(floor(floor( $seconds / 60) / 60) / 24);
            if($returnString == 1){
                $returnString .= " day ago";
            }else{
                $returnString .= " days ago";
            }
        }else if($seconds <= 2592000){
            $returnString = floor(floor(floor(floor( $seconds / 60) / 60) / 24) / 7);
            if($returnString == 1){
                $returnString .= " week ago";
            }else{
                $returnString .= " weeks ago";
            }
        }else if($seconds <= 31104000){
            $returnString = floor(floor(floor(floor( $seconds / 60) / 60) / 24) / 30);
            if($returnString == 1){
                $returnString .= " month ago";
            }else{
                $returnString .= " months ago";
            }
        }else{
            $returnString = floor(floor(floor(floor(floor(floor( $seconds / 60) / 60) / 24) / 30.43) / 12));
            if($returnString == 1){
                $returnString .= " year ago";
            }else{
                $returnString .= " years ago";
            }
        }

        return $returnString;
    }

    public function send_sendActivationKey($id, $key) {
        //TODO: Make check if user exists
        global $pdo, $config;
        // Get user e-mail from database
        $query = $pdo->prepare("SELECT email, username FROM f2users WHERE id = :uid LIMIT 1");
        $query->bindValue(":uid", $id, PDO::PARAM_INT);
        $query->execute();

        $user = $query->fetch();

        $headers    = array();
        $mail_to    = $user[0];
        $mail_from  = $config['noreply_mail'];
        $subject    = "Forty2 account activation";
        $mail = <<<mail
Beste {$user['username']},

Je hebt nu een account aangemaakt bij Forty2 reviews met de inlognaam: {$user['username']}
Gebruik dit account om de status van je bestelling te zien bij http://forty2reviews.com

Voordat je de account kunt gebruiken moet je eenmalig op onderstaande link klikken:
http://forty2reviews.com/user/activation/{$id}/{$key}

Als de link niet werkt, kopieer deze dan in de adresbalk van je browser.

Met vriendelijke groet,
Het Forty2reviews team

info@forty2reviews.com
http://forty2reviews.com
mail;

        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=iso-8859-1";
        $headers[] = "From: Forty2 Reviews <" . $mail_from . ">";
        $headers[] = "Reply-To: ".$user['username']." <" . $mail_to . ">";
        $headers[] = "X-Mailer: PHP/".phpversion();

        if(mail($mail_to, $subject, $mail, implode("\r\n", $headers))){
            $this->logError("mail", "Mail sent","ID: " . $id . " Key: " . $key .  " To: " . $mail_to . " From: " . $mail_from . " Headers: " . implode(", ", $headers) . " Mail: " . $mail);
        }else{
            $this->logError("mail", "Failed to send mail","ID: " . $id . " Key: " . $key .  " To: " . $mail_to . " From: " . $mail_from . " Headers: " . implode(", ", $headers) . " Mail: " . $mail);
        }


    }

    public function mod_activateUser($id, $key) {
        global $pdo, $config;

        // Check if the activation key exists
        $query = $pdo->prepare("SELECT count(*) FROM f2activations WHERE user_id = :uid AND act_key = :akey");
        $query->bindValue(":uid", $id, PDO::PARAM_INT);
        $query->bindValue(":akey", $key, PDO::PARAM_STR);
        $query->execute();

        $_tmp = $query->fetch();

        if($_tmp[0] < 1) {
            // No such key!
            return array("status" => FALSE, "message" => "Activation key already used or invalid.");
        }
        // Key exists, activate user
        $query = $pdo->prepare("UPDATE f2users SET activated = 1, power_level = 1 WHERE id = :uid");
        $query->bindValue(":uid", $id, PDO::PARAM_INT);
        $query->execute();

        // User activated, remove key
        $query = $pdo->prepare("DELETE FROM f2activations WHERE user_id = :uid AND act_key = :akey");
        $query->bindValue(":uid", $id, PDO::PARAM_INT);
        $query->bindValue(":akey", $key, PDO::PARAM_STR);
        $query->execute();

        //Done!
        return array("status" => TRUE, "message" => "account_activated");
    }

}