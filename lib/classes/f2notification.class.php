<?php
/**
 * Created by PhpStorm.
 * User: The Russian Engineer
 * Date: 30-4-15
 * Time: 9:35
 */

class f2notification {

    private function add_notification($id, $message) {
        global $pdo, $config;


        $query = $pdo->prepare('
	 		INSERT INTO
	 		`f2notifications`
	 		(to_userid, message)
	 		VALUES
	 		(:uid, :mess,)
	 		');
        $query->bindValue(":uid", $id, PDO::PARAM_INT);
        $query->bindValue(":mess", $message, PDO::PARAM_INT);

        if ($query->execute) {
            return array("status" => TRUE, "message" => "succes");
        } else {
            return array("status" => TRUE, "message" => "sql_error");
        }
    }

    public function get_notification($id) {
        global $pdo, $config;

        $query = $pdo->prepare('SELECT
        message FROM f2notifications WHERE to_user = :uid AND watched = 0');
        $query->bindValue(":uid", $id, PDO::PARAM_INT);

        if ($query->execute()) {
            $rtn = array("status" => TRUE, "message" => "success");
        } else {
            return array("status" => FALSE, "message" => "sql_error");
        }

        $result = $query->fetch();

        return array("status" => TRUE, "message" => "success", "content" => $result[0]);
    }

    public function mod_seen($id) {
    global $pdo, $config;

        $query = $pdo->prepare('UPDATE f2notification SET watched = 1 WHERE to_userid = :uid ');
        $query->bindValue(":uid", $id, PDO::PARAM_INT);

        if ($query->execute) {
            return array("status" => TRUE, "message" => "succes");
        } else {
            return array("status" => TRUE, "message" => "sql_error");
        }
    }
}