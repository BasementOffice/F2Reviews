<?php

/****************
f2comment class
 * login system
 ****************/
require_once($_SERVER['DOCUMENT_ROOT'] . "lib/secret/config.cfg.php");


class f2comment {

    public function get_reviewComments($id, $page = 0) {
        global $pdo, $config;

        if(!$this->priv_existsReview($id)) {
            return array("status" => FALSE, "message" => "invalid_review");
        }
        $query = $pdo->prepare('
			SELECT f2users.username, f2comments.id, f2comments.comment, f2comments.created_on, f2comments.hidden, f2comments.upboats
			FROM (f2comments INNER JOIN f2users ON f2comments.author_id = f2users.id)
			WHERE f2comments.review_id = :rid
			ORDER BY f2comments.id ASC
			LIMIT ' . ($page-1) * $config['results_per_page'] . ', ' . $config['results_per_page']);
        $query->bindValue(":rid", $id, PDO::PARAM_INT );
        $query->execute();

        $comments = $query->fetchAll();

        return array("status" => TRUE, "message" => "success", "content" => $comments);
    }

    public function rm_deleteComment($id) {
        global $pdo, $config;

        if(!$this->priv_existsComment($id)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("UPDATE f2comments SET hidden = 1 WHERE id = :rcid");
        $query->bindValue(":rcid", $id, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function mod_editComment($id, $comment) {
        global $pdo, $config;

        if(!$this->priv_existsComment($id)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("UPDATE f2comments SET comment = :comment WHERE id = :rcid");
        $query->bindValue(":comment", $comment, PDO::PARAM_STR);
        $query->bindValue(":rcid", $id, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function add_addComment($rid, $authorid, $comment) {
        global $pdo, $config;

        if(!$this->priv_existsReview($rid)) {
            return array("status" => FALSE, "message" => "invalid_review");
        }

        if(!$this->priv_canComment($authorid)) {
            return array("status" => FALSE, "message" => "post_time_limit");
        }

        $tmp = new DateTime("now");
        $query = $pdo->prepare("
        INSERT INTO f2comments
        (review_id, author_id, comment, created_on)
        VALUES
        (:rid, :aid, :comment, :con)
        ");

        $query->bindValue(":rid", $rid, PDO::PARAM_INT);
        $query->bindValue(":aid", $authorid, PDO::PARAM_INT);
        $query->bindValue(":comment", $comment, PDO::PARAM_STR);
        $query->bindValue(":con", $tmp->format("Y-m-d H:i:s"), PDO::PARAM_STR);

        $query->execute();

        return array("status" => TRUE, "message" => "success");
    }

    public function get_hasUpboated($cid, $aid) {
        global $pdo, $config;

        if(!$this->priv_existsComment($cid)) {
            return array("status" => FALSE, "message" => "invalid_id");
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2upboats WHERE comment_id = :cid AND user_id = :aid");
        $query->bindValue(":cid", $cid, PDO::PARAM_INT);
        $query->bindValue(":aid", $aid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => (boolean)$query->fetch()[0], "message" => "success");
    }

    public function add_upboat($cid, $aid) {
        global $pdo, $config;

        if($this->get_hasUpboated($cid, $aid)['status']) {
            $query = $pdo->prepare("
            DELETE FROM f2upboats WHERE comment_id = :cid AND user_id = :aid LIMIT 1
            ");
            $query->bindValue(":cid", $cid, PDO::PARAM_INT);
            $query->bindValue(":aid", $aid, PDO::PARAM_INT);
            $query->execute();
            return array("status" => FALSE, "message" => "removed_upboat");
        }else{
            $query = $pdo->prepare("
            INSERT INTO f2upboats
            (comment_id, user_id)
            VALUES
            (:cid, :aid)
            ");
            $query->bindValue(":cid", $cid, PDO::PARAM_INT);
            $query->bindValue(":aid", $aid, PDO::PARAM_INT);
            $query->execute();

            return array("status" => TRUE, "message" => "success");
        }
    }

    public function get_commentCount($rid) {
        global $pdo, $config;

        if(!$this->priv_existsReview($rid)) {
            return array("status" => FALSE, "message" => "invalid_review");
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2comments WHERE review_id = :rid");
        $query->bindValue(":rid", $rid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

       public function get_commentCountUser($uid) {
        global $pdo, $config;


        $query = $pdo->prepare("SELECT COUNT(*) FROM f2comments WHERE author_id = :uid");
        $query->bindValue(":uid", $uid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    public function get_upboatCount($cid) {
        global $pdo, $config;

        if(!$this->priv_existsComment($cid)) {
            return array("status" => FALSE, "message" => "invalid_comment", "content" => 0);
        }

        $query = $pdo->prepare("SELECT COUNT(*) FROM f2upboats WHERE comment_id = :cid");
        $query->bindValue(":cid", $cid, PDO::PARAM_INT);
        $query->execute();

        return array("status" => TRUE, "message" => "success", "content" => $query->fetch()[0]);
    }

    //-----------------------------------------------------------------------------

    private function priv_existsReview($id) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM `f2reviews` WHERE `id` = :rid");
        $query->bindValue(":rid", $id, PDO::PARAM_INT);

        if(!$query->execute()) {
            return FALSE;
        }
        $result = $query->fetch();

        return (boolean)$result[0];
    }

    private function priv_existsComment($rcid) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM `f2comments` WHERE `id` = :rcid");
        $query->bindValue(":rcid", $rcid, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch();

        return (boolean)$result[0];
    }

    private function priv_canComment($id) {
        global $pdo, $config;

        $query = $pdo->prepare("SELECT count(*) FROM f2comments WHERE (author_id = :id AND created_on > date_sub(now(), interval 1 minute))");
        $query->bindValue(":id", $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch();

        return ($result[0] < $config['comments_per_minute']);
    }

}