<?php

/****************
f2user class
 * login system
****************/


// $rtn = array();

// $rtn['status'] = TRUE / FALSE
// $rtn['message'] = "Foutmelding";

// $tmp = $query->fetchAll();
// foreach($tmp as $_tmp) {
// 	$rtn[] = $_tmp;
// }
// return $rtn;

// return array("status" => FALSE, "message" => "Error");
//

require_once($_SERVER['DOCUMENT_ROOT'] . 'lib/secret/config.cfg.php');

class f2user {

	private $loggedIn;
    public $user_id;

	function __construct() {
        global $pdo, $config;

	}
	public function mod_login($username , $password) {
        global $pdo, $config;

		$query = $pdo->prepare('
			SELECT id, activated, password_hash
	        FROM f2users
	        WHERE (username = :username OR email = :email)
	        LIMIT 1;
			');
		$query->bindValue(":username", $username, PDO::PARAM_INT);
        $query->bindValue(":email", $username, PDO::PARAM_STR);

        if($query->execute()) {
            // Query executed, check results
            $_tmp = $query->fetch();
            if(count($_tmp) > 0 && password_verify($password, $_tmp['password_hash'])) {
                // User exists!
                if($_tmp['activated'] == 0) {
                    return array("status" => FALSE, "message" => "account_not_activated");
                }
                    $this->loggedIn = true;
                    $this->user_id = $_tmp['id'];

                    $time = new DateTime("now");
                    $query = $pdo->prepare('
                    UPDATE f2users SET last_logon = :llog,
                    last_used_ip = :luip, online = 1 WHERE (username = :username OR email = :email)
                        ');
                    $query->bindValue(":llog", $time->format("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $query->bindValue(':luip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
                    $query->bindValue(":username", $username, PDO::PARAM_INT);
                    $query->bindValue(":email", $username, PDO::PARAM_STR);
                    $query->execute();
            }else{
                // No such user
                return array("status" => FALSE, "message" => "invalid_user_or_pass");
            }
            return array("status" => TRUE, "message" => "success");
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }
	}

    public function user_tick(){
        global $pdo, $config;
        $now = new DateTime('now');
        $query = $pdo->prepare("UPDATE  f2users SET last_active = :la WHERE id = :id");
        $query->bindValue(":la", $now->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $query->bindValue(":id", $this->user_id, PDO::PARAM_STR);
        return $query->execute();
    }

	public function mod_logout() {
		global $pdo, $config;

		$this->loggedIn = false;
        $this->user_id = -1;
	}

    public function get_loggedIn() {
        if ($this->loggedIn && $this->user_id > 0) {
            return true;
        }else{
            return false;
        }
    }

    public function get_isBanned() {
        global $pdo, $config;
        // User exists, and is activated, check for ban
        $query= $pdo->prepare("SELECT * FROM f2bans WHERE user_id = :uid");
        $query->bindValue(":uid", $this->user_id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll();

        if(count($result) > 0) {
            return array("status" => TRUE, "message" => "account_suspended", "content" => array("ban_expiration" => $result[0]['ban_expiration'], "reason" => $result[0]['reason']));
        }else{
            return array("status" => FALSE, "message" => "account_not_suspended");
        }
    }

	public function add_register($username, $email, $password, $password2) {
        global $pdo, $config;
    	$tmp = new DateTime("now");

		if (!$this->valid_username($username)) {
        	return array("status" => FALSE, "message" => "invalid_username");
    	}
    	if (!$this->valid_email($email)){
    		return array("status" => FALSE, "message" => "invalid_email");
    	}
    	if ($this->priv_userDetailsExist($username, $email)){
    		return array("status" => FALSE, "message" => "username_exists");
    	}
    	if (!$this->valid_password($password, $password2)){
    		return array("status" => FALSE, "message" => "invalid_password");
    	}
    	if ($password2 !== $password) {
    		return array("status" => FALSE, "message" => "password_dont_match");
    	}

        // Let's sanitize/create the variables we're gonna use
        $v_username = $username;
        $v_password = password_hash($password, PASSWORD_DEFAULT);
        $v_email    = $email;


	 	$query = $pdo->prepare('
	 		INSERT INTO
	 		`f2users`
	 		(username, email, password_hash, created_on, ip)
	 		VALUES
	 		(:username, :email, :password, :con, :ip)
	 		');
        $query->bindValue(':username', $v_username, PDO::PARAM_STR);
        $query->bindValue(':email', $v_email, PDO::PARAM_STR);
        $query->bindValue(':password', $v_password, PDO::PARAM_STR);
        $query->bindValue(':con', $tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

	 	if($query->execute()) {
            //
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }
        $user_id = $pdo->lastInsertId();

    	// Create verfication key
    	$key = $this->generate_token(16);

    	$query = $pdo->prepare('INSERT INTO f2activations (user_id, act_key) VALUES (:uid, :actkey)');
        $query->bindValue(':uid', $pdo->lastInsertId(), PDO::PARAM_INT);
        $query->bindValue(':actkey', $key, PDO::PARAM_STR);
    	if($query->execute()) {
            // Better than expected
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }

        return array("status" => TRUE, "message" => "account_created", "content" => array("id" => $user_id, "key" => $key));

	}
    public function change_password($password1, $password2){
        global $pdo, $config;

        if (!$this->valid_password($password1, $password2)){
            return array("status" => FALSE, "message" => "invalid_password");
        }
        if ($password2 !== $password1) {
            return array("status" => FALSE, "message" => "password_dont_match");
        }

        $v_password = password_hash($password1, PASSWORD_DEFAULT);

        $query = $pdo->prepare('
	 		UPDATE
	 		`f2users`
	 		set
	 		password_hash=:password
	 		WHERE
	 		`id` = :uid

	 		');

        $query->bindValue(':password', $v_password, PDO::PARAM_STR);
        $query->bindValue(':uid', $this->user_id, PDO::PARAM_STR);

        $query->execute();

        return array ("status" => TRUE, "message" => "password_changed", "content" => 0);
    }
    public function change_email($email){
        global $pdo, $config, $f2backend;

        if (!$this->valid_email($email)){
            return array("status" => FALSE, "message" => "invalid_password");
        }
        if ($this->priv_emailExists($email)){
            return array("status" => FALSE, "message" => "username_exists");
        }

        $query = $pdo->prepare('
	 		UPDATE
	 		`f2users`
	 		set
	 		email=:email
	 		WHERE
	 		`id` = :uid

	 		');

        $query->bindValue(':uid', $this->user_id, PDO::PARAM_STR);
        $query->bindValue(':email', $this->$email, PDO::PARAM_STR);
        $query->execute();



        $key = $this->generate_token(16);

        $query = $pdo->prepare('
            UPDATE
	 		`f2activations`
	 		set
	 		(uid=:user_id, actkey=:act_key)
	 		WHERE
	 		`id` = :uid)');
        $query->bindValue(':uid', $pdo->lastInsertId(), PDO::PARAM_INT);
        $query->bindValue(':actkey', $key, PDO::PARAM_STR);
        if($query->execute()) {
            // Better than expected
        }else{
            return array("status" => FALSE, "message" => "sql_error");
        }

        $f2backend->send_sendActivationKey($this->user_id, $_tmp['content']['key']);
        $this->mod_logout();
        return array ("status" => TRUE, "message" => "password_changed", "content" => 0);
    }

    public function get_details($id = -1) {
    	global $pdo, $config;

        if($id == -1) {
            if(!$this->get_loggedIn()) {
                return array("status" => FALSE, "message" => "not_logged_in", "content" => 0);
            }
            $id = $this->user_id;
        }

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail");
        }

    	$query = $pdo->prepare('SELECT * FROM f2users WHERE id = :uid ');
    	$query->bindValue(':uid', $id, PDO::PARAM_INT);

    	if ($query->execute()) {
    		//
    	} else {
    		return array("status" => FALSE, "message" => "sql_error");
    	}

    	$result = $query->fetchAll();

    	return array("status" => TRUE, "message" => "success", "content" => $result[0]);
	}

    public function get_powerlevel($id = -1) {
    	global $pdo, $config;

        if($id == -1) {
            if(!$this->get_loggedIn()) {
                return array("status" => FALSE, "message" => "not_logged_in", "content" => 0);
            }
            $id = $this->user_id;
        }

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail");
        }

    	$query = $pdo->prepare('SELECT `power_level` FROM `f2users` WHERE `id` = :uid');
    	$query->bindValue(':uid', $id, PDO::PARAM_INT);

    	if ($query->execute()) {
    		$rtn = array("status" => TRUE, "message" => "success");
    	} else {
    		return array("status" => FALSE, "message" => "sql_error");
    	}

    	$result = $query->fetch();

        return array("status" => TRUE, "message" => "success", "content" => $result[0]);
	}

    public function get_rankTitle($id = -1) {
        global $pdo, $config;

        if($id == -1) {
            if(!$this->get_loggedIn()) {
                return array("status" => FALSE, "message" => "not_logged_in", "content" => "Disabled");
            }
            $id = $this->user_id;
        }

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail", "content" => "Disabled");
        }

        $query = $pdo->prepare('SELECT rank_name FROM (f2users INNER JOIN f2ranks ON f2users.power_level = f2ranks.id) WHERE f2users.id = :uid');
        $query->bindValue(':uid', $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch();

        return array("status" => TRUE, "message" => "success", "content" => $result[0]);
    }

    public function get_hasAccess($id = -1, $request) {
        global $pdo, $config;

        if($id == -1) {
            if(!$this->get_loggedIn()) {
                return array("status" => FALSE, "message" => "not_logged_in", "content" => "Disabled");
            }
            $id = $this->user_id;
        }

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail", "content" => "Disabled");
        }

        $query = $pdo->prepare('SELECT rank_permissions FROM (f2users INNER JOIN f2ranks ON f2users.power_level = f2ranks.id) WHERE f2users.id = :uid');
        $query->bindValue(':uid', $id, PDO::PARAM_INT);
        $query->execute();

        $_tmp = json_decode($query->fetch()[0], TRUE);

        return array("status" => TRUE, "message" => "success", "content" => (boolean)@$_tmp[$request]);
    }

    public function get_username($id = -1) {
		global $pdo, $config;
        if($id == -1) {$id = $this->user_id;}

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail");
        }

		$query = $pdo->prepare('SELECT `username` FROM `f2users` WHERE `id` = :uid ');
		$query->bindValue(':uid', $id, PDO::PARAM_INT);

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        } else {
            return array("status" => FALSE, "message" => "sql_error");
        }

		$result = $query->fetch();

        return array("status" => TRUE, "message" => "success", "content" => $result[0]);
	}

    public function get_useravatar($id = -1) {
        global $pdo, $config;

        if($id == -1 && $this->get_loggedIn()) {$id = $this->user_id;}
        if($id == -1) {
            return array("status" => FALSE, "message" => "fail", "content" => "userPlaceholder.jpg");
        }

        if(!$this->get_userExists("", $id)) {
            return array("status" => FALSE, "message" => "fail", "content" => "userPlaceholder.jpg");
        }

        $query = $pdo->prepare('SELECT `avatar_url` FROM `f2users` WHERE `id` = :uid ');
        $query->bindValue(':uid', $id, PDO::PARAM_INT);

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        } else {
            return array("status" => FALSE, "message" => "sql_error");
        }

        $result = $query->fetch();

        return array("status" => TRUE, "message" => "success", "content" => 'http://forty2reviews.com/lib/images/avatar_pics/'.$result[0]);
    }
    public function get_userid($username = "") {
        global $pdo, $config;
        if($username == "" && $this->get_loggedIn()) {$username = $this->get_username($this->user_id)["content"];}
        if($username == "") {
            return array("status" => FALSE, "message" => "fail");
        }

        if(!$this->get_userExists($username)) {
            return array("status" => FALSE, "message" => "fail");
        }

        $query = $pdo->prepare('SELECT `id` FROM `f2users` WHERE `username` = :username ');
        $query->bindValue(':username', $username, PDO::PARAM_STR);

        if($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        } else {
            return array("status" => FALSE, "message" => "sql_error");
        }

        $result = $query->fetch();
            return array("status" => TRUE, "message" => "success", "content" => $result[0]);
    }

	public function get_userExists($username = "", $user_id = 0) {
		global $pdo, $config;
        if($username == "" && $user_id == 0) {
            return false;
        }

        $query = $pdo->prepare("SELECT count(*) FROM f2users WHERE (id = :uid OR username = :username)");
        $query->bindValue(":uid", $user_id, PDO::PARAM_INT);
        $query->bindValue(":username", $username, PDO::PARAM_STR);

        $query->execute();

        return (boolean)$query->fetch()[0];
	}


	// private validation functions

	private function valid_email($email) {
		global $pdo, $config;

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		} else {
			return true;
		}

	}

	private function valid_username($username) {
        global $pdo, $config;

		if ($username == "") {
	       return FALSE;
		}

		if (strlen($username) < $config['minuser_length'] || strlen($username) > $config['maxuser_length']) {
            return FALSE;
		}
		return !preg_match('/[^A-Za-z0-9.#\\-$]/', $username); //only A-Z, a-z and 0-9

	}

	private function valid_password($password) {
		global $pdo, $config;
		$pass = trim($password);

	    if (empty($pass)) {
	        return FALSE;
	    }

	    if (strlen($pass) < $config['minpass_length'] || strlen($pass) > $config['maxpass_length']) {
            return FALSE;
	    }

	    return preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{1,64}$/',$password);

	}

	private function generate_token($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }
        return $code;
    }

    private function priv_userDetailsExist($username, $email) {
        global $pdo, $config;
        if($username == "" || $email == "") {
            return true;
        }

        $query = $pdo->prepare("SELECT count(*) FROM f2users WHERE (email = :mail OR username = :username)");
        $query->bindValue(":mail", $email, PDO::PARAM_STR);
        $query->bindValue(":username", $username, PDO::PARAM_STR);

        $query->execute();
        $result = $query->fetch();

        if($result[0] > 0) {
            return true;
        }else{
            return false;
        }
    }
    private function priv_emailExists($email) {
        global $pdo, $config;
        if($email == "") {
            return true;
        }

        $query = $pdo->prepare("SELECT count(*) FROM f2users WHERE email = :mail");
        $query->bindValue(":mail", $email, PDO::PARAM_STR);

        $query->execute();
        $result = $query->fetch();

        if($result[0] > 0) {
            return true;
        }else{
            return false;
        }
    }
}
